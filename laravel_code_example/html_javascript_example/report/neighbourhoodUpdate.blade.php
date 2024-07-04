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
                                    <div>
                                        <form action="{{route('report.updateNeighbourhood')}}" method="POST" class="form-group">
                                            @csrf
                                            <input type="hidden" name="id" value="{{$neighbourhood->id}}">
                                            <div style="margin: auto">
                                                <div style="display: inline-block;width: 40%">
                                                    <label for="">Mahalle İsmi :</label>
                                                    <input type="text" name="name" class="form-control" value="{{$neighbourhood->name}}">
                                                </div>
                                                <div style="display:block;margin-top: 10px">
                                                    <input type="submit" name="submit" value="Güncelle" class="btn btn-warning">
                                                </div>
                                            </div>
                                        </form>
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

@endsection
