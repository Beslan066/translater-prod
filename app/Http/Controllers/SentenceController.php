<?php

namespace App\Http\Controllers;

use App\Models\Sentence;
use App\Models\Translate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Jobs\ProcessSentenceBatchJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;



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
                DB::beginTransaction();

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
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Файл отправлен в очередь на обработку.',
                    'total_lines' => $totalLines,
                    'batch_id' => $batch->id,
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка обработки файла: ' . $e->getMessage(),
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось открыть файл.',
            ], 500);
        }
    }



    public function progress()
    {
        $total = Cache::get('total_sentences', 0);
        $processed = Cache::get('processed_sentences', 0);

        $progress = $total > 0 ? round(($processed / $total) * 100) : 0;

        return response()->json(['progress' => $progress]);
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
                    ->whereNull('locked_by')
                    ->inRandomOrder() // Выбираем случайное предложение
                    ->first();

                if ($sentence) {
                    $sentence->update(['locked_by' => Auth::id()]);
                    Session::put('current_sentence_id', $sentence->id);
                }
            });
        }

        $sentencesTranslate = Sentence::query()->with(['translations', 'author'])->where('status', 1)->orderBy('id', 'desc')->paginate(10);
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

    public function editTranslation(Request $request, Translate $translation)
    {
        $request->validate([
            'translation' => 'required|string',
        ]);

        if (Auth::check()) {
            $translation->update([
                'translation' => $request->translation,
            ]);

            return redirect()->route('translate')->with('success', 'Translation updated successfully.');
        }

        return redirect()->route('translate')->with('error', 'Failed to update translation.');
    }


    public function approveTranslation(Request $request, Sentence $sentence)
    {
        // Проверяем, что статус предложения равен 1
        if ($sentence->status == 1) {
            // Обновляем статус на 2
            $sentence->update(['status' => 2]);

            return redirect()->route('translate')->with('success', 'Translation approved successfully.');
        }

        return redirect()->route('translate')->with('error', 'Failed to approve translation.');
    }

    public function rejectTranslation(Request $request, Sentence $sentence)
    {
        // Проверяем, что статус предложения равен 1
        if ($sentence->status == 1) {
            // Удаляем перевод из таблицы translates
            Translate::where('sentence_id', $sentence->id)->delete();

            // Обновляем статус на 0
            $sentence->update(['status' => 0, 'locked_by' => null]);

            return redirect()->route('translate')->with('success', 'Translation rejected successfully.');
        }

        return redirect()->route('translate')->with('error', 'Failed to reject translation.');
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

}
