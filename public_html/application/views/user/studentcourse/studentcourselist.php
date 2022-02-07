<?php $this->load->view('layout/course_css.php'); ?>
<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box box-primary">
          <div class="box-header with-border pb0">
            <h3 class="box-title header_tab_style"><i class="fa fa-search"></i><?php echo $this->lang->line('course_list'); ?> </h3>
            <div class="nav-tabs-custom border0 navcustom-right posiright5 tab mb0">
              <ul class="nav nav-tabs pull-right">
                <li><a href="#tab_1" data-toggle="tab" class="tablinks miusttop10" onclick="openCourse(event, 'course_card_tab')" ><i class="fa fa-list"></i></a></li>
                <li ><a href="#tab_2"  data-toggle="tab" class="tablinks miusttop10" onclick="openCourse(event, 'course_detail_tab')"><i class="fa fa-th"></i></a></li>
              </ul>
            </div>
          </div>
<div id="course_card_tab" class="tabcontent"> 
    <div class="nav-tabs-custom border0 navnoshadow">
      <div class="tab-content">
        <div class="download_label"><?php echo $this->lang->line('course_list') ; ?></div>
        <div class="tab-pane active table-responsive no-padding" id="tab_1">
           <table class="table table-striped table-bordered table-hover course-list course-table" data-export-title="<?php echo $this->lang->line('course_list'); ?>">
            <thead>
              <tr>
                <th class="white-space-nowrap"><?php echo $this->lang->line('title'); ?></th>
                <th class="white-space-nowrap"><?php echo $this->lang->line('class'); ?></th>
                <th class="white-space-nowrap"><?php echo $this->lang->line('section'); ?></th>
                <th class="white-space-nowrap"><?php echo $this->lang->line('lesson'); ?></th>
                <th class="white-space-nowrap"><?php echo $this->lang->line('quiz'); ?></th>
                <th class="white-space-nowrap"><?php echo $this->lang->line('total_hour_count'); ?></th>
                <th class="white-space-nowrap"><?php echo $this->lang->line('price').' ('.$currency_symbol.')'; ?></th>
                <th class="white-space-nowrap"><?php echo $this->lang->line('current_price').' ('.$currency_symbol.')'; ?></th>
                <th class="white-space-nowrap"><?php echo $this->lang->line('last_updated'); ?></th>
                <th class="text-right noExport white-space-nowrap"><?php echo $this->lang->line('action'); ?></th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>   
</div>
<div id="course_detail_tab" class="tabcontent">
  <section class="content">
    <div class="row">
      <div class="col-md-12">
          <div class="row flex-row">
            <?php if (!empty($new_courselist)) {
    foreach ($new_courselist as $new_courselist_value) {
        ?>
            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
              <div class="coursebox">
              
              <a href="#" class="coursedetail text-dark" data-toggle="modal" data-target="#coursedetailmodal" data-id="<?php echo $new_courselist_value['id']; ?>">
                <div class="coursebox-img">
                  <img src="<?php echo base_url(); ?>uploads/course/course_thumbnail/<?php echo $new_courselist_value['course_thumbnail']; ?>">
                  <div class="author-block author-wrap">
				  
					<?php if (!empty($new_courselist_value['image'])) {?>
                        <img class="img-circle" src="<?php echo base_url(); ?>uploads/staff_images/<?php echo $new_courselist_value['image']; ?>" alt="User Image">
                    <?php } else {
                    if($new_courselist_value['gender']=='Female'){
                        $file= "uploads/staff_images/default_female.jpg";
                    }else{
                        $file ="uploads/staff_images/default_male.jpg";
                    }
                        ?>
                        <img class="img-circle" src="<?php echo base_url(); ?><?php echo $file; ?>" alt="">
                    <?php }?>                
				  
                    <span class="authorname"><?php echo $new_courselist_value['name']; ?> <?php echo $new_courselist_value['surname']; ?></span>
                    <span class="description"><span><?php echo $this->lang->line('last_updated'); ?> </span> <?php echo date($this->customlib->getSchoolDateFormat(), strtotime($new_courselist_value['updated_date'])); ?></span>
                  </div>
                </div>
                <div class="coursebox-body">
                  <h4><?php echo $new_courselist_value['title']; ?> :</h4><div class="course-caption"><?php echo $new_courselist_value['description']; ?></div>
                  <div class="classstats">
                    <i class="fa fa-list-alt"></i><?php echo $this->lang->line('class'); ?> - <?php echo $new_courselist_value['class']; ?>
                    <?php if (!empty($new_courselist_value['total_lesson'])) {?>
                    <span class="pull-right">
                     <i class="fa fa-play-circle"></i>
                      <?php echo $this->lang->line('lesson') . ' ' . $new_courselist_value['total_lesson']; ?>
                    </span>
                    <?php } else {echo "<br>";}?>
                  </div>
                  <div class="classstats">
                  <?php
        $free_course    = $new_courselist_value['free_course'];
        $discount       = $new_courselist_value['discount'];
        $price          = $new_courselist_value['price'];
        $discount_price = '';
        $price          = '';
        if ($new_courselist_value['discount'] != '0.00') {
            $discount = $new_courselist_value['price'] - (($new_courselist_value['price'] * $new_courselist_value['discount']) / 100);
        }
        if (($new_courselist_value["free_course"] == 1) && ($new_courselist_value["price"] == '0.00')) {
            $price = 'Free';
        } elseif (($new_courselist_value["free_course"] == 1) && ($new_courselist_value["price"] != '0.00')) {
            if ($new_courselist_value['price'] > '0.00') {
                $courseprice = $currency_symbol . '' . $new_courselist_value['price'];
            } else {
                $courseprice = '';
            }
            $price = "Free <span><del>" . $courseprice . '</del></span>';
        } elseif (($new_courselist_value["price"] != '0.00') && ($new_courselist_value["discount"] != '0.00')) {
            $discount = number_format((float) $discount, 2, '.', '');
            if ($new_courselist_value['price'] > '0.00') {
                $courseprice = $currency_symbol . '' . $new_courselist_value['price'];
            } else {
                $courseprice = '';
            }
            $price = $currency_symbol . '' . $discount . ' <span><del>' . $courseprice . '</del></span> ';
        } else {
            $price = $currency_symbol . '' . $new_courselist_value['price'];
        }
        ?>
                      <?php echo $price; ?>
                    <span class="pull-right">
                     <?php if (!empty($new_courselist_value['total_hour_count']) && $new_courselist_value['total_hour_count'] != '00:00:00') {?>
                      <i class="fa fa-clock-o"></i>
                     <?php echo $new_courselist_value['total_hour_count'] . " " . $this->lang->line('hrs');} ?>
                   </span>
                    </div>
                  <div class="row mt-5">
                    <div class="col-md-10 col-sm-10 col-xs-10">						
                      <div class="courssprogress">						  
							<?php $course_progress	=	intval($new_courselist_value['course_progress']); ?>		
							<div class="progress-bar <?php if($course_progress < '100'){ ?> progress-bar-warning <?php }elseif($course_progress == '100'){ ?> progress-bar-info <?php } ?>" role="progressbar" aria-valuenow="45" id="progressbar<?= $new_courselist_value["id"]; ?>"  aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $course_progress;?>%">
							</div>				  
                      </div>					  
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-2 text-right">
						<span id="progressbarval<?= $new_courselist_value["id"]; ?>">
						<?php echo intval($new_courselist_value['course_progress']); ?>%
						</span>
					</div>
                  </div>
                </div>	
				</a>	
                
                <div class="coursebtn">
				
					<a href="#" class="btn btn-add coursedetail" data-toggle="modal" data-target="#coursedetailmodal" data-id="<?php echo $new_courselist_value['id']; ?>"><?php echo $this->lang->line('course_detail'); ?></a>		
					<?php if ($free_course == '1') {  ?>
					<?php if ($loginsession['role'] != 'parent') {  ?>				  
                    <a href="#" class="btn btn-buygreen lesson_ID" data-toggle="modal" data-target="#coursemodal" lesson-data="<?php echo $new_courselist_value['id']; ?>"><?php echo $this->lang->line('start_lesson'); ?></a>
					<?php }else{}?>					
					<?php } else {  if($new_courselist_value['paidstatus'] == '1'){   ?>					
					<?php if ($loginsession['role'] != 'parent') {  ?>
                    <a href="#" class="btn btn-buygreen lesson_ID" data-toggle="modal" data-target="#coursemodal" lesson-data="<?php echo $new_courselist_value['id']; ?>"><?php echo $this->lang->line('start_lesson'); ?></a>
					<?php }else{}?>					
					<?php }else{ ?>	
                  <?php if($new_courselist_value['course_progress'] > 0){ ?>				
                    <a href="#" class="btn btn-buygreen lesson_ID" data-toggle="modal" data-target="#coursemodal" lesson-data="<?php echo $new_courselist_value['id']; ?>"><?php echo $this->lang->line('start_lesson'); ?></a>
					<?php }else{ 
				  if(!empty($paymentgateway)){
				  ?>
                    <a href="<?php echo base_url(); ?>students/online_course/course_payment/payment/<?php echo $new_courselist_value['id']; ?>" class="btn btn-buygreen"><?php echo $this->lang->line('buy_now'); ?></a>
                  <?php 
				  }
				  } ?>
					<?php } } ?>						
                </div>					
              </div>
            </div><!--./col-lg-3-->
            <?php }}else{?>
              <div class="col-lg-12">
                <div class="alert alert-danger full-width">
                  <?php echo $this->lang->line('no_record_found') ?>
                </div>
              </div>  
            <?php } ?>
          </div><!--./row-->        
       <!--  </div> -->
      </div>
    </div>
  </section>
</div>
    </div><!--./box box-primary -->
    </div>
    </div>
    </section>
</div>
<!-- Modal -->
<div id="coursemodal" class="modal fade" role="dialog">
  <div class="modal-dialog video-dialogfull">
    <div class="video-contentfull">
        <div id="course_model_body"></div>
    </div>
  </div>
</div>

<div id="coursedetailmodal" class="modal fade" role="dialog">
  <div class="modal-dialog modalwrapwidth">
    <div class="modal-content">
      <button type="button" class="close" data-dismiss="modal" onclick="stopvideo()">&times;</button>
        <div class="scroll-area">
          <div class="modal-body paddbtop">
              <div class="row">
                <div id="coursedetail1_id">
                </div>
              </div><!--./row-->
          </div><!--./modal-body-->
      </div>
    </div>
  </div>
</div><!--#/coursedetailmodal-->

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
<script>
function openCourse(evt, courseName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(courseName).style.display = "block";
  evt.currentTarget.className += " active";
}

(function ($) {
  "use strict";

  $(document).ready(function(){
    $('#course_detail_tab').show();
  })

  $('.lesson_ID').click(function(){
     var coureseID = $(this).attr('lesson-data');
	 $('#course_model_body').html('');
     $.ajax({
       url  : "<?php echo base_url(); ?>user/studentcourse/startlesson",
       type : 'post',
       data : {coureseID:coureseID},
       success : function(response){
          $('#course_model_body').html(response);
       }
     });
  });

  $('.coursedetail').click(function(){
    var courseID = $(this).attr('data-id');
	$('#coursedetail1_id').html('');
    $.ajax({
     url  : "<?php echo base_url(); ?>user/studentcourse/coursedetail",
     type : 'post',
     data : {courseID:courseID},
     beforeSend: function () {
      $('#coursedetail1_id').html('Loading...  <i class="fa fa-spinner fa-spin"></i>');
     },
     success : function(response){
       $('#coursedetail1_id').html(response);
     }
    });
  })
  
  $(document).on("click",".lesson_ID",function() {
    var coureseID = $(this).attr('lesson-data');
	$('#course_model_body').html('');
    $.ajax({
       url  : "<?php echo base_url(); ?>user/studentcourse/startlesson",
       type : 'post',
       data : {coureseID:coureseID},
       success : function(response){
          $('#course_model_body').html(response);
       }
     });
    $('#coursedetailmodal').modal('hide');
  });
})(jQuery);
</script>

<script>
( function ( $ ) {
  'use strict';
    $(document).ready(function () {
      initDatatable('course-list','user/studentcourse/getcourselist',[],[],100);
    });
} ( jQuery ) )
</script>
<script>
( function ( $ ) {
  'use strict';
  $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
    $($.fn.dataTable.tables(true)).DataTable()
      .columns.adjust();
  });
} ( jQuery ) )
</script>
<script>
function loadcoursedetail(courseID){
	$('#coursedetail1_id').html('');
  $.ajax({
    url  : "<?php echo base_url(); ?>user/studentcourse/coursedetail",
    type : 'post',
    data : {courseID:courseID},
    beforeSend: function () {
     $('#coursedetail1_id').html('Loading...  <center><i class="fa fa-spinner fa-spin"></i></center>');
    },
    success : function(response){
      $("#coursedetailmodal").modal();
      $('#coursedetail1_id').html(response);
    }
  });
}

function afterprint(courseID){
	$('#course_model_body').html('');
	$.ajax({
   url  : "<?php echo base_url(); ?>user/studentcourse/startlesson",
   type : 'post',
   data : {coureseID:courseID},
   success : function(response){		   
      $('#course_model_body').html(response);
   }
 });
}
</script>
<script>
	function stopvideo(){
		$('#coursedetail1_id').html('');
		$('#coursedetailmodal').modal('hide');
	}

( function ( $ ) {
  'use strict';
  
  $(document).on('click', '.print_btn', function () {
    var courseid = $(this).attr('data-id');
    $.ajax({
        url: '<?php echo site_url("user/studentcourse/printinvoice") ?>',
        type: 'post',
        data: {courseid:courseid},
        success: function (response) {
            popup(response);
        }
    });
  });
} ( jQuery ) )
</script>
<script>
function popup(data)
{
    var base_url = '<?php echo base_url() ?>';
    var frame1 = $('<iframe />');
    frame1[0].name = "frame1";
    frame1.css({"position": "absolute", "top": "-1000000px"});
    $("body").append(frame1);
    var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
    frameDoc.document.open();
    //Create a new HTML document.
    frameDoc.document.write('<html>');
    frameDoc.document.write('<head>');
    frameDoc.document.write('<title></title>');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/bootstrap/css/bootstrap.min.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/font-awesome.min.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/ionicons.min.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/AdminLTE.min.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/skins/_all-skins.min.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/iCheck/flat/blue.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/morris/morris.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/jvectormap/jquery-jvectormap-1.2.2.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/datepicker/datepicker3.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/daterangepicker/daterangepicker-bs3.css">');
    frameDoc.document.write('</head>');
    frameDoc.document.write('<body>');
    frameDoc.document.write(data);
    frameDoc.document.write('</body>');
    frameDoc.document.write('</html>');
    frameDoc.document.close();
    setTimeout(function () {
        window.frames["frame1"].focus();
        window.frames["frame1"].print();
        frame1.remove();
    }, 500);
    return true;
}
</script>