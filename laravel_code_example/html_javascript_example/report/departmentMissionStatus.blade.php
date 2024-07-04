@extends('dashboard.layouts.master')
@section('title','Çek Gönder')
@section('content')
    <div class="layout-px-spacing">
        <!-- Create Modal !-->
        <div class="modal" id="exampleModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bildirim Gönder</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="mission_id">
                            <div class="col-12">
                                <label for="schemaID">Bildirim Şablonu :</label>
                                <select name="" id="schemaID" class="form-control" style="height: max-content" onchange="checkOption(this)">
                                    @foreach($notifies as $notify)
                                        <option value="{{$notify->id}}">{{$notify->name}}</option>
                                    @endforeach
                                        <option value="0">Diğer</option>
                                </select>
                            </div>
                            <div class="col-12 mt-2" style="display: none" id="otherArea">
                                <label for="schemaContent">Bildirim İçeriği :</label>
                                <textarea class="form-control" name="" id="notifyContent" cols="15" rows="5"></textarea>
                                <div style="display: flex;justify-content: center;width: 100%">
                                    <span id="charCount">0 /255</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="sendNotify()">Gönder</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal" id="missionNotificationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bildirim Gönder</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="notification_mission_id">
                            <div class="col-12">
                                <label for="schemaID">Bildirim Şablonu :</label>
                                <select name="" id="notification_schemaID" class="form-control" style="height: max-content" onchange="checkNotificationOption(this)">
                                    @foreach($notifies as $notify)
                                        <option value="{{$notify->id}}">{{$notify->name}}</option>
                                    @endforeach
                                    <option value="0">Diğer</option>

                                </select>
                            </div>
                            <div class="col-12 mt-2" style="display: none" id="notification_otherArea">
                                <label for="schemaContent">Bildirim İçeriği :</label>
                                <textarea class="form-control" name="" id="notification_notifyContent" cols="15" rows="5"></textarea>
                                <span id="notification_charCount">0 /255</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="sendMissionNotification()">Gönder</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="layout-top-spacing mb-2">
            <div class="col-md-12">
                <div class="row">
                    <div class="container p-0">
                        <div class="row layout-top-spacing date-table-container">
                            <!-- Datatable with export options -->
                            <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                                <div class="widget-content widget-content-area" style="display: none" id="alertMessage">
                                    <div style="margin: auto">
                                        Çek Gönder İçin Yetkilendirmeniz Bulunmuyor
                                    </div>
                                </div>
                                <div class="widget-content widget-content-area m-5" style="border-radius: 5px; display: none" id="settingButtonArea">
                                    <div style="margin: auto; justify-content: center; text-align: center;">
                                    </div>
                                </div>
                                <div class="widget-content widget-content-area br-6" id="tableArea">
                                    <div class="table-header">
                                        <h4>
                                            <label style="display: flex;margin-top: 2rem; flex-direction: row;justify-content: center; align-items: center;text-align: center">Bilgi: Bu sayfada hangi kullanıcıya hangi görevin atandığını ve görevin durumuyla birlikte detayını görebilmekteyiz.</label>
                                        </h4>
                                    </div>
                                    <div class="container" style=" padding-bottom: 30px; padding-top: 30px; display: flex; flex-direction: row;align-items: end; flex-wrap: wrap; justify-content: center ; box-shadow: 1px 1px 11px 1px #c5b5b5; border-radius: 0.25rem;margin-bottom: 15px ">
                                        <div style="width: 500px;margin-right: 2rem">
                                            <label class="col-form-label text-center" for="unit-select">Filtre Türleri</label>
                                            <div>
                                                <select onchange="makeFiliter()" name="" id="filter" class="form-control" style="height: max-content">
                                                    <option value="10">Hepsi</option>
                                                    <option value="0">Atanmış / Bekleyen</option>
                                                    <option value="1">Görev Tamamlandı</option>
                                                    <option value="2">Asılsız Olarak İşaretlendi</option>
                                                    <option value="3">Bildirim Gönderildi</option>
                                                    <option value="4">Bildirim Bekleyen</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-4" style="display: block">
                                        <table id="export-dt" class="table table-hover" style="">
                                            <thead>
                                                <tr>
                                                    <th>id</th>
                                                    <th>Şikayet Eden</th>
                                                    <th>Açıklama</th>
                                                    <th>Adres</th>
                                                    <th>Kategori</th>
                                                    <th>Atanan Kullanıcı</th>
                                                    <th>Görev Tamamlanma Tarihi</th>
                                                    <th>Durum</th>
                                                    <th>İşlem Ağacı</th>
                                                    <th>Bildirim</th>
                                                    <th>Ara Bildirim</th>
                                                    <th>Detay</th>
                                                </tr>
                                            </thead>
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
        let reportTable = null
        let status = document.getElementById('filter').value;
        $(document).ready(()=>{
            reportTable = $('#export-dt').DataTable( {
                order: [
                    [0,'DESC']
                ],
                "scrollX": true,    // Enable horizontal scroll
                "scrollCollapse": true, // Collapse the table when it's smaller than the viewport
                "fixedHeader": true,  // Enable fixed header
                processing: true,
                dom: '<"row"<"col-md-6"B><"col-md-6"f> ><""rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>>',
                scrollY:  true,
                ajax: '{!!route('report.missionFetch')!!}?status=' + status,
                columns: [
                    {data: 'id'},
                    {data: 'reporter'},
                    {data:'description'},
                    {data:'address'},
                    {data:'getReportCategory'},
                    {data:'assignedUser'},
                    {data:'missionDoneDate'},
                    {data:'status_name'},
                    {data:'processTree'},
                    {data:'sendNotify'},
                    {data:'sendMissionNotification'},
                    {data:'detail'},
                ],
                buttons: {
                    buttons: [
                        { extend: 'copy', className: 'btn btn-primary' },
                        { extend: 'csv', className: 'btn btn-primary' },
                        { extend: 'excel', className: 'btn btn-primary' },
                        { extend: 'pdf', className: 'btn btn-primary' },
                        { extend: 'print', className: 'btn btn-primary' },
                        { extend: 'pageLength', className: 'btn'},
                    ],
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
        })

        function makeFiliter() {
            status = document.getElementById('filter').value;
            let url = '{!!route('report.missionFetch')!!}?status=' + status
            reportTable.ajax.url(url).load();
        }

        function customSearch (){
            reportTable.columns([0, 1, 2, 3, 4, 5, 6, 7, 8]).every(function(){
                this.search($('#customSearch').val()).draw();
            })
        }

        function sendNotify(){
            let schemaID = document.getElementById('schemaID').value;
            let notifyContent = document.getElementById('notifyContent').value;
            let missionID = document.getElementById('mission_id').value;

            if (notifyContent.length > 255) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata !',
                    text: 'İçerik 255 karakterden fazla olamaz.'
                });
                return;
            }

            $.ajax({
                url:'{{route('report.sendMissionNotification')}}',
                type:'POST',
                data: {
                    notify_id:schemaID,
                    notify_text:notifyContent,
                    mission_id:missionID,
                    _token:'{{csrf_token()}}',
                },
                success:(res)=>{
                    Swal.fire({
                        icon:'success',
                        title:'Başarılı',
                        text:'Bildirim Gönderildi'
                    })
                    reportTable.ajax.reload()
                    $('#exampleModal').modal('hide')
                },
                error:()=>{
                    Swal.fire({
                        icon:'error',
                        title:'Hata !',
                    })
                    reportTable.ajax.reload()
                    $('#exampleModal').modal('hide')
                }
            })
        }

        function sendMissionNotification(){
            let schemaID = document.getElementById('notification_schemaID').value;
            let notifyContent = document.getElementById('notification_notifyContent').value;
            let missionID = document.getElementById('notification_mission_id').value;

            if (notifyContent.length > 255) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata !',
                    text: 'İçerik 255 karakterden fazla olamaz.'
                });
                return;
            }
            $.ajax({
                url:'{{route('report.sendNotification')}}',
                type:'POST',
                data: {
                    notify_id:schemaID,
                    notify_text:notifyContent,
                    mission_id:missionID,
                    _token:'{{csrf_token()}}',
                },
                success:(res)=>{
                    Swal.fire({
                        icon:'success',
                        title:'Başarılı',
                        text:'Bildirim Gönderildi'
                    })
                    reportTable.ajax.reload()
                    $('#missionNotificationModal').modal('hide')
                },
                error:()=>{
                    Swal.fire({
                        icon:'error',
                        title:'Hata !',
                    })
                    reportTable.ajax.reload()
                    $('#missionNotificationModal').modal('hide')
                }
            })
        }

        function checkNotificationOption(e){
            let value = e.value;

            if (value == 0){
                document.getElementById('notification_otherArea').style.display = 'block'
            }
            else {
                document.getElementById('notification_otherArea').style.display = 'none'
            }
        }
        checkNotificationOption(document.getElementById('notification_schemaID'))

        function openNotificationModal(id){
            document.getElementById('notification_notifyContent').value = '';
            document.getElementById('notification_mission_id').value = id;
            $('#missionNotificationModal').modal('toggle')
        }



        function checkOption(e){
            let value = e.value;

            if (value == 0){
                document.getElementById('otherArea').style.display = 'block'
            }
            else {
                document.getElementById('otherArea').style.display = 'none'
            }
        }
        checkOption(document.getElementById('schemaID'))

        function openNotifyModal(id){
            document.getElementById('notifyContent').value = '';
            document.getElementById('mission_id').value = id;
            $('#exampleModal').modal('toggle')
        }



        // İlk modal için
        $(document).ready(function () {
            $("#notifyContent").on("input", function () {
                var maxChar = 255;
                var charCount = $(this).val().length;

                if (charCount > maxChar) {
                    $(this).val($(this).val().substr(0, maxChar));
                    $("#charCount").text(maxChar + " /255");
                } else {
                    $("#charCount").text(charCount + " /255");
                }
            });
        });

        // İkinci modal için
        $(document).ready(function () {
            $("#notification_notifyContent").on("input", function () {
                var maxChar = 255;
                var charCount = $(this).val().length;

                if (charCount > maxChar) {
                    $(this).val($(this).val().substr(0, maxChar));
                    $("#notification_charCount").text(maxChar + " /255");
                } else {
                    $("#notification_charCount").text(charCount + " /255");
                }
            });
        });

    </script>

@endsection
