<?php $this->load->view('layout/course_css.php'); ?>
<div class="wrapheader">
	<div class="row">
		<div class="col-lg-6 col-md-6 col-sm-8">
			<div class="wraplogo">
				<img src="<?php echo base_url(); ?>uploads/school_content/admin_logo/<?php $this->setting_model->getAdminlogo();?>" alt="<?php echo $this->customlib->getAppName() ?>" />
				<span id="course_title_id"><?php if (!empty($coursesList['title'])) {echo ucfirst($coursesList['title']);}?> </span>
			</div>
		</div>
	
		<div class="col-lg-6 col-md-6 col-sm-4">
			<ul class="wraplist">
				<li>
					<?php if(!empty($quizprogress)){ ?>
					<a type="button" data-toggle="modal" course-data-id="<?php echo $coursesList['id']; ?>" class=" quiz_button quiz_button-align"><?php echo $this->lang->line('course_performance'); ?></a>
					<?php } ?>
				</li>
				<li>
					<a href="#menu-toggle" class="sidebar-closebtn" id="menu-toggle"><i class="fa fa-angle-right icon-rotate"></i></a>
				</li> 
				<li><a type="button" onclick="closevideo()" data-dismiss="modal">&times;</a></li>	
			</ul>	
		</div>	
	</div>		
</div>
<div class="wrapper-modal">
    <div id="sidebar-wrapper">
        <div class="sidebar-nav">
			<?php if($coursesList['free_course'] == '1' || $paidstatus == '1' || (!empty($lessonprogress)) || (!empty($quizprogress))){ 
			?>
			<div class="videoaccordion">				
				<div class="box-group" id="accordion">
					<div class="panel">
					<h4 class="course-content fontmedium"><?php echo $this->lang->line('course_content'); ?></h4>

					<?php if (!empty($sectionList)) {
						$lessoncount=0; $quizcount=0; $sectioncount = 1;
						foreach ($sectionList as $sectionList_key => $sectionList_value) {
					?>

					<?php $sectionID = $sectionList_value->id;?>
						<div class="box-header">
							<h4 class="box-title">
							<a data-toggle="collapse" data-parent="#accordion" href="#course_<?php echo $sectionID; ?>">
							<h5 class="h5section fontmedium"><?php echo $this->lang->line('section'); ?> <?php echo $sectioncount; ?>: <?php echo $sectionList_value->section_title; ?></h5></a>
							</h4>
						</div>
						<div id="course_<?php echo $sectionID; ?>" class="panel-collapse collapse">
							<div class="box-body pt0 pb0">
							<?php
							if (!empty($lessonquizdetail[$sectionID])) {
								foreach ($lessonquizdetail[$sectionID] as $lessonquizdetail_value) { 
							
							if($lessonquizdetail_value['type'] == 'lesson'){
								$lessoncount = $lessoncount+1;
									$lesson_id =	 $lessonquizdetail_value['lesson_id'];							
									$checked = "";
									if($lessonprogress[$lesson_id]){
										$checked = "checked";
									}
								
								if($lessonquizdetail[$sectionID][0]['type'] == 'lesson'){ ?>
								    <input type="hidden" id="type"  value="lesson">
								<?php }else{ ?>
	                                <input type="hidden" id="type"  value="quiz">
								<?php } ?>
								<input type="hidden" id="lessonID"  value="<?php echo $lessonquizdetail_value['lesson_id']; ?>">
								<input type="hidden" id="sectionID"  value="<?php echo $sectionID; ?>">
								<?php if($lessonquizdetail_value['type'] !=''){ ?>
								<ul class="navsidelist">
									<li class="video_list" id="lesson_<?php echo $lessonquizdetail_value["lesson_id"]; ?>"> 
										<div class="firstcontent">											
											<input type="checkbox" id="<?php echo $lessonquizdetail_value["lesson_id"]; ?>" onchange="markAsComplete(this.id,1,'<?php echo $sectionList_value->id; ?>')" <?php echo $checked; ?>/><div class="lesson_video_ID displayinline pl5 valign-top" section-data-id="<?php echo $sectionID; ?>" data-id="<?php echo $lessonquizdetail_value['lesson_id']; ?>"><?php echo "<b>".$this->lang->line($lessonquizdetail_value['type'])." ".$lessoncount.": "."</b>". $lessonquizdetail_value['lesson_title']; ?></div></div>
											<div class="video_time"><?php if($lessonquizdetail_value['lesson_type'] == 'video'){ echo $lessonquizdetail_value['duration'];} ?></div>
									</li>
								</ul>
							<?php } ?>
							<?php }else{ $quizcount = $quizcount+1;
									$quiz_id = $lessonquizdetail_value["quiz_id"]	;							
									$checkedquiz = "";
									if($quizprogress[$quiz_id]){
										$checkedquiz = "checked";
									}									
									?>
							<?php if($lessonquizdetail_value['type'] !=''){ ?>
								<ul class="navsidelist"> 				
									<li class="video_list" id="quiz_<?php echo $lessonquizdetail_value['quiz_id']; ?>">
										<div class="firstcontent">
											<input type="hidden" id="quiz_id" value="<?php echo $lessonquizdetail_value['quiz_id']; ?>">
											<input type="checkbox" id="<?php echo $lessonquizdetail_value["quiz_id"]; ?>" onchange="markAsComplete(this.id,2,'<?php echo $sectionList_value->id; ?>')" <?php echo $checkedquiz; ?>/>
											<div class="quiz_btn_id displayinline pl5 valign-top" course-data-id="<?php echo $coursesList['id']; ?>" data-id="<?php echo $lessonquizdetail_value['quiz_id']; ?>"><?php echo "<b>".$this->lang->line($lessonquizdetail_value['type'])." ".$quizcount.": "."</b>". $lessonquizdetail_value['quiz_title']; ?></div>
										</div>
									</li>
								</ul>
							<?php } ?>
							<?php } } } ?>
							</div>
						</div>
						<?php
						$sectioncount++;
						}} else {?>
						<div class="alert alert-danger">
						<?php echo $this->lang->line('no_record_found') ?>
						</div>
						<?php }?>
					</div>
				</div>
			</div>
			<?php } ?>
		</div><!--./nav-->
	</div><!--/#sidebar-wrapper-->
    <div class="">
        <div class="row">
            <div class="col-lg-12">
                <?php if($coursesList['free_course'] == '1' || $paidstatus == '1' || (!empty($lessonprogress)) || (!empty($quizprogress))){
                ?>
                <div id="video_id"></div>
                <?php } ?>
            </div>
        </div><!--./row-->
    </div><!--./container-fluid-->
</div>
<script>
function closevideo()
{ 
    window.location.reload();
}
</script>
<script>
	function markAsComplete(lesson_quiz_id,lesson_quiz_type,section_id) {
		$.ajax({
			type : 'POST',
			url : "<?= base_url('user/studentcourse/markascomplete'); ?>",
			data : {lesson_quiz_id : lesson_quiz_id,lesson_quiz_type : lesson_quiz_type,section_id : section_id},
			success : function(data){

			}
		});
    }
</script>
<script>
(function ($) {
  "use strict";

  $('.quiz_button').click(function(){
  	$('#video_id').html('');
    var courseid = $(this).attr('course-data-id');
    $.ajax({
      url : '<?php echo base_url(); ?>user/studentcourse/quizperformance',
      data: {courseid:courseid},
      type:'post',
      success : function(response){
        $('#video_id').html(response);
      }
    });
  });

  $('.lesson_video_ID').click(function(){
  	$('#video_id').html('');
    var sectionID = $(this).attr('section-data-id');
    var lessonID = $(this).attr('data-id');
	$('.video_list').removeClass('active');
	$('#lesson_'+lessonID).addClass('active');	
    $.ajax({
      url : '<?php echo base_url(); ?>user/studentcourse/getlessonvideo',
      data: {lessonID:lessonID,sectionID:sectionID},
      type:'post',
      success : function(response){
        $('#video_id').html(response);
      }
    });
  });

  $('.quiz_btn_id').click(function(){
  	$('#video_id').html('');
    var courseid = $(this).attr('course-data-id');
    var quizID = $(this).attr('data-id');
	$('.video_list').removeClass('active');
	$('#quiz_'+quizID).addClass('active');
    $.ajax({
      url : '<?php echo base_url(); ?>user/studentcourse/quizinstruction',
      data: {quizID:quizID,courseid:courseid},
      type:'post',
      success : function(response){
        $('#video_id').html(response);
      }
    });
  });

  $(document).ready(function(){
  	$('#video_id').html('');
    var lessonID = $('#lessonID').val();
    var sectionID = $('#sectionID').val();
    var type = $('#type').val();
	
    if(type == 'lesson'){
		$('#lesson_'+lessonID).addClass('active');
	    $.ajax({
	      url : '<?php echo base_url(); ?>user/studentcourse/getlessonvideo',
	      data: {lessonID:lessonID,sectionID:sectionID},
	      type:'post',
	      success : function(response){
	        $('#video_id').html(response);
	      }
	    });
    }else{
    	var courseid = "<?php echo $coursesList['id']; ?>";
    	var quizID = $('#quiz_id').val();
		$('#quiz_'+quizID).addClass('active');
	    $.ajax({
	      url : '<?php echo base_url(); ?>user/studentcourse/quizinstruction',
	      data: {quizID:quizID,courseid:courseid},
	      type:'post',
	      success : function(response){
	        $('#video_id').html(response);
	      }
	    });
    }
  })
})(jQuery);
</script>
<script>
(function ($) {
   "use strict";

  $(".sidebar-closebtn").on('click', function () {
    $(".fa-angle-right").toggleClass("rotate");
  });

  $("#menu-toggle").click(function (e) {
    e.preventDefault();
    $(".wrapper-modal").toggleClass("toggled");
  });
})(jQuery);
</script>