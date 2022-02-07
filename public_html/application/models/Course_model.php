<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Course_model extends MY_Model {

    public function __construct() {
        parent::__construct();
		$this->current_session = $this->setting_model->getCurrentSession();
    }
 
    /*
    This is used to add or edit course
    */
	function add($data) {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id']) && $data['id'] != '') {
            $this->db->where('id', $data['id']);
            $this->db->update('online_courses', $data);
            $message = UPDATE_RECORD_CONSTANT . " On online courses id " . $data['id'];
            $action = "Update";
            $record_id = $data['id'];
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                return $record_id;
            }
        } else {
            $this->db->insert('online_courses', $data);

            $insert_id = $this->db->insert_id();
            $message = INSERT_RECORD_CONSTANT . " On online courses id " . $insert_id;
            $action = "Insert";
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                //return $return_value;
            }
            return $insert_id;
        }
    }

    /*
    This is used to getting all teacher list
    */
    public function allteacher() {
        $this->db->select('staff.id,staff.name,staff.surname');
        $this->db->from('staff');
        $this->db->join('staff_roles','staff_roles.staff_id=staff.id');
        $this->db->where('staff_roles.role_id','2');
        $this->db->where('staff.is_active','1');
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
    This is used to getting all course list
    */
    public function courselist($userid, $roleid, $limit = '', $start = '', $search = '') {
        if($roleid == "2"){
            $userdata = $this->customlib->getUserData();
            $role_id = $userdata["role_id"];
            $carray = array();
            $class_section_id=array();
            if (isset($role_id) && ($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
                if ($userdata["class_teacher"] == 'yes') {

                    $classlist = $this->teacher_model->get_teacherrestricted_mode($userdata["id"]);
                }
                foreach ($classlist as $key => $value) {
                    $class_section=$this->teacher_model->get_teacherrestricted_modesections($userdata["id"], $value['id']);
                    $class_section_id[]=$class_section[0]['id'];
                }
            }
        }

        if ($limit != "" && ( $start != "" || ($start >= 0))) {
            $this->db->limit($limit, $start);
        }

        if ($search != '') {
            $this->db->like('online_courses.title', $search);
            $this->db->or_like('online_courses.description', $search);
        } 

        $this->db->select('online_courses.*,classes.class,staff.name,staff.surname,staff.image,staff.gender,sections.section')->from('online_courses');
        $this->db->join('staff', 'staff.id = online_courses.teacher_id');
        $this->db->join('online_course_class_sections', 'online_course_class_sections.course_id = online_courses.id');
        $this->db->join('class_sections', 'class_sections.id =  online_course_class_sections.class_section_id');
        $this->db->join('classes', 'classes.id = class_sections.class_id');
        $this->db->join('sections', 'sections.id = class_sections.section_id');
        $this->db->group_by('online_course_class_sections.course_id');
		// For teacher
        if($roleid == "2"){
          if (isset($role_id) && ($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
        if(!empty($class_section_id)){
             $this->db->where_in('online_course_class_sections.class_section_id', $class_section_id);
        }
         $this->db->or_where('online_courses.teacher_id',$userid);
         $this->db->or_where('online_courses.created_by',$userid);
        }else{
        $this->db->where('online_courses.teacher_id',$userid);
         $this->db->or_where('online_courses.created_by',$userid);
        }
        }
        $this->db->order_by('online_courses.id', 'desc');
        $query = $this->db->get();
        return  $query->result_array();
    }
    
    /*
    This is used to getting all course list through datatable
    */
    public function getcourselist($userid, $roleid) {
      
        $condition="" ;
        $where_condition_status=FALSE;
       
       if($roleid == "2"){
             $this->datatables->where('teacher_id',$userid,true);
             $this->datatables->or_where('created_by',$userid,true);
              $where_condition_status=TRUE;
       }
       
        $query="select online_courses.*,classes.class,staff.name,staff.surname,sections.section from online_courses join staff on staff.id = online_courses.teacher_id  join online_course_class_sections on online_course_class_sections.course_id = online_courses.id join class_sections on class_sections.id =  online_course_class_sections.class_section_id join classes on classes.id = class_sections.class_id join sections on sections.id = class_sections.section_id  " ;

        $this->datatables->query($query)
        ->searchable('online_courses.title,classes.class,sections.section')
        ->orderable('online_courses.title,classes.class,null,null,null,null,null,null,updated_date') 
        ->query_where_enable($where_condition_status)
        ->group_by('online_course_class_sections.course_id',true)
        ->sort('online_courses.id', 'desc');

        return $this->datatables->generate('json');
    }

    /*
    This is used to getting single course
    */
    public function singlecourselist($courseid) {
        $this->db->select('online_courses.*,online_course_class_sections.id as class_sections_id,classes.class,staff.name as staff_name,staff.surname as staff_surname,s.name,s.surname,s.employee_id,staff.image,staff.gender,sections.section,class_sections.class_id,class_sections.section_id,online_course_class_sections.class_section_id as class_sections')->from('online_courses');
        $this->db->where('online_courses.id',$courseid);
        $this->db->join('staff', 'staff.id = online_courses.teacher_id');
        $this->db->join('staff as s', 's.id = online_courses.created_by');
        $this->db->join('online_course_class_sections', 'online_course_class_sections.course_id = online_courses.id');
        $this->db->join('class_sections', 'class_sections.id =  online_course_class_sections.class_section_id');
        $this->db->join('classes', 'classes.id = class_sections.class_id');
        $this->db->join('sections', 'sections.id = class_sections.section_id');
        $this->db->group_by('online_course_class_sections.course_id');
        $this->db->order_by('online_courses.title', 'asc');
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is used to getting lesson by section id
    */
    public function lessonbysection($id) {
        $this->db->select('*');
        $this->db->from('online_course_lesson');
        $this->db->where('online_section_id',$id);
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
    This is used to add and edit multipal section in course
    */
	public function addsections($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('online_course_class_sections', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On online course class sections id " . $data['id'];
            $action    = "Update";
            $record_id = $id = $data['id'];
            $this->log($message, $record_id, $action);
        } else {
            $this->db->insert('online_course_class_sections', $data);
            $id        = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On online course class sections id " . $id;
            $action    = "Insert";
            $record_id = $id;
            $this->log($message, $record_id, $action);           
        }
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return $id;
        }
    }

    /*
    This is used to get total count of section by course id
    */
    public function sectioncount($courseid) {
        $this->db->select('count(course_id) as section_count')->from('online_course_class_sections');
        $this->db->where('course_id',$courseid);
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is used to getting class with sections id by course id
    */
    public function coursesectionlist($id,$courseid) {
        $this->db->select('*')->from('online_course_class_sections');
        $this->db->where('class_section_id',$id);
        $this->db->where('course_id',$courseid);
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is used to get class section list by course id
    */
    public function sectionbycourse($courseid) {
        $this->db->select('class_section_id')->from('online_course_class_sections');
        $this->db->where('course_id',$courseid);
        $query = $this->db->get();
        $result =  $query->result_array();
        foreach ($result as $result_value) {
           $results[] = $result_value['class_section_id'];
        }
        return $results;
    }

    /*
    This is used to delete class section list
    */
    public function remove($id,$courseID) {
        $this->db->where('class_section_id',$id);
        $this->db->where('course_id',$courseID);
        $this->db->delete('online_course_class_sections');
    }

    /*
    This is used to get total section by course id
    */
    public function getclassid($courseid) {
        $this->db->select('class_sections.class_id')->from('online_course_class_sections');
        $this->db->join('class_sections', 'class_sections.id = online_course_class_sections.class_section_id');
        $this->db->where('online_course_class_sections.course_id',$courseid);
        $this->db->group_by('class_sections.class_id');
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is used to get selected section by course id
    */
    public function selectedsection($courseid) {
        $this->db->select('online_course_class_sections.class_section_id')->from('online_course_class_sections');
        $this->db->where('online_course_class_sections.course_id',$courseid);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    /*
    This is used to get total section by course id
    */
    public function multipalsection($courseid) {
        $this->db->select('sections.section,online_course_class_sections.class_section_id')->from('online_course_class_sections');
        $this->db->join('class_sections', 'class_sections.id = online_course_class_sections.class_section_id');
        $this->db->join('sections', 'sections.id = class_sections.section_id');
        $this->db->where('online_course_class_sections.course_id',$courseid);
        $query = $this->db->get();
        return $query->result_array();
    }
	
	/*
    This is used to getting course id based on section id
    */
    public function coursebysection($section_id) {
        $this->db->select('online_courses.id');
		$this->db->join('online_courses', 'online_courses.id = online_course_section.online_course_id');
        $this->db->from('online_course_section');
        $this->db->where('online_course_section.id',$section_id);
        $query = $this->db->get();
        return $query->row_array();
    }
	
	/*
    This is used to get student list based on class section id
    */
	public function getStudentByClassSectionID($class_section_id)
    {
        $this->db->select('classes.id AS `class_id`, classes.class, sections.id AS `section_id`, sections.section, students.id, students.admission_no, students.roll_no, students.admission_date, students.firstname, students.lastname, students.mobileno, students.email, students.previous_school, students.guardian_is, students.parent_id, students.permanent_address, students.is_active , students.created_at, students.updated_at, users.username, students.app_key')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('users', 'users.user_id = students.id', 'left');
        $this->db->join('class_sections', ' class_sections.class_id=classes.id and class_sections.section_id= sections.id');
        $this->db->where_in('class_sections.id', $class_section_id);
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('users.role', 'student');
        $this->db->where('students.is_active', 'yes');
        $this->db->order_by('students.id', 'desc');
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
     This function is used to delete course and their section, lesson, quiz question, quiz 
     */
    public function delete($id)
    {
        $query  = $this->db->where("online_course_id", $id)->get('online_course_section');
        $result = $query->result_array();
        foreach ($result as $key => $value) {
            $section_id = $value["id"];
            $this->db->where("course_section_id", $section_id)->delete("course_lesson_quiz_order");            
            $this->db->where("course_section_id", $section_id)->delete("online_course_lesson");
            $this->db->where("course_section_id", $section_id)->delete("course_progress");
            $quiz_query  = $this->db->where("course_section_id", $section_id)->get('online_course_quiz');
            $quiz_result = $quiz_query->result_array();
            foreach ($quiz_result as $key => $quiz_result_value) {
                $quiz_id = $quiz_result_value['id'];
                $this->db->where("course_quiz_id", $quiz_id)->delete("student_quiz_status");
                $this->db->where("course_quiz_id", $quiz_id)->delete("course_quiz_answer");
                $this->db->where("course_quiz_id", $quiz_id)->delete("course_quiz_question");
            }
            $this->db->where("course_section_id", $section_id)->delete("online_course_quiz");
        }
        $this->db->where("online_course_id", $id)->delete('online_course_section');
        $this->db->where("course_id", $id)->delete('online_course_class_sections');
        $this->db->where("id", $id)->delete('online_courses'); 
    }

    /*
     This function is used to get section name by course id 
    */
    public function getsectionnamebycourse($course_id)
    {
        $this->db->select('sections.section')->from('online_courses');
        $this->db->join('online_course_class_sections', 'online_courses.id = online_course_class_sections.course_id');
        $this->db->join('class_sections', 'class_sections.id = online_course_class_sections.class_section_id');
        $this->db->join('sections', 'sections.id = class_sections.section_id');
        $this->db->where('online_courses.id', $course_id);
        $query = $this->db->get();
        return $query->result_array();

    }

    /*
     This function is used to update section order 
    */
    public function updatesectionorder($data) {
        $this->db->update_batch('online_course_section', $data, 'id');
    }

    /*
     This function is used to update lesson, quiz order 
    */
    public function updatelessonquizorder($data) {
        $this->db->update_batch('course_lesson_quiz_order', $data, 'id');
    }	
	
	public function coursesellcount($course_id) {
        $this->db->select('online_course_payment.id,');
        $this->db->from('online_course_payment');
        $this->db->where('online_course_payment.online_courses_id',$course_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
     This function is used to add and update s3 bucket settings
    */
    public function addAwsS3Settings($data)
    {
        $this->db->trans_begin();
        $q = $this->db->get('aws_s3_settings');
        if ($q->num_rows() > 0) {
            $results = $q->row();
            $this->db->where('id', $results->id);
            $this->db->update('aws_s3_settings', $data);
        } else {

            $this->db->insert('aws_s3_settings', $data);
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }
    }
    
    /*
     This function is used to get s3 bucket settings
    */
    public function getAwsS3Settings()
    {
        $this->db->select('*');
        $this->db->from('aws_s3_settings');
        $this->db->order_by('aws_s3_settings.id');
        $query = $this->db->get();
        return $query->row();
    }

}