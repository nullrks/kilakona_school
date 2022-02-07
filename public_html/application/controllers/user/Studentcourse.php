<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Studentcourse extends Student_Controller
{

    public function __construct()
    {
        parent::__construct();
		$this->load->model(array('course_model','coursesection_model','courselesson_model','studentcourse_model','coursequiz_model','course_payment_model','courseofflinepayment_model','coursereport_model'));
        $this->current_classSection = $this->customlib->getStudentCurrentClsSection();
        $this->result               = $this->customlib->getLoggedInUserData();
        $this->load->library("aws3");
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    /*
    This is used to get student course list
    */
    public function index()
    {		
		
        $userid = $this->result["student_id"];
        $this->session->set_userdata('top_menu', 'user/studentcourse');
        $class_id           = $this->current_classSection->class_id;
        $data['class_id']   = $class_id;
        $section_id         = $this->current_classSection->section_id;
        $data['section_id'] = $section_id;
        $courselist         = $this->studentcourse_model->courselist($class_id, $section_id);
		$data['paymentgateway']         	= $this->paymentsetting_model->getActiveMethod();
        $new_courselist     = array();
        foreach ($courselist as $courselist_value) {
			
			$lessonquizcount = $this->studentcourse_model->lessonquizcountbycourseid($courselist_value['id'],$userid);
			$data['lesson_count'] =  $total_lesson = $lessonquizcount['lessoncount'];
			$data['quiz_count'] = $total_quiz = $lessonquizcount['quizcount'];
            $courselist_value['total_hour_count'] = $this->studentcourse_model->counthours($courselist_value['id']);
            $courselist_value['paidstatus']       = $this->courseofflinepayment_model->paidstatus($courselist_value['id'], $userid);
			$courseprogresscount = $lessonquizcount['courseprogresscount'];		
			$total_quiz_lession = $total_lesson + $total_quiz;
			
            $course_progress    = 0;
            if ($total_quiz_lession > 0) {
                $course_progress = (count($courseprogresscount) / $total_quiz_lession) * 100;
            }

            $courselist_value['course_progress'] = $course_progress;
            $new_courselist[]                    = $courselist_value;

        }

        $data['loginsession'] = $this->session->userdata('student');
        $data['new_courselist'] = $new_courselist;
        $this->load->view('layout/student/header');
        $this->load->view('user/studentcourse/studentcourselist', $data);
        $this->load->view('layout/student/footer');
    }

    /*
    This is used to get start lesson list for student section
    */
    public function startlesson()
    {
        $userid              = $this->result["student_id"];
        $courseID            = $this->input->post('coureseID');
        $data['paidstatus']  = $this->courseofflinepayment_model->paidstatus($courseID, $userid);
        $coursesList         = $this->course_model->singlecourselist($courseID);
        $data['coursesList'] = $coursesList;
        $sectionList         = $this->coursesection_model->getsectionbycourse($courseID);
        $data['sectionList'] = $sectionList;
        if (!empty($coursesList)) {
            $viewcount['id']         = $courseID;
            $viewcount['view_count'] = $coursesList['view_count'] + 1;
            $this->course_model->add($viewcount);
        }

        $lessonquizlist_array = array();
        if (!empty($sectionList)) {
            foreach ($sectionList as $sectionList_value) {
                $lessonquizlist_array[$sectionList_value->id] = $this->coursesection_model->lessonquizbysection($sectionList_value->id);
                foreach ($lessonquizlist_array[$sectionList_value->id] as $lesson_array) {
                    $lesson_id                  = $lesson_array['lesson_id'];
                    $lessonprogress[$lesson_id] = $this->studentcourse_model->getcourseprogress($courseID, $userid, $sectionList_value->id, 1, $lesson_id);
                }
                foreach ($lessonquizlist_array[$sectionList_value->id] as $quiz_array) {
                    $quiz_id                = $quiz_array['quiz_id'];
                    $quizprogress[$quiz_id] = $this->studentcourse_model->getcourseprogress($courseID, $userid, $sectionList_value->id, 2, $quiz_id);
                }
            }
            if (!empty($lessonprogress)) {
                $data['lessonprogress'] = $lessonprogress;
            }
             if (!empty($quizprogress)) {
                $data['quizprogress'] = $quizprogress;
            }
			
			if(!empty($lessonquizlist_array)){
				$data['lessonquizdetail'] = $lessonquizlist_array;
			} else {
				$data['lessonquizdetail'] = '';
			}
        } 
        $this->load->view('user/studentcourse/studentstartlesson', $data);
    }

    /*
    This is used to get start lesson video list for student section
     */
    public function getlessonvideo()
    {
        $data['sectionid'] = $this->input->post('sectionID');
        $lessonID          = $this->input->post('lessonID');
        $lesson            = $this->studentcourse_model->singlevideo($lessonID);
        if ($lesson['video_provider'] == "s3_bucket") {
            $lesson['s3_url'] = $this->aws3->generateUrl($lesson['video_id']);
        }
        $data['lesson'] = $lesson;
        $this->load->view('user/studentcourse/studentlessonvideo', $data);
    }

    /*
    This is used to get quiz question list from quiz for student section
     */
    public function quizinstruction()
    {
        $userid                  = $this->result["student_id"];
        $courseid                = $this->input->post('courseid');
        $data['courseid']        = $courseid;
        $quizID                  = $this->input->post('quizID');
        $data['singlequizlist']  = $this->studentcourse_model->getsinglequiz($quizID);
        $questioncount           = $this->studentcourse_model->getquestioncount($quizID);
        $data['questioncount']   = $questioncount;
        $data['total_questions'] = count($this->studentcourse_model->getallquestion($quizID));
        $questionlist            = $this->studentcourse_model->getallquestion($quizID);
        if (!empty($questionlist)) {
            $data['questionlist'] = $questionlist[0];
        } else {
            $data['questionlist'] = '';
        }

        $answerlist = array();

        foreach ($questionlist as $questionlist_value) {
            $answerlist[$questionlist_value['id']] = $this->studentcourse_model->getanswer($quizID, $questionlist_value['id'] ,$userid); 
        }
        if(!empty($answerlist)){
            $data['answerlist'] = $answerlist;
        }else{
            $data['answerlist'] = '';
        }

        $resultstatus          = $this->studentcourse_model->checkstatus($quizID, $userid);
        $totalmarks            = $this->quizgraph($courseid, $userid);
        $data['totalmarks']    = $totalmarks['totalmarks'];
        $data['totalquiz']     = $totalmarks['totalquiz'];
        $data['graphdata']     = $resultstatus;
        if(!empty($resultstatus['not_answer'])){
			$data['not_attempted'] = $resultstatus['not_answer'];
		}else{
			$data['not_attempted'] = 0;
		}
		if(!empty($resultstatus['wrong_answer'])){
			$data['wronganswer']   = $resultstatus['wrong_answer'];
		}else{
			$data['wronganswer']   = 0;
		}
		if(!empty($resultstatus['correct_answer'])){
			$data['answercount']   = $resultstatus['correct_answer'];
		}else{
			$data['answercount']   = 0;
		}
        if (!empty($resultstatus)) {
            if ($resultstatus['status'] == 1) {
                $data['questionlist'] = $questionlist;
                $data['status']        = $resultstatus['status'];
                $data['quizid']        = $resultstatus['course_quiz_id'];
                $data['studentresult'] = $this->studentcourse_model->getresult($quizID, $userid);
                $data['questioncount'] = $this->studentcourse_model->getquestioncount($quizID);
                $data['options']       = array('option_1', 'option_2', 'option_3', 'option_4', 'option_5');

                $this->load->view('user/studentcourse/studentresult', $data);
            }
        } else {
            $this->load->view('user/studentcourse/_quizinstruction', $data);
        }
    }

    /*
    This is used to get single question list by quiz for student section
    */
    public function quizquestion()
    {
        $userid                     = $this->result["student_id"];
        $data['courseid']           = $this->input->post('courseid');
        $quizID                     = $this->input->post('quizID');
        $questionID                 = $this->input->post('quizquestionID');
        $data['quizID']             = $quizID;
        $data['questionlist']       = $this->studentcourse_model->getallquestion($quizID);
        $data['total_questions']    = count($this->studentcourse_model->getallquestion($quizID));
        $data['singlequestionlist'] = $this->studentcourse_model->firstquestion($quizID, $questionID);
        $data['answerlist']         = $this->studentcourse_model->getpreviousquestiondetail($questionID, $quizID, $userid);
        $allanswerlist              = $this->studentcourse_model->getallanswer($quizID, $userid);

        // get button color of question
        $color                 = array();
        $result                = $this->color($quizID, $userid);
        $data['color']         = $result['color'];
        $data['allanswerlist'] = $allanswerlist;
        $this->load->view('user/studentcourse/_quizquestion', $data);
    }

    /*
    This is used to save quiz answer for student section
     */
    public function create()
    {
        $userid           = $this->result["student_id"];
        $data['courseid'] = $this->input->post('courseid');
        $previousID       = $this->input->post('previousID');
        $quizID           = $this->input->post('quizID');
        $questionID       = $this->input->post('question_id');
        $answer1          = $this->input->post('answer_1');
        $answer2          = $this->input->post('answer_2');
        $answer3          = $this->input->post('answer_3');
        $answer4          = $this->input->post('answer_4');
        $answer5          = $this->input->post('answer_5');

        if (!empty($answer1)) {
            $answer1 = 'option_1';
        }
        if (!empty($answer2)) {
            $answer2 = 'option_2';
        }
        if (!empty($answer3)) {
            $answer3 = 'option_3';
        }
        if (!empty($answer4)) {
            $answer4 = 'option_4';
        }
        if (!empty($answer5)) {
            $answer5 = 'option_5';
        }

        $data['questionlist']    = $this->studentcourse_model->getallquestion($quizID);
        $data['total_questions'] = count($this->studentcourse_model->getallquestion($quizID));

        // get button color of question
        $color         = array();
        $result        = $this->color($quizID, $userid);
        $data['color'] = $result['color'];

        if (!empty($answer1) || !empty($answer2) || !empty($answer3) || !empty($answer4) || !empty($answer5)) {
            $correctAnswer = array($answer1, $answer2, $answer3, $answer4, $answer5);

            if (!empty($previousID)) {
                $previousdata               = $this->previousdata($previousID, $quizID, $userid);
                $data['singlequestionlist'] = $previousdata['singlequestionlist'];
                $data['answerlist']         = $previousdata['answerlist'];

                // get button color of question
                $color         = array();
                $result        = $this->color($quizID, $userid);
                $data['color'] = $result['color'];
            } else {
                $questionexist = $this->studentcourse_model->getpreviousquestiondetail($questionID, $quizID, $userid);
                if (!empty($questionexist)) {

                    $correctAnswer = array($answer1, $answer2, $answer3, $answer4, $answer5);

                    $updatedanswerlist          = $this->updatedanswer($questionexist['id'], $correctAnswer, $questionID, $quizID, $userid);
                    $data['singlequestionlist'] = $updatedanswerlist['singlequestionlist'];
                    $data['answerlist']         = $updatedanswerlist['answerlist'];

                    // get button color of question
                    $color         = array();
                    $result        = $this->color($quizID, $userid);
                    $data['color'] = $result['color'];
                } else {
                    $correctAnswer = array($answer1, $answer2, $answer3, $answer4, $answer5);
                    $addData       = array(
                        'student_id' => $userid,
                        'course_quiz_id' => $quizID,
                        'course_quiz_question_id' => $questionID,
                        'answer' => json_encode($correctAnswer),
                        'created_date' => date('Y-m-d H:i:s'),
                    );
                    $this->studentcourse_model->addanswer($addData);

                    $singlequestionlist         = $this->studentcourse_model->getsinglequestion($quizID, $questionID);
                    $data['singlequestionlist'] = $singlequestionlist;   
                    // get button color of question
                    $color         = array();
                    $result        = $this->color($quizID, $userid);
                    $data['color'] = $result['color'];
                }
            }

        } else {
            if (!empty($previousID)) {
                $previousdata               = $this->previousdata($previousID, $quizID, $userid);
                $data['singlequestionlist'] = $previousdata['singlequestionlist'];
                $data['answerlist']         = $previousdata['answerlist'];
                // get button color of question
                $color         = array();
                $result        = $this->color($quizID, $userid);
                $data['color'] = $result['color'];
            } else {
                $questionexist = $this->studentcourse_model->getpreviousquestiondetail($questionID, $quizID, $userid);
                if (!empty($questionexist)) {
                    $correctAnswer = array($answer1, $answer2, $answer3, $answer4, $answer5);
                    $updatedanswerlist          = $this->updatedanswer($questionexist['id'], $correctAnswer, $questionID, $quizID, $userid);
                    $data['singlequestionlist'] = $updatedanswerlist['singlequestionlist'];
                    $data['answerlist']         = $updatedanswerlist['answerlist'];
                    // get button color of question
                    $color         = array();
                    $result        = $this->color($quizID, $userid);
                    $data['color'] = $result['color'];

                } else {
                    $correctAnswer = array($answer1, $answer2, $answer3, $answer4, $answer5);
                    $addData       = array(
                        'student_id'   => $userid,
                        'course_quiz_id' => $quizID,
                        'course_quiz_question_id'  => $questionID,
                        'answer'       => json_encode($correctAnswer),
                        'created_date' => date('Y-m-d H:i:s'),
                    );
                    $this->studentcourse_model->addanswer($addData);
                    $singlequestionlist         = $this->studentcourse_model->getsinglequestion($quizID, $questionID);
                    $data['singlequestionlist'] = $singlequestionlist;
                    $answerlist                 = $this->studentcourse_model->getpreviousquestiondetail($questionID, $quizID, $userid);
                    $data['answerlist']         = $answerlist;
                    // get button color of question
                    $color         = array();
                    $result        = $this->color($quizID, $userid);
                    $data['color'] = $result['color'];
                }
            }
        }
        $this->load->view('user/studentcourse/_quizquestion', $data);
    }

    /*
    This is used to get course detail
     */
    public function coursedetail()
    {
        $userid                   = $this->result["student_id"];
        $courseID                 = $this->input->post('courseID');
        
		$lessonquizcount = $lessonquizcount =$this->studentcourse_model->lessonquizcountbycourseid($courseID,$userid);
		$data['lesson_count'] =  $total_lesson = $lessonquizcount['lessoncount'];
		$data['quiz_count'] = $total_quiz = $lessonquizcount['quizcount'];
			
        $data['total_hour_count'] = $this->studentcourse_model->counthours($courseID);
        $data['coursesList']      = $this->course_model->singlecourselist($courseID);
        $data['paidstatus']       = $this->courseofflinepayment_model->paidstatus($courseID, $userid);
        $data['courseprogresscount'] = $lessonquizcount['courseprogresscount'];
		
        $sectionList              = $this->coursesection_model->getsectionbycourse($courseID);
        $data['sectionList']      = $sectionList;

        $lessonquizlist_array = array();
        if (!empty($sectionList)) {
            foreach ($sectionList as $sectionList_value) {
                $lessonquizlist_array[$sectionList_value->id] = $this->coursesection_model->lessonquizbysection($sectionList_value->id);
            }
            $data['lessonquizdetail'] = $lessonquizlist_array;
        } else {
            $data['lessonquizdetail'] = '';
        }
		$data['loginsession'] = $this->session->userdata('student');
		$data['paymentgateway']         	= $this->paymentsetting_model->getActiveMethod();
        $this->load->view('user/studentcourse/_coursedetail', $data);
    }

    /*
    This is used for purpose of download course in pdf, txt,.doc format
     */
    public function download($doc, $section_id, $lesson_id)
    {
        $this->load->helper('download');
        $filepath = "./uploads/course_content/" . $section_id . "/" . $lesson_id . "/" . $doc;
        $data     = file_get_contents($filepath);
        $name     = $doc;
        force_download($name, $data);
    }

    /*
    This is used to get result for single student
     */
    public function getresult()
    {
        $userid           = $this->result["student_id"];
        $courseid         = $this->input->post('courseid');
        $data['courseid'] = $courseid;
        $status = $this->input->post('status');
        $quizID     = $this->input->post('quizID');
        $questionID = $this->input->post('question_id');
        $answer1    = $this->input->post('answer_1');
        $answer2    = $this->input->post('answer_2');
        $answer3    = $this->input->post('answer_3');
        $answer4    = $this->input->post('answer_4');
        $answer5    = $this->input->post('answer_5');

        if (!empty($answer1)) {
            $answer1 = 'option_1';
        }
        if (!empty($answer2)) {
            $answer2 = 'option_2';
        }
        if (!empty($answer3)) {
            $answer3 = 'option_3';
        }
        if (!empty($answer4)) {
            $answer4 = 'option_4';
        }
        if (!empty($answer5)) {
            $answer5 = 'option_5';
        }

        $questioncount         = $this->studentcourse_model->getquestioncount($quizID);
        $data['questioncount'] = $questioncount;

        $correctAnswer   = array($answer1, $answer2, $answer3, $answer4, $answer5);
        $options         = array('option_1', 'option_2', 'option_3', 'option_4', 'option_5');
        $data['options'] = $options;

        $addData = array(
            'student_id'   => $userid,
            'course_quiz_id'      => $quizID,
            'course_quiz_question_id'  => $questionID,
            'answer'       => json_encode($correctAnswer),
            'created_date' => date('Y-m-d H:i:s'),
        );
        $this->studentcourse_model->addanswer($addData);

        $resultData = array(
            'student_id'   => $userid,
            'course_quiz_id'      => $quizID,
            'status'       => $status,
            'created_date' => date('Y-m-d H:i:s'),
        );
        $lastid        = $this->studentcourse_model->addresult($resultData);
        $studentresult = $this->studentcourse_model->getresult($quizID, $userid);
        $answercount   = array();
        $wronganswer    = array();
        $not_attempted = array();

        if (!empty($studentresult)) {
            foreach ($studentresult as $studentresult_value) {
                $result = '';
                if (!empty($studentresult_value['answer'])) {
                    $submit_answer = json_decode($studentresult_value['answer']);

                foreach ($submit_answer as $key => $submit_answer_value) {
            
                if (array_filter($submit_answer)) {
                    if (!empty($submit_answer_value)) {
                        $key = $key + 1;
                        if ($key == 1) {
                            $result = "option_1,";
                        }if ($key == 2) {
                            $result = $result . "option_2,";
                        }if ($key == 3) {
                            $result = $result . "option_3,";
                        }if ($key == 4) {
                            $result = $result . "option_4,";
                        }if ($key == 5) {
                            $result = $result . "option_5";
                        }
                    }
                    $result = rtrim($result, ',');
                } else {
                    $result = 'empty';
                }
                    }
                    $result = rtrim($result, ',');
                }

                if ($studentresult_value['correct_answer'] == $result) {
                    $answer_value = '1';
                    array_push($answercount, $answer_value);
                }elseif($result == 'empty'){
                }else{
                    $wronganswer_value = '1';
                    array_push($wronganswer, $wronganswer_value);
                }
            }
        }

        $questionlist = $this->studentcourse_model->getallquestion($quizID);
        $data['questionlist'] = $questionlist;
        $answerlist = array();

        foreach ($questionlist as $questionlist_value) {
            $answerlist[$questionlist_value['id']] = $this->studentcourse_model->getanswer($quizID, $questionlist_value['id'] ,$userid); 
        }
        if(!empty($answerlist)){
            $data['answerlist'] = $answerlist;
        }else{
            $data['answerlist'] = '';
        }

        $answercount   = count($answercount);
        $wrong_answer = count($wronganswer);
        $not_attempted = $questioncount['question_count'] - ($answercount + $wrong_answer);

        if (!empty($lastid)) {
            $updateData = array(
                'id'             => $lastid,
                'total_question' => $questioncount['question_count'],
                'correct_answer' => $answercount,
                'wrong_answer'   => $wrong_answer,
                'not_answer'     => $not_attempted,
            );

            $this->studentcourse_model->addresult($updateData);
        }

        $data['answercount']   = $answercount;
        $data['wronganswer']   = $wrong_answer;
        $data['not_attempted'] = $not_attempted;
        $data['quizid']        = $quizID;

        $data['status']        = '';
        $data['studentresult'] = $studentresult;
        $graphdata             = $this->studentcourse_model->checkstatus($quizID, $userid);
        $data['graphdata']     = $graphdata;
        $totalmarks            = $this->quizgraph($courseid, $userid);
        $data['totalmarks']    = $totalmarks['totalmarks'];
        $data['totalquiz']     = $totalmarks['totalquiz'];
        $this->load->view('user/studentcourse/studentresult', $data);
    }

    /*
    This is used to delete previous record of student if he has given exam
     */
    public function reset()
    {
        $userid                 = $this->result["student_id"];
        $courseid               = $this->input->post('courseid');
        $data['courseid']       = $courseid;
        $quizID                 = $this->input->post('quizID');
        $data['singlequizlist'] = $this->studentcourse_model->getsinglequiz($quizID);
        $questionlist           = $this->studentcourse_model->getallquestion($quizID);
        if (!empty($questionlist)) {
            $data['questionlist'] = $questionlist[0];
        } else {
            $data['questionlist'] = '';
        }
        $data['notanswer'] = '';

        $data['questioncount'] = $this->studentcourse_model->getquestioncount($quizID);
        $totalmarks            = $this->quizgraph($courseid, $userid);
        $data['totalmarks']    = $totalmarks['totalmarks'];
        $data['totalquiz']     = $totalmarks['totalquiz'];
        $this->studentcourse_model->remove($quizID, $userid);
        $this->studentcourse_model->removeanswer($quizID, $userid);
        $this->load->view('user/studentcourse/_quizinstruction', $data);
    }

    /*
    This is used to get previous question data
     */
    public function previousdata($previousid, $quizid, $userid)
    {
        $data['answerlist']         = '';
        $data['singlequestionlist'] = '';

        $singlequestionlist         = $this->studentcourse_model->previousquestion($quizid, $previousid);
        $data['singlequestionlist'] = $singlequestionlist;

        $questionexist = $this->studentcourse_model->getpreviousquestiondetail($singlequestionlist['id'], $quizid, $userid);
        $id            = '';
        if (!empty($questionexist)) {
            $id = $questionexist['id'];
        }
        $answerlist         = $this->studentcourse_model->getpreviousanswer($id);
        $data['answerlist'] = $answerlist;
        return $data;
    }

    /*
    This is used to identify question is attempt or not
    */
    public function color($quizid, $userid)
    {
        $data['color'] = '';
        $allanswerlist = $this->studentcourse_model->getallanswer($quizid, $userid);
        foreach ($allanswerlist as $key => $allanswerlist_value) {
            $colors        = '';
            $question_id   = $allanswerlist_value['course_quiz_question_id'];
            $correctanswer = json_decode($allanswerlist_value['answer']);
            if (array_filter($correctanswer)) {
                $colors = 'alert-success';
            } else {
                $colors = 'alert-danger';
            }
            $color[$question_id] = $colors;
        }
        if (!empty($color)) {
            $data['color'] = $color;
        }
        return $data;
    }

    /*
    This is used to update answer of question by answer id
    */
    public function updatedanswer($id, $correctAnswer, $questionID, $quizID, $userid)
    {
        $updateData = array(
            'id'     => $id,
            'answer' => json_encode($correctAnswer),
        );
        $this->studentcourse_model->addanswer($updateData);
        $id                         = $id + 1;
        $singlequestionlist         = $this->studentcourse_model->getsinglequestion($quizID, $questionID);
        $data['singlequestionlist'] = $singlequestionlist;
        $answerlist                 = $this->studentcourse_model->getpreviousanswer($id);
        $data['answerlist']         = $answerlist;
        return $data;
    }

    /**
     * This function is used to mark a lesson as complete
     */

    public function markascomplete()
    {
        $student_id       = $this->result["student_id"];
        $section_id       = $this->input->post("section_id");
        $result           = $this->course_model->coursebysection($section_id);
        $lesson_quiz_type = $this->input->post("lesson_quiz_type");
        $lesson_quiz_id   = $this->input->post("lesson_quiz_id");

        if (!empty($result)) {
            $data = array(

                "student_id"        => $student_id,
                "lesson_quiz_id"    => $this->input->post("lesson_quiz_id"),
                "lesson_quiz_type"  => $this->input->post("lesson_quiz_type"),
                "course_section_id" => $this->input->post("section_id"),
                "course_id"         => $result['id'],
            );

            $is_completed = $this->studentcourse_model->getcourseprogress($result['id'], $student_id, $section_id, $lesson_quiz_type, $lesson_quiz_id);

            if (!empty($is_completed)) {
                $this->studentcourse_model->markAsComplete($data, 0);
            } else {
                $this->studentcourse_model->markascomplete($data, 1);
            }

        } else {
            print_r("not enrolled");
        }
    }

    /**
     * This function is used to get course progress
     */
    public function getcourseprogress()
    {
        $course_id          = $this->input->post("course");
        $student_id         = $this->result["student_id"];
		
		$lessonquizcount = $lessonquizcount =$this->studentcourse_model->lessonquizcountbycourseid($course_id,$student_id);
		$data['lesson_count'] =  $total_lesson = $lessonquizcount['lessoncount'];
		$data['quiz_count'] = $total_quiz = $lessonquizcount['quizcount'];
		$courseprogresscount = $lessonquizcount['courseprogresscount'];
		
        $total_quiz_lession = $total_lesson + $total_quiz;

        if ($total_quiz_lession > 0) {
            $progress = ((count($courseprogress) / $total_quiz_lession)) * 100;
        }
        $data["progress"] = intval($progress); 
        echo json_encode($data);
    }

    /*
    This is used to get course list for datatable
     */
    public function getcourselist()
    {
        $userid             = $this->result["student_id"];
        $currency_symbol    = $this->customlib->getSchoolCurrencyFormat();
        $class_id           = $this->current_classSection->class_id;
        $data['class_id']   = $class_id;
        $section_id         = $this->current_classSection->section_id;

        $data['section_id'] = $section_id;
        $courselist         = $this->studentcourse_model->getcourselist($class_id, $section_id);
        $new_courselist     = array();
        $m                  = json_decode($courselist);
        $dt_data            = array();
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
				$lessonquizcount = $lessonquizcount =$this->studentcourse_model->lessonquizcountbycourseid($value->id,$userid);
		        $total_lesson = $lessonquizcount['lessoncount'];
		        $total_quiz = $lessonquizcount['quizcount'];
		        $courseprogresscount = $lessonquizcount['courseprogresscount'];
                $total_hour_count    = $this->studentcourse_model->counthours($value->id);
                $paidstatus          = $this->courseofflinepayment_model->paidstatus($value->id, $userid);
                $total_quiz_lession  = $total_lesson + $total_quiz;
				
                $course_progress     = 0;
                if ($total_quiz_lession > 0) {
                    $course_progress = (count($courseprogresscount) / $total_quiz_lession) * 100;
                }
                $quiz_count = $this->studentcourse_model->totalquizbycourse($value->id);
                $section_total = $this->coursesection_model->getsectioncount($value->id);

                $free_course    = $value->free_course;
                $discount       = $value->discount;
                $price          = $value->price;

                if ($value->discount != '0.00') {
                    $discount = $value->price - (($value->price * $value->discount) / 100);
                }

                if (($value->free_course == 1) && ($value->price == '0.00')) {
                    $price = 'Free';
                    $discount = 'Free';
                } elseif (($value->free_course == 1) && ($value->price != '0.00')) {
                    if ($value->price > '0.00') {
                        $courseprice = $value->price;
                    } else {
                        $courseprice = "Free";
                    }
                    $price = $courseprice;
                    $discount = "Free";
                } elseif (($value->price != '0.00') && ($value->discount != '0.00')) {
                    $discount = number_format((float) $discount, 2, '.', '');
                    if ($value->price > '0.00') {
                        $courseprice = $value->price;
                    } else {
                        $courseprice = '';
                    }
                    $price = $courseprice;
                }else{
                    $price = $value->price;
                    $discount = $value->price;
                }

                $viewbtn = "<a  data-toggle='tab' onclick='loadcoursedetail(" . '"' . $value->id . '"' . "  )' class='btn btn-default btn-xs btn-add course_detail_id' data-id=" . $value->id . " data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#course_detail_modal' title=" . $this->lang->line('course_detail') . "><i class='fa fa-reorder'></i></a>";

                $row       = array();
                $row[]     = $value->title;
                $row[]     = $value->class. " (".rtrim($value->section,", ").")";
                $row[]     = $section_total;
                $row[]     = $total_lesson;
                $row[]     = $total_quiz;
                $row[]     = $total_hour_count;
                $row[]     = $price;
                $row[]     = $discount;
                $row[]     = date($this->customlib->getSchoolDateFormat(), strtotime($value->updated_date));
                $row[]     = $viewbtn;
                $dt_data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval($m->draw),
            "recordsTotal"    => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    /*
    This is used to get quiz data for quiz progress graph
    */
    public function quizgraph($courseid, $userid)
    {
        $totalquiz          = $this->studentcourse_model->quizbycourse($courseid);
        $data['totalquiz']  = $totalquiz;
        $data['totalmarks'] = '';

        $totalmarks_array = array();
        foreach ($totalquiz as $totalquiz_value) {
            $totalmarks = $this->studentcourse_model->quizgraph($totalquiz_value->id, $userid);

            if (!empty($totalmarks['total_question']) and  $totalmarks['total_question'] != 0) {
                $marks              = ($totalmarks['right_answer'] * 100) / $totalmarks['total_question'];
                $totalmarks_array[] = number_format((float)$marks, 2, '.', '');			
            }else{
				$totalmarks_array[] = 0 ;
			}
        }

        if (!empty($totalmarks_array)) {
            $data['totalmarks'] = $totalmarks_array;
        }
        return $data;
    }

    /*
    This is used to get quiz list for quiz performance report
    */
    public function quizperformance()
    {
        $userid           = $this->result["student_id"];
        $courseid         = $this->input->post('courseid');
        $data['courseid'] = $courseid;

        // for bar graph start
        $totalmarks         = $this->quizgraph($courseid, $userid);
        $data['totalmarks'] = $totalmarks['totalmarks'];
        $data['quizdata']   = $totalmarks['totalquiz'];
        $data['quizcount']  = count($totalmarks['totalquiz']);
        // end
        // quiz progress start 
        $lessonquizcount = $this->studentcourse_model->lessonquizcountbycourseid($courseid,$userid);
        $data['lesson_count'] =  $total_lesson = $lessonquizcount['lessoncount'];
        $data['quiz_count'] = $total_quiz = $lessonquizcount['quizcount'];       
        $courseprogresscount = $lessonquizcount['courseprogresscount'];
        $total_quiz_lession = $total_lesson + $total_quiz;
        $course_progress    = 0;
        if ($total_quiz_lession > 0) {
            $course_progress = (count($courseprogresscount) / $total_quiz_lession) * 100;
        }
        $data['course_progress'] = intval($course_progress);
        // end
        // for completed status start      
		
		$completedquiz = $this->studentcourse_model->completelessonquizbycourse($courseid,$userid);
		if(!empty($completedquiz['quiz'])){
			$quiz	=	$completedquiz['quiz'];
		}else{
			$quiz	= 0;
		}
		
		if(!empty($completedquiz['lesson'])){
			$lesson	=	$completedquiz['lesson'];
		}else{
			$lesson	= 0;
		}	
		
        $data['completedquiz'] = $quiz;
        $data['completedlesson'] = $lesson;
        // end
        
        $data['quizperformancedata'] = $this->studentcourse_model->quizstatusbycourseid($courseid,$userid);
        $this->load->view('user/studentcourse/_quizperformance', $data);
    }

    /*
    This is used to print course payment detail or it is a invoice
    */
    public function printinvoice() {
        $userid              = $this->result["student_id"];
        $courseid            = $this->input->post('courseid');
        $data['courselist']  = $this->courseofflinepayment_model->courseprint($courseid,$userid);
        $setting_result      = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('user/studentcourse/print/printinvoice', $data);
    } 
}