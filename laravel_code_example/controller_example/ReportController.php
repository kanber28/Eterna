<?php


use App\Exports\ReportExport;
use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\ImageGalery;
use App\Models\Mission;
use App\Models\MissionNotification;
use App\Models\Neighbourhood;
use App\Models\NotifySchema;
use App\Models\ProcessTree;
use App\Models\RejectedMission;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\ReportsRejectPattern;
use App\Models\User;
use App\Models\UserReportType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPUnit\TextUI\Help;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use function PHPUnit\Framework\isFalse;

class ReportController extends Controller
{
    public function index()
    {
        $userReportType = UserReportType::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->first();
        $tableStatus = true;
        $rejectPatterns=ReportsRejectPattern::all();
        $user = User::find(Auth::user()->id);
        if (Auth::user()->hasRole('Süper Admin') || Auth::user()->hasRole('Başkan')) {
            $tableStatus = true;
        } else if ($userReportType == null) {
            $tableStatus = false;
        }
        if  (Auth::user()->hasRole('Süper Admin') || Auth::user()->hasRole('Başkan')){
            $isTherePermit = true;
        }
        else {
            if (isset($userReportType)){
                $isTherePermit = true;
            }
            else {
                $isTherePermit = false;
            }
        }

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.list', compact('userReportType', 'rejectPatterns', 'tableStatus', 'isTherePermit'));
    }

    function fetchReports()
    {
        $user = User::find(Auth::user()->id);
        $userReportType = UserReportType::where('user_id', $user->id)->orderBy('id', 'DESC')->first();
        if (!isset($userReportType) && !$user->hasRole('Süper Admin') && !$user->hasRole('Başkan')){
            return false;
        }
        if ($user->hasRole('Süper Admin')) {
            $reports = Report::query()->where('status', '0');

            return DataTables::of($reports)
                ->addColumn('assign', function ($report) {
                    return '<a href="' . \App\Http\Controllers\Dashboard\route('report.assignMission', $report->id) . '" class="btn btn-primary">Ata</a>';
                })
                ->addColumn('detail', function ($report) {
                    return '<a href="' . \App\Http\Controllers\Dashboard\route('report.getDetail', $report->id) . '" class="btn btn-info">Detay</a>';
                })
                ->addColumn('reject', function ($report) {
                    return '<button onclick="superAdminRejectReport(' . $report->id . ')" class="btn btn-danger">Reddet</button>';
                })
                ->addcolumn('category', function ($report) {
                    $category = ReportCategory::withTrashed()->where('id', $report->category_id)->first();
                    return $category->name . (is_null($category->is_deleted) ? '' : ' (Silinmiş Birim)');
                })
                ->addColumn('processTree', function ($report){
                    $tree = ProcessTree::where('report_id', $report->id)->first();
                    if (isset($tree->id)){
                        return '<a href="'. \App\Http\Controllers\Dashboard\route('report.processTree', $tree->id) .'" class="btn btn-outline-primary" >İşlem Ağacı</a>';
                    }
                    else {
                        return '';
                    }
                })
                ->addColumn('reporter', function ($report){
                    $user = User::withTrashed()->where('id', $report->user_id)->first();
                    if (isset($user)){
                        return $user->firstname . ' ' . $user->lastname;
                    }
                    else {
                        return '';
                    }
                })
                ->rawColumns(['assign', 'detail', 'reject', 'processTree'])
                ->make(true);
        } else {
            if ($userReportType == null && !$user->hasRole('Süper Admin')) {
                $reports = Report::query()->where('status', '1');

                return DataTables::of($reports)
                    ->addColumn('assign', function ($report) {
                        return '';
                    })
                    ->addColumn('detail', function ($report) {
                        return '';
                    })
                    ->addColumn('reject', function ($report) {
                        return '';
                    })
                    ->addcolumn('category', function ($report) {
                        return '';
                    })
                    ->addColumn('processTree', function ($report){
                        return '';
                    })
                    ->addColumn('reporter', function ($report){
                        $user = User::withTrashed()->where('id', $report->user_id)->first();
                        if (isset($user)){
                            return $user->firstname . ' ' . $user->lastname;
                        }
                        else {
                            return '';
                        }
                    })
                    ->rawColumns(['assign', 'detail', 'reject', 'processTree'])
                    ->make(true);
            } else if ($userReportType->is_administrator == 1) {
                $reports = Report::query()->where('status', '0');

                return DataTables::of($reports)
                    ->addColumn('assign', function ($report) {
                        return '<a href="' . \App\Http\Controllers\Dashboard\route('report.assignMission', $report->id) . '" class="btn btn-primary">Ata</a>';
                    })
                    ->addColumn('detail', function ($report) {
                        return '<a href="' . \App\Http\Controllers\Dashboard\route('report.getDetail', $report->id) . '" class="btn btn-info">Detay</a>';
                    })
                    ->addColumn('reject', function ($report) {
                        return '<button onclick="rejectReport(' . $report->id . ')" class="btn btn-danger">Reddet</button>';
                    })
                    ->addcolumn('category', function ($report) {
                        $category = ReportCategory::withTrashed()->where('id', $report->category_id)->first();
                        return $category->name . (is_null($category->is_deleted) ?  '': ' (Silinmiş Birim)');
                    })
                    ->addColumn('processTree', function ($report){
                        $tree = ProcessTree::where('report_id', $report->id)->first();
                        if (isset($tree->id)){
                            return '<a href="'. \App\Http\Controllers\Dashboard\route('report.processTree', $tree->id) .'" class="btn btn-outline-primary" >İşlem Ağacı</a>';
                        }
                        else {
                            return '';
                        }
                    })
                    ->addColumn('reporter', function ($report){
                        $user = User::withTrashed()->where('id', $report->user_id)->first();
                        if (isset($user)){
                            return $user->firstname . ' ' . $user->lastname;
                        }
                        else {
                            return '';
                        }
                    })
                    ->rawColumns(['assign', 'detail', 'reject', 'processTree'])
                    ->make(true);
            } else if ($userReportType->is_administrator == 0) {
                $reportNotAdmin = Report::query()->where('category_id', $userReportType->report_category_id)->where('status', '1');

                return DataTables::of($reportNotAdmin)
                    ->addColumn('assign', function ($report) {
                        return '<a href="' . \App\Http\Controllers\Dashboard\route('report.assignMissionToUser', $report->id) . '" class="btn btn-primary">Ata</a>';
                    })
                    ->addColumn('detail', function ($report) {
                        return '<a href="' . \App\Http\Controllers\Dashboard\route('report.getDetail', $report->id) . '" class="btn btn-info">Detay</a>';
                    })
                    ->addColumn('reject', function ($report) {
                        return '<button onclick="rejectFromDepartment(' . $report->id . ')" class="btn btn-danger">Reddet</button>';
                    })
                    ->addcolumn('category', function ($report) {
                        $category = ReportCategory::withTrashed()->where('id', $report->category_id)->first();
                        return $category->name . (is_null($category->is_deleted) ?  '': ' (Silinmiş Birim)');
                    })
                    ->addColumn('processTree', function ($report){
                        $tree = ProcessTree::where('report_id', $report->id)->first();

                        if (isset($tree->id)){
                            return '<a href="'. \App\Http\Controllers\Dashboard\route('report.processTree', $tree->id) .'" class="btn btn-outline-primary" >İşlem Ağacı</a>';
                        }
                        else {
                            return '';
                        }
                    })
                    ->addColumn('reporter', function ($report){
                        $user = User::withTrashed()->where('id', $report->user_id)->first();
                        if (isset($user)){
                            return $user->firstname . ' ' . $user->lastname;
                        }
                        else {
                            return '';
                        }
                    })
                    ->rawColumns(['assign', 'detail', 'reject', 'processTree'])
                    ->make(true);
            }
        }
    }

    function getDetail($id)
    {
        $report = Report::find($id);
        if ($report == null){
            return \App\Http\Controllers\Dashboard\redirect('report');
        }
        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.edit', compact('report'));
    }

    function assignMissionToAdministration($id)
    {
        $report = Report::find($id);
        $categories = ReportCategory::all();
        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.assignMissionToAdministration', compact('report', 'categories'));
    }

    function listOfReportCategoryMission()
    {
        $user = User::find(Auth::user()->id);
        $userReportType = UserReportType::where('user_id', $user->id)->orderBy('id', 'DESC')->first();
        $reports = Report::query()->where('asigned_report_category_id', $userReportType->report_category_id);

        return DataTables::of($reports)
            ->addColumn('assign', function ($report) {
                return '<a href="' . \App\Http\Controllers\Dashboard\route('report.assignMission', $report->id) . '" class="btn btn-primary">Ata</a>';
            })
            ->addColumn('detail', function ($report) {
                return '<a href="' . \App\Http\Controllers\Dashboard\route('report.getDetail', $report->id) . '" class="btn btn-info">Detay</a>';
            })
            ->addColumn('reject', function ($report) {
                return '<button class="btn btn-danger">Reddet</button>';
            })
            ->rawColumns(['assign', 'detail', 'reject'])
            ->make(true);
    }

    function fetchEmployeeUser()
    {
        $users = User::where('user_type', 1)->get();

        return \App\Http\Controllers\Dashboard\response()->json($users);
    }

    function assignMissionToUser($id)
    {
        $report = Report::find($id);
        $user = User::find(Auth::user()->id);
        $type = UserReportType::where('report_category_id', $user->getReportCategory[0]->report_category_id)->first();
        $employee = User::where('user_type', 1)->where('user_employee_category', $type->report_category_id)->get();//$type->getUser;
        $categories = ReportCategory::all();


        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.assignMissionToUser', compact('report', 'categories', 'employee'));
    }


    function assignDepartment()
    {

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.assignDepartment');
    }

    function fetchEmployee()
    {
        $users = User::query()->where('user_type', 1);

        return DataTables::of($users)
            ->addColumn('category', function ($user) {
                $category = ReportCategory::all();
                if ( $user->user_employee_category != null) {
                    foreach ($category as $item) {
                        if ( $user->user_employee_category== $item->id) {
                            return  $item->name ;
                        }
                    }
                } else {
                    return 'Yetkisiz';
                }

            }) ->addColumn('update', function ($user) {
                return '<button onclick="updateModal(' . $user->id . ')" class="btn btn-warning">Güncelle</button>';
            })
            ->addColumn('detail', function ($user) {
                return '<button onclick="detailModal(' . $user->id . ')" class="btn btn-primary">Detay</button>';
            })
            ->rawColumns(['update','category', 'detail'])
            ->make(true);
    }

    function getEmployeeInformation(Request $request)
    {
        $user = User::where('id', $request->id)->withTrashed()->with('getReportCategory', 'UserEmployeeCategory')->first();

        return \App\Http\Controllers\Dashboard\response()->json($user);
    }

    function assignDepartmentToUser(Request $request)
    {
        $user = User::find($request->id);
        $user->user_employee_category = $request->category_id;
        $user->save();
        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }

    function assignReportToDepartment(Request $request)
    {
        $department = ReportCategory::find($request->category_id);
        $report = Report::find($request->report_id);
        $report->status = 1;
        $report->category_id = $department->id;
        $report->assigment_date_to_unit = Carbon::now();
        $report->save();
        $departmentName = $report->getReportCategory->name;

        $user = Auth::user();
        $processContent = 'Şikayet ' . $user->firstname . ' ' . $user->lastname . ' tarafından '. $departmentName . ' birimine atandı';
        Helper::saveProcess($user->id, $report->id, 'Birime Atandı', $processContent);

        return \App\Http\Controllers\Dashboard\response()->json(['status' => 'success', 'message' => 'Atama Başarılı']);
    }

    function createMission(Request $request)
    {
        $report = Report::find($request->report_id);
        $user = User::find($request->user_id);
        //status	image_gallery_id	category	assigned_user_id	report_id	appointing_id	is_trust	created_at	updated_at	deleted_at
        $isAssigned = Mission::where('report_id', $report->id)->count();
        if ($isAssigned < 1) {
            $mission = new Mission();
            $mission->status = 0;
            $mission->image_gallery_id = $report->image_gallery_id;
            $mission->category_id = $report->category_id;
            $mission->assigned_user_id = $user->id;
            $mission->report_id = $report->id;
            $mission->neighbourhood_id = $report->neighbourhood_id;
            $mission->appointing_id = Auth::user()->id;
            $mission->mission_assigment_date = Carbon::now();
            $mission->is_trust = 1;
            $mission->save();

            $report->status = 2;
            $report->save();

            if ($user->firebase_token != null){
                $notifyData = Mission::where('id', $mission->id)->with('getReport', 'getCategory', 'getGallery')->first();
                $notifyTitle = "Yeni Görev";
                $notifyBody = "Biriminiz Tarafından Yeni Bir Görev Atandı";
                Helper::pushNotificationToCurl($notifyTitle, $notifyBody, $user->firebase_token, $notifyData, 'mission');
            }

            $processContent = 'Birime atanan şikayet ' . Auth::user()->firstname . ' ' . Auth::user()->lastname . ' tarafından '. $user->firstname . ' ' . $user->lastname .
            ' adlı personele atandı';
            Helper::saveProcess(Auth::user()->id, $report->id, 'Şikayet Personele Atandı', $processContent);

            return \App\Http\Controllers\Dashboard\response()->json(['message' => 'Görev Personele Atandı', 'status' => 'success']);
        } else {
            return \App\Http\Controllers\Dashboard\response()->json(['message' => 'Görev Zaten Atanmış', 'status' => 'error']);

        }

    }

    function createDepartmentIndex()
    {
        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.createDepartment');
    }

    function createDepartment(Request $request)
    {
        $department = new ReportCategory();
        $department->name = $request->departmentName;
        $department->save();

        return \App\Http\Controllers\Dashboard\response()->json(true);
    }

    function deleteDepartment(Request $request){
        $department = ReportCategory::find($request->id);
        $department->delete();

        $reports = Report::where('category_id', $department->id)->where('status', 1)->get();
        foreach ($reports as $report){
            $report->status = 0;
            $report->save();
        }
        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }

    function fetchDepartments()
    {
        $departments = ReportCategory::query();

        return DataTables::of($departments)
            ->addColumn('update', function ($department) {
                return '<button class="btn btn-warning" onclick="updateDepartmentModal(' . $department->id . ')">Güncelle</button>';
            })
            ->addColumn('delete', function($department){
                return '<button class="btn btn-danger" onclick="deleteDepartment('.$department->id.')">Sil</button>';
            })
            ->rawColumns(['update', 'delete'])
            ->make(true);
    }

    function getDepartment(Request $request)
    {
        $department = ReportCategory::find($request->id);

        return \App\Http\Controllers\Dashboard\response()->json($department);
    }

    function updateDepartment(Request $request)
    {
        $department = ReportCategory::find($request->id);
        $department->name = $request->name;
        $department->save();

        return \App\Http\Controllers\Dashboard\response()->json(true);
    }

    function rejectReport(Request $request)
    {
        $report = Report::find($request->id);
        $report->status = 3;
        $report->last_rejected_user_id = Auth::user()->id;
        $report->last_rejected_date	= Carbon::now();
        $report->save();

        $user = User::find($report->user_id);
        $user->send_check_count -= 1;
        $user->save();

        $processContent = 'Şikayet ' . Auth::user()->firstname . ' ' . Auth::user()->lastname . ' süperadmini tarafından reddedildi <br> Red Sebebi: '. $request->rejectPatternTextArea;
        Helper::saveProcess($user->id, $report->id, 'Süper Admin Tarafından Reddedildi', $processContent);

        return \App\Http\Controllers\Dashboard\response()->json(['status' => 'success']);
    }

    function rejectReportFromDepartment(Request $request){
        $report = Report::find($request->id);
        $report->status = 4;
        $report->last_rejected_user_id = Auth::user()->id;
        $report->last_rejected_date = Carbon::now();
        $report->save();

        $user = User::find($report->user_id);
        $user->send_check_count -= 1;
        $user->save();

        $processContent = 'Birime atanan şikayet ' . Auth::user()->firstname . ' ' . Auth::user()->lastname . ' tarafından reddedildi';
        Helper::saveProcess(Auth::user()->id, $report->id, 'Şikayet Birim Tarafından Reddedildi', $processContent);

        return \App\Http\Controllers\Dashboard\response()->json(['status' => 'success']);
    }

    function missions()
    {
        $notifies = NotifySchema::all();

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.departmentMissionStatus', compact('notifies'));
    }

    function missionFetch(Request $request)
    {
        $userReportType = Auth::user()->getReportCategory()->first();
        if (Auth::user()->hasRole('Süper Admin') ||  Auth::user()->hasRole('Başkan') || $userReportType->is_administrator == 1 ) {
            if (isset($request->status)){
                if ($request->status == 10){
                    $missions = Mission::query()->where('status', '!=', 3);
                }
                elseif ($request->status >= 0 && $request->status <= 2){
                    $missions = Mission::query()->where('status', $request->status);
                }
                elseif ($request->status == 4){
                    $missions = Mission::query()->where('status', 1)->where('is_notify', 0);
                }
                elseif($request->status == 3){
                    $missions = Mission::query()->where('status', '!=', 3)->where('is_notify', 1);
                }
            }
            else {
                $missions = Mission::query()->where('status', '!=', 3);
            }

        } else {
            if (isset($request->status)){
                if ($request->status == 10){
                    $missions = Mission::query()->where('category_id', Auth::user()->getReportCategorySingle->report_category_id)->where('status', '!=', 3);
                }
                elseif ($request->status >= 0 && $request->status <= 2){
                    $missions = Mission::query()->where('category_id', Auth::user()->getReportCategorySingle->report_category_id)->where('status', $request->status);
                }
                elseif ($request->status == 4){
                    $missions = Mission::query()->where('category_id', Auth::user()->getReportCategorySingle->report_category_id)->where('status', 1)->where('is_notify', 0);
                }
                elseif($request->status == 3){
                    $missions = Mission::query()->where('category_id', Auth::user()->getReportCategorySingle->report_category_id)->where('status', '!=', 3)->where('is_notify', 1);
                }
            }
            else {
                $missions = Mission::query()->where('category_id', Auth::user()->getReportCategorySingle->report_category_id)->where('status', '!=', 3);
            }
        }
        return DataTables::of($missions)
            ->editColumn('id', function ($mission){
                return $mission->report_id;
            })
            ->addColumn('description', function ($mission) {
                $out = strlen($mission->getReport->description) > 100 ? substr($mission->getReport->description,0,100)."..." : $mission->getReport->description;
                return $out;
            })
            ->addColumn('address', function ($mission) {
                return $mission->getReport->address;
            })
            ->addColumn('getReportCategory', function ($mission) {
                return $mission->getReport->getReportCategory->name;
            })
            ->addColumn('assignedUser', function ($mission) {
                if (isset($mission->getAssignedUser->firstname)){
                    return $mission->getAssignedUser->firstname . ' ' . $mission->getAssignedUser->lastname;
                }
                else {
                    $user = User::withTrashed()->where('id', $mission->assigned_user_id)->first();
                    return $user->firstname . ' ' . $user->lastname;
                }
            })
            ->addColumn('status_name', function ($mission) {
                if ($mission->status == 0) {
                    return 'Atandı';
                }
                elseif ($mission->status == 1) {
                    return 'Görev Tamamlandı';
                }
                elseif ($mission->status == 2) {
                    return 'Asılsız Olarak İşaretlendi';
                }
                else {
                    return '';
                }
            })
            ->addColumn('sendNotify', function ($mission){
                if ($mission->status == 0){
                    return 'Görev Henüz Sonuçlanmadı';
                }
                elseif($mission->status == 1) {
                    if ($mission->is_notify == 0){
                        return '<button class="btn btn-info" onclick="openNotifyModal('. $mission->id .')">Bildirim Gönder</button>';
                    }
                    else {
                        return 'Bildirim Gönderildi';
                    }
                }
                elseif($mission->status == 2){
                    if($mission->is_notify == 0){
                        return '<button class="btn btn-info" onclick="openNotifyModal('. $mission->id .')">Asılsız Bildirimi Gönder</button>';
                    }
                    else{
                        return 'Bildirim Gönderildi';
                    }
                }
            })
            ->addColumn('detail', function ($mission) {
                return '<a href="' . \App\Http\Controllers\Dashboard\route('report.getMissionDetail', $mission->id) . '" class="btn btn-primary">Detay</a>';
            })
            ->addColumn('missionDoneDate', function ($mission){
                return (!is_null($mission->done_date) ? Carbon::createFromFormat('Y-m-d H:i:s', $mission->done_date) : '-');

            })
            ->addColumn('processTree', function ($mission){
                $tree = ProcessTree::where('report_id', $mission->report_id)->first();
                if (isset($tree->id)){
                    return '<a href="'. \App\Http\Controllers\Dashboard\route('report.processTree', $tree->id) .'" class="btn btn-outline-primary" >İşlem Ağacı</a>';
                }
                else {
                    return '';
                }
            })
            ->addColumn('sendMissionNotification', function ($mission){
                if($mission->status == 0){
                    return '<button onclick="openNotificationModal('. $mission->id .')" class="btn btn-info" >Ara Bildirim Gönder</button>';
                }else{

                    return 'Görev Sonuçlandırıldı';

                }
            })
            ->addColumn('reporter', function ($mission){
                $user = User::withTrashed()->where('id', $mission->getReport->user_id)->first();

                return $user->firstname . ' ' . $user->lastname;
            })
            ->rawColumns(['detail', 'sendNotify', 'processTree', 'sendMissionNotification'])
            ->make(true);
    }

    function getMissionDetail($id)
    {
        $mission = Mission::find($id);
        $report = $mission->getReport;
        $notifies = NotifySchema::all();

        $users = User::where('user_employee_category', $mission->category_id)->get();

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.missionDetail', compact('mission', 'report', 'notifies', 'users'));
    }

    function getRejectedMission()
    {

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.rejectedMission');
    }

    function fetchRejectedMission()
    {
        if (Auth::user()->hasRole('Süper Admin')|| Auth::user()->hasRole('Başkan')) {
            $missions = Mission::query()->where('status', 3);
        } else {
            $missions = Mission::query()->where('category_id', Auth::user()->getReportCategorySingle->report_category_id)->where('status', 3);
        }
        return DataTables::of($missions)
            ->addColumn('description', function ($mission) {
                return $mission->getReport->description;
            })
            ->addColumn('address', function ($mission) {
                return $mission->getReport->address;
            })
            ->addColumn('getReportCategory', function ($mission) {
                return $mission->getReport->getReportCategory->name;
            })
            ->addColumn('assignedUser', function ($mission) {
                if (isset($mission->getAssignedUser->firstname)){
                    return $mission->getAssignedUser->firstname . ' ' . $mission->getAssignedUser->lastname;
                }
                else {
                    $user = User::withTrashed()->where('id', $mission->assigned_user_id)->first();
                    return $user->firstname . ' ' . $user->lastname;
                }
            })
            ->addColumn('detail', function ($mission) {
                return '<a href="' . \App\Http\Controllers\Dashboard\route('report.getRejectedMissionDetail', $mission->id) . '" class="btn btn-primary">İncele</a>';
            })
            ->addColumn('processTree', function ($mission){
                $tree = ProcessTree::where('report_id', $mission->report_id)->first();
                if (isset($tree->id)){
                    return '<a href="'. \App\Http\Controllers\Dashboard\route('report.processTree', $tree->id) .'" class="btn btn-outline-primary" >İşlem Ağacı</a>';
                }
                else {
                    return '';
                }
            })
            ->addColumn('reporter', function ($mission){
                $user = User::withTrashed()->where('id', $mission->getReport->user_id)->first();

                return $user->firstname . ' ' . $user->lastname;
            })
            ->rawColumns(['detail', 'processTree'])
            ->make(true);
    }

    function getRejectedMissionDetail($id)
    {
        $mission = Mission::find($id);
        $rejectedDescription = RejectedMission::orderBy('id', 'DESC')->where('mission_id', $mission->id)->first();
        $report = $mission->getReport;
        $user = User::find(Auth::user()->id);
        if (isset($user->getReportCategory[0])) {
            $employee = User::where('user_type', 1)->where('user_employee_category', $report->category_id)->get();//$type->getUser;
        } else if (Auth::user()->hasRole('Süper Admin')) {
            $employee = User::where('user_type', 1)->where('user_employee_category',  $report->category_id)->get();
        }

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.rejectedMissionDetail', compact('mission', 'rejectedDescription', 'report', 'employee'));
    }

    function assignMissionToUserAgain(Request $request)
    {
        $user = User::find($request->user_id);
        $mission = Mission::find($request->mission_id);
        $mission->assigned_user_id = $user->id;
        $mission->appointing_id = Auth::user()->id;
        $mission->mission_assigment_date = Carbon::now();
        $mission->status = 0;
        $mission->save();

        $processContent = 'Reddedilen görev ' . Auth::user()->firstname . ' ' . Auth::user()->lastname . ' tarafından ' .$user->firstname . ' ' . $user->lastname . ' personeline atandı.';
        Helper::saveProcess(Auth::user()->id, $mission->report_id, 'Reddedilen Görev Tekrar Atandı', $processContent);

        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }

    function departmentSettings()
    {
        $employee = User::where('user_type', 2);
        $reportCategory = ReportCategory::all();

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.departmentSettings', compact('employee', 'reportCategory'));
    }

    function fetchEmployeeForDepartmentSettings()
    {
        $user = User::query()->where('user_type', 2);
        return DataTables::of($user)
            ->addColumn('departmentSettings', function ($user) {
                $category = ReportCategory::all();
                $authority = UserReportType::where('user_id', $user->id)->first();
                $categoryOption = '';
                if ($authority != null) {
                    if ($authority->is_administrator == 1) {
                        return 'Çek Gönder Admini';
                    }
                    foreach ($category as $item) {
                        if ($authority->report_category_id == $item->id) {
                            $categoryOption .= '<option selected value="' . $item->id . '">' . $item->name . '</option>';
                        } else {
                            $categoryOption .= '<option value="' . $item->id . '">' . $item->name . '</option>';
                        }
                    }
                } else {
                    foreach ($category as $item) {
                        $categoryOption .= '<option value="' . $item->id . '">' . $item->name . '</option>';
                    }
                }

                return '<select onchange="changeAuthority(this,' . $user->id . ')" class="form-control" name="" id=""><option value="0">Yetkisiz</option>' . $categoryOption . '</select>';
            })
            ->addColumn('doAdmin', function ($user) {
                return '<button onclick="doNewAdmin(' . $user->id . ')" class="btn btn-warning">Çek Gönder Admini Olarak Ata</button>';
            })
            ->addColumn('doAdministrator', function ($user) {
                return '<button class="btn btn-danger" onclick="removeAllAuthority(' . $user->id . ')">Bütün Yetkileri Kaldır</button>';
            })
            ->rawColumns(['departmentSettings', 'doAdministrator', 'doAdmin'])
            ->make(true);
    }

    function removeAllAuthority(Request $request)
    {
        $authority = UserReportType::where('user_id', $request->user_id)->get();
        foreach ($authority as $item) {
            $item->delete();
        }
        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }

    function changeAuthority(Request $request)
    {
        $userReportType = UserReportType::where('user_id', $request->user_id)->get();
        foreach ($userReportType as $item) {
            $item->delete();
        }
        if ($request->report_category_id != 0) {
            $newAuthority = new UserReportType();
            $newAuthority->report_category_id = $request->report_category_id;
            $newAuthority->user_id = $request->user_id;
            $newAuthority->is_administrator = 0;
            $newAuthority->save();
        }
        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }

    function doTakeAndSendAdmin(Request $request)
    {
        $userReportType = UserReportType::where('user_id', $request->user_id)->get();
        foreach ($userReportType as $item) {
            $item->delete();
        }
        $category = ReportCategory::orderBy('id', 'ASC')->first();
        if ($category != null) {
            $newAdmin = new UserReportType();
            $newAdmin->user_id = $request->user_id;
            $newAdmin->report_category_id = $category->id;
            $newAdmin->is_administrator = 1;
            $newAdmin->save();
        }


        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }

    function neighbourhoodIndex(){

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.neighbourhoodList');
    }

    function fetchNeighbourhood(){
        $neighbourhood = Neighbourhood::query();
        return DataTables::of($neighbourhood)
            ->addColumn('update', function($data){
                return '<button class="btn btn-warning" onclick="updateNeighbourhoodModal(' . $data->id . ')">Güncelle</button>';
            })
            ->addColumn('delete', function($data){
                return '<button onclick="deleteNeighbourhood('.$data->id.')" class="btn btn-danger">Sil</button>';
            })
            ->rawColumns(['update', 'delete'])
            ->make();
    }

//    function createNeighbourhoodIndex(){
//        return view('dashboard.pages.report.neighbourhoodCreate');
//    }


    function getNeighbourhood(Request $request)
    {
        $neighbourhood = Neighbourhood::find($request->id);

        return \App\Http\Controllers\Dashboard\response()->json($neighbourhood);
    }

    function createNeighbourhood(Request $request){
        $request->validate([
            'neighbourhoodName' => 'required',
            'lat' => 'required',
            'lng' => 'required',
        ],
            [
                'neighbourhoodName.required' => 'Mahalle İsmi Boş Olamaz',
                'lat.required' => 'X Ekseni Boş Olamaz',
                'lng.required' => 'Y Ekseni Boş Olamaz',
            ]);

        $neighbourhood = new Neighbourhood();
        $neighbourhood->name = $request->neighbourhoodName;
        $neighbourhood->lat = $request->lat;
        $neighbourhood->lng = $request->lng;
        $neighbourhood->save();

        return \App\Http\Controllers\Dashboard\response()->json(true);
    }

//    function updateNeighbourhoodIndex($id){
//        $neighbourhood = Neighbourhood::find($id);
//
//        return view('dashboard.pages.report.neighbourhoodUpdate', compact('neighbourhood'));
//    }

    function updateNeighbourhood(Request $request){
        $request->validate([
            'neighbourhoodName' => 'required',
            'lat' => 'required',
            'lng' => 'required',
        ],
            [
                'neighbourhoodName.required' => 'Mahalle İsmi Boş Olamaz',
                'lat.required' => 'X Ekseni Boş Olamaz',
                'lng.required' => 'Y Ekseni Boş Olamaz',
            ]);
        $neighbourhood = Neighbourhood::find($request->id);
        $neighbourhood->name = $request->neighbourhoodName;
        $neighbourhood->lat = $request->lat;
        $neighbourhood->lng = $request->lng;
        $neighbourhood->save();

        return \App\Http\Controllers\Dashboard\response()->json(true);
    }

    function deleteNeighbourhood(Request $request){
        $neighbourhood = Neighbourhood::find($request->id);
        $neighbourhood->delete();

        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }

    function rejectedReportFromDepartment(){

        $rejectPatterns=ReportsRejectPattern::all();

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.rejectedReportFromDepartment', compact('rejectPatterns'));
    }

    function rejectedReportFromDepartmentFetch(){
        $reports = Report::query()->whereIn('status', [3,4]);

        return DataTables::of($reports)
            ->addColumn('assign', function ($report) {
                return '<a href="'. \App\Http\Controllers\Dashboard\route('report.assignRejectedReportAgain', $report->id) .'" class="btn btn-primary">Tekrar Ata</a>';
            })
            ->addcolumn('category', function ($report) {
                $category = ReportCategory::withTrashed()->where('id', $report->category_id)->first();
                return $category->name . (is_null($category->is_deleted) ? '' : ' (Silinmiş Birim)');
            })
            ->addColumn('whereFromReject', function ($report){
                if ($report->status == 3){
                    if(User::find($report->last_rejected_user_id)->hasRole('Süper Admin')){
                        return 'Süper Admin Tarafından Reddedildi';
                    }
                    return 'Çek Gönder Admini Tarafından Reddedildi';
                }
                else if ($report->status == 4){
                    return 'Atanılan Birim Admini Tarafından Reddedildi';
                }
            })
            ->addColumn('rejectedUser', function ($report){
                $user = User::withTrashed()->where('id', $report->last_rejected_user_id)->first();
                if (!is_null($user)){
                    return $user->firstname . ' ' . $user->lastname;
                }
                    return '';
            })
            ->addColumn('sendNotification', function ($report){
                if ($report->is_send_rejected_report_notification == 0){
                    return '<button class="btn btn-info" onclick="sendNotification('. $report->id .')">Bildirim Gönder</button>';
                }
                else {
                    return 'Görev Sonuçlandırıldı';
                }
            })
            ->addColumn('processTree', function ($report){
                $tree = ProcessTree::where('report_id', $report->id)->first();
                if (isset($tree->id)){
                    return '<a href="'. \App\Http\Controllers\Dashboard\route('report.processTree', $tree->id) .'" class="btn btn-outline-primary" >İşlem Ağacı</a>';
                }
                else {
                    return '';
                }
            })
            ->addColumn('reporter', function ($report){
                $user = User::withTrashed()->where('id', $report->user_id)->first();

                return $user->firstname . ' ' . $user->lastname;
            })
            ->rawColumns(['assign', 'category', 'processTree', 'sendNotification'])
            ->make(true);
    }

    function assignRejectedReportAgain($id){
        $report = Report::find($id);
        $categories = ReportCategory::all();

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.againAssignRejectedReportFromDepartment', compact('report', 'categories'));
    }

    function waitingReportFromDepartment() {
        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.waitingReportFromDepartment');
    }

    function fetchWaitingReportFromDepartment() {
        $reports = Report::query()->where('status', 1);
        return DataTables::of($reports)
            ->addColumn('detail' , function ($reports) {
                return '<a href="'. \App\Http\Controllers\Dashboard\route('report.waitingReportFromDepartmentDetail', $reports->id) .'" class="btn btn-primary">Detay</a>';
            })
            ->addColumn('user_name_serialized', function ($reports) {
                return $reports->getUser->firstname ." ". $reports->getUser->lastname;
            })
            ->addColumn('category_name_serialized', function ($reports) {
                return $reports->getReportCategory->name;
            })
            ->addColumn('processTree', function ($report){
                $tree = ProcessTree::where('report_id', $report->id)->first();
                if (isset($tree->id)){
                    return '<a href="'. \App\Http\Controllers\Dashboard\route('report.processTree', $tree->id) .'" class="btn btn-outline-primary" >İşlem Ağacı</a>';
                }
                else {
                    return '';
                }
            })
            ->addColumn('reporter', function ($report){
                $user = User::withTrashed()->where('id', $report->user_id)->first();

                return $user->firstname . ' ' . $user->lastname;
            })

            ->rawColumns(['detail','user_name_serialized','category_name_serialized', 'processTree'])
            ->make(true);
    }
    function rejectPatternIndex(){
        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.rejectPattern.index');
    }
    function fetchRejectPattern() {
        $rejectPattern = ReportsRejectPattern::query();

        return DataTables::of($rejectPattern)
            ->addColumn('update', function ($rejectPattern) {
                $userReportType = UserReportType::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->first();
                if (Auth::user()->hasRole('Süper Admin')) {
                    return '<button onclick="updateModalOpen(' . $rejectPattern->id . ')" class="btn btn-warning">Güncelle</button>';
                }
                elseif(isset($userReportType->is_administrator)){
                    if ($userReportType->is_administrator == 1){
                        return '<button onclick="updateModalOpen(' . $rejectPattern->id . ')" class="btn btn-warning">Güncelle</button>';
                    }
                    else {
                        return 'Yetkiniz Bulunmamaktadır.';
                    }
                }
                else{
                    return 'Yetkiniz Bulunmamaktadır.';
                }
            })
            ->addColumn('delete', function ($rejectPattern) {
                $userReportType = UserReportType::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->first();
                if (Auth::user()->hasRole('Süper Admin')) {
                    return '<button onclick="deleteRejectPattern(' . $rejectPattern->id . ')" class="btn btn-danger">Sil</button>';
                }
                elseif(isset($userReportType->is_administrator)){
                    if ($userReportType->is_administrator == 1){
                        return '<button onclick="deleteRejectPattern(' . $rejectPattern->id . ')" class="btn btn-danger">Sil</button>';
                    }
                    else {
                        return 'Yetkiniz Bulunmamaktadır.';
                    }
                }
                else {
                    return 'Yetkiniz Bulunmamaktadır.';
                }
            })
            ->rawColumns(['update','delete'])
            ->make(true);
    }
    function fetchUpdateRejectPattern(Request $request){
        $rejectPattern=ReportsRejectPattern::find($request->id);
        return $rejectPattern;
    }
    function updateRejectPattern(Request $request){
        $rejectPattern=ReportsRejectPattern::find($request->id);
        $rejectPattern->name= $request->name;
        $rejectPattern->content= $request->content;
        $rejectPattern->save();
        return \App\Http\Controllers\Dashboard\response()->json(['status'=>'success','content'=>'Reddetme şablonu başarıyla güncellendi.']);
    }
    function rejectPatternGetContent(Request $request){
        if ($request->id=="other"){
            return \App\Http\Controllers\Dashboard\response()->json(['content'=>'']);
        }
        $rejectPattern=ReportsRejectPattern::find($request->id);
        return \App\Http\Controllers\Dashboard\response()->json(['content'=>$rejectPattern->content]);
    }
    function createRejectPattern(Request $request){
        $rejectPattern=ReportsRejectPattern::create([
            'name'=>$request->name,
            'content'=>$request->content
        ]);
        return \App\Http\Controllers\Dashboard\response()->json(['status'=>'success','content'=>'Reddetme şablonu başarıyla oluşturuldu.']);
    }
    function deleteRejectPattern(Request $request){
        $rejectPattern=ReportsRejectPattern::find($request->id);
        $rejectPattern->delete();
        return \App\Http\Controllers\Dashboard\response()->json(['status'=>'success','content'=>'Reddetme şablonu başarıyla silindi.']);
    }

    function waitingReportFromDepartmentDetail($id) {
        $reports = Report::find($id);
        $neighbourhood = Neighbourhood::withTrashed()->where('id', $reports->neighbourhood_id)->first();
        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.waitingReportFromDepartmentDetail', compact('reports', 'neighbourhood'));
    }

    function notifyIndex(){
        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.notifySchema');
    }

    function fetchNotify(){
        $notifies = NotifySchema::query();

        return DataTables::of($notifies)
            ->addColumn('update', function ($notify){
                return '<button onclick="openUpdateModal('. $notify->id .')" class="btn btn-primary">Güncelle</button>';
            })
            ->addColumn('delete', function ($notify){
                return '<button class="btn btn-danger" onclick="deleteSchema('. $notify->id .')">Sil</button>';
            })
            ->rawColumns(['update', 'delete'])
            ->make();
    }

    function createNotifyScheme(Request $request){
        $notify = new NotifySchema();
        $notify->name = $request->name;
        $notify->content = $request->content;

        $notify->save();

        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }

    function getSchema(Request $request){
        $schema = NotifySchema::find($request->id);

        return \App\Http\Controllers\Dashboard\response()->json($schema);
    }

    function updateSchema(Request $request){
        $schema = NotifySchema::find($request->id);
        $schema->name = $request->name;
        $schema->content = $request->content;

        $schema->save();

        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }

    function deleteSchema(Request $request){
        $schema = NotifySchema::find($request->id);

        $schema->delete();

        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }

    function sendMissionNotification(Request $request){
        if ($request->notify_id != 0){
            $notify = NotifySchema::find($request->notify_id);
            $message = $notify->content;
        }
        else {
            $message = $request->notify_text;
        }
        $mission = Mission::find($request->mission_id);

        if ($mission->status == 1){
            if (isset($mission->getReport->getUser)){
                if ($mission->getReport->getUser->firebase_token != null){
                    Helper::pushNotificationToCurl('Gönderdiğiniz Şikayet Çözüldü !', $message, $mission->getReport->getUser->firebase_token, $mission->getReport, "missionDone");
                }
            }

            $processContent = Auth::user()->firstname . ' ' . Auth::user()->lastname . ' tarafından çözülme bildirimi gönderildi <br> Gönderilen Bildirim: ' . $message ;
            Helper::saveProcess(Auth::user()->id, $mission->report_id, 'Çözüm Bildirimi Gönderildi', $processContent);
            $mission->is_notify = 1;
            $mission->notify_content = $message;
            $mission->notify_type = 0;
        }

        elseif($mission->status == 2){
            $processContent = Auth::user()->firstname . ' ' . Auth::user()->lastname . ' tarafından asılsız bildirimi gönderildi <br> Gönderilen Bildirim: ' . $message ;
            Helper::saveProcess(Auth::user()->id, $mission->report_id, 'Asılsız Bildirimi Gönderildi', $processContent);
            $mission->is_notify = 1;
            $mission->notify_content = $message;
            $mission->notify_type = 1;
        }

        $mission->save();

        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }

    function sendNotification(Request $request){
        if ($request->notify_id != 0){
            $notify = NotifySchema::find($request->notify_id);
            $message = $notify->content;
        }
        else {
            $message = $request->notify_text;
        }
        $mission = Mission::find($request->mission_id);

        if (isset($mission->getReport->getUser)){
            if ($mission->getReport->getUser->firebase_token != null){
                $title = $mission->report_id . ' No\'lu Şikayet Hakkında !';
                $res = Helper::pushNotificationToCurl($title, $message, $mission->getReport->getUser->firebase_token, $mission->getReport, "missionNotification");
            }
        }

        $notification = new MissionNotification();
        $notification->mission_id = $mission->id;
        $notification->content = $message;
        $notification->save();

        $processContent = Auth::user()->firstname . ' ' . Auth::user()->lastname . ' tarafından ara bildirim gönderildi <br> Gönderilen Bildirim: ' . $message ;
        Helper::saveProcess(Auth::user()->id, $mission->report_id, 'Ara Bildirimi Gönderildi', $processContent);

        return \App\Http\Controllers\Dashboard\response()->json(['message' => $res]);
    }

    function exportReportExcel(Request $request){
        $type = $request->type;
        $unit = $request->unit;
        $excelPageOption = [];
        if ($type == 0){
            $excelPageOption = ['Görevler','Şikayetler','Reddedilen Görevler', 'Adminler Tarafından Reddedilen Şikayetler'];
        }
        elseif ($type == 1){
            $excelPageOption = ['Görevler'];
        }
        elseif ($type == 2){
            $excelPageOption = ['Şikayetler'];
        }
        elseif ($type == 3){
            $excelPageOption = ['Reddedilen Görevler'];
        }
        elseif($type == 4){
            $excelPageOption = ['Adminler Tarafından Reddedilen Şikayetler'];
        }

        $missionFilter = [
            'missionStatus' => $request->missionStatus,
            'neighbourhood' => $request->neighbourhood,
            'missionFixedStart' => $request->missionFixedStart,
            'missionFixedEnd' => $request->missionFixedEnd,
            'assigmentStartDate' => $request->assigmentStartDate,
            'assigmentEndDate' => $request->assigmentEndDate,
            'assigmentEmployeeStartDate' => $request->assigmentEmployeeStartDate,
            'assigmentEmployeeEndDate' => $request->assigmentEmployeeEndDate,

        ];

        $reportFilter = [
            'reportStatus' => $request->reportStatus,
            'neighbourhoodReport' => $request->neighbourhoodReport,
            'reportCreateDateStart' => $request->reportCreateDateStart,
            'reportCreateDateEnd' => $request->reportCreateDateEnd,
        ];

        $rejectedMissionFilter = [
            'missionRejectDateStart' => $request->missionRejectDateStart,
            'missionRejectDateEnd' => $request->missionRejectDateEnd,
            'rejectedMissionNeighbourhood' => $request->rejectedMissionNeighbourhood,
        ];

        $rejectedReportFilter = [
            'reportRejectDateStart' => $request->reportRejectDateStart,
            'reportRejectDateEnd' => $request->reportRejectDateEnd,
            'rejectedReportNeighbourhood' => $request->rejectedReportNeighbourhood,
        ];


        return Excel::download(new ReportExport($excelPageOption, $unit, $type, ['reportFilter' => $reportFilter, 'missionFilter' => $missionFilter, 'rejectedMission' => $rejectedMissionFilter, 'rejectedReport' => $rejectedReportFilter]), 'report.xlsx');
    }

    function exportExcelPage(){
        $reportCategories = ReportCategory::all();
        $neighbourhoods = Neighbourhood::withTrashed()->get();
        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.exportExcelFilter', compact('reportCategories', 'neighbourhoods'));
    }

    function processTree($id){
        $tree = ProcessTree::find($id);

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.processTree', compact('tree'));
    }

    function rollbackMission(Request $request){
        $mission = Mission::find($request->missionID);
        $employee = User::find($request->employeeID);
        $oldEmployee = User::find($mission->assigned_user_id);

        $processContent = Auth::user()->firstname . ' ' . Auth::user()->lastname . ' tarafından görev ' . $oldEmployee->firstname . ' ' . $oldEmployee->lastname .
            ' personelinden alınarak ' . $employee->firstname . ' ' . $employee->lastname . ' personeline atamıştır';
        Helper::saveProcess(Auth::user()->id, $mission->report_id, 'Atanan Personel Değiştirildi', $processContent);

        $mission->assigned_user_id = $request->employeeID;
        $mission->is_approved = 0;
        $mission->approved_date = null;
        $mission->appointing_id = Auth::user()->id;
        $mission->mission_assigment_date = Carbon::now();
        $mission->status = 0;
        $mission->save();

        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }



    function sendRejectedReportNotification(Request $request){
        $report = Report::find($request->id);
        Helper::pushNotificationToCurl('Yeni Bir Bildiriminiz Var !', $request->content, $report->getUser->firebase_token, $report->id, 'rejectedReportNotification');

        $title = 'Reddedilen Çek Gönder Bildirimi Gönderildi';
        $content = Auth::user()->firstname . ' ' . Auth::user()->lastname .
            ' tarafından reddedilen ' . $report->id . ' ID\'li çek gönderin bildirimi gönderildi. <br>Gönderilen Bildirim : <br>'.
            $request->titlle . '<br>' . $request->content;
        Helper::saveProcess(Auth::user()->id, $report->id, $title, $content);

        $report->is_send_rejected_report_notification = 1;
        $report->rejected_report_notification_content = $request->content;
        $report->rejected_report_notification_sender_id = Auth::user()->id;
        $report->notification_date = Carbon::now();

        $report->save();

        return \App\Http\Controllers\Dashboard\response()->json(['message' => 'success']);
    }

    public function missionsSolveIndex($id){
        $mission = Mission::find($id);

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.missionsSolveIndex', compact('mission'));
    }
    public function missionsSolved(Request $request)
    {
        $request->validate([

           'image_path_url' =>'required',
            'mission_reason_description'=>'required'
        ],
            [
            'image_path_url.required'=>'Fotoğraf alanı boş bırakılamaz',
                'mission_reason_description.required'=>'Açıklama alanı boş bırakılamaz'

            ]);


        $mission = Mission::find($request->id);
        $mission->mission_solution_description = $request->mission_solution_description;
        $mission->mission_reason_description = $request->mission_reason_description;
        $mission->solves_missions_user_id = Auth::user()->id;

        $imageGalery = new ImageGalery();
        $imageGalery->name = time();
        $imageGalery->save();

        foreach ($request->file('image_path_url') as $item) {
            $imageRow = new Image();
            $name = time() . $item->getClientOriginalName();
            $item->move(\App\Http\Controllers\Dashboard\public_path('/reportImages'), $name);

            $imageRow->image_path_url = '/reportImages/' . $name;
            $imageRow->image_gallery_id = $imageGalery->id;
            $imageRow->save();
        }

        $mission->status = 1;
        $mission->image_gallery_id = $imageGalery->id;
        $mission->done_date = Carbon::now()->toDateTimeString();
        $mission->save();

        $user = User::find($mission->assigned_user_id);
        $user->job_count += 1;
        $user->save();

        $processContent =$user->firstname . ' ' . $user->lastname . ' personeline atanan görev ' . ' ' . Auth::user()->firstname . ' ' . Auth::user()->lastname . ' ' . ' tarafından çözüldü' ;
        Helper::saveProcess($user->id, $mission->report_id, 'Şikayet Web Panelinden Çözüldü', $processContent);

        return \App\Http\Controllers\Dashboard\redirect()->route('report.missions');
    }

    public function missionsFakeIndex($id){
        $mission = Mission::find($id);

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.report.fakeMissionIndex', compact('mission'));
    }

    function missionsFake(Request $request)
    {

        $request->validate([
            'image_path_url'=>'required',
            'why_mission_is_fake'=>'required'
            ],
            [
                'image_path_url.required'=>'Fotoğraf alanı boş bırakılamaz',
                'why_mission_is_fake.required'=>'Asılsız olma sebebi boş bırakılamaz'
        ]);

        $mission = Mission::find($request->id);
        $imageGalery = new ImageGalery();
        $imageGalery->name = time();
        $imageGalery->save();

        foreach ($request->file('image_path_url') as $item) {
            $imageRow = new Image();
            $name = time() . $item->getClientOriginalName();
            $item->move(\App\Http\Controllers\Dashboard\public_path('/reportImages'), $name);

            $imageRow->image_path_url = '/reportImages/' . $name;
            $imageRow->image_gallery_id = $imageGalery->id;
            $imageRow->save();
        }

        $mission->status = 2;
        $mission->image_gallery_id = $imageGalery->id;
        $mission->why_mission_is_fake = $request->why_mission_is_fake;
        $mission->solves_missions_user_id = Auth::user()->id;
        $mission->done_date = Carbon::now()->toDateTimeString();

        $mission->save();

        $user = User::find($mission->getReport->user_id);
        $user->send_check_count = $user->send_check_count - 1;
        $user->save();

        $personal = User::find($mission->assigned_user_id);
        $personal->job_count += 1;
        $personal->save();

        $processContent = $personal->firstname . ' ' . $personal->lastname . '/a' . ' atanan şikayet' . ' ' . Auth::user()->firstname . ' ' . Auth::user()->lastname . ' ' . ' tarafından asılsız olarak bildirildi ' . ' ' . '<br>Sebebi: ' . $request->why_mission_is_fake;
        Helper::saveProcess($personal->id, $mission->report_id, 'Şikayet Web Panelinden Asılsız Olarak İşaretlendi', $processContent);

        return \App\Http\Controllers\Dashboard\redirect()->route('report.missions');
    }



}
