@extends('dashboard.layouts.master')
@section('title','Çek Gönder')
@section('content')
    <style>
        .box-border{
            border: 1px solid #d4d4d4;
            border-radius: 5px;
            padding: 5px;
            width: 100%;
            height: 100%;
        }
        .text-box-items {
            padding: 3px;
            margin-top:5px;

        }
        p {
            word-wrap: break-word;
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
                                <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Personel Detay</h5>
                                                <button onclick="closeUpdateModal()" type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row" style="justify-content: center;width: 100%">
                                                    <div class="col-6 text-box-items">
                                                        <div class="box-border">
                                                        <label for="">İsim Soyisim : </label>
                                                        <p id="employeeName"></p>
                                                        <p id="employeeLastName"></p>

                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-box-items">
                                                        <div class="box-border">

                                                        <label for="">Sicil Numarası : </label>
                                                        <p id="registrationNumber"></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-box-items">
                                                        <div class="box-border">

                                                        <label for="">Telefon No : </label>
                                                        <p id="employeePhone"></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-box-items">
                                                        <div class="box-border">

                                                        <label for="">Email : </label>
                                                        <p id="employeeEmail"></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-box-items">
                                                        <div class="box-border">
                                                            <label for="">T.C : </label>
                                                            <p id="employeeTC"></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-box-items">
                                                        <div class="box-border">
                                                            <label for="">Birim : </label>
                                                            <p id="employeeDepartment"></p>
                                                        </div>
                                                    </div>
                                                    <div  style="padding: 0 0 0 0;width: 100%">
                                                        <div style="">
                                                            <select style="height: max-content;" class="form-control" onchange="selectDepartment(this)" name="department" id="department">
                                                                <option value="" disabled selected>Birim Seçiniz</option>
                                                                @foreach(\App\Models\ReportCategory::all() as $category)
                                                                    <option value="{{$category->id}}">{{$category->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button onclick="closeUpdateModal()" type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="widget-content widget-content-area br-6" id="tableArea">
                                    <div class="table-header" >
                                        <h4>
                                            <label style="display: flex;margin-top: 2rem; flex-direction: row;justify-content: center; align-items: center;text-align: center">Bilgi: Kullanıcı birim belirlede mobil personel istenilen (çalışacağı) birime atanmaktadır.</label>
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
                                                <th>Birim</th>
                                                <th>Güncelle</th>
                                            </tr>
                                            </thead>

                                            <tfoot>
                                            <tr>
                                                <th>ID</th>
                                                <th>İSİM</th>
                                                <th>SOYİSİM</th>
                                                <th>SİCİL NUMARASI</th>
                                                <th>BİRİM</th>
                                                <th>GÜNCELLE</th>
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
        function detailModal(id){
            $.ajax({
                url:'{{route('report.getEmployeeInformation')}}',
                type:'GET',
                data:{
                    id:id
                },
                success:(response)=>{
                    $('#detailModal').modal('toggle');
                }
            })
        }
        function closeDetailModal(){
            $('#detailModal').modal('hide');
        }

        function updateModal(id){
            $.ajax({
                url:'{{route('report.getEmployeeInformation')}}',
                type:'GET',
                data:{
                    id:id
                },
                success:(response)=>{
                    document.getElementById('employeeName').innerText = response.firstname;
                    document.getElementById('employeeLastName').innerText = response.lastname;
                    document.getElementById('employeeEmail').innerText = response.email;
                    document.getElementById('employeePhone').innerText = response.phone;
                    document.getElementById('registrationNumber').innerText = response.registration_number;
                    document.getElementById('employeeTC').innerText = response.identification_number
                    if(response.user_employee_category != null){
                        document.getElementById('employeeDepartment').innerText = response.user_employee_category.name
                    }
                    else {
                        document.getElementById('employeeDepartment').innerText = 'Bir Birimde Değil'
                    }
                    document.getElementById('department').setAttribute('data-id', response.id)

                    if(response.user_employee_category != null) {
                        let selectBox = $('#department option')
                        for (let i = 0; i < selectBox.length; i++) {
                            if(selectBox[i].value == response.user_employee_category.id){
                                selectBox[i].setAttribute('selected', true)
                            }
                        }
                    }
                    else {
                        let selectBox = $('#department option')
                        for(let i = 0; i < selectBox.length; i++){
                            selectBox[i].removeAttribute('selected')
                        }
                        selectBox[0].setAttribute('selected', true)
                    }
                    $('#updateModal').modal('toggle');
                }
            })
        }
        function closeUpdateModal(){
            $('#updateModal').modal('hide');
        }

        function selectDepartment(e){
            let id = e.getAttribute('data-id');
            let category_id = document.getElementById('department').value;
            $.ajax({
                url:'{{route('report.assignDepartmentToUser')}}',
                type:'POST',
                data:{
                    id:id,
                    category_id:category_id,
                    "_token":'{{csrf_token()}}'
                },
                success:(response)=>{
                    Swal.fire({
                        icon:'success',
                        title:'Başarılı',
                        showCancelButton:true,
                        showConfirmButton: false,
                        cancelButtonText:'Tamam ',
                    })
                    closeUpdateModal();
                   exportTable.ajax.reload();
                }
            })
        }

    </script>
    <script>
        let exportTable=$('#export-dt').DataTable( {
            order: [
                [0,'DESC']
            ],
            dom: '<"row"<"col-md-6"B><"col-md-6"f> ><""rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>>',
            "scrollX": true,    // Enable horizontal scroll
            "scrollCollapse": true, // Collapse the table when it's smaller than the viewport
            "fixedHeader": true,  // Enable fixed header
            ajax: '{!!route('report.fetchEmployee')!!}',
            columns: [
                {data: 'id'},
                {data: 'firstname'},
                {data: 'lastname'},
                {data: 'registration_number'},
                {data: 'category'},
                {data: 'update'},
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
@endsection
