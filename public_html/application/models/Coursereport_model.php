<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Coursereport_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /*
    This is used to get course list by payment mode and search type
    */
    public function coursereport($payment_type, $start_date, $end_date) {
        $condition="" ;
        if($payment_type != 'all'){
                $condition.= "and online_course_payment.payment_type='".$payment_type."' " ;
            }
		
        $sql="select online_courses.title, online_courses.course_provider, online_course_payment.*, students.firstname, students.lastname ,students.admission_no,online_course_payment.payment_type from online_courses inner join online_course_payment on online_course_payment.online_courses_id=online_courses.id inner join students on  students.id=online_course_payment.student_id where date_format(online_course_payment.date,'%Y-%m-%d') >='". $start_date."'  and date_format(online_course_payment.date,'%Y-%m-%d') <= '".$end_date."' and online_courses.status = '1' and students.is_active = 'yes' ".$condition ;
             $this->datatables->query($sql) 
              ->searchable('students.firstname,students.admission_no,date,online_courses.title,online_courses.course_provider,payment_type,payment_mode,paid_amount')

              ->orderable('students.firstname,students.admission_no,date,online_courses.title,online_courses.course_provider,payment_type,payment_mode,paid_amount')
              ->sort('date_format(online_course_payment.date, "%m/%e/%Y")','desc')
              ->query_where_enable(TRUE);
        return $this->datatables->generate('json');
    }    

    /*
    This is used to get data for seller report
    */
    public function sellreport() {
       
        $this->datatables
            ->select('count(online_courses.id) as sell_count, online_courses.title, online_courses.created_by, staff.name, staff.surname, staff.employee_id, students.firstname,students.lastname,students.admission_no,online_course_payment.date,online_course_payment.online_courses_id,classes.class,s.name as assign_name,s.surname as assign_surname,s.employee_id as assign_employee_id')
            ->searchable('online_courses.title, online_courses.created_by, staff.name, staff.surname, students.firstname,students.lastname,students.admission_no,online_course_payment.date,online_course_payment.online_courses_id,classes.class')
             ->orderable('online_courses.title,classes.class," ",sell_count,assign_name, name')
            ->group_by('online_course_payment.online_courses_id')
            ->join('online_courses','online_courses.id = online_course_payment.online_courses_id')
            ->join('staff','staff.id=online_courses.created_by')			
			->join('staff as s', 's.id = online_courses.teacher_id')			
            ->join('students','students.id=online_course_payment.student_id')			
			->join('online_course_class_sections', 'online_course_class_sections.course_id = online_courses.id')
			->join('class_sections', 'class_sections.id =  online_course_class_sections.class_section_id')
			->join('classes', 'classes.id = class_sections.class_id')		
            ->sort('sell_count','desc')
            ->from('online_course_payment')
            ->where(array('online_courses.status' =>'1'));
            return $this->datatables->generate('json');
    }

    /*
    This is used to show student list by purchasing course
    */
    public function studentdata($courseid) {
      
       $this->datatables
            ->select('online_courses.title,students.firstname,students.lastname,students.admission_no,online_course_payment.date,online_course_payment.online_courses_id,online_course_payment.paid_amount')
           ->searchable('students.firstname,students.admission_no,online_course_payment.date')
            ->orderable('students.firstname,students.admission_no,online_course_payment.date')
            ->join('online_courses','online_courses.id = online_course_payment.online_courses_id')
            ->join('students','students.id=online_course_payment.student_id')
           ->where(array('online_course_payment.online_courses_id'=> $courseid, 'online_courses.status' =>'1'))         
            ->from('online_course_payment');
            return $this->datatables->generate('json');
    }

     /*
    This is used to get top trending course list
    */
    public function trendingreport() {
      
        $this->datatables
            ->select('online_courses.*,staff.name,staff.surname,staff.employee_id,s.name as assign_name,s.surname as assign_surname,s.employee_id as assign_employee_id,classes.class,')
            ->searchable('online_courses.title, online_courses.created_by, staff.name, staff.surname,classes.class,view_count')
            ->orderable('online_courses.title,classes.class," ",view_count, online_courses.created_by, staff.name, staff.surname')
            ->join('staff','staff.id=online_courses.created_by')
			->join('staff as s', 's.id = online_courses.teacher_id')
            ->join('staff_roles','staff_roles.staff_id=staff.id')
			->join('online_course_class_sections', 'online_course_class_sections.course_id = online_courses.id')
			->join('class_sections', 'class_sections.id =  online_course_class_sections.class_section_id')
			->join('classes', 'classes.id = class_sections.class_id')
            ->sort('online_courses.view_count','desc')
			->group_by('online_courses.id')
            ->from('online_courses')
            ->where(array('online_courses.status' =>'1'));
            return $this->datatables->generate('json');
    }

    /*
    This is used to get student list by class and section
    */
    public function courselist($class_section_id) {
        $this->db->select('online_courses.id,online_courses.title');
        $this->db->from('online_courses');
        $this->db->join('online_course_class_sections','online_course_class_sections.course_id=online_courses.id');
        $this->db->where('online_course_class_sections.class_section_id',$class_section_id);
        $this->db->where('online_courses.status','1');
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
    This is used to get student list by class_section_id and course id
    */
    public function coursecompletereport($class_section_id) {
      
        $this->datatables
            ->select('students.id,students.firstname,students.lastname,students.admission_no')
            ->searchable('students.firstname, students.lastname,students.admission_no')
            ->orderable('students.firstname, students.admission_no," "')
            ->group_by('students.id')            
            ->join('student_session', 'student_session.class_id = class_sections.class_id and student_session.section_id = class_sections.section_id')
            ->join('students', 'student_session.student_id = students.id')           
            ->from('class_sections')
            ->where(array('class_sections.id' => $class_section_id));
            return $this->datatables->generate('json');
    }
}