<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'third_party/stripe/init.php';

class Payment extends Admin_Controller
{

    public $payment_method;
    public $school_name;
    public $school_setting;
    public $setting;

    public function __construct()
    {
        parent::__construct();
        $this->payment_method     = $this->paymentsetting_model->get();
        $this->setting            = $this->setting_model->get();
        $this->std_fine           = 0;
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->load->library('Customlib');
    }

    public function index($student_fees_master_id, $fee_groups_feetype_id, $student_id)
    {

        $this->session->unset_userdata("params");

        if (!empty($this->payment_method)) {
            $data                           = array();
            $data['fee_groups_feetype_id']  = $fee_groups_feetype_id;
            $data['student_fees_master_id'] = $student_fees_master_id;
            $result                         = $this->studentfeemaster_model->studentDeposit($data);
            $amount_balance                 = 0;
            $amount                         = 0;
            $amount_fine                    = 0;
            $amount_discount                = 0;
            $amount_detail                  = json_decode($result->amount_detail);
            $totalamount_fine               =0;
            if (strtotime($result->due_date) < strtotime(date('Y-m-d'))) {
                $totalamount_fine = $result->fine_amount;
            }

            if (is_object($amount_detail)) {
                foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                    $amount          = $amount + $amount_detail_value->amount;
                    $amount_discount = $amount_discount + $amount_detail_value->amount_discount;
                    $amount_fine     = $amount_fine + $amount_detail_value->amount_fine;
                }
            }

            $amount_balance = $result->amount - ($amount + $amount_discount);
            if ($result->is_system) {

                $amount_balance = $result->student_fees_master_amount - ($amount + $amount_discount);
            }
            $totalamount_fine = $totalamount_fine - $amount_fine;

            $student_record = $this->student_model->get($student_id);
            $pay_method     = $this->paymentsetting_model->getActiveMethod();

            if ($pay_method->payment_type == "stripe") {
                if ($pay_method->api_secret_key == "" || $pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Stripe settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;

                    $page                = new stdClass();
                    $page->symbol        = $this->setting[0]['currency_symbol'];
                    $page->currency_name = $this->setting[0]['currency'];
                    $params              = array(
                        'key'                    => $pay_method->api_secret_key,
                        'api_publishable_key'    => $pay_method->api_publishable_key,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);
                    
                    redirect("gateway/stripe", 'refresh');
                }
            } else if ($pay_method->payment_type == "payu") {
                $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                $payment_details->fine_amount = $totalamount_fine;
                $page                         = new stdClass();
                $page->symbol                 = $this->setting[0]['currency_symbol'];
                $page->currency_name          = $this->setting[0]['currency'];

                $params = array(
                    'key'                    => $pay_method->api_secret_key,
                    'salt'                   => $pay_method->salt,
                    'invoice'                => $page,
                    'total'                  => $amount_balance,
                    'student_session_id'     => $student_record->student_session_id,
                    'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                    'email'                  => $student_record->email,
                    'guardian_phone'         => $student_record->guardian_phone,
                    'address'                => $student_record->permanent_address,
                    'student_fees_master_id' => $student_fees_master_id,
                    'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                    'student_id'             => $student_id,
                    'payment_detail'         => $payment_details,
                );

                $this->session->set_userdata("params", $params);
                redirect(base_url("gateway/payu"));
            } else if ($pay_method->payment_type == "paypal") {

                if ($pay_method->api_username == "" || $pay_method->api_password == "" || $pay_method->api_signature == "") {

                    $this->session->set_flashdata('error', 'Paypal settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;
                    $page                         = new stdClass();
                    $page->symbol                 = $this->setting[0]['currency_symbol'];
                    $page->currency_name          = $this->setting[0]['currency'];

                    $params = array(
                        'api_username'           => $pay_method->api_username,
                        'api_password'           => $pay_method->api_password,
                        'api_signature'          => $pay_method->api_signature,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'address'                => $student_record->permanent_address,
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);
                    redirect("gateway/paypal", 'refresh');
                }
            } else if ($pay_method->payment_type == "instamojo") {

                if ($pay_method->api_secret_key == "" || $pay_method->salt == "" || $pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Instamojo settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;
                    $page                         = new stdClass();
                    $page->symbol                 = $this->setting[0]['currency_symbol'];
                    $page->currency_name          = $this->setting[0]['currency'];

                    $params = array(
                        'api_secret_key'         => $pay_method->api_secret_key,
                        'salt'                   => $pay_method->salt,
                        'api_publishable_key'    => $pay_method->api_publishable_key,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'address'                => $student_record->permanent_address,
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);
                    redirect("gateway/Instamojo", 'refresh');
                }
            } else if ($pay_method->payment_type == "razorpay") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Razorpay settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;
                    $page                         = new stdClass();
                    $page->symbol                 = $this->setting[0]['currency_symbol'];
                    $page->currency_name          = $this->setting[0]['currency'];

                    $params = array(
                        'api_secret_key'         => $pay_method->api_secret_key,
                        'salt'                   => $pay_method->salt,
                        'api_publishable_key'    => $pay_method->api_publishable_key,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'address'                => $student_record->permanent_address,
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);
                    redirect("gateway/Razorpay", 'refresh');
                }
            } else if ($pay_method->payment_type == "paystack") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Paystack settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;
                    $page                         = new stdClass();
                    $page->symbol                 = $this->setting[0]['currency_symbol'];
                    $page->currency_name          = $this->setting[0]['currency'];

                    $params = array(
                        'api_secret_key'         => $pay_method->api_secret_key,
                        'salt'                   => $pay_method->salt,
                        'api_publishable_key'    => $pay_method->api_publishable_key,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'address'                => $student_record->permanent_address,
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);
                    redirect("gateway/Paystack", 'refresh');
                }
            } else if ($pay_method->payment_type == "paytm") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'paytm settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;
                    $page                         = new stdClass();
                    $page->symbol                 = $this->setting[0]['currency_symbol'];
                    $page->currency_name          = $this->setting[0]['currency'];

                    $params = array(
                        'api_secret_key'         => $pay_method->api_secret_key,
                        'salt'                   => $pay_method->salt,
                        'api_publishable_key'    => $pay_method->api_publishable_key,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'address'                => $student_record->permanent_address,
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);
                    redirect("gateway/Paytm", 'refresh');
                }
            } else if ($pay_method->payment_type == "midtrans") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'midtrans settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;
                    $page                         = new stdClass();
                    $page->symbol                 = $this->setting[0]['currency_symbol'];
                    $page->currency_name          = $this->setting[0]['currency'];

                    $params = array(
                        'api_secret_key'         => $pay_method->api_secret_key,
                        'salt'                   => $pay_method->salt,
                        'api_publishable_key'    => $pay_method->api_publishable_key,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'address'                => $student_record->permanent_address,
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);
                    redirect("gateway/midtrans", 'refresh');
                }
            } else if ($pay_method->payment_type == "pesapal") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'pesapal settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;
                    $page                         = new stdClass();
                    $page->symbol                 = $this->setting[0]['currency_symbol'];
                    $page->currency_name          = $this->setting[0]['currency'];

                    $params = array(
                        'api_secret_key'         => $pay_method->api_secret_key,
                        'salt'                   => $pay_method->salt,
                        'api_publishable_key'    => $pay_method->api_publishable_key,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'address'                => $student_record->permanent_address,
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);

                    redirect("gateway/pesapal", 'refresh');
                }
            } else if ($pay_method->payment_type == "flutterwave") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Flutterwave settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;
                    $page                         = new stdClass();
                    $page->symbol                 = $this->setting[0]['currency_symbol'];
                    $page->currency_name          = $this->setting[0]['currency'];

                    $params = array(
                        'api_secret_key'         => $pay_method->api_secret_key,
                        'salt'                   => $pay_method->salt,
                        'api_publishable_key'    => $pay_method->api_publishable_key,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'address'                => $student_record->permanent_address,
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);

                    redirect("gateway/flutterwave", 'refresh');
                }
            } else if ($pay_method->payment_type == "ipayafrica") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'iPayAfrica settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;
                    $page                         = new stdClass();
                    $page->symbol                 = $this->setting[0]['currency_symbol'];
                    $page->currency_name          = $this->setting[0]['currency'];

                    $params = array(
                        'api_secret_key'         => $pay_method->api_secret_key,
                        'salt'                   => $pay_method->salt,
                        'api_publishable_key'    => $pay_method->api_publishable_key,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'address'                => $student_record->permanent_address,
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);

                    redirect("gateway/ipayafrica", 'refresh');
                }
            } else if ($pay_method->payment_type == "jazzcash") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'iPayAfrica settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;
                    $page                         = new stdClass();
                    $page->symbol                 = $this->setting[0]['currency_symbol'];
                    $page->currency_name          = $this->setting[0]['currency'];
                    $this->std_fine               = $payment_details->fine_amount;
                    $params                       = array(
                        'api_secret_key'         => $pay_method->api_secret_key,
                        'salt'                   => $pay_method->salt,
                        'api_publishable_key'    => $pay_method->api_publishable_key,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'address'                => $student_record->permanent_address,
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);

                    redirect("gateway/jazzcash", 'refresh');
                }
            } else if ($pay_method->payment_type == "billplz") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Billplz settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;
                    $page                         = new stdClass();
                    $page->symbol                 = $this->setting[0]['currency_symbol'];
                    $page->currency_name          = $this->setting[0]['currency'];
                    $this->std_fine               = $payment_details->fine_amount;
                    $params                       = array(
                        'api_secret_key'         => $pay_method->api_secret_key,
                        'salt'                   => $pay_method->salt,
                        'api_publishable_key'    => $pay_method->api_publishable_key,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'address'                => $student_record->permanent_address,
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);

                    redirect("gateway/billplz", 'refresh');
                }
            } else if ($pay_method->payment_type == "ccavenue") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'CCAvenue settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;
                    $page                         = new stdClass();
                    $page->symbol                 = $this->setting[0]['currency_symbol'];
                    $page->currency_name          = $this->setting[0]['currency'];
                    $this->std_fine               = $payment_details->fine_amount;
                    $params                       = array(
                        'api_secret_key'         => $pay_method->api_secret_key,
                        'salt'                   => $pay_method->salt,
                        'api_publishable_key'    => $pay_method->api_publishable_key,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'address'                => $student_record->permanent_address,
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);

                    redirect("gateway/ccavenue", 'refresh');
                }
            } else if ($pay_method->payment_type == "sslcommerz") {

                if ($pay_method->api_password == "") {

                    $this->session->set_flashdata('error', 'Sslcommerz settings not available');
                    $this->load->view('payment/error');
                } else {
                    $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                    $payment_details->fine_amount = $totalamount_fine;
                    $page                         = new stdClass();
                    $page->symbol                 = $this->setting[0]['currency_symbol'];
                    $page->currency_name          = $this->setting[0]['currency'];
                    $this->std_fine               = $payment_details->fine_amount;
                    $params                       = array(
                        'api_secret_key'         => $pay_method->api_secret_key,
                        'salt'                   => $pay_method->salt,
                        'api_publishable_key'    => $pay_method->api_publishable_key,
                        'invoice'                => $page,
                        'total'                  => $amount_balance,
                        'student_session_id'     => $student_record->student_session_id,
                        'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                        'email'                  => $student_record->email,
                        'guardian_phone'         => $student_record->guardian_phone,
                        'address'                => $student_record->permanent_address,
                        'student_fees_master_id' => $student_fees_master_id,
                        'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                        'student_id'             => $student_id,
                        'payment_detail'         => $payment_details,
                    );

                    $this->session->set_userdata("params", $params);

                    redirect("gateway/sslcommerz", 'refresh');
                }
            } else {
                $this->session->set_flashdata('error', 'Oops! An error occurred with this payment, Please contact to administrator');
                $this->load->view('payment/error');
            }
        }
    }

    public function paymentfailed()
    {

        $data = array();
        $this->load->view('payment/paymentfailed', $data);
    }

    public function successinvoice($invoice_id, $sub_invoice_id)
    {
        $data                = array();
        $data['title']       = 'Invoice';
        $setting_result      = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $studentfee          = $this->studentfeemaster_model->getFeeByInvoice($invoice_id, $sub_invoice_id);

        $a                         = json_decode($studentfee->amount_detail);
        $record                    = $a->{$sub_invoice_id};
        $data['studentfee']        = $studentfee;
        $data['studentfee_detail'] = $record;

        $this->load->view('payment/invoice', $data);
    }

}
