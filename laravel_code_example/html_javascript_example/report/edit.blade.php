@extends('dashboard.layouts.master')
@section('title','Çek Gönder')
@section('content')
    <style>
        .multi-select-area{
            border:1px solid #f0f0f0;
            padding: 20px 20px 20px 20px;
            border-radius: 5px;
            box-shadow: 5px 5px  5px 5px #f0f0f0;
        }

        * {
            font-size: 15px;
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
                                    <div class="row">
                                        <div class="col-12">
                                            <div style="height: 300px" id="map"></div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;max-height: 200px;overflow: auto">
                                                <div style="padding: 10px 10px 10px 10px">
                                                    <h6 class="card-title text-primary" style="width: fit-content">Şikayet Açıklaması</h6>
                                                    <p style="min-width: 200px" class="card-text text-primary">{{$report->description}}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;height: 200px;max-height: 200px;overflow: auto">
                                                <div style="padding: 10px 10px 10px 10px">
                                                    <h6 class="card-title text-primary" style="width: fit-content">Şikayet Adresi</h6>
                                                    <p style="min-width: 200px" class="card-text text-primary">{{$report->address}}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;height: 200px;max-height: 200px;overflow: auto">
                                                <div style="padding: 10px 10px 10px 10px">
                                                    <h6 class="card-title text-primary" style="width: fit-content">Şikayet Eden</h6>
                                                    <p style="min-width: 200px" class="card-text text-primary">{{$report->getUser->firstname}} {{$report->getUser->lastname}}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(isset($report->getReportCategory))
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12" style="margin: auto">
                                                <div class="mt-3 box-border shadow" style="width: 100%;border-radius: 5px;height: 200px;max-height: 200px;overflow: auto">
                                                    <div style="padding: 10px 10px 10px 10px">
                                                        <h6 class="card-title text-primary" style="width: fit-content">Şikayet Edilen Kategori</h6>
                                                        <p style="min-width: 200px" class="card-text text-primary">{{$report->getReportCategory->name}}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row mt-5">
                                        <div class="col-12 ">
                                            <h6>Şikayet Fotoğrafları:</h6>
                                        </div>
                                        @if(isset($report->getGallery->getImage))
                                            @foreach($report->getGallery->getImage as $image)
                                                <div class="col-sm-12 col-md-4 col-lg-4 mt-2">
                                                    <img onclick="imageDetail(this)" style="width: 100%;height: 306px;border: 1px solid black;object-fit:contain" src="{{asset($image->image_path_url)}}" alt="">
                                                </div>
                                            @endforeach
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
        showMap({{$report->lat}}, {{$report->lng}})
        function showMap(lat,long){
            map = L.map('map').setView([lat, long], 11);
            L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFsdGVwZW13ZWIiLCJhIjoiY2xmbWgxeTM0MGJ6ZTNxcDZ0ZXVzaGhtdiJ9.uWLmgsTZ1YP1_5-3rV4L8Q', {
                attribution: '<div>Maltepe Belediyesi</div>',
                maxZoom: 18,
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

@endsection
