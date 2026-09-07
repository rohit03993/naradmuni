<?php

    include"config.php";
 
	if (!isset($_SESSION['aemail'])) {
		$_SESSION['msg'] = "You must log in first";
		header('location: ../manage.php');
	}

	if (isset($_GET['logout'])) {
		session_destroy();
		unset($_SESSION['aemail']);
		header("location: ../manage.php");
	}

 if(!isset($_SESSION['aemail']))
 {
  echo ("<script language='javascript'>
                   window.location.href='logout.php';
                        </script>");
 }

$productsession=$_SESSION['aemail'];

$res=mysqli_query($con,"SELECT * FROM admin WHERE aemail='$productsession'");

$userRow=mysqli_fetch_array($res,MYSQLI_ASSOC);


if(isset($_POST['add']))
                    {
                            // Optional fields only exist for Video type (ajaxVid.php) — never assume they are posted
                            $post = function($key, $default = '') use ($con) {
                                return mysqli_real_escape_string($con, isset($_POST[$key]) ? $_POST[$key] : $default);
                            };

                            $title = $post('title');
                            $latest_news = $post('latest_news', 'No');
                            $description = $post('description');
                            $newsurl = $post('newsurl');
                            $metat = $post('metat');
                            $metad = $post('metad');
                            $category = $post('category');
                            $top_side_bar = $post('top_side_bar', 'No');
                            $slider = $post('slider', 'No');
                            $slider_priority = $post('slider_priority', '0');
                            $latest_priority = $post('latest_priority', '0');
                            $show_home = $post('show_home', 'No');
                            $short_description = $post('short_description');
                            $date = date("d-m-Y");
                            $time = date('H:i');
                            $newstype = $post('newstype', 'Content');
                            $img_source = $post('img_source');
                            $img_abt = $post('img_abt');
                            $v_link = $post('videolink');
                            $team_id = $post('team_id', '0');
                            $hashtags = $post('hashtags');
                            $pub_date_time = $post('pub_date_time');
                            $status = 'Unpublished';
                            $name = (isset($_FILES['video_file']['name']) ? $_FILES['video_file']['name'] : '');
                            $video_id = '';
                            $post_image = '';

                            if (empty($title)) { array_push($errors, "Kindly fill news title"); }
                            if (empty($short_description)) { array_push($errors, "Kindly fill Short Description"); }
                            if (empty($newsurl)) { array_push($errors, "Kindly fill news url"); }
                            if (empty($metat)) { array_push($errors, "Kindly fill meta title"); }
                            if (empty($category) || $category === '0') { array_push($errors, "Kindly fill news category"); }
                            if (empty($_FILES['image']['tmp_name'])) { array_push($errors, "Kindly add image"); }

                            /* Linkname */
                            $replace = array(" ",",",".","'","&","-","_",":","(",")","+",";","#","!","*","{","}","[","]","?","/","\"","|","@","%","$");
                            $linkStr_Replace = str_replace($replace, "-", trim($newsurl));
                            $linkStr_Replace = str_replace(array("----","---","--"), "-", $linkStr_Replace);
                            $linkname = $linkStr_Replace;

                            $folderStr_Replace = str_replace($replace, "-", trim($category));
                            $folderStr_Replace = str_replace(array("----","---","--"), "-", $folderStr_Replace);
                            $foldername = $folderStr_Replace . "/";
                            $link = $foldername . $linkname . "/";

                            if (!empty($linkname)) {
                                $res_u = mysqli_query($con, "SELECT newsid FROM `news` WHERE `newsurl`='$linkname' LIMIT 1");
                                if ($res_u && mysqli_num_rows($res_u) > 0) {
                                    array_push($errors, "Sorry... News URL already Exixts");
                                }
                            }

                        if (count($errors) == 0) {

                            if (!empty($v_link)) {
                                $video_parts = explode("?v=", $v_link);
                                if (empty($video_parts[1])) {
                                    $video_parts = explode("/v/", $v_link);
                                }
                                if (empty($video_parts[1])) {
                                    $video_parts = explode("youtu.be/", $v_link);
                                }
                                if (!empty($video_parts[1])) {
                                    $video_parts = explode("&", $video_parts[1]);
                                    $video_id = $video_parts[0];
                                }
                            }

                            if (!empty($name) && !empty($_FILES['video_file']['tmp_name'])) {
                                $target_dir = __DIR__ . "/../videos/";
                                if (!is_dir($target_dir)) {
                                    @mkdir($target_dir, 0755, true);
                                }
                                move_uploaded_file($_FILES['video_file']['tmp_name'], $target_dir . $name);
                            }

                            $tmp_file = $_FILES['image']['tmp_name'];
                            $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                            $rand = md5(uniqid().rand());
                            $post_image = $rand.".".$ext;
                            $news_img_dir = __DIR__ . "/../images/news/";
                            if (!is_dir($news_img_dir)) {
                                @mkdir($news_img_dir, 0755, true);
                            }
                            move_uploaded_file($tmp_file, $news_img_dir . $post_image);

                            if ($slider_priority !== '' && $slider_priority !== '0') {
                                $pri = mysqli_query($con,"SELECT `slider_priority`,`newsid` FROM `news` WHERE `slider`='Yes' AND `slider_priority` >= '$slider_priority' ORDER BY `slider_priority` ASC");
                                while($pr = mysqli_fetch_array($pri)){
                                    $new_slider_priority = $pr['slider_priority']+1;
                                    mysqli_query($con,"UPDATE `news` SET `slider_priority`='$new_slider_priority' WHERE `newsid`='".$pr['newsid']."'");
                                }
                            }

                            if ($latest_priority !== '' && $latest_priority !== '0') {
                                mysqli_query($con, "UPDATE `news` SET `latest_priority`='0' WHERE `latest_priority`='$latest_priority'");
                            }

                            if(empty($slider_priority)){ $slider_priority = '0'; }
                            if(empty($latest_priority)){ $latest_priority = '0'; }

                            $qry="insert into `news` (`newsurl`, `folder`, `seolink`, `metat`, `metad`, `title`, `description`, `short_description`, `image`, `img_abt`, `img_source`, `newstype`, `latest_news`, `category`, `top_side_bar`, `slider`, `date`, `time` , `videoid`, `video_file`, `show_home`, `status`, `slider_priority`, `latest_priority`, `team_id`,`hashtags`, `pub_date_time`) values('$linkname','$foldername','$link','$metat','$metad','$title','$description','$short_description','$post_image','$img_abt','$img_source','$newstype','$latest_news','$category','$top_side_bar','$slider','$date','$time','$video_id','$name','$show_home','$status','$slider_priority','$latest_priority','$team_id','$hashtags','$pub_date_time')";

                             $ex=mysqli_query($con,$qry);
                             $lastInsertId = mysqli_insert_id($con);
                              if($ex>0) {

                            $number1 = isset($_POST["cat_id"]) ? count($_POST["cat_id"]) : 0;
                             if($number1 > 0)
                             {
                                  for($i=0; $i<$number1; $i++)
                                  {
                                       if(trim($_POST["cat_id"][$i] != ''))
                                       {
                                            $cat_id=mysqli_real_escape_string($con, $_POST["cat_id"][$i]);
                                            mysqli_query($con, "INSERT INTO `news_cat`(`category`, `news_id`) VALUES('$cat_id','$lastInsertId')");
                                       }
                                  }
                             }
                                   echo("<script language='javascript'>
                                  window.alert('added Successfully')
                                  window.location.href='news.php';
                                  </script>");
                              }
                              else{ array_push($errors, "Sorry, there was an error: " . mysqli_error($con)); }
                             }

                            }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../include/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="../include/css/style.css">
<link rel="stylesheet" href="../include/css/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.5/css/responsive.dataTables.min.css">
<script src="../include/js/jquery.min.js"></script>
</head>
<body>
<div id="overlay"><div><img src="img/loading.gif" width="64px" height="64px"/></div></div>
    <div class="wrapper">
        <!-- Sidebar  -->
        <?php include"sidebar.php"; ?>

        <!-- Page Content  -->
<div id="content">
            <?php include"header.php"; ?>
            
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Add News</li>
            </ol>
<div class="container-fluid page-content">
                            <?php include('errors.php'); ?>
                            <?php include('sucsess.php'); ?>
            <form id="SubmitForm" class="nm-news-form" method="post" enctype="multipart/form-data">

              <section class="nm-form-section">
                <h2 class="nm-form-section__title">Story</h2>
                <div class="nm-form-grid">
                  <div class="nm-form-field nm-form-field--full">
                    <label class="control-label" for="nm-title">Title</label>
                    <input class="form-control" id="nm-title" type="text" name="title" value="<?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['title']); } ?>" >
                  </div>
                  <div class="nm-form-field nm-form-field--full">
                    <label class="control-label" for="nm-newsurl">News URL</label>
                    <input class="form-control" id="nm-newsurl" type="text" name="newsurl" value="<?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['newsurl']); } ?>" >
                    <p class="nm-form-hint">Public path stays <code>/news/{this-slug}</code> — do not change after publish.</p>
                  </div>
                  <div class="nm-form-field nm-form-field--full">
                    <label class="control-label" for="nm-short">Short Description</label>
                    <textarea class="form-control" id="nm-short" cols="20" rows="3" name="short_description"><?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['short_description']); } ?></textarea>
                  </div>
                </div>
              </section>

              <section class="nm-form-section">
                <h2 class="nm-form-section__title">Where it appears</h2>
                <div class="nm-form-grid">
                  <div class="nm-form-field">
                    <label class="control-label" for="category">Home Category</label>
                    <select class="custom-select" id="category" name="category">
                      <option value="0">select</option>
                      <?php
                      $query = $con->query("SELECT * FROM `categories` ORDER BY id ASC");
                      $rowCount = $query->num_rows;
                      if($rowCount > 0){
                          while($row = $query->fetch_assoc()){
                              echo '<option value="'.(int)$row['id'].'">'.htmlspecialchars($row['maincat']).'</option>';
                          }
                      }else{
                          echo '<option value="0">no data available</option>';
                      }
                      ?>
                    </select>
                  </div>
                  <div class="nm-form-field">
                    <label class="control-label" for="newstype">News Type</label>
                    <select class="custom-select" id="newstype" name="newstype">
                      <option>Content</option>
                      <option>Video</option>
                    </select>
                  </div>
                  <div class="nm-form-field nm-form-field--full">
                    <label class="control-label">Select Categories</label>
                    <input type="search" class="form-control nm-cat-filter" placeholder="Filter categories…" autocomplete="off" aria-label="Filter categories">
                    <div class="nm-cat-grid">
                      <?php
                      $query = $con->query("SELECT * FROM `categories` WHERE `cat_url` IS NOT NULL ORDER BY id ASC");
                      $rowCount = $query->num_rows;
                      if($rowCount > 0){
                          while($row = $query->fetch_assoc()){
                              echo '<label class="nm-cat-chip"><input type="checkbox" name="cat_id[]" value="'.(int)$row['id'].'"> <span>'.htmlspecialchars($row['maincat']).'</span></label>';
                          }
                      }else{
                          echo '<p class="nm-form-hint">No categories available.</p>';
                      }
                      ?>
                    </div>
                  </div>
                </div>
                <div id="vid"></div>
                <div id="vid2"></div>
              </section>

              <section class="nm-form-section">
                <h2 class="nm-form-section__title">Media &amp; author</h2>
                <div class="nm-form-grid">
                  <div class="nm-form-field">
                    <label class="control-label">Image (850×565)</label>
                    <input class="form-control" type="file" name="image">
                  </div>
                  <div class="nm-form-field">
                    <label class="control-label" for="team_id">Select Author</label>
                    <select class="custom-select" id="team_id" name="team_id">
                      <?php if(isset($_POST['add'])){ echo '<option>'.htmlspecialchars($_POST['img_abt']).'</option>'; }else{
                      echo '<option value="0">select</option>'; } ?>
                      <?php
                      $query = $con->query("SELECT * FROM `team` ORDER BY t_id ASC");
                      $rowCount = $query->num_rows;
                      if($rowCount > 0){
                          while($row = $query->fetch_assoc()){
                              echo '<option value="'.(int)$row['t_id'].'">'.htmlspecialchars($row['name']).'</option>';
                          }
                      }else{
                          echo '<option value="0">no data available</option>';
                      }
                      ?>
                    </select>
                  </div>
                  <div class="nm-form-field">
                    <label class="control-label">About Image</label>
                    <input class="form-control" type="text" name="img_abt" value="<?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['img_abt']); } ?>" >
                  </div>
                  <div class="nm-form-field">
                    <label class="control-label">Image Source</label>
                    <input class="form-control" type="text" name="img_source" >
                  </div>
                </div>
              </section>

              <section class="nm-form-section">
                <h2 class="nm-form-section__title">Publish &amp; placement</h2>
                <div class="nm-form-grid">
                  <div class="nm-form-field">
                    <label class="control-label" for="datetimepicker">Publish Date Time</label>
                    <input class="form-control" id="datetimepicker" type="text" name="pub_date_time" value="<?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['pub_date_time']); } ?>" readonly>
                  </div>
                  <div class="nm-form-field">
                    <label class="control-label" for="slider">Show In Slider</label>
                    <select class="custom-select" id="slider" name="slider">
                      <option>No</option>
                      <option>Yes</option>
                    </select>
                  </div>
                  <div class="nm-form-field">
                    <label class="control-label">Slider Priority</label>
                    <input class="form-control" type="number" name="slider_priority" value="<?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['slider_priority']); } ?>" >
                  </div>
                  <div class="nm-form-field">
                    <label class="control-label" for="latest_news">Latest News</label>
                    <select class="custom-select" id="latest_news" name="latest_news">
                      <option>No</option>
                      <option>Yes</option>
                    </select>
                  </div>
                  <div class="nm-form-field">
                    <label class="control-label">Latest News Priority</label>
                    <input class="form-control" type="number" name="latest_priority" value="<?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['latest_priority']); } ?>" >
                  </div>
                </div>
              </section>

              <details class="nm-form-section nm-form-section--optional">
                <summary class="nm-form-section__title">SEO &amp; hashtags <span>(optional)</span></summary>
                <div class="nm-form-grid">
                  <div class="nm-form-field nm-form-field--full">
                    <label class="control-label">Meta Title</label>
                    <input class="form-control" type="text" name="metat" value="<?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['metat']); } ?>" >
                  </div>
                  <div class="nm-form-field nm-form-field--full">
                    <label class="control-label">Meta Description</label>
                    <textarea class="form-control" cols="20" rows="3" name="metad"><?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['metad']); } ?></textarea>
                  </div>
                  <div class="nm-form-field nm-form-field--full">
                    <label class="control-label">Hashtags for social media</label>
                    <textarea class="form-control" cols="20" rows="3" name="hashtags"><?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['hashtags']); } ?></textarea>
                  </div>
                </div>
              </details>

              <section class="nm-form-section">
                <h2 class="nm-form-section__title">Full article</h2>
                <div class="nm-form-field nm-form-field--full">
                  <label class="control-label" for="description">Description</label>
                  <textarea class="ckeditor form-control" id="description" name="description"><?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['description']); } ?></textarea>
                </div>
              </section>

              <div class="nm-form-actions">
                <button type="submit" name="add" class="btn btn-info">Add News</button>
                <a class="btn btn-outline-secondary" href="news.php">Cancel</a>
              </div>
        </form>
</div>
</div>			

    

</div>
<?php include"footer.php"; ?>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script type="text/javascript">
			//<![CDATA[

				// This call can be placed at any point after the
				// <textarea>, or inside a <head><script> in a
				// window.onload event handler.

				// Replace the <textarea id="editor"> with an CKEditor
				// instance, using default configurations.
				CKEDITOR.replace( 'description',
                {
                    filebrowserBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Connector=<?php echo $urlroot; ?>admin/ckeditor/filemanager/connectors/php/connector.php',
                    filebrowserImageBrowseUrl : 'ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector=<?php echo $urlroot; ?>admin/ckeditor/filemanager/connectors/php/connector.php',
                    filebrowserFlashBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector=<?php echo $urlroot; ?>admin/ckeditor/filemanager/connectors/php/connector.php',
					filebrowserUploadUrl  :'<?php echo $urlroot; ?>admin/ckeditor/filemanager/connectors/php/upload.php?Type=File',
					filebrowserImageUploadUrl : '<?php echo $urlroot; ?>admin/ckeditor/filemanager/connectors/php/upload.php?Type=Image',
					filebrowserFlashUploadUrl : '<?php echo $urlroot; ?>admin/ckeditor/filemanager/connectors/php/upload.php?Type=Flash'
				});

			//]]>
</script>
<script type="text/javascript">
        $(document).ready(function () {
            $('#sidebar').toggleClass('');
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="../include/js/bootstrap.min.js"></script>
  
  <!-- Font Awesome JS -->
    <script src="js/all.js"></script>
<script src="../include/js/jquery-ui.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.5/js/dataTables.responsive.min.js"></script>
    
<script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js" type="text/javascript"></script>
<link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet" type="text/css" />
<script>
        $('#datetimepicker').datetimepicker({
            uiLibrary: 'bootstrap4',
            modal: true,
            footer: true,
            format: 'yy-mm-dd HH:MM'
        });
    </script>
    <script>
    $("#newstype").on('change', function(){
			$.ajax({
						type: "POST",
						url: "ajaxVid.php",
						data:{newstype:$("#newstype").val()},
						beforeSend:function(){
						    $('#vid').html("<p>Loading....</p>");
                          },
						success: function(data){
							$('#vid').html(data);
						}
			});
	});
    
    // #videotype is injected later — must use delegated binding
    $(document).on('change', '#videotype', function(){
			$.ajax({
						type: "POST",
						url: "ajaxVid.php",
						data:{videotype:$("#videotype").val()},
						beforeSend:function(){
						    $('#vid2').html("<p>Loading....</p>");
                          },
						success: function(data){
							$('#vid2').html(data);
						}
			});
	});
</script>
<script>
(function () {
  var input = document.querySelector(".nm-news-form .nm-cat-filter");
  var chips = document.querySelectorAll(".nm-news-form .nm-cat-chip");
  if (!input || !chips.length) return;
  input.addEventListener("input", function () {
    var q = (input.value || "").toLowerCase().trim();
    chips.forEach(function (chip) {
      var text = (chip.textContent || "").toLowerCase();
      chip.classList.toggle("is-hidden", q !== "" && text.indexOf(q) === -1);
    });
  });
})();
</script>
</body>
</html>