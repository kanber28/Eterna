<?php


use App\Models\Event;
use App\Models\Process;
use App\Models\ProcessTree;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use NotificationChannels\ExpoPushNotifications\ExpoChannel;
use NotificationChannels\ExpoPushNotifications\ExpoMessage;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class Helper extends Notification
{
    //firebase notification key
    //public const fcm_notification_key = "key=AAAAfFeeoHU:APA91bFsX04087mBJiE-jczq9TR4DpmVADruZsPcLlEsK7EWnmkST0Y3BugGJIpQ4rSYDb2pGs8ByX3RSNzUhU-r7Qq9nHw-KRnUMoTswIEd3ff3aaBQjim4gXpYanBQZvKJAFrNEXZX";

    public static function pushNotificationToCurl($title, $body, $expoToken, $data, $type){

        if  (is_array($expoToken)){
            $maxNotificationsPerBatch = 100;
            $notificationBatches = array_chunk($expoToken, $maxNotificationsPerBatch);
            foreach ($notificationBatches as $batch){
                $payload = array(
                    'channelName' => "all/staff/users",
                    'to' => $batch,
                    'sound' => 'default',
                    'body' => $body,
                    'title' => $title,
                    'data' => ['type' => $type, 'data' => $data],
                );
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://exp.host/--/api/v2/push/send",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_HTTPHEADER => array(
                        "Accept: application/json",
                        "Accept-Encoding: gzip, deflate",
                        "Content-Type: application/json",
                        "cache-control: no-cache",
                        "host: exp.host"
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    return $err;
                } else {
                    return $response;
                }
            }
        }
        else {
            $payload = array(
                'channelName' => "all/staff/users",
                'to' => $expoToken,
                'sound' => 'default',
                'body' => $body,
                'title' => $title,
                'data' => ['type' => $type, 'data' => $data],
            );
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://exp.host/--/api/v2/push/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json",
                    "Accept-Encoding: gzip, deflate",
                    "Content-Type: application/json",
                    "cache-control: no-cache",
                    "host: exp.host"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return $err;
            } else {
                return $response;
            }
        }


        //typeler bunlardan biri olacak => event - announcement - news - mission - missionDone - missionProcess - default - rejectReport - missionNotification

    }

    public static function forceNotificationToPersonOne($userID, $title, $body, $data){
        $user = User::find($userID);
        $command =
            'curl -k -H "Content-Type: application/json" -X POST "https://exp.host/--/api/v2/push/send" -d'.
            "'{
             \"to\":\"ExponentPushToken[30IUgLKfB_MYoJnsvT0Sik]\",
             \"title\":".$title.",
             \"body\":".$body.",
             \"data\":".$data."
            }'";
        exec($command, $output, $code);

        return [$output, $code, $user->firebase_token];
    }

    public static function forceNotificationToMultiple($userArr, $title, $body, $data){
        $resArr = [];
        $arr = [1, 2];
        foreach ($arr as $user){
            $command =
                'curl -k -H "Content-Type: application/json" -X POST "https://exp.host/--/api/v2/push/send" -d'.
                "'{
                 \"to\":[\"ExponentPushToken[w2RKXaGhN7h5H5RHfuE75s]\", \"ExponentPushToken[0erLu3JeuLsKlIQqfOSm60]\"],
                 \"title\":".$title.",
                 \"body\":".$body.",
                 \"data\":".$data."
                }'";
            exec($command, $output, $code);
            array_push($resArr, $output);
        }

        return $resArr;
    }

    public static function curlExpoPushNotification($title, $subTitle, $expoToken, $data)
    {
        $payload = array(
            'channelName' => "all/staff/users",
            'to' => $expoToken,
            'sound' => 'default',
            'body' => $subTitle,
            'title' => $title,
            'data' => ['data' => $data],
        );
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://exp.host/--/api/v2/push/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Accept-Encoding: gzip, deflate",
                "Content-Type: application/json",
                "cache-control: no-cache",
                "host: exp.host"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            print_r("cURL Error #:" . $err);
        } else {
            print_r($response);
        }
    }

    public static function pushNotificationTopic($title, $subTitle, $channelname, $data)
    {
        self::curlExpoPushNotification($title, $subTitle, $channelname, $data, "all");

        //expo push notification
//        $channelName = $channelname;
//        $expo = \ExponentPhpSDK\Expo::normalSetup();
//
//        if ($channelname == "all") {
//            $user = User::all();
//        } elseif ($channelname == "users") {
//            $user = User::role('Mobil Kullanıcı')->get();
//        } elseif ($channelname == "staff") {
//            $user = User::role('Employee')->get();
//        }
//        if (!$user->isEmpty()) {
//            $user = json_decode(json_encode($user), true);
//            $parse_user = array_chunk($user, 100);
//            $sayac = 0;
//            //review edilecek
//            foreach ($parse_user as $use) {
//                foreach ($use as $u) {
//                    if (isset($u['firebase_token'])) {
//                        //$expo->subscribe($channelName, $u->firebase_token);
//                        $sayac++;
//                        if ($sayac % 100 == 0) {
//                            sleep(2);
//                        }
//                    }
//                }
//            }
//            $notification = ['title' => $title, 'body' => $subTitle, 'data' => $data];
//            $expo->notify([$channelName], $notification);
//        } else {
//            return redirect()->back()->with('error', 'İlgili kullanıcı grubuna ait hiç token bulunamadı.');
//        }

        //firebase
//        $datas = [
//            "topic" => '/topics',
//            "condition" => " '$channelName' in topics",
//            "notification" => [
//                "title" => $title,
//                "body" => $subTitle,
//            ],
//            "data" => $data,
//            "contentAvailable" => "true",
//            "priority" => "high",
//            "apns" => [
//                "payload" => [
//                    "aps" => [
//                        "sound" => "default",
//                    ]
//                ]
//            ]
//        ];
//        $url = 'https://fcm.googleapis.com/fcm/send';
//        $token = Helper::fcm_notification_key;
//        $result = Http::withHeaders([
//            'Content-Type' => 'application/json',
//            'Authorization' => $token
//        ])->post($url,$datas);
    }

    public static function pushNotificationToPersonOne($title, $subTitle, $userToken, $data)
    {
        //expo push notification
        $channelName = 'all';
        $recipient = $userToken;
        $expo = \ExponentPhpSDK\Expo::normalSetup();
        $expo->subscribe($channelName, $recipient);
        $notification = ['title' => $title, 'body' => $subTitle, 'data' => $data];
        $expo->notify([$channelName], $notification);

        //firebase
//        $datas = [
//            //gönderilecek kişinin tokenı gönderilecek
//            "to" => $userToken,
//
//            "notification" => [
//                "title" => $title,
//                "body" => $subTitle,
//            ],
//            "data" => $data,
//            "contentAvailable" => "true",
//            "priority" => "high",
//            "apns" => [
//                "payload" => [
//                    "aps" => [
//                        "sound" => "default",
//                    ]
//                ]
//            ]
//        ];
//        $url = 'https://fcm.googleapis.com/fcm/send';
//        $token = Helper::fcm_notification_key;
//        $result = Http::withHeaders([
//            'Content-Type' => 'application/json',
//            'Authorization' => $token
//        ])->post($url,$datas);
    }

    public static function pushNotificationToPersonMultiple($title, $subTitle, $userTokenArray, $data)
    {
        //expo push notification
        $channelName = 'all';
        $expo = \ExponentPhpSDK\Expo::normalSetup();

        foreach ($userTokenArray as $array) {
            $expo->subscribe($channelName, $array);
        }

        $notification = ['title' => $title, 'body' => $subTitle, 'data' => $data];
        $expo->notify([$channelName], $notification);

        //firebase
//        $datas = [
//            //gönderilecek kişilerin tokenı array gönderilecek
//            // ['1.token', '2.token', ...];
//            "registration_ids" =>$userTokenArray,
//
//            "notification" => [
//                "title" => $title,
//                "body" => $subTitle,
//            ],
//
//            "data" => $data,
//            "contentAvailable" => "true",
//            "priority" => "high",
//            "apns" => [
//                "payload" => [
//                    "aps" => [
//                        "sound" => "default",
//                    ]
//                ]
//            ]
//        ];
//        $url = 'https://fcm.googleapis.com/fcm/send';
//        $token = Helper::fcm_notification_key;
//        $result = Http::withHeaders([
//            'Content-Type' => 'application/json',
//            'Authorization' => $token
//        ])->post($url,$datas);
    }

    public static function getWeather()
    {
        $response = Http::get('https://api.openweathermap.org/data/2.5/weather?q=Maltepe&appid=bea8e829e7b8a327c400923997674dd4&lang=tr&units=metric');
        $response = json_decode($response, true);
        if ($response['cod'] == 200) {
            $weather = \App\Models\WeatherApi::find(1);
            $weather->main = $response['weather'][0]['main'];
            $weather->icon = $response['weather'][0]['icon'];
            $weather->description = $response['weather'][0]['description'];
            $weather->temp = $response['main']['temp'];
            $weather->fells_like = $response['main']['feels_like'];
            $weather->temp_min = $response['main']['temp_min'];
            $weather->temp_max = $response['main']['temp_max'];
            $weather->humidity = $response['main']['humidity'];
            $weather->wind_speed = $response['wind']['speed'];
            $weather->area_name = $response['name'];
            $weather->save();
        }
    }

    public static function scriptStripper($input, $ck_editor = false)
    {
        if ($input != null) {
            if ($ck_editor != false) {
                return $input;
            }
            return strip_tags($input);
        }
        if ($input == null) {
            return null;
        }
    }

    public static function isDocument($FileName)
    {
        $response = [
            'status' => 'error',
            'data' => ''
        ];
        $path_parts = pathinfo($FileName->getClientOriginalName());
        $validateFile = ['pdf', 'xls', 'xlsx', 'doc', 'docx'];
        if (in_array($path_parts['extension'], $validateFile)) {
            $response = [
                'status' => 'ok',
                'data' => $path_parts
            ];
        }
        return $response;
    }

    public static function isImage($FileName)
    {

        $response = [
            'status' => 'error',
            'data' => ''
        ];
        $path_parts = pathinfo($FileName->getClientOriginalName());
        $validateFile = ['jpeg', 'bmp', 'png', 'jpg'];

        if (in_array($path_parts['extension'], $validateFile)) {

            $response = [
                'status' => 'ok',
                'data' => $path_parts
            ];

        } else {
            $response = [
                'status' => 'error',
                'data' => ''
            ];
        }
        return $response;

    }

    public static function saveProcess($userID, $reportID, $title, $processContent){
        $processTree = ProcessTree::where('report_id', $reportID)->first();

        if (isset($processTree)){
            $process = new Process();
            $process->title = $title;
            $process->process = $processContent;
            $process->user_id = $userID;
            $process->process_tree_id = $processTree->id;
            $process->save();
        }
        else {
            $processTree = new ProcessTree();
            $processTree->report_id = $reportID;
            $processTree->save();

            $process = new Process();
            $process->title = $title;
            $process->process = $processContent;
            $process->user_id = $userID;
            $process->process_tree_id = $processTree->id;
            $process->save();
        }
    }
}
