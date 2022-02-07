<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Razorpay extends Student_Controller {

    public $api_config = "";

    function __construct() {
        parent::__construct();
		$this->load->model(array('course_model','coursesection_model','courselesson_model','studentcourse_model','coursequiz_model','course_payment_model','courseofflinepayment_model','coursereport_model'));
        $this->api_config = $this->paymentsetting_model->getActiveMethod();
        $this->setting = $this->setting_model->get();
        $this->load->library('course_mail_sms');
    }

    /*
    This is used to show payment detail page
    */
    public function index() {
        $params = $this->session->userdata('course_amount');
        $data['params'] = $params;
        $data['setting'] = $this->setting;
        $data['merchant_order_id'] = time() . "01";
        $data['txnid'] = time() . "02";
        $data['return_url'] = site_url() . 'students/online_course/razorpay/callback';
        $data['total'] = $params['total_amount'] * 100;
        $data['key_id'] = $this->api_config->api_publishable_key;
        $this->load->view('user/studentcourse/online_course/razorpay/index', $data);
    }

    /*
    This is used to show payment detail page
    */
    public function callback() {
        $params = $this->session->userdata('course_amount');
        $payment_id = $_POST['razorpay_payment_id'];
        $payment_data = array(
            'date' => date('Y-m-d'),
            'student_id' => $params['student_id'],
            'online_courses_id' => $params['courseid'],
            'course_name' => $params['course_name'],
            'actual_price' => $params['actual_amount'],
            'paid_amount' => $params['total_amount'],
            'payment_type' => 'Online',
            'transaction_id' => $payment_id,
            'note' => "Online course fees deposit through Razorpay Txn ID: " . $payment_id,
            'payment_mode' => 'Razorpay',
        );
        $this->course_payment_model->add($payment_data);
        if(!empty($params['courseid'])) {
            $sender_details = array('courseid'=>$params['courseid'],'class' => $params['class'],  'class_section_id'=> $params['class_sections'], 'section'=> $params['section'], 'title' => $params['course_name'], 'price' => $params['total_amount'], 'discount' => $params['discount'], 'assign_teacher' => $params['staff'], 'paid_free' => $params['paid_free'], 'purchase_date' => date('Y-m-d'));         
            $this->course_mail_sms->purchasemail('online_course_purchase', $sender_details);              
        }
        echo 1;
    }

    /*
    This is used to show success page status
    */
    public function success() {
        $this->load->view('user/studentcourse/paymentsuccess');
    }
}