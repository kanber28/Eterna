<?php


use App\Models\UserReportType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\Report;
use App\Models\Mission;
class CheckReportPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $routeNamesPermissionIsAdministrator=[
            'assignDepartment','fetchEmployee','getEmployeeInformation','assignDepartmentToUser',//Kullanıcı birim belirle
            'missions','missionFetch', 'getMissionDetail','missionsSolveIndex','missionsSolved','missionsFakeIndex','missionsFake',//Görevler
            'getRejectedMission','fetchRejectedMission','getRejectedMissionDetail','assignMissionToUserAgain',//Reddedilen görevler
            'rejectedReportFromDepartment','rejectedReportFromDepartmentFetch','assignRejectedReportAgain','rejectReportFromDepartment',//Adminler tarafından reddedilen görevler
            'waitingReportFromDepartment','fetchWaitingReportFromDepartment','waitingReportFromDepartmentDetail',//Birimde bekleyen şikayetler
            'fetchReports','getDetail','assignMission','assignReportToDepartment','rejectReport','sendMissionNotification','rollbackMission','processTree', 'sendNotification'//çek-gönderlerin listelendiği, reddedildiği ve atanıp detaylarının görüldüğü routlar
    ];
        $routeNamesPermissionIsNotAdministrator=[
            'fetchReports','createMission','assignMissionToUser','missions','missionFetch', 'getMissionDetail','rejectReportFromDepartment','missionsSolveIndex','missionsSolved','missionsFakeIndex','missionsFake',
            'getRejectedMission','fetchRejectedMission','getRejectedMissionDetail','assignMissionToUserAgain','sendMissionNotification','rollbackMission','processTree', 'sendNotification',
            ];
        $userReportType = UserReportType::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->first();
        if (Auth::user()->hasRole('Süper Admin') || Auth::user()->hasRole('Başkan')) {
            return $next($request);
        }
        if (isset($userReportType->is_administrator)){
            if ($userReportType->is_administrator == 1){
                return $next($request);
            }
        }
        $routeName = substr(Route::getRoutes()->match($request)->getName(), 7);
         if(!is_null($userReportType)){
            if ($userReportType->is_administrator == 1){
                if (in_array($routeName,$routeNamesPermissionIsAdministrator) || Route::getRoutes()->match($request)->getName()=='report'){
                    return $next($request);
                }
                else{
                    return \App\Http\Middleware\abort(403);
                }
            }
            elseif($userReportType->is_administrator == 0){
                if (in_array($routeName,$routeNamesPermissionIsNotAdministrator) || Route::getRoutes()->match($request)->getName()=='report'){
                    if($routeName=="assignMissionToUser"){
                         $report= Report::find($request->route('id'));
                        if(isset($report->category_id) && ($userReportType->report_category_id==$report->category_id)){
                            return $next($request);
                        }
                        else{
                            return \App\Http\Middleware\abort(403);
                        }
                    }
                    elseif($routeName=="getRejectedMissionDetail"){
                        $mission= Mission::find($request->route('id'));
                        if(isset($mission->category_id) && $userReportType->report_category_id==$mission->category_id){
                            return $next($request);
                        }
                        else{
                            return \App\Http\Middleware\abort(403);
                        }
                    }
                    return $next($request);
                }
                elseif($routeName == "missionsSolveIndex"){
                    $mission= Mission::find($request->route('id'));
                    if(isset($mission->category_id) && $userReportType->report_category_id==$mission->category_id){
                        return $next($request);
                    }
                }
                elseif($routeName == "missionsFakeIndex"){
                    $mission= Mission::find($request->route('id'));
                    if(isset($mission->category_id) && $userReportType->report_category_id==$mission->category_id){
                        return $next($request);
                    }
                }
                else{
                    return \App\Http\Middleware\abort(403);
                }
            }
        }
        else{
            return \App\Http\Middleware\abort(403);
        }
    }
}
