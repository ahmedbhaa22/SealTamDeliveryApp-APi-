<?php
namespace App\Helpers;

use DB;

class FCM
{
    private  const URL ='https://fcm.googleapis.com/fcm/send';
    private  const HEADER = array(
        'Authorization: key=' . "AAAAM7mSsoE:APA91bFwY_7HlIj1-R72mGcOvpXAVRfUqYnAMwkpFTORJnoCkQzxyi-Rh8mRbiESDPg4xPurR5Z1hjXQW1SzqkksL68UQCx_3zVzXBaOX6LSNhTs_mtAQ7W4AgIkOkdwGd7dL8I4RObu",
        'Content-Type: application/json');
    public static function snedNotification($data, $devicToken)
    {
        $fields = array(
                'registration_ids' => array(
                        $devicToken
                ),
                'data' => $data

        );
        $fields = json_encode($fields);



        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, SELF::URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, SELF::HEADER);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
