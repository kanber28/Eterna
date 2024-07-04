@extends('dashboard.layouts.master')
@section('title','Anketler')
@section('style')
    <style>
        .switch__container {
            margin: 30px auto;
        }

        .switch {
            visibility: hidden;
            position: absolute;
            margin-left: -9999px;
        }

        .switch + label {
            display: block;
            position: relative;
            cursor: pointer;
            outline: none;
            user-select: none;
        }

        .switch--shadow + label {
            padding: 2px;
            width: 67px;
            height: 30px;
            background-color: #dddddd;
            border-radius: 60px;
            margin: auto;
        }
        .switch--shadow + label:before,
        .switch--shadow + label:after {
            display: block;
            position: absolute;
            top: 1px;
            left: 1px;
            bottom: 1px;
            content: "";
        }
        .switch--shadow + label:before {
            right: 1px;
            background-color: #f1f1f1;
            border-radius: 60px;
            transition: background 0.4s;
        }
        .switch--shadow + label:after {
            width: 29px;
            background-color: #fff;
            border-radius: 100%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            transition: all 0.4s;
        }
        .switch--shadow:checked + label:before {
            background-color: #8ce196;
        }
        .switch--shadow:checked + label:after {
            transform: translateX(37px);
        }

        /* Estilo Flat */
        .switch--flat + label {
            padding: 2px;
            width: 120px;
            height: 60px;
            background-color: #dddddd;
            border-radius: 60px;
            transition: background 0.4s;
        }
        .switch--flat + label:before,
        .switch--flat + label:after {
            display: block;
            position: absolute;
            content: "";
        }
        .switch--flat + label:before {
            top: 2px;
            left: 2px;
            bottom: 2px;
            right: 2px;
            background-color: #fff;
            border-radius: 60px;
            transition: background 0.4s;
        }
        .switch--flat + label:after {
            top: 4px;
            left: 4px;
            bottom: 4px;
            width: 56px;
            background-color: #dddddd;
            border-radius: 52px;
            transition: margin 0.4s, background 0.4s;
        }
        .switch--flat:checked + label {
            background-color: #8ce196;
        }
        .switch--flat:checked + label:after {
            margin-left: 60px;
            background-color: #8ce196;
        }
    </style>
@endsection
@section('content')
    <div class="layout-px-spacing">
        <div class="layout-top-spacing mb-2">
            <div class="col-md-12">
                <div class="row">
                    <div class="container p-0">
                        <div class="row layout-top-spacing date-table-container">
                            <!-- Datatable with export options -->
                            <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                                <div class="widget-content widget-content-area br-6">
                                    <h4 class="table-header">
                                        Anketler
                                        @if(\Illuminate\Support\Facades\Auth::user()->can('create questionnaire'))
                                            <div style="display: flex;width: 100%;justify-content: end">
                                                <a href="{{route('questionnaire.create.index')}}" class="btn btn-primary">Anket Oluştur</a>
                                            </div>
                                        @endif
                                        <label style="display: flex;padding-top: 2rem; flex-direction: row;justify-content: center; align-items: center;text-align: center">Bilgi: Bu sayfada oluşturulmuş anketlerin bilgilerine ulaşabilirsiniz.</label>
                                        <br>
                                    </h4>

                                    <div class="table-responsive mb-4">
                                        <table id="export-dt" class="table table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>id</th>
                                                    <th>Anket İsmi</th>
                                                    <th>Anket Açıklaması</th>
                                                    <th>Durum</th>
                                                    <th>Cevaplar</th>
                                                    <th>Güncelle</th>
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
        $('#export-dt').DataTable({
            order: [
                [0, 'DESC']
            ],
            dom: '<"row"<"col-md-6"B><"col-md-6"f> ><""rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>>',
            "scrollX": true,    // Enable horizontal scroll
            "scrollCollapse": true, // Collapse the table when it's smaller than the viewport
            "fixedHeader": true,  // Enable fixed header
            ajax: '{!!route('questionnaire.fetch')!!}',
            columns: [
                {data: 'id'},
                {data: 'name'},
                {data: 'description'},
                {data: 'is_active'},
                {data: 'responses'},
                {data: 'update'},
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

        function updateActive(id, e){
            let status = 0;

            if (e.checked){
                status = 1;
            }
            else {
                status = 0;
            }

            $.ajax({
                url:'{{route('questionnaire.changeStatus')}}',
                type:'POST',
                data:{
                    id:id,
                    status:status,
                    _token:'{{csrf_token()}}'
                }
            })
        }


    </script>
@endsection

