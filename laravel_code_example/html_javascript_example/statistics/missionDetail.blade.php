@extends('dashboard.layouts.master')
@section('title','Çek Gönder')
@section('content')
    <style>

        * {
            font-size: 15px;
        }

        .mySlides {display: none}
        .mySlides2 {display: none}
        img {vertical-align: middle;}

        /* Slideshow container */
        .slideshow-container {
            max-width: 1000px;
            position: relative;
            margin: auto;
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
        .multi-select-area {
            border: 1px solid #f0f0f0;
            padding: 20px 20px 20px 20px;
            border-radius: 5px;
            box-shadow: 5px 5px 5px 5px #f0f0f0;
        }

        .multi-select-area label {
            color: #6b6d78;
        }

        @media screen and (max-width: 600px) {
            .multi-select-area label {
                margin-top: 20px;
            }
        }

        .box-border {
            border: 1px solid #d4d4d4
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css"
          integrity="sha512-hoalWLoI8r4UszCkZ5kL8vayOGVae1oxXe/2A4AO6J9+580uKHDO3JdHb7NzwwzK5xr/Fs0W40kiNHxM9vyTtQ=="
          crossorigin=""/>
    <div>
        <div class="layout-px-spacing">
            <div class="layout-top-spacing mb-2">
                <div class="col-md-12">
                    <div class="row">
                        <div class="container p-0">
                            <div class="row layout-top-spacing date-table-container">
                                <!-- Datatable with export options -->
                                <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                                    <div class="widget-content widget-content-area br-6">
                                        <div class="row">
                                            <div class="col-12">
                                                <div style="height: 300px" id="map"></div>
                                            </div>

                                            <div class="row" style="width: 100%">
                                                <div class="col-6" style="padding: 0 !important;">
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow shadow"
                                                             style="width: 100%;border-radius: 5px;height: max-content;max-height: 200px;overflow: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary" style="width: fit-content">
                                                                    Şikayet Açıklaması</h6>
                                                                <p style="min-width: max-content"
                                                                   class="card-text text-primary">{{$report->description}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;height: max-content;overflow: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary" style="width: fit-content">Şikayet Mahallesi</h6>
                                                                <p style="min-width: 200px" class="card-text text-primary">{{$report->getNeighbourhood->name}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow"
                                                             style="width: 100%;border-radius: 5px;height: max-content;overflow: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary" style="width: fit-content">
                                                                    Şikayet Adresi</h6>
                                                                <p style="min-width: 200px"
                                                                   class="card-text text-primary">{{$report->address}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;height: max-content;overflow: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary" style="width: fit-content">Şikayet Tarihi</h6>
                                                                <p style="min-width: 200px" class="card-text text-primary">{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $report->created_at)->format('d.m.Y H:i:s')}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if(isset($report->getReportCategory))
                                                        <div class="col-md-12 col-sm-12">
                                                            <div class="mt-3 box-border shadow"
                                                                 style="width: 100%;border-radius: 5px;height: max-content;overflow: auto">
                                                                <div style="padding: 10px 10px 10px 10px">
                                                                    <h6 class="card-title text-primary"
                                                                        style="width: fit-content">
                                                                        Şikayet Edilen Kategori</h6>
                                                                    <p style="min-width: 200px"
                                                                       class="card-text text-primary">{{$report->getReportCategory->name}}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow"
                                                             style="width: 100%;border-radius: 5px;height: max-content;overflow: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary" style="width: fit-content">
                                                                    Şikayet Eden</h6>
                                                                <p style="min-width: 200px"
                                                                   class="card-text text-primary">{{$report->getUser->firstname}} {{$report->getUser->lastname}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-6" style="padding: 0 !important;">
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;height: max-content;overflow: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary" style="width: fit-content">Görevin Atandığı Tarih</h6>
                                                                <p style="min-width: 200px" class="card-text text-primary">{{ !is_null($mission->mission_assigment_date) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $mission->mission_assigment_date)->format('d.m.Y H:i:s') : '-'  }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow"
                                                             style="width: 100%;border-radius: 5px;height: max-content;overflow: auto;margin: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary" style="width: fit-content">
                                                                    Atanan Personel</h6>
                                                                <p style="min-width: 200px"
                                                                   class="card-text text-primary">{{$mission->getAssignedUser->firstname .' '.$mission->getAssignedUser->lastname}}</p>
                                                                <br>
                                                                <h6 class="card-title text-primary" style="width: fit-content">Sicil Numarası</h6>
                                                                <p style="min-width: 200px" class="card-text text-primary">{{$mission->getAssignedUser->registration_number}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow"
                                                             style="width: 100%;border-radius: 5px;height: max-content;overflow: auto;margin: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary" style="width: fit-content">
                                                                    Görev
                                                                    Kabul Edilme Durumu</h6>
                                                                <p style="min-width: 200px"
                                                                   class="card-text text-primary">{{($mission->is_approved == 1) ? 'Kabul Edildi':'Henüz Kabul Edilmedi'}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow"
                                                             style="width: 100%;border-radius: 5px;height: max-content;overflow: auto;margin: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary" style="width: fit-content">
                                                                    Şikayetin Oluşma Nedeni</h6>
                                                                <p style="min-width: 200px"
                                                                   class="card-text text-primary">{{(!is_null($mission->mission_solution_description) ? $mission->mission_reason_description : '-')}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow"
                                                             style="width: 100%;border-radius: 5px;height: max-content;overflow: auto;margin: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary" style="width: fit-content">
                                                                    Görevin Çözüm Açıklaması</h6>
                                                                <p style="min-width: 200px"
                                                                   class="card-text text-primary">{{(!is_null($mission->mission_solution_description) ? $mission->mission_solution_description : '-')}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if($mission->status == 2)
                                                        <div class="col-md-12 col-sm-12">
                                                            <div class="mt-3 box-border shadow"
                                                                 style="width: 100%;border-radius: 5px;height: max-content;overflow: auto;margin: auto">
                                                                <div style="padding: 10px 10px 10px 10px">
                                                                    <h6 class="card-title text-primary"
                                                                        style="width: fit-content">
                                                                        Görevin Asılsız Olma Açıklaması</h6>
                                                                    <p style="min-width: 200px"
                                                                       class="card-text text-primary">{{(!is_null($mission->why_mission_is_fake) ? $mission->why_mission_is_fake : '-')}}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;height: max-content;overflow: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary" style="width: fit-content">Görev Tamamlanma Tarihi</h6>
                                                                <p style="min-width: 200px" class="card-text text-primary">{{ !is_null($mission->done_date) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $mission->done_date)->format('d.m.Y H:i:s') : '-'  }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow"
                                                             style="width: 100%;border-radius: 5px;height: max-content;overflow: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary"
                                                                    style="width: fit-content">
                                                                    Durum</h6>
                                                                <p style="min-width: 200px"
                                                                   class="card-text text-primary">{{$mission->status == 0 ? 'Atandı':''}}{{$mission->status == 1 ? 'Görev Tamamlandı':''}}{{$mission->status == 2 ? 'Asılsız Olarak İşaretlendi':''}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="mt-3 box-border shadow"
                                                             style="width: 100%;border-radius: 5px;height: max-content;overflow: auto;margin: auto">
                                                            <div style="padding: 10px 10px 10px 10px">
                                                                <h6 class="card-title text-primary"
                                                                    style="width: fit-content">
                                                                    Bildirim İçeriği</h6>
                                                                <p style="min-width: 200px"
                                                                   class="card-text text-primary">{{(!is_null($mission->notify_content) ? $mission->notify_content : 'Bildirim Gönderilmemiş')}}</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="row mt-5" style="width: 100%;padding-top: 8px;border-top: 1px solid grey">
                                                <div style="display: flex;width: 100%;justify-content: center;margin-bottom: 7px">
                                                    <h5>Fotoğraflar</h5>
                                                </div>
                                                <div class="col-md-6">
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
                                                <div class="col-md-6">
                                                    <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;overflow: auto">
                                                        <div style="padding: 10px 10px 10px 10px">
                                                            <h6 class="card-title text-primary" style="width: fit-content">Çözüm Fotoğrafları</h6>
                                                            <div class="slideshow-container">
                                                                @if($mission->status == 1 || $mission->status == 2)
                                                                    @if(isset($mission->getGallery->getImage))
                                                                        @foreach($mission->getGallery->getImage as $image)
                                                                            <div class="mySlides2 fade">
                                                                                <img class="box-border" onclick="imageDetail(this)" style="width: 100%;height: 306px;object-fit:contain" src="{{asset($image->image_path_url)}}">
                                                                            </div>
                                                                        @endforeach
                                                                        <a class="prev" onclick="plusSlides2(-1)">❮</a>
                                                                        <a class="next" onclick="plusSlides2(1)">❯</a>
                                                                    @endif
                                                                @else
                                                                    <div style="width: 100%;height: 200px;display: flex;justify-content: center;align-items: center;align-content: center">
                                                                        Fotoğraf Bulunmuyor
                                                                    </div>
                                                                @endif
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
                </div>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"
            integrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68nAhlwcDFLGPCQ=="
            crossorigin=""></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function imageDetail(e) {
            let imageSrc = e.getAttribute('src');
            Swal.fire({
                html: '<img style="width: 100%; object-fit: contain" src="' + imageSrc + '" />',
                confirmButtonText: 'Kapat'
            })
        }
    </script>
    <script>
        let map
        let marker
        showMap({{$mission->getReport->lat}}, {{$mission->getReport->lng}})

        function showMap(lat, long) {
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
    <script>
        let slideIndex2 = 1;

        $(document).ready(()=>{
            showSlides2(slideIndex2);
        })

        function plusSlides2(n) {
            showSlides2(slideIndex2 += n);
        }

        function currentSlide2(n) {
            showSlides2(slideIndex2 = n);
        }

        function showSlides2(n) {
            let i;
            let slides = document.getElementsByClassName("mySlides2");
            if (n > slides.length) {slideIndex2 = 1}
            if (n < 1) {slideIndex2 = slides.length}
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slides[slideIndex2-1].style.display = "contents";
        }





    </script>
@endsection
