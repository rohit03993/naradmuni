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
                            $title = mysqli_real_escape_string($con,$_POST['title']);
                            $latest_news=mysqli_real_escape_string($con,$_POST['latest_news']);
                            $description = mysqli_real_escape_string($con,$_POST['description']);
                            $newsurl = mysqli_real_escape_string($con,$_POST['newsurl']);
                            $metat = mysqli_real_escape_string($con,$_POST['metat']);
                            $metad = mysqli_real_escape_string($con,$_POST['metad']);
                            //$metak = mysqli_real_escape_string($con,$_POST['metak']);
                            $category = mysqli_real_escape_string($con,$_POST['category']);
                            $top_side_bar = mysqli_real_escape_string($con,$_POST['top_side_bar']);
                            $slider = mysqli_real_escape_string($con,$_POST['slider']);
                            $slider_priority = mysqli_real_escape_string($con,$_POST['slider_priority']);
                            $latest_priority = mysqli_real_escape_string($con,$_POST['latest_priority']);
                            $show_home = mysqli_real_escape_string($con,$_POST['show_home']);
                            $short_description = mysqli_real_escape_string($con,$_POST['short_description']);
                            $date=date("d-m-Y");
                            $time=date('H:i');
                            $newstype = mysqli_real_escape_string($con,$_POST['newstype']);
                            $img_source = mysqli_real_escape_string($con,$_POST['img_source']);
                            $img_abt = mysqli_real_escape_string($con,$_POST['img_abt']);
                            $v_link = mysqli_real_escape_string($con,$_POST['videolink']);
                            $status = 'Unpublished';
if(!empty($v_link)){
    $video_id = explode("?v=", $v_link); // For videos like http://www.youtube.com/watch?v=...
if (empty($video_id[1]))
    $video_id = explode("/v/", $v_link); // For videos like http://www.youtube.com/watch/v/..
    
if (empty($video_id[1]))
    $video_id = explode("youtu.be/", $v_link); // https://youtu.be/zADj0k0waFY..

$video_id = explode("&", $video_id[1]); // Deleting any other params

$video_id = $video_id[0];
}else{
    $video_id='';
}
$name = $_FILES['video_file']['name'];
if(!empty($name)){
    $target_dir = "../videos/";
    $target_file = $target_dir . $_FILES["video_file"]["name"];
    move_uploaded_file($_FILES['video_file']['tmp_name'],$target_file);
}

    
                            $tmp_file = $_FILES['image']['tmp_name'];
                            $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                            $rand = md5(uniqid().rand());
                            $post_image = $rand.".".$ext;
                             // Get Image Dimension
                            $fileinfo = @getimagesize($_FILES["image"]["tmp_name"]);
                            $width = $fileinfo[0];
                            $height = $fileinfo[1];
                            
                            move_uploaded_file($tmp_file,"../images/news/".$post_image); 

                             /*   Linkname starts  */
                            $replace = array(" ",",",".","'","&","-","_",":","(",")","+",";","#","!","*","{","}","[","]","?","/","\"","|","@","%","$");
                    		$linkStr_Replace = str_replace($replace,"-",trim($_POST['newsurl']));
                    		$linkStr_Replace = str_replace("----","-",$linkStr_Replace);
                    		$linkStr_Replace = str_replace("---","-",$linkStr_Replace);
                    		$linkStr_Replace = str_replace("--","-",$linkStr_Replace);
                    		$linkname =  $linkStr_Replace;
                        
                            /*   foldername starts  */
                            $replace = array(" ",",",".","'","&","-","_",":","(",")","+",";","#","!","*","{","}","[","]","?","/","\"","|","@","%","$");
                    		$folderStr_Replace = str_replace($replace,"-",trim($_POST['category']));
                    		$folderStr_Replace = str_replace("----","-",$folderStr_Replace);
                    		$folderStr_Replace = str_replace("---","-",$folderStr_Replace);
                    		$folderStr_Replace = str_replace("--","-",$folderStr_Replace);
                    		$maincat =  $folderStr_Replace;
                        
                            $foldername=$maincat."/";
    
                            $link=$foldername.$linkname."/";
                         
                            //mkdir("students/".$id);
                            $sql_u = "SELECT * FROM `news` WHERE `newsurl`='$linkname'";
    
                            $res_u = mysqli_query($con, $sql_u);
    
                            if (mysqli_num_rows($res_u) > 0) {array_push($errors, "Sorry... News URL already Exixts");}
                        
                            if (empty($title)) { array_push($errors, "Kindly fill news title"); }
                            
                             if (empty($short_description)) { array_push($errors, "Kindly fill Short Description"); }
                           
                            if (empty($description)) { array_push($errors, "Kindly fill news description"); }
                         
                             if (empty($newsurl)) { array_push($errors, "Kindly fill news url"); }
                           
                            if (empty($metat)) { array_push($errors, "Kindly fill meta title"); } 
                        
                            if (empty($category)) { array_push($errors, "Kindly fill news category"); } 
                        
                            if (empty($_FILES['image']['tmp_name'])) { array_push($errors, "Kindly add image"); }
                            
    
                        if (count($errors) == 0) {
                            
                            
                           $qry="insert into `news` (`newsurl`, `folder`, `seolink`, `metat`, `metad`, `title`, `description`, `short_description`, `image`, `img_abt`, `img_source`, `newstype`, `latest_news`, `category`, `top_side_bar`, `slider`, `date`, `time` , `videoid`, `video_file`, `show_home`, `status`, `slider_priority`, `latest_priority`) values('$linkname','$foldername','$link','$metat','$metad','$title','$description','$short_description','$post_image','$img_abt','$img_source','$newstype','$latest_news','$category','$top_side_bar','$slider','$date','$time','$video_id','$name','$show_home','$status','$slider_priority','$latest_priority')";
                                
                             $ex=mysqli_query($con,$qry);
                             $lastInsertId = mysqli_insert_id($con);
                              if($ex>0) {
                                  
                            $number1 = count($_POST["cat_id"]);  
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
                                  //move_uploaded_file($_FILES["image"]["tmp_name"], $targetpath1);
                                   echo("<script language='javascript'>
                                  window.alert('added Successfully') 
                                  window.location.href='news.php';
                                  </script>");
                                  
                                  //array_push($sucs, "added Successfuly."); 
                              }
                              else{ array_push($errors, "Sorry, there was an error."); }
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
<script>

    $( function() {
    $( "#datepicker" ).datepicker({ yearRange: "-100:+0",changeMonth: true,
    changeYear: true,
    dateFormat: 'yy-mm-dd' });
    $( "#datepicker1" ).datepicker({ dateFormat: 'yy-mm-dd' });
    $( "#datepicker2" ).datepicker({ dateFormat: 'yy-mm-dd' });
    $( "#datepicker3" ).datepicker({ dateFormat: 'yy-mm-dd' });
    });
</script>
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
            <form id="SubmitForm" method="post" enctype="multipart/form-data">
                
                <div class="col-md-12 form-group group group">
                <p><b>Select Categories: </b></p>
               <?php
                
                $query = $con->query("SELECT * FROM `categories` WHERE `cat_url` IS NOT NULL ORDER BY id ASC");
    
                //Count total number of rows
                $rowCount = $query->num_rows;

                //City option list
                if($rowCount > 0){

                    while($row = $query->fetch_assoc()){ 
                        echo '<label style="margin-right: 10px;" class="checkbox-inline"><input type="checkbox" name="cat_id[]" value='.$row['id'].' '.$chk.'> '.$row['maincat'].' </label>';
                    }
                }else{
                    echo '<option value="0">no data available</option>';
                }
                
                ?>
            </div>
                
            <div class="col-md-3 form-group group group">
            <label class="control-label">News Type</label>
            <select class="custom-select" id="newstype" name="newstype">
                <option>Content</option>
            	<option>Video</option>
            </select>
            </div>
            
            <div class="col-md-3 form-group group">
              <label class="control-label">Home Category:</label>
        
               <select  class="custom-select" id="category" name="category">
                <option value="0">select</option>
            	<?php
                
                $query = $con->query("SELECT * FROM `categories` ORDER BY id ASC");
    
                //Count total number of rows
                $rowCount = $query->num_rows;

                //City option list
                if($rowCount > 0){

                    while($row = $query->fetch_assoc()){ 
                        echo '<option value='.$row['id'].'>'.$row['maincat'].'</option>';
                    }
                }else{
                    echo '<option value="0">no data available</option>';
                }
                
                ?>
            </select>
            </div>
                
            <div class="col-md-3 form-group group group">
            <label class="control-label">Show In Slider</label>
            <select class="custom-select" id="slider" name="slider">
                <option>No</option>
            	<option>Yes</option>
            </select>
            </div>
            
            <div class="col-md-3 form-group group">
              <label class="control-label">Slider Priority:</label>
              <input class="form-control" type="number" name="slider_priority" value="<?php echo $_POST['slider_priority']; ?>">
            </div>
                
                
            <div class="col-md-4 form-group group group">
            <label class="control-label">Latest News</label>
            <select class="custom-select" id="latest_news" name="latest_news">
                <option>No</option>
            	<option>Yes</option>
            </select>
            </div>
            
            <div class="col-md-4 form-group group">
              <label class="control-label">Letest News Priority:</label>
              <input class="form-control" type="number" name="latest_priority" value="<?php echo $_POST['latest_priority']; ?>">
            </div>
                
            <div class="col-md-4 form-group group">
              <label class="control-label">Image (850X565 Pixels):</label>
                <input class="form-control" type="file" name="image">
            </div>
                
            <div id="vid"></div>
            <div id="vid2"></div>
            
            <div class="col-md-6 form-group group">
              <label class="control-label">About Image:</label>
              <input class="form-control" type="text" name="img_abt" value="<?php echo $_POST['img_abt']; ?>">
            </div>
               
            <div class="col-md-6 form-group group">
              <label class="control-label">Image Source:</label>
              <input class="form-control" type="text" name="img_source" value="<?php echo $_POST['img_source']; ?>">
            </div>
                
            <div class="col-md-6 form-group group">
              <label class="control-label">News URL:</label>
              <input class="form-control" type="text" name="newsurl" value="<?php echo $_POST['newsurl']; ?>">
            </div>
               
            <div class="col-md-6 form-group group">
              <label class="control-label">Title:</label>
              <input class="form-control" type="text" name="title" value="<?php echo $_POST['title']; ?>">
            </div>
                
            
                 
            <div class="col-md-12 form-group group">
              <label class="control-label">Meta Title:</label>
              <input class="form-control" type="text" name="metat" value="<?php echo $_POST['metat']; ?>">
            </div>
                
            <div class="col-md-12 form-group group">
              <label class="control-label">Meta Description:</label>
             <textarea class="form-control" cols="20" rows="5" name="metad"><?php echo $_POST['metad']; ?></textarea>
            </div>
            
            <div class="col-md-12 form-group group">
              <label class="control-label">Short Description:</label>
             <textarea class="form-control" cols="20" rows="5" name="short_description"><?php echo $_POST['short_description']; ?></textarea>
            </div>
            
            <div class="col-md-12 form-group group">
              <label class="control-label">Description</label>
              <textarea class="ckeditor form-control" id="description"  name="description"><?php echo $_POST['description']; ?></textarea>
            </div>
                
            <div class="col-md-12 form-group group">
                <button type="submit" name="add" class="btn btn-info">Add</button>
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
    
    $("#videotype").on('change', function(){
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
</body>
</html>