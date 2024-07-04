@extends('dashboard.layouts.master')
@section('title','Reddedilme Şablonları')
@section('content')
    <style>
        .info-modal {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.4);
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

    <div class="widget-content widget-content-area br-6" id="tableArea">
        <div class="table-header" style=" padding-bottom: 10px;">
            <h4>
                <label
                    style="display: flex;margin-top: 2rem; flex-direction: row;justify-content: center; align-items: center;text-align: center">Bilgi:
                    Bu sayfada süper adminin reddetme şablonları listelenmektedir.
                </label>
                <button class="btn btn-primary float-right" onclick="createModalOpen()">Yeni Reddetme Şablonu Oluştur</button>
            </h4>
        </div>
        <div class="table-responsive mb-4">
            <table id="reject-datatable" class="table table-hover" style="width:100%">
                <thead>
                <tr>
                    <th>Id</th>
                    <th>Adı</th>
                    <th>İçerik</th>
                    <th>Güncelle</th>
                    <th>Sil</th>
                </tr>
                </thead>

                <tfoot>
                <tr>
                    <th>Id</th>
                    <th>Adı</th>
                    <th>İçerik</th>
                    <th>Güncelle</th>
                    <th>Sil</th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <!-- createModal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog"
         aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Reddetme Şablonu Oluşturma</h5>
                    <button type="button" class="close" data-dismiss="modal"
                            aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <label for="">Reddetme Şablonu Adı :</label>
                            <input type="text" id="createName" class="form-control" value="">
                        </div>
                        <div class="col-12 mt-2">
                            <label for="">Reddetme Şablonu İçeriği :</label>
                            <textarea id="createContent" maxlength="255" class="form-control" value=""></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger"
                            data-dismiss="modal">Kapat
                    </button>
                    <button type="button" class="btn btn-primary"
                            data-dismiss="modal" onclick="createRejectPattern()">Oluştur
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- createModal End -->
    <!-- updateModal -->
    <div class="modal fade" id="updateModal" tabindex="-1" role="dialog"
         aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Reddetme Şablonu Güncelleme</h5>
                    <button type="button" class="close" data-dismiss="modal"
                            aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <label for="">Reddetme Şablonu Adı :</label>
                            <input type="text" id="updateName" class="form-control" value="">
                        </div>
                        <div class="col-12 mt-2">
                            <label for="">Reddetme Şablonu İçeriği :</label>
                            <textarea  id="updateContent" maxlength="255" class="form-control" value=""></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger"
                            data-dismiss="modal">Kapat
                    </button>
                    <button type="button" class="btn btn-primary"
                            data-dismiss="modal" onclick="updateRejectPattern()">Güncelle
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- updateModal End -->
@endsection
@section('js')
    <script>
        var update_id;
        function createModalOpen(){
            document.getElementById('createName').value="";
            document.getElementById('createContent').value="";
            $('#createModal').modal('show');
        }
        function updateModalOpen(id){
            update_id=id;
            $.ajax({
                url: '{{route('report.fetchUpdateRejectPattern')}}',
                type: 'GET',
                data: {
                    id: id,
                    "_token": '{{csrf_token()}}',
                },
                success:function (e){
                    document.getElementById('updateName').value=e.name;
                    document.getElementById('updateContent').value=e.content;
                }
            })
            $('#updateModal').modal('show');
        }
        let rejectPatternTable = $('#reject-datatable').DataTable({
            order: [
                [0, 'DESC']
            ],
            dom: '<"row"<"col-md-6"B><"col-md-6"f> ><""rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>>',
            processing: true,
            serverSide: true,
            scrollY: true,
            scrollCollapse: true,
            ajax: '{!!route('report.fetchRejectPattern')!!}',
            columns: [
                {data: 'id'},
                {data: 'name'},
                {data: 'content'},
                {data: 'update'},
                {data: 'delete'}

            ],
            columnDefs: [{
                targets: [1,2],
                render: function(data, type, row) {
                    return type === 'display' && data.length > 100 ? data.substr(0, 100) + '…' : data;
                }
            }],
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


        function updateRejectPattern() {
            $.ajax({
                url: '{{route('report.updateRejectPattern')}}',
                type: 'POST',
                data: {
                    id: update_id,
                    name: document.getElementById('updateName').value,
                    content: document.getElementById('updateContent').value,
                    "_token": '{{csrf_token()}}',
                },
                success: (res) => {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Başarılı !',
                            text:res.content,
                            confirmButtonText: 'Tamam',
                        })
                    }
                    rejectPatternTable.ajax.reload()
                },
                error: () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata !',
                        text: 'Bir Hata Oluştu !',
                        confirmButtonText: 'Tamam',
                    })
                }
            })
        }

        function createRejectPattern() {
            $.ajax({
                url: '{{route('report.createRejectPattern')}}',
                type: 'POST',
                data: {
                    name: document.getElementById('createName').value,
                    content: document.getElementById('createContent').value,
                    "_token": '{{csrf_token()}}',
                },
                success: (res) => {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Başarılı !',
                            text:res.content,
                            confirmButtonText: 'Tamam',
                        })
                    }
                    rejectPatternTable.ajax.reload()
                },
                error: () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata !',
                        text: 'Bir Hata Oluştu !',
                        confirmButtonText: 'Tamam',
                    })
                }
            })
        }

        function deleteRejectPattern(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Emin Misiniz ?',
                text: 'Bu reddetme şablonunu silmek istediğinize emin misiniz ?',
                showConfirmButton: true,
                showCancelButton: true,
                confirmButtonText: 'Sil',
                confirmButtonColor: '#f21818',
                cancelButtonText: 'İptal Et',
                cancelButtonClass: 'btn btn-primary'
            }).then((response) => {
                if (response.isConfirmed) {
                    $.ajax({
                        url: '{{route('report.deleteRejectPattern')}}',
                        type: 'POST',
                        data: {
                            id: id,
                            "_token": '{{csrf_token()}}',
                        },
                        success: (res) => {
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Başarılı !',
                                    text:res.content,
                                    confirmButtonText: 'Tamam',
                                })
                            }
                            rejectPatternTable.ajax.reload()
                        },
                        error: () => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Hata !',
                                text: 'Bir Hata Oluştu !',
                                confirmButtonText: 'Tamam',
                            })
                        }
                    })
                }
            })
        }


    </script>
@endsection

