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


	$sentencesTranslate = Sentence::with(['translations', 'author']) ->where('status', 1) ->orderBy('id', 'desc') ->paginate(30); 
	$sentencesTranslateCompleted = Sentence::query()->where('status', 2)->orderBy('id', 'desc')->get();

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

    public function completedSentences()
    {

        $sentencesTranslate = Sentence::with(['translations', 'author'])->where('status', 1)->orderBy('id', 'desc')->paginate(10);
        $sentencesTranslateCompleted = Sentence::query()->where('status', 2)->orderBy('id', 'desc')->paginate(10);

        $users = User::query()->where('role', 3)->get();



        return view('sentences.completed', compact('sentencesTranslateCompleted', 'users'));
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
            ->get();

        return view('sentences.district', [
            'sentences' => $sentences,
        ]);
    }

}
