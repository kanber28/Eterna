@extends('dashboard.layouts.master')
@section('title','İstatistikler')
@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <style>
        tbody tr:hover {
            background-color: rgb(121,121,121, 0.2);
        }
        .statistic-area{
            display: none;
            height: 300px;
            width: 100%;
        }

        .tableArea {
        }
    </style>
    <div class="layout-top-spacing mb-2">
        <div class="row" style="justify-content: center">
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12 layout-spacing">
                <div class="widget quick-category">
                    <div class="quick-category-head" style="width: 100%">
                                    <span class="quick-category-icon qc-primary rounded-circle">
                                        <i class="las la-user"></i>
                                    </span>
                        <div class="ml-auto">
                        </div>
                    </div>
                    <div class="quick-category-content">
                        <h3 >{{$employee->firstname}} {{$employee->lastname}}</h3>

                        <p class="font-17 text-primary mb-0">Personel İsmi</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12 layout-spacing">
                <div class="widget quick-category">
                    <div class="quick-category-head" style="width: 100%">
                                    <span class="quick-category-icon qc-primary rounded-circle">
                                        <i class="las la-wrench"></i>
                                    </span>
                        <div style="padding: 8px">
                            <select onchange="getAverageTime()" class="form-control" name="averageType" id="averageType" style="max-width: max-content;float: right">
                                <option value="0">Son 7 Gün</option>
                                <option value="1">Bu Ay</option>
                                <option selected value="2">Tüm Zamanlar</option>
                            </select>
                        </div>
                        <div class="ml-auto">
                        </div>
                    </div>
                    <div class="quick-category-content">
                        <h3 id="averageTime">{{$averageSolveTime}}</h3>
                        <p class="font-17 text-primary mb-0">Ortalama Çek Gönder Bitirme Süresi</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12 layout-spacing">
                <a class="widget quick-category">
                    <div class="quick-category-head">
                                    <span class="quick-category-icon qc-success-teal rounded-circle">
                                        <i class="las la-history"></i>
                                    </span>
                        <div style="padding: 8px">
                            <select onchange="getTotalMission()" class="form-control" name="missionCount" id="missionCount" style="max-width: max-content;float: right">
                                <option value="0">Son 7 Gün</option>
                                <option value="1">Bu Ay</option>
                                <option selected value="2">Tüm Zamanlar</option>
                            </select>
                        </div>
                        <div class="ml-auto">
                        </div>
                    </div>
                    <div class="quick-category-content">
                        <h3 id="totalMissionCount">{{$missions->count() + $rejectedMission}}</h3>
                        <p class="font-17 text-success-teal mb-0">Personele Atanan Toplam Görev Sayısı</p>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12 layout-spacing">
                <a class="widget quick-category">
                    <div class="quick-category-head">
                                    <span class="quick-category-icon qc-warning rounded-circle">
                                        <i class="las la-ban"></i>
                                    </span>
                        <div style="padding: 8px">
                            <select onchange="getRejectedMission()" class="form-control" name="rejectedMissionCount" id="rejectedMissionCount" style="max-width: max-content;float: right">
                                <option value="0">Son 7 Gün</option>
                                <option value="1">Bu Ay</option>
                                <option selected value="2">Tüm Zamanlar</option>
                            </select>
                        </div>
                        <div class="ml-auto">
                        </div>
                    </div>
                    <div class="quick-category-content">
                        <h3 id="rejectedCount">{{$rejectedMission}}</h3>
                        <p class="font-17 text-warning mb-0">Reddedilen Görev Sayısı</p>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="container p-0">
                    <div class="row layout-top-spacing date-table-container">
                        <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                            <div class="card" style="padding: 2rem; margin: 10px">
                                <div class="card-header">
                                    <h4>Çek Gönder Çözüm İstatistikleri</h4>
                                </div>
                                <div class="card-body">
                                    <div class="tableArea">
                                        <table class="table" id="missionTable">
                                            <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Görev Açıklaması</th>
                                                <th>Çözüm Süresi</th>
                                                <th>Görev Detayı</th>
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>

                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                                <div class="widget widget-chart-one">
                                    <div class="widget-heading" style="display: flex;justify-content: space-between">
                                        <h5 class="">Aylık çek gönder performansı</h5>
                                        <select onchange="changeYearOfCekGonder()" class="form-control" name="yearOfCekGonder" id="yearOfCekGonder" style="width: 10%">

                                        </select>
                                    </div>
                                    <div class="widget-content">
                                        <div class="tabs tab-content">
                                            <div id="content_1" class="tabcontent">
                                                <div id="cekGonderTable"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                                <div class="widget widget-chart-one">
                                    <div class="widget-heading" style="display: flex;justify-content: space-between">
                                        <h5 class="">Ayarlanabilir Günlük İstatistik</h5>
                                    </div>
                                    <div class="widget-content">
                                        <div class="tabs tab-content">
                                            <div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <label for="start_time">Başlangıç Tarihi</label>
                                                            <input class="form-control" type="date" name="start_time" id="start_time">
                                                        </div>
                                                        <div class="col-4">
                                                            <label for="end_time">Bitiş Tarihi Tarihi</label>
                                                            <input class="form-control" type="date" name="end_time" id="end_time">
                                                        </div>
                                                        <div class="col-3">
                                                            <label for="" style="color: white">block</label>
                                                            <button onclick="createCustomStatistic()" class="btn btn-primary form-control">Göster</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="content_2" class="tabcontent">
                                                <div id="customStatistic"></div>
                                            </div>
                                        </div>
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
        let now = new Date()
        let year = now.getFullYear();
        let dif = year - 2022;
        for(let i = 0; i <= dif ; i++){
            if(year-i == year){
                document.getElementById('yearOfCekGonder').innerHTML += '<option value="'+ (year-i) +'" selected>'+ (year-i) + '</option>';
            }
            else {
                document.getElementById('yearOfCekGonder').innerHTML += '<option value="'+year-i+'"+>'+year-i+'</option>';
            }
        }

        $('#missionTable').DataTable({
            order: [
                [0, 'DESC']
            ],
            dom: '<"row"<"col-md-6"B><"col-md-6"f> ><""rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>>',
            processing: true,
            serverSide: true,
            scrollY: true,
            scrollCollapse: true,
            ajax: '{!!route('fetchEmployeeDetail', $id)!!}',
            columns: [
                {data: 'id'},
                {data: 'description'},
                {data: 'diff'},
                {data: 'detail'},
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


        function getAverageTime(){
            let type = document.getElementById('averageType').value;

            $.ajax({
                url:'{{route('getAverageTime')}}',
                type:'GET',
                data:{
                    type:type,
                    id:'{{$id}}'
                },
                success:(response)=>{
                    document.getElementById('averageTime').innerText = response
                }
            })
        }
        function getTotalMission(){
            let type = document.getElementById('missionCount').value;

            $.ajax({
                url:'{{route('getMissionCount')}}',
                type:'GET',
                data:{
                    type:type,
                    id:'{{$id}}'
                },
                success:(response)=>{
                    document.getElementById('totalMissionCount').innerText = (response.mission + response.rejectedMission)
                }
            })
        }

        function getRejectedMission(){
            let type = document.getElementById('rejectedMissionCount').value;

            $.ajax({
                url:'{{route('getMissionCount')}}',
                type:'GET',
                data:{
                    type:type,
                    id:'{{$id}}'
                },
                success:(response)=>{
                    document.getElementById('rejectedCount').innerText = response.rejectedMission
                }
            })
        }
    </script>
    <script>
        function createCustomStatistic(){
            let start = document.getElementById('start_time').value;
            let end = document.getElementById('end_time').value;
            if (start > end){
                Swal.fire({
                    icon:'warning',
                    title:'Hata !',
                    text:'Başlangıç Tarihi Bitiş Tarihinden Büyük Olamaz !'
                })

                return false;
            }
            $.ajax({
                url:'{{route('getPersonelCustomStatistic')}}',
                type:'GET',
                data:{

                    start:start,
                    end:end,
                    id:{{$id}}
                },
                success:(response)=>{
                    document.getElementById('customStatistic').remove()
                    document.getElementById('content_2').innerHTML = '<div id="customStatistic"></div>'
                    let fakeMission = [];
                    let successMission = [];
                    let rejectedMission = [];
                    let totalCekGonder = [];

                    for(let i = 0; i < response.successMission.length; i++){
                        successMission.push(response.successMission[i])
                    }
                    for(let i = 0; i < response.fakeMission.length; i++){
                        fakeMission.push(response.fakeMission[i])
                    }
                    for(let i = 0; i < response.rejectedCekGonder.length; i++){
                        rejectedMission.push(response.rejectedCekGonder[i])
                    }
                    for(let i = 0; i < response.totalCekGonder.length; i++){
                        totalCekGonder.push(response.totalCekGonder[i])
                    }

                    var options1 = {
                        chart: {
                            fontFamily: 'Poppins, sans-serif',
                            height: 350,
                            type: 'area',
                            zoom: {
                                enabled: false
                            },
                            dropShadow: {
                                enabled: true,
                                opacity: 0.2,
                                blur: 5,
                                left: -7,
                                top: 22
                            },
                            toolbar: {
                                show: true
                            },
                            events: {
                                mounted: function(ctx, config) {
                                    const highest1 = ctx.getHighestValueInSeries(0);
                                    const highest2 = ctx.getHighestValueInSeries(1);
                                    ctx.addPointAnnotation({
                                        x: new Date(ctx.w.globals.seriesX[0][ctx.w.globals.series[0].indexOf(highest1)]).getTime(),
                                        y: highest1,
                                        label: {
                                            style: {
                                                cssClass: 'd-none'
                                            }
                                        },

                                    })
                                    ctx.addPointAnnotation({
                                        x: new Date(ctx.w.globals.seriesX[1][ctx.w.globals.series[1].indexOf(highest2)]).getTime(),
                                        y: highest2,
                                        label: {
                                            style: {
                                                cssClass: 'd-none'
                                            }
                                        },

                                    })
                                },
                            }
                        },
                        colors: ['#0fa8f5', '#2ef26c', '#faf62a', '#f53131'],
                        dataLabels: {
                            enabled: false
                        },
                        markers: {
                            discrete: [{
                                seriesIndex: 0,
                                dataPointIndex: 7,
                                fillColor: '#000',
                                strokeColor: '#000',
                                size: 5
                            }, {
                                seriesIndex: 2,
                                dataPointIndex: 11,
                                fillColor: '#000',
                                strokeColor: '#000',
                                size: 4
                            }]
                        },
                        toolbar: {
                            show: true
                        },
                        subtitle: {
                            text: 'Günlük Grafik',
                            align: 'left',
                            margin: 0,
                            offsetX: -10,
                            offsetY: 35,
                            floating: false,
                            style: {
                                fontSize: '14px',
                                color:  '#888ea8'
                            }
                        },
                        title: {
                            text: 'Performans',
                            align: 'left',
                            margin: 0,
                            offsetX: -10,
                            offsetY: 0,
                            floating: false,
                            style: {
                                fontSize: '25px',
                                color:  '#515365'
                            },
                        },
                        stroke: {
                            show: true,
                            curve: 'smooth',
                            width: 2,
                            lineCap: 'square'
                        },
                        series: [{
                            name: 'Toplam Atanan',
                            data: totalCekGonder,
                        },
                            {
                                name: 'Başarılı',
                                data: successMission,
                            },
                            {
                                name: 'Asılsız',
                                data: fakeMission,
                            },
                            {
                                name: 'Reddedilen',
                                data: rejectedMission,
                            }
                        ],
                        labels:  response.dates,
                        xaxis: {
                            axisBorder: {
                                show: false
                            },
                            axisTicks: {
                                show: false
                            },
                            crosshairs: {
                                show: true
                            },
                            labels: {
                                rotate: -90,
                                offsetX: 0,
                                offsetY: 3,
                                style: {
                                    fontSize: '12px',
                                    fontFamily: 'Poppins, sans-serif',
                                    cssClass: 'apexcharts-xaxis-title',
                                },
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: function(value, index) {
                                    return value
                                },
                                offsetX: -22,
                                offsetY: 0,
                                style: {
                                    fontSize: '12px',
                                    fontFamily: 'Poppins, sans-serif',
                                    cssClass: 'apexcharts-yaxis-title',
                                },
                            }
                        },
                        grid: {
                            borderColor: '#e0e6ed',
                            strokeDashArray: 8,
                            xaxis: {
                                lines: {
                                    show: true
                                }
                            },
                            yaxis: {
                                lines: {
                                    show: true,
                                }
                            },
                            padding: {
                                top: 0,
                                right: 0,
                                bottom: 0,
                                left: -10
                            },
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right',
                            offsetY: -50,
                            fontSize: '13px',
                            fontFamily: 'Poppins, sans-serif',
                            markers: {
                                width: 10,
                                height: 10,
                                strokeWidth: 0,
                                strokeColor: '#fff',
                                fillColors: undefined,
                                radius: 12,
                                onClick: undefined,
                                offsetX: 0,
                                offsetY: 0
                            },
                            itemMargin: {
                                horizontal: 0,
                                vertical: 20
                            }
                        },
                        tooltip: {
                            theme: 'dark',
                            marker: {
                                show: true,
                            },
                            x: {
                                show: false,
                            }
                        },
                        fill: {
                            type:"gradient",
                            gradient: {
                                type: "vertical",
                                shadeIntensity: 1,
                                inverseColors: !1,
                                opacityFrom: .28,
                                opacityTo: .05,
                                stops: [45, 100]
                            }
                        },
                        responsive: [{
                            breakpoint: 575,
                            options: {
                                legend: {
                                    offsetY: -30,
                                },
                            },
                        }]
                    }
                    if (response.dateDiff > 60) {
                        options1.xaxis.labels.show = false;
                        options1.xaxis.show = false;
                    }
                    var chart1 = new ApexCharts(
                        document.querySelector("#customStatistic"),
                        options1
                    );
                    chart1.render();
                }
            })

        }
    </script>

    <script>
        function openStatistic(id){
            $('#statisticArea').slideToggle()
            statisticArea.innerText = id;
        }

        $.ajax({
            url:'{{route('getPersonelTakeSendStatistic')}}',
            type:'GET',
            data:{
                year:document.getElementById('yearOfCekGonder').value,
                id:{{$id}}
            },
            success:(response)=>{
                let fakeMission = [];
                let successMission = [];
                let rejectedMission = [];
                let totalCekGonder = [];

                for(let i = 0; i < response.successMission.length; i++){
                    successMission.push(response.successMission[i])
                }
                for(let i = 0; i < response.fakeMission.length; i++){
                    fakeMission.push(response.fakeMission[i])
                }
                for(let i = 0; i < response.rejectedCekGonder.length; i++){
                    rejectedMission.push(response.rejectedCekGonder[i])
                }
                for(let i = 0; i < response.totalCekGonder.length; i++){
                    totalCekGonder.push(response.totalCekGonder[i])
                }

                var options1 = {
                    chart: {
                        fontFamily: 'Poppins, sans-serif',
                        height: 350,
                        type: 'area',
                        zoom: {
                            enabled: false
                        },
                        dropShadow: {
                            enabled: true,
                            opacity: 0.2,
                            blur: 5,
                            left: -7,
                            top: 22
                        },
                        toolbar: {
                            show: true
                        },
                        events: {
                            mounted: function(ctx, config) {
                                const highest1 = ctx.getHighestValueInSeries(0);
                                const highest2 = ctx.getHighestValueInSeries(1);
                                ctx.addPointAnnotation({
                                    x: new Date(ctx.w.globals.seriesX[0][ctx.w.globals.series[0].indexOf(highest1)]).getTime(),
                                    y: highest1,
                                    label: {
                                        style: {
                                            cssClass: 'd-none'
                                        }
                                    },

                                })
                                ctx.addPointAnnotation({
                                    x: new Date(ctx.w.globals.seriesX[1][ctx.w.globals.series[1].indexOf(highest2)]).getTime(),
                                    y: highest2,
                                    label: {
                                        style: {
                                            cssClass: 'd-none'
                                        }
                                    },

                                })
                            },
                        }
                    },
                    colors: ['#0fa8f5', '#2ef26c', '#faf62a', '#f53131'],
                    dataLabels: {
                        enabled: false
                    },
                    markers: {
                        discrete: [{
                            seriesIndex: 0,
                            dataPointIndex: 7,
                            fillColor: '#000',
                            strokeColor: '#000',
                            size: 5
                        }, {
                            seriesIndex: 2,
                            dataPointIndex: 11,
                            fillColor: '#000',
                            strokeColor: '#000',
                            size: 4
                        }]
                    },
                    toolbar: {
                        show: true
                    },
                    subtitle: {
                        text: 'Aylık Grafik',
                        align: 'left',
                        margin: 0,
                        offsetX: -10,
                        offsetY: 35,
                        floating: false,
                        style: {
                            fontSize: '14px',
                            color:  '#888ea8'
                        }
                    },
                    title: {
                        text: 'Performans',
                        align: 'left',
                        margin: 0,
                        offsetX: -10,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: '25px',
                            color:  '#515365'
                        },
                    },
                    stroke: {
                        show: true,
                        curve: 'smooth',
                        width: 2,
                        lineCap: 'square'
                    },
                    series: [{
                        name: 'Toplam Atanan',
                        data: totalCekGonder,
                    },
                        {
                            name: 'Başarılı',
                            data: successMission,
                        },
                        {
                            name: 'Asılsız',
                            data: fakeMission,
                        },
                        {
                            name: 'Reddedilen',
                            data: rejectedMission,
                        }
                    ],
                    labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
                    xaxis: {
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                        crosshairs: {
                            show: true
                        },
                        labels: {
                            offsetX: 0,
                            offsetY: 5,
                            style: {
                                fontSize: '12px',
                                fontFamily: 'Poppins, sans-serif',
                                cssClass: 'apexcharts-xaxis-title',
                            },
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(value, index) {
                                return value
                            },
                            offsetX: -22,
                            offsetY: 0,
                            style: {
                                fontSize: '12px',
                                fontFamily: 'Poppins, sans-serif',
                                cssClass: 'apexcharts-yaxis-title',
                            },
                        }
                    },
                    grid: {
                        borderColor: '#e0e6ed',
                        strokeDashArray: 8,
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true,
                            }
                        },
                        padding: {
                            top: 0,
                            right: 0,
                            bottom: 0,
                            left: -10
                        },
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        offsetY: -50,
                        fontSize: '13px',
                        fontFamily: 'Poppins, sans-serif',
                        markers: {
                            width: 10,
                            height: 10,
                            strokeWidth: 0,
                            strokeColor: '#fff',
                            fillColors: undefined,
                            radius: 12,
                            onClick: undefined,
                            offsetX: 0,
                            offsetY: 0
                        },
                        itemMargin: {
                            horizontal: 0,
                            vertical: 20
                        }
                    },
                    tooltip: {
                        theme: 'dark',
                        marker: {
                            show: true,
                        },
                        x: {
                            show: false,
                        }
                    },
                    fill: {
                        type:"gradient",
                        gradient: {
                            type: "vertical",
                            shadeIntensity: 1,
                            inverseColors: !1,
                            opacityFrom: .28,
                            opacityTo: .05,
                            stops: [45, 100]
                        }
                    },
                    responsive: [{
                        breakpoint: 575,
                        options: {
                            legend: {
                                offsetY: -30,
                            },
                        },
                    }]
                }

                var chart1 = new ApexCharts(
                    document.querySelector("#cekGonderTable"),
                    options1
                );
                chart1.render();
            }
        })

        function changeYearOfCekGonder() {
            let year = document.getElementById('yearOfCekGonder').value;
            document.getElementById('cekGonderTable').remove()
            document.getElementById('content_1').innerHTML = '<div id="cekGonderTable"></div>'

            $.ajax({
                url:'{{route('getPersonelTakeSendStatistic')}}',
                type:'GET',
                data:{
                    year:year,
                    id:{{$id}}
                },
                success:(response)=>{
                    let fakeMission = [];
                    let successMission = [];
                    let rejectedMission = [];
                    let totalCekGonder = [];

                    for(let i = 0; i < response.successMission.length; i++){
                        successMission.push(response.successMission[i])
                    }
                    for(let i = 0; i < response.fakeMission.length; i++){
                        fakeMission.push(response.fakeMission[i])
                    }
                    for(let i = 0; i < response.rejectedCekGonder.length; i++){
                        rejectedMission.push(response.rejectedCekGonder[i])
                    }
                    for(let i = 0; i < response.totalCekGonder.length; i++){
                        totalCekGonder.push(response.totalCekGonder[i])
                    }

                    var options1 = {
                        chart: {
                            fontFamily: 'Poppins, sans-serif',
                            height: 350,
                            type: 'area',
                            zoom: {
                                enabled: false
                            },
                            dropShadow: {
                                enabled: true,
                                opacity: 0.2,
                                blur: 5,
                                left: -7,
                                top: 22
                            },
                            toolbar: {
                                show: true
                            },
                            events: {
                                mounted: function(ctx, config) {
                                    const highest1 = ctx.getHighestValueInSeries(0);
                                    const highest2 = ctx.getHighestValueInSeries(1);
                                    ctx.addPointAnnotation({
                                        x: new Date(ctx.w.globals.seriesX[0][ctx.w.globals.series[0].indexOf(highest1)]).getTime(),
                                        y: highest1,
                                        label: {
                                            style: {
                                                cssClass: 'd-none'
                                            }
                                        },

                                    })
                                    ctx.addPointAnnotation({
                                        x: new Date(ctx.w.globals.seriesX[1][ctx.w.globals.series[1].indexOf(highest2)]).getTime(),
                                        y: highest2,
                                        label: {
                                            style: {
                                                cssClass: 'd-none'
                                            }
                                        },

                                    })
                                },
                            }
                        },
                        colors: ['#0fa8f5', '#2ef26c', '#faf62a', '#f53131'],
                        dataLabels: {
                            enabled: false
                        },
                        markers: {
                            discrete: [{
                                seriesIndex: 0,
                                dataPointIndex: 7,
                                fillColor: '#000',
                                strokeColor: '#000',
                                size: 5
                            }, {
                                seriesIndex: 2,
                                dataPointIndex: 11,
                                fillColor: '#000',
                                strokeColor: '#000',
                                size: 4
                            }]
                        },
                        toolbar: {
                            show: true
                        },
                        subtitle: {
                            text: 'Aylık Grafik',
                            align: 'left',
                            margin: 0,
                            offsetX: -10,
                            offsetY: 35,
                            floating: false,
                            style: {
                                fontSize: '14px',
                                color:  '#888ea8'
                            }
                        },
                        title: {
                            text: 'Performans',
                            align: 'left',
                            margin: 0,
                            offsetX: -10,
                            offsetY: 0,
                            floating: false,
                            style: {
                                fontSize: '25px',
                                color:  '#515365'
                            },
                        },
                        stroke: {
                            show: true,
                            curve: 'smooth',
                            width: 2,
                            lineCap: 'square'
                        },
                        series: [{
                            name: 'Toplam Atanan',
                            data: totalCekGonder,
                        },
                            {
                                name: 'Başarılı',
                                data: successMission,
                            },
                            {
                                name: 'Asılsız',
                                data: fakeMission,
                            },
                            {
                                name: 'Reddedilen',
                                data: rejectedMission,
                            }
                        ],
                        labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
                        xaxis: {
                            axisBorder: {
                                show: false
                            },
                            axisTicks: {
                                show: false
                            },
                            crosshairs: {
                                show: true
                            },
                            labels: {
                                offsetX: 0,
                                offsetY: 5,
                                style: {
                                    fontSize: '12px',
                                    fontFamily: 'Poppins, sans-serif',
                                    cssClass: 'apexcharts-xaxis-title',
                                },
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: function(value, index) {
                                    return value
                                },
                                offsetX: -22,
                                offsetY: 0,
                                style: {
                                    fontSize: '12px',
                                    fontFamily: 'Poppins, sans-serif',
                                    cssClass: 'apexcharts-yaxis-title',
                                },
                            }
                        },
                        grid: {
                            borderColor: '#e0e6ed',
                            strokeDashArray: 8,
                            xaxis: {
                                lines: {
                                    show: true
                                }
                            },
                            yaxis: {
                                lines: {
                                    show: true,
                                }
                            },
                            padding: {
                                top: 0,
                                right: 0,
                                bottom: 0,
                                left: -10
                            },
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right',
                            offsetY: -50,
                            fontSize: '13px',
                            fontFamily: 'Poppins, sans-serif',
                            markers: {
                                width: 10,
                                height: 10,
                                strokeWidth: 0,
                                strokeColor: '#fff',
                                fillColors: undefined,
                                radius: 12,
                                onClick: undefined,
                                offsetX: 0,
                                offsetY: 0
                            },
                            itemMargin: {
                                horizontal: 0,
                                vertical: 20
                            }
                        },
                        tooltip: {
                            theme: 'dark',
                            marker: {
                                show: true,
                            },
                            x: {
                                show: false,
                            }
                        },
                        fill: {
                            type:"gradient",
                            gradient: {
                                type: "vertical",
                                shadeIntensity: 1,
                                inverseColors: !1,
                                opacityFrom: .28,
                                opacityTo: .05,
                                stops: [45, 100]
                            }
                        },
                        responsive: [{
                            breakpoint: 575,
                            options: {
                                legend: {
                                    offsetY: -30,
                                },
                            },
                        }]
                    }

                    var chart1 = new ApexCharts(
                        document.querySelector("#cekGonderTable"),
                        options1
                    );
                    chart1.render();
                }
            })

        }
    </script>
@endsection
