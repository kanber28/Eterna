@extends('dashboard.layouts.master')
@section('title','Çek Gönder')
@section('content')
    <style>
        select {
            height: min-content !important;
        }
    </style>
    <div class="layout-px-spacing">
        <div class="layout-top-spacing mb-2">
            <div class="col-md-12">
                <div class="row">
                    <div class="container p-0">
                        <div class="row layout-top-spacing date-table-container">
                            <!-- Datatable with export options -->
                            <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                                <!-- modal -->
                                <div class="modal fade" id="assignDepartmentManager" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">
                                                    Birim Yöneticisi Ata
                                                </h5>
                                                <button onclick="closeModal()" type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">

                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button onclick="closeModal()" type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="widget-content widget-content-area" style="display: none" id="alertMessage">
                                    <div style="margin: auto">
                                        Çek Gönder İçin Yetkilendirmeniz Bulunmuyor
                                    </div>
                                </div>
                                <div class="widget-content widget-content-area br-6" id="tableArea">
                                    <div class="table-header" >
                                        <h4>
                                            <label style="display: flex;margin-top: 2rem; flex-direction: row;justify-content: center; align-items: center;text-align: center">Bu sayfada çek-gönder yönetici ayarlarında birimlerin çek-gönder yöneticileri(amirleri) atanmaktadır.</label>
                                        </h4>
                                    </div>
                                    <div class="table-responsive mb-4">
                                        <table id="export-dt" class="table table-hover" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>id</th>
                                                <th>İsim</th>
                                                <th>Soyisim</th>
                                                <th>Sicil Numarası</th>
                                                <th>Birim Admini Yap</th>
                                                <th>Çek Gönder Admin Yap</th>
                                                <th>Yetkileri Kaldır</th>
                                            </tr>
                                            </thead>

                                            <tfoot>
                                            <tr>
                                                <th>id</th>
                                                <th>İsim</th>
                                                <th>Soyisim</th>
                                                <th>Sicil Numarası</th>
                                                <th>Birim Admini Yap</th>
                                                <th>Çek Gönder Admin Yap</th>
                                                <th>Yetkileri Kaldır</th>
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
        let employeeTable = $('#export-dt').DataTable( {
            order: [
                [0,'DESC']
            ],
            dom: '<"row"<"col-md-6"B><"col-md-6"f> ><""rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>>',
            "scrollX": true,    // Enable horizontal scroll
            "scrollCollapse": true, // Collapse the table when it's smaller than the viewport
            "fixedHeader": true,  // Enable fixed header
            ajax: '{!!route('report.fetchEmployeeForDepartmentSettings')!!}',
            columns: [
                {data: 'id'},
                {data: 'firstname'},
                {data: 'lastname'},
                {data: 'registration_number'},
                {data: 'departmentSettings'},
                {data: 'doAdmin'},
                {data: 'doAdministrator'},
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
                    "next": "<i class='las la-angle-right'></i>"
                }
            },
            "lengthMenu": [ [7, 25, 50, -1], ["7", "25", "50", "Hepsi"] ],
            "pageLength": 7
        });
    </script>
    <script>
        function removeAllAuthority(id){
            Swal.fire({
                icon:'warning',
                title:'Emin Misiniz!',
                text:'Bu kullanıcının bütün yekilerini kaldırmak istediğinize emin misiniz',
                showConfirmButton:true,
                confirmButtonText:'Kaldır',
                showCancelButton:true,
                cancelButtonText:'İptal ET'
            }).then((response)=>{
                if(response.isConfirmed){
                    $.ajax({
                        url:'{{route('report.removeAllAuthority')}}',
                        type:'POST',
                        data:{
                            user_id:id,
                            "_token":'{{csrf_token()}}',
                        },
                        success:()=>{
                            employeeTable.ajax.reload()
                        }
                    })
                }
            })
        }

        function changeAuthority(e, user_id){
            $.ajax({
                url:'{{route('report.changeAuthority')}}',
                type:'POST',
                data:{
                    user_id:user_id,
                    report_category_id:e.value,
                    "_token":'{{csrf_token()}}',
                },
                success:()=>{
                    employeeTable.ajax.reload()
                }
            })
        }

        function doNewAdmin(user_id){
            $.ajax({
                url:'{{route('report.doTakeAndSendAdmin')}}',
                type:'POST',
                data:{
                    user_id:user_id,
                    "_token":'{{csrf_token()}}',
                },
                success:()=>{
                    employeeTable.ajax.reload()
                }
            })
        }
    </script>
@endsection
