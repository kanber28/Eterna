@extends('dashboard.layouts.master')
@section('title','Çek Gönder')
@section('style')
    <link rel="stylesheet" href="{{asset('ltr/')}}/plugins/maps/leaflet-map/leaflet.css"/>
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
                                <!-- Modal -->
                                <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Mahalle Oluştur</h5>
                                                <button onclick="closeCreateModal()" type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row" style="display: flex; width: 100%;justify-content: center">
                                                    <div class="col-12" style="display: flex;justify-content: center">
                                                        <div id="map" style="height: 300px;width: 100%"></div>
                                                    </div>
                                                    <div class="mt-3 row">
                                                        <div class="col-md-6 col-lg-6 col-sm-12">
                                                            <label for="">X Ekseni</label>
                                                            <input type="text" class="form-control"  id="x_loc" name="x_loc">
                                                        </div>
                                                        <div class="col-md-6 col-lg-6 col-sm-12">
                                                            <label for="">Y Ekseni</label>
                                                            <input type="text" class="form-control"  id="y_loc" name="y_loc">
                                                        </div>
                                                    </div>
                                                    <div class="row" style="justify-content: center; width: 100%; margin-top: 20px">
                                                        <div class="col-md-3 col-lg-3 col-sm-3">
                                                            <button class="btn btn-primary" onclick="flyToCreateMap()">Konuma Git</button>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-12 mt-3">
                                                        <label for="">Mahalle İsmi : </label>
                                                        <input type="text" class="form-control" name="neighbourhoodName" id="neighbourhoodName" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button onclick="createNeighbourhood()" class="btn btn-primary">Oluştur</button>
                                                <button onclick="closeCreateModal()" type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Update Modal -->
                                <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Mahalle Güncelle</h5>
                                                <button onclick="closeUpdateModal()" type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row" style="display: flex; width: 100%;justify-content: center">
                                                    <div class="col-12" style="display: flex;justify-content: center" id="updateMapArea">
                                                        <div id="update_map" style="height: 300px;width: 100%"></div>
                                                    </div>
                                                    <div class="mt-3 row">
                                                        <div class="col-md-6 col-lg-6 col-sm-12">
                                                            <label for="">X Ekseni</label>
                                                            <input type="text" class="form-control" id="update_x_loc" name="update_x_loc">
                                                        </div>
                                                        <div class="col-md-6 col-lg-6 col-sm-12">
                                                            <label for="">Y Ekseni</label>
                                                            <input type="text" class="form-control" id="update_y_loc" name="update_y_loc">
                                                        </div>
                                                    </div>
                                                    <div class="row" style="justify-content: center; width: 100%; margin-top: 20px">
                                                        <div class="col-md-3 col-lg-3 col-sm-3">
                                                            <button class="btn btn-primary" onclick="flyToUpdateMap()">Konuma Git</button>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-12">
                                                        <label for="">Mahalle İsmi : </label>
                                                        <input type="hidden" id="updateNeighbourhoodNameId">
                                                        <input type="text" class="form-control" name="updateNeighbourhoodName" id="updateNeighbourhoodName">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button onclick="updateNeighbourhood()" class="btn btn-primary">Güncelle</button>
                                                <button onclick="closeUpdateModal()" type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="widget-content widget-content-area br-6" id="tableArea">
                                    <div style="display: block;min-height: 70px">
                                        <div style="display: flex;position: relative;">
                                            <div style="display: flex;position: absolute;right:5px;top:2px">
                                                <button onclick="createModal()" class="btn btn-primary">Yeni Mahalle Oluştur</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-header" >
                                        <h4>
                                            <label style="display: flex;margin-top: 2rem; flex-direction: row;justify-content: center; align-items: center;text-align: center">Bu sayfada mahalleler kısmından da mahalleleri listeleyebiliyor, silebiliyor ve yeni mahalle oluşturabiliyoruz.</label>
                                        </h4>
                                    <div class="table-responsive mb-4">
                                        <table id="export-dt" class="table table-hover" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>id</th>
                                                <th>İsim</th>
                                                <th>Güncelle</th>
                                                <th>Sil</th>
                                            </tr>
                                            </thead>

                                            <tfoot>
                                            <tr>
                                                <th>id</th>
                                                <th>İsim</th>
                                                <th>Güncelle</th>
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
    <script src="{{asset('ltr')}}/plugins/maps/leaflet-map/leaflet.js"></script>
    <script src="{{asset('ltr')}}/assets/js/maps/us-states.js"></script>
    <script src="{{asset('ltr')}}/assets/js/maps/eu-countries.js"></script>
    <script src="{{asset('ltr')}}/assets/js/maps/leaflet-map.js"></script>
            <script>
                let myBigMap = 0;
                $(document).ready(()=>{
                })

                function createMap(){
                    setTimeout(()=>{
                        myBigMap = L.map('map').setView([40.966340, 29.158118], 11);
                        L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFsdGVwZW13ZWIiLCJhIjoiY2xmbWgxeTM0MGJ6ZTNxcDZ0ZXVzaGhtdiJ9.uWLmgsTZ1YP1_5-3rV4L8Q', {
                            maxZoom: 22,
                            id: 'mapbox/streets-v11',
                            tileSize: 512,
                            zoomOffset: -1
                        }).addTo(myBigMap);
                        let popup = L.popup();
                        function onMapClick(e) {
                            document.getElementById('x_loc').value = e.latlng.lat
                            document.getElementById('y_loc').value = e.latlng.lng
                            popup
                                .setLatLng(e.latlng)
                                .setContent("Tıklanan Yerin Konumu " + e.latlng.toString())
                                .openOn(myBigMap);
                        }
                        myBigMap.on('click', onMapClick);
                    }, 400)
                }

                function flyToCreateMap(){
                    let lat = document.getElementById('x_loc').value;
                    let lng = document.getElementById('y_loc').value;

                    if (lat != "" && lng != ""){
                        myBigMap.flyTo([lat, lng], 16)
                    }
                }
            </script>
    <script>
        let neighbourhoodTable = $('#export-dt').DataTable( {
            order: [
                [0,'DESC']
            ],
            dom: '<"row"<"col-md-6"B><"col-md-6"f> ><""rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>>',
            "scrollX": true,    // Enable horizontal scroll
            "scrollCollapse": true, // Collapse the table when it's smaller than the viewport
            "fixedHeader": true,  // Enable fixed header
            ajax: '{!!route('report.neighbourhoodFetch')!!}',
            columns: [
                {data: 'id'},
                {data:'name'},
                {data:'update'},
                {data:'delete'},
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


        function createNeighbourhood(){
            let neighbourhoodName = document.getElementById('neighbourhoodName').value;
            let lat = document.getElementById('x_loc').value;
            let lng = document.getElementById('y_loc').value;
            $.ajax({
                url:'{{route('report.createNeighbourhood')}}',
                type:'POST',
                data:{
                    neighbourhoodName:neighbourhoodName,
                    "_token":'{{csrf_token()}}',
                    lat:lat,
                    lng:lng,
                },
                success:()=>{
                    $('#createModal').modal('hide');
                    Swal.fire({
                        icon:'success',
                        title:'Başarılı',
                        confirmButtonText: "Tamam"
                    })
                    neighbourhoodTable.ajax.reload();
                    document.getElementById('neighbourhoodName').value="";
                },

            });

        }



        function closeUpdateModal(){
            $('#updateModal').modal('hide');
        }
        var redIcon = new L.Icon({
            iconUrl: '{{asset('uploads')}}/marker-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [20, 35],
            iconAnchor: [10, 35],
            popupAnchor: [1, -34],
            shadowSize: [10, 10]
        });
        let updateMap
        function createUpdateMap(lat, lng){
            document.getElementById('update_map').remove();
            document.getElementById('updateMapArea').innerHTML = '<div id="update_map" style="height: 300px;width: 100%"></div>'

            setTimeout(()=>{
                if  (lat != "" && lng != ""){
                    updateMap = L.map('update_map').setView([lat, lng], 11);
                }
                else {
                    updateMap = L.map('update_map').setView([40.966340, 29.158118], 11);
                }
                L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFsdGVwZW13ZWIiLCJhIjoiY2xmbWgxeTM0MGJ6ZTNxcDZ0ZXVzaGhtdiJ9.uWLmgsTZ1YP1_5-3rV4L8Q', {
                    maxZoom: 22,
                    id: 'mapbox/streets-v11',
                    tileSize: 512,
                    zoomOffset: -1
                }).addTo(updateMap);
                let popup = L.popup();
                function onMapClick(e) {
                    document.getElementById('update_x_loc').value = e.latlng.lat
                    document.getElementById('update_y_loc').value = e.latlng.lng
                    popup
                        .setLatLng(e.latlng)
                        .setContent("Tıklanan Yerin Konumu " + e.latlng.toString())
                        .openOn(updateMap);
                }
                updateMap.on('click', onMapClick);
                if (lat != "" && lng != ""){
                    L.marker([lat, lng],{icon: redIcon}).addTo(updateMap)
                }

            }, 300)

        }
        function flyToUpdateMap(){
            let lat = document.getElementById('update_x_loc').value;
            let lng = document.getElementById('update_y_loc').value;
            updateMap.flyTo([lat, lng], 16)
        }
        function updateNeighbourhoodModal(id){
            $.ajax({
                url:'{{route('report.getNeighbourhood')}}',
                type:'GET',
                data:{
                    id:id,
                },
                success:(response)=>{
                    document.getElementById('updateNeighbourhoodNameId').value = response.id;
                    document.getElementById('updateNeighbourhoodName').value = response.name;
                    document.getElementById('updateNeighbourhoodName').value = response.name;
                    document.getElementById('update_x_loc').value = response.lat;
                    document.getElementById('update_y_loc').value = response.lng;
                    $('#updateModal').modal('toggle');
                    createUpdateMap(response.lat, response.lng)
                }
            })
        }



        function updateNeighbourhood(){
            let id = document.getElementById('updateNeighbourhoodNameId').value;
            let name = document.getElementById('updateNeighbourhoodName').value;
            let lat = document.getElementById('update_x_loc').value;
            let lng = document.getElementById('update_y_loc').value;
            $.ajax({
                url:'{{route('report.updateNeighbourhood')}}',
                type:'POST',
                data:{
                    id:id,
                    neighbourhoodName:name,
                    "_token":'{{csrf_token()}}',
                    lat:lat,
                    lng:lng,
                },
                success:()=>{
                    $('#updateModal').modal('hide');
                    Swal.fire({
                        icon:'success',
                        title:'Başarılı',
                        confirmButtonText: "Tamam"
                    })
                    neighbourhoodTable.ajax.reload();
                }
            })
        }

        function deleteNeighbourhood(id){
            Swal.fire({
                icon:"warning",
                title:"Emin Misiniz !",
                text:'Bu Mahalleyi Silmek İstediğinize Emin Misiniz?',
                showCancelButton:true,
                showConfirmButton:true,
                confirmButtonText:'Sil',
                cancelButtonText:'İptal',
                confirmButtonColor:'#f71414'
            }).then((response)=>{
                if (response.isConfirmed){
                    $.ajax({
                        url:'{{route('report.deleteNeighbourhood')}}',
                        type:'POST',
                        data:{
                            id:id,
                            "_token":'{{csrf_token()}}',
                        },
                        success:()=>{
                            Swal.fire({
                                icon:'success',
                                title:'Başarılı',
                                confirmButtonText:'Tamam',
                                showCancelButton:false,
                                showConfirmButton:true,
                            })
                            neighbourhoodTable.ajax.reload();
                        }
                    })
                }
            })
        }

        function createModal(){
            if (myBigMap == 0){
                createMap()
            }
            $('#createModal').modal('toggle');
        }
        function closeCreateModal(){
            $('#createModal').modal('hide');
        }

    </script>
@endsection
