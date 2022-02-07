<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Paytm extends Student_Controller {

    public $setting = "";

    public function __construct() {

        parent::__construct();
		$this->load->model(array('course_model','coursesection_model','courselesson_model','studentcourse_model','coursequiz_model','course_payment_model','courseofflinepayment_model','coursereport_model'));
        $this->api_config = $this->paymentsetting_model->getActiveMethod();
        $this->setting = $this->setting_model->get();
        $this->load->library('Paytm_lib');
        $this->load->library('course_mail_sms');
    }

    /*
    This is used to show payment detail page and payment gateway functionality
    */
    public function index() {
        $params = $this->session->userdata('course_amount');
        $data['params'] = $params;
        $data['setting'] = $this->setting;
        
        $paytmParams = array();
        $ORDER_ID = time();
        $CUST_ID = time();
        $paytmParams = array(
            "MID" => $this->api_config->api_publishable_key,
            "WEBSITE" => $this->api_config->paytm_website,
            "INDUSTRY_TYPE_ID" => $this->api_config->paytm_industrytype,
            "CHANNEL_ID" => "WEB",
            "ORDER_ID" => $ORDER_ID,
            "CUST_ID" => $params['student_id'],
            "TXN_AMOUNT" => $params['total_amount'],
            "CALLBACK_URL" => base_url() . "students/online_course/Paytm/paytm_response",
        ); 

        $paytmChecksum = $this->paytm_lib->getChecksumFromArray($paytmParams, $this->api_config->api_secret_key);
 
        $paytmParams["CHECKSUMHASH"] = $paytmChecksum;
        $transactionURL = 'https://securegw.paytm.in/order/process';
        $data['paytmParams'] = $paytmParams;
        $data['transactionURL'] = $transactionURL;
        $this->load->view('user/studentcourse/online_course/paytm/index', $data);
    }

    /*
    This is used to show success page status
    */
    public function paytm_response() {
      
        $paytmChecksum = "";
        $paramList = array();
        $isValidChecksum = "FALSE";
        $paramList = $_POST;
        $paytmChecksum = isset($_POST["CHECKSUMHASH"]) ? $_POST["CHECKSUMHASH"] : "";

        $isValidChecksum = $this->paytm_lib->verifychecksum_e($paramList, $this->api_config->api_secret_key, $paytmChecksum);

        if ($isValidChecksum == "TRUE") {

            if ($_POST["STATUS"] == "TXN_SUCCESS") {

                $params = $this->session->userdata('course_amount');
                $payment_id = $_POST['TXNID'];
                $payment_data = array(
                    'date' => date('Y-m-d'),
                    'student_id' => $params['student_id'],
                    'online_courses_id' => $params['courseid'],
                    'course_name' => $params['course_name'],
                    'actual_price' => $params['actual_amount'],
                    'paid_amount' => $params['total_amount'],
                    'payment_type' => 'Online',
                    'transaction_id' => $payment_id,
                    'note' => "Online course fees deposit through Paytm Txn ID: " . $payment_id,
                    'payment_mode' => 'Paytm'
                );
                $this->course_payment_model->add($payment_data);
                if(!empty($params['courseid'])) {
                    $sender_details = array('courseid'=>$params['courseid'],'class' => $params['class'],  'class_section_id'=> $params['class_sections'], 'section'=> $params['section'], 'title' => $params['course_name'], 'price' => $params['total_amount'], 'discount' => $params['discount'], 'assign_teacher' => $params['staff'], 'paid_free' => $params['paid_free'], 'purchase_date' => date('Y-m-d'));         
                    $this->course_mail_sms->purchasemail('online_course_purchase', $sender_details);              
                }
                $this->load->view('user/studentcourse/paymentsuccess');
            } else {
              redirect(base_url("students/online_course/course_payment/paymentfailed"));
            }
        } else {
            redirect(base_url("students/online_course/course_payment/paymentfailed"));
        }
    }
}
