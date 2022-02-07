<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Customsms {

    private $_CI;
    var $AUTH_KEY = ""; //your AUTH_KEY here
    var $senderId = ""; //your senderId here
    var $routeId = ""; //your routeId here
    var $smsContentType = ""; //your smsContentType here

    function __construct($params) {
        $this->_CI = & get_instance();
        $this->session_name = $this->_CI->setting_model->getCurrentSessionName();
    } 

    function sendSMS($to, $message) {
       /* $content = 'AUTH_KEY=' . rawurlencode($this->AUTH_KEY) .
                '&message=' . rawurlencode($message) .
                '&senderId=' . rawurlencode($this->senderId) .
                '&routeId=' . rawurlencode($this->routeId) .
                '&mobileNos=' . rawurlencode($to) .
                '&smsContentType=' . rawurlencode($this->smsContentType);
        $ch = curl_init('https://yourapiurl.com' . $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
	curl_close($ch); */
	$api_key = '560910CA063493';
	$contacts = rawurlencode($to);
	$from = 'DEMO';
	$sms_text = urlencode($message);

//Submit to server

	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, "http://sms.kilakona.co.tz/app/smsapi/index.php");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "key=".$api_key."&campaign=1&routeid=14&type=text&contacts=".$contacts."&senderid=".$from."&msg=".$sms_text);
	$response = curl_exec($ch);
	curl_close($ch);
        return $response;
    }

}

?>
