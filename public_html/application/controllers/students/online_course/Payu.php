<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Payu extends Student_Controller {

    function __construct() {
        parent::__construct();
		$this->load->model(array('course_model','coursesection_model','courselesson_model','studentcourse_model','coursequiz_model','course_payment_model','courseofflinepayment_model','coursereport_model'));
        $this->setting = $this->setting_model->get();
        $this->load->library('course_mail_sms');
    }
 
    /*
    This is used to show payment detail page and payment gateway functionality
    */
    public function index() {
        $pre_session_data = $this->session->userdata('course_amount');
        $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
        $pre_session_data['txn_id'] = $txnid;
        $this->session->set_userdata("params", $pre_session_data);
        $session_data = $this->session->userdata('course_amount');
        $session_data['name'] = ($session_data['name'] != "") ? $session_data['name'] : "noname";
        $session_data['email'] = ($session_data['email'] != "") ? $session_data['email'] : "noemail@gmail.com";
        $session_data['contact_no'] = ($session_data['contact_no'] != "") ? $session_data['contact_no'] : "0000000000";
        $pay_method = $this->paymentsetting_model->getActiveMethod();
    
        //payumoney details
        $amount = $session_data['total_amount'];
        $customer_name = $session_data['name'];
        $customer_emial = $session_data['email'];
        //echo $customer_emial;die;
        $product_info = 'online course';
        $MERCHANT_KEY = $pay_method->api_secret_key;
        $SALT = $pay_method->salt;

        //optional udf values 
        $udf1 = '';
        $udf2 = '';
        $udf3 = '';
        $udf4 = '';
        $udf5 = '';

        $hashstring = $MERCHANT_KEY . '|' . $txnid . '|' . $amount . '|' . $product_info . '|' . $customer_name . '|' . $customer_emial . '|' . $udf1 . '|' . $udf2 . '|' . $udf3 . '|' . $udf4 . '|' . $udf5 . '||||||' . $SALT;
        
        $hash = strtolower(hash('sha512', $hashstring));

        $success = base_url('students/online_course/payu/success');
        $fail = base_url('students/online_course/payu/success');
        $cancel = base_url('students/online_course/payu/success');
        $data = array(
            'mkey' => $MERCHANT_KEY,
            'tid' => $txnid,
            'hash' => $hash,
            'amount' => $amount,
            'name' => $customer_name,
            'productinfo' => $product_info,
            'mailid' => $customer_emial,
            'action' => "https://secure.payu.in", //for live change action  https://secure.payu.in
            'sucess' => $success,
            'failure' => $fail,
            'cancel' => $cancel
        );
        $data['session_data'] = $session_data;
        $data['setting'] = $this->setting;
        $this->load->view('user/studentcourse/online_course/payu/index', $data);
    }
 
    /*
    This is used to validate user and payment information
    */
    function checkout() {
        $this->form_validation->set_rules('firstname', 'Customer Name', 'required|trim|xss_clean');
        $this->form_validation->set_rules('phone', 'Mobile No', 'required|trim|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|xss_clean');
        $this->form_validation->set_rules('amount', 'Amount', 'required|trim|xss_clean');
        if ($this->form_validation->run() == false) {
            $data = array(
                'amount' => form_error('amount'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
       } else {
            $array = array('status' => 'success', 'error' => '');
            echo json_encode($array);
        }
    }

    /*
    This is used to show success page status
    */
    public function success() {
        
        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            if ($this->input->post('status') == "success") {
                $params = $this->session->userdata('course_amount');
                $mihpayid = $this->input->post('mihpayid');
                $transactionid = $this->input->post('txnid');
                    $payment_data = array(
                        'date' => date('Y-m-d'),
                        'student_id' => $params['student_id'],
                        'online_courses_id' => $params['courseid'],
                        'course_name' => $params['course_name'],
                        'actual_price' => $params['actual_amount'],
                        'paid_amount' => $params['total_amount'],
                        'payment_type' => 'Online',
                        'transaction_id' => $transactionid,
                        'note' => "Online course fees deposit through PayU Txn ID: " . $transactionid . ", PayU Ref ID: " . $mihpayid,
                        'payment_mode' => 'PayU',
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
        }
    }
}