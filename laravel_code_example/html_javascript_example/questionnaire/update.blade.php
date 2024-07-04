@extends('dashboard.layouts.master')
@section('title','Anket Güncelle')
@section('style')
    <style>
        select {
            height: max-content;
        }
        .questionnaire_input{
            border-radius: 5px;
            border: 1px solid #e5eaff;
            width: 70%;
            padding: 0.75rem 1rem;
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
                                    <div id="questionArea" >
                                        <div class="row" style="justify-content: center">
                                            <div class="col-12" style="display: flex;justify-content: center;flex-direction: column;align-content: center;align-items: center">
                                                <label for="">Anket İsmi:</label>
                                                <input type="text" class="form-control" value="{{$questionnaire->name}}" style="width: 50%" id="questionnaire_name">
                                            </div>
                                            <div class="col-12 mt-2" style="display: flex;justify-content: center;flex-direction: column;align-content: center;align-items: center">
                                                <label for="">Anket Açıklaması:</label>
                                                <textarea onkeyup="characterCounter()" name="" id="questionnaire_description" cols="30" rows="4" class="form-control" style="padding: 5px !important;width: 50% !important;" maxlength="255" >{{$questionnaire->description}}</textarea>
                                                <div style="display: flex;justify-content: center;align-items: center;width: 100%">
                                                    <div>Kalan Karakter: <span id="counterArea">255</span></div>
                                                </div>
                                            </div>
                                            <div class="col-12 mt-3" style="display: flex;justify-content: center;flex-direction: column;align-content: center;align-items: center">
                                                <label for="">Anket Durumu:</label>
                                                <select  class="form-control" name="" style="width: 25%" id="questionnaire_status">
                                                    <option value="0" {{$questionnaire->questionnaire_open_status == 0 ? 'selected' : ''}} >Herkese Açık</option>
                                                    <option value="1" {{$questionnaire->questionnaire_open_status == 1 ? 'selected' : ''}} >Üyelere Açık</option>
                                                </select>
                                            </div>
                                        </div>
                                        @foreach($questionnaire->getQuestions as $key => $question)
                                            <input type="hidden" id="question_{{$key+1}}_id" value="{{$question->id}}">
                                            <div style="border: 1px solid #e5eaff; padding: 20px; margin-top:20px; {{$question->is_active == 0 ? 'display:none' : ''}}" id="">
                                                <div class="" style="display: flex;width: 100%; justify-content: end;align-items: center">
                                                    <button class="btn btn-danger" onclick="deleteQuestion({{$question->id}}, 'question_area_{{$key+1}}')" >Sil</button>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3 col-sm-12">
                                                        <label for="question_{{$key+1}}" >Soru Tipi</label>
                                                        <select class="form-control" disabled name="question_type_{{$key+1}}" id="question_type_{{$key+1}}" questionOrder="{{$key+1}}" onchange="questionTypeSelection(this)">
                                                            <option value="" disabled selected>Seçiniz</option>
                                                            <option {{$question->type == 1 ? 'selected' : ''}} value="1">Yazı</option>
                                                            <option {{$question->type == 2 ? 'selected' : ''}} value="2">Çoktan Seçmeli</option>
                                                            <option {{$question->type == 3 ? 'selected' : ''}} value="3">Onay Kutuları</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-7 col-sm-12">
                                                        <label for="question_title">Soru Başlığı</label>
                                                        <input id="question_title_{{$key+1}}" value="{{$question->title}}" type="text" class="form-control">
                                                    </div>
                                                    <div class="col-md-2 col-sm-12">
                                                        <label for="question_{{$key+1}}">Zorunluluk</label>
                                                        <select class="form-control" id="question_is_necessary_{{$key+1}}">
                                                            <option {{$question->is_necessary == 1 ? 'selected' : ''}} value="1">Zorunlu</option>
                                                            <option {{$question->is_necessary == 0 ? 'selected' : ''}} value="0">Zorunlu Değil</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div id="question_option_area_{{$key+1}}">
                                                    @if($question->type == 1)
                                                        @foreach($question->getOptions as $option)
                                                            <div class="row mt-3">
                                                                <div class="col-md-6 col-sm-12">
                                                                    <input disabled type="text" placeholder="Cevap" id="option_{{$key+1}}_1" class="form-control">
                                                                    <input type="hidden" value="{{$option->id}}" id="questionnaire_input_{{$key+1}}_1_id">
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @elseif($question->type == 2)
                                                            <div class="row mt-3" id="optionarea_{{$key+1}}">
                                                                <input type="hidden" id="option_count_{{$key+1}}" value="{{$question->getOptions->count()}}" />
                                                                <div class="col-12" style="display: flex;justify-content: end;gap:7px">
{{--                                                                    <button class="btn btn-primary" onclick="addSelectOption('{{$key+1}}')">Şık Ekle</button>--}}
{{--                                                                    <button class="btn btn-danger" onclick="removeSelectOption('{{$key+1}}')">Şık Çıkar</button>--}}
                                                                </div>
                                                                @foreach($question->getOptions as $key1 => $option)
                                                                    <div class="col-md-6 col-sm-12 mt-3" id="option_area_{{$key+1}}_{{$key1+1}}">
                                                                        <div style="display:flex;gap:10px;width:100%">
                                                                            <input type="radio" class="" disabled>
                                                                            <input type="text" disabled value="{{$option->option_title}}" placeholder="Seçenek {{$key1+1}}" class="questionnaire_input"  id="questionnaire_input_{{$key1+1}}_{{$key+1}}">
                                                                            <input type="hidden" value="{{$option->id}}" id="questionnaire_input_{{$key1+1}}_{{$key+1}}_id">
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                    @elseif($question->type == 3)
                                                        <div class="row mt-3" id="optionarea_{{$key+1}}" >
                                                            <input type="hidden" id="option_count_{{$key+1}}" value="{{$question->getOptions->count()}}" />
                                                            <div class="col-12" style="display: flex;justify-content: end;gap: 7px;">
{{--                                                                <button class="btn btn-primary" onclick="addMultiOption('{{$key+1}}')" > Seçenek Ekle</button>--}}
{{--                                                                <button class="btn btn-danger" onclick="removeMultiOption('{{$key+1}}')" > Seçenek Çıkar</button>--}}
                                                                </div>
                                                            @foreach($question->getOptions as $key1 => $option)
                                                                <div class="col-md-6 col-sm-12 mt-3" id="multi_option_area_{{$key+1}}_{{$key1+1}}">
                                                                    <div style="display:flex;gap:10px;width:100%">
                                                                        <input type="checkbox" class="" disabled>
                                                                        <input type="text" disabled placeholder="Seçenek {{$key1+1}}" value="{{$option->option_title}}" class="questionnaire_input" id="questionnaire_input_{{$key1+1}}_{{$key+1}}">
                                                                        <input type="hidden" value="{{$option->id}}"  id="questionnaire_input_{{$key1+1}}_{{$key+1}}_id">
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>
                                    <div style="margin-top: 50px;display: flex;justify-content: space-between">
                                        <div>
                                            <button onclick="addQuestion()" class="btn btn-primary">Soru Ekle</button>
                                            <button onclick="removeQuestion()" class="btn btn-danger">Soru Çıkart</button>
                                        </div>

                                        <button class="btn btn-info" onclick="saveQuestionnaire()">Anketi Kaydet</button>
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
    <script>

        function deleteQuestion(questionID, elementID){
            Swal.fire({
                icon:'warning',
                title:'Emin Misiniz ?',
                text:'Bu Soruyu Silmek İstediğinize Emin Misiniz ?',
                showConfirmButton:true,
                confirmButtonText:'Sil',
                confirmButtonColor:'red',
                showCancelButton:true,
                cancelButtonText:'İptal',
            }).then((res)=>{
                if(res.isConfirmed){
                    $.ajax({
                        url:'{{route('questionnaire.removeQuestion')}}',
                        type:'POST',
                        data:{
                            question_id:questionID,
                            _token:'{{csrf_token()}}',
                        },
                        success:()=>{
                            document.getElementById(elementID).style.display = 'none'
                        }
                    })
                }
            })
        }

        function characterCounter(){
            let e = document.getElementById('questionnaire_description')
            let character = e.value;
            let count = character.length
            let remainder = 255 - count

            document.getElementById('counterArea').innerText = remainder;
        }
        characterCounter()

        let questionCount = {{$questionnaire->getQuestions->count()}};

        function addQuestion(){
            questionCount++;
            let questionArea = $('#questionArea')

            let content =
                '<input type="hidden" id="question_'+ questionCount +'_id" value="0">'+
                '<div style="border: 1px solid #e5eaff; padding: 20px; margin-top:20px" id="question_area_'+ questionCount +'">'+
                '<div class="row">'+
                '<div class="col-md-3 col-sm-12">'+
                '<label for="question_'+questionCount+'">Soru Tipi</label>'+
                '<select class="form-control" name="question_type_'+questionCount+'" id="question_type_'+questionCount+'" questionOrder="'+questionCount+'" onchange="questionTypeSelection(this)">'+
                '<option value="" disabled selected>Seçiniz</option>'+
                '<option value="1">Yazı</option>'+
                '<option value="2">Çoktan Seçmeli</option>'+
                '<option value="3">Onay Kutuları</option>'+
                '</select>'+
                '</div>'+
                '<div class="col-md-7 col-sm-12">'+
                '<label for="question_title_'+questionCount+'">Soru Başlığı</label>'+
                '<input type="text" class="form-control" id="question_title_'+questionCount+'">'+
                '</div>'+
                '<div class="col-md-2 col-sm-12">'+
                '<label for="question_1">Zorunluluk</label>'+
                '<select class="form-control" id="question_is_necessary_'+questionCount+'">'+
                '<option value="1">Zorunlu</option>'+
                '<option value="0">Zorunlu Değil</option>'+
                '</select>'+
                '</div>'+
                '</div>'+
                '<div id="question_option_area_'+questionCount+'">'+

                '</div>'+
                '</div>';

            questionArea.append(content)
        }
        function removeQuestion(){
            if (questionCount > 1){
                let question = document.getElementById("question_area_"+ questionCount);
                question.remove()
                questionCount--;
            }
        }

        function addSelectOption(order){
            let optionValueId = 'option_count_' + order
            let optionValue = document.getElementById(optionValueId).value;
            optionValue++
            document.getElementById(optionValueId).value = optionValue
            let id = '#optionarea_' + order;
            let content =
                '<div class="col-md-6 col-sm-12 mt-2" id="option_area_'+order+'_'+optionValue+'">'+
                '<div style="display:flex;gap:10px;width:100%;margin-top:10px">'+
                '<input type="radio" class="" disabled>'+
                '<input type="text" placeholder="Seçenek '+ optionValue +'" class="questionnaire_input" id="questionnaire_input_'+ optionValue +'_'+ order +'" >'+
                '<input type="hidden" value="0" id="questionnaire_input_'+optionValue+'_'+ order +'_id">'+
                '</div>'+
                '</div>'

            $(id).append(content)

        }

        function questionTypeSelection(e){
            let order = e.getAttribute('questionOrder')
            let id = '#question_option_area_' + order
            let content = ''
            if (e.value == 1){
                content =
                    '<div class="row mt-3">'+
                    '<div class="col-md-6 col-sm-12">'+
                    '<input disabled type="text" placeholder="Cevap" id="option_'+order+'_1" class="form-control">'+
                    '<input type="hidden" value="0" id="questionnaire_input_'+ order +'_1_id">'+
                    '</div>'+
                    '</div>';
            }
            else if(e.value == 2){
                content =
                    '<div class="row mt-3" id="optionarea_'+order+'">'+
                    '<input type="hidden" id="option_count_'+order+'" value="1" />'+
                    '<div class="col-12" style="display: flex;justify-content: end;gap:7px">'+
                    '<button class="btn btn-primary" onclick="addSelectOption('+order+')">Şık Ekle</button>'+
                    '<button class="btn btn-danger" onclick="removeSelectOption('+order+')">Şık Çıkar</button>'+
                    '</div>'+
                    '<div class="col-md-6 col-sm-12 mt-3" >'+
                    '<div style="display:flex;gap:10px;width:100%">'+
                    '<input type="radio" class="" disabled>'+
                    '<input type="text" placeholder="Seçenek 1" class="questionnaire_input"  id="questionnaire_input_1_'+order+'">'+
                    '<input type="hidden" value="0" id="questionnaire_input_1_'+ order +'_id">'+
                    '</div>'+
                    '</div>'+
                    '</div>';

            }
            else if (e.value == 3){
                content =
                    '<div class="row mt-3" id="optionarea_'+order+'" >'+
                    '<input type="hidden" id="option_count_'+order+'" value="1" />'+
                    '<div class="col-12" style="display: flex;justify-content: end;gap: 7px;">'+
                    '<button class="btn btn-primary" onclick="addMultiOption('+order+')" > Seçenek Ekle</button>'+
                    '<button class="btn btn-danger" onclick="removeMultiOption('+order+')" > Seçenek Çıkar</button>'+
                    '</div>'+
                    '<div class="col-md-6 col-sm-12 mt-3">'+
                    '<div style="display:flex;gap:10px;width:100%">'+
                    '<input type="checkbox" class="" disabled>'+
                    '<input type="text" placeholder="Seçenek 1" class="questionnaire_input" id="questionnaire_input_1_'+order+'">'+
                    '<input type="hidden" value="0" id="questionnaire_input_1_'+ order +'_id">'+
                    '</div>'+
                    '</div>'+
                    '</div>';
            }

            let area = $(id)
            area.html('')
            area.append(content)
        }

        function removeSelectOption(order){
            let optionValueId = 'option_count_' + order;
            let optionValue = document.getElementById(optionValueId).value;
            document.getElementById(optionValueId).value = optionValue;

            if(optionValue > 1){
                let optionId = "option_area_" + order + "_" + optionValue;
                let option = document.getElementById(optionId);

                option.remove();
                optionValue--;
                document.getElementById(optionValueId).value = optionValue;
            }
        }

        function addMultiOption(order){
            let optionValueId = 'option_count_' + order
            let optionValue = document.getElementById(optionValueId).value;
            optionValue++
            document.getElementById(optionValueId).value = optionValue
            let id = '#optionarea_' + order;
            let content =
                '<div class="col-md-6 col-sm-12 mt-3" id="multi_option_area_'+order+'_'+optionValue+'">'+
                    '<div style="display:flex;gap:10px;width:100%">'+
                        '<input type="checkbox" class="" disabled>'+
                        '<input type="text" placeholder="Seçenek '+optionValue+'" class="questionnaire_input" id="questionnaire_input_'+ optionValue +'_'+ order +'" >'+
                        '<input type="hidden" value="0" id="questionnaire_input_'+optionValue+'_'+ order +'_id">'+
                    '</div>'+
                '</div>'

            $(id).append(content)
        }

        function removeMultiOption(order){
            let optionValueId = 'option_count_' + order
            let optionValue = document.getElementById(optionValueId).value;

            if(optionValue > 1){
                let optionId = "multi_option_area_" + order + "_" + optionValue;
                let option = document.getElementById(optionId);

                option.remove();
                optionValue--;
                document.getElementById(optionValueId).value = optionValue;
            }
        }

        function saveQuestionnaire(){
            let typeId = ''
            let questionTitleId = ''
            let questionTitle = ""
            let type = 0
            let optionCount = 0;
            let questionArr = []
            let tmpObj = {}
            let optionId = ''
            let tmpOptionTitle = ""
            let tmpOptionArr = []
            let isNecessary = ''
            let isNecessaryId = ''
            let tmpOptionIdId = ""
            let tmpOptionId = ""
            let questionId = "";
            for (let i = 1; i <= questionCount; i++){
                questionId = document.getElementById('question_'+i+'_id').value;
                typeId = "question_type_" + i;
                questionTitleId = "question_title_" + i
                isNecessaryId = 'question_is_necessary_' + i
                type = document.getElementById(typeId).value
                if(type == 0){
                    Swal.fire({
                        'icon':'warning',
                        'title':'Bütün Soru Tipi Alanlarını Doldurunuz'
                    })
                    return false;
                }
                questionTitle = document.getElementById(questionTitleId).value
                isNecessary = document.getElementById(isNecessaryId).value
                if (questionTitle.trim() == ''){
                    Swal.fire({
                        'icon':'warning',
                        'title':'Bütün Soru Başlığı Alanlarını Doldurunuz'
                    })
                    return false;
                }
                tmpOptionArr = []
                if (type == 2 || type == 3){
                    optionCount = document.getElementById('option_count_' + i).value;
                    for (let j = 1; j <= optionCount; j++ ){
                        optionId = 'questionnaire_input_' + j + '_' + i;
                        tmpOptionTitle = document.getElementById(optionId).value;
                        tmpOptionIdId = 'questionnaire_input_' + j + '_' + i + "_id";
                        tmpOptionId = document.getElementById(tmpOptionIdId).value;
                        if(tmpOptionTitle.trim() == ''){
                            Swal.fire({
                                'icon':'warning',
                                'title':'Bütün Seçenek Alanlarını Doldurunuz'
                            })
                            return false;
                        }
                        tmpOptionArr.push({'title':tmpOptionTitle, 'id':tmpOptionId})
                    }

                }
                else {
                    tmpOptionIdId = 'questionnaire_input_'+ i +'_1_id';
                    tmpOptionId = document.getElementById(tmpOptionIdId).value
                    tmpOptionArr = [{"title": "yazı", "id":tmpOptionId}]
                }

                tmpObj = {
                    "question_id":questionId,
                    "type":type,
                    "title":questionTitle,
                    "is_necessary":isNecessary,
                    'options': tmpOptionArr,
                }
                questionArr.push(tmpObj)
            }

            let questionnaireName = document.getElementById('questionnaire_name').value
            if (questionnaireName.trim() == ''){
                Swal.fire({
                    'icon':'warning',
                    'title':'Anket İsmi Boş Bırakılamaz'
                })
                return false;
            }

            let questionnaireDescription = document.getElementById('questionnaire_description').value;
            if(questionnaireDescription.trim() == ''){
                Swal.fire({
                    'icon':'warning',
                    'title':'Anket Açıklaması Boş Bırakılamaz'
                })
                return false;
            }

            let questionnaireStatus = document.getElementById('questionnaire_status').value;

            $.ajax({
                url:'{{route('questionnaire.update')}}',
                method:'POST',
                data:{
                    questionnaireID:'{{$id}}',
                    questionnaireName:questionnaireName,
                    questionnaireDescription:questionnaireDescription,
                    questionnaireStatus:questionnaireStatus,
                    questionArr:questionArr,
                    _token:'{{csrf_token()}}'
                },
                success:(res)=>{
                    Swal.fire({
                        icon:'success',
                        title:'Başarılı'
                    }).then((res)=>{
                        window.location = '{{route('questionnaire.list')}}'
                    })
                }
            })
        }


    </script>
@endsection
