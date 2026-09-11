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
                            $menu=mysqli_real_escape_string($con,$_POST['menu']);
                            $metat = mysqli_real_escape_string($con,$_POST['metat']);
                            $metad = mysqli_real_escape_string($con,$_POST['metad']);
                           /*   Linkname starts  */
                            $replace = array(" ",",",".","'","&","-","_",":","(",")","+",";","#","!","*","{","}","[","]","?","/","\"","|","@","%","$");
                    		$linkStr_Replace = str_replace($replace,"-",trim($cat_url));
                    		$linkStr_Replace = str_replace("----","-",$linkStr_Replace);
                    		$linkStr_Replace = str_replace("---","-",$linkStr_Replace);
                    		$linkStr_Replace = str_replace("--","-",$linkStr_Replace);
                    		$linkname =  $linkStr_Replace;
                           
                            $parent = mysqli_real_escape_string($con,$_POST['parent']);
                                    
                        if($parent==0){
                                
                                $qry="insert into news (short,menu,maincat,cat_url,metat,metad) values('$short','$menu','$category','$linkname','$metat','$metad')";
                                
                            }else{
                                
                               $qry="insert into news (short,menu,maincat,parent,cat_url,metat,metad) values('$short','$menu','$category','$parent','$linkname','$metat','$metad')";
                            }
                                
                                    $ex=mysqli_query($con,$qry);

                                 if($ex>0)

                                    {echo 
                                    ("<script language='javascript'>
                                  window.alert('Added Successfully.') 
                                  window.location.href='rashifal.php';
                                  </script>");



                                    } else {
                                        echo ("<script language='javascript'>
                                  window.alert('Sorry, there was an error uploading your file.') 
                                  window.location.href='rashifal.php';
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
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Rashifal</li>
            </ol>
<div class="container-fluid page-content">
        <div class="row">
        <div class="col-sm-4"><button class="btn btn-warning reset" type="reset"><i class="fas fa-redo-alt"></i> Reset</button> <button onclick="myFunction()" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
        <a href="add_rashifal.php" class="btn btn-success"><span class="fa fa-plus"></span> Add New </a>
        </div> 
    <div class="EditstatusMsg col-sm-4"></div>
    <div class="col-sm-4"></div>
    </div> <hr>
    <div class="row">
        <div class="col-sm-12">
    <form id="SearchForm" style="display:none;">
        
    <select name="pagination-setting" onChange="changePagination(this.value);" class="custom-select" id="pagination-setting" hidden="true">
    <option value="all-links">Display All Page Link</option>
    <option value="prev-next">Display Prev Next Only</option>
    </select>
        
           <div class="col-md-12 form-group group">
              <label class="control-label">Title:</label>
              <input class="form-control"  type="text" onChange="changePagination(this.value);" id="title" autocomplete="off">
            </div>
    </form>
        </div>
    </div>
    <hr>
<script>
x.style.display === "none";
function myFunction() {
  var x = document.getElementById("SearchForm");
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}
</script>

<!-- tables -->
<script>
function getresult(url) {
	$.ajax({
		url: url,
		type: "GET",
		data:  {rowcount:$("#rowcount").val(),
                "pagination_setting":$("#pagination-setting").val(),
                "search[title]":$("#title").val(),
                "search[latest_news]":$("#latest_news").val(),
                "search[slider]":$("#slider").val(),
                "search[category]":$("#category").val()},
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
		getresult("desp_rashifal.php");
	}
}

$(document).on('click', '.delete', function(){  
           var id = $(this).attr("id");  
           if(confirm("Are you sure you want to remove this data?"))  
           {  
                var actions = "delete";  
                $.ajax({  
                     url:"ajax_rashifal.php",  
                     method:"POST",  
                     data:{id:id, actions:actions},
                     beforeSend: function(){$("#overlay").show();},
                     success:function(data)  
                     {   
                         alert(data); 
                         $("#overlay").hide();
                         getresult("desp_rashifal.php");
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
        getresult("desp_rashifal.php");
      });
</script>
    <div id="pagination-result">
	<input type="hidden" name="rowcount" id="rowcount" />
	</div>
</div>
<script>
getresult("desp_rashifal.php");
</script>
</div>			

    

</div>
<?php include"footer.php"; ?>
<script>
$(document).on('change', '.status', function(){
    var $select = $(this);
    var $tr = $select.closest('tr');
    var id = $(this).attr("id");
    var status = $select.val();
        
          $.ajax({
				 type: "POST",
				 url: "ajaxStatus.php",
				 data:{status:status,
                       newsid:id,},
                 beforeSend: function(){$('.EditstatusMsg').delay(200).fadeIn();},
				 success: function(data){
				 $('.EditstatusMsg').html(data);
                 $('.EditstatusMsg').delay(1000).fadeOut();
                 getresult("desp_rashifal.php");
				 }
			});
	   });
</script>
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