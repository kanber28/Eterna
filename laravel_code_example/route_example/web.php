<?php

use App\Http\Controllers\Dashboard\DiscoverInterfaceController;
use App\Http\Controllers\Dashboard\GaleryController;
use App\Http\Controllers\Dashboard\MapController;
use App\Http\Controllers\Dashboard\MobileUserController;
use App\Http\Controllers\Dashboard\PharmacyController;
use App\Http\Controllers\Dashboard\SocialFacilitiesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;
use \App\Http\Controllers\Dashboard\CulturalCentersController;
use \App\Http\Controllers\Dashboard\MarketPlacesController;
use \App\Http\Controllers\Dashboard\LibraryController;
use \App\Http\Controllers\Dashboard\NotaryController;
use \App\Http\Controllers\Dashboard\PlaceOfWorshipController;
use \App\Http\Controllers\Dashboard\TaxiController;
use \App\Http\Controllers\Dashboard\ContactFormController;
use \App\Http\Controllers\Dashboard\AnnouncementController;
use \App\Http\Controllers\Dashboard\EmployeeController;
use \App\Http\Controllers\Dashboard\EventController;
use \App\Http\Controllers\Dashboard\MayorController;
use \App\Http\Controllers\Dashboard\MobileInterfaceController;
use \App\Http\Controllers\Dashboard\NotificationController;
use \App\Http\Controllers\Dashboard\ReportController;
use \App\Http\Controllers\Dashboard\StatisticsController;
use \App\Http\Controllers\Dashboard\StoryController;
use \App\Http\Controllers\Dashboard\UserController;
use \App\Http\Controllers\Dashboard\WriteToMayorController;
use \App\Http\Controllers\Dashboard\roleContoller;
use \App\Http\Controllers\Dashboard\JobApplicationController;
use App\Http\Controllers\Dashboard\PublicRelationController;
use App\Http\Controllers\Dashboard\SportController;
use App\Http\Controllers\Dashboard\EducationController;
use App\Http\Controllers\Dashboard\WomanFamilyController;
use App\Http\Controllers\Dashboard\HealthController;
use App\Http\Controllers\Dashboard\NurseryController;
use App\Http\Controllers\Dashboard\VeterinaryController;
use App\Http\Controllers\Dashboard\Rehabilitation;
use App\Http\Controllers\Dashboard\RepublicEducationCenterController;
use App\Http\Controllers\Dashboard\HobbyController;
use App\Http\Controllers\Dashboard\HeadmanController;
use App\Http\Controllers\Dashboard\MunicipalityInterfaceController;
use App\Http\Controllers\Dashboard\WifiController;
use App\Http\Controllers\Dashboard\QuestionnaireController;
use App\Http\Controllers\Dashboard\AloMaltepemNumberController;

Route::get('/maintenance', function (){
    return view('maintenancePage');
})->name('maintenance');

    //Login
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::group(['prefix' => 'gmzmmsf', 'middleware' => 'BackupPermission'], function (){
        Route::get('/backup', [\App\Http\Controllers\BackupController::class, 'index'])->name('backup.index');
        Route::get('/downloadBackup', [\App\Http\Controllers\BackupController::class, 'download'])->name('backup.download');
    });

    Route::get('/mj5toyszpj/login', [DashboardController::class, 'login'])->middleware('isOut')->name('login');
    Route::post('/loginPost', [DashboardController::class, 'loginPost'])->name('loginPost');
    Route::get('/logout', [UserController::class, 'userLogOut'])->name('logout');
Route::group(['prefix' => '/', 'middleware' => 'checkMaintenance'], function (){
    Route::get('/mj5toyszpj/refreshPassword', [UserController::class, 'refreshPassword'])->name('refreshPassword');
    Route::post('/mj5toyszpj/refreshPasswordConfirm', [UserController::class, 'refreshPasswordConfirm'])->name('refreshPasswordConfirm');
    Route::get('/mj5toyszpj/newPassword/{id}', [UserController::class, 'newPassword'])->name('newPassword');
    Route::post('/mj5toyszpj/newPasswordConfirm/{id}', [UserController::class, 'newPasswordConfirm'])->name('newPasswordConfirm');
    Route::get('kjsbaro/export/', [DashboardController::class, 'export'])->name('export');
    Route::get('mj5toyszpj/export/', [DashboardController::class, 'exportPage'])->name('exportPage');
    Route::prefix('mj5toyszpj')->middleware('isLogin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/xcusa', [DashboardController::class, 'getCekGonderStatistic'])->name('getCekGonderStatistic');
        Route::get('/pageViews', [DashboardController::class, 'pageViews'])->name('pageViews');
        Route::get('/getAgeStatistic', [DashboardController::class, 'getAgeStatistic'])->name('getAgeStatistic');
        Route::get('/newUserStatistic', [DashboardController::class, 'newUserStatistic'])->name('newUserStatistic');
        Route::get('/getCustomPageViewStatistic', [DashboardController::class, 'getCustomPageViewStatistic'])->name('getCustomPageViewStatistic');
        Route::get('/getCustomCekGonderStatistic', [DashboardController::class, 'getCustomCekGonderStatistic'])->name('getCustomCekGonderStatistic');
        Route::post('/setMaintenanceMode', [DashboardController::class, 'setMaintenanceMode'])->name('dashboard.setMaintenanceMode');
        Route::get('/map', [MapController::class, 'index'])->middleware('check_permission:harita')->name('map');
        Route::get('/getMapMarker', [MapController::class, 'getMapMarker'])->name('getMapMarker');
        Route::get('/statistics', [StatisticsController::class, 'index'])->middleware('check_permission:istatistikler')->name('statistics');
        Route::get('/fetchPersonalStatistic', [StatisticsController::class, 'fetchPersonalStatistic'])->middleware('check_permission:istatistikler')->name('fetchPersonalStatistic');
        Route::get('/statisticsEmployee/{id}', [StatisticsController::class, 'employeeDetail'])->middleware('check_permission:istatistikler')->name('employeeDetail');
        Route::get('/statisticsMissionDetail/{id}', [StatisticsController::class, 'statisticMissionDetail'])->middleware('check_permission:istatistikler')->name('statisticMissionDetail');
        Route::get('/getPersonelTakeSendStatistic', [StatisticsController::class, 'getPersonelTakeSendStatistic'])->middleware('check_permission:istatistikler')->name('getPersonelTakeSendStatistic');
        Route::get('/getPersonelCustomStatistic', [StatisticsController::class, 'getPersonelCustomStatistic'])->middleware('check_permission:istatistikler')->name('getPersonelCustomStatistic');
        Route::get('/getAverageTime', [StatisticsController::class, 'getAverageTime'])->middleware('check_permission:istatistikler')->name('getAverageTime');
        Route::get('/getMissionCount', [StatisticsController::class, 'getMissionCount'])->middleware('check_permission:istatistikler')->name('getMissionCount');
        Route::get('/fetchEmployeeDetail/{id}', [StatisticsController::class, 'fetchEmployeeDetail'])->middleware('check_permission:istatistikler')->name('fetchEmployeeDetail');

        Route::prefix('nuxsld')->middleware('check_permission:tum_kullanicilar')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('user');
            Route::get('/fetch', [UserController::class, 'fetch'])->name('user.fetch');
            Route::get('/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
            Route::post('/edit/post/{id}', [UserController::class, 'edit_post'])->name('user.edit.post');
            Route::post('/delete', [UserController::class, 'delete'])->name('user.delete');

        });
        Route::prefix('racxwc')->middleware('check_permission:silinen_kullanicilar')->group(function () {
            Route::get('/deletedUser', [UserController::class, 'deletedUser'])->name('user.deletedUser');
            Route::post('/update/restoreUser', [UserController::class, 'restoreUser'])->name('user.restoreUser');
            Route::get('/fetchDeletedUser', [UserController::class, 'fetchDeletedUser'])->name('user.fetchDeletedUser');
        });

        Route::prefix('daskenti')->middleware('check_permission:mobil_kullanicilar')->group(function () {
            Route::get('/', [MobileUserController::class, 'index'])->name('mobileuser');
            Route::get('/fetch', [MobileUserController::class, 'fetch'])->name('mobileuser.fetch');
            Route::get('/edit/{id}', [MobileUserController::class, 'edit'])->name('mobileuser.edit');
            Route::post('update/edit/post/{id}', [MobileUserController::class, 'edit_post'])->name('mobileuser.edit.post');
            Route::post('/delete', [MobileUserController::class, 'delete'])->name('mobileuser.delete');
        });

        Route::prefix('blincuksta')->middleware('check_permission:bildirim_gonder')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('notification');
            Route::get('/fetch', [NotificationController::class, 'fetch'])->name('notification.fetch');
            Route::get('/create', [NotificationController::class, 'create'])->name('notification.create');
            Route::post('update/create-post', [NotificationController::class, 'create_post'])->name('notification.create.post');
            Route::post('/delete', [NotificationController::class, 'delete'])->name('notification.delete');
            Route::get('/fetchDetail', [NotificationController::class, 'fetchDetail'])->name('notification.fetchDetail');
        });



        Route::prefix('gafasfrlery')->group(function () {
//        Route::get('/', [GaleryController::class, 'index'])->name('gallery');
//        Route::get('/fetch', [GaleryController::class, 'fetch'])->name('gallery.fetch');
//        Route::get('/gallery-fetch', [GaleryController::class, 'report_fetch'])->name('report.gallery.fetch');
//
//        Route::get('/create', [GaleryController::class, 'create'])->name('gallery.create');
//        Route::post('/create_post', [GaleryController::class, 'create_post'])->name('galery.create.post');
//        Route::post('/delete', [GaleryController::class, 'delete'])->name('gallery.delete');
//        Route::get('/edit/{id}', [GaleryController::class, 'update'])->name('gallery.edit');
//        Route::post('/update/{id}', [GaleryController::class, 'update_post'])->name('gallery.update');
            Route::post('/deleteImage', [GaleryController::class, 'deleteImage'])->name('gallery.deleteImage');
//        Route::post('/addImage', [GaleryController::class, 'addImage'])->name('gallery.addImage');
//        Route::get('/addImage', [GaleryController::class, 'addImage'])->name('gallery.addImage');
//        Route::get('/checkImageCount', [GaleryController::class, 'checkImageCount'])->name('gallery.checkImageCount');


        });

        Route::prefix('alkestradsx')->middleware('check_permission:duyurular')->group(function () {
            Route::get('/', [AnnouncementController::class, 'index'])->name('announcement');
            Route::get('/fetch', [AnnouncementController::class, 'fetch'])->name('announcement.fetch');
            Route::get('/create', [AnnouncementController::class, 'create'])->name('announcement.create');
            Route::get('/edit/{id}', [AnnouncementController::class, 'update'])->name('announcement.update');
            Route::post('update/edit-post/{id}', [AnnouncementController::class, 'update_post'])->name('announcement.update.post');
            Route::post('/create/post', [AnnouncementController::class, 'create_post'])->name('announcement.create.post');
            Route::get('/detail/{id}', [AnnouncementController::class, 'detail'])->name('announcement.detail');
            Route::get('/update_active', [AnnouncementController::class, 'update_active'])->name('announcement.update_active');
            Route::post('/delete', [AnnouncementController::class, 'delete'])->name('announcement.delete');
            Route::post('/delete-one', [AnnouncementController::class, 'delete_one'])->name('announcement.delete-one');
            Route::post('/addImageToAnnouncement', [AnnouncementController::class, 'addImage'])->name('announcement.addImage');
            Route::get('/checkImageCount', [AnnouncementController::class, 'checkImageCount'])->name('announcement.checkImageCount');
        });

        Route::prefix('kesblockaxi')->middleware('check_permission:personeller')->group(function () {
            Route::get('/', [EmployeeController::class, 'index'])->name('employee');
            Route::get('/fetch', [EmployeeController::class, 'fetch'])->name('employee.fetch');
            Route::get('/create', [EmployeeController::class, 'create'])->name('employee.create');
            Route::post('/createPost', [EmployeeController::class, 'create_post'])->name('employee.create.post');
            Route::get('/edit/{id}', [EmployeeController::class, 'update'])->name('employee.update');
            Route::post('update/edit-post/{id}', [EmployeeController::class, 'update_post'])->name('employee.update.post');
            Route::get('/detail/{id}', [EmployeeController::class, 'detail'])->name('employee.detail');
        });

        Route::prefix('ovxdlay')->middleware('check_permission:personal_unit')->group(function () {
            Route::get('/index', [EmployeeController::class, 'unitIndex'])->name('employee.unit');
            Route::get('/unitFetch', [EmployeeController::class, 'fetchUnit'])->name('employee.fetchUnit');
            Route::post('/createUnit', [EmployeeController::class, 'createUnit'])->name('employee.createUnit');
            Route::post('update/updateUnit', [EmployeeController::class, 'updateUnit'])->name('employee.updateUnit');
            Route::post('/deleteUnit', [EmployeeController::class, 'deleteUnit'])->name('employee.deleteUnit');
            Route::post('/status/update_unit_active', [EmployeeController::class, 'is_active'])->name('employee.update_unit_active');
        });

        Route::prefix('jastodry')->middleware('check_permission:hikaye')->group(function () {
            Route::get('/', [StoryController::class, 'index'])->name('story');
            Route::get('/fetch', [StoryController::class, 'fetch'])->name('story.fetch');
            Route::get('/create', [StoryController::class, 'create'])->name('story.create');
            Route::get('/edit/{id}', [StoryController::class, 'update'])->name('story.edit');
            Route::post('update/edit/post/{id}', [StoryController::class, 'update_post'])->name('story.edit.post');
            Route::post('/post', [StoryController::class, 'create_post'])->name('story.create.post');
            Route::post('/delete', [StoryController::class, 'delete'])->name('story.delete');
            Route::get('/story-type', [StoryController::class, 'getType'])->name('story.type');
            Route::get('/status/is_active', [StoryController::class, 'is_active'])->name('story.update_active');
        });

        Route::prefix('evmflakent')->middleware('check_permission:etkinlikler')->group(function () {
            Route::get('/', [EventController::class, 'index'])->name('event');
            Route::get('/getAddress', [EventController::class, 'addressInfo'])->name('getAddress');
            Route::get('/fetch', [EventController::class, 'fetch'])->name('event.fetch');
            Route::get('/create', [EventController::class, 'create'])->name('event.create');
            Route::get('/edit/{id}', [EventController::class, 'update'])->name('event.update');
            Route::post('update/edit/post/{id}', [EventController::class, 'update_post'])->name('event.edit.post');
            Route::post('/post', [EventController::class, 'create_post'])->name('event.create.post');
            Route::get('/update_active', [EventController::class, 'update_active'])->name('event.update_active');
            Route::get('/detail/{id}', [EventController::class, 'detail'])->name('event.detail');
            Route::post('/delete', [EventController::class, 'delete'])->name('event.delete');
            Route::post('/delete-one', [EventController::class, 'delete_one'])->name('event.delete-one');
            Route::get('/checkImageCount', [EventController::class, 'checkImageCount'])->name('event.checkImageCount');
        });

        Route::group(['prefix' => '/xcaskendix', 'middleware' => ['CheckReportPermission']], function (){
            //rejectPattern ret şablonları
            Route::get('/rejectPattern',[ReportController::class,'rejectPatternIndex'])->name('report.rejectPatternIndex');
            Route::get('/fetchUpdateRejectPattern',[ReportController::class,'fetchUpdateRejectPattern'])->name('report.fetchUpdateRejectPattern');
            Route::get('/rejectPatternGetContent',[ReportController::class,'rejectPatternGetContent'])->name('report.rejectPatternGetContent');
            Route::post('/createRejectPattern',[ReportController::class,'createRejectPattern'])->name('report.createRejectPattern');
            Route::post('/updateRejectPattern',[ReportController::class,'updateRejectPattern'])->name('report.updateRejectPattern');
            Route::post('/deleteRejectPattern',[ReportController::class,'deleteRejectPattern'])->name('report.deleteRejectPattern');
            Route::get('/fetchRejectPattern',[ReportController::class,'fetchRejectPattern'])->name('report.fetchRejectPattern');

            Route::get('/processTree/{id}',[ReportController::class,'processTree'])->name('report.processTree');
            Route::post('/sendNotification',[ReportController::class,'sendNotification'])->name('report.sendNotification');

            //neighbourhood mahalleler
            Route::get('/neighbourhood', [ReportController::class, 'neighbourhoodIndex'])->name('report.neighbourhoodIndex');
            Route::get('/fetchNeighbourhood', [ReportController::class, 'fetchNeighbourhood'])->name('report.neighbourhoodFetch');

            // Route::get('/createNeighbourhoodIndex', [ReportController::class, 'createNeighbourhoodIndex'])->name('report.createNeighbourhoodIndex');
            Route::get('/getNeighbourhood', [ReportController::class, 'getNeighbourhood'])->name('report.getNeighbourhood');
            Route::post('/createNeighbourhood', [ReportController::class, 'createNeighbourhood'])->name('report.createNeighbourhood');
            Route::post('/updateNeighbourhood', [ReportController::class, 'updateNeighbourhood'])->name('report.updateNeighbourhood');
            // Route::get('/updateNeighbourhoodIndex/{id}', [ReportController::class, 'updateNeighbourhoodIndex'])->name('report.updateNeighbourhoodIndex');
            Route::post('/deleteNeighbourhood', [ReportController::class, 'deleteNeighbourhood'])->name('report.deleteNeighbourhood');

            //assignDepartment Kullanıcı Birim Belirle
            Route::get('/assignDepartment', [ReportController::class, 'assignDepartment'])->name('report.assignDepartment');
            Route::post('/assignDepartmentToUser', [ReportController::class, 'assignDepartmentToUser'])->name('report.assignDepartmentToUser');

            //depertmant Settings Çek gönder yönetici ayarları
            Route::get('/departmentSettings', [ReportController::class, 'departmentSettings'])->name('report.departmentSettings');
            Route::get('/fetchEmployeeForDepartmentSettings', [ReportController::class, 'fetchEmployeeForDepartmentSettings'])->name('report.fetchEmployeeForDepartmentSettings');

            Route::post('/removeAllAuthority', [ReportController::class, 'removeAllAuthority'])->name('report.removeAllAuthority');
            Route::post('/changeAuthority', [ReportController::class, 'changeAuthority'])->name('report.changeAuthority');
            Route::post('/doTakeAndSendAdmin', [ReportController::class, 'doTakeAndSendAdmin'])->name('report.doTakeAndSendAdmin');

            //department birim
            Route::get('/createDepartmentIndex', [ReportController::class, 'createDepartmentIndex'])->name('report.createDepartmentIndex');
            Route::post('/deleteDepartment', [ReportController::class, 'deleteDepartment'])->name('report.deleteDepartment');
            Route::get('/fetchDepartments', [ReportController::class, 'fetchDepartments'])->name('report.fetchDepartments');
            Route::get('/getDepartment', [ReportController::class, 'getDepartment'])->name('report.getDepartment');
            Route::post('/createDepartment', [ReportController::class, 'createDepartment'])->name('report.createDepartment');
            Route::post('/updateDepartment', [ReportController::class, 'updateDepartment'])->name('report.updateDepartment');

            Route::get('/fetchNotify', [ReportController::class, 'fetchNotify'])->name('report.fetchNotify');
            Route::get('/notifyIndex', [ReportController::class, 'notifyIndex'])->name('report.notifyIndex');
            Route::post('/createNotifyScheme', [ReportController::class, 'createNotifyScheme'])->name('report.createNotifyScheme');
            Route::get('/getSchema', [ReportController::class, 'getSchema'])->name('report.getSchema');
            Route::post('/updateSchema', [ReportController::class, 'updateSchema'])->name('report.updateSchema');
            Route::post('/deleteSchema', [ReportController::class, 'deleteSchema'])->name('report.deleteSchema');
            Route::post('/sendRejectedReportNotification', [ReportController::class, 'sendRejectedReportNotification'])->name('report.sendRejectedReportNotification');
            Route::post('/rollbackMission', [ReportController::class, 'rollbackMission'])->name('report.rollbackMission');
        });
        Route::prefix('xcbdescx')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('report');
            Route::group(['prefix' => '/', 'middleware' => 'CheckReportPermission'], function (){
                Route::get('/listOfReportCategoryMission', [ReportController::class, 'listOfReportCategoryMission'])->name('report.listOfReportCategoryMission');
                Route::get('/getDetail/{id}', [ReportController::class, 'getDetail'])->name('report.getDetail');
                Route::get('/assignMission/{id}', [ReportController::class, 'assignMissionToAdministration'])->name('report.assignMission');
                Route::get('/assignMissionToUser/{id}', [ReportController::class, 'assignMissionToUser'])->name('report.assignMissionToUser');
                Route::get('/fetchEmployeeUser', [ReportController::class, 'fetchEmployeeUser'])->name('report.fetchEmployeeUser');
                Route::get('/fetchEmployee', [ReportController::class, 'fetchEmployee'])->name('report.fetchEmployee');
                Route::get('/fetchReports', [ReportController::class, 'fetchReports'])->name('report.fetchReports');
                Route::get('/getEmployeeInformation', [ReportController::class, 'getEmployeeInformation'])->name('report.getEmployeeInformation');

                Route::post('/sendMissionNotification', [ReportController::class, 'sendMissionNotification'])->name('report.sendMissionNotification');

                //department birim
                Route::post('/assignReportToDepartment', [ReportController::class, 'assignReportToDepartment'])->name('report.assignReportToDepartment');

                //Mission görevler
                Route::get('/missions', [ReportController::class, 'missions'])->name('report.missions');
                Route::get('/missionFetch', [ReportController::class, 'missionFetch'])->name('report.missionFetch');
                Route::get('/getMissionDetail/{id}', [ReportController::class, 'getMissionDetail'])->name('report.getMissionDetail');
                Route::get('/fetchRejectedMission', [ReportController::class, 'fetchRejectedMission'])->name('report.fetchRejectedMission');
                Route::post('/assignMissionToUserAgain', [ReportController::class, 'assignMissionToUserAgain'])->name('report.assignMissionToUserAgain');

                //görev çöz
                Route::get('/missionsSolveIndex/{id}',[ReportController::class,'missionsSolveIndex'])->name('report.missionsSolveIndex');
                Route::post('/missionsSolved',[ReportController::class,'missionsSolved'])->name('report.missionsSolved');

                //Asılsız Görev Bildirimi

                Route::get('/missionsFakeIndex/{id}',[ReportController::class,'missionsFakeIndex'])->name('report.missionsFakeIndex');
                Route::post('/missionsFake',[ReportController::class,'missionsFake'])->name('report.missionsFake');

                //waitingReportFromDepartment Birimde bekleyen şikayetler
                Route::get('/waitingReportFromDepartment', [ReportController::class, 'waitingReportFromDepartment'])->name('report.waitingReportFromDepartment');
                Route::get('/waitingReportFromDepartmentDetail/{id}', [ReportController::class, 'waitingReportFromDepartmentDetail'])->name('report.waitingReportFromDepartmentDetail');
                Route::get('/fetchWaitingReportFromDepartment', [ReportController::class, 'fetchWaitingReportFromDepartment'])->name('report.fetchWaitingReportFromDepartment');

                //getRejectedMission Reddedilen Görevler
                Route::get('/getRejectedMission', [ReportController::class, 'getRejectedMission'])->name('report.getRejectedMission');
                Route::get('/getRejectedMissionDetail/{id}', [ReportController::class, 'getRejectedMissionDetail'])->name('report.getRejectedMissionDetail');

                //rejectedReportFromDepartment adminler tarafından reddedilen çek gönderler
                Route::get('/rejectedReportFromDepartment', [ReportController::class, 'rejectedReportFromDepartment'])->name('report.rejectedReportFromDepartment');
                Route::get('/rejectedReportFromDepartmentFetch', [ReportController::class, 'rejectedReportFromDepartmentFetch'])->name('report.rejectedReportFromDepartmentFetch');

                Route::post('/createMission', [ReportController::class, 'createMission'])->name('report.createMission');
                Route::post('/rejectReport', [ReportController::class, 'rejectReport'])->name('report.rejectReport');
                Route::post('/rejectReportFromDepartment', [ReportController::class, 'rejectReportFromDepartment'])->name('report.rejectReportFromDepartment');
                Route::get('/assignRejectedReportAgain/{id}', [ReportController::class, 'assignRejectedReportAgain'])->name('report.assignRejectedReportAgain');

                Route::post('/exportReportExcel', [ReportController::class, 'exportReportExcel'])->name('report.exportReportExcel');
                Route::get('/exportReportExcel', [ReportController::class, 'exportReportExcel'])->name('report.exportReportExcel');
                Route::get('/exportExcelPage', [ReportController::class, 'exportExcelPage'])->name('report.exportExcelPage');
            });
        });

        Route::prefix('crjaxl')->middleware('check_permission:hizmetler')->name('services.')->group(function () {
//            Route::prefix('marketPlaces')->group(function () {
//                Route::get('/', [MarketPlacesController::class, 'index'])->name('marketPlaces');
//                Route::get('/fetch', [MarketPlacesController::class, 'fetch'])->name('marketPlaces.fetch');
//                Route::get('/create', [MarketPlacesController::class, 'create'])->name('marketPlaces.create');
//                Route::get('/edit/{id}', [MarketPlacesController::class, 'update'])->name('marketPlaces.edit');
//                Route::post('update/post/{id}', [MarketPlacesController::class, 'update_post'])->name('marketPlaces.edit.post');
//                Route::post('/post', [MarketPlacesController::class, 'create_post'])->name('marketPlaces.create.post');
//                Route::post('/delete', [MarketPlacesController::class, 'delete'])->name('marketPlaces.delete');
//            });
            Route::prefix('glaux')->group(function () {
                Route::get('/', [PharmacyController::class, 'index'])->name('pharmacy');
                Route::get('/fetch', [PharmacyController::class, 'fetch'])->name('pharmacy.fetch');
                Route::get('/create', [PharmacyController::class, 'create'])->name('pharmacy.create');
                Route::get('/edit/{id}', [PharmacyController::class, 'update'])->name('pharmacy.edit');
                Route::post('update/post/{id}', [PharmacyController::class, 'update_post'])->name('pharmacy.edit.post');
                Route::post('/post', [PharmacyController::class, 'create_post'])->name('pharmacy.create.post');
                Route::post('/delete', [PharmacyController::class, 'delete'])->name('pharmacy.delete');
                Route::post('/switchAll', [PharmacyController::class, 'switchAll'])->name('pharmacy.switchAll');
                Route::get('/status/is_active', [PharmacyController::class, 'is_active'])->name('pharmacy.update_active');

            });
            Route::prefix('ajrzldsk')->group(function () {
                Route::get('/', [LibraryController::class, 'index'])->name('library');
                Route::get('/fetch', [LibraryController::class, 'fetch'])->name('library.fetch');
                Route::get('/create', [LibraryController::class, 'create'])->name('library.create');
                Route::get('/edit/{id}', [LibraryController::class, 'update'])->name('library.edit');
                Route::post('update/post/{id}', [LibraryController::class, 'update_post'])->name('library.edit.post');
                Route::post('/post', [LibraryController::class, 'create_post'])->name('library.create.post');
                Route::post('/delete', [LibraryController::class, 'delete'])->name('library.delete');
            });

            Route::prefix('wojrlaanv')->group(function () {
                Route::get('/', [PlaceOfWorshipController::class, 'index'])->name('placeOfWorship');
                Route::get('/fetch', [PlaceOfWorshipController::class, 'fetch'])->name('placeOfWorship.fetch');
                Route::get('/create', [PlaceOfWorshipController::class, 'create'])->name('placeOfWorship.create');
                Route::get('/edit/{id}', [PlaceOfWorshipController::class, 'update'])->name('placeOfWorship.edit');
                Route::post('update/post/{id}', [PlaceOfWorshipController::class, 'update_post'])->name('placeOfWorship.edit.post');
                Route::post('/post', [PlaceOfWorshipController::class, 'create_post'])->name('placeOfWorship.create.post');
                Route::post('/delete', [PlaceOfWorshipController::class, 'delete'])->name('placeOfWorship.delete');
            });

            Route::prefix('flaxjemla')->group(function () {
                Route::get('/', [SocialFacilitiesController::class, 'index'])->name('socialFacility');
                Route::get('/fetch', [SocialFacilitiesController::class, 'fetch'])->name('socialFacilities.fetch');
                Route::get('/create', [SocialFacilitiesController::class, 'create'])->name('socialFacilities.create');
                Route::get('/edit/{id}', [SocialFacilitiesController::class, 'update'])->name('socialFacilities.edit');
                Route::post('update/post/{id}', [SocialFacilitiesController::class, 'update_post'])->name('socialFacilities.edit.post');
                Route::post('/post', [SocialFacilitiesController::class, 'create_post'])->name('socialFacilities.create.post');
                Route::post('/delete', [SocialFacilitiesController::class, 'delete'])->name('socialFacilities.delete');
                Route::get('/checkImageCount', [SocialFacilitiesController::class, 'checkImageCount'])->name('socialFacilities.checkImageCount');
            });

            Route::prefix('xaktalb')->group(function () {
                Route::get('/', [TaxiController::class, 'index'])->name('taxi');
                Route::get('/fetch', [TaxiController::class, 'fetch'])->name('taxi.fetch');
                Route::get('/create', [TaxiController::class, 'create'])->name('taxi.create');
                Route::get('/edit/{id}', [TaxiController::class, 'update'])->name('taxi.edit');
                Route::post('update/post/{id}', [TaxiController::class, 'update_post'])->name('taxi.edit.post');
                Route::post('/post', [TaxiController::class, 'create_post'])->name('taxi.create.post');
                Route::post('/delete', [TaxiController::class, 'delete'])->name('taxi.delete');
            });


            Route::prefix('meadoad')->group(function () {
                Route::get('/', [HeadmanController::class, 'index'])->name('headman');
                Route::get('/fetch', [HeadmanController::class, 'fetch'])->name('headman.fetch');
                Route::get('/create', [HeadmanController::class, 'create'])->name('headman.create');
                Route::get('/edit/{id}', [HeadmanController::class, 'update'])->name('headman.edit');
                Route::post('update/post/{id}', [HeadmanController::class, 'update_post'])->name('headman.edit.post');
                Route::post('/post', [HeadmanController::class, 'create_post'])->name('headman.create.post');
                Route::post('/delete', [HeadmanController::class, 'delete'])->name('headman.delete');
            });

            Route::prefix('trlelasj')->group(function () {
                Route::get('/', [NotaryController::class, 'index'])->name('notary');
                Route::get('/fetch', [NotaryController::class, 'fetch'])->name('notary.fetch');
                Route::get('/create', [NotaryController::class, 'create'])->name('notary.create');
                Route::get('/edit/{id}', [NotaryController::class, 'update'])->name('notary.edit');
                Route::post('update/post/{id}', [NotaryController::class, 'update_post'])->name('notary.edit.post');
                Route::post('/create/post', [NotaryController::class, 'create_post'])->name('notary.create.post');
                Route::post('/delete', [NotaryController::class, 'delete'])->name('notary.delete');
            });

            Route::prefix('relkjaf')->group(function () {
                Route::get('/', [CulturalCentersController::class, 'index'])->name('culturalCenter');
                Route::get('/fetch', [CulturalCentersController::class, 'fetch'])->name('culturalCenters.fetch');
                Route::get('/create', [CulturalCentersController::class, 'create'])->name('culturalCenters.create');
                Route::get('/edit/{id}', [CulturalCentersController::class, 'update'])->name('culturalCenters.edit');
                Route::post('update/post/{id}', [CulturalCentersController::class, 'update_post'])->name('culturalCenters.edit.post');
                Route::post('/create/post', [CulturalCentersController::class, 'create_post'])->name('culturalCenters.create.post');
                Route::post('/delete', [CulturalCentersController::class, 'delete'])->name('culturalCenters.delete');
                Route::post('/delete-one', [CulturalCentersController::class, 'delete_one'])->name('culturalCenters.delete-one');
                Route::post('/addImageToCulturalCenters', [CulturalCentersController::class, 'addImage'])->name('culturalCenters.addImage');
                Route::get('/checkImageCountCulturalCenters', [CulturalCentersController::class, 'checkImageCount'])->name('culturalCenters.checkImageCount');
            });

            Route::group(['prefix' => 'calstabm'], function (){
                Route::get('/', [WifiController::class, 'index'])->name('wifi.index');
                Route::get('/fetch', [WifiController::class, 'fetch_wifi'])->name('wifi.fetch');
                Route::get('/create', [WifiController::class, 'create'])->name('wifi.create.index');
                Route::post('/create_post', [WifiController::class, 'create_post'])->name('wifi.create.post');
                Route::get('/update/{id}', [WifiController::class, 'update'])->name('wifi.update.index');
                Route::post('/update_post', [WifiController::class, 'update_post'])->name('wifi.update.post');
                Route::post('/delete', [WifiController::class, 'delete'])->name('wifi.delete');
            });
        });

        Route::get('/jasklaksipro/fetch', [roleContoller::class, 'fetch'])
            ->name('panel.super_admin_role.fetch')->middleware('check_permission:personel_yetkileri');
        Route::resource('swandlyx', roleContoller::class)->names([
            'index' => 'panel.super_admin_role.index',
            'create' => 'panel.super_admin_role.create',
            'store' => 'panel.super_admin_role.store',
            'edit' => 'panel.super_admin_role.edit',
            'update' => 'panel.super_admin_role.update',
            'show' => 'panel.super_admin_role.show',
            'destroy' => 'panel.super_admin_role.destroy',
        ])->middleware('check_permission:personel_yetkileri');

        Route::group(['prefix' => '/ashwaganda'], function (){
            Route::prefix('fasxdles')->middleware('check_permission:mobil_arayuz')->group(function () {
                Route::get('/', [MobileInterfaceController::class, 'index'])->name('mobileInterface');
                Route::get('/edit/{id}', [MobileInterfaceController::class, 'edit'])->name('mobileInterface.edit');
                Route::post('update/edit-post/{id}', [MobileInterfaceController::class, 'edit_post'])->name('mobileInterface.edit.post');
            });

            Route::prefix('insxlrtda')->middleware('check_permission:kesfet_arayuz')->group(function () {
                Route::get('/', [DiscoverInterfaceController::class, 'index'])->name('discoverInterface');
                Route::get('/edit/{id}', [DiscoverInterfaceController::class, 'edit'])->name('discoverInterface.edit');
                Route::post('update/edit-post/{id}', [DiscoverInterfaceController::class, 'edit_post'])->name('discoverInterface.edit.post');
            });

            Route::group(['prefix' => '/faskiskaris'], function(){
                Route::get('/index', [AloMaltepemNumberController::class, 'index'])->name('maltepemNumber');
                Route::post('/edit', [AloMaltepemNumberController::class, 'edit'])->name('maltepemNumber.edit');
            });

        });

        Route::prefix('kelxuislax/')->group(function () {

            Route::prefix('foromaxtrk')->middleware('check_permission:iletisim_formu')->group(function () {
                Route::get('/', [ContactFormController::class, 'index'])->name('contactForm');
                Route::get('/fetch', [ContactFormController::class, 'fetch'])->name('contactForm.fetch');
                Route::get('/detail/{id}', [ContactFormController::class, 'get'])->name('contactForm.show');
                Route::post('/delete', [ContactFormController::class, 'delete'])->name('contactForm.delete');
                Route::get('/active', [ContactFormController::class, 'update_active'])->name('contactForm.update_active');
                Route::post('/createContactNotification', [ContactFormController::class, 'createContactNotification'])->name('contactForm.createContactNotification');
            });

            Route::prefix('catioxlam')->middleware('check_permission:is_basvuru')->group(function () {
                Route::get('/', [JobApplicationController::class, 'index'])->name('jobApplication');
                Route::get('/detail/{id}', [JobApplicationController::class, 'jobApplicationDetail'])->name('jobApplication.detail');
                Route::get('/fetch', [JobApplicationController::class, 'jobApplicationFetch'])->name('jobApplication.fetch');
                Route::get('/saveToPdf/{id}', [JobApplicationController::class, 'saveToPdf'])->name('jobApplication.saveToPdf');
                Route::post('/delete', [JobApplicationController::class, 'jobApplicationDelete'])->name('jobApplication.delete');

                Route::get('/jobType', [JobApplicationController::class, 'indexJobType'])->name('jobApplication.jobType');
                Route::post('/updatePostJobType', [JobApplicationController::class, 'updatePostJobType'])->name('jobApplicationJobType.updatePostJobType');
                Route::get('/fetchJobType', [JobApplicationController::class, 'fetchJobType'])->name('jobApplicationJobType.fetchJobType');
                //     Route::post('/update_unit_active', [JobApplicationController::class, 'update_unit_active'])->name('jobApplicationUnit.update_unit_active');
                Route::post('/createJobType', [JobApplicationController::class, 'createJobType'])->name('jobApplicationJobType.createJobType');
                Route::post('/deleteJobType', [JobApplicationController::class, 'deleteJobType'])->name('jobApplicationJobType.delete');
            });

            Route::group(['prefix' => 'alapubcion', 'middleware' => 'check_permission:publicRelation_service'], function () {
                Route::get('/', [PublicRelationController::class, 'index'])->name('publicRelation.index');
                Route::post('/updateOrCreatePublicRelation', [PublicRelationController::class, 'updateOrCreatePublicRelation'])->name('publicRelation.updateOrCreatePublicRelation');
                Route::post('/checkImageCount', [PublicRelationController::class, 'checkImageCount'])->name('publicRelation.checkImageCount');
            });

            Route::group(['prefix' => 'viheactikslax', 'middleware' => 'check_permission:health_service'], function () {
                Route::get('/', [HealthController::class, 'index'])->name('healthService.index');
                Route::post('/updateOrCreateHealthService', [HealthController::class, 'updateOrCreateHealthService'])->name('healthService.updateOrCreateHealthService');
                Route::post('/checkImageCount', [HealthController::class, 'checkImageCount'])->name('healthService.checkImageCount');
            });

            Route::group(['prefix' => 'tropsfixlamion'], function () {
                Route::group(['middleware' => 'check_permission:sport_service'], function () {
                    Route::get('/', [SportController::class, 'index'])->name('sport.index');
                    Route::post('/updateOrCreateSportService', [SportController::class, 'updateOrCreateSportService'])->name('sport.updateOrCreateSportService');
                    Route::get('/checkImageCount', [SportController::class, 'checkImageCount'])->name('sport.checkImageCount');

                });

                Route::group(['prefix' => 'rndomforexti'], function () {
                    Route::group(['middleware' => 'check_permission:sport_center'], function () {
                        Route::post('/status/changeActivePassive', [SportController::class, 'changeActivePassive'])->name('sport.center.changeActivePassive');
                        Route::get('/checkImageCenterCount', [SportController::class, 'checkImageCenterCount'])->name('sport.center.checkImageCount');
                        Route::get('/sportCenterList', [SportController::class, 'sportCenterList'])->name('sport.sportCenterList');
                        Route::get('/fetchSportCenter', [SportController::class, 'fetchSportCenter'])->name('sport.fetchSportCenter');
                        Route::get('/updateSportCenterIndex/{id}', [SportController::class, 'updateSportCenterIndex'])->name('sport.updateSportCenterIndex');
                        Route::post('/deleteSportCenter', [SportController::class, 'deleteSportCenter'])->name('sport.deleteSportCenter');
                        Route::post('/updateSportCenter', [SportController::class, 'updateSportCenter'])->name('sport.updateSportCenter');
                        Route::post('/createSportCenter', [SportController::class, 'createSportCenter'])->name('sport.createSportCenter');
                        Route::get('/createSportCenterIndex', [SportController::class, 'createSportCenterIndex'])->name('sport.createSportCenterIndex');
                    });

                    Route::group(['middleware' => 'check_permission:sport_branch'], function () {
                        Route::get('/SportBranchDeletedList/{id}', [SportController::class, 'SportBranchDeletedList'])->name('sport.SportBranchDeletedList');
                        Route::get('/sportBranchListIndex/{id}', [SportController::class, 'sportBranchListIndex'])->name('sport.sportBranchListIndex');
                        Route::get('/fetchBranch/{id}', [SportController::class, 'fetchBranch'])->name('sport.fetchBranch');
                        Route::get('/fetchBranchDeleted/{id}', [SportController::class, 'fetchBranchDeleted'])->name('sport.fetchBranchDeleted');
                        Route::post('/createBranch', [SportController::class, 'createBranch'])->name('sport.createBranch');
                        Route::get('/getBranch', [SportController::class, 'getBranch'])->name('sport.getBranch');
                        Route::post('/updateBranch', [SportController::class, 'updateBranch'])->name('sport.updateBranch');
                        Route::post('/deleteBranch', [SportController::class, 'deleteBranch'])->name('sport.deleteBranch');
                        Route::post('/status/sportChangeActivePassive', [SportController::class, 'sportChangeActivePassive'])->name('sport.sportChangeActivePassive');

                    });

                    Route::group(['middleware' => 'check_permission:sport_apply'], function () {
                        Route::get('/fetchApply/{id}', [SportController::class, 'fetchApply'])->name('sport.fetchApply');
                        Route::get('/applyList/{id}', [SportController::class, 'applyIndex'])->name('sport.applyIndex');
                        Route::get('/getApplyDetail', [SportController::class, 'getApplyDetail'])->name('sport.getApplyDetail');
                    });
                });
            });

            Route::group(['prefix' => 'cutaxoxtion'], function () {
                Route::group(['middleware' => 'check_permission:education_service'], function () {
                    Route::get('/', [EducationController::class, 'index'])->name('education.index');
                    Route::post('/updateOrCreateEducationService', [EducationController::class, 'updateOrCreateEducationService'])->name('education.updateOrCreateEducationService');
                    Route::get('/checkImageCount', [EducationController::class, 'checkImageCount'])->name('education.checkImageCount');
                });
                Route::group(['middleware' => 'check_permission:education_center'], function () {
                    Route::get('/checkImageCenterCount', [EducationController::class, 'checkImageCenterCount'])->name('education.center.checkImageCount');
                    Route::post('/status/changeActivePasive', [EducationController::class, 'changeActivePasive'])->name('education.center.changeActivePasive');
                    Route::get('/educationCenterList', [EducationController::class, 'educationCenterList'])->name('education.educationCenterList');
                    Route::get('/fetchEducationCenter', [EducationController::class, 'fetchEducationCenter'])->name('education.fetchEducationCenter');
                    Route::post('/createEducationCenter', [EducationController::class, 'createEducationCenter'])->name('education.createEducationCenter');
                    Route::get('/createEducationIndex', [EducationController::class, 'createEducationIndex'])->name('education.createEducationIndex');
                    Route::get('/updateEducationCenterIndex/{id}', [EducationController::class, 'updateEducationCenterIndex'])->name('education.updateEducationCenterIndex');
                    Route::post('/deleteEducationCenter', [EducationController::class, 'deleteEducationCenter'])->name('education.deleteEducationCenter');
                    Route::post('/updateEducationCenter', [EducationController::class, 'updateEducationCenter'])->name('education.updateEducationCenter');
                });
                Route::group(['middleware' => 'check_permission:education_course'], function () {
                    Route::get('/courseDeletedList/{id}', [EducationController::class, 'courseDeletedList'])->name('education.courseDeletedList');
                    Route::get('/courseListIndex/{id}', [EducationController::class, 'courseListIndex'])->name('education.courseListIndex');
                    Route::post('/createCourse', [EducationController::class, 'createCourse'])->name('education.createCourse');
                    Route::post('/updateCourse', [EducationController::class, 'updateCourse'])->name('education.updateCourse');
                    Route::post('/deleteCourse', [EducationController::class, 'deleteCourse'])->name('education.deleteCourse');
                    Route::get('/fetchCourse/{id}', [EducationController::class, 'fetchCourse'])->name('education.fetchCourse');
                    Route::get('/fetchCourseDeleted/{id}', [EducationController::class, 'fetchCourseDeleted'])->name('education.fetchCourseDeleted');
                    Route::post('/status/changeActivePassive', [EducationController::class, 'courseChangeActivePassive'])->name('education.courseChangeActivePassive');

                    Route::get('/getCourse', [EducationController::class, 'getCourse'])->name('education.getCourse');
                });
                Route::group(['middleware' => 'check_permission:education_apply'], function () {
                    Route::get('/fetchApply/{id}', [EducationController::class, 'fetchApply'])->name('education.fetchApply');
                    Route::get('/applyList/{id}', [EducationController::class, 'applyIndex'])->name('education.applyIndex');
                    Route::get('/getApplyDetail', [EducationController::class, 'getApplyDetail'])->name('education.getApplyDetail');
                });
            });

            Route::group(['prefix' => 'lakmnd'], function () {
                Route::group(['middleware' => 'check_permission:republiceducation_center'], function () {

                    Route::get('/republicEducationCenter', [RepublicEducationCenterController::class, 'index'])->name('republicEducationCenter.index');
                    Route::post('/updateOrCreateRepublicEducationCenter', [RepublicEducationCenterController::class, 'updateOrCreateRepublicEducationCenter'])->name('republic.updateOrCreateRepublicEducationCenter');
                    Route::post('/republicEducationCenterCheckImageCount', [RepublicEducationCenterController::class, 'republicEducationCenterCheckImageCount'])->name('republic.checkImageCount');
                });
                Route::group(['middleware' => 'check_permission:republiceducation_youth_center'], function () {
                    Route::get('/youthCenter', [RepublicEducationCenterController::class, 'indexYouth'])->name('youthCenter.index');
                    Route::post('/updateOrCreateYouthCenter', [RepublicEducationCenterController::class, 'updateOrCreateYouth'])->name('youthCenter.updateOrCreateYouthCenter');
                    Route::post('/youthCheckImageCount', [RepublicEducationCenterController::class, 'youthCheckImageCount'])->name('youthCenter.checkImageCount');

                    Route::get('/applyList', [RepublicEducationCenterController::class, 'applyIndex'])->name('youthCenter.applyIndex');
                    Route::get('/fetchApply', [RepublicEducationCenterController::class, 'fetchApply'])->name('youthCenter.fetchApply');
                    Route::get('/youthCenter/getApplyDetail', [RepublicEducationCenterController::class, 'getApplyDetail'])->name('youthCenter.getApplyDetail');
                });
                Route::group(['middleware' => 'check_permission:republiceducation_youth_hobby'], function () {
                    Route::get('/hobbyList', [HobbyController::class, 'hobbyList'])->name('hobby.applyIndex');
                    Route::get('/fetchHobby', [HobbyController::class, 'fetchHobby'])->name('hobby.fetchHobby');
                    Route::get('/getHobby', [HobbyController::class, 'getHobby'])->name('hobby.getHobby');
                    Route::post('/newHobby', [HobbyController::class, 'newHobby'])->name('hobby.newHobby');
                    Route::post('/updateHobby', [HobbyController::class, 'updateHobby'])->name('hobby.updateHobby');
                    Route::post('/deleteHobby', [HobbyController::class, 'deleteHobby'])->name('hobby.deleteHobby');
                });
            });

            Route::group(['prefix' => 'wfanixmilay'], function () {
                Route::group(['middleware' => 'check_permission:womanFamily_service'], function () {
                    Route::get('/', [WomanFamilyController::class, 'index'])->name('womanFamily.index');
                    Route::post('/updateOrCreateWomanFamilyService', [WomanFamilyController::class, 'updateOrCreateWomanFamilyService'])->name('womanFamily.updateOrCreateWomanFamilyService');
                    Route::get('/checkImageCount', [WomanFamilyController::class, 'checkImageCount'])->name('womanFamily.checkImageCount');
                });
                Route::group(['middleware' => 'check_permission:womanFamily_center'], function () {
                    Route::post('/status/changeActivePassive', [WomanFamilyController::class, 'changeActivePassive'])->name('womanFamily.center.changeActivePassive');
                    Route::get('/checkImageCenterCount', [WomanFamilyController::class, 'checkImageCenterCount'])->name('womanFamily.center.checkImageCount');
                    Route::get('/womanFamilyCenterList', [WomanFamilyController::class, 'womanFamilyCenterList'])->name('womanFamily.womanFamilyCenterList');
                    Route::get('/fetchWomanFamilyCenter', [WomanFamilyController::class, 'fetchWomanFamilyCenter'])->name('womanFamily.fetchWomanFamilyCenter');
                    Route::get('/updateWomanFamilyCenterIndex/{id}', [WomanFamilyController::class, 'updateWomanFamilyCenterIndex'])->name('womanFamily.updateWomanFamilyCenterIndex');
                    Route::post('/deleteWomanFamilyCenter', [WomanFamilyController::class, 'deleteWomanFamilyCenter'])->name('womanFamily.deleteWomanFamilyCenter');
                    Route::post('/updateWomanFamilyCenter', [WomanFamilyController::class, 'updateWomanFamilyCenter'])->name('womanFamily.updateWomanFamilyCenter');
                    Route::post('/createWomanFamilyCenter', [WomanFamilyController::class, 'createWomanFamilyCenter'])->name('womanFamily.createWomanFamilyCenter');
                    Route::get('/createWomanFamilyCenterIndex', [WomanFamilyController::class, 'createWomanFamilyCenterIndex'])->name('womanFamily.createWomanFamilyCenterIndex');
                    Route::get('/womanFamilyListDeleted', [WomanFamilyController::class, 'listDeleted'])->name('womanFamily.listDeleted');
                    Route::get('/womanFamilyFetchDeleted', [WomanFamilyController::class, 'fetchDeleted'])->name('womanFamily.fetchDeleted');
                });
                Route::group(['middleware' => 'check_permission:womanFamily_apply'], function () {
                    Route::get('/fetchApply/{id}', [WomanFamilyController::class, 'fetchApply'])->name('womanFamily.fetchApply');
                    Route::get('/applyList/{id}', [WomanFamilyController::class, 'applyIndex'])->name('womanFamily.applyIndex');
                    Route::get('/getApplyDetail', [WomanFamilyController::class, 'getApplyDetail'])->name('womanFamily.getApplyDetail');
                });

            });

            Route::group(['prefix' => 'erynur'], function () {
                Route::group(['middleware' => 'check_permission:nursery_service'], function () {
                    Route::get('/', [NurseryController::class, 'index'])->name('nursery.index');
                    Route::post('/updateOrCreateNurseryService', [NurseryController::class, 'updateOrCreateNurseryService'])->name('nursery.updateOrCreateNurseryService');
                    Route::get('/checkImageCount', [NurseryController::class, 'checkImageCount'])->name('nursery.checkImageCount');
                });
                Route::group(['middleware' => 'check_permission:nursery_center'], function () {
                    Route::get('/checkImageCenterCount', [NurseryController::class, 'checkImageCenterCount'])->name('nursery.center.checkImageCount');
                    Route::post('/status/changeActivePassive', [NurseryController::class, 'changeActivePasive'])->name('nursery.center.changeActivePasive');
                    Route::get('/nurseryList', [NurseryController::class, 'nurseryList'])->name('nursery.nurseryList');
                    Route::get('/fetchNursery', [NurseryController::class, 'fetchNursery'])->name('nursery.fetchNursery');
                    Route::get('/updateNurseryIndex/{id}', [NurseryController::class, 'updateNurseryIndex'])->name('nursery.updateNurseryIndex');
                    Route::post('/deleteNursery', [NurseryController::class, 'deleteNursery'])->name('nursery.deleteNursery');
                    Route::post('/updateNursery', [NurseryController::class, 'updateNursery'])->name('nursery.updateNursery');
                    Route::post('/createNursery', [NurseryController::class, 'createNursery'])->name('nursery.createNursery');
                    Route::get('/createNurseryIndex', [NurseryController::class, 'createNurseryIndex'])->name('nursery.createNurseryIndex');

                    Route::get('/nurseryListDeleted', [NurseryController::class, 'listDeleted'])->name('nursery.listDeleted');
                    Route::get('/nurseryFetchDeleted', [NurseryController::class, 'fetchDeleted'])->name('nursery.fetchDeleted');
                });
                Route::group(['middleware' => 'check_permission:nursery_apply'], function () {
                    Route::get('/fetchApply/{id}', [NurseryController::class, 'fetchApply'])->name('nursery.fetchApply');
                    Route::get('/applyList/{id}', [NurseryController::class, 'applyIndex'])->name('nursery.applyIndex');
                    Route::get('/getApplyDetail', [NurseryController::class, 'getApplyDetail'])->name('nursery.getApplyDetail');
                });

            });

            Route::group(['prefix' => 'tinarverbilow'], function () {
                Route::group(['middleware' => 'check_permission:veterinary_service'], function () {
                    Route::get('/', [VeterinaryController::class, 'index'])->name('veterinary.index');
                    Route::post('/updateOrCreateVeterinaryService', [VeterinaryController::class, 'updateOrCreateVeterinaryService'])->name('veterinary.updateOrCreateVeterinaryService');
                    Route::get('/checkServiceImageCount', [VeterinaryController::class, 'checkImageCount'])->name('veterinary.center.checkImageCount');
                });
                Route::group(['middleware' => 'check_permission:veterinary_center'], function () {
                    Route::post('/status/changeActivePassive', [VeterinaryController::class, 'changeActivePassive'])->name('veterinary.changeActivePassive');
                    Route::get('/checkImageCount', [VeterinaryController::class, 'checkImageCenterCount'])->name('veterinary.checkImageCount');
                    Route::get('/updateVeterinaryCenterIndex/{id}', [VeterinaryController::class, 'updateVeterinaryCenterIndex'])->name('veterinary.updateVeterinaryCenterIndex');
                    Route::post('/updateVeterinaryCenter', [VeterinaryController::class, 'updateVeterinaryCenter'])->name('veterinary.updateVeterinaryCenter');
                    Route::post('/createVeterinaryCenter', [VeterinaryController::class, 'createVeterinaryCenter'])->name('veterinary.createVeterinaryCenter');
                    Route::get('/createVeterinaryCenterIndex', [VeterinaryController::class, 'createVeterinaryCenterIndex'])->name('veterinary.createVeterinaryCenterIndex');
                    Route::post('/deleteVeterinaryCenter', [VeterinaryController::class, 'deleteVeterinaryCenter'])->name('veterinary.deleteVeterinaryCenter');
                    Route::get('/fetchVeterinaryCenter', [VeterinaryController::class, 'fetchVeterinaryCenter'])->name('veterinary.fetchVeterinaryCenter');
                    Route::get('/veterinaryCenterList', [VeterinaryController::class, 'veterinaryCenterList'])->name('veterinary.veterinaryCenterList');
                });
            });

            Route::group(['prefix' => 'tationrehwib'], function () {
                Route::group(['middleware' => 'check_permission:rehabilitation_service'], function () {
                    Route::get('/', [Rehabilitation::class, 'index'])->name('rehabilitation.index');
                    Route::post('/updateOrCreateRehabilitationService', [Rehabilitation::class, 'updateOrCreateRehabilitationService'])->name('rehabilitation.updateOrCreateRehabilitationService');
                    Route::get('/checkImageCount', [Rehabilitation::class, 'checkImageCount'])->name('rehabilitation.checkImageCount');
                });
                Route::group(['middleware' => 'check_permission:rehabilitation_center'], function () {
                    Route::post('/status/changeActivePassive', [Rehabilitation::class, 'changeActivePassive'])->name('rehabilitation.center.changeActivePassive');
                    Route::get('/checkImageCenterCount', [Rehabilitation::class, 'checkImageCenterCount'])->name('rehabilitation.center.checkImageCount');
                    Route::get('/rehabilitationCenterList', [Rehabilitation::class, 'rehabilitationCenterList'])->name('rehabilitation.rehabilitationCenterList');
                    Route::get('/fetchRehabilitationCenter', [Rehabilitation::class, 'fetchRehabilitationCenter'])->name('rehabilitation.fetchRehabilitationCenter');
                    Route::get('/updateRehabilitationCenterIndex/{id}', [Rehabilitation::class, 'updateRehabilitationCenterIndex'])->name('rehabilitation.updateRehabilitationCenterIndex');
                    Route::post('/deleteRehabilitationCenter', [Rehabilitation::class, 'deleteRehabilitationCenter'])->name('rehabilitation.deleteRehabilitationCenter');
                    Route::post('/updateRehabilitationCenter', [Rehabilitation::class, 'updateRehabilitationCenter'])->name('rehabilitation.updateRehabilitationCenter');
                    Route::post('/createRehabilitationCenter', [Rehabilitation::class, 'createRehabilitationCenter'])->name('rehabilitation.createRehabilitationCenter');
                    Route::get('/createRehabilitationCenterIndex', [Rehabilitation::class, 'createRehabilitationCenterIndex'])->name('rehabilitation.createRehabilitationCenterIndex');

                    Route::get('/rehabilitationListDeleted', [Rehabilitation::class, 'listDeleted'])->name('rehabilitation.listDeleted');
                    Route::get('/rehabilitationFetchDeleted', [Rehabilitation::class, 'fetchDeleted'])->name('rehabilitation.fetchDeleted');
                });
                Route::group(['middleware' => 'check_permission:rehabilitation_apply'], function () {
                    Route::get('/fetchApply/{id}', [Rehabilitation::class, 'fetchApply'])->name('rehabilitation.fetchApply');
                    Route::get('/applyList/{id}', [Rehabilitation::class, 'applyIndex'])->name('rehabilitation.applyIndex');
                    Route::get('/getApplyDetail', [Rehabilitation::class, 'getApplyDetail'])->name('rehabilitation.getApplyDetail');
                });

                Route::group(['prefix' => '/kjkszpj'], function (){
                    Route::get('/', [MunicipalityInterfaceController::class, 'index'])->name('municipalityPhoto');
                    Route::get('/edit', [MunicipalityInterfaceController::class, 'edit'])->name('municipalityPhoto.edit');
                    Route::post('update/edit-post', [MunicipalityInterfaceController::class, 'edit_post'])->name('municipalityPhoto.edit.post');
                });
            });


            Route::prefix('toyormarxetwri')->middleware('check_permission:baskana_yaz')->group(function () {
                Route::get('/', [WriteToMayorController::class, 'index'])->name('writeToMayor');
                Route::get('/fetch', [WriteToMayorController::class, 'fetch'])->name('writeToMayor.fetch');
                Route::get('/detail/{id}', [WriteToMayorController::class, 'get'])->name('writeToMayor.show');
                Route::post('/delete', [WriteToMayorController::class, 'delete'])->name('writeToMayor.delete');
                Route::get('/update_active', [WriteToMayorController::class, 'update_active'])->name('writeToMayor.update_active');
                Route::post('/createWriteToMayorNotification', [WriteToMayorController::class, 'createWriteToMayorNotification'])->name('writeToMayor.createWriteToMayorNotification');
                Route::post('/writeToMayorStatus', [WriteToMayorController::class, 'writeToMayorStatus'])->name('writeToMayorStatus');
            });
            Route::prefix('royaksikome')->middleware(['check_permission:baskanim', 'checkMayor'])->group(function () {
                Route::get('/', [MayorController::class, 'index'])->name('mayor');
                Route::get('/getMayorsUser', [MayorController::class, 'getMayorsUser'])->name('mayor.getMayorsUser');
                Route::get('/fetch', [MayorController::class, 'fetch'])->name('mayor.fetch');
                Route::get('/create', [MayorController::class, 'create'])->name('mayor.create');
                Route::post('update/post/{id}', [MayorController::class, 'update_post'])->name('mayor.edit.post');
                Route::post('/post', [MayorController::class, 'create_post'])->name('mayor.create.post');
                Route::post('/delete', [MayorController::class, 'delete'])->name('mayor.delete');
            });
        });

        Route::group(['prefix' => '/askaklfmanb'], function (){
            Route::get('/create', [QuestionnaireController::class, 'create'])->name('questionnaire.create.index');
            Route::post('/create_post', [QuestionnaireController::class, 'saveQuestionnaire'])->name('questionnaire.create.post');
            Route::get('/fetchQuestionnaire', [QuestionnaireController::class, 'fetchQuestionnaire'])->name('questionnaire.fetch');
            Route::get('/questionnaireList', [QuestionnaireController::class, 'questionnaireList'])->name('questionnaire.list');
            Route::post('/changeStatus', [QuestionnaireController::class, 'changeStatus'])->name('questionnaire.changeStatus');
            Route::get('/updateIndex/{id}', [QuestionnaireController::class, 'updateIndex'])->name('questionnaire.updateIndex');
            Route::post('/update', [QuestionnaireController::class, 'update'])->name('questionnaire.update');
            Route::get('/answerList/{id}', [QuestionnaireController::class, 'answers'])->name('questionnaire.answers');
            Route::get('/fetchAnswers/{id}', [QuestionnaireController::class, 'fetchAnswers'])->name('questionnaire.fetchAnswers');
            Route::get('/answersDetail/{id}', [QuestionnaireController::class, 'answersDetail'])->name('questionnaire.answersDetail');
            Route::post('/removeQuestion', [QuestionnaireController::class, 'removeQuestion'])->name('questionnaire.removeQuestion');
        });

    });
});
Route::get('/processTree', function (){
    return view('dashboard.pages.event.processTree');
});


