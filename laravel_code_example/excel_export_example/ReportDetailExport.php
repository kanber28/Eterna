<?php


use App\Models\Mission;
use App\Models\RejectedMission;
use App\Models\Report;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;
class ReportDetailExport implements WithTitle, FromView
{

    public function __construct($title, $filter, $unit)
    {
        $this->filter = $filter;
        $this->title = $title;
        $this->unit = $unit;
    }

    public function view(): View
    {
        $unit = $this->unit;
        $filter = $this->filter;
        $title = $this->title;
        $missionQuerySet = [];

        if ($this->unit != 0){
            array_push($missionQuerySet, ['category_id', '=', $this->unit]);
        }
        if ($filter['missionFilter']['missionFixedStart'] != null){
            array_push($missionQuerySet,['done_date','>=', $filter['missionFilter']['missionFixedStart']] );
        }
        if ($filter['missionFilter']['missionFixedEnd'] != null){
            array_push($missionQuerySet,['done_date','<=', $filter['missionFilter']['missionFixedEnd']] );
        }

        if ($filter['missionFilter']['assigmentStartDate'] != null){
            array_push($missionQuerySet,['mission_assigment_date','>=', $filter['missionFilter']['assigmentStartDate']] );
        }
        if ($filter['missionFilter']['assigmentEndDate'] != null){
            array_push($missionQuerySet,['mission_assigment_date','<=', $filter['missionFilter']['assigmentEndDate']] );
        }
        if ($filter['missionFilter']['missionStatus'] != -1){
            array_push($missionQuerySet, ['status', '=', $filter['missionFilter']['missionStatus']]);
        }
        if ($filter['missionFilter']['neighbourhood'] != 0){
            array_push($missionQuerySet, ['neighbourhood_id', '=', $filter['missionFilter']['neighbourhood']]);
        }

        if ($filter['missionFilter']['assigmentEmployeeStartDate'] != null && $filter['missionFilter']['assigmentEmployeeEndDate'] != null){
            $missions = Mission::where($missionQuerySet)
                ->whereRelation('getReport','assigment_date_to_unit', '<=',  $filter['missionFilter']['assigmentEmployeeEndDate'])
                ->whereRelation('getReport','assigment_date_to_unit', '>=',  $filter['missionFilter']['assigmentEmployeeStartDate'])
                ->get();
        }
        elseif ($filter['missionFilter']['assigmentEmployeeStartDate'] != null && $filter['missionFilter']['assigmentEmployeeEndDate'] == null){
            $missions = Mission::where($missionQuerySet)
                ->whereRelation('getReport','assigment_date_to_unit', '>=',  $filter['missionFilter']['assigmentEmployeeStartDate'])
                ->get();
        }
        elseif ($filter['missionFilter']['assigmentEmployeeStartDate'] == null && $filter['missionFilter']['assigmentEmployeeEndDate'] != null){
            $missions = Mission::where($missionQuerySet)
                ->whereRelation('getReport','assigment_date_to_unit', '<=',  $filter['missionFilter']['assigmentEmployeeEndDate'])
                ->get();
        }
        else {
            $missions = Mission::where($missionQuerySet)->get();
        }

        $reportQuerySet = [];

        if ($filter['reportFilter']['reportStatus'] != -1){
            array_push($reportQuerySet, ['status', '=', $filter['reportFilter']['reportStatus']]);
        }
        if ($filter['reportFilter']['neighbourhoodReport'] != 0){
            array_push($reportQuerySet, ['neighbourhood_id', '=', $filter['reportFilter']['neighbourhoodReport']]);
        }
        if ($filter['reportFilter']['reportCreateDateStart'] != null){
            array_push($reportQuerySet, ['created_at', '>=', $filter['reportFilter']['reportCreateDateStart']]);
        }
        if ($filter['reportFilter']['reportCreateDateEnd']  != null){
            array_push($reportQuerySet, ['created_at', '<=', $filter['reportFilter']['reportCreateDateEnd']]);
        }

        $rejectedMissionQuerySet = [];

        if ($filter['rejectedMission']['missionRejectDateStart'] != null){
            array_push($rejectedMissionQuerySet, ['created_at', '>=', $filter['rejectedMission']['missionRejectDateStart']]);
        }
        if ($filter['rejectedMission']['missionRejectDateEnd'] != null){
            array_push($rejectedMissionQuerySet, ['created_at', '>=', $filter['rejectedMission']['missionRejectDateEnd']]);
        }
        if ($unit !=0 && $filter['rejectedMission']['rejectedMissionNeighbourhood'] != 0){
            $rejectedMission = RejectedMission::where($rejectedMissionQuerySet)
                ->whereRelation('getMission', 'category_id', $unit)
                ->whereRelation('getMission', 'neighbourhood_id', $filter['rejectedMission']['rejectedMissionNeighbourhood'])->get();
        }
        elseif ($unit == 0 && $filter['rejectedMission']['rejectedMissionNeighbourhood'] != 0){
            $rejectedMission = RejectedMission::where($rejectedMissionQuerySet)
                ->whereRelation('getMission', 'neighbourhood_id', $filter['rejectedMission']['rejectedMissionNeighbourhood'])->get();
        }
        elseif ($unit != 0 && $filter['rejectedMission']['rejectedMissionNeighbourhood'] == 0){
            $rejectedMission = RejectedMission::where($rejectedMissionQuerySet)
                ->whereRelation('getMission', 'category_id', $unit)->get();
        }
        else {
            $rejectedMission = RejectedMission::where($rejectedMissionQuerySet)->get();
        }


        $rejectedReportQuerySet = [];

        if ($filter['rejectedReport']['reportRejectDateStart'] != null){
            array_push($rejectedReportQuerySet, ['created_at', '>=', $filter['rejectedReport']['reportRejectDateStart']]);
        }
        if ($filter['rejectedReport']['reportRejectDateEnd'] != null){
            array_push($rejectedReportQuerySet, ['created_at', '>=', $filter['rejectedReport']['reportRejectDateEnd']]);
        }
        if ($unit != 0 && $filter['rejectedReport']['rejectedReportNeighbourhood'] != 0){
            $rejectedReport = Report::where($rejectedReportQuerySet)
                ->where('category_id', $unit)
                ->where('neighbourhood_id', $filter['rejectedReport']['rejectedReportNeighbourhood'])->whereBetween('status', [3,4])->get();
        }
        elseif ($unit == 0 && $filter['rejectedReport']['rejectedReportNeighbourhood'] != 0){
            $rejectedReport = Report::where($rejectedReportQuerySet)
                ->where('neighbourhood_id', $filter['rejectedReport']['rejectedReportNeighbourhood'])->whereBetween('status', [3,4])->get();
        }
        elseif ($unit != 0 && $filter['rejectedReport']['rejectedReportNeighbourhood'] == 0){
            $rejectedReport = Report::where($rejectedReportQuerySet)->whereBetween('status', [3,4])
                ->where('category_id', $unit)->get();
        }
        else {
            $rejectedReport = Report::where($rejectedReportQuerySet)->whereBetween('status', [3,4])->get();
        }





        if ($this->unit != 0){
            array_push($missionQuerySet, ['category_id', '=', $this->unit]);
            array_push($reportQuerySet, ['category_id', '=', $this->unit]);
        }

        $reports = Report::where($reportQuerySet)->get();

        return \App\Exports\view('exports.reportExport', compact('reports', 'missions', 'rejectedMission', 'rejectedReport', 'title'));
    }

    public function title(): string
    {
        return $this->title;
    }
}
