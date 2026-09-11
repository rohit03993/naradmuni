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
                            $category = mysqli_real_escape_string($con,$_POST['category']);
                            $cat_url=mysqli_real_escape_string($con,$_POST['cat_url']);
                            $short=mysqli_real_escape_string($con,$_POST['short']);
                            $main_heading=mysqli_real_escape_string($con,$_POST['main_heading']);
                            $menu=mysqli_real_escape_string($con,$_POST['menu']);
                            $metat = mysqli_real_escape_string($con,$_POST['metat']);
                            $metad = mysqli_real_escape_string($con,$_POST['metad']);
                            $latter = mysqli_real_escape_string($con,$_POST['latter']);
                            $hindi_name = mysqli_real_escape_string($con,$_POST['hindi_name']);
                           /*   Linkname starts  */
                            $replace = array(" ",",",".","'","&","-","_",":","(",")","+",";","#","!","*","{","}","[","]","?","/","\"","|","@","%","$");
                    		$linkStr_Replace = str_replace($replace,"-",trim($cat_url));
                    		$linkStr_Replace = str_replace("----","-",$linkStr_Replace);
                    		$linkStr_Replace = str_replace("---","-",$linkStr_Replace);
                    		$linkStr_Replace = str_replace("--","-",$linkStr_Replace);
                    		$linkname =  $linkStr_Replace;
                           
                            $parent = mysqli_real_escape_string($con,$_POST['parent']);
                                    
                        if($parent==0){
                                
                                $qry="insert into categories (short,main_heading,menu,maincat,cat_url,metat,metad,hindi_name,latter) values('$short','$main_heading','$menu','$category','$linkname','$metat','$metad','$hindi_name','$latter')";
                                
                            }else{
                                
                               $qry="insert into categories (main_heading,short,menu,maincat,parent,cat_url,metat,metad,hindi_name,latter) values('$main_heading','$short','$menu','$category','$parent','$linkname','$metat','$metad','$hindi_name','$latter')";
                            }
                                
                                    $ex=mysqli_query($con,$qry);

                                 if($ex>0)

                                    {echo 
                                    ("<script language='javascript'>
                                  window.alert('Added Successfully.') 
                                  window.location.href='categories.php';
                                  </script>");



                                    } else {
                                        echo ("<script language='javascript'>
                                  window.alert('Sorry, there was an error uploading your file.') 
                                  window.location.href='categories.php';
                                  </script>");
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
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Categories</li>
            </ol>
<div class="container-fluid page-content">
    <div class="row">
        <div class="col-sm-4">
        
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#add212"><span class="fa fa-plus"></span> Add New </button>
        </div> 
    <div class="EditstatusMsg col-sm-4"></div>
    <div class="col-sm-4"></div>
    </div> 
    <div class="row">
        <div class="col-sm-12">
            <form id="SearchForm" style="display:none;">
        
    <select name="pagination-setting" onChange="changePagination(this.value);" class="custom-select" id="pagination-setting" hidden="true">
    <option value="all-links">Display All Page Link</option>
    <option value="prev-next">Display Prev Next Only</option>
    </select>
        
         
    </form>
        </div>
    </div>

<!-- tables -->
<script>
function getresult(url) {
	$.ajax({
		url: url,
		type: "GET",
		data:  {rowcount:$("#rowcount").val(),
                "pagination_setting":$("#pagination-setting").val(),
                "search[product_name]":$("#product_name").val(),
                "search[status]":$("#status").val()},
		beforeSend: function(){$("#overlay").show();},
		success: function(data){
		$("#pagination-result").html(data);
		$("#overlay").hide();
		},
		error: function(xhr) {
			$("#overlay").hide();
			if ($("#pagination-result").length) {
				$("#pagination-result").html('<div class="alert alert-danger">List failed to load'+(xhr&&xhr.status?' (HTTP '+xhr.status+')':'')+'. Refresh and try again.</div>');
			}
		} 
   });
}
function changePagination(option) {
	if(option!= "") {
		getresult("desp_categories.php");
	}
}

$(document).on('click', '.delete', function(){  
           var id = $(this).attr("id");  
           if(confirm("Are you sure you want to remove this data?"))  
           {  
                var actions = "delete";  
                $.ajax({  
                     url:"ajax_categories.php",  
                     method:"POST",  
                     data:{id:id, actions:actions},
                     beforeSend: function(){$("#overlay").show();},
                     success:function(data)  
                     {   
                         alert(data); 
                         $("#overlay").hide();
                         getresult("desp_categories.php");
                     }  
                })  
           }  
           else  
           {  
                return false;  
           }  
      });
    
    $(document).on('click', '.reset', function(){  
        $('#SearchForm')[0].reset();
        getresult("desp_categories.php");
      });
</script>
<div id="pagination-result">
	<input type="hidden" name="rowcount" id="rowcount" />
	</div>
</div>
<script>
getresult("desp_categories.php");
</script>
</div>			

 <!-- Add Modal -->
  <div class="modal fade" id="add212" role="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add New</h4>
        </div>
        <div class="modal-body">
            <div class="statusMsg"></div>
            <form id="SubmitForm" method="post" enctype="multipart/form-data"> 
            
                <div class="col-md-4 form-group group">
              <label class="control-label">Parent Category:</label>
        
               <select  class="custom-select" id="maincat" name="parent">
                <option value="0">select</option>
            	<?php
                
                $query = nm_categories_result($con);
                if ($query instanceof mysqli_result && $query->num_rows > 0) {
                    while($row = $query->fetch_assoc()){
                        echo '<option value="'.(int)$row['id'].'">'.nm_h(nm_cat_label($row)).'</option>';
                    }
                }else{
                    echo '<option value="0">no data available</option>';
                }
                
                ?>
            </select>
            </div>
                
            <div class="col-md-4 form-group group">
              <label class="control-label">Sort Order:</label>
              <input class="form-control" type="text" name="short">
            </div>
                
            <div class="col-md-4 form-group group group">
            <label class="control-label">Show In Menu</label>
            <select class="custom-select" id="breaking" name="menu">
                <option>No</option>
            	<option>Yes</option>
            </select>
            </div>
			
			<div class="col-md-4 form-group group group">
            <label class="control-label">Show In Child</label>
            <select class="custom-select" id="main_heading" name="main_heading">
                <option>No</option>
            	<option>Yes</option>
            </select>
            </div>
                
            <div class="col-md-6 form-group group">
              <label class="control-label">English Name:</label>
              <input  type="text" class="form-control" name="category" >
            </div>
                
            <div class="col-md-6 form-group group">
              <label class="control-label">Hindi Name:</label>
              <input  type="text" class="form-control" name="hindi_name">
            </div>
                
            <div class="col-md-3 form-group group">
              <label class="control-label">Starting Alphabate:</label>
              <input  type="text" class="form-control" name="latter">
            </div>
                
            <div class="col-md-9 form-group group">
              <label class="control-label">Category URL:</label>
              <input  type="text" class="form-control" name="cat_url">
            </div>
                
            
                 
            <div class="col-md-12 form-group group">
              <label class="control-label">Meta Title:</label>
              <input class="form-control" type="text" name="metat">
            </div>
                
            <div class="col-md-12 form-group group">
              <label class="control-label">Meta Description:</label>
             <textarea class="form-control" cols="20" rows="5" name="metad"></textarea>
            </div>
            
                
            <div class="col-md-12 form-group group">
                <button type="submit" name="add" class="btn btn-info">Add</button>
            </div>
           
        </form>
          </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
 <!-- edit Modal -->
    

</div>
<?php include"footer.php"; ?>
<script type="text/javascript">
        $(document).ready(function () {
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
    
</body>
</html>