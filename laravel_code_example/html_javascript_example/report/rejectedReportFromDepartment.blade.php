@extends('dashboard.layouts.master')
@section('title', 'Çek Gönder')
@section('content')
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
                                        Çek Gönder İçin Yetkilendirmeniz Bulunmuyor
                                    </div>
                                </div>
                                <div class="widget-content widget-content-area m-5"
                                    style="border-radius: 5px; display: none" id="settingButtonArea">
                                    <div style="margin: auto; justify-content: center; text-align: center;">
                                    </div>
                                </div>
                                <div class="widget-content widget-content-area br-6" id="tableArea">
                                    <div class="table-header">
                                        <h4>
                                            <label
                                                style="display: flex;margin-top: 2rem; flex-direction: row;justify-content: center; align-items: center;text-align: center">
                                                Bilgi: Bu sayfada adminler tarafından reddedilen çek gönderler
                                                listelenir.</label>
                                        </h4>
                                    </div>
                                    <div class="table-responsive mb-4">
                                        <table id="export-dt" class="table table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>id</th>
                                                    <th>Şikayet Eden</th>
                                                    <th>Açıklama</th>
                                                    <th>Adres</th>
                                                    <th>Kategori</th>
                                                    <th>Reddeden Birim</th>
                                                    <th>Reddeden Kullanıcı Adı</th>
                                                    <th>Bildirim Gönder</th>
                                                    <th>İşlem Ağacı</th>
                                                    <th>Tekrar Ata</th>
                                                </tr>
                                            </thead>

                                            <tfoot>
                                                <tr>
                                                    <th>id</th>
                                                    <th>Şikayet Eden</th>
                                                    <th>Açıklama</th>
                                                    <th>Adres</th>
                                                    <th>Kategori</th>
                                                    <th>Reddeden Birim</th>
                                                    <th>Reddeden Kullanıcı Adı</th>
                                                    <th>Bildirim Gönder</th>
                                                    <th>İşlem Ağacı</th>
                                                    <th>Tekrar Ata</th>
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

    <!--


     -->
@endsection
@section('js')
    <script>
        let reportTable = $('#export-dt').DataTable({
            order: [
                [0, 'DESC']
            ],
            dom: '<"row"<"col-md-6"B><"col-md-6"f> ><""rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>>',
            "scrollX": true,    // Enable horizontal scroll
            "scrollCollapse": true, // Collapse the table when it's smaller than the viewport
            "fixedHeader": true,  // Enable fixed header
            ajax: '{!! route('report.rejectedReportFromDepartmentFetch') !!}',
            columns: [{
                    data: 'id'
                },
                {
                    data: 'reporter'
                },
                {
                    data: 'description'
                },
                {
                    data: 'address'
                },
                {
                    data: 'category'
                },
                {
                    data: 'whereFromReject'
                },
                {
                    data: 'rejectedUser'
                },
                {
                    data: 'sendNotification'
                },
                {
                    data: 'processTree'
                },
                {
                    data: 'assign'
                },

            ],
            columnDefs: [{
                targets: [1,2,3,4,5],
                render: function(data, type, row) {
                    return type === 'display' && data.length > 100 ? data.substr(0, 100) + '…' : data;
                }
            }],
            buttons: {
                buttons: [{
                        extend: 'copy',
                        className: 'btn btn-primary'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-primary'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-primary'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-primary'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-primary'
                    },
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
        function sendNotification(id){
            Swal.fire({
                title:'Emin Misiniz ?',
                html:'<span>Bu çek gönderi reddetmek için lütfen <br>ret şablonu seçiniz.</span> ' +
                    '<br><div>' +
                    '<select placeholder="Reddetme Şablonunu Seçiniz" class="swal2-select" placeholder="" style="display: flex; width:87%;" onchange=getRejectPatternContent(this)>'
                    +'<option value="other">Diğer</option>@foreach($rejectPatterns as $rejectPattern)'+
                    '<option value="{{$rejectPattern->id}}">{{$rejectPattern->name}}</option>@endforeach'+
                    '</select><textarea placeholder="Reddetme Açıklamasını Giriniz" class="swal2-textarea" placeholder="" style="display: flex;  width:87%;" id="rejectPatternTextArea"></textarea></div>',
            }).then((response)=>{
                if(response.isConfirmed){
                    $.ajax({
                        url:'{{route('report.sendRejectedReportNotification')}}',
                        type:'POST',
                        data:{
                            _token:'{{csrf_token()}}',
                            id:id,
                            content:document.getElementById('rejectPatternTextArea').value,
                        },
                        success:()=>{
                            reportTable.ajax.reload();
                            Swal.fire({
                                icon:'success',
                                title:'Başarılı',
                                text:'Bildirim Gönderildi'
                            })
                        }
                    })
                }
            })
        }


    </script>

@endsection
