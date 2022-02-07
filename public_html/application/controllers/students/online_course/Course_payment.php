<?php 
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
	
class Course_payment extends Student_Controller {

    public $pay_method;
    public $school_name;
    public $school_setting;
    public $setting;

    function __construct() {
        parent::__construct();

        $this->load->model(array('course_model','coursesection_model','courselesson_model','studentcourse_model','coursequiz_model','course_payment_model','courseofflinepayment_model','coursereport_model'));       
        $this->pay_method = $this->paymentsetting_model->getActiveMethod();
        $this->school_name = $this->customlib->getAppName();
        $this->school_setting = $this->setting_model->get();
        $this->setting = $this->setting_model->get();
        $this->result = $this->customlib->getLoggedInUserData();
    }

    /*
    This is used to call all payment gateway and also store payment data in session
    */
    public function payment()
    {
        $this->session->unset_userdata("course_amount");
        $courseid = $this->uri->segment(5);
        $userid = $this->result["student_id"];
        $studentdata = $this->student_model->get($userid);
        $contact_no = $studentdata["mobileno"];
        $email = $studentdata["email"];
        $name = $this->result["username"];
        $courseslist = $this->course_model->singlecourselist($courseid);
        $multipalsection   =   $this->course_model->multipalsection($courseid);
        $staff = $courseslist["staff_name"].' '.$courseslist["staff_surname"];  
        $discount = '';
        $price = '';
        if (!empty($courseslist['discount'])) {
            $discount = $courseslist['price'] - (($courseslist['price'] * $courseslist['discount']) / 100);
        }
        if (($courseslist["free_course"] == 1) && (empty($courseslist["price"]))) {
            $price      = 'Free';           
        } elseif (($courseslist["free_course"] == 1) && (!empty($courseslist["price"]))) {
            if($courseslist['price'] > '0.00'){
                $courseprice = $courseslist['price'];
            }else{
                $courseprice = '';
            }
            $price      = $courseprice;           
        } elseif (!empty($courseslist["price"]) && (!empty($courseslist["discount"]))) {
            $discount   = number_format((float) $discount, 2, '.', '');
            if($courseslist['price'] > '0.00'){
                $courseprice = $courseslist['price'];
            }else{
                $courseprice = '';
            }
            $price      = $discount ;
        } else {
            $price      = $courseslist['price']  ; 
        }

        $section = "";
        $store_section = array();
        foreach ($multipalsection as $multipalsection_value) {
            if (!in_array($multipalsection_value['section'], $store_section)) {
                $store_section[] = $multipalsection_value['section'];
                $section .= $multipalsection_value['section'] . ", ";
            }
        }

        $paymentdata = array(
            'actual_amount' => $courseslist['price'],
            'discount' => $courseslist['discount'],
            'total_amount' => $price,
            'courseid' => $courseid,
            'course_name' => $courseslist['title'],
            'description' => $courseslist['description'],
            'course_thumbnail' => $courseslist['course_thumbnail'],
            'paid_free' => $courseslist['free_course'],
            'student_id' => $userid,
            'contact_no' => $contact_no,
            'email' => $email,
            'name' => $name,
            'section' => $section,
            'class' => $courseslist['class'],
            'class_sections' => $courseslist['class_sections'],
            'staff' => $staff,
            'address' => '',
        );
        $this->session->set_userdata('course_amount', $paymentdata);
        $data = array();
        if (!empty($this->pay_method)) {
            $course_amount = $this->session->userdata('course_amount');
            $id             = $course_amount['id'];
            $total_amount   = $course_amount['total_amount'];
            if ($this->pay_method->payment_type == "payu") {
                redirect(base_url("students/online_course/payu"));
            } elseif ($this->pay_method->payment_type == "stripe") {
                redirect(base_url("students/online_course/stripe"));
            } elseif ($this->pay_method->payment_type == "ccavenue") {
                redirect(base_url("students/online_course/ccavenue"));
            } elseif ($this->pay_method->payment_type == "paypal") {
                redirect(base_url("students/online_course/paypal"));
            } elseif ($this->pay_method->payment_type == "instamojo") {
                redirect(base_url("students/online_course/instamojo"));
            } elseif ($this->pay_method->payment_type == "paytm") {
                redirect(base_url("students/online_course/paytm"));
            } elseif ($this->pay_method->payment_type == "razorpay") {
                redirect(base_url("students/online_course/razorpay"));
            } elseif ($this->pay_method->payment_type == "paystack") {
                redirect(base_url("students/online_course/paystack"));
            } elseif ($this->pay_method->payment_type == "midtrans") {
                redirect(base_url("students/online_course/midtrans"));
            }elseif ($this->pay_method->payment_type == "ipayafrica") {
                redirect(base_url("students/online_course/ipayafrica"));
            }elseif ($this->pay_method->payment_type == "jazzcash") {
                redirect(base_url("students/online_course/jazzcash"));
            }elseif ($this->pay_method->payment_type == "pesapal") {
                redirect(base_url("students/online_course/pesapal"));
            }elseif ($this->pay_method->payment_type == "flutterwave") {
                redirect(base_url("students/online_course/flutterwave"));
            }elseif ($this->pay_method->payment_type == "billplz") {
                redirect(base_url("students/online_course/billplz"));
            }elseif ($this->pay_method->payment_type == "sslcommerz") {
                redirect(base_url("students/online_course/sslcommerz"));
            }
        }
    }

	/*
    This is used to show failed payment status
    */
    public function paymentfailed() {
        $this->session->set_userdata('top_menu', 'Fees');
        $data['title'] = 'Invoice';
        $data['message'] = "dfsdfds";
        $setting_result = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $this->load->view('layout/student/header', $data);
        $this->load->view('user/paymentfailed', $data);
        $this->load->view('layout/student/footer', $data);
    }
}