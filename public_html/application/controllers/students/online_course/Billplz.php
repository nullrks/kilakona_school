<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Billplz extends Student_Controller {

    public $api_config = "";

    function __construct() {
        parent::__construct();
		$this->load->model(array('course_model','coursesection_model','courselesson_model','studentcourse_model','coursequiz_model','course_payment_model','courseofflinepayment_model','coursereport_model'));
        $this->api_config = $this->paymentsetting_model->getActiveMethod();
        $this->setting = $this->setting_model->get();
        $this->load->library('billplz_lib');
        $this->load->library('course_mail_sms');
    }

    /*
    This is used to show payment detail page
    */
    public function index() {
        $params = $this->session->userdata('course_amount');
        $data['params'] = $params;
        $data['setting'] = $this->setting;
        $this->load->view('user/studentcourse/online_course/billplz/index', $data);
    }

    /*
    This is for payment gateway functionality
    */
    public function pay(){
    	$params = $this->session->userdata('course_amount');
        $data['name'] = $params['name'];
        $data['title'] = 'Online Course Fee';
        $data['return_url'] = base_url() . 'students/online_course/billplz/callback';
        $parameter = array(
            'title' => $data['name'],
            'description' => $data['title'],
            'amount' => $params['total_amount']*100
        );
        $optional = array(
            'fixed_amount' => 'true',
            'fixed_quantity' => 'true',
            'payment_button' => 'pay',
            'redirect_uri'=>$data['return_url'],
            'photo' => '',
            'split_header' => false,
            'split_payments' => array(
            ['split_payments[][email]' => $this->api_config->api_email],
            ['split_payments[][fixed_cut]' => '0'],
            ['split_payments[][variable_cut]' => ''],
            ['split_payments[][stack_order]' => '0'],
        )
        );
        $api_key=$this->api_config->api_secret_key;
        $this->billplz_lib->payment($parameter,$optional,$api_key);
    }

    /*
    This is used to show success page status
    */
    public function callback() {
        $params = $this->session->userdata('course_amount');
        if($_GET['billplz']['paid']=='true'){
        	$payment_id =$_GET['billplz']['id'];
            $payment_data = array(
                'date' => date('Y-m-d'),
                'student_id' => $params['student_id'],
                'online_courses_id' => $params['courseid'],
                'course_name' => $params['course_name'],
                'actual_price' => $params['actual_amount'],
                'paid_amount' => $params['total_amount'],
                'payment_type' => 'Online',
                'transaction_id' => $payment_id,
                'note' => "Online course fees deposit through Billplz Txn ID: " . $payment_id,
                'payment_mode' => 'Billplz',
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