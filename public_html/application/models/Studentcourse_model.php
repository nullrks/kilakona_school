<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Studentcourse_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /*
    This is used to show course list by class and section
    */
    public function courselist($classID,$sectionID) {
        $this->db->select('online_courses.*,classes.class,staff.name,staff.surname,staff.image,staff.gender,sections.section')->from('online_courses');
        $this->db->join('staff', 'staff.id = online_courses.teacher_id');
        $this->db->join('online_course_class_sections', 'online_course_class_sections.course_id = online_courses.id','left');
        $this->db->join('class_sections', 'class_sections.id =  online_course_class_sections.class_section_id','left');
        $this->db->join('classes', 'classes.id = class_sections.class_id','left');
        $this->db->join('sections', 'sections.id = class_sections.class_id','left');
        $this->db->group_by('online_course_class_sections.course_id');
        $this->db->where('class_sections.class_id',$classID);
        $this->db->where('class_sections.section_id',$sectionID);
        $this->db->where('online_courses.status','1');
        $this->db->order_by('online_courses.id', 'desc');
        $query = $this->db->get();
        return $query->result_array();
    }

        /*
    This is used to show course list by class and section
    */
    public function getcourselist($classID,$sectionID) {
        
           $query="select online_courses.*,classes.class,staff.name,staff.surname,staff.image,sections.section from online_courses join staff on staff.id = online_courses.teacher_id  join online_course_class_sections on online_course_class_sections.course_id = online_courses.id join class_sections on class_sections.id =  online_course_class_sections.class_section_id join classes on classes.id = class_sections.class_id join sections on sections.id = class_sections.section_id where class_sections.class_id=".$classID." and class_sections.section_id = ".$sectionID." and online_courses.status = '1' group by online_course_class_sections.course_id  " ;

        $this->datatables->query($query)
        ->searchable('online_courses.title,classes.class,sections.section')
        ->orderable('online_courses.title,classes.class,null,null,null,null,null,null,online_courses.updated_date') 
        ->query_where_enable(TRUE)
        ->sort('online_courses.id', 'desc') ;
        return $this->datatables->generate('json');
    }
    /*
    This is used to get total lesson count by course
    */
    public function totallessonbycourse($courseID) {
        $this->db->select('count(online_course_lesson.id) as total_lesson')->from('online_course_section');
        $this->db->join('online_course_lesson', 'online_course_lesson.course_section_id = online_course_section.id');
        $this->db->where('online_course_section.online_course_id',$courseID);
        $query = $this->db->get();
        return $query->result_array();
    }
	
	/*
    This is used to get total lesson count by course
    */
    public function totalquizbycourse($courseID) {
        $this->db->select('count(online_course_quiz.id) as total_lesson')->from('online_course_quiz');
        $this->db->join('online_course_section', 'online_course_section.id = online_course_quiz.course_section_id');
        $this->db->join('online_courses', 'online_courses.id = online_course_section.online_course_id');
        $this->db->where('online_courses.id',$courseID);
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
    This is used to get all quiz by course
    */
    public function quizbycourse($courseID) {
        $this->db->select('online_course_quiz.id,online_course_quiz.quiz_title')->from('online_course_quiz');
        $this->db->join('online_course_section', 'online_course_section.id = online_course_quiz.course_section_id');
        $this->db->join('online_courses', 'online_courses.id = online_course_section.online_course_id');
        $this->db->where('online_courses.id',$courseID);
        $this->db->order_by('online_course_quiz.id','asc');
		$query = $this->db->get();
        return $query->result();
    }

    public function quizstatusbycourseid($courseID,$studentid) {
        $this->db->select('student_quiz_status.*,online_course_quiz.quiz_title')->from('student_quiz_status');
        $this->db->join('online_course_quiz', 'online_course_quiz.id = student_quiz_status.course_quiz_id');
        $this->db->join('online_course_section', 'online_course_section.id = online_course_quiz.course_section_id');
        $this->db->join('online_courses', 'online_courses.id = online_course_section.online_course_id');
        $this->db->where('online_courses.id',$courseID);
        $this->db->where('student_quiz_status.student_id',$studentid);
        $this->db->group_by('student_quiz_status.course_quiz_id');
        $this->db->order_by('online_course_quiz.id','asc');
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
    This is used to get all attempt quiz by course
    */
    public function attemptquizbycourse($courseID) {
        $this->db->select('online_course_quiz.id,online_course_quiz.quiz_title')->from('online_course_quiz');
        $this->db->join('online_course_section', 'online_course_section.id = online_course_quiz.course_section_id');
        $this->db->join('online_courses', 'online_courses.id = online_course_section.online_course_id');
        $this->db->join('student_quiz_status', 'student_quiz_status.course_quiz_id = online_course_quiz.id');
        $this->db->where('online_courses.id',$courseID);
        $this->db->where('student_quiz_status.status','1');
        $this->db->order_by('online_course_quiz.id','asc');
		$this->db->group_by('online_course_quiz.id');	
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
    This is used to get total lesson count by section
    */
    public function totallessonbysection($sectionID) {
        $this->db->select('count(online_course_lesson.id) as total_lesson')->from('online_course_section');
        $this->db->join('online_course_lesson', 'online_course_lesson.course_section_id = online_course_section.id');
        $this->db->where('online_course_lesson.course_section_id',$sectionID);
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
    This is used to calculate total hours count
    */
    public function counthours($id)
    {
        $this->db->select('online_course_lesson.duration,online_course_lesson.lesson_type,')
            ->join("online_course_section", "online_course_section.online_course_id = online_courses.id")
            ->join("online_course_lesson", "online_course_lesson.course_section_id = online_course_section.id")
            ->where("online_course_lesson.lesson_type", 'video')
            ->where("online_courses.id", $id);
        $query     = $this->db->get('online_courses');
        $result    = $query->result_array();
        $totaltime = 0;
        $hours     = 0;
        $min       = 0;
        $sec       = 0;
        $total       = 0;
        $hh = 0;
        $mm = 0;
        $ss = 0;
        foreach ($result as $rs) {
            if ($rs['lesson_type'] == 'video') {
                $str_arr = explode(":", $rs['duration']);
				if(!empty($str_arr[0])){
                $hh      = $str_arr[0] * 3600;
				}
                if(!empty($str_arr[1])){
                $mm      = $str_arr[1] * 60;
                }
                if(!empty($str_arr[2])){
                $ss      = $str_arr[2];
                }
                $total   = $hh + $mm + $ss;
            }
            $totaltime += $total;
        }
        $hours = intval($totaltime / 3600);
        $min1  = $totaltime - ($hours * 3600);
        $min   = intval($min1 / 60);
        $sec   = $totaltime - (($min * 60) + ($hours * 3600));
        if($hours < 10){$hours = "0".$hours;}
        if($min < 10){$min = "0".$min;}
        if($sec < 10){$sec = "0".$sec;}
        
        return $hours . ':' . $min . ':' . $sec;
    }

    /*
    This is used to get lesson video for student section
    */
    public function singlevideo($lessonID) {
        $this->db->select('*')->from('online_course_lesson');
        $this->db->where('online_course_lesson.id',$lessonID);
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is used to get single quiz for student section
    */
    public function getsinglequiz($quizID) {
        $this->db->select('*')->from('online_course_quiz');
        $this->db->where('online_course_quiz.id',$quizID);
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is for getting total number of question present in single quiz  for student section
    */
    public function getquestioncount($quizID) {
        $this->db->select('count(id) as question_count')->from('course_quiz_question');
        $this->db->where('course_quiz_question.course_quiz_id',$quizID);
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is for get all question by quiz for student section
    */
    public function getallquestion($quizID) {
        $this->db->select('*')->from('course_quiz_question');
        $this->db->where('course_quiz_id',$quizID);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    /*
    This is used to get first question by quiz and question id
    */
    public function firstquestion($quizID,$quizquestionID) {
        $this->db->select('*')->from('course_quiz_question');
        $this->db->where('course_quiz_id',$quizID);
        $this->db->where('id',$quizquestionID);
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is used to get single question for next question
    */
    public function getsinglequestion($quizID,$quizquestionID) {
        $this->db->select('*')->from('course_quiz_question');
        $this->db->where('course_quiz_id',$quizID);
        $this->db->where('id >',$quizquestionID);
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is used to get single question for previous question
    */
    public function previousquestion($quizID,$quizquestionID) {
        $this->db->select('*')->from('course_quiz_question');
        $this->db->where('course_quiz_id',$quizID);
        $this->db->where('id <',$quizquestionID);
        $this->db->order_by('id','desc');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is used to add quiz answer
    */
	public function addanswer($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('course_quiz_answer', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On  course quiz answer id " . $data['id'];
            $action    = "Update";
            $record_id = $id = $data['id'];
            $this->log($message, $record_id, $action);
        } else {
            $this->db->insert('course_quiz_answer', $data);

            $id        = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On course quiz answer id " . $id;
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
    This is used to get previous answer detail
    */
    public function getpreviousanswer($id) {
        $this->db->select('course_quiz_answer.*,course_quiz_question.id as question_id,course_quiz_question.question,course_quiz_question.option_1,course_quiz_question.option_2,course_quiz_question.option_3,course_quiz_question.option_4,course_quiz_question.option_5,course_quiz_question.course_quiz_id')->from('course_quiz_answer');
        $this->db->join('course_quiz_question', 'course_quiz_question.id = course_quiz_answer.course_quiz_question_id','left');
        $this->db->where('course_quiz_answer.id',$id);
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is used to get previous question detail
    */
    public function getpreviousquestiondetail($questionID,$quizID,$student_id) {
        $this->db->select('course_quiz_answer.*,course_quiz_question.id as question_id,course_quiz_question.question,course_quiz_question.option_1,course_quiz_question.option_2,course_quiz_question.option_3,course_quiz_question.option_4,course_quiz_question.option_5,course_quiz_question.course_quiz_id')->from('course_quiz_answer');
        $this->db->join('course_quiz_question', 'course_quiz_question.id = course_quiz_answer.course_quiz_question_id','left');
        $this->db->where('course_quiz_answer.course_quiz_question_id',$questionID);
        $this->db->where('course_quiz_answer.course_quiz_id',$quizID);
        $this->db->where('course_quiz_answer.student_id',$student_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is used to get all answer detail
    */
    public function getallanswer($quizID,$studentid) {
        $this->db->select('course_quiz_question_id,answer')->from('course_quiz_answer');
        $this->db->where('course_quiz_id',$quizID);
        $this->db->where('student_id',$studentid);
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
    This is used to add student result status
    */
	public function addresult($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('student_quiz_status', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On student quiz status id " . $data['id'];
            $action    = "Update";
            $record_id = $id = $data['id'];
            $this->log($message, $record_id, $action);
        } else {
            $this->db->insert('student_quiz_status', $data);

            $id        = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On student quiz status id" . $id;
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
    This is used to get single student result
    */
    public function getresult($quizID,$studentid) {
        $this->db->select('course_quiz_answer.answer,course_quiz_question.id,course_quiz_question.question,course_quiz_question.option_1,course_quiz_question.option_2,course_quiz_question.option_3,course_quiz_question.option_4,course_quiz_question.option_5,course_quiz_question.correct_answer')->from('course_quiz_question');        
        $this->db->join('course_quiz_answer','course_quiz_question.id = course_quiz_answer.course_quiz_question_id','left');   
        $this->db->where('course_quiz_question.course_quiz_id',$quizID);
        $this->db->where('course_quiz_answer.course_quiz_id',$quizID);
        $this->db->where('course_quiz_answer.student_id',$studentid);
		$this->db->group_by('course_quiz_question.id');		
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
    This is used to get all given answer of attempt quiz 
    */
    public function getanswer($quizid,$questionid,$studentid) {
        $this->db->select('course_quiz_answer.answer')->from('course_quiz_question');        
        $this->db->join('course_quiz_answer','course_quiz_question.id = course_quiz_answer.course_quiz_question_id','left');  
        $this->db->where('course_quiz_question.course_quiz_id',$quizid);
        $this->db->where('course_quiz_answer.course_quiz_question_id',$questionid);
        $this->db->where('course_quiz_answer.student_id',$studentid);
        $this->db->group_by('course_quiz_question.id');     
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is used to get data to show student performance
    */
    public function graphdata($quizID,$studentid) {
        $this->db->select('student_quiz_status.*,course_quiz_answer.answer,course_quiz_question.id,course_quiz_question.question,course_quiz_question.option_1,course_quiz_question.option_2,course_quiz_question.option_3,course_quiz_question.option_4,course_quiz_question.option_5,course_quiz_question.correct_answer')->from('student_quiz_status');
        $this->db->join('course_quiz_answer','course_quiz_answer.course_quiz_id = student_quiz_status.course_quiz_id');
        $this->db->join('course_quiz_question','course_quiz_question.id = course_quiz_answer.question_id');
        $this->db->where('student_quiz_status.course_quiz_id',$quizID);
        $this->db->where('student_quiz_status.student_id',$studentid);
        $this->db->group_by('course_quiz_question.id');
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
    This is used to get data to show quiz performance
    */
    public function quizgraph($quizID,$studentid) {
        $this->db->select('student_quiz_status.*, student_quiz_status.correct_answer as right_answer,course_quiz_answer.answer,course_quiz_question.id,course_quiz_question.question,course_quiz_question.option_1,course_quiz_question.option_2,course_quiz_question.option_3,course_quiz_question.option_4,course_quiz_question.option_5,course_quiz_question.correct_answer,student_quiz_status.total_question')->from('student_quiz_status');
        $this->db->join('course_quiz_answer','course_quiz_answer.course_quiz_id = student_quiz_status.course_quiz_id');
        $this->db->join('course_quiz_question','course_quiz_question.id = course_quiz_answer.course_quiz_question_id');
        $this->db->where('student_quiz_status.course_quiz_id',$quizID);
        $this->db->where('student_quiz_status.student_id',$studentid);
        $this->db->group_by('student_quiz_status.course_quiz_id');
        $this->db->order_by('course_quiz_question.course_quiz_id','asc');
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is used to get total question count for result
    */
    public function questioncount($quizID,$studentid) {
        $this->db->select('count(id) as total_count')->from('course_quiz_answer');
        $this->db->where('course_quiz_id',$quizID);
        $this->db->where('student_id',$studentid);
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is for check result is complet or incomplete when student click on quiz 
    */
    public function checkstatus($quizID,$studentid) {
        $this->db->select('*')->from('student_quiz_status');
        $this->db->where('course_quiz_id',$quizID);
        $this->db->where('student_id',$studentid);
        $query = $this->db->get();
        return $query->row_array();
    }

    /*
    This is for delete result status record when student click on reset button 
    */
    public function remove($quizID,$studentid) {
        $this->db->where('course_quiz_id',$quizID);
        $this->db->where('student_id',$studentid);
        $this->db->delete('student_quiz_status');
    }

    /*
    This is for delete all answer when result status is 1 
    */
    public function removeanswer($quizID,$roleID) {
        $this->db->where('course_quiz_id',$quizID);
        $this->db->where('student_id',$roleID);
        $this->db->delete('course_quiz_answer');
    }
	
	/**
     * This function is used to mark/unmark lesson complete
     */
    public function markascomplete($lesson_data, $mark)
    {
        if ($mark == 0) {
            $this->db->where(array(
				"course_id" => $lesson_data["course_id"], 
				"course_section_id" => $lesson_data["course_section_id"], 
				"student_id" => $lesson_data["student_id"],
				"lesson_quiz_type" => $lesson_data["lesson_quiz_type"],
				"lesson_quiz_id" => $lesson_data["lesson_quiz_id"]));				
            $this->db->delete("course_progress");
        } elseif ($mark == 1) {
            $this->db->insert("course_progress", $lesson_data);
        }
    }
	
	/**
     * This function is used to get course progress
     */
    public function getcourseprogress($courseid, $student_id, $section_id, $lesson_quiz_type, $lesson_quiz_id)
    {
        $result = $this->db->select("id")
            ->where(array("course_id" => $courseid, "student_id" => $student_id, "course_section_id" => $section_id, "lesson_quiz_type" => $lesson_quiz_type, "lesson_quiz_id" => $lesson_quiz_id))
            ->get("course_progress")
            ->result_array();
        return $result;
    }
	
	/*
    This function is used to get course progress
    */
    public function courseprogresscount($courseid, $student_id)
    {
        $result = $this->db->select("id")
            ->where(array("course_id" => $courseid, "student_id" => $student_id))
            ->get("course_progress")
            ->result_array();
        return $result;
    }

    /*
    This is used to get section count based on the course id
    */
    public function getsectioncount($id){         
        $this->db->select('count(*) as total_section');
        $this->db->from('online_course_section');
        $this->db->where('online_course_id',$id);
        $this->db->join('online_courses','online_courses.id=online_course_section.online_course_id');
        $query = $this->db->get();
        $result=$query->row_array();
        return $result['total_section'] ;
    }

    /*
    This is used to get completed lesson, quiz count by course
    */
    public function completelessonquizbycourse($courseid,$userid) {
        $this->db->select('course_progress.*')->from('course_progress');
        $this->db->where('course_progress.course_id',$courseid);
        $this->db->where('course_progress.student_id',$userid);
        $query = $this->db->get();
        $result = $query->result();
        $lesscount = 0 ;
        $quizcount = 0 ;		
        foreach ($result as $key => $sectionList_value) {            
            if($sectionList_value->lesson_quiz_type == 1){
               $lesscount++ ; 
            }
            if($sectionList_value->lesson_quiz_type == 2){
               $quizcount++ ; 
            }
            $result['lesson'] = $lesscount;                  
            $result['quiz'] = $quizcount;              
        }                           
        return $result;
    }

    public function lessonquizcountbycourseid($course_id,$studentid) {
        $this->db->select('online_course_section.*,online_courses.title');
        $this->db->from('online_course_section');        
        $this->db->join('online_courses','online_courses.id=online_course_section.online_course_id');
        $this->db->where('online_course_section.online_course_id',$course_id);
        $query = $this->db->get();
        $result = $query->result();
        $result['lessoncount'] ='';
        $result['quizcount'] ='';
        foreach ($result as $key => $sectionList_value) {
            $lesson_count = $this->totallessonbycourse($course_id);
            $result['lessoncount'] = $lesson_count[0]['total_lesson'];             
            $quiz_count = $this->totalquizbycourse($course_id);
            $result['quizcount'] = $quiz_count[0]['total_lesson'];
			if($studentid != ''){
				$result['courseprogresscount'] = $this->courseprogresscount($course_id,$studentid);  
			}			
        }                           
        return $result;
    }   
}