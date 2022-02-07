<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Courselesson extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
		$this->load->model(array('course_model','coursesection_model','courselesson_model','studentcourse_model','coursequiz_model','course_payment_model','courseofflinepayment_model','coursereport_model'));
        $this->auth->addonchk('ssoclc', site_url('onlinecourse/course/setting'));
        $this->load->model('course_model');
        $this->load->library('aws3');
		$this->load->helper('course');
    }

    /*
    This is used to add lesson
     */
    public function addlesson()
    {
		if (!$this->rbac->hasPrivilege('online_course_lesson', 'can_add')) {
            access_denied();
        }
        $this->form_validation->set_rules('title', $this->lang->line('title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('lesson_type', $this->lang->line('lesson_type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('add_lesson_thumbnail', $this->lang->line('thumbnail') . ' ' . $this->lang->line('field_is_required'), 'callback_handle_upload[add_lesson_thumbnail]');
        $lesson_type     = $this->input->post('lesson_type');
        $lesson_provider = $this->input->post('lesson_provider');
        if ($lesson_type == 'pdf') {
            $this->form_validation->set_rules('lesson_attachment', '', 'callback_pdf_handle_upload');
        } elseif ($lesson_type == 'document') {
            $this->form_validation->set_rules('lesson_attachment', $this->lang->line('lesson_attachment'), 'callback_file_check');
        } elseif ($lesson_type == 'text') {
            $this->form_validation->set_rules('lesson_attachment', $this->lang->line('lesson_attachment'), 'callback_text_check');
        } elseif ($lesson_type == 'video') {
            if ($lesson_provider == "s3_bucket") {
                /* File validation code goes here */
            } else {
                $this->form_validation->set_rules('lesson_url', $this->lang->line('video_url'), 'trim|required|xss_clean');
            }
            $this->form_validation->set_rules('lesson_duration', $this->lang->line('duration'), array('required', array('check_exists', array($this->courselesson_model, 'validateduration'))));
        } else {
            $this->form_validation->set_rules('lesson_url', $this->lang->line('video_url'), 'trim|required|xss_clean');

        }
        if ($this->form_validation->run() == false) {
            if ($lesson_type == 'pdf') {
                $msg = array(
                    'lesson_attachment'    => form_error('lesson_attachment'),
                    'title'                => form_error('title'),
                    'lesson_type'          => form_error('lesson_type'),
                    'add_lesson_thumbnail' => form_error('add_lesson_thumbnail'),
                );
            } elseif ($lesson_type == 'document') {
                $msg = array(
                    'lesson_attachment'    => form_error('lesson_attachment'),
                    'title'                => form_error('title'),
                    'lesson_type'          => form_error('lesson_type'),
                    'add_lesson_thumbnail' => form_error('add_lesson_thumbnail'),
                );
            } elseif ($lesson_type == 'text') {
                $msg = array(
                    'lesson_attachment'    => form_error('lesson_attachment'),
                    'title'                => form_error('title'),
                    'lesson_type'          => form_error('lesson_type'),
                    'add_lesson_thumbnail' => form_error('add_lesson_thumbnail'),
                );
            } elseif ($lesson_type == 'video') {
                $msg = array(
                    'title'                => form_error('title'),
                    'lesson_type'          => form_error('lesson_type'),
                    'lesson_duration'      => form_error('lesson_duration'),
                    'add_lesson_thumbnail' => form_error('add_lesson_thumbnail'),
                );
                if ($lesson_provider == "s3_bucket") {
                    $msg['lesson_file'] = form_error('lesson_file');
                } else {
                    $msg['lesson_url'] = form_error('lesson_url');
                }
            } else {
                $msg = array(
                    'title'                => form_error('title'),
                    'lesson_type'          => form_error('lesson_type'),
                    'add_lesson_thumbnail' => form_error('add_lesson_thumbnail'),
                );
            }
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $section_id  = $this->input->post('add_lesson_section_id');
            $sectionData = array(
                'lesson_title'      => $this->input->post('title'),
                'course_section_id' => $section_id,
                'lesson_type'       => $lesson_type,
                'summary'           => $this->input->post('summary'),
                'created_date'      => date('Y-m-d h:i:s'),
            );
            // This is used to add lesson
            $insert_id = $this->courselesson_model->addlesson($sectionData);

            $orderData = array(
                'type'        => 'lesson',
                'course_section_id' => $section_id,
                'lesson_quiz_id' => $insert_id
            );
            $this->coursesection_model->addlessonquizorder($orderData);

            $updatecourse = array(
                'updated_date' => date("Y-m-d h:i:s"),
                'id'           => $this->input->post('lesson_course_id'),
            );
            $this->course_model->add($updatecourse);
            
            // This is used to create new directory
            $directory = FCPATH . '/uploads/course_content/' . $section_id . '/' . $insert_id;

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            if ($lesson_type == "text" || $lesson_type == "pdf" || $lesson_type == "document") {
                $lesson_attachment_image = '';
                if (!empty($_FILES['lesson_attachment']['name'])) {
                    $ext                     = pathinfo($_FILES['lesson_attachment']['name'], PATHINFO_EXTENSION);
                    $config['upload_path']   = "uploads/course_content/". $section_id . "/" . $insert_id;
                    $config['allowed_types'] = $ext;
                    $file_name               = $_FILES['lesson_attachment']['name'];
                    $config['file_name']     = $insert_id;
                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);
                    if ($this->upload->do_upload('lesson_attachment')) {
                        $uploadData              = $this->upload->data();
                        $lesson_attachment_image = $uploadData['file_name'];
                    }
                }
                $upload_attachment_data = array('id' => $insert_id, 'attachment' => $lesson_attachment_image);
                // This is used to add lesson attachment
                $this->courselesson_model->addlesson($upload_attachment_data);
            } else {

                $videoData = array(
                    'id'             => $insert_id,
                    'video_provider' => $this->input->post('lesson_provider'),
                    'duration'       => $this->input->post('lesson_duration'),
                );
                if ($lesson_provider == "s3_bucket") {
                    if (isset($_FILES['lesson_file'])) {
                        $file_name          = $_FILES['lesson_file']['name'];
                        $temp_file_location = $_FILES['lesson_file']['tmp_name'];
                        $url                = $this->aws3->uploadFile($file_name, $temp_file_location);
                        $getVideoUrl        = $_FILES['lesson_file']['name'];
                    }
                    $videoData['video_id'] = $getVideoUrl;
                } else {
					
					if ($lesson_provider == 'youtube') {
						$lesson_url = $this->input->post('lesson_url');
						$video_id = youtubeID($lesson_url);
					} elseif ($lesson_provider == 'html5') {
						$lesson_url = $this->input->post('lesson_url');
					} elseif ($lesson_provider == 'vimeo') {
						$lesson_url = $this->input->post('lesson_url');
						$video_id = vimeoID($lesson_url);
					} else {
						$lesson_url = "";
						$video_id   = "";
					}
				
                    $videoData['video_url'] = $lesson_url;
                    $videoData['video_id']  = $video_id;
                }
                // This is used to add lesson video
                $this->courselesson_model->addlesson($videoData);
            }
            if (!empty($_FILES['add_lesson_thumbnail']['name'])) {
                $ext                     = pathinfo($_FILES['add_lesson_thumbnail']['name'], PATHINFO_EXTENSION);
                $config['upload_path']   = "uploads/course_content/". $section_id . "/" . $insert_id;
                $config['allowed_types'] = $ext;
                $file_name               = $_FILES['add_lesson_thumbnail']['name'];
                $config['file_name']     = $insert_id;
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if ($this->upload->do_upload('add_lesson_thumbnail')) {
                    $uploadData      = $this->upload->data();
                    $thumbnail_image = $uploadData['file_name'];
                } else {
                    $thumbnail_image = '';
                }
            } else {
                $thumbnail_image = '';
            }
            $upload_data = array('id' => $insert_id, 'thumbnail' => $thumbnail_image);
            // This is used to add lesson thumbnail
            $this->courselesson_model->addlesson($upload_data);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    /*
    This is used to get single lesson list
     */
    public function singlelessondetail()
    {
        $data['course_id']   = $this->input->post('courseID');
        $lessonID            = $this->input->post('lessonID');
        $getsinglelessondata = $this->courselesson_model->singlelessondetail($lessonID);
        if (!empty($getsinglelessondata)) {
            echo json_encode($getsinglelessondata);
        }
    }

    /*
    This is used to edit lesson
     */
    public function editlesson()
    {	
		if (!$this->rbac->hasPrivilege('online_course_lesson', 'can_edit')) {
            access_denied();
        }
        $lesson_thumbnail = $_FILES['lesson_thumbnail']['name'];
        $this->form_validation->set_rules('lesson_titleID', $this->lang->line('title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('lessons_type', $this->lang->line('lesson_type'), 'trim|required|xss_clean');
        if ($lesson_thumbnail != '') {
            $this->form_validation->set_rules('lesson_thumbnail', $this->lang->line('thumbnail') . ' ' . $this->lang->line('field_is_required'), 'callback_handle_upload[lesson_thumbnail]');
        }
        $lessons_type    = $this->input->post('lessons_type');
        $lesson_provider = $this->input->post('lesson_provider');
        if ($lessons_type == 'pdf') {
            $this->form_validation->set_rules('lesson_attachment', $this->lang->line('lesson_attachment'), 'callback_edit_handle_upload');
        } elseif ($lessons_type == 'document') {
            $this->form_validation->set_rules('lesson_attachment', $this->lang->line('lesson_attachment'), 'callback_edit_file_check');
        } elseif ($lessons_type == 'text') {
            $this->form_validation->set_rules('lesson_attachment', $this->lang->line('lesson_attachment'), 'callback_edit_text_check');
        } elseif ($lessons_type == 'video') {
            if ($lesson_provider == "s3_bucket") {
                /* File validation code goes here */
            } else {
                $this->form_validation->set_rules('lesson_url', $this->lang->line('video_url'), 'trim|required|xss_clean');
            }
            $this->form_validation->set_rules('lesson_duration', $this->lang->line('duration'), array('required', array('check_exists', array($this->courselesson_model, 'validateduration'))));
        } else {
            $this->form_validation->set_rules('lesson_url', $this->lang->line('video_url'), 'trim|required|xss_clean');
        }
        if ($this->form_validation->run() == false) {
            if ($lessons_type == 'pdf') {
                $msg = array(
                    'lesson_attachment' => form_error('lesson_attachment'),
                    'lesson_titleID'    => form_error('lesson_titleID'),
                    'lessons_type'      => form_error('lessons_type'),
                    'lesson_thumbnail'  => form_error('lesson_thumbnail'),
                );
            } elseif ($lessons_type == 'document') {
                $msg = array(
                    'lesson_attachment' => form_error('lesson_attachment'),
                    'lesson_titleID'    => form_error('lesson_titleID'),
                    'lessons_type'      => form_error('lessons_type'),
                    'lesson_thumbnail'  => form_error('lesson_thumbnail'),
                );
            } elseif ($lessons_type == 'text') {
                $msg = array(
                    'lesson_attachment' => form_error('lesson_attachment'),
                    'lesson_titleID'    => form_error('lesson_titleID'),
                    'lessons_type'      => form_error('lessons_type'),
                    'lesson_thumbnail'  => form_error('lesson_thumbnail'),
                );
            } elseif ($lessons_type == 'video') {
                $msg = array(
                    'lesson_titleID'   => form_error('lesson_titleID'),
                    'lessons_type'     => form_error('lessons_type'),
                    'lesson_duration'  => form_error('lesson_duration'),
                    'lesson_thumbnail' => form_error('lesson_thumbnail'),
                );
                if ($lesson_provider == "s3_bucket") {
                    $msg['lesson_file'] = form_error('lesson_file');
                } else {
                    $msg['lesson_url'] = form_error('lesson_url');
                }
            } else {
                $msg = array(
                    'lesson_titleID'   => form_error('lesson_titleID'),
                    'lessons_type'     => form_error('lessons_type'),
                    'lesson_thumbnail' => form_error('lesson_thumbnail'),
                );
            }
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $lessonID    = $this->input->post('lessons_id');
            $sectionData = array(
                'id'                => $lessonID,
                'course_section_id' => $this->input->post('lesson_section_id'),
                'lesson_title'      => $this->input->post('lesson_titleID'),
                'lesson_type'       => $this->input->post('lessons_type'),
                'summary'           => $this->input->post('lessons_summary'),
            );

            $updatecourse = array(
                'updated_date' => date("Y-m-d h:i:s"),
                'id'           => $this->input->post('edit_lesson_course_id'),
            );
            $this->course_model->add($updatecourse);

            // This is used to create new directory
            $directory = FCPATH . '/uploads/course_content/' . $this->input->post('lesson_section_id') . '/' . $lessonID;

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            if ($lessons_type == "text" || $lessons_type == "pdf" || $lessons_type == "document") {
                if (!empty($_FILES['lesson_attachment']['name'])) {
                    $ext                     = pathinfo($_FILES['lesson_attachment']['name'], PATHINFO_EXTENSION);
                    $config['upload_path']   = "uploads/course_content/". $this->input->post('lesson_section_id') . "/" . $lessonID;
                    $config['allowed_types'] = $ext;
                    $file_name               = $_FILES['lesson_attachment']['name'];
                    $config['file_name']     = $lessonID;
                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);
                    if ($this->upload->do_upload('lesson_attachment')) {
                        $uploadData              = $this->upload->data();
                        $lesson_attachment_image = $uploadData['file_name'];
                    } else {
                        $lesson_attachment_image = $this->input->post('old_attachment_img');
                    }
                } else {
                    $lesson_attachment_image = $this->input->post('old_attachment_img');
                }
                $upload_attachment_data = array('id' => $lessonID, 'attachment' => $lesson_attachment_image);
                // This is used to edit lesson attachment
                $this->courselesson_model->addlesson($upload_attachment_data);
            } else {
                $videoData = array(
                    'id'             => $lessonID,
                    'video_provider' => $this->input->post('lesson_provider'),
                    'video_url'      => $this->input->post('lesson_url'),
                    'duration'       => $this->input->post('lesson_duration'),
                );
                if ($lesson_provider == "s3_bucket") {
                    if (isset($_FILES['lesson_file']) && $_FILES['lesson_file']['name'] != '') {
                        $file_name             = $_FILES['lesson_file']['name'];
                        $temp_file_location    = $_FILES['lesson_file']['tmp_name'];
                        $url                   = $this->aws3->uploadFile($file_name, $temp_file_location);
                        $getVideoUrl           = $_FILES['lesson_file']['name'];
                        $videoData['video_id'] = $getVideoUrl;
                    }
                } else {                    
                    
                    if ($lesson_provider == 'youtube') {
						$lesson_url = $this->input->post('lesson_url');
						$video_id = youtubeID($lesson_url);
					} elseif ($lesson_provider == 'html5') {
						$lesson_url = $this->input->post('lesson_url');
					} elseif ($lesson_provider == 'vimeo') {
						$lesson_url = $this->input->post('lesson_url');
						$video_id = vimeoID($lesson_url);
					} else {
						$lesson_url = "";
						$video_id   = "";
					}
				
                    $videoData['video_url'] = $lesson_url;
                    $videoData['video_id']  = $video_id;
                }

                // This is used to edit lesson video
                $this->courselesson_model->addlesson($videoData);
            }

            if (!empty($_FILES['lesson_thumbnail']['name'])) {
                $ext                     = pathinfo($_FILES['lesson_thumbnail']['name'], PATHINFO_EXTENSION);
				$config['upload_path']   = "uploads/course_content/". $this->input->post('lesson_section_id') . "/" . $lessonID;
                $config['allowed_types'] = $ext;
                $file_name               = $_FILES['lesson_thumbnail']['name'];
                $config['file_name']     = $lessonID;
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if ($this->upload->do_upload('lesson_thumbnail')) {
                    $uploadData      = $this->upload->data();
                    $thumbnail_image = $uploadData['file_name'];
                } else {
                    $thumbnail_image = $this->input->post('old_background');
                }
            } else {
                $thumbnail_image = $this->input->post('old_background');
            }
            // This is used to edit lesson
            $this->courselesson_model->addlesson($sectionData);
            $upload_data = array('id' => $lessonID, 'thumbnail' => $thumbnail_image);
            // This is used to edit lesson thumbnail
            $this->courselesson_model->addlesson($upload_data);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    /*
    This is used to delete lesson
     */
    public function deletelesson()
    {	
		if (!$this->rbac->hasPrivilege('online_course_lesson', 'can_delete')) {
            access_denied();
        }
        $lessonID = $this->input->post('lessonID');
        if (!empty($lessonID)) {
            // This is used to delete lesson
            $this->coursesection_model->deletequizlesson($lessonID,'lesson');
            $this->courselesson_model->remove($lessonID);
            $arrays = array('status' => 'success', 'message' => $this->lang->line('delete_message'));
            echo json_encode($arrays);
        } else {
            $arrays = array('status' => 'success', 'error' => $this->lang->line('some_thing_went_wrong'), 'message' => '');
            echo json_encode($arrays);
        }
    }

    /*
    This is used for thumbnail validation
     */
    public function handle_upload($var, $name)
    {
        $image_validate = $this->config->item('image_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES[$name]) && !empty($_FILES[$name]["name"])) {

            $file_type = $_FILES[$name]['type'];
            $file_size = $_FILES[$name]["size"];
            $file_name = $_FILES[$name]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->image_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->image_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = @getimagesize($_FILES[$name]['tmp_name'])) {

                if (!in_array($files['mime'], $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', 'File Type Not Allowed');
                    return false;
                }

                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', 'Extension Not Allowed');
                    return false;
                }
                if ($file_size > $result->image_size) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($image_validate['upload_size'] / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', "File Type / Extension Error Uploading  Image");
                return false;
            }

            return true;
        } else {
            $this->form_validation->set_message('handle_upload', $this->lang->line('the_file_field_is_required'));
            return false;
        }
    }

    /*
    This is used to add lesson pdf file thumbnail validation
     */
    public function pdf_handle_upload()
    {
        if (isset($_FILES["lesson_attachment"]) && !empty($_FILES['lesson_attachment']['name'])) {
            $allowedExts = array("pdf");
            $temp        = explode(".", $_FILES["lesson_attachment"]["name"]);
            $extension   = end($temp);
            if ($_FILES["lesson_attachment"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if (($_FILES["lesson_attachment"]["type"] != "application/pdf")) {
                $this->form_validation->set_message('pdf_handle_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('pdf_handle_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            return true;
        } else {
            $this->form_validation->set_message('pdf_handle_upload', $this->lang->line('attachment_field_is_required'));
            return false;
        }
    }

    /*
    This is used to edit lesson pdf file thumbnail validation
     */
    public function edit_handle_upload()
    {
        if (isset($_FILES["lesson_attachment"]) && !empty($_FILES['lesson_attachment']['name'])) {
            $allowedExts = array("pdf");
            $temp        = explode(".", $_FILES["lesson_attachment"]["name"]);
            $extension   = end($temp);
            if ($_FILES["lesson_attachment"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if (($_FILES["lesson_attachment"]["type"] != "application/pdf")) {
                $this->form_validation->set_message('edit_handle_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('edit_handle_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            return true;
        }
    }

    /*
    This is used to add lesson doc file thumbnail validation
     */
    public function file_check()
    {
        if (isset($_FILES["lesson_attachment"]) && !empty($_FILES['lesson_attachment']['name'])) {
            $allowedExts = array("doc", "docx", "pptx", "pptm", "ppt", "xlsx", "xlsm");
            $temp        = explode(".", $_FILES["lesson_attachment"]["name"]);
            $extension   = end($temp);
            if ($_FILES["lesson_attachment"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if (
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.openxmlformats-officedocument.wordprocessingml.document") && 
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.openxmlformats-officedocument.wordprocessingml.document")&&                
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.openxmlformats-officedocument.presentationml.presentation")&& 
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.ms-powerpoint.presentation.macroEnabled.12")&& 
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.ms-powerpoint")&& 
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")&& 
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.ms-excel.sheet.macroEnabled.12")
                ) {
                $this->form_validation->set_message('file_check', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('file_check', $this->lang->line('extension_not_allowed'));
                return false;
            }
            return true;
        } else {
            $this->form_validation->set_message('file_check', $this->lang->line('attachment_field_is_required'));
            return false;
        }
    }

    /*
    This is used to edit lesson doc file thumbnail validation
     */
    public function edit_file_check()
    {
        if (isset($_FILES["lesson_attachment"]) && !empty($_FILES['lesson_attachment']['name'])) {
            $allowedExts = array("doc", "docx", "pptx", "pptm", "ppt", "xlsx", "xlsm");
            $temp        = explode(".", $_FILES["lesson_attachment"]["name"]);
            $extension   = end($temp);
            if ($_FILES["lesson_attachment"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
             if (
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.openxmlformats-officedocument.wordprocessingml.document") && 
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.openxmlformats-officedocument.wordprocessingml.document")&&                
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.openxmlformats-officedocument.presentationml.presentation")&& 
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.ms-powerpoint.presentation.macroEnabled.12")&& 
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.ms-powerpoint")&& 
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")&& 
                ($_FILES["lesson_attachment"]["type"] != "application/vnd.ms-excel.sheet.macroEnabled.12")
                ) {
                $this->form_validation->set_message('edit_file_check', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('edit_file_check', $this->lang->line('extension_not_allowed'));
                return false;
            }
            return true;
        }
    }

    /*
    This is used to add lesson text file thumbnail validation
     */
    public function text_check()
    {
        if (isset($_FILES["lesson_attachment"]) && !empty($_FILES['lesson_attachment']['name'])) {
            $allowedExts = array('txt');
            $temp        = explode(".", $_FILES["lesson_attachment"]["name"]);
            $extension   = end($temp);
            if ($_FILES["lesson_attachment"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if ($_FILES["lesson_attachment"]["type"] !== "text/plain") {
                $this->form_validation->set_message('text_check', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('text_check', $this->lang->line('extension_not_allowed'));
                return false;
            }
            return true;
        } else {
            $this->form_validation->set_message('text_check', $this->lang->line('attachment_field_is_required'));
            return false;
        }
    }

    /*
    This is used to edit lesson text file thumbnail validation
     */
    public function edit_text_check()
    {
        if (isset($_FILES["lesson_attachment"]) && !empty($_FILES['lesson_attachment']['name'])) {
            $allowedExts = array('txt');
            $temp        = explode(".", $_FILES["lesson_attachment"]["name"]);
            $extension   = end($temp);
            if ($_FILES["lesson_attachment"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if ($_FILES["lesson_attachment"]["type"] !== "text/plain") {
                $this->form_validation->set_message('edit_text_check', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('edit_text_check', $this->lang->line('extension_not_allowed'));
                return false;
            }
            return true;
        }
    }
}