@extends('dashboard.layouts.master')
@section('title','Çek-Gönder İstatistikleri')
@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <style>
        tbody tr:hover {
            background-color: rgb(121,121,121, 0.2);
        }
        .statistic-area{
            display: none;
            height: 300px;
            width: 100%;
        }
        button:hover {
            background-color: #0ba360;
        }

        .profile-img {
            height: 100px;
            width: 100px;
            object-fit: contain;
        }
        .biggestImage{
            width: 100%;
            height: 100%;
        }
        .cancelButton{
            width: max-content;
            height: max-content;
        }
    </style>
        <div class="layout-top-spacing mb-2">
            <div class="col-md-12">
                <div class="row">
                    <div class="container p-0">
                        <div class="row layout-top-spacing date-table-container">
                            <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                                @if($status)
                                    <div class="card" style="padding: 2rem; margin: 10px">
                                        <div class="card-header">
                                            <h4>Çek Gönder Personel İstatistikleri</h4>

                                        </div>
                                        <h4>
                                            <label style="display: flex;margin-top: 2rem; flex-direction: row;justify-content: center; align-items: center;text-align: center">Bilgi: Bu sayfada sadece çek-gönder birimi olan mobil personelin bilgilerini görebilir, var olan personelin detay kısmından yaptığı işlerin istatistiktiksel verilerine ulaşılabilir.</label>
                                        </h4>
                                        <div class="card-body">
                                            <div class="table-responsive mb-4">
                                                    <table class="table table-hover" id="table" style="width: 100%!important;">
                                                        <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Profil Fotoğrafı</th>
                                                                <th>İsim</th>
                                                                <th>Personel Birimi</th>
                                                                <th>Sicil Numarası</th>
                                                                <th>Çek-Gönder Birimi</th>
                                                                <th>Telefon</th>
                                                                <th>Detay</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                            </div>
                                        </div>
                                        <div class="statistic-area"  id="statisticArea">

                                        </div>
                                    </div>
                                @else
                                    <div class="card" style="padding: 2rem; margin: 10px">
                                        Bu Sayfadaki Verileri Görmek İçin İzniniz Bulunmuyor
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
@section('js')
    <script>

        $('#table').DataTable({
            order: [
                [0, 'DESC']
            ],
            dom: '<"row"<"col-md-6"B><"col-md-6"f> ><""rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>>',
            "scrollX": true,    // Enable horizontal scroll
            "scrollCollapse": true, // Collapse the table when it's smaller than the viewport
            "fixedHeader": true,  // Enable fixed header
            ajax: "{{ route('fetchPersonalStatistic') }}",
            columns: [
                {data:'id'},
                {data: 'profile_photo_path_url'},
                {data: 'name'},
                {data: 'unit'},
                {data: 'registration_number'},
                {data: 'report_unit'},
                {data: 'phone'},
                {data: 'detail'},
            ],
            buttons: {
                buttons: [
                    {extend: 'copy', className: 'btn btn-primary'},
                    {extend: 'csv', className: 'btn btn-primary'},
                    {extend: 'excel', className: 'btn btn-primary'},
                    {extend: 'pdf', className: 'btn btn-primary'},
                    {extend: 'print', className: 'btn btn-primary'},
                    { extend: 'pageLength', className: 'btn'},
                ]
            },
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.18/i18n/Turkish.json",
                "paginate": {
                    "previous": "<i class='las la-angle-left'></i>",
                    "next": "<i class='las la-angle-right'></i>"
                }
            },
            "lengthMenu": [ [7, 25, 50, -1], ["7", "25", "50", "Hepsi"] ],
            "pageLength": 7
        });


        function openStatistic(id){
            $('#statisticArea').slideToggle()
            statisticArea.innerText = id;
        }

        function showImage(e){
            Swal.fire({
                title:'Profil Fotoğrafı',
                html:'<img class="biggestImage" src="'+e.src+'" alt="hata"  />',
                showConfirmButton:false,
                showCancelButton:true,
                cancelButtonText:'Kapat',
                cancelButtonClass:'cancelButton'
            })
        }
    </script>
@endsection
