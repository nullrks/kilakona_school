<?php if (!empty($coursesList)) {
    $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
    $student_data    = $this->customlib->getLoggedInUserData();
    $student_img     = $student_data["image"];
    $free_course     = $coursesList['free_course'];
    $discount        = $coursesList['discount'];
    $price           = $coursesList['price'];
    $discount_price  = '';
    if ($discount != '0.00') {
        $discount_price = number_format($price - $price * $discount / 100, 2);
    }	
	
    ?>
    <div class="flex-row row"> 
        <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
            <div class="whatyou coursebox-body mbDM15">
                <?php if (!empty($coursesList['course_url'])) { ?>
				<div class="coursebox mb0">	
                    
        				<?php	if ($coursesList['course_provider'] == "html5") {	?>   
        			<div class="course-video-height"> 		
        				<video id="videoPlayer" controls>
        					<source src="<?php echo $coursesList['course_url']; ?>" type="video/mp4">
        				</video>	
                    </div>    			
        				
        				<?php	} elseif ($coursesList['course_provider'] == "youtube") {	?>    
                    <div class="course-video-height">        
        				<iframe width="100%" src="//www.youtube.com/embed/<?php echo $coursesList['video_id']; ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
                    </div>          				
        				<?php	} elseif ($coursesList['course_provider'] == "vimeo") { 	?>   
        				
        				<iframe src="https://player.vimeo.com/video/<?php echo $coursesList['video_id']; ?>" width="640" height="1164" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
        				
        				<?php	}elseif ($lesson['video_provider'] == "s3_bucket") { ?>		
        				
        				<video controls width="100%">
        					<source src="<?php echo $lesson['s3_url'] ?>">
        				</video>
        				
        				<?php } ?>                     
				</div>				
                <?php } else {?>
                <div class="coursebox mb0">
                    <div class="coursebox-img">
                       <img src="<?php echo base_url(); ?>uploads/course/course_thumbnail/<?php echo $coursesList['course_thumbnail']; ?>" class="img-responsive">
                    </div>   
                </div>
                <?php }?>
            </div>
        </div><!--./col-lg-7-->
        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="whatyou coursebox-body relative">
                <div class="author-block-center text-center">
                    <?php if (!empty($coursesList['image'])) {?>
                        <img class="img-circle" src="<?php echo base_url(); ?>uploads/staff_images/<?php echo $coursesList['image']; ?>" alt="User Image">
                    <?php } else {
                    if($coursesList['gender']=='Female'){
                        $file= "uploads/staff_images/default_female.jpg";
                    }else{
                        $file ="uploads/staff_images/default_male.jpg";
                    }
                        ?>
                        <img class="img-circle" src="<?php echo base_url(); ?><?php echo $file; ?>" alt="">
                    <?php }?>
                    <span class="authornamebig"><?php echo $coursesList['staff_name'].' '.$coursesList['staff_surname']; ?></span>
                    <span class="descriptionbig"><?php echo $this->lang->line('last_updated'); ?> <span><?php echo date('d/m/Y', strtotime($coursesList['updated_date'])); ?></span></span>
                </div>
                <ul class="lessonsblock ptt10">
                    <li><i class="fa fa-list-alt"></i><?php echo $this->lang->line('class'); ?> - <?php echo $coursesList['class']; ?></li>
                    <?php if ($lesson_count !='' && $lesson_count !='0') {?>
                    <li>
                        <i class="fa fa-play-circle"></i><?php echo $this->lang->line('lesson') . " " . $lesson_count; ?>
                    </li>
                    <?php } ?>
                    <?php if ($quiz_count !='' && $quiz_count !='0') {?>       
                    <li>
                        <i class="fa fa-question-circle"></i><?php echo $this->lang->line('quiz') . " " . $quiz_count; ?>
                    </li>
                    <?php } ?>
                    <?php if (!empty($total_hour_count) && $total_hour_count != '00:00:00') {?>
                    <li>
                        <i class="fa fa-clock-o"></i><?php echo $total_hour_count ." ".$this->lang->line('hrs'); ?>
                    </li>
                    <?php } ?>
                    <li>
					
                    <?php if($paidstatus != '1'){ if ($free_course == '1' ) {
                        echo $this->lang->line('free');
                    } else {
                        if (!empty($discount_price)) {
                           echo $currency_symbol . ' ' . $discount_price;?>
                           <del><?php echo $currency_symbol . ' ' . $price; ?></del>
                    <?php } else {
                           echo $currency_symbol . '' . $price;?>
                    <?php }} } ?>

                    <?php if($paidstatus == '1'){ ?>                    
                    <?php if ($loginsession['role'] != 'parent') {  ?>
                            <button data-backdrop="static" data-id="<?php echo $coursesList['id']; ?>" class="btn btn-primary print_btn btn-xs valign-text-bottom"><i class="fa fa-print pr0"></i> <?php echo $this->lang->line('print_invoice'); ?></button>
                    <?php }else{}?>                 
                    <?php } ?> 
                    
                    </li>
                </ul>
                <div class="coursebtnfull">      
                    <?php if ($free_course == '1') {  ?>
                    <?php if ($loginsession['role'] != 'parent') {  ?>                
                    <a href="#" class="btn btn-add-full lesson_ID" data-toggle="modal" data-target="#coursemodal" lesson-data="<?php echo $coursesList['id']; ?>"><?php echo $this->lang->line('start_lesson'); ?></a>
                    <?php }else{}?>                 
                    <?php } else {  if($paidstatus == '1'){ ?>                    
                    <?php if ($loginsession['role'] != 'parent') {  ?>

                    <a href="#" class="btn btn-add-full lesson_ID" data-toggle="modal" data-target="#coursemodal" lesson-data="<?php echo $coursesList['id']; ?>"><?php echo $this->lang->line('start_lesson'); ?></a>
                    <?php }else{}?>                 
                    <?php }else{ ?>                 
                        <?php if(!empty($courseprogresscount)){ ?>
                            <a href="#" class="btn btn-add-full lesson_ID" data-toggle="modal" data-target="#coursemodal" lesson-data="<?php echo $coursesList['id']; ?>"><?php echo $this->lang->line('start_lesson'); ?></a>
                       <?php }else{ ?>
							<?php if(!empty($paymentgateway)){ ?>
					   
                            <a href="<?php echo base_url(); ?>students/online_course/Course_payment/payment/<?php echo $coursesList['id']; ?>" class="btn btn-add-full"><?php echo $this->lang->line('buy_now'); ?> 
                            <?php if ($free_course == '1') {
                                echo $this->lang->line('free');
                            } else {
                                if (!empty($discount_price)) {
                                echo $currency_symbol . ' ' . $discount_price;?>
                                <del><?php echo $currency_symbol . ' ' . $price; ?></del>
                            <?php } else {
                            echo $currency_symbol . '' . $price;?>
                            <?php }}?>   
                            </a>
							<?php }?>
							
                       <?php } ?>
                    <?php } } ?>    
                </div>  
            </div>    
        </div><!--./col-lg-5-->
     </div><!--./detailmodalbg-->  
 <div class="row">     
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="imgbottomtext">
            <h3 class="modal-title pb3 fontmedium"><?php echo $coursesList['title']; ?></h3>
            <p><?php echo $coursesList['description']; ?>.</p>
        </div>
    </div><!--./col-lg-9-->
    
<?php }?>
<?php if (!empty($sectionList)) { ?>
    <?php 
		$outcomes = json_decode($coursesList['outcomes']);
        $check_empty = '';
        if (array_filter($outcomes)) {
            $check_empty = $outcomes;
        } else {
            $check_empty = '';
        }
    ?>
    <div class="col-lg-12 col-md-12 col-sm-12">
        <?php if (!empty($check_empty)){ ?>
        <div class="coursecard whatyou">
			<h3 class="fontmedium"><?php echo $this->lang->line('what_will_i_learn'); ?></h3>
			<?php $outcomes = json_decode($coursesList['outcomes']); ?>
			<ul class="whatlearn">
				<li>
					<?php foreach ($outcomes as $outcomes_value) {?>
					<?php echo $outcomes_value; ?>
					<?php }?>
				</li>
			</ul>
        </div><!--./coursecard-->
        <?php } ?>
        <div class="coursecard ptt10">
            <h4 class="fontmedium"><?php echo $this->lang->line('curriculum_for_this_course'); ?> </h4>
            <div class="panel-group faq mb10" id="accordionplus">
				<div class="panel panel-info">
				<?php $lessoncount=0; $quizcount=0; $sectioncount=0;
                foreach ($sectionList as $sectionList_key => $sectionList_value) {  $sectioncount = $sectioncount+1;?>
                <?php $sectionID = $sectionList_value->id;?>
					<div class="panel-heading" data-toggle="collapse" data-parent="#accordionplus" data-target="#<?php echo $sectionList_key; ?>" aria-expanded="true">
						<h4 class="panelh3 accordion-togglelpus"><?php echo "<b>".$this->lang->line('section').' '. $sectioncount.'</b>: '. $sectionList_value->section_title; ?><span class="mr0"><i class="fa fa-play-circle"></i><?php if (!empty($sectionList_value->total_lessons)) {echo $sectionList_value->total_lessons;}?> <?php echo $this->lang->line('lesson'); ?></span></h4>
					</div>
					<div id="<?php echo $sectionList_key; ?>" class="panel-collapse collapse in" aria-expanded="true">						
						<ul class="introlist">
                            <?php if (!empty($lessonquizdetail[$sectionID])) { ?>
                            <?php foreach ($lessonquizdetail[$sectionID] as $lessonquizdetail_value) {
                            if ($lessonquizdetail_value['type'] == 'lesson') { $lessoncount = $lessoncount+1; ?>
                            <?php if($lessonquizdetail_value['type'] !=''){ ?> 
                                <li><i class="fa fa-play-circle"></i><?php echo "<b>".$this->lang->line($lessonquizdetail_value['type'])." ".$lessoncount.": "."</b>". $lessonquizdetail_value['lesson_title']; ?><span><?php if($lessonquizdetail_value['lesson_type'] == 'video'){ echo $lessonquizdetail_value['duration']; } ?></span></li>
                            <?php } ?>                      
                            <?php }else{ $quizcount = $quizcount+1; ?>
                            <?php if($lessonquizdetail_value['type'] !=''){ ?>
                                <li><i class="fa fa-question-circle"></i><?php echo "<b>".$this->lang->line($lessonquizdetail_value['type'])." ".$quizcount.": "."</b>". $lessonquizdetail_value['quiz_title']; ?></li>
                            <?php } ?>                       
                            <?php } } }?>
                        </ul>						
					</div><!--#/collapseOne-->
					<?php }?>
				</div><!--./panel-info-->
            </div><!--./panel-group-->
        </div><!--./coursecard-->
    </div><!--./col-lg-8-->
  </div><!--./row-->  
<?php }?>