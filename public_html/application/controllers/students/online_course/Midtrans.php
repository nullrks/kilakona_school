<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Midtrans extends Student_Controller {

    public $api_config = "";

    public function __construct() {
        parent::__construct();
		$this->load->model(array('course_model','coursesection_model','courselesson_model','studentcourse_model','coursequiz_model','course_payment_model','courseofflinepayment_model','coursereport_model'));
        $this->api_config = $this->paymentsetting_model->getActiveMethod();
        $this->setting = $this->setting_model->get();
        $this->load->library('Midtrans_lib');
        $this->load->library('course_mail_sms');
    }

    /*
    This is used to show payment detail page
    */
    public function index() {
        $data['params'] = $this->session->userdata('course_amount');
        
        $data['setting'] = $this->setting;
        $enable_payments = array('credit_card');
        $transaction = array(
            'enabled_payments' => $enable_payments,
            'transaction_details' => array(
                'order_id' => time(),
                'gross_amount' => round($data['params']['total_amount']), // no decimal allowed
            ),
        );
        $snapToken = $this->midtrans_lib->getSnapToken($transaction, $this->api_config->api_secret_key);
        $data['snap_Token'] = $snapToken;
        $this->load->view('user/studentcourse/online_course/midtrans/index', $data);
    }

    /*
    This is for payment gateway functionality
    */
    public function midtrans_pay() {
        $response = json_decode($_POST['result_data']);
        $payment_id = $response->transaction_id;
        $params = $this->session->userdata('course_amount');
        $payment_data = array(
                'date' => date('Y-m-d'),
                'student_id' => $params['student_id'],
                'online_courses_id' => $params['courseid'],
                'course_name' => $params['course_name'],
                'actual_price' => $params['actual_amount'],
                'paid_amount' => $params['total_amount'],
                'payment_type' => 'Online',
                'transaction_id' => $payment_id,
                'note' => "Online course fees deposit through midtrans Txn ID: " . $payment_id,
                'payment_mode' => 'midtrans',
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
    public function success(){
       $this->load->view('user/studentcourse/paymentsuccess');
    }
}
