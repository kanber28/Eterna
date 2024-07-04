@extends('dashboard.layouts.master')
@section('title','Asılsızlığı Bildirimi')
@section('content')
    <style>
        #image_path_url {
            flex-wrap: wrap;
            width: 100%;
        }

        .image-area {
            width: 100%;
            display: flex;
        }

        .form-group {
            margin-left: auto;
            margin-right: auto;
        }

        .img_class {
            width: 100%;
            height: 140px;
            object-fit: contain;
            padding: 5px;
        }

        .image-content {
            display: inline-block;
            width: 215px;
            height: 140px;
            max-width: 100%;
            border: 1px solid #d4d4d4;
            border-radius: 10px;
            margin-bottom: 10px;
            margin-right: 10px;
            position: relative;
        }
        .centeredAlign {
            display: flex;
            align-content: center;
            align-items: center;
            height: 100%;
            padding: 5px;
        }

        label {
            display: block;
            padding: 0;
            margin: 0;
        }
    </style>

    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow mb-4">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <div class="makeitSticky">
                            <h4>Asılsızlığı Bildirimi</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="widget-content widget-content-area">
                <div class="w-100">
                    <div class="mt-2">
                        @foreach($errors->all() as $error)
                            <div class="alert alert-danger mb-2" style="background-color: #677ada;border-radius: 5px;border-color: #677ada; color: white;">{{$error}}</div>
                        @endforeach
                        <form method="POST" id="fakeMissionForm" name="fakeMissionForm" action="{{route('report.missionsFake')}}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{$mission->id}}">
                            <div class="form-group row centeredAlign">
                                <label class="col-12 col-md-3"><span style="color: red">* </span>Asılsız Olma Sebebi</label>
                                <div class="col-9">
                                    <input name="why_mission_is_fake" id="why_mission_is_fake" class="form-control form-control-solid" type="text"
                                           value="" >
                                </div>
                            </div>
                            <div class="form-group row centeredAlign">
                                <label class="col-12 col-md-3"><span style="color: red">* </span>Fotoğrafı Yükleyiniz</label>

                                <div class="col-md-9">
                                    <div class="custom-file">
                                        <div id="image_path_url"></div>
                                        <label for="file_upload" class="col-12 col-md-3">
                                            <a title="Attach a file" class="mr-2 pointer text-primary">
                                                <i class="las la-paperclip font-20"></i>
                                                <span class="font-17">Fotoğraf Seçiniz</span>
                                            </a>
                                        </label>
                                        <input id="file_upload" name='image_path_url[]' accept="image/*" onchange="readMultiURL(this)" type="file" class="custom-file-input" style="display:none;" multiple>
                                    </div>
                                </div>

                                <div class="col-md-9 offset-md-3 mt-2">
                                    <img src="" id="image" class="img" style="height: 150px; border-radius: 7px; display: none">
                                </div>
                            </div>
                            <div class="widget-footer text-right" style="padding-left: 0px!important;">
                                <span style="color: red;float: left!important;">(*) Zorunlu alanları ifade etmektedir.</span>
                                <input type="submit" class="btn btn-primary" value="Kaydet"/>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script type='text/javascript'
            src="https://rawgit.com/RobinHerbots/jquery.inputmask/3.x/dist/jquery.inputmask.bundle.js"></script>
    <script>
        $(function(){
            $("#fakeMissionForm").submit(function(e){
                var $fileUpload = $("input[type='file']");
                if (parseInt($fileUpload.get(0).files.length) > 3){
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata',
                        text: 'Maksimum 3 fotoğraf seçebilirsiniz',
                    });
                    e.preventDefault();
                }
            });
        });

        $("#fakeMissionForm").on('submit', function(e) {
            Swal.fire({
                icon: 'success',
                title: 'Başarılı',
                text: 'Başarıyla gönderildi',
                confirmButtonText: 'Tamam'
            });
        });


        function readMultiURL(input) {
            var url = input.value;
            var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
            $('#file_info').css('display', 'block');
            $imageArea = document.getElementById('image_path_url');
            $imageArea.innerHTML = "";
            for (i = 0; i < input.files.length; i++) {
                if (input.files && input.files[i] && (ext == "png" || ext == "jpeg" || ext == "jpg")) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $imageArea.innerHTML += '  <div class="image-content">' +
                            '<img class="img_class" style="border-radius: 7px;" ' +
                            'onclick="zoomImage(this)"' +
                            'src="' + e.target.result + '"' +
                            'alt="Resim Yok"> </div>';
                    }
                    reader.readAsDataURL(input.files[i]);
                } else {
                    console.log("Not found image");
                }
            }

        }


    </script>
@endsection

