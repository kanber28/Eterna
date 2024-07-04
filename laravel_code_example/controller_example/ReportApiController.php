<?php
use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\ImageGalery;
use App\Models\Mission;
use App\Models\Neighbourhood;
use App\Models\RejectedMission;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use App\Models\UserReportType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportApiController extends Controller
{
    function createReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'address' => 'required',
            'category_id' => 'required',
            'images.*' => 'required|mimes:jpeg,jpg,bmp,png',
            'lat' => 'required',
            'lng' => 'required',
            'neighbourhood_id' => 'required',
        ]);

        if ($validator->fails()) {
            return \App\Http\Controllers\Api\response()->json(['error' => $validator->errors()], 400);
        }

        $imageGalery = new ImageGalery();
        $imageGalery->name = time();
        $imageGalery->save();
        foreach ($request->file('images') as $image) {
            $imageRow = new Image();
            $name = time() . $image->getClientOriginalName();
            $image->move(\App\Http\Controllers\Api\public_path('/reportImages'), $name);
            $imageRow->image_path_url = '/reportImages/' . $name;
            $imageRow->image_gallery_id = $imageGalery->id;
            $imageRow->save();
        }
        $reports = new Report();

        $reports->description = $request->description;
        $reports->address = $request->address;
        $reports->category_id = $request->category_id;
        $reports->user_id = $request->user_id;
        $reports->status = 0;
        $reports->lat = $request->lat;
        $reports->lng = $request->lng;
        $reports->neighbourhood_id = $request->neighbourhood_id;
        $reports->image_gallery_id = $imageGalery->id;

        $reports->save();


        $user = User::find($request->user_id);
        $user->send_check_count = $user->send_check_count + 1;
        $user->save();

        $processContent = 'Şikayet ' . $user->firstname . ' ' . $user->lastname . ' tarafından Oluşturuldu';
        Helper::saveProcess($request->user_id, $reports->id, 'Şikayet Oluşturuldu', $processContent);

        return \App\Http\Controllers\Api\response()->json(['message' => 'success']);
    }

    function getMission(Request $request)
    {
        $time = $request->type;

        if ($time == 1) {
            $responseArr = [];
            $month = Carbon::now()->format('m');
            $year = Carbon::now()->format('Y');
            $missions = Mission::where('assigned_user_id',
                $request->user_id)->where('status', 0)->whereMonth('mission_assigment_date',
                $month)->whereYear('mission_assigment_date',
                $year)->with('getCategory')->orderBy('id',
                'DESC')->skip($request->page *
                $request->page_size)->take($request->page_size)->get();
            foreach ($missions as $mission) {
                if (isset($mission->getGallery->getImage)) {
                    array_push($responseArr, ['mission' => $mission,
                        'images' => $mission->getGallery->getImage, 'reportImage' =>
                            (isset($mission->getReport->getGallery->getImage) ?
                                $mission->getReport->getGallery->getImage : null)]);
                } else {
                    array_push($responseArr, ['mission' => $mission,
                        'images' => null, 'reportImage' =>
                            (isset($mission->getReport->getGallery->getImage) ?
                                $mission->getReport->getGallery->getImage : null)]);
                }
            }
            return \App\Http\Controllers\Api\response()->json($responseArr);
        } else if ($time == 2) {
            $responseArr = [];
            $month = Carbon::now()->format('m');
            $year = Carbon::now()->format('Y');
            if ($month < 4) {
                $year--;
                $month = $month + 12 - 3;
            } else {
                $month -= 3;
            }
            $startingTime = Carbon::createFromDate($year, $month, 01);
            $missions = Mission::where('assigned_user_id', $request->user_id)
                ->where('status', 0)
                ->where('mission_assigment_date', '>=', $startingTime)->with('getCategory')->orderBy('id', 'DESC')
                ->skip($request->page * $request->page_size)
                ->take($request->page_size)->get();

            foreach ($missions as $mission) {
                if (isset($mission->getGallery->getImage)) {
                    array_push($responseArr, ['mission' => $mission,
                        'images' => $mission->getGallery->getImage, 'reportImage' =>
                            isset($mission->getReport->getGallery->getImage) ?
                                $mission->getReport->getGallery->getImage : null]);
                } else {
                    array_push($responseArr, ['mission' => $mission,
                        'images' => $mission->getGallery, 'reportImage' =>
                            isset($mission->getReport->getGallery->getImage) ?
                                $mission->getReport->getGallery->getImage : null]);
                }
            }

            return \App\Http\Controllers\Api\response()->json($responseArr);
        } else if ($time == 3) {
            $responseArr = [];
            $missions = Mission::where('assigned_user_id',
                $request->user_id)->where('status',
                0)->with('getCategory')->orderBy('id', 'DESC')->skip($request->page *
                $request->page_size)->take($request->page_size)->get();

            foreach ($missions as $mission) {
                if (isset($mission->getGallery->getImage)) {
                    array_push($responseArr, ['mission' => $mission,
                        'images' => $mission->getGallery->getImage, 'reportImage' =>
                            isset($mission->getReport->getGallery->getImage) ?
                                $mission->getReport->getGallery->getImage : null]);
                } else {
                    array_push($responseArr, ['mission' => $mission,
                        'images' => null, 'reportImage' =>
                            isset($mission->getReport->getGallery->getImage) ?
                                $mission->getReport->getGallery->getImage : null]);
                }
            }

            return \App\Http\Controllers\Api\response()->json($responseArr);
        }
    }

    function rejectMission(Request $request)
    {
        $request->validate([
            'mission_id' => 'required',
            'reason_of_reject' => 'required',
            'rejected_user_id' => 'required'
        ]);

       if (RejectedMission::where('rejected_user_id','=',intval($request->rejected_user_id))->where('created_at','>=',date("Y-m")."-00")->count()+1>3){
           return \App\Http\Controllers\Api\response()->json(['message' => 'Bu ayki reddetme sınırınız dolmuştur.'],400);
       }
       else{
           $mission = Mission::find($request->mission_id);
           $mission->status = 3;
           $mission->is_approved = 0;
           $mission->save();
           $rejectedMission = new RejectedMission();
           $rejectedMission->mission_id = $mission->id;
           $rejectedMission->reason_of_reject = $request->reason_of_reject;
           $rejectedMission->rejected_user_id = $request->rejected_user_id;
           $rejectedMission->save();

           $user = User::find($request->rejected_user_id);
           $processContent = $user->firstname . ' ' . $user->lastname . ' personeli atanan şikayeti reddetti <br> Red Sebebi : ' . $request->reason_of_reject;
           Helper::saveProcess($user->id, $mission->report_id, 'Şikayet Personel Tarafından Reddedildi', $processContent);

           return \App\Http\Controllers\Api\response()->json(['message' => 'success']);
       }
    }

    function missionDone(Request $request)
    {

        $mission = Mission::find($request->mission_id);
        $mission->mission_solution_description = $request->mission_solution_description;
        $mission->mission_reason_description = $request->mission_reason_description;

        $imageGalery = new ImageGalery();
        $imageGalery->name = time();
        $imageGalery->save();

        foreach ($request->file('images') as $item) {
            $imageRow = new Image();
            $name = time() . $item->getClientOriginalName();
            $item->move(\App\Http\Controllers\Api\public_path('/reportImages'), $name);

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

//        if (isset($mission->getReport->getUser)){
//            if ($mission->getReport->getUser->firebase_token != null){
//                $message = $mission->getReport->id. " No'lu Şikayetiniz Çözüme Kavuşturuldu !";
//                Helper::pushNotificationToCurl('Gönderdiğiniz Şikayet Çözüldü !', $message, $mission->getReport->getUser->firebase_token, $mission->getReport, "missionDone");
//            }
//        }

        $processContent = 'Şikayet ' . $user->firstname . ' ' . $user->lastname . ' tarafından çözüldü.';
        Helper::saveProcess($user->id, $mission->report_id, 'Şikayet Personel Tarafından Çözüldü', $processContent);

        return \App\Http\Controllers\Api\response()->json(['message' => 'success']);
    }

    function approveMission(Request $request)
    {
        $mission = Mission::find($request->mission_id);
        $mission->is_approved = 1;
        $mission->approved_date = Carbon::now()->toDateTimeString();
        $mission->save();

        $user = User::withTrashed()->where('id', $mission->assigned_user_id)->first();
        $processContent = 'Şikayet ' . $user->firstname . ' ' . $user->lastname . ' isimli personel tarafından kabul edildi.';
        Helper::saveProcess($user->id, $mission->report_id, 'Şikayet Personel Tarafından Kabul Edildi', $processContent);

        if (isset($mission->getReport->getUser)){
            if ($mission->getReport->getUser->firebase_token != null){
                $message = $mission->getReport->created_at. " Tarihinde Gönderdiğniz Şikayet İlgili Personele Aktarıldı";
                Helper::pushNotificationToCurl('Gönderdiğiniz Şikayet Görevliye Aktarıldı !', $message, $mission->getReport->getUser->firebase_token, $mission->getReport, "missionProcess");
            }
        }

        return \App\Http\Controllers\Api\response()->json(['message' => 'success']);
    }

    function fakeMission(Request $request)
    {
        $mission = Mission::find($request->mission_id);
        $imageGalery = new ImageGalery();
        $imageGalery->name = time();
        $imageGalery->save();

        foreach ($request->file('images') as $item) {
            $imageRow = new Image();
            $name = time() . $item->getClientOriginalName();
            $item->move(\App\Http\Controllers\Api\public_path('/reportImages'), $name);

            $imageRow->image_path_url = '/reportImages/' . $name;
            $imageRow->image_gallery_id = $imageGalery->id;
            $imageRow->save();
        }
        $mission->status = 2;
        $mission->image_gallery_id = $imageGalery->id;
        $mission->why_mission_is_fake = $request->why_mission_is_fake;
        $mission->done_date = Carbon::now()->toDateTimeString();

        $mission->save();

        $user = User::find($mission->getReport->user_id);
        $user->send_check_count = $user->send_check_count - 1;
        $user->save();

        $personal = User::find($mission->assigned_user_id);
        $personal->job_count += 1;
        $personal->save();

        $processContent = 'Şikayet ' . $personal->firstname . ' ' . $personal->lastname . ' tarafından asılsız olarak işaretlendi. <br>Sebebi: ' . $request->why_mission_is_fake;
        Helper::saveProcess($personal->id, $mission->report_id, 'Şikayet Personel Tarafından Asılsız Olarak İşaretlendi', $processContent);

        return \App\Http\Controllers\Api\response()->json(['message' => 'success']);
    }

    function missionDetail(Request $request)
    {
        $mission = Mission::where('id', $request->mission_id)->with('getReport.getNeighbourhood')->first();

        if (!is_null($mission)) {
            if ($mission->is_approved == 1 || $mission->status == 1) {
                return \App\Http\Controllers\Api\response()->json(['message' => 'success',
                    'mission' => $mission, 'reportImages' =>
                        isset($mission->getReport->getGallery->getImage) ?
                            $mission->getReport->getGallery->getImage : null, 'getCategory' =>
                        $mission->getCategory, 'missionImage' =>
                        (isset($mission->getGallery->getImage) ?
                            $mission->getGallery->getImage : null)]);
            } else {
                return \App\Http\Controllers\Api\response()->json(['message' => 'error',
                    'messageDescription' => 'Detaylar İçin Görevi Kabul Etmeniz Gerekir']);
            }
        } else {
            return \App\Http\Controllers\Api\response()->json(['message' => 'error',
                'messageDescription' => 'Böyle Bir Görev Bulunamadı']);
        }


    }

    function missionDetailForAdmin(Request $request)
    {
        $mission = Mission::where('id', $request->mission_id)->with('getReport.getNeighbourhood')->first();

        if (isset($mission)) {
            return \App\Http\Controllers\Api\response()->json(['message' => 'success', 'mission' => $mission,
                'reportImages' => isset($mission->getReport->getGallery->getImage) ? $mission->getReport->getGallery->getImage : null, 'getCategory' => $mission->getCategory,
                'missionImage' => (isset($mission->getGallery->getImage) ? $mission->getGallery->getImage : null)]);
        }
        else {
            return \App\Http\Controllers\Api\response()->json(['message' => 'error', 'messageDescription' => 'Böyle Bir Görev Bulunamadı']);
        }
    }

    function getDepartment()
    {
        $departments = ReportCategory::all();

        return \App\Http\Controllers\Api\response()->json($departments);
    }

    function rejectedMission(Request $request)
    {
        $time = $request->type;
        if ($time == 1) {
            $month = Carbon::now()->format('m');
            $year = Carbon::now()->format('Y');
            $rejectedMission =
                RejectedMission::where('rejected_user_id',
                    $request->user_id)->orderBy('id', 'DESC')->whereMonth('created_at',
                    $month)->whereYear('created_at', $year)->with('getMission',
                    'getRejectedUser')->orderBy('id', 'DESC')->skip($request->page *
                    $request->page_size)->take($request->page_size)->get();
            $rejectedReportArr = [];
            foreach ($rejectedMission as $item) {
                array_push($rejectedReportArr, ['rejectedMission' =>
                    $item, 'getCategory' => $item->getMission->getCategory]);
            }
            return \App\Http\Controllers\Api\response()->json($rejectedReportArr);
        } else if ($time == 2) {
            $month = Carbon::now()->format('m');
            $year = Carbon::now()->format('Y');
            if ($month < 4) {
                $year--;
                $month = $month + 12 - 3;
            } else {
                $month -= 3;
            }
            $startingTime = Carbon::createFromDate($year, $month, 01);

            $rejectedMission =
                RejectedMission::where('rejected_user_id',
                    $request->user_id)->where('created_at', '>=',
                    $startingTime)->orderBy('id', 'DESC')->with('getMission',
                    'getRejectedUser')->orderBy('id', 'DESC')->skip($request->page *
                    $request->page_size)->take($request->page_size)->get();
            $rejectedReportArr = [];
            foreach ($rejectedMission as $item) {
                array_push($rejectedReportArr, ['rejectedMission' =>
                    $item, 'getCategory' => $item->getMission->getCategory]);
            }
            return \App\Http\Controllers\Api\response()->json($rejectedReportArr);
        } else if ($time == 3) {
            $rejectedMission =
                RejectedMission::where('rejected_user_id',
                    $request->user_id)->orderBy('id', 'DESC')->with('getMission',
                    'getRejectedUser')->orderBy('id', 'DESC')->skip($request->page *
                    $request->page_size)->take($request->page_size)->get();
            $rejectedReportArr = [];
            foreach ($rejectedMission as $item) {
                array_push($rejectedReportArr, ['rejectedMission' =>
                    $item, 'getCategory' => $item->getMission->getCategory]);
            }
            return \App\Http\Controllers\Api\response()->json($rejectedReportArr);
        }
    }

    function getDoneMissions(Request $request)
    {
        $time = $request->type;
        if ($time == 1) {
            $month = Carbon::now()->format('m');
            $year = Carbon::now()->format('Y');
            $missions = Mission::where('assigned_user_id',
                $request->user_id)->orderBy('id', 'DESC')->whereBetween('status', [1,
                2])->whereMonth('created_at', $month)->whereYear('created_at',
                $year)->with('getReport', 'getCategory')->skip($request->page *
                $request->page_size)->take($request->page_size)->get();
            return \App\Http\Controllers\Api\response()->json($missions);
        } else if ($time == 2) {
            $month = Carbon::now()->format('m');
            $year = Carbon::now()->format('Y');
            if ($month < 4) {
                $year--;
                $month = $month + 12 - 3;
            } else {
                $month -= 3;
            }
            $startingTime = Carbon::createFromDate($year, $month, 01);
            $missions = Mission::where('assigned_user_id',
                $request->user_id)->orderBy('id', 'DESC')->whereBetween('status', [1,
                2])->where('created_at', '>=', $startingTime)->with('getReport',
                'getCategory')->skip($request->page *
                $request->page_size)->take($request->page_size)->get();
            return \App\Http\Controllers\Api\response()->json($missions);
        } else if ($time == 3) {
            $missions = Mission::where('assigned_user_id',
                $request->user_id)->orderBy('id', 'DESC')->whereBetween('status', [1,
                2])->with('getReport', 'getCategory')->skip($request->page *
                $request->page_size)->take($request->page_size)->get();
            return \App\Http\Controllers\Api\response()->json($missions);
        }
    }

    function getReports(Request $request)
    {
        $reports = Report::withTrashed()->where('user_id',
            $request->user_id)->orderBy('id',
            'DESC')->with('getReportCategory')->skip($request->page *
            $request->page_size)->take($request->page_size)->get();
        $responseArray = [];

        foreach ($reports as $item) {
            if ($item->status == 0) {
                array_push($responseArray, ['report' => $item,
                    'reportImages' => (isset($item->getGalerry->getImage) ?
                        $item->getGalerry->getImage : null), 'status' => 'Beklemede']);
            } else if ($item->status == 1) {
                array_push($responseArray, ['report' => $item,
                    'reportImages' => (isset($item->getGalerry->getImage) ?
                        $item->getGalerry->getImage : null), 'status' => 'Birime
Yönlendirildi']);
            } else if ($item->status == 3) {
                array_push($responseArray, ['report' => $item,
                    'reportImages' => (isset($item->getGalerry->getImage) ?
                        $item->getGalerry->getImage : null), 'status' => 'Reddedildi']);
            } else if ($item->status == 2) {
                if ($item->getMission->status == 0) {
                    array_push($responseArray, ['report' => $item,
                        'reportImages' => (isset($item->getGalerry->getImage) ?
                            $item->getGalerry->getImage : null), 'status' => 'Personel
Görevlendirildi']);
                } else if ($item->getMission->status == 1) {
                    array_push($responseArray, ['report' => $item,
                        'reportImages' => (isset($item->getGalerry->getImage) ?
                            $item->getGalerry->getImage : null), 'status' => 'Çözüldü']);
                } else if ($item->getMission->status == 2) {
                    array_push($responseArray, ['report' => $item,
                        'reportImages' => (isset($item->getGalerry->getImage) ?
                            $item->getGalerry->getImage : null), 'status' => 'Asılsız']);
                } else if ($item->getMission->status == 3) {
                    array_push($responseArray, ['report' => $item,
                        'reportImages' => (isset($item->getGalerry->getImage) ?
                            $item->getGalerry->getImage : null), 'status' => 'Yeniden Birime
Yönlendirildi']);
                }
            }
        }
        return \App\Http\Controllers\Api\response()->json($responseArray);
    }

    function cancelReport(Request $request)
    {
        $report = Report::find($request->report_id);
        $user = User::find($report->user_id);
        $user->send_check_count = $user->send_check_count - 1;
        $user->save();
        if ($report->status == 0) {
            $report->delete();
            return \App\Http\Controllers\Api\response()->json(['message' => 'success']);
        } else {
            return \App\Http\Controllers\Api\response()->json(['message' => 'error']);
        }

    }

    function reportDetail(Request $request)
    {
        $report = Report::withTrashed()->where('id',
            $request->report_id)->with('getNeighbourhood')->first();

        return \App\Http\Controllers\Api\response()->json(['report' => $report, 'reportImage' =>
            $report->getGallery->getImage, 'getReportCategory' =>
            $report->getReportCategory, 'getMission' => $report->getMission,
            'getMissionImage' => isset($report->getMission->getGallery->getImage)
                ? $report->getMission->getGallery->getImage : null]);
    }

    function getStatistic(Request $request)
    {
        $time = $request->type;
        $user = User::find($request->user_id);
        if ($time == 1) {
            $month = Carbon::now()->format('m');
            $year = Carbon::now()->format('Y');
            $rejectedMission =
                RejectedMission::where('rejected_user_id',
                    $user->id)->whereMonth('created_at', $month)->whereYear('created_at',
                    $year)->count();
            $doneMission = Mission::where('assigned_user_id',
                $user->id)->where('status', 1)->whereMonth('created_at',
                $month)->whereYear('created_at', $year)->count();
            $fakeMission = Mission::where('assigned_user_id',
                $user->id)->where('status', 2)->whereMonth('created_at',
                $month)->whereYear('created_at', $year)->count();

            return \App\Http\Controllers\Api\response()->json(['rejectedMission' =>
                $rejectedMission, 'doneMission' => $doneMission, 'fakeMission' =>
                $fakeMission]);
        } else if ($time == 2) {
            $month = Carbon::now()->format('m');
            $year = Carbon::now()->format('Y');
            if ($month < 4) {
                $year--;
                $month = $month + 12 - 3;
            } else {
                $month -= 3;
            }
            $startingTime = Carbon::createFromDate($year, $month, 01);
            $rejectedMission =
                RejectedMission::where('rejected_user_id',
                    $user->id)->where('created_at', '>=', $startingTime)->count();
            $doneMission = Mission::where('assigned_user_id',
                $user->id)->where('status', 1)->where('created_at', '>=',
                $startingTime)->count();
            $fakeMission = Mission::where('assigned_user_id',
                $user->id)->where('status', 2)->where('created_at', '>=',
                $startingTime)->count();

            return \App\Http\Controllers\Api\response()->json(['rejectedMission' =>
                $rejectedMission, 'doneMission' => $doneMission, 'fakeMission' =>
                $fakeMission]);

        } else if ($time = 3) {
            $rejectedMission =
                RejectedMission::where('rejected_user_id', $user->id)->count();
            $doneMission = Mission::where('assigned_user_id',
                $user->id)->where('status', 1)->count();
            $fakeMission = Mission::where('assigned_user_id',
                $user->id)->where('status', 2)->count();

            return \App\Http\Controllers\Api\response()->json(['rejectedMission' =>
                $rejectedMission, 'doneMission' => $doneMission, 'fakeMission' =>
                $fakeMission]);
        }


    }

    function getNeighbourhoodList(){
        $neighbourhood = Neighbourhood::all();

        return \App\Http\Controllers\Api\response()->json($neighbourhood);
    }

    function reportAdminStatus(Request $request){
        $reportAdminStatus = UserReportType::where('user_id', $request->user_id)->first();

        return \App\Http\Controllers\Api\response()->json(['reportAdminStatus' => $reportAdminStatus]);
    }

    function getAdministratorEmployee(Request $request){
        $employees = User::where('user_employee_category', $request->report_category_id)
            ->skip($request->page * $request->page_size)->take($request->page_size)->get();
        $employeeArr = [];
        foreach ($employees as $employee){
            $missionsCount = Mission::where('assigned_user_id', $employee->id)->where('status', 0)->count();
            $tmpArr = ['employee' => $employee, 'waitingMissionCount' => $missionsCount];
            array_push($employeeArr, $tmpArr);
        }


        return \App\Http\Controllers\Api\response()->json($employeeArr);
    }

    function missionListForAdmin(Request $request){
        $missions = Mission::where('assigned_user_id', $request->user_id)->where('status', 0)
            ->skip($request->page * $request->page_size)->take($request->page_size)->get();

        return \App\Http\Controllers\Api\response()->json(['missions' => $missions]);
    }

}
