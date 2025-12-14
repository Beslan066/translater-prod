<?php

namespace App\Http\Controllers;

use App\Jobs\ExportSentencesToCsv;
use App\Models\Sentence;
use App\Models\Translate;
use App\Models\User;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Jobs\ProcessSentenceBatchJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;



class SentenceController extends Controller
{

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt|max:307200', // Ограничение на загрузку до 300 МБ
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $chunkSize = 1000; // Количество строк на один чанк
        $totalLines = 0;

        if (($handle = fopen($filePath, 'r')) !== false) {
            try {
                DB::beginTransaction(); // Начинаем транзакцию

                $batch = Bus::batch([])->dispatch();
                $currentBatch = [];

                while (($line = fgets($handle)) !== false) {
                    $trimmedLine = trim($line);

                    if (!empty($trimmedLine)) {
                        $currentBatch[] = $trimmedLine;

                        if (count($currentBatch) >= $chunkSize) {
                            $batch->add(new ProcessSentenceBatchJob($currentBatch));
                            $currentBatch = [];
                        }

                        $totalLines++;
                    }
                }

                // Обработка последнего чанка
                if (count($currentBatch) > 0) {
                    $batch->add(new ProcessSentenceBatchJob($currentBatch));
                }

                fclose($handle);

                DB::commit(); // Завершаем транзакцию

                return response()->json([
                    'success' => true,
                    'message' => 'Файл отправлен в очередь на обработку.',
                    'total_lines' => $totalLines,
                    'batch_id' => $batch->id,
                ], 200);
            } catch (\Exception $e) {
                // В случае ошибки откатываем транзакцию
                DB::rollBack();

                Log::error('Ошибка при загрузке файла: ' . $e->getMessage(), [
                    'stack' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка обработки файла: ' . $e->getMessage(),
                    'stack' => $e->getTraceAsString(),  // Добавляем стек ошибки для отладки
                ], 500);
            }
        } else {
            // Если файл не удалось открыть, откатываем все изменения
            Log::error('Не удалось открыть файл', ['file' => $filePath]);
            return response()->json([
                'success' => false,
                'message' => 'Не удалось открыть файл.',
            ], 500);
        }
    }



    public function getLogs()
    {
        $logPath = storage_path('logs/sentence_jobs.log');

        if (File::exists($logPath)) {
            $logs = File::get($logPath);
            return response()->json(['logs' => nl2br($logs)]);
        }

        return response()->json(['message' => 'Логи отсутствуют.']);
    }


    public function getSentence()
    {
        $sentence = null;

        // Проверяем, есть ли предложение в сеансе
        if (Session::has('current_sentence_id')) {
            $sentenceId = Session::get('current_sentence_id');
            $sentence = Sentence::find($sentenceId);

            // Проверяем, если предложение не найдено или его статус изменен, очищаем сеанс
            if (!$sentence || $sentence->status != 0 || $sentence->locked_by != Auth::id()) {
                Session::forget('current_sentence_id');
                $sentence = null;
            }
        }

        // Если предложение не найдено в сеансе, выбираем новое предложение
        if (!$sentence) {
            DB::transaction(function () use (&$sentence) {
                $sentence = Sentence::where('status', 0)
                    ->whereNot('status', 4)
                    ->whereNull('locked_by')
                    ->inRandomOrder() // Выбираем случайное предложение
                    ->first();

                if ($sentence) {
                    $sentence->update(['locked_by' => Auth::id()]);
                    Session::put('current_sentence_id', $sentence->id);
                }
            });
        }

        $sentencesTranslate = Sentence::query()->with(['translations', 'author'])
            ->where('status', 1)
            ->inRandomOrder()->paginate(15);

        $users = User::query()->where('role', 3)->get();



        if (\auth()->user()->role) {
            $completedSentences = Translate::query()
                ->where('user_id', \auth()->user()->id)
                ->whereHas('sentence', function ($query) {
                    $query->where('status', 2); // Учитываем только предложения со статусом 2
                })
                ->with('sentence') // Подгружаем связанные предложения
                ->get();

            $totalEarnings = $completedSentences->sum(function ($translation) {
                return $translation->sentence->price ?? 0; // Суммируем price из связанных предложений
            });
        }


        $deletedSentences = Translate::query()->where('deleted_at', '!=', null)->get();


        return view('translate', [
            'sentence' => $sentence,
            'sentencesTranslate' => $sentencesTranslate,
            'users' => $users,
            'completedSentences' => $completedSentences,
            'deletedSentences' => $deletedSentences,
            'totalEarnings' => $totalEarnings
        ]);
    }

    public function showUserTranslations()
    {
        $user = auth()->user();

        // Базовый запрос для переводов пользователя
        $baseQuery = Translate::where('user_id', $user->id)
            ->with(['sentence', 'user'])
            ->orderBy('created_at', 'desc');

        // Используем имена переменных, которые ожидает шаблон
        $sentencesInReview = (clone $baseQuery)
            ->whereHas('sentence', function($query) {
                $query->where('status', 1);
            })
            ->paginate(10, ['*'], 'in_review_page');

        $sentencesTranslated = (clone $baseQuery)
            ->whereHas('sentence', function($query) {
                $query->where('status', 2);
            })
            ->paginate(10, ['*'], 'translated_page');

        return view('translate-progress', [
            'sentencesInReview' => $sentencesInReview,
            'sentencesTranslated' => $sentencesTranslated,
        ]);
    }



    public function saveTranslation(Request $request)
    {


        $request->validate([
            'sentence_id' => 'required|exists:sentences,id',
            'translation' => 'required|string',
        ]);


        $sentence = Sentence::find($request->sentence_id);

        if ($sentence->status == 0 && $sentence->locked_by == Auth::id()) {
            Translate::create([
                'sentence_id' => $sentence->id,
                'user_id' => Auth::id(),
                'translation' => $request->translation,
            ]);

            $sentence->update(['status' => 1, 'locked_by' => null]);

            return redirect()->route('translate')->with('success', 'Translation saved successfully.');
        }

        return redirect()->route('translate')->with('error', 'Failed to save translation.');
    }

    public function delayTranslation(Sentence $sentence)
    {
        if ($sentence->status == 1) {
            // Повторяем логику подтверждения + delayed=1
            $sentence->update([
                'status' => 2,  // статус как при подтверждении
                'delayed' => 1  // помечаем как отложенное
            ]);

            return redirect()->back()->with('success', 'Предложение отложено');
        }

        return redirect()->back()->with('error', 'Не удалось отложить предложение');
    }

    public function editTranslation(Request $request, Translate $translation)
    {
        $request->validate([
            'translation' => 'required|string',
        ]);

        if (Auth::check()) {
            $translation->update([
                'translation' => $request->translation,
            ]);

            return redirect()->back()->with('success', 'Translation updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update translation.');
    }


    public function approveTranslation(Request $request, Sentence $sentence)
    {
        // Проверяем, что статус предложения равен 1
        if ($sentence->status == 1) {
            // Обновляем статус на 2
            $sentence->update([
                'status' => 2,
                'delayed' => 0
            ]);

            return redirect()->back()->with('success', 'Translation approved successfully.');
        }

        return redirect()->back()->with('error', 'Failed to approve translation.');
    }

    public function rejectTranslation(Request $request, Sentence $sentence)
    {
        // Проверяем, что статус предложения равен 1
        if ($sentence->status == 1) {
            // Удаляем перевод из таблицы translates
            Translate::where('sentence_id', $sentence->id)->delete();

            // Обновляем статус на 0
            $sentence->update([
                'status' => 0,
                'locked_by' => null,
                'delayed' => 0
            ]);

            return redirect()->back()->with('success', 'Translation rejected successfully.');
        }

        return redirect()->back()->with('error', 'Failed to reject translation.');
    }
    public function moderate() {

        $sentences = Sentence::query()->where('locked_by', '!=', null)->get();
        $users = User::all();

        $lockedUser = [];
        foreach ($sentences as $sentence) {
            $lockedUser = User::query()->where('id', $sentence->locked_by)->get();
        }



        return view('sentence-moderate', [
            'sentences' => $sentences,
            'users' => $users,
            'lockedUser' => $lockedUser
        ]);
    }

    public function resetTeacherSentence(Sentence $sentence) {

        $sentence->update(['locked_by'=> null]);
        return redirect()->route('home');
    }


    // Экспорт предложений с переводами

    public function exportSentences()
    {
        $job = new ExportSentencesToCsv(Auth::id());

        $batch = Bus::batch([$job])->dispatch();

        // Сохраняем связь batch_id → имя файла
        Cache::put('export_file_batch_' . $batch->id, $job->fileName, now()->addHours(2));

        return response()->json([
            'batch_id' => $batch->id,
            'file_name' => $job->fileName
        ]);
    }

    public function checkExportProgress($batchId)
    {
        $batch = Bus::findBatch($batchId);
        if (!$batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        $fileName = Cache::get('export_file_batch_' . $batchId);
        $fileExists = $fileName && file_exists(storage_path('app/public/exports/' . $fileName));

        return response()->json([
            'progress' => $batch->progress(),
            'finished' => $batch->finished(),
            'file_name' => $fileName,
            'file_exists' => $fileExists,
            'download_url' => $fileExists ? url('storage/exports/' . $fileName) : null
        ]);
    }



    public function downloadExport($filename)
    {
        if (!Storage::disk('public')->exists('exports/'.$filename)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download('exports/'.$filename, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ]);
    }

}
