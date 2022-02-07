<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webservice_model extends CI_Model {


   public function __construct() {
        parent::__construct();
        $CI =& get_instance();
        $CI->load->model('setting_model');
        $CI->load->model('paymentsetting_model');
        $this->current_session = $this->setting_model->getCurrentSession();
       
    }

 public function getappdetails() {
    $this->db->select('sch_settings.mobile_api_url as url,sch_settings.app_primary_color_code,sch_settings.app_secondary_color_code,sch_settings.image as app_logo,languages.short_code as lang_code')->from('sch_settings');
    $this->db->join('languages', 'languages.id = sch_settings.lang_id', "left");
    $q = $this->db->get();
    $result = $q->row_array();
    return $result;
    }



//================================Patient_model=============================================

    public function getPatientProfile($user_id) {
 	$this->db->select('patients.*')->from('patients');
    $this->db->where('patients.id', $user_id);
	$q = $this->db->get();
    
		if($q->num_rows() == 0){
		  	return array('success' => 0, 'status' => 401,'errorMsg' => 'Profile Not Found!');
		} else {
		 	$data['success'] = 1;
			$data['data'] = $q->result_array();
		  	return $data;
		}
  	}

     public function getpatientDetails($id)
    {
        $this->db->select('patients.*')->from('patients')->where('patients.id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

  	public function getDataAppoint($id) {
        $query = $this->db->where('patients.id', $id)->get('patients');
        return $query->row_array();
    }

    public function getUser($id) {
        $query = $this->db->where('users.user_id', $id)->where('users.is_active', 'yes')->get('users');
        return $query->row_array();
    }

    public function getUserLoginDetails($patient_id) {

        $sql = "SELECT users.* FROM users WHERE user_id =" . $patient_id . " and role = 'patient'";
        $query = $this->db->query($sql);
        return $query->row_array();
    }

  	 public function getDetails($id, $opdid = '') {
         $this->db->select('patients.*,opd_details.appointment_date,opd_details.case_type,opd_details.id as opdid,opd_details.casualty,opd_details.cons_doctor,opd_details.generated_by as generated_id,opd_details.refference,opd_details.opd_no,opd_details.known_allergies as opdknown_allergies,opd_details.amount as amount,opd_details.height,opd_details.weight,opd_details.bp,opd_details.symptoms,opd_details.tax,opd_details.payment_mode,opd_details.note_remark,opd_details.discharged,opd_details.pulse,opd_details.temperature,opd_details.respiration,opd_details.opd_no,opd_details.live_consult,opd_billing.status,opd_billing.gross_total,opd_billing.discount,opd_billing.date as discharge_date,opd_billing.tax,opd_billing.net_amount,opd_billing.total_amount,opd_billing.other_charge,opd_billing.generated_by,opd_billing.id as bill_id,organisation.organisation_name,organisation.id as orgid,staff.id as staff_id,staff.name,staff.surname,consult_charges.standard_charge,opd_patient_charges.apply_charge,visit_details.amount as visitamount,visit_details.id as visitid')->from('patients');
        $this->db->join('opd_details', 'patients.id = opd_details.patient_id', "left");
        $this->db->join('staff', 'staff.id = opd_details.cons_doctor', "left");
        $this->db->join('organisation', 'organisation.id = patients.organisation', "left");
        $this->db->join('opd_billing', 'opd_details.id = opd_billing.opd_id', "left");
        $this->db->join('consult_charges', 'consult_charges.doctor=opd_details.cons_doctor', 'left');
        $this->db->join('opd_patient_charges', 'opd_details.id=opd_patient_charges.opd_id', 'left');
        $this->db->join('visit_details', 'visit_details.opd_id=opd_details.id', 'left');
        $this->db->where('patients.is_active', 'yes');
        $this->db->where('patients.id', $id);
        if ($opdid != null) {
            $this->db->where('opd_details.id', $opdid);
        }
        $query  = $this->db->get();
        $result = $query->row_array();
        return $result;
    }

    public function getDetailsbilling($id, $opdid = '') {
         $this->db->select('patients.*,opd_details.appointment_date,opd_details.case_type,opd_details.id as opdid,opd_details.casualty,opd_details.cons_doctor,opd_details.generated_by as generated_id,opd_details.refference,opd_details.opd_no,opd_details.known_allergies as opdknown_allergies,IF(opd_details.amount IS NULL,0,opd_details.amount) as opdamount,opd_details.height,opd_details.weight,opd_details.bp,opd_details.symptoms,opd_details.tax,opd_details.payment_mode,opd_details.note_remark,opd_details.discharged,opd_details.pulse,opd_details.temperature,opd_details.respiration,opd_details.opd_no,opd_details.live_consult,IF(opd_billing.status IS NULL,"",opd_billing.status) as status,IF(opd_billing.gross_total IS NULL,0,opd_billing.gross_total) as gross_total,IF(opd_billing.discount IS NULL,0,opd_billing.discount) as discount,opd_billing.date as discharge_date,IF(opd_billing.tax IS NULL,0,opd_billing.tax) as tax,IF(opd_billing.net_amount IS NULL,0,opd_billing.net_amount) as net_amount,opd_billing.total_amount,IF(opd_billing.other_charge IS NULL,0,opd_billing.other_charge) as other_charge,opd_billing.generated_by,opd_billing.id as bill_id,organisation.organisation_name,organisation.id as orgid,staff.id as staff_id,staff.name,staff.surname,consult_charges.standard_charge,opd_patient_charges.apply_charge,IF(visit_details.amount IS NULL,0,visit_details.amount) as visitamount,visit_details.id as visitid')->from('patients');
        $this->db->join('opd_details', 'patients.id = opd_details.patient_id', "left");
        $this->db->join('staff', 'staff.id = opd_details.cons_doctor', "left");
        $this->db->join('organisation', 'organisation.id = patients.organisation', "left");
        $this->db->join('opd_billing', 'opd_details.id = opd_billing.opd_id', "left");
        $this->db->join('consult_charges', 'consult_charges.doctor=opd_details.cons_doctor', 'left');
        $this->db->join('opd_patient_charges', 'opd_details.id=opd_patient_charges.opd_id', 'left');
        $this->db->join('visit_details', 'visit_details.opd_id=opd_details.id', 'left');
        $this->db->where('patients.is_active', 'yes');
        $this->db->where('patients.id', $id);
        if ($opdid != null) {
            $this->db->where('opd_details.id', $opdid);
        }
        $query  = $this->db->get();
        $result = $query->row_array();
        if (!empty($result)) {
              $result['consultant_charges'] = $result['opdamount'] + $result['visitamount'];
              $charge = $this->getOPDchargesTotal($id, $opdid);
              $result['total_charges'] = $charge['apply_charge'];
              $payment = $this->getOPDPaidTotal($id, $opdid);
              $result['total_payment'] = $payment['paid_amount'];
              if ($result['status'] !='paid') {
                $result['gross_total_amount'] = $charge['apply_charge'] - $payment['paid_amount'];
                $result['net_payable_amount'] = $charge['apply_charge'] - $payment['paid_amount']; 
              }
         }
        return $result;
    }

    public function getOPDchargesTotal($id, $opdid) {
        $query = $this->db->select("IFNULL(sum(apply_charge), '0') as apply_charge")->where("opd_patient_charges.patient_id", $id)->where("opd_patient_charges.opd_id", $opdid)->get("opd_patient_charges");
        return $query->row_array();
    }

    public function getOPDPaidTotal($id, $opdid) {
        $query = $this->db->select("IFNULL(sum(paid_amount), '0') as paid_amount")->where("opd_payment.patient_id", $id)->where("opd_payment.opd_id", $opdid)->get("opd_payment");
        return $query->row_array();
    }

    public function getopdSummaryDetails($patientid, $ipdid) {
        $query = $this->db->select('discharged_summary_opd.*')
                ->where("discharged_summary_opd.opd_id", $ipdid)
                ->get("discharged_summary_opd");
        $result = $query->row_array();
        if (!empty($result)) {
            return $result;
        } else {
             return $result = ""; 
        }
        
    }

//===================== OPD Details =================================================

      public function getOPDDetails($id, $opdid = null) {
        if (!empty($opdid)) {
            $this->db->where("opd_details.id", $opdid);

        }
        $this->db->select('opd_details.*,opd_details.id as visitid,patients.organisation,patients.old_patient,staff.id as staff_id,staff.name,staff.surname,consult_charges.standard_charge')->from('opd_details');
        $this->db->join('staff', 'staff.id = opd_details.cons_doctor', "inner");
        $this->db->join('patients', 'patients.id = opd_details.patient_id', "inner");
        $this->db->join('consult_charges', 'consult_charges.doctor=opd_details.cons_doctor', 'left');
        $this->db->where('opd_details.patient_id', $id);
        $this->db->order_by('opd_details.id', 'desc');
        $query = $this->db->get();
       
        if (!empty($opdid)) {
            return $query->result_array();

        } else {

            $result = $query->result_array();

            $i = 0;
            foreach ($result as $key => $value) {

                $result[$key]['header_note'] = strip_tags(str_replace(PHP_EOL,'', $value['header_note']));
                $result[$key]['footer_note'] = strip_tags(str_replace(PHP_EOL,'', $value['footer_note']));
                $opd_id = $value["id"];
                $check = $this->db->where("opd_id", $opd_id)->where("visit_id", 0)->get('prescription');
                if ($check->num_rows() > 0) {
                    $result[$i]['prescription'] = 'yes';
                } else {
                    $result[$i]['prescription'] = 'no';
                   
                }
                $i++;
            }
           
            return $result;
        }
    }

        public function getVisitRechekup($id,$visitid){
            $sql = "select opd.*,opd_details.opd_no,opd_details.cons_doctor,opd_details.casualty,staff.name,staff.surname,patients.id as pid,patients.patient_name,patients.patient_unique_id,patients.guardian_name,patients.address,patients.admission_date,patients.gender,patients.mobileno,patients.age,patients.month from (SELECT id, patient_id,appointment_date,symptoms,refference,header_note,footer_note,'0' visit_id FROM opd_details UNION ALL SELECT opd_id,patient_id,appointment_date,symptoms,refference,header_note,footer_note,id as visit_id FROM visit_details) AS opd LEFT JOIN patients ON opd.patient_id = patients.id LEFT JOIN opd_details ON opd_details.id = opd.id LEFT JOIN staff ON opd_details.cons_doctor = staff.id where opd.id = ".$visitid." and opd.patient_id =".$id."  ORDER BY visit_id ASC"  ;
            $query = $this->db->query($sql);
		      $result = $query->result_array();
				$i = 0;
            foreach ($result as $key => $value) {
                $result[$key]['header_note'] = strip_tags(str_replace(PHP_EOL,'', $value['header_note']));
                $result[$key]['footer_note'] = strip_tags(str_replace(PHP_EOL,'', $value['footer_note']));
                $opd_id = $value["id"];
				$visit_id = $value["visit_id"];
                $check = $this->db->where("opd_id", $opd_id)->where("visit_id", $visit_id)->get('prescription');
                if ($check->num_rows() > 0) {
                    $result[$i]['prescription'] = 'yes';
                } else {
                    $result[$i]['prescription'] = 'no';
                   
                }
                $i++;
            }
			 return $result;
			
        }

     public function getVisitDetails($id, $visitid) {
        $query = $this->db->select('opd_details.*,staff.name,staff.surname')
                ->join('patients', 'opd_details.patient_id = patients.id')
                ->join('staff', 'opd_details.cons_doctor = staff.id')
                ->where(array('opd_details.patient_id' => $id, 'opd_details.id' => $visitid))
                ->get('opd_details');
        return $query->result_array();  
    }

     public function getVisitDetailsByOPD($id, $visitid) {
        $query = $this->db->select('visit_details.*,opd_details.id as opdid, staff.name,staff.surname')
                ->join('patients', 'visit_details.patient_id = patients.id')
                ->join('opd_details', 'visit_details.opd_no = opd_details.opd_no')
                ->join('staff', 'opd_details.cons_doctor = staff.id')
                ->where(array('opd_details.patient_id' => $id, 'visit_details.opd_id' => $visitid))
                ->get('visit_details');
        // return $query->result_array();    
        $result = $query->result_array();

        $i = 0;
        foreach ($result as $key => $value) {
            $opd_id = $value["id"];
            $check = $this->db->where("visit_id", $opd_id)->get('prescription');
            if ($check->num_rows() > 0) {
                $result[$i]['prescription'] = 'yes';
            } else {
                $result[$i]['prescription'] = 'no';
               
            }
            $i++;
        }
        return $result;
    }
	
//===============================OPD  Prescription Details==================================================
	
	public function getopdprescription($id, $visitid = '') {
        if (!empty($visitid)) {
            $this->db->where("prescription.visit_id", $visitid);
        } else {
            $this->db->where("prescription.visit_id", 0);
        }
        $query = $this->db->select('prescription.*,medicine_category.medicine_category,staff.id as staff_id')->join("opd_details", "prescription.opd_id = opd_details.id")->join("medicine_category", "prescription.medicine_category_id = medicine_category.id")->join("patients", "patients.id = opd_details.patient_id")->join("staff", "staff.id = opd_details.cons_doctor")->where("prescription.opd_id", $id)->get("prescription");

        $result = $query->result_array();
        return $result;
    }

	 public function getpres($id) {
        $query = $this->db->select("opd_details.*,patients.*,staff.name,staff.surname,staff.local_address,prescription.opd_id,prescription.id as presid")->join("opd_details", "prescription.opd_id = opd_details.id")->join("patients", "patients.id = opd_details.patient_id")->join("staff", "staff.id = opd_details.cons_doctor")->where("prescription.opd_id", $id)->get("prescription");
        return $query->row_array();
    }
	
     public function getpresvisit($id)
    {
        $query = $this->db->select("visit_details.*,patients.*,staff.name,staff.surname,staff.local_address,prescription.opd_id,prescription.id as presid")->join("visit_details", "prescription.visit_id = visit_details.id")->join("patients", "patients.id = visit_details.patient_id")->join("staff", "staff.id = visit_details.cons_doctor")->where("prescription.visit_id", $id)->get("prescription");
        return $query->row_array();
    }
//===============================IPD Details==================================================

     public function getIpdDetails($id, $ipdid='', $active = 'yes') {
        $this->db->select('patients.*,ipd_details.patient_id,ipd_details.date,ipd_details.case_type,ipd_details.ipd_no,ipd_details.id as ipdid,ipd_details.casualty,ipd_details.height,ipd_details.weight,ipd_details.bp,ipd_details.cons_doctor,ipd_details.refference,ipd_details.known_allergies,ipd_details.amount,ipd_details.credit_limit as ipdcredit_limit,ipd_details.symptoms,ipd_details.discharged as ipd_discharge,ipd_details.tax,ipd_details.bed,ipd_details.bed_group_id,ipd_details.note as ipdnote,ipd_details.bed,ipd_details.bed_group_id,ipd_details.payment_mode,IF(ipd_billing.status IS NULL,"",ipd_billing.status) as payment_status,IF(ipd_billing.gross_total IS NULL,0,ipd_billing.gross_total) as gross_total,IF(ipd_billing.discount IS NULL,0,ipd_billing.discount) as discount ,IF(ipd_billing.date IS NULL,"",ipd_billing.date) as discharge_date,IF(ipd_billing.tax IS NULL,0,ipd_billing.tax) as tax, IF(ipd_billing.net_amount IS NULL,0,ipd_billing.net_amount) as net_amount,IF(ipd_billing.total_amount IS NULL,0,ipd_billing.total_amount) as total_amount,IF(ipd_billing.other_charge IS NULL,0,ipd_billing.other_charge) as other_charge,ipd_billing.generated_by,ipd_billing.id as bill_id,staff.id as staff_id,staff.name,staff.surname,organisation.organisation_name,bed.name as bed_name,bed.id as bed_id,bed_group.name as bedgroup_name,floor.name as floor_name')->from('patients');
        $this->db->join('ipd_details', 'patients.id = ipd_details.patient_id', "left");
        $this->db->join('ipd_billing', 'ipd_details.id = ipd_billing.ipd_id', "left");
        $this->db->join('staff', 'staff.id = ipd_details.cons_doctor', "inner");
        $this->db->join('organisation', 'organisation.id = patients.organisation', "left");
        $this->db->join('bed', 'ipd_details.bed = bed.id', "left");
        $this->db->join('bed_group', 'ipd_details.bed_group_id = bed_group.id', "left");
        $this->db->join('floor', 'floor.id = bed_group.floor', "left");
        $this->db->where('patients.is_active', $active);
        $this->db->where('patients.id', $id);
        $this->db->where('ipd_details.id', $ipdid);
        $query = $this->db->get();
        return $query->row_array();
    }  

    public function getBillInfo($id) {
        $query = $this->db->select('staff.name,staff.surname,staff.employee_id,ipd_billing.date as discharge_date')
                ->join('ipd_billing', 'staff.id = ipd_billing.generated_by')
                ->where('ipd_billing.patient_id', $id)
                ->get('staff');
        $result = $query->result_array();
        return $result;
    }

    public function getBilldetails($id,$ipdid) {
        $query = $this->db->select('ipd_billing.*')
                ->where('ipd_billing.ipd_id', $ipdid)
                ->get('ipd_billing');
        $result = $query->result_array();
        return $result;
    }

     function getPatientConsultant($id, $ipdid) {
        $query = $this->db->select('consultant_register.*,staff.name,staff.surname')->join('staff', 'staff.id = consultant_register.cons_doctor', "inner")->where("patient_id", $id)->where("ipd_id", $ipdid)->get("consultant_register");
        return $query->result_array();
    }

    public function getIpdno($ipdno) {
        $this->db->select('ipd_details.*')->from('ipd_details');
        $this->db->where('ipd_details.ipd_no', $ipdno);
        $query = $this->db->get();
        $result = $query->row_array();
        return $result;
    }
	
	public function getopdno($opdno) {
        $this->db->select('opd_details.*')->from('opd_details');
        $this->db->where('opd_details.opd_no', $opdno);
        $query = $this->db->get();
        $result = $query->row_array();
        return $result;
    }

    public function patientipddetails($patient_id) {
        $this->db->select('patients.id,patients.patient_name,patients.gender,patients.mobileno,IF(patients.credit_limit IS NULL ,0,patients.credit_limit) as credit_limit,bed.name as bed_name,bed_group.name as bedgroup_name, floor.name as floor_name,ipd_details.date,ipd_details.id as ipdid,ipd_details.ipd_no,ipd_details.discharged,staff.name,staff.surname
              ')->from('patients');
        $this->db->join('ipd_details', 'patients.id = ipd_details.patient_id', "inner");
        $this->db->join('staff', 'staff.id = ipd_details.cons_doctor', "inner");
        $this->db->join('bed', 'ipd_details.bed = bed.id', "left");
        $this->db->join('bed_group', 'ipd_details.bed_group_id = bed_group.id', "left");
        $this->db->join('floor', 'floor.id = bed_group.floor', "left");
        $this->db->where('patients.id', $patient_id);
        $this->db->where('ipd_details.discharged', "yes");
        $this->db->order_by('ipd_details.id', "desc");

        $query = $this->db->get();
        return $query->result_array();
    }

	public function patientipddetailslist($patient_id) {
        
        $this->db->select('patients.id,patients.patient_name,patients.gender,patients.mobileno,IF(ipd_details.credit_limit IS NULL ,0,ipd_details.credit_limit) as ipdcredit_limit,bed.name as bed_name,bed_group.name as bedgroup_name, floor.name as floor_name,ipd_details.date,ipd_details.id as ipdid,ipd_details.id as ipd_no,ipd_details.discharged,staff.name,staff.surname
              ')->from('patients');
        $this->db->join('ipd_details', 'patients.id = ipd_details.patient_id', "inner");
        $this->db->join('staff', 'staff.id = ipd_details.cons_doctor', "inner");
        $this->db->join('bed', 'ipd_details.bed = bed.id', "left");
        $this->db->join('bed_group', 'ipd_details.bed_group_id = bed_group.id', "left");
        $this->db->join('floor', 'floor.id = bed_group.floor', "left");
        $this->db->where('patients.id', $patient_id);
        $this->db->order_by('ipd_details.id', "desc");
        $query = $this->db->get();

        return $query->result_array();
    }

//===========================organisation TPA ==================================================

  public function get($id = null) {
        $this->db->select()->from('organisation');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id', 'desc');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

//===========================Diagnosis_model=======================================================
     
     function getDiagnosisDetails($id) {

        $query1 = $this->db->select('diagnosis.*,diagnosis.id as diagnosis')
                ->join('patients', 'patients.id = diagnosis.patient_id', "inner")
                ->where("patient_id", $id)
                ->get("diagnosis");
        $result1 = $query1->result_array();

        $query2 = $this->db->select('pathology_report.reporting_date as report_date,pathology_report.id,pathology_report.patient_id as patient_id,pathology_report.pathology_report as document,pathology.test_name as report_type,pathology_report.description')
                ->join('pathology', 'pathology.id = pathology_report.pathology_id', "inner")
                ->join('patients', 'patients.id = pathology_report.patient_id', "inner")
                ->where("pathology_report.patient_id", $id)
                ->get("pathology_report");
        $result2 = $query2->result_array();
        $query3 = $this->db->select('radiology_report.reporting_date as report_date,radiology_report.id,radiology_report.patient_id as patient_id,IF(`radiology_report`.`radiology_report` IS NULL,"",`radiology_report`.`radiology_report`) as document,radio.test_name as report_type,radiology_report.description')
                ->join('radio', 'radio.id = radiology_report.radiology_id', "inner")
                ->join('patients', 'patients.id = radiology_report.patient_id', "inner")
                ->where("radiology_report.patient_id", $id)
                ->get("radiology_report");
        $result3 = $query3->result_array();
        return array_merge($result1, $result2, $result3);
    }

//======================================Timeline_model===============================================

    public function getPatientTimeline($id, $status) {
        if (!empty($status)) {

            $this->db->where("status", $status);
        }
        $query = $this->db->where("patient_id", $id)->order_by("timeline_date", "desc")->get("patient_timeline");
        return $query->result_array();
    }

//==============================Prescription_model=================================================
        public function getpresipd($id)
        {
            $query = $this->db->select("ipd_details.*,patients.*,staff.name,staff.surname,staff.local_address,ipd_prescription_basic.ipd_id,ipd_prescription_basic.id as presid,ipd_prescription_basic.date as presdate,ipd_prescription_basic.header_note,ipd_prescription_basic.footer_note")->join("ipd_details", "ipd_prescription_basic.ipd_id = ipd_details.id")->join("patients", "patients.id = ipd_details.patient_id")->join("staff", "staff.id = ipd_details.cons_doctor")->where("ipd_prescription_basic.id", $id)->get("ipd_prescription_basic");
            return $query->row_array();
        }

        public function getipdprescriptionbyid($ipdid)
        {

            $query = $this->db->select('ipd_prescription_details.*,medicine_category.medicine_category')
                ->join("ipd_prescription_basic", "ipd_prescription_basic.id = ipd_prescription_details.basic_id","LEFT")
                ->join("ipd_details", "ipd_prescription_basic.ipd_id = ipd_details.id", "LEFT")
                ->join("medicine_category", "ipd_prescription_details.medicine_category_id = medicine_category.id", "LEFT")
                ->join("patients", "patients.id = ipd_details.patient_id", "LEFT")
                ->join("staff", "staff.id = ipd_details.cons_doctor", "LEFT")
                ->join("medicine_dosage", "medicine_dosage.id=ipd_prescription_details.dosage", "LEFT")
                ->where("ipd_prescription_details.basic_id", $ipdid)
                ->get("ipd_prescription_details");
            $result = $query->result_array();
            return $result;
        }

      public function getPatientPrescription($id) {
        $query = $this->db->join("opd_details", "prescription.opd_id = opd_details.id")->where("prescription.opd_id", $id)->get("prescription");
        $result = $query->result_array();
             $i = 0;
            foreach ($result as $key => $value) {
              
                $result[$key]['header_note'] = strip_tags(str_replace(PHP_EOL,'', $value['header_note']));
                $result[$key]['footer_note'] = strip_tags(str_replace(PHP_EOL,'', $value['footer_note']));
                $i++;
            }
            return $result;
        }

     public function getIpdPrescription($ipdid,$ipdno)
    {
        $query = $this->db->select('ipd_prescription_basic.*')
            ->join('ipd_prescription_details', 'ipd_prescription_basic.id = ipd_prescription_details.basic_id')
            ->where("ipd_prescription_basic.ipd_id", $ipdid)
            ->group_by("ipd_prescription_basic.id")
            ->get('ipd_prescription_basic');
        $result = $query->result_array();
        $i = 0;
        foreach ($result as $key => $value) {
          
             $result[$key]['ipd_id'] = strip_tags(str_replace(PHP_EOL,'', $ipdno));
             $result[$key]['header_note'] = strip_tags(str_replace(PHP_EOL,'', $value['header_note']));
             $result[$key]['footer_note'] = strip_tags(str_replace(PHP_EOL,'', $value['footer_note']));
             $i++;
        }
        return $result;
    }

   
//=====================================Payment_model============================================================

    public function getSummaryDetails($patientid, $ipdid) {
        $query = $this->db->select('discharged_summary.*')
                ->where("discharged_summary.ipd_id", $ipdid)
                ->get("discharged_summary");
        $result = $query->row_array();
        if (!empty($result)) {
            return $result;
        } else {
             return $result = ""; 
        }
        
    }
  
//=====================================Payment_model============================================================

    public function opdPaymentDetails($id, $visitid) {
        $query = $this->db->select('opd_payment.*,patients.id as pid,patients.note as pnote')
                ->join("patients", "patients.id = opd_payment.patient_id")->where("opd_payment.patient_id", $id)->where("opd_payment.opd_id", $visitid)
                ->get("opd_payment");
        return $query->result_array();
    }

    public function paymentDetails($id, $ipdid) {
        $query = $this->db->select('payment.*,patients.id as pid,patients.note as pnote')
                ->join("patients", "patients.id = payment.patient_id","left")->where("payment.patient_id", $id)->where("payment.ipd_id", $ipdid)
                ->get("payment");
        return $query->result_array();
    }

    public function getPaidTotal($id, $ipdid) {
        $query = $this->db->select("IFNULL(sum(paid_amount), '0') as paid_amount")->where("payment.patient_id", $id)->where("payment.ipd_id", $ipdid)->get("payment");
        return $query->row_array();
    }


     public function getBalanceTotal($id, $ipdid) {
        $query = $this->db->select("IFNULL(sum(balance_amount),'0') as balance_amount")->where("payment.patient_id", $id)->where("payment.ipd_id", $ipdid)->get("payment");
        return $query->result_array();
    }

     public function getPaymentipd($patient_id, $ipdid = '') {
        $query = $this->db->select("IFNULL(sum(paid_amount),'0') as payment")->where("patient_id", $patient_id)->where("ipd_id", $ipdid)->get("payment");
        return $query->row_array();
    }

    public function getPayment($patient_id, $ipdid = '') {
        $query = $this->db->select("IFNULL(sum(paid_amount),'0') as payment")->where("patient_id", $patient_id)->where("ipd_id", $ipdid)->get("payment");
        return $query->row_array();
    }

    public function getbilling($patient_id, $ipdid = '') {
        $query = $this->db->select("IFNULL(sum(net_amount),'0') as billamount,IFNULL(sum(other_charge),'0') as othercharge")->where("patient_id", $patient_id)->where("ipd_id", $ipdid)->get("ipd_billing");
        return $query->row_array();
    }
    
    public function addPayment($data) {
        $this->db->insert("payment", $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function paymentByID($id) {
        $query = $this->db->select('payment.*,patients.id as pid,patients.note as pnote')
                ->join("patients", "patients.id = payment.patient_id")->where("payment.id", $id)
                ->get("payment");
        return $query->row();
    }

    
//======================================Charges_model==========================================

	public function getOPDCharges($id, $visitid) {
        $query = $this->db->select('opd_patient_charges.*,patients.id as pid,charges.charge_type,charges.charge_category,charges.standard_charge,organisations_charges.id as oid,IF(organisations_charges.org_charge IS NULL,"",organisations_charges.org_charge) as tpa_charge')
                ->join('patients', 'opd_patient_charges.patient_id = patients.id', 'inner')
                ->join('charges', 'opd_patient_charges.charge_id = charges.id', 'inner')
                ->join('organisations_charges', 'opd_patient_charges.org_charge_id = organisations_charges.id', 'left')
                ->where('opd_patient_charges.patient_id', $id)
                ->where('opd_patient_charges.opd_id', $visitid)
                ->get('opd_patient_charges');
        return $query->result_array();
    }

    public function getChargesipd($patient_id, $ipdid = '') {
      
        $query = $this->db->select('patient_charges.*,charges.charge_type,charges.charge_category,charges.standard_charge,IF(`organisations_charges`.`org_charge` IS NULL,0,`organisations_charges`.`org_charge`) as tpa_charge')
                ->join('patients', 'patient_charges.patient_id = patients.id', 'LEFT')
                ->join('charges', 'patient_charges.charge_id = charges.id', 'LEFT')
                ->join('organisations_charges', 'patient_charges.org_charge_id = organisations_charges.id', 'LEFT')
                ->where('patient_charges.patient_id', $patient_id)
                ->where('patient_charges.ipd_id', $ipdid)
                ->get('patient_charges');
        return $query->result_array();
    }

    public function getCharges($patient_id, $ipdid = '') {
        $query = $this->db->select("IFNULL(sum(apply_charge),'0') as charge")->where("patient_id", $patient_id)->where("ipd_id", $ipdid)->get("patient_charges");
        return $query->row_array();
    }
	
    public function getTotalCharges($patient_id, $ipdid = '') {
        $query = $this->db->select("IFNULL(sum(apply_charge),'0') as charge")->where("patient_id", $patient_id)->where("ipd_id", $ipdid)->get("patient_charges");
        return $query->row_array();

    }    
//=========================Pharmacy Model==============================================================

    public function getBillDetailsPharma_old($id) {
        $this->db->select('pharmacy_bill_basic.*');
        $this->db->where('pharmacy_bill_basic.patient_id', $id);
		$this->db->order_by("pharmacy_bill_basic.id", "desc");
        $query = $this->db->get('pharmacy_bill_basic');				
        $result = $query->result_array();     
        return $result ;
    }
    
    public function getBillDetailsPharma($id) {
        $i                         = 1;
        $custom_fields             = $this->customfield_model->getcustomfields('pharmacy');

        $custom_field_column_array = array();

        $field_var_array = array();
        
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, 'table_custom_' . $i . '.field_value as ' . $custom_fields_value['name']);
                $this->db->join('custom_field_values as ' . $tb_counter, 'pharmacy_bill_basic.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value['id'], "left");
                $i++;
            }
        }


        $field_variable      = (empty($field_var_array)) ? "" : "," . implode(',', $field_var_array);
        $custom_field_column = (empty($custom_field_column_array)) ? "" : "," . implode(',', $custom_field_column_array);
        $this->db->select('pharmacy_bill_basic.*,IFNULL((select sum(amount) as amount_paid from transactions WHERE transactions.pharmacy_bill_basic_id =pharmacy_bill_basic.id and transactions.type="payment" ),0) as paid_amount, IFNULL((select sum(amount) as refund from transactions WHERE transactions.pharmacy_bill_basic_id =pharmacy_bill_basic.id and transactions.type="refund" ),0) as refund_amount,patients.patient_name,patients.id as pid' . $field_variable);
        $this->db->join('patients', 'patients.id = pharmacy_bill_basic.patient_id');
        $this->db->where('pharmacy_bill_basic.patient_id', $id);
        $query = $this->db->get('pharmacy_bill_basic');
        return $query->result_array();
    }

   public function getAllBillDetailsPharma($id) {
        $query = $this->db->select('pharmacy_bill_detail.*,medicine_category.medicine_category as medicine_category_name, pharmacy.medicine_name,pharmacy.unit,pharmacy.id as medicine_id')
                ->join('pharmacy', 'pharmacy_bill_detail.medicine_name = pharmacy.id')
                ->join('medicine_category','medicine_category.id = pharmacy_bill_detail.medicine_category_id')
                ->where('pharmacy_bill_basic_id', $id)
                ->get('pharmacy_bill_detail');
        return $query->result_array();
    }

//=====================Pathology model============================================================

    public function getBillDetailsPatho($id) {
        // $this->db->select('pathology_report.*,pathology.test_name,pathology.short_name,pathology.report_days,pathology.charge_id,patients.patient_name,staff.name as doctor_name,staff.surname as doctor_surname,charge_categories.name as charge_category','pathology.id');
        // $this->db->where('pathology_report.patient_id', $id);
        // $this->db->join('pathology', 'pathology.id = pathology_report.pathology_id');
        // $this->db->join('patients', 'patients.id = pathology_report.patient_id');
        // $this->db->join('staff', 'staff.id = pathology_report.consultant_doctor','left');
        // $this->db->join('charges', 'pathology.charge_id = charges.id','left');
        // $this->db->join('charge_categories', 'charge_categories.id = charges.charge_category_id','left');        
		// $this->db->order_by("pathology_report.id", "desc");		
        // $query = $this->db->get('pathology_report');
        // $result = $query->result_array();
        // return $result;
        
        $this->db->select('pathology_billing.*,sum(transactions.amount) as paid_amount,patients.patient_name,patients.id as pid,staff.name,staff.surname,staff.employee_id');
        $this->db->join('patients', 'patients.id = pathology_billing.patient_id', 'left');
        $this->db->join('staff', 'staff.id = pathology_billing.doctor_id', 'left');
        $this->db->join('transactions', 'transactions.pathology_billing_id = pathology_billing.id');
        $this->db->group_by('transactions.pathology_billing_id');
        $this->db->where('pathology_billing.patient_id', $id);
        $this->db->order_by('pathology_billing.id', 'desc');
        $query = $this->db->get('pathology_billing');
        return $query->result();
        
    }


    public function getAllBillDetailsPatho($id) {
        $query = $this->db->select('pathology_report.*,pathology.test_name,pathology.short_name,pathology.report_days,pathology.charge_id')
                ->join('pathology', 'pathology.id = pathology_report.pathology_id')
                ->where('pathology_report.patient_id', $id)
                ->get('pathology_report');
        return $query->result_array();
    }

     public function getparameterDetailspatho($report_id)
    {
        $query = $this->db->select('pathology_report_parameterdetails.*,pathology_parameter.parameter_name,pathology_parameter.reference_range,pathology_parameter.unit,unit.unit_name')
                ->join('pathology_parameter', 'pathology_parameter.id = pathology_report_parameterdetails.parameter_id')
                ->join('unit', 'unit.id = pathology_parameter.unit')
                ->where("pathology_report_parameterdetails.pathology_report_id",$report_id)
                ->get('pathology_report_parameterdetails');
                   return $query->result_array();
    }

//================================Radiology model=====================================================

    public function getBillDetailsRadio($id) {
        $this->db->select('radiology_report.id as report_id,radiology_report.id as bill_no,radiology_report.apply_charge,radiology_report.description,radiology_report.reporting_date,IF(radiology_report.radiology_report IS NULL,0,radiology_report.radiology_report)as radiology_report,radio.test_name,radio.short_name,radio.report_days,radio.charge_id,charge_categories.name as charge_category,patients.patient_name,staff.name as doctor_name,staff.surname as doctor_surname');
        $this->db->where('radiology_report.patient_id', $id);
        $this->db->join('radio', 'radio.id = radiology_report.radiology_id');
        $this->db->join('patients', 'patients.id = radiology_report.patient_id');
        $this->db->join('staff', 'staff.id = radiology_report.consultant_doctor','left');
        $this->db->join('charges', 'radio.charge_id = charges.id','left');
        $this->db->join('charge_categories', 'charge_categories.id = charges.charge_category_id','left');      
		$this->db->order_by("radiology_report.id", "desc");
        $query = $this->db->get('radiology_report');
        $result = $query->result_array();
        return $result;
    }

    public function getAllBillDetailsRadio($id) {
        $query = $this->db->select('radiology_report.*,radio.test_name,radio.short_name,radio.report_days,radio.charge_id')
                ->join('radio', 'radio.id = radiology_report.radiology_id')
                ->where('radiology_report.id', $id)
                ->get('radiology_report');
        return $query->result_array();
    }

     public function getparameterDetailsradio($report_id)
    {
           $query = $this->db->select('radiology_report_parameterdetails.*,radiology_parameter.parameter_name,radiology_parameter.reference_range,radiology_parameter.unit,unit.unit_name')
                ->join('radiology_parameter', 'radiology_parameter.id = radiology_report_parameterdetails.parameter_id')
                ->join('unit', 'unit.id = radiology_parameter.unit')
                ->where("radiology_report_parameterdetails.radiology_report_id",$report_id)
                ->get('radiology_report_parameterdetails');
                   return $query->result_array();
                  
    }
//========================================operationtheatre_model=============================================

    public function getBillDetailsOt($id) {
        $this->db->select('operation_theatre.*,patients.patient_name,patients.patient_unique_id,staff.name as doctor_name,staff.surname as doctor_surname');
        $this->db->join('patients', 'patients.id = operation_theatre.patient_id');
        $this->db->join('staff', 'staff.id = operation_theatre.consultant_doctor', "inner");
        $this->db->where('operation_theatre.patient_id', $id);
		$this->db->order_by("operation_theatre.bill_no", "desc");
        $query = $this->db->get('operation_theatre');
        return $query->result_array();
    }


    public function getAllBillDetailsOt($id) {
        $query = $this->db->select('operation_theatre.*')
                ->where('operation_theatre.id', $id)
                ->get('operation_theatre');
        return $query->result_array();
    }

//===================================Ambulance========================================================

    public function getBillDetailsAmbulance($id) {
        $query = $this->db->select('ambulance_call.*,vehicles.vehicle_no,vehicles.vehicle_model,vehicles.driver_name,vehicles.driver_contact,patients.patient_name,patients.mobileno,patients.address')
                ->join('vehicles', 'vehicles.id = ambulance_call.vehicle_id')
                ->join('patients', 'patients.id = ambulance_call.patient_id')
                ->where('ambulance_call.patient_id', $id)
				->order_by("ambulance_call.id", "desc")
                ->get('ambulance_call');
        return $query->result_array();
    }

//==========================Blood Bank==============================================================

    public function getBloodbank($patient_id) {
        $query = $this->db->select('blood_issue.*,patients.patient_name,blood_donor.donor_name as donorname,blood_donor.blood_group as bloodgroup')
                ->join('blood_donor', 'blood_donor.id = blood_issue.donor_name')
                ->join('patients', 'patients.id = blood_issue.recieve_to')
                ->where('blood_issue.recieve_to', $patient_id)
				->order_by("blood_issue.bill_no", "desc")
                ->get('blood_issue');
        return $query->result_array();
    }

//=================================== Appointment Model===========================================

     public function getAppointment($id) {
        
        $this->db->select('appointment.*,specialist.specialist_name,staff.id as sid,staff.name,staff.surname,staff.employee_id,patients.id as pid');
        $this->db->join('staff', 'appointment.doctor = staff.id', "inner");
        $this->db->join('patients', 'appointment.patient_id = patients.id', 'inner');
        $this->db->join('specialist', 'specialist.id = staff.specialist', 'left');
        $this->db->where('`appointment`.`doctor`=`staff`.`id`');
        $this->db->where('appointment.patient_id = patients.id');
        $this->db->where('appointment.patient_id=' . $id);
        $query = $this->db->get('appointment');
        return $query->result_array();
    }
	
    public function getAppointmentCustomfieldData($id) {
        $i               = 1;
        $custom_fields   = $this->customfield_model->getcustomfields('appointment');
        $field_var_array = array();
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, 'table_custom_' . $i . '.field_value as ' . $custom_fields_value['name']);
                $this->db->join('custom_field_values as ' . $tb_counter, 'appointment.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value['id'], 'left');
                $i++;
            }
        }

        $field_variable = implode(',', $field_var_array);
        $this->db->select($field_variable);
        $this->db->where('appointment.patient_id=' . $id);
        $query = $this->db->get('appointment');
        return $query->result_array();
    }
    
    public function getAppointmentbydate($id,$date_from, $date_to) {
        $this->db->select('appointment.*,staff.id as sid,staff.name,staff.surname,patients.id as pid,patients.patient_unique_id');
        $this->db->join('staff', 'appointment.doctor = staff.id', "LEFT");
        $this->db->join('patients', 'appointment.patient_id = patients.id', 'LEFT');
        $this->db->where('appointment.patient_id=' . $id);
        $this->db->where('date between "'.$date_from.'" AND "'.$date_to.' 23:59:59"');
        $query = $this->db->get('appointment');
        $result = $query->result_array();
        foreach ($result as $key => $value) {
            $result[$key]['date_list'] =  date('Y-m-d', strtotime($value['date']));
        }
        return $result ;
    }
    
    public function addAppointment($data) {
        if (isset($data["id"])) {

            $this->db->where("id", $data["id"])->update("events", $data);
        } else {

            $this->db->insert("appointment", $data);
        }
    }
  
    public function deleteAppointment($id) {
      $query =  $this->db->where("id", $id)
                ->where("appointment.appointment_status",'pending')
                ->delete('appointment');          
       
        if($this->db->affected_rows() > 0){
                return  $json_array = array('status' => 'success', 'error' => '', 'message' => 'Data Deleted Successfully');
         }else{             
                return  $json_array = array('status' => 'fail', 'error' => '', 'message' => '');
         }

    }

//================================================== Notification Model ==================================

    public function getNotifications($id) {

        $this->db->select('system_notification.*, IF(`read_systemnotification`.`is_active` IS NULL,0,1) as `read` ');    
        $this->db->from('system_notification');
        $this->db->join('read_systemnotification', "system_notification.id = read_systemnotification.notification_id", "left");
        $this->db->where('system_notification.notification_for','Patient');
        $this->db->where('system_notification.receiver_id',$id);
        $this->db->group_by('system_notification.id');
        $this->db->order_by('date','desc');
        $query = $this->db->get();
		return $query->result_array();
        
    }

    public function updateReadNotification($data) {
        $this->db->insert("read_systemnotification", $data);
    }

//========================================== Get Staff =====================================================

 
    function getStaff($id,$spec_id) {
        
        $this->db->select('staff.*,staff_designation.designation as designation,staff_roles.role_id, department.department_name as department,roles.name as user_type');
        $this->db->join("staff_designation", "staff_designation.id = staff.designation", "left");
        $this->db->join("department", "department.id = staff.department", "left");
        $this->db->join("staff_roles", "staff_roles.staff_id = staff.id", "left");
        $this->db->join("roles", "staff_roles.role_id = roles.id", "left");
        $this->db->where("staff_roles.role_id", $id);
        $this->db->where("staff.is_active", "1");
        $this->db->where("staff.specialist", $spec_id);
        $this->db->from('staff');
        $query = $this->db->get();
        return $query->result_array();
    }

//========================================== Get Specialist =====================================================
 
    function getSpecialist() {
        
        $this->db->select('specialist.*');
        $this->db->from('specialist');
        $query = $this->db->get();
        return $query->result_array();
    }
	
//========================================== Get Live Consult OPD =====================================================
	
	public function getconfrencebyopd($staff_id = null,$patient_id = null,$opdid = null)
    {        
        $this->db->select('conferences.*,patients.id as pid,patients.patient_name,patients.patient_unique_id,opd_details.id as opdid,opd_details.opd_no,for_create.name as create_for_name,for_create.surname as create_for_surname,for_create.employee_id as create_for_employee_id,for_create_role.name as create_for_role_name,create_by.name as create_by_name,create_by.surname as create_by_surname,create_by.employee_id as create_by_employee_id,create_by_role.name as create_by_role_name')->from('conferences');
        $this->db->join('patients', 'patients.id = conferences.patient_id');
        $this->db->join('opd_details', 'conferences.opd_id = opd_details.id');
         $this->db->join('staff as for_create', 'for_create.id = conferences.staff_id');
        $this->db->join('staff_roles' ,'staff_roles.staff_id = for_create.id');
        $this->db->join('roles as for_create_role','for_create_role.id = staff_roles.role_id');
        $this->db->join('staff as create_by', 'create_by.id = conferences.created_id');
        $this->db->join('staff_roles as staff_create_by_roles' ,'staff_create_by_roles.staff_id = create_by.id');
        $this->db->join('roles as create_by_role','create_by_role.id = staff_create_by_roles.role_id');
        $this->db->where('conferences.patient_id', $patient_id);        
        if ($opdid != "") {
            $this->db->where('conferences.opd_id', $opdid);
        }
        $this->db->order_by('DATE(`conferences`.`date`)', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }
	
//========================================== Get Live Consult IPD =====================================================	
	
	public function getconfrencebyipd($staff_id = null,$patient_id = null,$ipdid = null)
    {       
        $this->db->select('conferences.*,patients.id as pid,patients.patient_name,patients.patient_unique_id,ipd_details.id as ipdid,ipd_details.ipd_no,for_create.name as create_for_name,for_create.surname as create_for_surname,for_create.employee_id as create_for_employee_id,for_create_role.name as create_for_role_name,create_by.name as create_by_name,create_by.surname as create_by_surname,create_by.employee_id as create_by_employee_id,create_by_role.name as create_by_role_name')->from('conferences');
        $this->db->join('patients', 'patients.id = conferences.patient_id');
        $this->db->join('ipd_details', 'conferences.ipd_id = ipd_details.id');
        $this->db->join('staff as for_create', 'for_create.id = conferences.staff_id');
        $this->db->join('staff_roles' ,'staff_roles.staff_id = for_create.id');
        $this->db->join('roles as for_create_role','for_create_role.id = staff_roles.role_id');
        $this->db->join('staff as create_by', 'create_by.id = conferences.created_id');
        $this->db->join('staff_roles as staff_create_by_roles' ,'staff_create_by_roles.staff_id = create_by.id');
        $this->db->join('roles as create_by_role','create_by_role.id = staff_create_by_roles.role_id');
        $this->db->where('conferences.patient_id', $patient_id);
        if ($ipdid != "") {
            $this->db->where('conferences.ipd_id', $ipdid);
        }
        $this->db->order_by('DATE(`conferences`.`date`)', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }
	
//========================================== Get Live Consult  =====================================================	
	
	public function getconfrencebypatient($patient_id = null)
    {        
        $this->db->select('conferences.*,patients.id as pid,patients.patient_name,patients.patient_unique_id,for_create.name as create_for_name,for_create.surname as create_for_surname,for_create.employee_id as create_for_employee_id,for_create_role.name as create_for_role_name,create_by.name as create_by_name,create_by.surname as create_by_surname,create_by.employee_id as create_by_employee_id,create_by_role.name as create_by_role_name')->from('conferences');
        $this->db->join('patients', 'patients.id = conferences.patient_id');
        $this->db->join('staff as for_create', 'for_create.id = conferences.staff_id');
        $this->db->join('staff_roles' ,'staff_roles.staff_id = for_create.id');
        $this->db->join('roles as for_create_role','for_create_role.id = staff_roles.role_id');
        $this->db->join('staff as create_by', 'create_by.id = conferences.created_id');
        $this->db->join('staff_roles as staff_create_by_roles' ,'staff_create_by_roles.staff_id = create_by.id');
        $this->db->join('roles as create_by_role','create_by_role.id = staff_create_by_roles.role_id');
        $this->db->where('conferences.patient_id', $patient_id);
        $query = $this->db->get();
        return $query->result();
    }
 //========================================== Get Events AND Task ==============================

   public function getPublicEvents($patient_id,$date_from,$date_to) {
        $this->db->where("(event_type='public' OR (event_type='task' and event_for=".$this->db->escape($patient_id)."))", NULL, FALSE);
        $this->db->where('start_date BETWEEN "'.$date_from.'" AND "'.$date_to.'" OR (event_type="public" OR (event_type="task" and event_for='.$this->db->escape($patient_id).')) AND "'.$date_from.'" BETWEEN start_date AND end_date');
        $query = $this->db->get('events');
        return $query->result();
    }    

    public function getPatientEvents($id = null) {
            $cond = "event_type = 'public' or event_type = 'task' ";
            $query = $this->db->where($cond)->get("events");
            return $query->result_array();
    }


    public function getEvents($id = null) {
        if (!empty($id)) {
            $query = $this->db->where("id", $id)->get("events");
            return $query->row_array();
        } else {

            $query = $this->db->get("events");
            return $query->result_array();
        }
    }

    public function getTask($limit = null, $offset = null, $id, $role_id) {
        $query = $this->db->where(array('event_type' => 'task', 'event_for' => $id, 'role_id' => $role_id))->order_by("is_active,start_date", "asc")->limit($limit, $offset)->get("events");
        return $query->result_array();
    }

     public function getTaskEvent($id, $role_id) {
        $query = $this->db->where(array('event_type' => 'task', 'event_for' => $id, 'role_id' => $role_id))->order_by("is_active,start_date", "asc")->get("events");
        return $query->result_array();
    }

    public function getTaskbyId($id) {
        $query = $this->db->where("id", $id)->where("event_type","task")->get("events");
        return $query->row_array();
    }

    public function addTask($data) {
        if (isset($data["id"])) {
            $this->db->where("id", $data["id"])->update("events", $data);
        } else {
            $this->db->insert("events", $data);
        }
    }

    public function deleteTask($id) {
      $query =  $this->db->where("id", $id)->delete('events');
        if($this->db->affected_rows() > 0){
           return  $json_array = array('status' => 'success', 'error' => '', 'message' => 'Data Deleted Successfull');
        }else{             
           return  $json_array = array('status' => 'fail', 'error' => '', 'message' => '');
        }
    }    

    public function getOPDBalanceTotal($id) {
        $query = $this->db->select("IFNULL(sum(balance_amount),'0') as balance_amount")->where("opd_payment.patient_id", $id)->get("opd_payment");
        return $query->row_array();
    }

    public function getOpdPaymentDetailpatient($opd_id)
    {
        $SQL   = 'select opd_patient_charges.amount_due,opd_payment.amount_deposit from (SELECT sum(paid_amount) as `amount_deposit` FROM `opd_payment` WHERE opd_id=' . $this->db->escape($opd_id) . ') as opd_payment ,(SELECT sum(apply_charge) as `amount_due` FROM `opd_patient_charges` WHERE opd_id=' . $this->db->escape($opd_id) . ') as opd_patient_charges';
        $query = $this->db->query($SQL);
        return $query->row();
    }

    public function addOPDPayment($data) {
        $this->db->insert("opd_payment", $data);
        return $this->db->insert_id();
    }

    public function opdpaymentByID($id) {
        $query = $this->db->select('opd_payment.*,patients.id as pid,patients.note as pnote')
                ->join("patients", "patients.id = opd_payment.patient_id")->where("opd_payment.id", $id)
                ->get("opd_payment");
        return $query->row();
    }
	
	function todaysTaskCount($id) {		
		$this->db->select('events.*');    
        $this->db->from('events');       
        $this->db->where('events.event_for', $id);
        $this->db->where('events.event_type','task');
        $this->db->like('events.start_date',date("Y-m-d"));
		$query = $this->db->get();
        return $query->result_array();
    }
	
	public function getNotificationsThisMonth($id) {
        $this->db->select('system_notification.*');    
        $this->db->from('system_notification');       
        $this->db->where('system_notification.receiver_id',$id);
        $this->db->where('system_notification.notification_for',"Patient");
		$this->db->like('system_notification.date', date('Y-m'));
        $this->db->order_by('date','desc');
        $query = $this->db->get();
		return $query->result_array();        
    }
	
    public function getprefixes($type)
    {
        $this->db->select()->from('prefixes');        
            $this->db->where('type', $type);
            $query = $this->db->get();
            return $query->row();
       
    }
    
    public function doctorShiftById($doctor_id)
    {
        $this->db->select("g.id,g.name");
        $this->db->join("global_shift as g", "dg.global_shift_id=g.id", "left");
        $this->db->where("dg.staff_id", $doctor_id);
        $query  = $this->db->get("doctor_global_shift as dg");
        $result = $query->result_array();
        return $result;
    }
    
    public function getShiftdata($doctor, $day, $shift)
    {
        $this->db->select("id,staff_id as doctor_id,date_format(start_time,'%h:%i %p') as start_time ,date_format(end_time,'%h:%i %p') as end_time");
        $this->db->where("staff_id", $doctor);
        $this->db->where("global_shift_id", $shift);
        $this->db->where("day", $day);
        $query  = $this->db->get("doctor_shift");
        $result = $query->result();
        return $result;
    }
    
}   