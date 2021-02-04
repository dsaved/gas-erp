<?php
class SMS
{
    private static $country = "NG";
    private static $smsmobile24_response = array(
        "OK"=>"Successful",
        "2904"=>"SMS Sending Failed",
        "2905"=>"Invalid username/password combination",
        "2906"=>"Credit exhausted",
        "2907"=>"Gateway unavailable",
        "2908"=>"Invalid schedule date format",
        "2909"=>"Unable to schedule",
        "2910"=>"Username is empty",
        "2911"=>"Password is empty",
        "2912"=>"Recipient is empty",
        "2913"=>"Message is empty",
        "2914"=>"Sender is empty",
        "2915"=>"One or more required fields are empty",
    );   
    
    private static $smsmnotify_response = array(
        "1000" => "Message submited successful",
        "1002" => "SMS sending failed",
        "1003" => "insufficient balance",
        "1004" => "invalid API key",
        "1005" => "invalid Phone Number",
        "1006" => "invalid Sender ID. Sender ID must not be more than 11 Characters. Characters include white space.",
        "1007" => "Message scheduled for later delivery",
        "1008" => "Empty Message",
        "1009" => "Empty from date and to date",
        "1010" => "No mesages has been sent on the specified dates using the specified api key",
        "1011" => "Numeric Sender IDs are not allowed",
        "1012" => "Sender ID is not registered. Please contact our support team via senderids@mnotify.com or call 0200896265 for assistance"
    );


    function __construct()
    { }

    public function formatNum($number, $countrySname)
    {
        self::$country = $countrySname;
        return CountryCodes::formatNumber($number, $countrySname);
    }

    public function send($message, $number)
    {
        if (self::$country==="NG") {
            return $this->nigeria($message, $number);
        } elseif (self::$country==="GH") {
            return $this->ghana($message, $number);
        } else {
            return null;
        }
    }

    public function balance($country = "NG")
    {
        self::$country = $country;
        if (self::$country==="NG") {
            return http_get("https://www.smsmobile24.com/index.php?option=com_spc&comm=spc_api&username=".CLIENT_ID."&password=".CLIENT_SECRET."&balance=true");
        } elseif (self::$country==="GH") {
            return http_get('https://apps.mnotify.net/smsbalance?key='.SMSKEY);
        } else {
            return null;
        }
    }

    /**
     * @param $message, $number
     * send sms to ghana using mNotify sms gateway API
     */
    public function nigeria($message, $number)
    {
        $response = array();
        //INITIALIZE PARAMITERS FOR CONNECTION
        $data = http_getp("https://www.smsmobile24.com/index.php", array(
            "option" => "com_spc",
            "comm" => "spc_api",
            "message" => urlencode($message),
            "recipient" => $number,
            "username" => CLIENT_ID,
            "password" => CLIENT_SECRET,
            "sender" => FROM,
        ));
        $result = explode(" ", $data)[0];

        if ("OK" === trim($result)) {
            $response['success'] = true;
            $response['message'] = self::$smsmobile24_response[$result];
        } else {
            $response['success'] = false;
            $response['message'] = self::$smsmobile24_response[$result];
        }
        return $response;
    }

    /**
     * @param $message, $number
     * send sms to ghana using mNotify sms gateway API
     */
    public function ghana($message, $number)
    {
        $response = array();
        $data = array(
            "key" => SMSKEY,
            "sender_id" => FROM,
            "to" => $number,
            "msg" => urlencode($message),
        );
        $data = http_getp("https://apps.mnotify.net/smsapi", $data);
        $data = json_decode($data);
        if ($data->code === "1000") {
            $response['success'] = true;
            $response['message'] = $data->message??self::$smsmnotify_response[$data->code];
        } else {
            $response['success'] = false;
            $response['message'] = $data->message??self::$smsmnotify_response[$data->code];
        }
        return $response;
    }
}
