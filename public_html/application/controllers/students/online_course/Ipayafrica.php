<?php
 if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Ipayafrica extends Student_Controller
{
    public $payment_method = array();
    public $pay_method     = array();
    public $patient_data;
    public $setting;

    public function __construct()
    {
        parent::__construct();
		$this->load->model(array('course_model','coursesection_model','courselesson_model','studentcourse_model','coursequiz_model','course_payment_model','courseofflinepayment_model','coursereport_model'));
        $this->pay_method   = $this->paymentsetting_model->getActiveMethod();
        $this->setting        = $this->setting_model->get();
        $this->load->library('course_mail_sms');
    }

    /*
    This is used to show payment detail page
    */
    public function index(){
        $data['params'] = $this->session->userdata('course_amount');
        $data['setting'] = $this->setting;
        $this->load->view('user/studentcourse/online_course/ipay_africa/index', $data);
    }

    /*
    This is for payment gateway functionality
    */
    public function pay() {
    $this->form_validation->set_rules('phone', $this->lang->line('phone'), 'trim|required|xss_clean');
    $this->form_validation->set_rules('email', $this->lang->line('email'), 'trim|required|xss_clean');

    $params = $this->session->userdata('course_amount');
    $data['params'] = $params;
    $data['setting'] = $this->setting;

    if ($this->form_validation->run() == false) {
        $this->load->view('user/studentcourse/online_course/ipay_africa/index', $data);
    } else {
        $instadetails = $this->paymentsetting_model->getActiveMethod();
        $insta_apikey = $instadetails->api_secret_key;
        $insta_authtoken = $instadetails->api_publishable_key;

        $fields = array("live"=> "1",
            "oid"=> uniqid(),
            "inv"=> time(),
            "ttl"=> $params['total_amount'],
            "tel"=> $_POST['phone'],
            "eml"=> $_POST['email'],
            "vid"=> ($this->pay_method->api_publishable_key),
            "curr"=> $this->setting[0]['currency'],
            "p1"=> "airtel",
            "p2"=> "",
            "p3"=> "",
            "p4"=> $params['total_amount'],
            "cbk"=> base_url().'students/online_course/ipayafrica/success',
            "cst"=> "1",
            "crl"=> "2"
            );
         
        $datastring =  $fields['live'].$fields['oid'].$fields['inv'].$fields['ttl'].$fields['tel'].$fields['eml'].$fields['vid'].$fields['curr'].$fields['p1'].$fields['p2'].$fields['p3'].$fields['p4'].$fields['cbk'].$fields['cst'].$fields['crl'];

        $hashkey =($this->pay_method->api_secret_key);
        $generated_hash = hash_hmac('sha1',$datastring , $hashkey);
        $data['fields']=$fields;
        $data['generated_hash']=$generated_hash;
        $this->load->view('user/studentcourse/online_course/ipay_africa/pay', $data);
    }
}

    /*
    This is used to show success page status
    */
    public function success(){
        if(!empty($_GET['status'])){
            $params = $this->session->userdata('course_amount');
            $payment_id = $_GET['txncd'];;
            $payment_data = array(
                    'date' => date('Y-m-d'),
                    'student_id' => $params['student_id'],
                    'online_courses_id' => $params['courseid'],
                    'course_name' => $params['course_name'],
                    'actual_price' => $params['actual_amount'],
                    'paid_amount' => $params['total_amount'],
                    'payment_type' => 'Online',
                    'transaction_id' => $payment_id,
                    'note' => "Online course fees deposit through iPayAfrica Txn ID: " . $payment_id,
                    'payment_mode' => 'iPayAfrica',
                );
                $this->course_payment_model->add($payment_data);
                if(!empty($params['courseid'])) {
                    $sender_details = array('courseid'=>$params['courseid'],'class' => $params['class'],  'class_section_id'=> $params['class_sections'], 'section'=> $params['section'], 'title' => $params['course_name'], 'price' => $params['total_amount'], 'discount' => $params['discount'], 'assign_teacher' => $params['staff'], 'paid_free' => $params['paid_free'], 'purchase_date' => date('Y-m-d'));
                    $this->course_mail_sms->purchasemail('online_course_purchase', $sender_details);              
                }
                $this->load->view('user/studentcourse/paymentsuccess');
        }else{
            redirect(base_url("students/online_course/course_payment/paymentfailed"));
        }
    }
}