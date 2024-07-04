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
                            <form action="{{route('report.exportReportExcel')}}" method="POST" enctype="multipart/form-data" style="width: 100%">
                                @csrf
                                <div class="col-xl-12 col-lg-12 col-sm-12  ">
                                    <div class="widget-content widget-content-area br-6">
                                        <div class="widget-heading" style="display: flex;justify-content: center;">
                                            <h4 class="">Excel Oluştur</h4>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 box shadow p-3">
                                                <div class="row">
                                                    <div class="col-md-6 col-sm-12">
                                                        <label>Tür</label>
                                                        <select onchange="optionInputSetting(this)" style="max-height: max-content" class="form-control" name="type" id="type">
                                                            <option value="0">Hepsi</option>
                                                            <option value="1">Görevler</option>
                                                            <option value="2">Şikayetler</option>
                                                            <option value="4">Adminler Tarafından Reddedilen Şikayetler</option>
                                                            <option value="3">Reddedilen Görevler</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 col-sm-12">
                                                        <label>Birim</label>
                                                        <select style="max-height: max-content" class="form-control" name="unit" id="">
                                                            <option value="0">Hepsi</option>
                                                            @foreach($reportCategories as $category)
                                                                <option value="{{$category->id}}">{{$category->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="missionSetting" style="width: 100%">
                                    <div class="col-xl-12 col-lg-12 col-sm-12  " >
                                        <div class="widget-content widget-content-area br-2">
                                            <div class="row">
                                                <div class="col-12 box shadow p-3">
                                                    <h5>Görev Durumu</h5>
                                                    <div class="row">
                                                        <div class="col-md-6 col-sm-12">
                                                            <label for="">Durum</label>
                                                            <select class="form-control" style="height: max-content" name="missionStatus" id="">
                                                                <option value="-1">Hepsi</option>
                                                                <option value="0">Personele Atandı</option>
                                                                <option value="1">Çözüldü</option>
                                                                <option value="2">Asılsız</option>
                                                                <option value="3">Reddedildi/Yeniden Birime Yönlendirildi</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 col-sm-12">
                                                            <label for="">Mahalle</label>
                                                            <select class="form-control" style="height: max-content" name="neighbourhood" id="">
                                                                <option value="0">Hepsi</option>
                                                                @foreach($neighbourhoods as $neighbourhood)
                                                                    <option value="{{$neighbourhood->id}}">{{$neighbourhood->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 col-lg-12 col-sm-12  " id="missionFixedArea" >
                                        <div class="widget-content widget-content-area br-0">
                                            <div class="row">
                                                <div class="col-12 box shadow p-3">
                                                    <h5>Görev Çözülme Tarih Aralığı</h5>
                                                    <div class="row">
                                                        <div class="col-md-6 col-sm-12">
                                                            <label>Başlangıç</label>
                                                            <input type="date" class="form-control" name="missionFixedStart">
                                                        </div>
                                                        <div class="col-md-6 col-sm-12">
                                                            <label>Bitiş</label>
                                                            <input type="date" class="form-control" name="missionFixeEnd">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 col-lg-12 col-sm-12  " >
                                        <div class="widget-content widget-content-area br-1">
                                            <div class="row">
                                                <div class="col-12 box shadow p-3">
                                                    <h5>Görev Birime Atanma Tarih Aralığı</h5>
                                                    <div class="row">
                                                        <div class="col-md-6 col-sm-12">
                                                            <label>Başlangıç</label>
                                                            <input type="date" class="form-control" name="assigmentStartDate">
                                                        </div>
                                                        <div class="col-md-6 col-sm-12">
                                                            <label>Bitiş</label>
                                                            <input type="date" class="form-control" name="assigmentEndDate">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 col-lg-12 col-sm-12  " >
                                        <div class="widget-content widget-content-area br-0">
                                            <div class="row">
                                                <div class="col-12 box shadow p-3">
                                                    <h5>Görev Personele Atanma Tarih Aralığı</h5>
                                                    <div class="row">
                                                        <div class="col-md-6 col-sm-12">
                                                            <label>Başlangıç</label>
                                                            <input type="date" class="form-control" name="assigmentEmployeeStartDate">
                                                        </div>
                                                        <div class="col-md-6 col-sm-12">
                                                            <label>Bitiş</label>
                                                            <input type="date" class="form-control" name="assigmentEmployeeEndDate">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="reportSetting" style="width: 100%">
                                    <div class="col-xl-12 col-lg-12 col-sm-12  " >
                                        <div class="widget-content widget-content-area br-0">
                                            <div class="row">
                                                <div class="col-12 box shadow p-3">
                                                    <h5>Şikayet Durumu</h5>
                                                    <div class="row">
                                                        <div class="col-md-6 col-sm-12">
                                                            <label for="">Durum</label>
                                                            <select style="height: max-content;" class="form-control" name="reportStatus" id="">
                                                                <option value="-1">Hepsi</option>
                                                                <option value="0">Beklemede</option>
                                                                <option value="1">Birime Yönlendirildi</option>
                                                                <option value="2">Personele Atandı</option>
                                                                <option value="3">Süper Admin/Çek Gönder Admini Tarafından Reddedildi</option>
                                                                <option value="4">Birim Tarafından Reddedildi</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 col-sm-12">
                                                            <label for="">Mahalle</label>
                                                            <select class="form-control" style="height: max-content" name="neighbourhoodReport" id="">
                                                                <option value="0">Hepsi</option>
                                                                @foreach($neighbourhoods as $neighbourhood)
                                                                    <option value="{{$neighbourhood->id}}">{{$neighbourhood->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 col-lg-12 col-sm-12  " >
                                        <div class="widget-content widget-content-area br-1">
                                            <div class="row">
                                                <div class="col-12 box shadow p-3">
                                                    <h5>Şikayetin Oluşturulma Tarih Aralığı</h5>
                                                    <div class="row">
                                                        <div class="col-md-6 col-sm-12">
                                                            <label>Başlangıç</label>
                                                            <input type="date" class="form-control" name="reportCreateDateStart">
                                                        </div>
                                                        <div class="col-md-6 col-sm-12">
                                                            <label>Bitiş</label>
                                                            <input type="date" class="form-control" name="reportCreateDateEnd">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12 col-lg-12 col-sm-12  " >
                                    <div id="rejectedMission" style="width: 100%">
                                        <div class="widget-content widget-content-area br-1">
                                            <div class="row">
                                                <div class="col-12 box shadow p-3">
                                                    <h5>Görevin Reddedilme Tarih Aralığı</h5>
                                                    <div class="row">
                                                        <div class="col-md-6 col-sm-12">
                                                            <label>Başlangıç</label>
                                                            <input type="date" class="form-control" name="missionRejectDateStart">
                                                        </div>
                                                        <div class="col-md-6 col-sm-12">
                                                            <label>Bitiş</label>
                                                            <input type="date" class="form-control" name="missionRejectDateEnd">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="widget-content widget-content-area br-1">
                                            <div class="row">
                                                <div class="col-12 box shadow p-3">
                                                    <h5>Reddedilen Görevin Mahallesi</h5>
                                                    <div class="row">
                                                        <div class="col-md-6 col-sm-12">
                                                            <label for="">Mahalle</label>
                                                            <select class="form-control" style="height: max-content" name="rejectedMissionNeighbourhood" id="">
                                                                <option value="0">Hepsi</option>
                                                                @foreach($neighbourhoods as $neighbourhood)
                                                                    <option value="{{$neighbourhood->id}}">{{$neighbourhood->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12 col-lg-12 col-sm-12"  id="rejectedReportFromDepartment">
                                    <div >
                                        <div class="widget-content widget-content-area br-1">
                                            <div class="row">
                                                <div class="col-12 box shadow p-3">
                                                    <h5>Şikayetin Reddedilme Tarih Aralığı</h5>
                                                    <div class="row">
                                                        <div class="col-md-6 col-sm-12">
                                                            <label>Başlangıç</label>
                                                            <input type="date" class="form-control" name="reportRejectDateStart">
                                                        </div>
                                                        <div class="col-md-6 col-sm-12">
                                                            <label>Bitiş</label>
                                                            <input type="date" class="form-control" name="reportRejectDateEnd">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="widget-content widget-content-area br-1">
                                        <div class="row">
                                            <div class="col-12 box shadow p-3">
                                                <h5>Reddedilen Şikayet Mahallesi</h5>
                                                <div class="row">
                                                    <div class="col-md-6 col-sm-12">
                                                        <label for="">Mahalle</label>
                                                        <select class="form-control" style="height: max-content" name="rejectedReportNeighbourhood" id="">
                                                            <option value="0">Hepsi</option>
                                                            @foreach($neighbourhoods as $neighbourhood)
                                                                <option value="{{$neighbourhood->id}}">{{$neighbourhood->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="row" style="justify-content: center">
                                    <input type="submit" value="Excel Oluştur" class="btn btn-primary">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        function optionInputSetting(e){
            if(e.value == 0){
                document.getElementById('missionSetting').style.display = 'block'
                document.getElementById('reportSetting').style.display = 'block'
                document.getElementById('rejectedMission').style.display = 'block'
                document.getElementById('rejectedReportFromDepartment').style.display = 'block'
            }
            else if (e.value == 1){
                document.getElementById('missionSetting').style.display = 'block'
                document.getElementById('reportSetting').style.display = 'none'
                document.getElementById('rejectedMission').style.display = 'none'
                document.getElementById('rejectedReportFromDepartment').style.display = 'none'
            }
            else if (e.value == 2){
                document.getElementById('reportSetting').style.display = 'block'
                document.getElementById('missionSetting').style.display = 'none'
                document.getElementById('rejectedMission').style.display = 'none'
                document.getElementById('rejectedReportFromDepartment').style.display = 'none'
            }
            else if (e.value == 3){
                document.getElementById('rejectedMission').style.display = 'block'
                document.getElementById('missionSetting').style.display = 'none'
                document.getElementById('reportSetting').style.display = 'none'
                document.getElementById('rejectedReportFromDepartment').style.display = 'none'
            }
            else if(e.value == 4){
                document.getElementById('rejectedMission').style.display = 'none'
                document.getElementById('missionSetting').style.display = 'none'
                document.getElementById('reportSetting').style.display = 'none'
                document.getElementById('rejectedReportFromDepartment').style.display = 'block'
            }
        }
    </script>
@endsection
