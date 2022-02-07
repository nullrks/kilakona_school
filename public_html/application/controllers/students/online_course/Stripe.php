<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Stripe extends Student_Controller {

    public $setting = "";
  
    function __construct() {
        parent::__construct();
		$this->load->model(array('course_model','coursesection_model','courselesson_model','studentcourse_model','coursequiz_model','course_payment_model','courseofflinepayment_model','coursereport_model'));
        $this->load->library('course_stripe_payment');
        $this->setting = $this->setting_model->get();
        $this->load->library('course_mail_sms');
    }

    /*
    This is used to show payment detail page
    */
    public function index() {  

        $stripedetails = $this->paymentsetting_model->getActiveMethod();
        $data['params'] = $this->session->userdata('course_amount');
        $data['params']['api_publishable_key'] =$stripedetails->api_publishable_key;
        $data['setting'] = $this->setting;
        $this->load->view('user/studentcourse/online_course/stripe/index', $data);
    }

    /*
    This is used to show success page status and payment gateway functionality
    */
    public function complete() {
        
        $params = $this->session->userdata('course_amount');
        $stripedetails = $this->paymentsetting_model->getActiveMethod();
        $stripeToken = $this->input->post('stripeToken');
        $stripeTokenType = $this->input->post('stripeTokenType');
        $stripeEmail = $this->input->post('stripeEmail');
        $data = $this->input->post();

        $data['currency'] = $this->setting[0]['currency']; 
        $data['total']=($params['actual_amount']*100);
        
        $response = $this->course_stripe_payment->make_payment($data);

        if ($response->isSuccessful()) {
            $transactionid = $response->getTransactionReference();
            $response = $response->getData();
            if ($response['status'] == 'succeeded') {                
                
                $payment_data = array(
                'date' => date('Y-m-d'),
                'student_id' => $params['student_id'],
                'online_courses_id' => $params['courseid'],
                'course_name' => $params['course_name'],
                'actual_price' => $params['actual_amount'],
                'paid_amount' => $params['total_amount'],
                'payment_type' => 'Online',
                'transaction_id' =>  $transactionid,
                'note' => "Online course fees deposit through Stripe Txn ID: " . $transactionid,
                'payment_mode' => 'Stripe',
            );
            $this->course_payment_model->add($payment_data);
            if(!empty($params['courseid'])) {
                $sender_details = array('courseid'=>$params['courseid'],'class' => $params['class'],  'class_section_id'=> $params['class_sections'], 'section'=> $params['section'], 'title' => $params['course_name'], 'price' => $params['total_amount'], 'discount' => $params['discount'], 'assign_teacher' => $params['staff'], 'paid_free' => $params['paid_free'], 'purchase_date' => date('Y-m-d'));         
                $this->course_mail_sms->purchasemail('online_course_purchase', $sender_details);              
            }
           $this->load->view('user/studentcourse/paymentsuccess');
            }
        } elseif ($response->isRedirect()) {
           $response->redirect();
        } else {
           redirect(base_url("students/online_course/course_payment/paymentfailed"));
        }
    }
}