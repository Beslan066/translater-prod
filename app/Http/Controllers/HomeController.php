<?php

namespace App\Http\Controllers;

use App\Models\Sentence;
use App\Models\Translate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index() {


	$sentencesTranslate = Sentence::with(['translations', 'author']) ->where('status', 1) ->orderBy('created_at', 'asc') ->paginate(30); 
	$sentencesTranslateCompleted = Sentence::query()->where('status', 2)->orderBy('created_at', 'desc')->get();

    $sentencesTranslateCompletedCount = count($sentencesTranslateCompleted);

        $users = User::query()
		->where('role', 3)
		->get();

        if(auth()->user()->role == 0) {
		return redirect()->route('login');
        }elseif(auth()->user()->role == 1) {
            return view('welcome' , compact('users', 'sentencesTranslate', 'sentencesTranslateCompletedCount'));
        }else {
            return redirect()->route('translate');
        }

 }

    public function deleteSentences(Sentence $sentence)
    {
        DB::table('sentences')->delete();
    }

    public function completedSentences(Request $request)
    {
        $query = Sentence::with(['translations.user', 'reviewer'])
            ->where('status', 2);

        // Фильтрация по дате от
        if ($request->filled('date_from')) {
            $query->where(function($q) use ($request) {
                $q->whereDate('reviewed_at', '>=', $request->date_from)
                    ->orWhereDate('created_at', '>=', $request->date_from);
            });
        }

        // Фильтрация по дате до
        if ($request->filled('date_to')) {
            $query->where(function($q) use ($request) {
                $q->whereDate('reviewed_at', '<=', $request->date_to)
                    ->orWhereDate('created_at', '<=', $request->date_to);
            });
        }

        // Фильтрация по переводчику
        if ($request->filled('translator_id')) {
            $query->whereHas('translations', function($q) use ($request) {
                $q->where('user_id', $request->translator_id);
            });
        }

        // Фильтрация по корректору
        if ($request->filled('reviewer_id')) {
            $query->where('reviewed_by', $request->reviewer_id);
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'id':
                // Сортировка по ID
                $query->orderBy('id', $sortOrder);
                break;

            case 'sentence':
                // Сортировка по тексту предложения
                $query->orderBy('sentence', $sortOrder);
                break;

            case 'date':
            default:
                // Сортировка по дате подтверждения (reviewed_at),
                // но если NULL то используем created_at
                if ($sortOrder === 'desc') {
                    // Сначала новые: сортируем NULL в конец
                    $query->orderByRaw('COALESCE(reviewed_at, created_at) DESC');
                } else {
                    // Сначала старые: сортируем NULL в начало
                    $query->orderByRaw('COALESCE(reviewed_at, created_at) ASC');
                }
                break;
        }

        $sentencesTranslateCompleted = $query->paginate(10);

        // Получаем всех переводчиков и корректоров
        $translators = User::where('role', User::ROLE_TEACHER)->get();
        $correctors = User::where('role', User::ROLE_CORRECTOR)->get();

        return view('sentences.completed', compact('sentencesTranslateCompleted', 'translators', 'correctors'));
    }

    public function returnToPending(Sentence $sentence)
    {
        // Проверяем, что предложение действительно в статусе 2
        if ($sentence->status !== 2) {
            return back()->with('error', 'Это предложение не может быть возвращено на проверку');
        }

        // Меняем статус обратно на 1
        $sentence->status = Sentence::STATUS_PENDING;
        $sentence->reviewed_by = null;
        $sentence->reviewed_at = null;
        $sentence->save();

        return back()->with('success', 'Предложение возвращено на проверку');
    }

    public function search(Request $request)
    {

        $query = $request->input('search');



        // Поиск по предложениям
        $sentences = Sentence::query()
            ->where('sentence', 'LIKE', "%{$query}%")
            ->orderBy('id', 'desc')
            ->get();

        // Поиск по переводам со статусом 1
        $sentencesTranslate = Sentence::query()
            ->where('status', 1)
            ->where('sentence', 'LIKE', "%{$query}%")
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Поиск по переводам со статусом 2
        $sentencesTranslateCompleted = Sentence::query()
            ->where('status', 2)
            ->where('sentence', 'LIKE', "%{$query}%")
            ->orderBy('id', 'desc')
            ->paginate(10);

        $translates = [];

        foreach ($sentencesTranslate as $translate) {
            $translates = Translate::query()->where('sentence_id', $translate->id)->get();
        }

        return view('sentences.search', compact('sentences', 'sentencesTranslate', 'sentencesTranslateCompleted', 'query', 'translates'));
    }


    public function districtSentences()
    {
        // Получение всех удаленных переводов, включая связанные предложения и авторов
        $sentences = Translate::withTrashed()
            ->with(['sentence', 'user']) // Загрузка предложения и автора перевода
            ->whereNotNull('deleted_at')
            ->paginate(10);

        return view('sentences.district', [
            'sentences' => $sentences,
        ]);
    }

}
