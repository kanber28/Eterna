@extends('dashboard.layouts.master')
@section('title','Çek Gönder')
@section('content')
    <style>
        .info-modal {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background-color: rgba(0,0,0,0.4);
            height: 100%;
            width: 100%;
            z-index: 99999;
            display: none;
        }

        .modal-center {
            overflow: auto;
            position: relative;
            margin: auto;
            width: 50%;
            height: 70%;
            background-color: white;
            display: flex;
            z-index: 999999;
            border-radius: 10px;
        }
    </style>
    <!-- MODAL -->
    <div class="info-modal ">
        <div class="modal-center">

        </div>
    </div>
    <div class="layout-px-spacing">

        <div class="layout-top-spacing mb-2">
            <div class="col-md-12">
                <div class="row">
                    <div class="container p-0">
                        <div class="row layout-top-spacing date-table-container">
                            <!-- Datatable with export options -->
                            <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                                <div class="widget-content widget-content-area" style="display: none" id="alertMessage">
                                    <div style="margin: auto">
                                        Çek Gönder İçerisinde Herhangi Bir Biriminiz Bulunmamaktadır.
                                    </div>
                                </div>
                                <div class="widget-content widget-content-area" style="display: none" id="alertMessageTwo">
                                    <div style="margin: auto ; text-align: center">
                                        Çek-Gönder görüntüleme yetkiniz var fakat herhangi bir birimde görevli olmadığınız için Çek-Gönder'i görüntüleyemiyorsunuz. <br>
                                        Lütfen yöneticiniz ile iletişime geçiniz.</div>
                                </div>
                                <div class="widget-content widget-content-area mr-5 mt-5 ml-5" style="border-radius: 5px; display: none" id="settingButtonArea">
                                    <div style="margin: auto; justify-content: center; text-align: center;">
                                        @if(\Illuminate\Support\Facades\Auth::user()->hasRole('Süper Admin') || \Illuminate\Support\Facades\Auth::user()->hasRole('Başkan'))
                                            <div class="mt-2">
                                                <a href="{{route('report.waitingReportFromDepartment')}}" class="btn" style="background-color: #e7515a; color: white">Birimde Bekleyen Şikayetler</a>
                                                <a href="{{route('report.missions')}}" class="btn" style="background-color: #144272; color: white">Görevler</a>
                                                <a href="{{route('report.getRejectedMission')}}" class="btn" style="background-color: #B7454D; color: white">Reddedilen Görevler</a>
                                                <a href="{{route('report.rejectedReportFromDepartment')}}" class="btn" style="background-color: #862D45; color: white">Adminler Tarafından Reddedilen Çek Gönderler</a>
                                            </div>
                                        @elseif(!is_null($userReportType))
                                            @if($userReportType->is_administrator == 1 )
                                                <a href="{{route('report.getRejectedMission')}}" class="btn" style="background-color: #B7454D; color: white">Reddedilen Görevler</a>
                                                <a href="{{route('report.missions')}}" class="btn" style="background-color: #144272; color: white">Görevler</a>
                                                <a href="{{route('report.waitingReportFromDepartment')}}" class="btn" style="background-color: #e7515a; color: white">Birimde Bekleyen Şikayetler</a>
                                                <a href="{{route('report.rejectedReportFromDepartment')}}" class="btn" style="background-color: #862D45; color: white">Adminler Tarafından Reddedilen Çek Gönderler</a>
                                               @elseif($userReportType->is_administrator == 0)
                                                <a href="{{route('report.getRejectedMission')}}" class="btn ing" >Reddedilen Görevler</a>
                                                <a href="{{route('report.missions')}}" style="background-color: #476098; color: white" class="btn ess" >Görevler</a>
                                            @endif
                                        @endif

                                    </div>
                                </div>
                                <div class="widget-content widget-content-area br-6" id="tableArea">
                                    <div class="table-header" style="margin-top: -55px; padding-bottom: 10px;">
                                        <h4>
                                            <label style="display: flex;margin-top: 2rem; flex-direction: row;justify-content: center; align-items: center;text-align: center">Bilgi: Bu sayfada mobil uygulama içerisinden gönderilen çek-gönderleri görüntülemekteyiz. Ata kısmından ilgili birim <br> seçildikten sonra web personel olan birim amirine çek gönder gönderilmektedir. Birim amiride o birimde çalışan mobil personele<br> çek-gönderi iletmektedir. Personele düşen çek-gönder mobil uygulama içerisinde kendisine gözükmektedir.
                                          </label>
                                        </h4>
                                    </div>
                                    <div class="table-responsive mb-4">
                                        @if(\Illuminate\Support\Facades\Auth::user()->hasRole('Süper Admin') || \Illuminate\Support\Facades\Auth::user()->hasRole('Başkan'))
                                            <div style="width: 100%;display: flex;justify-content: end">
                                                <a href="{{route('report.exportExcelPage')}}" class="btn btn-info">Excel Çıktısı</a>
                                            </div>
                                        @else
                                            @if(!is_null($userReportType))
                                            @if($userReportType->is_administrator == 1 )
                                                <div style="width: 100%;display: flex;justify-content: end">
                                                    <a href="{{route('report.exportExcelPage')}}" class="btn btn-info">Excel Çıktısı</a>
                                                </div>
                                            @endif
                                            @endif
                                        @endif
                                        <table id="export-dt" class="table table-hover" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>id</th>
                                                <th>Şikayet Eden</th>
                                                <th>Kategori</th>
                                                <th>Adres</th>
                                                <th>Açıklama</th>
                                                <th>İşlem Ağacı</th>
                                                <th>Ata</th>
                                                <th>Reddet</th>
                                            </tr>
                                            </thead>

                                            <tfoot>
                                            <tr>
                                                <th>id</th>
                                                <th>Şikayet Eden</th>
                                                <th>Kategori</th>
                                                <th>Adres</th>
                                                <th>Açıklama</th>
                                                <th>İşlem Ağacı</th>
                                                <th>Ata</th>
                                                <th>Reddet</th>
                                            </tr>
                                            </tfoot>
                                        </table>
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
        let reportTable  = null
        $(document).ready(()=>{
            document.getElementById('settingButtonArea').style.display = "flex"
            @if(!$tableStatus)
                document.getElementById('tableArea').style.display = "none";
                document.getElementById('alertMessage').style.display = "flex";
            @endif
            @if(!is_null($userReportType))
                @if($userReportType->is_administor == 1)
                    document.getElementById('settingButtonArea').style.display = "flex"
                @endif
            @endif
            @if(\Illuminate\Support\Facades\Auth::user()->hasRole('Süper Admin'))
                document.getElementById('settingButtonArea').style.display = "flex"
            @endif
            @if(!$isTherePermit)
                document.getElementById('tableArea').remove();
                document.getElementById('settingButtonArea').remove();
                document.getElementById('alertMessage').style.display = "none";
                document.getElementById('alertMessageTwo').style.display = "flex";
            @endif

            @if($isTherePermit)
                 reportTable = $('#export-dt').DataTable( {
                    order: [
                        [0,'DESC']
                    ],
                    dom: '<"row"<"col-md-6"B><"col-md-6"f> ><""rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>>',
                    "scrollX": true,    // Enable horizontal scroll
                    "scrollCollapse": true, // Collapse the table when it's smaller than the viewport
                    "fixedHeader": true,  // Enable fixed header
                    ajax: '{!!route('report.fetchReports')!!}',
                    columns: [
                        {data: 'id'},
                        {data: 'reporter'},
                        {data:'category'},
                        {data:'address'},
                        {data:'description'},
                        {data:'processTree'},
                        {data:'assign'},
                        {data:'reject'},

                    ],

                    buttons: {
                        buttons: [
                            { extend: 'copy', className: 'btn btn-primary' },
                            { extend: 'csv', className: 'btn btn-primary' },
                            { extend: 'excel', className: 'btn btn-primary' },
                            { extend: 'pdf', className: 'btn btn-primary' },
                            { extend: 'print', className: 'btn btn-primary' },
                            { extend: 'pageLength', className: 'btn'},
                        ]
                    },
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.18/i18n/Turkish.json",
                        "paginate": {
                            "previous": "<i class='las la-angle-left'></i>",
                            "next": "<i class='las la-angle-right'></i>",
                        }
                    },
                    "lengthMenu": [ [7, 25, 50, -1], ["7", "25", "50", "Hepsi"] ],
                    "pageLength": 7
                });
            @endif
        })
    </script>
    <script>

        function superAdminRejectReport(id){
            Swal.fire({
                icon:'warning',
                title:'Emin Misiniz ?',
                text:'Bu çek gönderi reddetmek istediğinize emin misiniz ?',
                showConfirmButton:true,
                showCancelButton:true,
                confirmButtonText:'Reddet',
                confirmButtonColor: '#f21818' ,
                cancelButtonText:'İptal Et',
                cancelButtonClass: 'btn btn-primary',
            }).then((response)=>{
                if(response.isConfirmed){
                    $.ajax({
                        url:'{{route('report.rejectReport')}}',
                        type:'POST',
                        data:{
                            id:id,
                           "_token":'{{csrf_token()}}',
                        },
                        success:(res)=>{
                            if(res.status === 'success'){
                                Swal.fire({
                                    icon:'success',
                                    title:'Başarılı !',
                                    confirmButtonText:'Tamam',
                                })
                            }
                            reportTable.ajax.reload()
                        },
                        error:()=>{
                            Swal.fire({
                                icon:'error',
                                title:'Hata !',
                                text:'Bir Hata Oluştu !',
                                confirmButtonText:'Tamam',
                            })
                        }
                    })
                }
            })
        }
        function getRejectPatternContent(e){
            $.ajax({
                url:'{{route('report.rejectPatternGetContent')}}',
                type:'GET',
                data:{
                    id:e.value,
                    "_token":'{{csrf_token()}}',
                },
                success:(res)=>{
                   document.getElementById('rejectPatternTextArea').value=res.content;
                }
            })
        }
        function rejectReport(id){
            Swal.fire({
                icon:'warning',
                title:'Emin Misiniz ?',
                text:'Bu çek gönderi reddetmek istediğinize emin misiniz ?',
                showConfirmButton:true,
                showCancelButton:true,
                confirmButtonText:'Reddet',
                confirmButtonColor: '#f21818' ,
                cancelButtonText:'İptal Et',
                cancelButtonClass: 'btn btn-primary'
            }).then((response)=>{
                if(response.isConfirmed){
                    $.ajax({
                        url:'{{route('report.rejectReport')}}',
                        type:'POST',
                        data:{
                            id:id,
                            "_token":'{{csrf_token()}}',
                        },
                        success:(res)=>{
                            if(res.status === 'success'){
                                Swal.fire({
                                    icon:'success',
                                    title:'Başarılı !',
                                    confirmButtonText:'Tamam',
                                })
                            }
                            reportTable.ajax.reload()
                        },
                        error:()=>{
                            Swal.fire({
                                icon:'error',
                                title:'Hata !',
                                text:'Bir Hata Oluştu !',
                                confirmButtonText:'Tamam',
                            })
                        }
                    })
                }
            })
        }

        function rejectFromDepartment(id){
            Swal.fire({
                icon:'warning',
                title:'Emin Misiniz?',
                text:'Bu çek gönderi reddetmek istediğinize emin misiniz?',
                showConfirmButton:true,
                showCancelButton:true,
                confirmButtonText:'Sil',
                confirmButtonColor: '#f21818' ,
                cancelButtonText:'İptal Et',
                cancelButtonClass: 'btn btn-primary'
            }).then((response)=>{
                if(response.isConfirmed){
                    $.ajax({
                        url:'{{route('report.rejectReportFromDepartment')}}',
                        type:'POST',
                        data:{
                            id:id,
                            "_token":'{{csrf_token()}}',
                        },
                        success:(res)=>{
                            if(res.status === 'success'){
                                Swal.fire({
                                    icon:'success',
                                    title:'Başarılı !',
                                    confirmButtonText:'Tamam',
                                })
                            }
                            reportTable.ajax.reload()
                        },
                        error:()=>{
                            Swal.fire({
                                icon:'error',
                                title:'Hata!',
                                text:'Bir hata oluştu!',
                                confirmButtonText:'Tamam',
                            })
                        }
                    })
                }
            })
        }
    </script>
@endsection
