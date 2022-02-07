<script src="<?php echo base_url(); ?>backend/plugins/ckeditor/ckeditor.js"></script>
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
                       <div class="row">
                          <div class="col-lg-4 col-md-3 col-sm-4">
                            <h3 class="box-title header_tab_style"><?php echo $this->lang->line('course_list'); ?></h3>
                          </div>
                          <div class="col-lg-8 col-md-9 col-sm-8">
                            <div class="nav-tabs-custom mb0 pull-right">
                              <ul class="nav nav-tabs tab border0 ">
              						<li><a href="#tab_2"  data-toggle="tab"  class="miusttop10 border0" onclick="openCourse(event, 'course_detail_tab')"><i class="fa fa-th"></i></a></li>

              						<li><a href="#tab_1" data-toggle="tab" class="miusttop10 border0" onclick="openCourse(event, 'course_card_tab')" ><i class="fa fa-list"></i></a></li>										

									<?php if($this->rbac->hasPrivilege('online_course', 'can_add')) {?> 

              						<li><button type="button" class="btn btn-sm btn-primary miusttop5" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#add_course_modal"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add_course'); ?></button></li>   

									<?php } ?>
                              </ul>
                                </div><!--./nav-tabs-custom -->
                                  <form class="navbar-form pull-right miusttop5" id="search_area" role="search" action="<?php echo site_url('onlinecourse/course/index'); ?>" method="GET">
                                    <?php echo $this->customlib->getCSRF(); ?>
                                    <div class="input-group">
                                      <input type="text" value="<?php echo $search_course;?>" name="search_course" id="search_course" class="form-control search-form" placeholder="<?php echo $this->lang->line('search_by_course_name'); ?>">

                                      <span class="input-group-btn">
                                        <button type="submit" name="search" id="search-btn" style="" class="btn btn-flat topsidesearchbtn"><i class="fa fa-search"></i></button>

                                      </span>
                                    </div>
                                  </form>            

                          </div>  
                        </div>           

                    </div>

<div id="course_card_tab" class="tabcontent">

   <?php if (isset($new_courselist)) {

    ?>

    <div class="nav-tabs-custom border0 navnoshadow">
        <div class="tab-content">           

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
    <?php }?>
</div>

<div id="course_detail_tab" class="tabcontent">
  <section class="content">
		<?php if (!empty($new_courselist)) { ?>
          <div class="row flex-row" >
            <?php foreach ($new_courselist as $new_courselist_value) { ?>
              <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                  <div class="coursebox">
                  
                    <a href="#" class="course_detail_id text-dark" data-id="<?php echo $new_courselist_value['id']; ?>" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#course_detail_modal">
                    
                    <div class="coursebox-img">
                      <img src="<?php echo base_url(); ?>uploads/course/course_thumbnail/<?php echo $new_courselist_value['course_thumbnail'];  ?>">

                      <div class="author-block author-wrap">				  

                      <?php if (!empty($new_courselist_value['image'])) {

                        ?>
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

                        <span class="authorname"><?php echo $new_courselist_value['name'] . ' ' . $new_courselist_value['surname']; ?></span>
                        <span class="description"><span><?php echo $this->lang->line('last_updated'); ?> </span> <?php echo date($this->customlib->getSchoolDateFormat(), strtotime($new_courselist_value['updated_date'])); ?></span>

                      </div>
                    </div>
                    <div class="coursebox-body">
                      <h4><?php echo $new_courselist_value['title']; ?> : </h4><div class="course-caption"><?php echo $new_courselist_value['description']; ?></div>

                       <div class="classstats">

                        <div><i class="fa fa-list-alt"></i><?php echo $this->lang->line('class'); ?> - <?php echo $new_courselist_value['class']; ?></div>
                        <div>
                          <?php if (!empty($new_courselist_value['total_lesson'])) {?><i class="fa fa-play-circle"></i><?php echo $this->lang->line('lesson') . ' ' . $new_courselist_value['total_lesson'];} else {echo '<br>';} ?>
                        </div>
                      </div>
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

                      <div class="classstats">
                        <div>
                            <?php echo $price; ?>
                        </div>
                        <div><?php if (!empty($new_courselist_value['total_hour_count']) && $new_courselist_value['total_hour_count'] != '00:00:00') {?><i class="fa fa-clock-o"></i><?php echo $new_courselist_value['total_hour_count'] . ' ' . $this->lang->line('hrs');} ?></div>
                      </div>
                    </div>
                    
                    </a>
                    
                    
                    <div class="coursebtn">
                      <?php if($this->rbac->hasPrivilege('online_course', 'can_view')) {?> 					  

					  <a href="#" class="btn btn-add course_detail_id" data-id="<?php echo $new_courselist_value['id']; ?>" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#course_detail_modal"><?php echo $this->lang->line('manage_course'); ?></a>

					  <?php } ?>					 

					  <a href="#" class="btn btn-buygreen course_preview_id pull-right" data-id="<?php echo $new_courselist_value['id']; ?>" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#course_preview_modal"><?php echo $this->lang->line('course_preview'); ?></a>

                    </div>
                  </div>
                </div><!--./col-lg-3-->
                <?php }?>
              </div><!--./row-->
			<?php }else{ 
        ?>
        <div class="text-center">
          <span class="dataTables_empty">No data available in table <br> <br></span>
          <img src="<?php echo base_url(); ?>backend/images/addnewitem.svg" width="150"><br><br> 
          <span class="text-success bolds"><i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('no_record_found_as_per_your_search_criteria');?></span>

        </div>


        <?php
         } ?>

        <div class="row text-center">
          <?php echo $this->pagination->create_links(); ?>
        </div>      

    </section>
</div>
    </div><!--./box box-primary -->
    </div>
    </div>
    </section>
</div>

<div class="modal fade" id="course_detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg course_modal" role="document">
    <div class="modal-content modal-media-content">
        <button id="close_btn" type="button" onclick="stopvideo()" class="close" data-dismiss="modal">&times;</button> 

      <div class="modal-body p0">
        <div id="course_detail"></div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="add_course_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg course_modal" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header p0">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <div class="box-header ptbnull noborder">
                    <h4 class="box-title titlefix"> <?php echo $this->lang->line('add_course'); ?></h4>
                    
                  </div> 
             </div>
   
    <div class="modal-body pt0 pb0">
      <form id="add_course_form_ID" method="post" class="ptt10" enctype="multipart/form-data">
         <div class="scroll-area-16">
      <?php echo $this->customlib->getCSRF(); ?>
        <div class="row">
          <div class="col-lg-8 col-md-8 col-sm-8">
              <div class="form-group">
                <label><?php echo $this->lang->line('title'); ?><small class="req"> *</small></label>
                <input autofocus="" id="title" name="title"  placeholder="" type="text" class="form-control"/><span class="text-danger"><?php echo form_error('title'); ?></span>
              </div>
              <label><?php echo $this->lang->line('outcomes'); ?></label>
                <table id="outcometableID" width="100%">
                  <tr id="outcomerow0">
                    <td width="98%">
                      <div class="form-group">
                      <input type="text" name="outcomes[]" class="form-control">
                      <span class="text-danger"><?php echo form_error('outcomes'); ?></span>
                      </div>
                    </td>
                    <td valign="top" width="30">
                      <button type="button" onclick="add_outcomerow()" class="plusgreenbtn addplus"><i class="fa fa-plus"></i></button>
                    </td>
                  </tr>
                </table>
                <div class="form-group">
                    <label><?php echo $this->lang->line('description'); ?><small class="req"> *</small></label>
                   <textarea id="editor1" placeholder="" type="text" class="form-control" ><?php echo set_value('description'); ?></textarea>
                   <span class="text-danger"><?php echo form_error('description'); ?></span>
                </div>  
          </div><!--./col-lg-8-->
          <div class="col-lg-4 col-md-4 col-sm-4">
						<div class="form-group">
              <label><?php echo $this->lang->line('inline_preview_image'); ?> (700px X 400px) <small class="req"> *</small></label>
                <input autofocus="" id="course_thumbnail" name="course_thumbnail"  placeholder="" type="file" class="filestyle form-control"/><span class="text-danger"><?php echo form_error('course_thumbnail'); ?></span>
            </div>		

                            <div class="form-group">
                                <label><?php echo $this->lang->line('class'); ?><small class="req"> *</small></label>
                                <select autofocus="" id="class_id" name="class" class="form-control class-list" >
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                <?php

if (!empty($classlist)) {

    foreach ($classlist as $classlist_value) {

        ?>

                                    <option value="<?php echo $classlist_value['id'] ?>" ><?php echo $classlist_value['class'] ?></option>
                                    <?php

}

}

?>

                                </select>
                                <span class="text-danger"><?php echo form_error('class'); ?></span>
                            </div>				

                        <div class="form-group">
                          <label> <?php echo $this->lang->line('section') ?><small class="req"> *</small></label>
                          <select id="section_id" name="section[]" class="form-control section-list"  multiple="multiple">
                          </select>
                        </div>					

                        <?php

$result = $this->customlib->getUserData();

$roleid = $result["role_id"];

if ($roleid != "2") {

    ?>

                        <div class="form-group">
                            <label><?php echo $this->lang->line('assign_teacher'); ?><small class="req"> *</small></label>
                            <select  id="teacher_id" name="teacher" class="form-control" >
                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                            <?php

if (!empty($allTeacherList)) {

        foreach ($allTeacherList as $allTeacherList_value) {?>

                                    <option value="<?php echo $allTeacherList_value['id']; ?>"><?php echo $allTeacherList_value['name'] . ' ' . $allTeacherList_value['surname']; ?> </option>

                            <?php
}

    }

    ?>

                            </select>
                            <span class="text-danger"><?php echo form_error('teacher'); ?></span>
                        </div>
                <?php }?>        

            <div class="row">
              <div class="col-md-12"><label><?php echo $this->lang->line('course_preview_url'); ?></label></div>
              <div class="form-group">
                <div class="col-md-4">
                  <select  id="course_provider" onclick="checkCourseProvider()" name="course_provider" class="form-control">
                  <?php foreach ($course_provider as $key => $cpvalue) {?>
                  <option value="<?php echo $key ?>"><?php echo $cpvalue ?></option>
                  <?php }?>
                  </select>
                </div>
              </div>
              <div class="col-md-8 hide" id="course_url_div">
                  <div class="form-group">
                    <input autofocus="" id="course_url" name="course_url"  placeholder="" type="text" class="form-control" />

                  </div>
              </div>
              <div class="col-md-8 hide" id="s3_file_div">
                  <div class="form-group">                   

                    <input autofocus="" id="s3_file" name="s3_file"  placeholder="" type="file" class="form-control filestyle" />
                  </div>
              </div>
            </div><!--./row-->
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                              <label><?php echo $this->lang->line('price'); ?><small class="req"> *</small></label>
                              <input autofocus="" id="course_price" name="course_price"  placeholder="" type="text" class="form-control"/><span class="text-danger"><?php echo form_error('course_price'); ?></span>

                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                                <label><?php echo $this->lang->line('discount'); ?> (%)</label>
                                <input autofocus="" id="course_discount" name="course_discount"  placeholder="" type="text" class="form-control"/><span class="text-danger"><?php echo form_error('course_discount'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label><?php echo $this->lang->line('free_course'); ?></label>
                            <div class="checkbox mt4">
                            <label><input type="checkbox" value="1" name="free_course" autocomplete="off" class="form-check-input"></label>
                            </div>
                          </div>
                        </div>
                      </div><!--./row-->
                </div><!--/.col (left) -->
           </div><!--./row-->
        </div><!--./scroll-area-->
          <div class="row">
            <div class="box-footer row">  
              <div class="pull-right">
              <a id="add_course_btn" class="btn btn-info"><span id="loader_btn"></span> <?php echo $this->lang->line('save'); ?></a>
            </div>
          </div>
        </div>    
       </form>            

     </div>

           
        </div>
    </div>
  </div>

  <div class="modal fade" id="edit_course_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg course_modal" role="document">
      <div class="modal-content modal-media-content">
        <div class="modal-header modal-media-header p0">
          <button type="button" class="close button" data-dismiss="modal">&times;</button>
		      <div class="box-header ptbnull noborder">
            <h4 class="box-title titlefix"><?php echo $this->lang->line('edit_course'); ?></h4>
            <div class="box-tools pull-right">
              

            </div>
          </div>
        </div>
        <div class="modal-body pb0">
          <div id="edit_course"></div>
         </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="add_section_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content modal-media-content">
        <div class="modal-header modal-media-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="box-title"> <?php echo $this->lang->line('add_section'); ?> 
            </h4>
        </div>
        <div class="modal-body pt0 pb0">
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
              <form id="formadd" method="post" class="ptt10">
                <input type="hidden" name="add_course_id" id="course_id">
                  <div class="row">
                    <div class="col-sm-12">
                      <div class="form-group">
                          <label for="pwd"><?php echo $this->lang->line('title'); ?></label><small class="req"> *</small>
                          <input type="text" id="title" autocomplete="off" class="form-control" name="title">
                          <span id="title_error" class="text-danger"></span>
                      </div>
                    </div>
                  </div>
              </form>
            </div>
          </div>
          <div class="row">
            <div class="box-footer col-md-12">
              <a  onclick="saveSection()" class="btn btn-info pull-right"><span id="section_loader"></span> <?php echo $this->lang->line('save'); ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="modal fade" id="order_section_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content modal-media-content">
        <div class="modal-header modal-media-header">
            <button type="button" class="close close_btn" data-dismiss="modal">&times;</button>
            <h4 class="box-title"> <?php echo $this->lang->line('order_section'); ?> 
            </h4>
        </div>
        <div class="modal-body pt0 pb0">
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
              <div id="order_section"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="edit_section_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content modal-media-content">
        <div class="modal-header modal-media-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="box-title"> <?php echo $this->lang->line('edit_section'); ?>
            </h4>
        </div>
        <div class="modal-body pt0 pb0">
          <span id="loader_section"></span>
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
              <form id="edit_section_form" method="post" class="ptt10">
                <input type="hidden" name="section_id" id="edit_sectionID">
                <input type="hidden" name="online_course_id" id="online_course_id">
                  <div class="row">
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="pwd"><?php echo $this->lang->line('title'); ?></label><small class="req"> *</small>
                        <input type="text" id="edit_title" autocomplete="off" class="form-control" name="edit_title">
                        <span id="title_error" class="text-danger"></span>
                      </div>
                    </div>
                  </div><!--./row-->
              </form>
            </div><!--./col-md-12-->
          </div><!--./row-->
          <div class="row">
            <div class="box-footer col-md-12">
              <a  id="edit_section_btn" class="btn btn-info pull-right"><span id="section_loaders"></span> <?php echo $this->lang->line('save'); ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

    <div class="modal fade" id="add_lesson_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-media-content">
                <div class="modal-header modal-media-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="box-title"> <?php echo $this->lang->line('add_lesson'); ?>
                    </h4>
                </div>
				<div  class="scroll-area">
                <div class="modal-body pt0 pb0">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <form id="add_lesson_form" method="post" enctype="multipart/form-data" class="ptt10">
                                <input type="hidden" name="lesson_course_id" id="lesson_course_id">
                                <input type="hidden" name="add_lesson_section_id" id="add_lesson_section_id">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('title'); ?></label><small class="req"> *</small>
                                            <input type="text" id="title" autocomplete="off" class="form-control" name="title">

                                            <span id="title_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('lesson_type'); ?></label><small class="req"> *</small>
                                            <select class="form-control" name="lesson_type" onchange="getcontent(this.value)">
                                            <option value=""><?php echo $this->lang->line('select') ?></option>
                                            <?php foreach ($lesson_type as $key => $lvalue) {?>
                                                <option value="<?php echo $key ?>"><?php echo $lvalue ?></option>
                                            <?php }?>
                                            </select>
                                            <span id="lesson_type_error" class="text-danger"><?php echo form_error('lesson_type'); ?></span>
                                            <div class="form-group displaynone" id="attachment"><br>
                                            <label><?php echo $this->lang->line('attachment'); ?> 
                                            </label><small class="req"> *</small>
                                            <input autofocus=""  name="lesson_attachment" id="lesson_attachment"  type="file" class="form-control filestyle" value="" />
                                            <span class="text-danger"><?php echo form_error('lesson_attachment'); ?></span>
                                            </div>
                                            <div id="video_detail" class="displaynone"><br>
                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('video_provider'); ?></label>
                                                <select class="form-control" id="lesson_provider" onclick="checkLessonProvider()" name="lesson_provider">
                                                    <?php foreach ($course_provider as $key => $cpvalue) {?>
                                                        <option value="<?php echo $key ?>"><?php echo $cpvalue ?></option>
                                                    <?php }?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('lesson_provider'); ?></span>
                                            </div>
                                            <div class="form-group" id="lesson_url_div">
                                                <label><?php echo $this->lang->line('video_url'); ?></label><small class="req"> *</small>

                                                <input autofocus="" name="lesson_url" type="text" class="form-control" value="" />

                                                <span class="text-danger"><?php echo form_error('video_url'); ?></span>

                                            </div>

                                            <div class="form-group" id="lesson_file_div">
                                                <label><?php echo $this->lang->line('lesson_file'); ?>
												</label><small class="req"> *</small>
                                                <input autofocus="" name="lesson_file" class="filestyle form-control" type="file" value="" />
                                                <span class="text-danger"><?php echo form_error('video_url'); ?></span>
                                            </div>
                                            <div class="form-group relative">
                                                <label><?php echo $this->lang->line('duration'); ?></label><small class="req"> *</small>
                                                <input autofocus="" name="lesson_duration" placeholder="HH:MM:SS" type="text" class="form-control timepicker">
                                                <span class="text-danger"><?php echo form_error('duration'); ?></span>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('inline_preview_image'); ?> (700px X 400px)</label><small class="req"> *</small>
                                            <input type="file" id="thumbnail" autocomplete="off" class="filestyle form-control" name="add_lesson_thumbnail">
                                            <span id="thumbnail_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('summary'); ?></label>
                                            <textarea rows="5" id="title" autocomplete="off" class="form-control" name="summary"></textarea>
                                            <span id="title_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                </div><!--./row-->
                            </form>
                        </div><!--./col-md-12-->
                    </div><!--./row-->
                    <div class="row">
                        <div class="box-footer col-md-12">
                            <button id="save_lesson" class="btn btn-info pull-right"><span id="lesson_loader"></span><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="edit_lesson_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-media-content">
                <div class="modal-header modal-media-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="box-title"> <?php echo $this->lang->line('edit_lesson'); ?>
                    </h4>
                </div>
				<div  class="scroll-area">
                <div class="modal-body pt0 pb0">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <form id="edit_lesson_form" method="post" enctype="multipart/form-data" class="ptt10">
                                <input type="hidden" name="edit_lesson_course_id" id="edit_lesson_course_id">
                                <input type="hidden" name="lesson_section_id" id="lesson_section_id">
                                <input type="hidden" name="lessons_id" id="lessons_id">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('title'); ?></label><small class="req"> *</small>
                                            <input type="text" id="lesson_titleID" autocomplete="off" class="form-control" name="lesson_titleID">
                                            <span id="title_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('lesson_type'); ?></label><small class="req"> *</small>
                                            <select class="form-control" id="lesson_selectedID" name="lessons_type" onchange="geteditcontent(this.value)">
                                                <option value=""><?php echo $this->lang->line('select') ?></option>
                                                <?php foreach ($lesson_type as $key => $lvalue) {?>
                                                <option value="<?php echo $key ?>"><?php echo $lvalue ?></option>
                                            <?php }?>
                                            </select>
                                            <span id="lesson_type_error" class="text-danger"></span>
                                        </div>
                                        <div class="form-group displaynone" id="editattachment">
                                        <label><?php echo $this->lang->line('attachment'); ?></label>
                                        <input autofocus=""  name="lesson_attachment"   type="file" class="form-control filestyle" value="" />
                                        <input type="hidden" name="old_attachment_img" id="old_attachment_img_id">
                                        <span class="text-danger"><?php echo form_error('lesson_attachment'); ?></span>
                                    </div>
                                    <div id="editvideo_detail" class="displaynone">
                                        <div class="form-group">

                                        <label><?php echo $this->lang->line('video_provider'); ?></label>
                                        <select class="form-control" onclick="checkEditLessonProvider()" name="lesson_provider" id="lesson_provider_edit" >
                                        <?php foreach ($course_provider as $key => $cpvalue) {?>
                                        <option value="<?php echo $key ?>"><?php echo $cpvalue ?></option>
                                        <?php }?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('lesson_provider'); ?></span>
                                        </div>
                                        <div class="form-group" id="lesson_url_edit_div">
                                            <label><?php echo $this->lang->line('video_url'); ?></label><small class="req"> *</small>
                                            <input autofocus=""  name="lesson_url" id="lesson_urlID"  type="text" class="form-control " value="" />
                                            <span class="text-danger"><?php echo form_error('video_url'); ?></span>
                                        </div>
                                        <div class="form-group" id="lesson_file_edit_div">
                                                <label> <?php echo $this->lang->line('lesson_file'); ?></label>
                                                <input autofocus="" name="lesson_file" class="filestyle form-control" type="file" value="" />
                                                <span class="text-danger"><?php echo form_error('video_url'); ?></span>
                                            </div>
                                        <div class="form-group relative">
                                            <label><?php echo $this->lang->line('duration'); ?></label>
                                            <input autofocus=""  name="lesson_duration" id="lesson_durationID" placeholder="HH:MM:SS" type="text" class="form-control timepicker">
                                            <span class="text-danger"><?php echo form_error('duration'); ?></span>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('inline_preview_image'); ?> (700px X 400px)</label>
                                            <input type="file" id="lesson_thumbnail" autocomplete="off" class="form-control filestyle" name="lesson_thumbnail">
                                            <input type="hidden" name="old_background" id="lesson_old_img_id">
                                            <span id="lesson_thumbnail_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('summary'); ?></label>
                                            <textarea rows="5" id="lessons_summaryID" autocomplete="off" class="form-control" name="lessons_summary"></textarea>
                                            <span id="lesson_summaryID_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                </div><!--./row-->
                            </form>
                        </div><!--./col-md-12-->
                    </div><!--./row-->
                    <div class="row">
                        <div class="box-footer col-md-12">
                            <button id="edit_lesson_btn" class="btn btn-info pull-right"><span id="lesson_loaders"></span> <?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_quiz_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-media-content">
                <div class="modal-header modal-media-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="box-title"> <?php echo $this->lang->line('add_new_quiz'); ?>
                    </h4>
                </div>

                <div class="modal-body pt0 pb0">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <form id="add_quiz_form" method="post" class="ptt10" enctype="multipart/form-data">
                                <input type="hidden" name="quiz_courseid" id="quiz_courseid">
                                <input type="hidden" name="sectionId" id="sectionId">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('quiz_title'); ?></label><small class="req"> *</small>
                                            <input type="text" id="quiz_title" autocomplete="off" class="form-control" name="quiz_title">
                                            <span id="title_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                </div><!--./row-->

                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('instruction'); ?></label>
                                            <textarea rows="5"  class="form-control" name="quiz_instruction" id="quiz_instruction"></textarea>
                                            <span id="quiz_instruction_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div><!--./col-md-12-->
                    </div><!--./row-->
                    <div class="row">
                        <div class="box-footer col-md-12">
                            <a  id="add_quiz_btn" class="btn btn-info pull-right"><span id="quiz_loader"></span> <?php echo $this->lang->line('save'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <div class="modal fade" id="edit_quiz_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-media-content">
                <div class="modal-header modal-media-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="box-title"> <?php echo $this->lang->line('edit_quiz'); ?>
                    </h4>
                </div>
                <div class="modal-body pt0 pb0">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <form id="edit_quiz_form" method="post" class="ptt10" enctype="multipart/form-data">
                                <input type="hidden" name="edit_quiz_course" id="edit_quiz_course">
                                <input type="hidden" name="quizId" id="quizId">
                                <input type="hidden" name="edit_sectionId" id="edit_sectionId">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('quiz_title'); ?></label><small class="req">* </small>
                                            <input type="text" id="edit_quiz_title" autocomplete="off" class="form-control" name="edit_quiz_title">
                                            <span id="title_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                </div><!--./row-->

                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="pwd"><?php echo $this->lang->line('instruction'); ?></label>
                                            <textarea rows="5"  class="form-control" name="edit_quiz_instruction" id="edit_quiz_instruction"></textarea>
                                            <span id="quiz_instruction_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div><!--./col-md-12-->
                    </div><!--./row-->
                    <div class="row">
                        <div class="box-footer col-md-12">
                            <a  id="edit_quiz_btn" class="btn btn-info pull-right"> <span id="quiz_loaders"></span><?php echo $this->lang->line('save'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


 <div class="modal fade" id="question_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content modal-media-content">
      <div class="modal-header modal-media-header">
        <div class="quizplusrighttop">
          <a id="add_new_question_btn" class="btn btn-info quizsavebtn"> <span id="question_loader"></span> <?php echo $this->lang->line('save'); ?></a>
          <button type="button" class="add-row plusgreenbtn addplus" data-toggle="tooltip"data-original-title="<?php echo $this->lang->line('add_question'); ?>"><i class='fa fa-plus'></i></button>

        </div>
        <button type="button" onclick="clear_question()" class="close" data-dismiss="modal">&times;</button>
        <h4 class="box-title"> <?php echo $this->lang->line('quiz_questions'); ?></h4><span id="total_question_" >&nbsp;</span>
      </div>
        <div class="scroll-area">
          <div class="modal-body pb5">
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12">
              <form id="add_new_question_form_ID" method="post">
                <input type="hidden" name="quiz_id" id="quiz_id">
                <input type="hidden" name="question_course_id" id="question_course_id">
                <table id="table_id" class="table tableinput">
                  <tbody>
                    <tr id="rowID0">
                      <td class="border0 pl0" width="75"><?php echo $this->lang->line('question'); ?><small class="req"> *</small></td>
                      <td class="pr0 border0 relative">
                        <input type='text' name='question_0' class="form-control pull-left">
                        <button type='button' data-toggle='tooltip' data-original-title='<?php echo $this->lang->line('delete_question'); ?>' data-placement="left" data-id='0' class='delete-row addclose quizplusright'><i class='fa fa-remove'></i></button>
                      </td>
                    </tr>
                    <tr class='options0'>
                      <td colspan="2" class="border0">
                        <div class="quizopationpad-left">
                            <table width="100%" align="right">
                             <tr>
                              <td width="30">A <small class="req"> *</small></td>
                              <td>
                                <div class="input-group input-group-full-width">
                                <input type='text' name='question_0_options_0' class="form-control">
                                <span class="input-group-addon input-group-addon-bg"><input type='checkbox' value="option_1" name='question_0_result_0[]' title="<?php echo $this->lang->line('check_for_correct_option'); ?>"></span>
                                </div>
                              </td>
                            </tr>
                            <tr>
                              <td>B <small class="req"> *</small></td>
                              <td>
                                <div class="input-group input-group-full-width">
                                <input type='text' name='question_0_options_1' class="form-control">
                                <span class="input-group-addon input-group-addon-bg"><input type='checkbox' value="option_2" name='question_0_result_0[]' title="<?php echo $this->lang->line('check_for_correct_option'); ?>"></span>
                                </div>
                              </td>
                            </tr>
                            <tr>
                              <td>C</td>
                              <td>
                                <div class="input-group input-group-full-width">
                                <input type='text' name='question_0_options_2' class="form-control">
                                <span class="input-group-addon input-group-addon-bg"><input type='checkbox' value="option_3" name='question_0_result_0[]' title="<?php echo $this->lang->line('check_for_correct_option'); ?>"></span>
                                </div>
                              </td>
                            </tr>
                            <tr>
                              <td>D</td>
                              <td>
                                <div class="input-group input-group-full-width">
                                <input type='text' name='question_0_options_3' class="form-control">
                                <span class="input-group-addon input-group-addon-bg"><input type='checkbox' value="option_4" name='question_0_result_0[]' title="<?php echo $this->lang->line('check_for_correct_option'); ?>"></span>
                                </div>
                              </td>
                            </tr>
                            <tr>
                              <td>E</td>
                              <td>
                                <div class="input-group input-group-full-width">
                                <input type='text' name='question_0_options_4' class="form-control">
                                <span class="input-group-addon input-group-addon-bg"><input type='checkbox' value="option_5" name='question_0_result_0[]' title="<?php echo $this->lang->line('check_for_correct_option'); ?>"></span>
                                </div>
                              </td>
                            </tr>
                          </table>
                        </div>  
                      </td>
                    </tr>
                  </tbody>
                </table>
                <input type="hidden" id="question_count" name="question_count" value="0"/>

              </form>
            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="edit_question_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-media-content">
                <div class="modal-header modal-media-header">
          <div class="quizplusrighttop">
            <a id="edit_new_question_btn" class="btn btn-info quizsavebtn"> <span id="question_loaders"></span><?php echo $this->lang->line('save'); ?></a>
            <button type="button" class="edit-row plusgreenbtn addplus" data-toggle="tooltip"data-original-title="<?php echo $this->lang->line('add_question'); ?>"><i class='fa fa-plus'></i></button>

          </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="box-title"> <?php echo $this->lang->line('quiz_questions'); ?></h4>
                    <?php echo $this->lang->line('total_question'); ?> <span id="total_edit_question" ></span>
          </div>
        <div class="scroll-area">
          <div class="modal-body pb0">
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12">
                <form id="edit_question_form" method="post">
                  <input type="hidden" name="editquestion_course_id" id="editquestion_course_id">
                  <div id="edit_question"></div>
                </form>
              </div>
            </div>
          </div>
                </div>
            </div>
        </div>    </div>



    <div id="course_preview_modal" class="modal fade" role="dialog">
      <div class="modal-dialog modalwrapwidth">
        <div class="modal-content">
          <button type="button" class="close" data-dismiss="modal" onclick="stopvideo()">&times;</button>
            <div class="scroll-area">
              <div class="modal-body paddbtop">
                  <div class="row">
                    <div id="course_preview">


                    </div>
                  </div><!--./row-->
              </div><!--./modal-body-->
          </div>
        </div>
      </div>
    </div><!--#/coursedetailmodal-->

<script>
(function ($) {

 "use strict";

 $('.miusttop5').click(function(){	 
	 CKEDITOR.replace('editor1',
    {
      allowedContent: true
    });
	  
  });
})(jQuery);
</script>

<script>

  ( function ( $ ) {
    'use strict';
    $(document).ready(function() {
      emptyDatatable('course-list','data');
    });

  } ( jQuery ) );
</script>
<script>
    ( function ( $ ) {
		'use strict';
		$(document).ready(function () {
			initDatatable('course-list','onlinecourse/course/getcourselist',[],[],100);
		});
	} ( jQuery ) );
</script>
<script>
  $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
   $($.fn.dataTable.tables(true)).DataTable()
      .columns.adjust();
});
</script>
<script>
function clear_question()
{
  var total_question = $('#total_question').html();
  for (i = 1; i <= total_question; i++) {
    $('#rowID'+i).remove();
    $('.options'+i).remove();
    var new_count = $('#question_count').val();
    var count = 1;
    var new_count = new_count - count ;
    $('#question_count').val(new_count);
    var total_question = total_question - count;
    $('#total_question').html(total_question);
  }
}


function getcontent(type)
{
  if (type == 'video') {
    $("#video_detail").show();
    $("#attachment").hide();
  } else {
    $("#video_detail").hide();
    $("#attachment").show();
  }
}


function geteditcontent(type){

  if (type == 'video' ) {
    $("#editvideo_detail").show();
    $("#editattachment").hide();
  } else {
    $("#editvideo_detail").hide();
    $("#editattachment").show();
  }
}


function add_outcomerow()
{
  var table = document.getElementById("outcometableID");
  var table_len = (table.rows.length);
  var id = parseInt(table_len + 1);
  var div = "<td><div class='form-group'><input type='text' name='outcomes[]' class='form-control'></div></td>";
  var row = table.insertRow(table_len).outerHTML = "<tr id='outcomerow" + id + "'>" + div + "<td valign='top'><button type='button' onclick='delete_outcomerow(" + id + ")' class='addclose'><i class='fa fa-remove'></i></button></td></tr>";

}

function delete_outcomerow(id)
{
  var table = document.getElementById("outcometableID");
  var rowCount = table.rows.length;
  $("#outcomerow" + id).remove();
}

function openCourse(evt, courseName) {

	if(courseName == 'course_card_tab'){
		$('#search_area').addClass('hide');
	}else{
		$('#search_area').removeClass('hide');
	}

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

 $('.course_preview_id').click(function(){
    var courseID = $(this).attr('data-id');
	$('#course_preview').html('');
    $.ajax({
     url  : "<?php echo base_url(); ?>onlinecourse/course/coursepreview",
     type : 'post',
     data : {courseID:courseID},
     beforeSend: function () {
      $('#course_preview').html('Loading...  <i class="fa fa-spinner fa-spin"></i>');
     },

     success : function(response){
       $('#course_preview').html(response);
     }
    });
  })

$(document).ready(function(){
    $('#course_detail_tab').show();
});

$('#add_course_btn').click(function(){
	
  var formData = new FormData($('#add_course_form_ID')[0]);
  formData.append('description', CKEDITOR.instances['editor1'].getData());
  $.ajax({
        url: '<?php echo base_url(); ?>onlinecourse/course/create',
        type: 'post',
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () {
          $('#loader_btn').html('<i class="fa fa-spinner fa-spin"></i>');
        },

        success: function(data){
          var result = JSON.parse(data);
           if (result.status == "fail") {
              var message = "";
              $.each(result.error, function (index, value) {
                  message += value;
              });
              errorMsg(message);

          } else {

              successMsg(result.message);
              $('#add_course_modal').modal('hide');
              $('#course_detail_modal').modal('show');
              $('#add_course_form_ID')[0].reset();
              CKEDITOR.instances.editor1.setData('');
              $(".dropify-clear").trigger("click");
              loadcoursedetail(result.course_id);

          }
        },

        error: function (xhr) { // if error occured
          $('#loader_btn').html('');
        },

        complete: function () {
          $('#loader_btn').html('');
        }
    });
});
})(jQuery);

function loadcoursedetail(courseID) {
	$('#course_detail').html('');
  $.ajax({
   url  : "<?php echo base_url(); ?>onlinecourse/course/coursedetail",
   type : 'post',
   data : {courseID:courseID},
   beforeSend: function () {
      $('#course_detail').html('Loading...  <i class="fa fa-spinner fa-spin"></i>');
    },

   success : function(response){
     $('#course_detail').html(response);
   }
 });
}

function publish_unpublish(courseID,status,title) {	
	if(status == 1){
		var confirmationBox = confirm("<?php echo $this->lang->line('are_you_sure_to_publish_course'); ?>");
	} else if (status == 0){
		var confirmationBox = confirm("<?php echo $this->lang->line('are_you_sure_to_unpublish_course'); ?>");	
	}	

	if (confirmationBox == true) {
	$.ajax({
		url  : "<?php echo base_url(); ?>onlinecourse/course/publish_unpublish",
		type : 'post',
		data : {courseID:courseID,status:status,title:title},  

		success : function(response){			

			successMsg('<?php echo $this->lang->line('update_message'); ?>');
			loadcoursedetail(courseID);
		}
	});
	}
}


(function ($) {
 "use strict";
$('.course_detail_id').click(function(){
 var courseID = $(this).attr('data-id');
  $.ajax({
    url  : "<?php echo base_url(); ?>onlinecourse/course/coursedetail",
    type : 'post',
    data : {courseID:courseID},
    beforeSend: function () {
      $('#course_detail').html('Loading...  <i class="fa fa-spinner fa-spin"></i>');
    },

    success : function(response){
      $('#course_detail').html(response);
   }
  });
});
})(jQuery);
</script>

<script type="text/javascript">
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
<script>
(function ($) {
  "use strict";  

    $("#class_id").change(function() {
		$('.class-list').select2();
	  $('.section-list').select2();
    var class_id = $('#class_id').val();
    var base_url = '<?php echo base_url() ?>';
    $('#section_id').html('');
	  var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
    $.ajax({
      url: base_url + "sections/getByClass",
      type: "GET",
      data: {'class_id': class_id},
      dataType: "json", 

      success: function (data) { 				

					$.each(data, function (i, obj)

                {
                $('#section_id').append("<option value=" + obj.id + ">" + obj.section + "</option>");

                });			

                },	    

    });
  });

})(jQuery);

function saveSection() {
  var courseid = $('#courseid').val();
  $.ajax({
    url: '<?php echo base_url(); ?>onlinecourse/coursesection/addsection/',
    type: 'POST',
    dataType: 'json',
    data: $("#formadd").serialize(),
    beforeSend: function () {
      $('#section_loader').html('<i class="fa fa-spinner fa-spin"></i>');
    },

    success: function (data) {
      if (data.status == "fail") {
        var message = "";

        $.each(data.error, function (index, value) {
          message += value;
        });
        errorMsg(message);
      } else {
        successMsg(data.message);
        $("#add_section_modal").modal('hide');
        coursedetail(courseid);
      }

    },

    error: function (xhr) { // if error occured
      $('#section_loader').html('');
    },

    complete: function () {
      $('#section_loader').html('');
    }
  });
}

function coursedetail(courseid) {
	$('#course_detail').html('');
  $.ajax({
    url  : "<?php echo base_url(); ?>onlinecourse/course/coursedetail",
    type : 'post',
    data : {courseID:courseid},
    success : function(response){
      $('#course_detail').html(response);
    }
  });
}

(function ($) {
  "use strict";
  $('#edit_section_btn').click(function(){
    var formData = new FormData($('#edit_section_form')[0]);
    var courseid = $('#courseid').val();
    $.ajax({
        url: '<?php echo base_url(); ?>onlinecourse/coursesection/editsection',
        type: 'post',
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {

          $('#section_loaders').html('<i class="fa fa-spinner fa-spin"></i>');

        },

        success: function(data){
            var result = JSON.parse(data);
            if (result.status == "fail") {
                var message = "";
                $.each(result.error, function (index, value) {
                    message += value;
                });
                errorMsg(message);
            } else {
                successMsg(result.message);
                $("#edit_section_modal").modal('hide');
                coursedetail(courseid);
            }
        },

        error: function (xhr) { // if error occured
          $('#section_loaders').html('');

        },
        complete: function () {
          $('#section_loaders').html('');
        }
    });

  });


  $('#add_quiz_btn').click(function(){
    var formData = new FormData($('#add_quiz_form')[0]);
    var courseid = $('#courseid').val();
    $.ajax({

        url: '<?php echo base_url(); ?>onlinecourse/coursequiz/add',
        type: 'post',
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {
          $('#quiz_loader').html('<i class="fa fa-spinner fa-spin"></i>');
        },

        success: function(data){
            var result = JSON.parse(data);
             if (result.status == "fail") {
                var message = "";
                $.each(result.error, function (index, value) {
                    message += value;
                });

                errorMsg(message);
            } else {

              successMsg(result.message);
              $("#add_quiz_modal").modal('hide');
              coursedetail(courseid);
            }
        },

        error: function (xhr) { // if error occured
          $('#quiz_loader').html('');
        },

        complete: function () {
          $('#quiz_loader').html('');
        }
    });
  });


  $('#edit_quiz_btn').click(function(){
    var formData = new FormData($('#edit_quiz_form')[0]);
    var courseid = $('#courseid').val();
    $.ajax({
        url: '<?php echo base_url(); ?>onlinecourse/coursequiz/edit',
        type: 'post',
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {
          $('#quiz_loaders').html('<i class="fa fa-spinner fa-spin"></i>');
        },

        success: function(data){
        var result = JSON.parse(data);
            if (result.status == "fail") {
                var message = "";
                $.each(result.error, function (index, value) {
                    message += value;
                });
                errorMsg(message);
            } else {
                successMsg(result.message);
                $("#edit_quiz_modal").modal('hide');
               coursedetail(courseid);
            }
        },

        error: function (xhr) { // if error occured
          $('#quiz_loaders').html('');
        },

        complete: function () {
          $('#quiz_loaders').html('');
        }
    });
  });


  $("#save_lesson").click(function(){
    var courseid = $('#courseid').val();
    var formData = new FormData($('#add_lesson_form')[0]);
    var files = $('#thumbnail')[0].files;
      $.ajax({
        url: '<?php echo base_url(); ?>onlinecourse/courselesson/addlesson',
        type: 'post',
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {
          $('#lesson_loader').html('<i class="fa fa-spinner fa-spin"></i>');
        },

        success: function(data){
          var result = JSON.parse(data);
           if (result.status == "fail") {
              var message = "";
              $.each(result.error, function (index, value) {
                  message += value;
              });
              errorMsg(message);
          } else {
              successMsg(result.message);
              $("#add_lesson_modal").modal('hide');
              coursedetail(courseid);
          }
        },

        error: function (xhr) { // if error occured
          $('#lesson_loader').html('');
          $('#thumbnail_error').html("Please select image.");
        },

        complete: function () {
          $('#lesson_loader').html('');
        }
      });
  });

  $("#edit_lesson_btn").click(function(){
    var courseid = $('#courseid').val();
    var formData = new FormData($('#edit_lesson_form')[0]);
    var files = $('#lesson_thumbnail')[0].files;
      $.ajax({
        url: '<?php echo base_url(); ?>onlinecourse/courselesson/editlesson',
        type: 'post',
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {
          $('#lesson_loaders').html('<i class="fa fa-spinner fa-spin"></i>');

        },

        success: function(data){
          var result = JSON.parse(data);
           if (result.status == "fail") {
              var message = "";
              $.each(result.error, function (index, value) {
                  message += value;
              });

              errorMsg(message);

          } else {
              successMsg(result.message);
              $("#edit_lesson_modal").modal('hide');
              coursedetail(courseid);
          }

        },

        error: function (xhr) { // if error occured
          $('#lesson_loaders').html('');
          $('#lesson_thumbnail_error').html("Please select image.");
        },

        complete: function () {
          $('#lesson_loaders').html('');
        }
      });
  });

  $('#add_new_question_btn').click(function(){
    var courseid = $('#courseid').val();
    var formData = new FormData($('#add_new_question_form_ID')[0]);

      $.ajax({
        url: '<?php echo base_url(); ?>onlinecourse/coursequiz/addnewquestion',
        type: 'post',
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {

          $('#question_loader').html('<i class="fa fa-spinner fa-spin"></i>');

        },

        success: function(data){
          var result = JSON.parse(data);
            if (result.status == "fail") {

                var message = "";
                $.each(result.error, function (index, value) {

          errorMsg(value);

                });
            } else {
                successMsg(result.message);
                $('#add_new_question_form_ID')[0].reset();
                $('#question_modal').modal('hide');
                window.location.reload(true);
            }
          },

          error: function (xhr) { // if error occured
            $('#question_loader').html('');
          },

          complete: function () {
            $('#question_loader').html('');
          }
      });
  });

  $('#edit_new_question_btn').click(function(){
    var courseid = $('#courseid').val();
    var formData = new FormData($('#edit_question_form')[0]);
      $.ajax({
        url: '<?php echo base_url(); ?>onlinecourse/coursequiz/editnewquestion',
        type: 'post',
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {
          $('#question_loaders').html('<i class="fa fa-spinner fa-spin"></i>');

        },
        success: function(data){
          var result = JSON.parse(data);
            if (result.status == "fail") {
                var message = "";
                $.each(result.error, function (index, value) {
          errorMsg(value);                   

                });               

            } else {
                successMsg(result.message);             

                window.location.reload(true);            

            }
          },

          error: function (xhr) { // if error occured
            $('#question_loaders').html('');
          },

          complete: function () {
            $('#question_loaders').html('');
          }
      });
  });

  $('.close_btn').click(function(){
    var courseid = $('#courseid').val();
    $('#order_section_modal').modal('hide');
    coursedetail(courseid);
  });
})(jQuery);

</script>
<script>
(function ($) {
  "use strict";
  $(document).ready(function() {

    $(".add-row").click(function() {
    var i = $('#question_count').val();
    i++;
    $('#question_count').val(i);
    var totalquestion = 1 + i;
    $('#total_question').html(totalquestion);  
    var markup = "<tr id='rowID"+i+"'><td width='75' class='border0 pl0'><?php echo $this->lang->line('question'); ?> <small class='req'> *</small></td><td class='pr0 border0 relative'><input type='text' name='question_"+i+"' class='form-control pull-left'><button type='button' data-toggle='tooltip' data-placement='left' data-original-title='<?php echo $this->lang->line('delete_question'); ?>' data-id='"+i+"' class='delete-row addclose quizplusright'><i class='fa fa-remove'></i></button></td></tr><tr class='options"+i+"'><td colspan='2' class=' border0'><div class='quizopationpad-left'><table width='100%' align='right'><tr><td width='30'>A <small class='req'> *</small></td><td><div class='input-group input-group-full-width'><input type='text' name='question_"+i+"_options_0' class='form-control'><span class='input-group-addon input-group-addon-bg'><input type='checkbox' title='<?php echo $this->lang->line('check_for_correct_option'); ?>' value='option_1' name='question_"+i+"_result_"+i+"[]'></span></div></td></tr><tr><td>B <small class='req'> *</small></td><td><div class='input-group input-group-full-width'><input type='text' name='question_"+i+"_options_1' class='form-control'><span class='input-group-addon input-group-addon-bg'><input type='checkbox'  title='<?php echo $this->lang->line('check_for_correct_option'); ?>' value='option_2' name='question_"+i+"_result_"+i+"[]'></span></div></td></tr><tr><td>C</td><td><div class='input-group input-group-full-width'><input type='text' name='question_"+i+"_options_2' class='form-control'><span class='input-group-addon input-group-addon-bg'><input type='checkbox'  title='<?php echo $this->lang->line('check_for_correct_option'); ?>' value='option_3' name='question_"+i+"_result_"+i+"[]'></span></div></td></tr><tr><td>D</td><td><div class='input-group input-group-full-width'><input type='text' name='question_"+i+"_options_3' class='form-control'><span class='input-group-addon input-group-addon-bg'><input type='checkbox'  title='<?php echo $this->lang->line('check_for_correct_option'); ?>' value='option_4' name='question_"+i+"_result_"+i+"[]'></span></div></td></tr><tr><td>E</td><td><div class='input-group input-group-full-width'><input type='text' name='question_"+i+"_options_4' class='form-control'><span class='input-group-addon input-group-addon-bg'><input type='checkbox'  title='<?php echo $this->lang->line('check_for_correct_option'); ?>' value='option_5' name='question_"+i+"_result_"+i+"[]'></span></div></td></tr></table></div></td></tr></tr>";

    $("#table_id").append(markup);
    });


  $(document).on('click','.delete-row',(function() {
    var removeQuestionID = $(this).attr('data-id');
    $('#rowID'+removeQuestionID).remove();
    $('.options'+removeQuestionID).remove();
    var new_count = $('#question_count').val();
    var count = 1;
    var new_count = new_count - count ;
    $('#question_count').val(new_count);
    var total_question = $('#total_question').html() - count;
    $('#total_question').html(total_question);
    }));

  $(".edit-row").click(function() {
    var i = $('#questioncount').val();
    i++;

    $('#questioncount').val(i);
    $('#total_edit_question').html(i);
    var markup = "<tr id='rowIDedit"+i+"'><td width='75' class='border0 pl0'><?php echo $this->lang->line('question'); ?> <small class='req'> *</small></td><td class='pr0 border0 relative'><input type='text' name='question_"+i+"' class='form-control pull-left'><input type='hidden' name='question_id_"+i+"' value='0' class='form-control pull-left'><button type='button' data-toggle='tooltip' data-placement='left' data-original-title='<?php echo $this->lang->line('delete_question'); ?>' data-id='"+i+"' class='delete-edit-row addclose quizplusright'><i class='fa fa-remove'></i></button></td></tr><tr class='optionsedit"+i+"'><td colspan='2' class='pr0 border0 quizopationpad-left'><table width='100%' align='right'><tr><td width='30'>A <small class='req'> *</small></td><td><div class='input-group input-group-full-width'><input type='text' name='question_"+i+"_option_0' class='form-control'><span class='input-group-addon input-group-addon-bg'><input type='checkbox'  title='<?php echo $this->lang->line('check_for_correct_option'); ?>' value='option_1' name='question_"+i+"_result_"+i+"[]'></span></div></td></tr><tr><td>B <small class='req'> *</small></td><td><div class='input-group input-group-full-width'><input type='text' name='question_"+i+"_option_1' class='form-control'><span class='input-group-addon input-group-addon-bg'><input type='checkbox'  title='<?php echo $this->lang->line('check_for_correct_option'); ?>' value='option_2' name='question_"+i+"_result_"+i+"[]'></span></div></td></tr><tr><td>C</td><td><div class='input-group input-group-full-width'><input type='text' name='question_"+i+"_option_2' class='form-control'><span class='input-group-addon input-group-addon-bg'><input type='checkbox'  title='<?php echo $this->lang->line('check_for_correct_option'); ?>' value='option_3' name='question_"+i+"_result_"+i+"[]'></span></div></td></tr><tr><td>D</td><td><div class='input-group input-group-full-width'><input type='text' name='question_"+i+"_option_3' class='form-control'><span class='input-group-addon input-group-addon-bg'><input type='checkbox'  title='<?php echo $this->lang->line('check_for_correct_option'); ?>' value='option_4' name='question_"+i+"_result_"+i+"[]'></span></div></td></tr><tr><td>E</td><td><div class='input-group input-group-full-width'><input type='text' name='question_"+i+"_option_4' class='form-control'><span class='input-group-addon input-group-addon-bg'><input type='checkbox'  title='<?php echo $this->lang->line('check_for_correct_option'); ?>' value='option_5' name='question_"+i+"_result_"+i+"[]'></span></div></td></tr></table></td></tr><tr>";

    $("#edit_table_id").append(markup);
    });


  $(document).on('click','.delete-edit-row',(function() {
    var removeQuestionID = $(this).attr('data-id');
    var question_id = $('#question_id_'+removeQuestionID).val();
    $('#rowIDedit'+removeQuestionID).remove();
    $('.optionsedit'+removeQuestionID).remove();
    var new_count = $('#questioncount').val();
    var count = 1;

    var new_count = new_count - count ;
    $('#questioncount').val(new_count);
    var deleted = $('#deleted').val();

    if (question_id != undefined){
      $('#deleted').val(deleted+ ',' +question_id);
    }
    var total_edit_question = $('#total_edit_question').html() - count;
    $('#total_edit_question').html(total_edit_question);
  }));
  });
})(jQuery);

function deloption(qid,optionid){
  $("#options_rowID"+qid+"_"+optionid).remove();
}

(function ($) {
  "use strict";
    $('#add_section_modal, #add_lesson_modal, #add_quiz_modal').on('hidden.bs.modal', function () {
    $(this).find('form').trigger('reset');
    $(".select2").select2().select2("val", '');

  })

  var dateNow = new Date();
  $('.timepicker').datetimepicker({
      format: 'HH:mm:ss',
   defaultDate:moment(dateNow).hours(0).minutes(0).seconds(0).milliseconds(0)

  });
})(jQuery);

</script>
<script>
  checkCourseProvider();
  function checkCourseProvider(){
    course_provider = $("#course_provider").val();
    if(course_provider == "s3_bucket"){
      $("#course_url_div").addClass("hide");
      $("#s3_file_div").removeClass("hide");

    }
    else{
      $("#course_url_div").removeClass("hide");
      $("#s3_file_div").addClass("hide");
    }

  }

  checkLessonProvider();
  function checkLessonProvider(){
    course_provider = $("#lesson_provider").val();
    if(course_provider == "s3_bucket"){
      $("#lesson_url_div").addClass("hide");
      $("#lesson_file_div").removeClass("hide");
    }

    else{
      $("#lesson_url_div").removeClass("hide");
      $("#lesson_file_div").addClass("hide");

    }
  }

  checkEditLessonProvider();
  function checkEditLessonProvider(){
    course_provider = $("#lesson_provider_edit").val();
    if(course_provider == "s3_bucket"){
      $("#lesson_url_edit_div").addClass("hide");
      $("#lesson_file_edit_div").removeClass("hide");

    }
    else{
      $("#lesson_url_edit_div").removeClass("hide");
      $("#lesson_file_edit_div").addClass("hide");
    }
  }
</script>
<script>
  function stopvideo(){
    $('#course_preview').html('');
    $('#course_preview_modal').modal('hide');
  } 
</script>
<script>
 function editcourse(courseid){
	 
    var formData = new FormData($('#edit_course_form_ID')[0]);
    formData.append('description', CKEDITOR.instances['editor2'].getData());
	
    $.ajax({
        url: '<?php echo base_url(); ?>onlinecourse/course/updatecourse',
        type: 'post',
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () {
           $('#loader_button').html('<i class="fa fa-spinner fa-spin"></i>');
        },
        success: function(data){
            var result = JSON.parse(data);
            if (result.status == "fail") {
                var message = "";
                $.each(result.error, function (index, value) {
                    message += value;
                });
                errorMsg(message);
            } else {
                successMsg(result.message);
                $('#edit_course_modal').modal('hide');
                $('#edit_course').html('');
				coursedetail(courseid);
                				
            }
        },
        error: function (xhr) { // if error occured
           $('#loader_button').html('');
        },
        complete: function () {
          $('#loader_button').html('');
        }
	});
 }
</script>