@extends('dashboard.layouts.master')
@section('title','Çek Gönder')
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
                                    <div class="table-header">
                                    </div>
                                    <div class="table-responsive mb-4">
                                        <table id="export-dt" class="table table-hover" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>id</th>
                                                <th>Adres</th>
                                                <th>Açıklama</th>
                                                <th>Ata</th>
                                                <th>Reddet</th>
                                            </tr>
                                            </thead>

                                            <tfoot>
                                            <tr>
                                                <th>id</th>
                                                <th>Adres</th>
                                                <th>Açıklama</th>
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
        $('#export-dt').DataTable( {
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
                {data:'address'},
                {data:'description'},
                {data:'assign'},
                {data:'reject'},

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
        } );
    </script>
@endsection
