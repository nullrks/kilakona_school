<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Coursereport extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
		$this->load->model(array('course_model','coursesection_model','courselesson_model','studentcourse_model','coursequiz_model','course_payment_model','courseofflinepayment_model','coursereport_model'));
        $this->auth->addonchk('ssoclc', site_url('onlinecourse/course/setting'));
    }

    /*
    This is used to show course report page
    */
    public function coursepurchase()
    {
		if (!$this->rbac->hasPrivilege('student_course_purchase_report', 'can_view')) {
            access_denied();
        }

		$this->session->set_userdata('top_menu', 'onlinecourse');
        $this->session->set_userdata('sub_menu', 'onlinecourse/coursereport/report');
        $this->session->set_userdata('subsub_menu', 'onlinecourse/coursereport/coursepurchase');		
		
        $payment_type = array(
            'all'     => $this->lang->line('all'),
            'Online'  => $this->lang->line('online'),
            'Offline' => $this->lang->line('offline'),
        );
        $data['payment_type'] = $payment_type;
        $data['searchlist']   = $this->customlib->get_searchtype();

        $this->load->view('layout/header');
        $this->load->view('onlinecourse/report/coursepurchase', $data);
        $this->load->view('layout/footer');
    }

    /*
    This is used to check validation for search form
    */
    public function checkvalidation()
    {
        $search = $this->input->post('search');
        $this->form_validation->set_rules('search_type', $this->lang->line('search_type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('payment_type', $this->lang->line('payment_type'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $msg = array(
                'search_type'  => form_error('search_type'),
                'payment_type' => form_error('payment_type'),
            );
            $json_array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $param = array(
                'search_type'  => $this->input->post('search_type'),
                'payment_type' => $this->input->post('payment_type'),
                'date_from'    => $this->input->post('date_from'),
                'date_to'      => $this->input->post('date_to'),
            );
           
            $json_array = array('status' => 'success', 'error' => '', 'param' => $param, 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($json_array);
    }

    /*
    This is used to get course list by class section id and student id
    */
    public function coursereport()
    {        
        $search['search_type']  = $this->input->post('search_type');
        $search['payment_type'] = $this->input->post('payment_type');
        $search['date_from']    = $this->input->post('date_from');
        $search['date_to']      = $this->input->post('date_to');;
        $start_date             = '';
        $end_date               = '';        

        if ($search['search_type'] == 'period') {
			
            $start_date = date('Y-m-d',$this->customlib->datetostrtotime($search['date_from']));           
            $end_date = date('Y-m-d',$this->customlib->datetostrtotime($search['date_to']));
            
        } else { 
 
            if (isset($search['search_type']) && $search['search_type'] != '') {
                $dates               = $this->customlib->get_betweendate($search['search_type']);
                $data['search_type'] = $search['search_type'];
            } else {
                $dates               = $this->customlib->get_betweendate('this_year');
                $data['search_type'] = '';
            }
            
            $start_date =  date('Y-m-d', strtotime($dates['from_date']));           
            $end_date = date('Y-m-d', strtotime($dates['to_date']));  
          
        }  
       
        $coursedata = $this->coursereport_model->coursereport($search['payment_type'], $start_date, $end_date);
     
        $coursedata = json_decode($coursedata);
        $dt_data    = array();
        if (!empty($coursedata->data)) {
            $doc = "";
            foreach ($coursedata->data as $key => $value) {
                $student_name = $value->firstname . ' ' . $value->lastname;
                $row   = array();
                
                $row[] = $student_name;
                $row[] = $value->admission_no;
                $row[] = date($this->customlib->getSchoolDateFormat(), strtotime($value->date));
                $row[] = $value->title;
                $row[] = $this->lang->line($value->course_provider);                
                $row[] = $value->payment_type;              
                if ($value->payment_type == 'Online') {
                    $row[] = $value->payment_mode . ' (' . $this->lang->line('txn_id') . ' - ' . $value->transaction_id . ')';
                } else {
                    $row[] = $value->payment_mode;
                }

                $row[] = $value->paid_amount;  
				$dt_data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval($coursedata->draw),
            "recordsTotal"    => intval($coursedata->recordsTotal),
            "recordsFiltered" => intval($coursedata->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    /*
    This is used to show top selling course list
    */
    public function coursesellreport()
    {
		if (!$this->rbac->hasPrivilege('course_sell_count_report', 'can_view')) {
            access_denied();
        }        
		
		$this->session->set_userdata('top_menu', 'onlinecourse');
        $this->session->set_userdata('sub_menu', 'onlinecourse/coursereport/report');
        $this->session->set_userdata('subsub_menu', 'onlinecourse/coursereport/coursesellreport');		
        $this->load->view('layout/header');
        $this->load->view('onlinecourse/report/coursesellreport');
        $this->load->view('layout/footer');
    }

    /*
    This is used to show top trending course list
    */
    public function trendingreport()
    {
		if (!$this->rbac->hasPrivilege('course_trending_report', 'can_view')) {
            access_denied();
        }		
		$this->session->set_userdata('top_menu', 'onlinecourse');
        $this->session->set_userdata('sub_menu', 'onlinecourse/coursereport/report');
        $this->session->set_userdata('subsub_menu', 'onlinecourse/coursereport/trendingreport');		
        $this->load->view('layout/header');
        $this->load->view('onlinecourse/report/coursetrending');
        $this->load->view('layout/footer');
    }

    /*
    This is used to show student list by purchasing course
    */
    public function saledata()
    {
        $courseid            = $this->input->post('courseid');
        $data['coursename']          = $this->input->post('coursename');
        $data['courseid']= $courseid ;
        $this->load->view('onlinecourse/report/_selllist',$data);
    }

    /*
    This is used to get seller data by course id
    */
     public function getsalelist($courseid)
    {
        $m = $this->coursereport_model->studentdata($courseid);
        $m       = json_decode($m);
        $dt_data = array();
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                
                $row       = array();
                $row[]     = $value->firstname.' '.$value->lastname;
                $row[]     = $value->admission_no;
                $row[]     = date($this->customlib->getSchoolDateFormat(), strtotime($value->date));
                $row[]     = $value->paid_amount;                
               
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
    This is used to get course data
    */
    public function getcourselist()
    {
        $m       = $this->coursereport_model->trendingreport();
        $m       = json_decode($m);
        $dt_data = array();
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $free_course    = $value->free_course;
                $discount       = $value->discount;
                $price          = $value->price;
                $discount_price = '';
                $price          = '';

                if (!empty($value->discount)) {
                    $discount = $value->price - (($value->price * $value->discount) / 100);
                }

                if (($value->free_course == 1) && (empty($value->price))) {
                    $price = 'Free';
                } elseif (($value->free_course == 1) && (!empty($value->price))) {
                    if ($value->price > '0.00') {
                        $courseprice = $value->price;
                    } else {
                        $courseprice = '';
                    }
                    $price = "Free";
                } elseif (!empty($value->price) && (!empty($value->discount))) {
                    $discount = number_format((float) $discount, 2, '.', '');
                    if ($value->price > '0.00') {
                        $courseprice = $value->price;
                    } else {
                        $courseprice = '';
                    }
                    $price = $courseprice;
                } else {
                    $price = $value->price;
                }
                
                $multipalsection  = $this->course_model->multipalsection($value->id);
                
                $section       = "";
                $store_section = array();
                foreach ($multipalsection as $multipalsection_value) {
                    if (!in_array($multipalsection_value['section'], $store_section)) {
                        $store_section[] = $multipalsection_value['section'];
                        $section .= $multipalsection_value['section'] . ", ";
                    }
                }
                
                $row       = array();
                $row[]     = $value->title;
                $row[]     = $value->class;
                $row[]     = rtrim($section,", ");
                $row[]     = $value->view_count;
                $row[]     = $value->assign_name . ' ' . $value->assign_surname. ' ('.$value->assign_employee_id.')';
                $row[]     = $value->name . ' ' . $value->surname. ' ('.$value->employee_id.')' ;
                $row[]     = $price;
                $row[]     = $discount;
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
    This is used to get seller data for report
    */
    public function getsellreport()
    {
        $m       = $this->coursereport_model->sellreport();
        $m       = json_decode($m);
        $dt_data = array();
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                
                $multipalsection  = $this->course_model->multipalsection($value->online_courses_id);
                $sellcount  = $this->course_model->coursesellcount($value->online_courses_id);
                
                $section       = "";
                $store_section = array();
                foreach ($multipalsection as $multipalsection_value) {
                    if (!in_array($multipalsection_value['section'], $store_section)) {
                        $store_section[] = $multipalsection_value['section'];
                        $section .= $multipalsection_value['section'] . ", ";
                    }
                }
                
                $btn       = "<button type='button' class='btn btn-default btn-xs' data-id=" . $value->title . " course-data-id=" . $value->online_courses_id . " title=". $this->lang->line('view') ." data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#sale_modal' onclick='loadcoursedetail(" . '"' . $value->online_courses_id . '"' . ',' . '"' . $value->title . '"' . "  )'  data-original-title='' title='' autocomplete='off'><i class='fa fa-reorder'></i> </button>";
                $row       = array();
                $row[]     = $value->title;
                $row[]     = $value->class;
                $row[]     = rtrim($section,", ");
                $row[]     = count($sellcount);
                $row[]     = $value->assign_name . ' ' . $value->assign_surname. ' ('.$value->assign_employee_id.')';
                $row[]     = $value->name . ' ' . $value->surname. ' ('.$value->employee_id.')';
                $row[]     = $btn;
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
    This is used to show top selling course list
    */
    public function report()
    {		
        $this->session->set_userdata('top_menu', 'onlinecourse');
        $this->session->set_userdata('sub_menu', 'onlinecourse/coursereport/report');          
        $this->session->set_userdata('subsub_menu', '');		
        $this->load->view('layout/header');
        $this->load->view('onlinecourse/report/report');
        $this->load->view('layout/footer');
    }

    /*
    This is used to show course complete report
    */
    public function completereport()
    {
		if (!$this->rbac->hasPrivilege('course_complete_report', 'can_view')) {
            access_denied();
        }		
		$this->session->set_userdata('top_menu', 'onlinecourse');
        $this->session->set_userdata('sub_menu', 'onlinecourse/coursereport/report');
        $this->session->set_userdata('subsub_menu', 'onlinecourse/coursereport/completereport');		
        $data['student_id'] = '';
        $data['classlist']  = $this->class_model->get();
        $this->load->view('layout/header');
        $this->load->view('onlinecourse/report/coursecompletereport',$data);
        $this->load->view('layout/footer');
    }

    /*
    This is used to check validation for course complete report form
    */
    public function validation()
    {
        $search = $this->input->post('search');
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('course_id', $this->lang->line('course'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $msg = array(
                'class_id'   => form_error('class_id'),
                'section_id' => form_error('section_id'),
                'course_id' => form_error('course_id'),
            );
            $json_array = array('status' => 'fail', 'error' => $msg, 'message' => '');

        } else {
            $class_section_id = $this->input->post('section_id');
            $course_id = $this->input->post('course_id');
            $params      = array('class_section_id' => $class_section_id , 'course_id' => $course_id );

            $json_array = array('status' => 'success', 'error' => '', 'params' => $params);
        }
        echo json_encode($json_array);
    }

    /*
    This is used to get course list by class and section
    */
    public function courselist()
    {
        $class_section_id    = $this->input->post('class_section_id');
        $courselist = $this->coursereport_model->courselist($class_section_id);
        echo json_encode($courselist);
    }

    /*
    This is used to get course list by class section id and student id
    */
    public function coursecompletelist()
    {        
        $course_id = $this->input->post('course_id');
        $class_section_id = $this->input->post('class_section_id');
        $studentdata = $this->coursereport_model->coursecompletereport($class_section_id);
       
        $studentdata = json_decode($studentdata);
		
        $dt_data    = array();
        if (!empty($studentdata->data)) {
            $doc = "";
            foreach ($studentdata->data as $key => $value) {
            $student_name = $value->firstname . ' ' . $value->lastname;              
			$lessonquizcount 		= 	$this->studentcourse_model->lessonquizcountbycourseid($course_id,$value->id);		
            $total_quiz_lession 	= 	$lessonquizcount['quizcount'] + $lessonquizcount['lessoncount'];
			
            $course_progress    = 0;
            if ($total_quiz_lession > 0) {
                $course_progress = (count($lessonquizcount['courseprogresscount']) / $total_quiz_lession) * 100;
            }

                $row   = array();                
                $row[] = $student_name;
                $row[] = $value->admission_no;
                $row[] = intval($course_progress);
                $row[] = '<a data-backdrop="static" target="_blank" class="btn btn-primary pull-right btn-xs performance_btn" href="' . base_url() . "onlinecourse/coursereport/quizperformance/" . $value->id . "/" . $course_id . '"
				><i class="fa fa-moneys"></i> ' . $this->lang->line("course_performance") . '</a>';

                $dt_data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval($studentdata->draw),
            "recordsTotal"    => intval($studentdata->recordsTotal),
            "recordsFiltered" => intval($studentdata->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    /*
    This is used to get quiz list for quiz performance report
    */
    public function quizperformance()
    {
		$userid           = $this->uri->segment(4);
        $courseid         = $this->uri->segment(5);
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
			$data['completedquiz'] = $completedquiz['quiz'];
		}else{
			$data['completedquiz'] = 0;
		}
		
		if(!empty($completedquiz['lesson'])){		
			$data['completedlesson'] = $completedquiz['lesson'];
		}else{
			$data['completedlesson'] = 0;
		}       
        // end
        
        $data['quizperformancedata'] = $this->studentcourse_model->quizstatusbycourseid($courseid,$userid);		
		$doughnut_chart=array();  
   
		if(!empty($data['quizperformancedata'])){
			foreach ($data['quizperformancedata'] as $quiz_key => $quiz_value) {
				$a=array();
                
				$a[]=array('value'=>$quiz_value['correct_answer'],'color'=>"#52d726", 'highlight'=> "#36a2eb", 'label'=> $this->lang->line('correct_answer'));
				$a[]=array('value'=>$quiz_value['wrong_answer'],'color'=>"#f93939", 'highlight'=> "#73c8b8", 'label'=> $this->lang->line('wrong_answer'));
				$a[]=array('value'=>$quiz_value['not_answer'],'color'=>"#c9cbcf", 'highlight'=> "#3bb9ab", 'label'=> $this->lang->line('not_attempted'));
				$doughnut_chart[]=$a;
            }
        }


		$data['graph_data']=($doughnut_chart);

        //==============
		$this->load->view('layout/header');
        $this->load->view('onlinecourse/report/_quizperformance', $data);
        $this->load->view('layout/footer');
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
				$totalmarks_array[] = '';
			}
        }
        if (!empty($totalmarks_array)) {
            $data['totalmarks'] = $totalmarks_array;
        }
        return $data;
    }
}