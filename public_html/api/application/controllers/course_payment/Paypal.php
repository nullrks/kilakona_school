<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Paypal extends Admin_Controller {

    var $setting;
    var $payment_method;

    public function __construct() {
        parent::__construct();
        $this->load->library('paypal_payment');
        $this->setting = $this->setting_model->get();
        $this->payment_method = $this->paymentsetting_model->get();
          $this->load->model('course_model');
    }
    
    /*
    This is used to show payment detail page
    */
    public function index() {

        $pay_method = $this->paymentsetting_model->getActiveMethod();
        if ($pay_method->payment_type == "paypal") {
            $data = array();
            if ($this->session->has_userdata('course_amount')) {
                if ($pay_method->api_username != "" && $pay_method->api_password != "" && $pay_method->api_signature != "") {
                    $params = $this->session->userdata('course_amount');
                    $data['session_params'] = $params;
                    $data['setting'] = $this->setting;
                    
                }
                $this->load->view('course_payment/paypal/index', $data);
            }
        } else {
            $this->session->set_flashdata('error', 'Oops! Something went wrong');
            $this->load->view('payment/error');
        }
    }
    
    /*
    This is for payment gateway functionality
    */
    public function pay() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $params=$this->session->userdata('course_amount');
            $payment = array(
            'cancelUrl' => site_url('course_payment/paypal/getsuccesspayment'),
            'returnUrl' => site_url('course_payment/paypal/getsuccesspayment'),
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
            'cancelUrl' => site_url('course_payment/paypal/getsuccesspayment'),
            'returnUrl' => site_url('course_payment/paypal/getsuccesspayment'),
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
                   
         
            $payment_data = array(
                'date' => date('Y-m-d'),
                'student_id' => $params['student_id'],
                'online_courses_id' => $params['courseid'],
                'course_name' => $params['course_name'],
                'actual_price' => $params['actual_amount'],
                'paid_amount' => $params['total_amount'],
                'payment_type' => 'Online',
                'transaction_id' => $ref_id,
                'note' => "Online course fees deposit through Paypal Txn ID: " . $ref_id,
                'payment_mode' => 'Paypal',
            );
            $this->course_model->add($payment_data);
            $this->load->view('course_payment/paymentsuccess');
                }
            }
        } elseif ($response->isRedirect()) {
            $response->redirect();
        } else {
            redirect(base_url("course_payment/course_payment/paymentfailed"));
        }
    }
}