<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Ccavenue extends Student_Controller {

    function __construct() {
        parent::__construct();
		$this->load->model(array('course_model','coursesection_model','courselesson_model','studentcourse_model','coursequiz_model','course_payment_model','courseofflinepayment_model','coursereport_model'));
        $this->setting = $this->setting_model->get();
        $this->load->library('Ccavenue_crypto');
        $this->load->library('course_mail_sms');
    }

    /*
    This is used to show payment detail page
    */
    public function index() {
        $data['params'] = $this->session->userdata('course_amount');
        $data['setting'] = $this->setting;
        $this->load->view('user/studentcourse/online_course/ccavenue/index', $data);
    }

    /*
    This is for payment gateway functionality
    */
     public function pay()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $session_data            = $this->session->userdata('course_amount');
            $pay_method              = $this->paymentsetting_model->getActiveMethod();
            $details['tid']          = abs(crc32(uniqid()));
            $details['merchant_id']  = $pay_method->api_secret_key;
            $details['order_id']     = abs(crc32(uniqid()));
            $details['amount']       = $session_data['total_amount'];
            $details['currency']     = $this->setting[0]['currency'];
            $details['redirect_url'] = base_url('students/online_course/ccavenue/success');
            $details['cancel_url']   = base_url('students/online_course/ccavenue/cancel');
            $details['language']     = "EN";
            $merchant_data = "";
            foreach ($details as $key => $value) {
                $merchant_data .= $key . '=' . $value . '&';
            }
            $data['encRequest']  = $this->ccavenue_crypto->encrypt($merchant_data, $pay_method->salt);
            $data['access_code'] = $pay_method->api_publishable_key;

            $this->load->view('user/studentcourse/online_course/ccavenue/ccavenue_pay', $data);
        } else {
            redirect(base_url("students/online_course/ccavenue"));
        }
    }

    /*
    This is for payment gateway functionality
    */
    public function success() {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $AuthDesc = "";
            $MerchantId = "";
            $OrderId = "";
            $Amount = 0;
            $Checksum = 0;
            $veriChecksum = false;
            $pay_method = $this->paymentsetting_model->getActiveMethod();
            $Checksum = $this->input->post('Checksum');
            $MerchantId = $this->input->post('Merchant_Id');
            $OrderId = $this->input->post('Order_Id');
            $Amount = $this->input->post('Amount');
            $AuthDesc = $this->input->post('AuthDesc');
            $workingKey = $pay_method->salt;
            $rcvdString = $MerchantId . '|' . $OrderId . '|' . $Amount . '|' . $AuthDesc . '|' . $workingKey;
            $veriChecksum = $this->adler32->verifyChecksum($this->adler32->genchecksum($rcvdString), $Checksum);

                $payment_data = array(
                    'date' => date('Y-m-d'),
                    'student_id' => $params['student_id'],
                    'online_courses_id' => $params['courseid'],
                    'course_name' => $params['course_name'],
                    'actual_price' => $params['actual_amount'],
                    'paid_amount' => $params['total_amount'],
                    'payment_type' => 'Online',
                    'transaction_id' => $OrderId,
                    'note' => "Online course fees deposit through CCAvenue Txn ID: " . $OrderId . ", CCAvenue Ref ID: " . $nb_order_no,
                    'payment_mode' => 'CCAvenue',
                );
            $this->course_payment_model->add($payment_data);
            if(!empty($params['courseid'])) {
                $sender_details = array('courseid'=>$params['courseid'],'class' => $params['class'],  'class_section_id'=> $params['class_sections'], 'section'=> $params['section'], 'title' => $params['course_name'], 'price' => $params['total_amount'], 'discount' => $params['discount'], 'assign_teacher' => $params['staff'], 'paid_free' => $params['paid_free'], 'purchase_date' => date('Y-m-d'));         
                $this->course_mail_sms->purchasemail('online_course_purchase', $sender_details);              
            }
            $this->load->view('user/studentcourse/paymentsuccess');

        } else if ($veriChecksum == TRUE && $AuthDesc === "B") {
            $this->session->set_flashdata('message', 'Thank you for shopping with us.We will keep you posted regarding the status of your order through e-mail');
            redirect(base_url("students/online_course/Course_payment/paymentfailed"));
        } else if ($veriChecksum == TRUE && $AuthDesc === "N") {
            $this->session->set_flashdata('message', 'Thank you for shopping with us.However,the transaction has been declined');
            redirect(base_url("students/online_course/Course_payment/paymentfailed"));
        } else {
            $this->session->set_flashdata('message', 'Security Error. Illegal access detected');
            redirect(base_url("students/online_course/course_payment/paymentfailed"));
        }
    }

    public function cancel()
    {

    }
}