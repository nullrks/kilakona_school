<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Paypal extends Student_Controller {

    public $setting = "";

    function __construct() {
        parent::__construct();
		$this->load->model(array('course_model','coursesection_model','courselesson_model','studentcourse_model','coursequiz_model','course_payment_model','courseofflinepayment_model','coursereport_model'));
        $this->load->helper('file');
 
        $this->load->library('auth');
        $this->load->library('paypal_payment');
        $this->load->library('course_mail_sms');
        $this->setting = $this->setting_model->get();
    }
 
    public function index() {
        $data = array();
        $data['params'] = $this->session->userdata('course_amount');
        $data['setting'] = $this->setting;
        $this->load->view('user/studentcourse/online_course/paypal/index', $data);
    }


 
    public function complete() {

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $params=$this->session->userdata('course_amount');
            $payment = array(
            'cancelUrl' => site_url('students/online_course/paypal/getsuccesspayment'),
            'returnUrl' => site_url('students/online_course/paypal/getsuccesspayment'),
            'course_name' => $params['course_name'],
            'name' => $params['name'],
            'description' => 'Online Course Fess',
            'amount' => $params['total_amount'],
            'currency' => $this->setting[0]['currency'],
            );
        
            $response = $this->paypal_payment->payment($payment);
            if ($response->isSuccessful()) {
                
            } elseif ($response->isRedirect()) {
                $response->redirect();
            } else {
                echo $response->getMessage();
            }
        }
    } 

    //paypal successpayment
    public function getsuccesspayment() {
            $params=$this->session->userdata('course_amount');
            $payment_success = array(
            'cancelUrl' => site_url('students/online_course/paypal/getsuccesspayment'),
            'returnUrl' => site_url('students/online_course/paypal/getsuccesspayment'),
            'course_name' => $params['course_name'],
            'name' => $params['name'],
            'description' => 'Online Course Fess',
            'amount' => $params['total_amount'],
            'currency' => $this->setting[0]['currency'],
            );
        $response = $this->paypal_payment->success($payment_success);

        $paypalResponse = $response->getData();
        if ($response->isSuccessful()) {
            $purchaseId = $_GET['PayerID'];

            if (isset($paypalResponse['PAYMENTINFO_0_ACK']) && $paypalResponse['PAYMENTINFO_0_ACK'] === 'Success') {
                if ($purchaseId) {
                    
                    $ref_id = $paypalResponse['PAYMENTINFO_0_TRANSACTIONID'];
                $params = $this->session->userdata('course_amount');
            
            $payment_data = array(
                'date' => date('Y-m-d'),
                'student_id' => $params['student_id'],
                'online_courses_id' => $params['courseid'],
                'course_name' => $params['course_name'],
                'actual_price' => $params['actual_amount'],
                'paid_amount' => $params['total_amount'],
                'payment_type' => 'Online',
                'transaction_id' => $ref_id,
                'note' => "Online course fees deposit through Paypal Ref. ID: " . $ref_id,
                'payment_mode' => 'Paypal',
            );
            $this->course_payment_model->add($payment_data);
            if(!empty($params['courseid'])) {
                $sender_details = array('courseid'=>$params['courseid'],'class' => $params['class'],  'class_section_id'=> $params['class_sections'], 'section'=> $params['section'], 'title' => $params['course_name'], 'price' => $params['total_amount'], 'discount' => $params['discount'], 'assign_teacher' => $params['staff'], 'paid_free' => $params['paid_free'], 'purchase_date' => date('Y-m-d'));         
                $this->course_mail_sms->purchasemail('online_course_purchase', $sender_details);              
            }
            $this->load->view('user/studentcourse/paymentsuccess');
                }
            }
        } elseif ($response->isRedirect()) {
            $response->redirect();
        } else {
            redirect(base_url("students/online_course/course_payment/paymentfailed"));
        }
    }
}