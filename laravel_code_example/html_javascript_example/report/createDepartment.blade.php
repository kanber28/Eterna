@extends('dashboard.layouts.master')
@section('title','Çek Gönder')
@section('content')
    <style>
        .box-border{
            border: 1px solid #d4d4d4;
            border-radius: 5px;
            margin-left: 8.333px;
            margin-right: 0;
            margin-top: 5px;
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
                                <!-- Modal -->
                                <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Birim Oluştur</h5>
                                                <button onclick="closeCreateModal()" type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row" style="display: flex; width: 100%;justify-content: center">
                                                    <div class="form-group col-12">
                                                        <label for="">Birim İsmi : </label>
                                                        <input type="text" class="form-control" name="departmentName" id="departmentName">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button onclick="createDepartment()" class="btn btn-primary">Oluştur</button>
                                                <button onclick="closeCreateModal()" type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Update Modal -->
                                <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Birim Güncelle</h5>
                                                <button onclick="closeUpdateModal()" type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row" style="display: flex; width: 100%;justify-content: center">
                                                    <div class="form-group col-12">
                                                        <label for="">Birim İsmi : </label>
                                                        <input type="hidden" id="updatedDepartmentId">
                                                        <input type="text" class="form-control" name="updateDepartmentName" id="updateDepartmentName">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button onclick="updateDepartment()" class="btn btn-primary">Güncelle</button>
                                                <button onclick="closeUpdateModal()" type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="widget-content widget-content-area br-6" id="tableArea">
                                    <div style="display: block;min-height: 70px">
                                        <div style="display: flex;position: relative;">
                                            <div style="display: flex;position: absolute;right:5px;top:2px">
                                                <button onclick="createModal()" class="btn btn-primary">Yeni Birim Oluştur</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-header" >
                                        <h4>
                                            <label style="display: flex;margin-top: 2rem; flex-direction: row;justify-content: center; align-items: center;text-align: center">Bilgi: Birim oluşturmada yeni birimler oluşturulabilmektedir. Bunları güncelle kısmından değiştirebilmekteyiz.</label>
                                        </h4>
                                    </div>
                                    <div class="table-responsive mb-4">
                                        <table id="export-dt" class="table table-hover" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>id</th>
                                                <th>İsim</th>
                                                <th>güncelle</th>
                                                <th>Sil</th>
                                            </tr>
                                            </thead>

                                            <tfoot>
                                            <tr>
                                                <th>ID</th>
                                                <th>İSİM</th>
                                                <th>GUNCELLE</th>
                                                <th>Sil</th>
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
        let departmentTable = $('#export-dt').DataTable( {
            order: [
                [0,'DESC']
            ],
            dom: '<"row"<"col-md-6"B><"col-md-6"f> ><""rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>>',
            "scrollX": true,    // Enable horizontal scroll
            "scrollCollapse": true, // Collapse the table when it's smaller than the viewport
            "fixedHeader": true,  // Enable fixed header
            ajax: '{!!route('report.fetchDepartments')!!}',
            columns: [
                {data: 'id'},
                {data: 'name'},
                {data: 'update'},
                {data: 'delete'},
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

        function createModal(){
            $('#createModal').modal('toggle');
        }
        function closeCreateModal(){
            $('#createModal').modal('hide');
        }


        function createDepartment(){
            let departmentName = document.getElementById('departmentName').value;

            $.ajax({
                url:'{{route('report.createDepartment')}}',
                type:'POST',
                data:{
                    departmentName:departmentName,
                    "_token":'{{csrf_token()}}',
                },
                success:()=>{
                    $('#createModal').modal('hide');
                    Swal.fire({
                        icon:'success',
                        title:'Başarılı',
                        confirmButtonText: "Tamam"
                    })
                    departmentTable.ajax.reload();
                    document.getElementById('departmentName').value="";
                }
            })
        }

        function closeUpdateModal(){
            $('#updateModal').modal('hide');
        }

        function updateDepartmentModal(id){
            $.ajax({
                url:'{{route('report.getDepartment')}}',
                type:'GET',
                data:{
                    id:id,
                },
                success:(response)=>{
                    document.getElementById('updatedDepartmentId').value = id;
                    document.getElementById('updateDepartmentName').value = response.name;
                    $('#updateModal').modal('toggle');
                }
            })
        }

        function updateDepartment(){
            let id = document.getElementById('updatedDepartmentId').value;
            let name = document.getElementById('updateDepartmentName').value;

            $.ajax({
                url:'{{route('report.updateDepartment')}}',
                type:'POST',
                data:{
                    id:id,
                    name:name,
                    "_token":'{{csrf_token()}}'
                },
                success:()=>{
                    $('#updateModal').modal('hide');
                    Swal.fire({
                        icon:'success',
                        title:'Başarılı',
                        confirmButtonText: "Tamam"
                    })
                    departmentTable.ajax.reload();
                }
            })
        }

        function deleteDepartment(id){
            Swal.fire({
                icon:'warning',
                title:'Emin Misiniz !',
                text:'Bu Birim\'i Silmek İstediğinize Emin Misiniz' ,
                showConfirmButton:true,
                showCancelButton:true,
                confirmButtonText:'Sil',
                confirmButtonColor:'red',
                cancelButtonText:'İptal',
            }).then((res)=>{
                if(res.isConfirmed){
                    $.ajax({
                        url:'{{route('report.deleteDepartment')}}',
                        type:'POST',
                        data:{
                            id:id,
                            "_token":'{{csrf_token()}}'
                        },
                        success:()=>{
                            Swal.fire({
                                icon:'success',
                                title:'Başarılı',
                                showConfirmButton:true,
                                showCancelButton:false,
                                confirmButtonText:'Tamam'

                            })
                            departmentTable.ajax.reload();
                        }
                    })
                }
            })
        }

    </script>
@endsection
