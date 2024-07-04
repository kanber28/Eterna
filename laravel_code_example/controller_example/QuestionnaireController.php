<?php


use App\Http\Controllers\Controller;
use App\Models\AnswerPackage;
use App\Models\Option;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class QuestionnaireController extends Controller
{
    function create(){
        return \App\Http\Controllers\Dashboard\view('dashboard.pages.questionnaire.create');
    }

    function saveQuestionnaire(Request $request){
        $questionnaire = new Questionnaire();
        $questionnaire->name = $request->questionnaireName;
        $questionnaire->description = $request->questionnaireDescription;
        $questionnaire->is_active = 0;
        $questionnaire->questionnaire_open_status = $request->questionnaireStatus;
        $questionnaire->save();

        try {
            foreach ($request->questionArr as $data){
                $question = new Question();
                $question->questionnaire_id = $questionnaire->id;
                $question->type = $data["type"];
                $question->title = $data["title"];
                $question->is_necessary = $data["is_necessary"];
                $question->save();

                if ($data["type"] == 2 || $data["type"] == 3){
                    foreach ($data["options"] as $item){
                        $option = new Option();
                        $option->question_id = $question->id;
                        $option->option_title = $item;
                        $option->option_content = "-";
                        $option->save();
                    }
                }
                else {
                    $option = new Option();
                    $option->question_id = $question->id;
                    $option->option_title = "-";
                    $option->option_content = "-";
                    $option->save();
                }

            }
        }
        catch (\Exception $e){
            $questionnaire->delete();
            \App\Http\Controllers\Dashboard\abort(500);
        }


        return \App\Http\Controllers\Dashboard\response()->json(['status' => 1, 'message' => 'success']);
    }

    function questionnaireList(){
        return \App\Http\Controllers\Dashboard\view('dashboard.pages.questionnaire.list');
    }

    function fetchQuestionnaire(){
        $questionnaires = Questionnaire::query();

        return DataTables::of($questionnaires)
            ->editColumn('is_active', function ($questionnaire) {
                if ($questionnaire->is_active == 1) {
                    return '<div class="switch__container">
                                <input onchange="updateActive(' . $questionnaire->id . ',this)" checked id="switch-shadow' . $questionnaire->id . '" class="switch switch--shadow" type="checkbox">
                                <label for="switch-shadow' . $questionnaire->id . '"></label>
                            </div>';
                }
                else {
                    return '<div class="switch__container">
                                <input onchange="updateActive(' . $questionnaire->id . ', this)" id="switch-shadow' . $questionnaire->id . '" class="switch switch--shadow" type="checkbox">
                                <label for="switch-shadow' . $questionnaire->id . '"></label>
                            </div>';
                }
            })
            ->editColumn('description', function ($questionnaire){
                if (strlen($questionnaire->description) > 100){
                    return substr($questionnaire->description, 0, 100) . '...';
                }
                else {
                    return $questionnaire->description;
                }

            })
            ->addColumn('update', function ($questionnaire){
                if (Auth::user()->can('update questionnaire')){
                    return '<a href="'. \App\Http\Controllers\Dashboard\route('questionnaire.updateIndex', $questionnaire->id) .'" class="btn btn-warning">Güncelle</a>';
                }
                else {
                    return '';
                }
            })
            ->addColumn('responses', function ($questionnaire){
                if (Auth::user()->can('read questionnaire')){
                    return '<a href="'. \App\Http\Controllers\Dashboard\route('questionnaire.answers', $questionnaire->id) .'" class="btn btn-info">Cevaplar</a>';
                }
                else {
                    return '';
                }
            })
            ->rawColumns(['is_active', 'update', 'responses'])
            ->make();
    }

    function changeStatus(Request $request){
        $questionnaire = Questionnaire::find($request->id);
        $questionnaire->is_active = $request->status;
        $questionnaire->save();

        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }

    function updateIndex($id){
        $questionnaire = Questionnaire::find($id);

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.questionnaire.update', compact('questionnaire', 'id'));
    }

    function update(Request $request){
        $questionnaire = Questionnaire::find($request->questionnaireID);
        $questionnaire->name = $request->questionnaireName;
        $questionnaire->description = $request->questionnaireDescription;
        $questionnaire->questionnaire_open_status = $request->questionnaireStatus;
        $questionnaire->save();

        foreach ($request->questionArr as $data){
            if ($data["question_id"] != 0){
                $question = Question::find($data["question_id"]);
                $question->is_necessary = $data["is_necessary"];
                $question->title = $data["title"];
                $question->save();
            }
            else {
                $question = new Question();
                $question->is_necessary = $data["is_necessary"];
                $question->title = $data["title"];
                $question->type = $data["type"];
                $question->questionnaire_id = $questionnaire->id;
                $question->save();
                if($data["type"] != 1){
                    foreach ($data["options"] as $item){
                        $option = new Option();
                        $option->question_id = $question->id;
                        $option->option_title = $item["title"];
                        $option->option_content = "-";
                        $option->save();
                    }
                }
                else {
                    $option = new Option();
                    $option->question_id = $question->id;
                    $option->option_title = "-";
                    $option->option_content = "-";
                    $option->save();
                }
            }

        }
    }

    function answers($id){
        return \App\Http\Controllers\Dashboard\view('dashboard.pages.questionnaire.answerList', compact('id'));
    }

    function fetchAnswers($id){
        $answerPackages = AnswerPackage::query()->where('questionnaire_id', $id);

        return DataTables::of($answerPackages)
            ->addColumn('nameSurname', function ($answerPackage){
                if ($answerPackage->user_id == 0){
                    return $answerPackage->name . ' ' . $answerPackage->surname;
                }
                else {
                    $user = User::withTrashed()->where('id', $answerPackage->user_id)->first();
                    return $user->firstname . ' ' . $user->lastname;
                }
            })
            ->addColumn('questionnaireName', function ($answerPackage){
                $questionnaire = Questionnaire::where('id', $answerPackage->questionnaire_id)->first();

                return $questionnaire->name;
            })
            ->addColumn('memberStatus', function ($answerPackage){
                if ($answerPackage->user_id == 0){
                    return 'Üye Değil';
                }
                else {
                    return 'Üye';
                }
            })
            ->addColumn('detail', function ($answerPackage){
                return '<a href="'. \App\Http\Controllers\Dashboard\route('questionnaire.answersDetail', $answerPackage->id) .'" class="btn btn-primary">Detay</a>';
            })
            ->rawColumns(['detail'])
            ->make();
    }

    function answersDetail($id){
        $answerPackage = AnswerPackage::find($id);
        $questionnaire = Questionnaire::find($answerPackage->questionnaire_id);

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.questionnaire.answerDetail', compact('answerPackage', 'questionnaire'));
    }

    function removeQuestion(Request $request){
        $question = Question::find($request->question_id);
        $question->is_active = 0;
        $question->save();

        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }
}
