@extends('dashboard.layouts.master')
@section('title','Çek Gönder')
@section('content')
    <div class="layout-px-spacing">
        <!-- Create Modal !-->
            <div class="modal" id="exampleModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Bildirim Şablonu Oluştur</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12">
                                    <label for="schemaName">Şablon İsmi :</label>
                                    <input type="text" id="schemaName" class="form-control">
                                </div>
                                <div class="col-12 mt-2">
                                    <label for="schemaContent">Şablon İçeriği :</label>
                                    <textarea class="form-control" name="" id="schemaContent" cols="15" rows="5"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="createSchema()">Kaydet</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Update Modal !-->
            <div class="modal" id="updateModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bildirim Şablonu Oluştur</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="schemaID">
                        <div class="row">
                            <div class="col-12">
                                <label for="schemaName">Şablon İsmi :</label>
                                <input type="text" id="schemaNameUpdate" class="form-control">
                            </div>
                            <div class="col-12 mt-2">
                                <label for="schemaContent">Şablon İçeriği :</label>
                                <textarea class="form-control" name="" id="schemaContentUpdate" cols="15" rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="updateSchema()">Kaydet</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="layout-top-spacing mb-2">
            <div class="col-12">
                <div class="row">
                    <div class="container p-0">
                        <div class="row layout-top-spacing date-table-container">
                                <div class="widget-content widget-content-area br-6" style="width: 100%" id="tableArea">
                                    <div class="table-header" >
                                        <h4>
                                            <label style="display: flex;margin-top: 2rem; flex-direction: row;justify-content: center; align-items: center;text-align: center">
                                                Bilgi: Görev sonuçlandırıldığında gönderilecek hazır mesaj şablonlarını listeler.</label></h4>
                                    </div>
                                    <div style="display: flex;width: 100%;justify-content: end">
                                        <div>
                                            <button onclick="openCreateModal()" class="btn btn-info">Oluştur</button>
                                        </div>
                                    </div>
                                    <div class="table-responsive mb-4">
                                        <table id="export-dt" class="table table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>id</th>
                                                    <th>Şablon İsmi</th>
                                                    <th>Şablon Mesajı</th>
                                                    <th>Güncelle</th>
                                                    <th>Sil</th>
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
@endsection
@section('js')
    <script>
        let reportTable = $('#export-dt').DataTable( {
            order: [
                [0,'DESC']
            ],
            dom: '<"row"<"col-md-6"B><"col-md-6"f> ><""rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>>',
            processing: true,
            serverSide: true,
            scrollY:  true,
            scrollCollapse: true,
            ajax: '{!!route('report.fetchNotify')!!}',
            columns: [
                {data: 'id'},
                {data:'name'},
                {data:'content'},
                {data:'update'},
                {data:'delete'},

            ],
            columnDefs: [{
                targets: [1,2],
                render: function(data, type, row) {
                    return type === 'display' && data.length > 100 ? data.substr(0, 100) + '…' : data;
                }
            }],
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


        function openCreateModal(){
            $('#exampleModal').modal('toggle')
        }

        function createSchema(){
            let name = document.getElementById('schemaName').value;
            let content = document.getElementById('schemaContent').value;
            if (name == '' || content == ''){
                Swal.fire({
                    title:'Uyarı',
                    icon:'warning',
                    text:'Lütfen Bütün Alanları Doldurunuz !'
                })

                return false;
            }

            $.ajax({
                url:'{{route('report.createNotifyScheme')}}',
                type:'POST',
                data: {
                    name:name,
                    content:content,
                    _token:'{{csrf_token()}}'
                },
                success:()=>{
                    $('#exampleModal').modal('hide')
                    Swal.fire({
                        title:'Başarılı',
                        icon:'success',
                        text:'Şablon Başarıyla Oluşturuldu !'
                    })
                    reportTable.ajax.reload();
                },
                error:()=>{
                    $('#exampleModal').modal('hide')
                    Swal.fire({
                        title:'Hata !',
                        icon:'error',
                        text:'Bir Hata Oluştu !'
                    })
                    reportTable.ajax.reload();
                }
            })
        }

        function openUpdateModal(id){
            document.getElementById('schemaID').value = id;

            $.ajax({
                url:'{{route('report.getSchema')}}',
                type:'GET',
                data: {
                    id:id
                },
                success:(res)=>{
                    document.getElementById('schemaNameUpdate').value = res.name;
                    document.getElementById('schemaContentUpdate').value = res.content;
                    $('#updateModal').modal('toggle')
                }
            })

        }
        function updateSchema(){
            let name = document.getElementById('schemaNameUpdate').value;
            let content = document.getElementById('schemaContentUpdate').value;
            let id = document.getElementById('schemaID').value;
            if (name == '' || content == ''){
                Swal.fire({
                    title:'Uyarı',
                    icon:'warning',
                    text:'Lütfen Bütün Alanları Doldurunuz !'
                })

                return false;
            }

            $.ajax({
                url:'{{route('report.updateSchema')}}',
                type:'POST',
                data: {
                    name:name,
                    content:content,
                    id:id,
                    _token:'{{csrf_token()}}'
                },
                success:()=>{
                    $('#updateModal').modal('hide')
                    Swal.fire({
                        title:'Başarılı',
                        icon:'success',
                        text:'Şablon Başarıyla Güncellendi !'
                    })
                    reportTable.ajax.reload();
                },
                error:()=>{
                    $('#updateModal').modal('hide')
                    Swal.fire({
                        title:'Hata !',
                        icon:'error',
                        text:'Bir Hata Oluştu !'
                    })
                    reportTable.ajax.reload();
                }
            })
        }

        function deleteSchema(id){

            Swal.fire({
                icon:'warning',
                title:'Emin Misiniz ?',
                text:'Bu Kaydı Silmek İstediğinizden Emin Misiniz ?',
                confirmButtonText:'Sil',
                confirmButton: true,
                confirmButtonColor:'red',
                cancelButton: true,
                cancelButtonText:'İptal',
            }).then((res)=>{
                if (res.isConfirmed){
                    $.ajax({
                        url:'{{route('report.deleteSchema')}}',
                        type:'POST',
                        data:{
                            id:id,
                            _token:'{{csrf_token()}}'
                        },
                        success:()=>{
                            Swal.fire({
                                icon:'success',
                                title:'Başarılı',
                                text:'Şema Silindi'
                            })
                            reportTable.ajax.reload();
                        },
                        error:()=>{
                            Swal.fire({
                                icon:'error',
                                title:'Hata !',
                            })
                        }
                    })
                }
            })

        }

    </script>

@endsection
