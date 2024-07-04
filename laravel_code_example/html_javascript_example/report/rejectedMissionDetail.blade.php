@extends('dashboard.layouts.master')
@section('title','Çek Gönder')
@section('content')
    <style>
        .mySlides {display: none}
        .mySlides2 {display: none}
        img {vertical-align: middle;}

        /* Slideshow container */
        .slideshow-container {
            max-width: 1000px;
            position: relative;
            margin: auto;
        }


        * {
            font-size: 15px;
        }

        /* Next & previous buttons */
        .prev, .next {
            cursor: pointer;
            position: absolute;
            top: 50%;
            width: auto;
            padding: 16px;
            margin-top: -22px;
            color: white;
            font-weight: bold;
            font-size: 18px;
            transition: 0.6s ease;
            border-radius: 0 3px 3px 0;
            user-select: none;
        }

        /* Position the "next button" to the right */
        .next {
            right: 0;
            border-radius: 3px 0 0 3px;
        }

        /* On hover, add a black background color with a little bit see-through */
        .prev:hover, .next:hover {
            background-color: rgba(0,0,0,0.8);
            color: white;
        }

        .prev , .next {
            background-color: silver;
            display: block;
        }

    </style>
    <style>
        .multi-select-area{
            border:1px solid #f0f0f0;
            padding: 20px 20px 20px 20px;
            border-radius: 5px;
            box-shadow: 5px 5px  5px 5px #f0f0f0;
        }
        .multi-select-area label {
            color:#6b6d78;
        }

        @media screen and (max-width: 600px) {
            .multi-select-area label {
                margin-top: 20px;
            }
        }

        .box-border{
            border: 1px solid #d4d4d4
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css"
          integrity="sha512-hoalWLoI8r4UszCkZ5kL8vayOGVae1oxXe/2A4AO6J9+580uKHDO3JdHb7NzwwzK5xr/Fs0W40kiNHxM9vyTtQ=="
          crossorigin=""/>
    <div class="layout-px-spacing">
        <div class="layout-top-spacing mb-2">
            <div class="col-md-12">
                <div class="row">
                    <div class="container p-0">
                        <div class="row layout-top-spacing date-table-container">
                            <!-- Datatable with export options -->
                            <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                                <div class="widget-content widget-content-area br-6">
                                    <!-- modal -->
                                    <div class="modal fade" id="againAssignModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title" id="exampleModalLabel">
                                                        Yeniden Ata
                                                    </h6>
                                                    <button onclick="closeModal()" type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <label for="">Personel Seçin</label>
                                                            <select style="height: min-content" class="form-control" name="assignedUserId" id="assignedUserId">
                                                                <option disabled selected>Lütfen Seçiniz</option>
                                                                @foreach($employee as $item)
                                                                    <option value="{{$item->id}}">{{$item->firstname . ' ' . $item->lastname}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12 mt-3" style="display: flex;justify-content: center">
                                                            <button class="btn btn-info" onclick="assignToUser()">Ata</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button onclick="closeModal()" type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div style="height: 300px" id="map"></div>
                                        </div>
                                        <div class="row" style="width: 100%">
                                            <div class="col-6">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="mt-3 box-border shadow" style="width: 100%;max-height: 200px;border-radius: 5px;overflow: auto">
                                                        <div style="padding: 10px 10px 10px 10px">
                                                            <h6 class="card-title text-primary" style="width: fit-content">Şikayet Açıklaması</h6>
                                                            <p style="min-width: 200px" class="card-text text-primary">{{$report->description}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;overflow: auto">
                                                        <div style="padding: 10px 10px 10px 10px">
                                                            <h6 class="card-title text-primary" style="width: fit-content">Şikayet Mahallesi</h6>
                                                            <p style="min-width: 200px" class="card-text text-primary">{{$report->getNeighbourhood->name}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;overflow: auto">
                                                        <div style="padding: 10px 10px 10px 10px">
                                                            <h6 class="card-title text-primary" style="width: fit-content">Şikayet Adresi</h6>
                                                            <p style="min-width: 200px" class="card-text text-primary">{{$report->address}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;overflow: auto">
                                                        <div style="padding: 10px 10px 10px 10px">
                                                            <h6 class="card-title text-primary" style="width: fit-content">Şikayet Tarihi</h6>
                                                            <p style="min-width: 200px" class="card-text text-primary">{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $report->created_at)->format('d.m.Y H:i:s')}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if(isset($report->getReportCategory))
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;height: max-content;overflow: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary" style="width: fit-content">Şikayet Edilen Kategori</h6>
                                                                <p style="min-width: 200px" class="card-text text-primary">{{$report->getReportCategory->name}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;height: max-content;overflow: auto">
                                                        <div style="padding: 10px 10px 10px 10px">
                                                            <h6 class="card-title text-primary" style="width: fit-content">Şikayet Eden</h6>
                                                            <p style="min-width: 200px" class="card-text text-primary">{{$report->getUser->firstname}} {{$report->getUser->lastname}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;overflow: auto">
                                                        <div style="padding: 10px 10px 10px 10px">
                                                            <h6 class="card-title text-primary" style="width: fit-content">Şikayet Fotoğrafı</h6>
                                                            <div class="slideshow-container">
                                                                @if(isset($report->getGallery->getImage))
                                                                    @foreach($report->getGallery->getImage as $image)
                                                                        <div class="mySlides fade">
                                                                            <img class="box-border" onclick="imageDetail(this)" style="width: 100%;height: 306px;object-fit:contain" src="{{asset($image->image_path_url)}}">
                                                                        </div>
                                                                    @endforeach
                                                                        <a class="prev" onclick="plusSlides(-1)">❮</a>
                                                                        <a class="next" onclick="plusSlides(1)">❯</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;overflow: auto">

                                                        <div style="padding: 10px 10px 10px 10px">
                                                            <h6 class="card-title text-primary" style="width: fit-content">Birime Atanma Tarihi</h6>
                                                            <p style="min-width: 200px" class="card-text text-primary">{{ !is_null($report->assigment_date_to_unit) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $report->assigment_date_to_unit)->format('d.m.Y H:i:s') : ''}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;overflow: auto">
                                                        <div style="padding: 10px 10px 10px 10px">
                                                            <h6 class="card-title text-primary" style="width: fit-content">Personele Atanma Tarihi</h6>
                                                            <p style="min-width: 200px" class="card-text text-primary">{{ !is_null($mission->mission_assigment_date) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $mission->mission_assigment_date)->format('d.m.Y H:i:s') : ''}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;height: max-content;overflow: auto">
                                                        <div style="padding: 10px 10px 10px 10px">
                                                            <h6 class="card-title text-primary" style="width: fit-content">Durum</h6>
                                                            <p style="min-width: 200px" class="card-text text-primary">{{$mission->status == 3 ? 'Reddedildi':''}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;height: max-content;overflow: auto">
                                                        <div style="padding: 10px 10px 10px 10px">
                                                            <h6 class="card-title text-primary" style="width: fit-content">Reddeden Kullanıcı</h6>
                                                            <p style="min-width: 200px" class="card-text text-primary">{{!is_null($rejectedDescription) ? $rejectedDescription->getRejectedUser->firstname .' '. $rejectedDescription->getRejectedUser->lastname : ''}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;height: max-content;overflow: auto">
                                                        <div style="padding: 10px 10px 10px 10px">
                                                            <h6 class="card-title text-primary" style="width: fit-content">Red Sebebi</h6>
                                                            <p style="min-width: 200px" class="card-text text-primary">{{!is_null($rejectedDescription) ? $rejectedDescription->reason_of_reject : ''}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-5">
                                        <div style="display: flex;justify-content: center;width: 100%">
                                            <button class="btn btn-primary" onclick="assignMissionAgain()">Yeniden Ata</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"
            integrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68nAhlwcDFLGPCQ=="
            crossorigin=""></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function imageDetail(e){
            let imageSrc = e.getAttribute('src');
            Swal.fire({
                html:'<img style="width: 100%; object-fit: contain" src="'+imageSrc+'" />',
                confirmButtonText:'Kapat'
            })
        }
    </script>
    <script>
        let map
        let marker
        showMap({{$mission->getReport->lat}}, {{$mission->getReport->lng}})
        function showMap(lat,long){
            map = L.map('map').setView([lat, long], 11);
            L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFsdGVwZW13ZWIiLCJhIjoiY2xmbWgxeTM0MGJ6ZTNxcDZ0ZXVzaGhtdiJ9.uWLmgsTZ1YP1_5-3rV4L8Q', {
                attribution: '<div>Maltepe Belediyesi</div>',
                maxZoom: 22,
                id: 'mapbox/streets-v11',
                tileSize: 512,
                zoomOffset: -1,
                accessToken: 'your.mapbox.access.token'
            }).addTo(map);
            marker = L.marker([lat, long]).addTo(map);
            marker.bindPopup("<b>Çek Gönder Noktası</b>").openPopup();
        }
    </script>
@endsection
@section('js')
    <script>
        function assignMissionAgain(){
            $('#againAssignModal').modal('toggle');
        }
        function assignToUser(){
            let user_id = document.getElementById('assignedUserId').value;
            let mission_id = '{{$mission->id}}'
            $.ajax({
                url:'{{route('report.assignMissionToUserAgain')}}',
                type:'POST',
                data:{
                    user_id:user_id,
                    mission_id:mission_id,
                    "_token":'{{csrf_token()}}',
                },
                success:()=>{
                    $('#againAssignModal').modal('hide');
                    window.location = "{{route('report.getRejectedMission')}}"
                }
            })
        }
        function closeModal(){
            $('#againAssignModal').modal('hide');
        }
    </script>

    <script>
        let slideIndex = 1;

        $(document).ready(()=>{
            showSlides(slideIndex);
        })

        function plusSlides(n) {
            showSlides(slideIndex += n);
        }

        function currentSlide(n) {
            showSlides(slideIndex = n);
        }

        function showSlides(n) {
            let i;
            let slides = document.getElementsByClassName("mySlides");
            if (n > slides.length) {slideIndex = 1}
            if (n < 1) {slideIndex = slides.length}
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slides[slideIndex-1].style.display = "contents";
        }
    </script>
@endsection
