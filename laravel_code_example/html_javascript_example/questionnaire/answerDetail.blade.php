@extends('dashboard.layouts.master')
@section('title','Anket Yanıtı')
@section('style')
    <style>
        .form-control {
            color:#2f44b2 !important ;
            background-color: transparent !important;
        }
        .questionnaire_input{
            border-radius: 5px;
            border: 1px solid #e5eaff;
            width: 70%;
            padding: 0.75rem 1rem;
            color:#2f44b2 !important ;
        }
        input:checked ~ span {
            background-color: #1bc5bd;
        }
        p {
            padding: 6px;
            border: 2px solid #e5eaff;
            color:#2f44b2;

        }
    </style>
@endsection
@section('content')
    <div class="layout-px-spacing">
        <div class="layout-top-spacing mb-2">
            <div class="col-md-12">
                <div class="row">
                    <div class="container p-0">
                        <div class="row layout-top-spacing date-table-container" >
                            <!-- Datatable with export options -->
                            <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                                <div class="widget-content widget-content-area br-6 widget" >
                                    <div>
                                        <div class="row justify-content-center align-items-center" >
                                            @if($answerPackage->user_id == 0)
                                                <div class="col-md-2 col-sm-12">
                                                    <label for="">Cevaplayan</label>
                                                    <input type="text" class="form-control" disabled value="{{$answerPackage->name}} {{$answerPackage->surname}}">
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <label for="">Üyelik</label>
                                                    <input type="text" class="form-control" disabled value="Üye Değil">
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <label for="">Cevaplama Tarihi</label>
                                                    <input type="text" class="form-control" disabled value="{{\Carbon\Carbon::parse($answerPackage->created_at)->format('d.m.Y')}}">
                                                </div>
                                                <div class="col-md-6 col-sm-12">
                                                    <label for="">Anket Başlığı</label>
                                                    <p>{{$questionnaire->name}}</p>
                                                </div>
                                            @else
                                                <div class="col-md-2 col-sm-12">
                                                    <label for="">Cevaplayan</label>
                                                    <input type="text" class="form-control" disabled value="{{$answerPackage->getUser->firstname}} {{$answerPackage->getUser->lastname}}">
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <label for="">Üyelik</label>
                                                    <input type="text" class="form-control" disabled value="Üye">
                                                </div>
                                                <div class="col-md-2 col-sm-12">
                                                    <label for="">Cevaplama Tarihi</label>
                                                    <input type="text" class="form-control" disabled value="{{\Carbon\Carbon::parse($answerPackage->created_at)->format('d.m.Y')}}">
                                                </div>
                                                <div class="col-md-6 col-sm-12">
                                                    <label for="">Anket Başlığı</label>
                                                    <p>{{$questionnaire->name}}</p>
                                                </div>
                                            @endif
                                        </div>
                                        @foreach($answerPackage->getAnswers as $answer)
                                            <div style="border: 1px solid #e5eaff; padding: 20px; margin-top:20px">
                                                @if($answer->getQuestion->type == 1)
                                                    <div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <p>Soru : {{$answer->getQuestion->title}}</p>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-8 col-sm-12">
                                                                <label for="">
                                                                    Cevap :
                                                                </label>
                                                                <p>{{$answer->answer}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($answer->getQuestion->type == 2)
                                                    <div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <p>Soru : {{$answer->getQuestion->title}}</p>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            @foreach($answer->getQuestion->getOptions as $option)
                                                                <div class="col-md-6">
                                                                    <div style="display:flex;gap:10px;width:100%;margin-top:10px;align-items: center">
                                                                        <label class="radio radio-info radio-disabled">
                                                                            <input disabled type="radio" value="{{$option->id}}" name="{{$answer->id}}_radio" {{$option->id == $answer->answer ? 'checked' : ''}}>
                                                                            <span></span>
                                                                        </label>
                                                                        <p>{{$option->option_title}}</p>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @elseif($answer->getQuestion->type == 3)
                                                    <div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <p>Soru : {{$answer->getQuestion->title}}</p>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            @foreach($answer->getQuestion->getOptions as $option)
                                                                <div class="col-md-6">
                                                                    <div style="display:flex;gap:10px;width:100%;margin-top:10px;align-items: center">
                                                                        <label class="radio check-info radio-disabled">
                                                                            <input disabled type="checkbox"  value="{{$option->id}}" name="{{$answer->id}}_checbox" {{in_array($option->id, explode('/-/', $answer->answer))  ? 'checked' : ''}}>
                                                                            <span></span>
                                                                        </label>
                                                                        <p>{{$option->option_title}}</p>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')

@endsection

