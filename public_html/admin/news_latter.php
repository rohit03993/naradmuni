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

$usersession=$_SESSION['aemail'];

$res=mysqli_query($con,"SELECT * FROM admin WHERE aemail='$usersession'");

$userRow=mysqli_fetch_array($res,MYSQLI_ASSOC);

   if(isset($_GET['del']))
   {
    $id=$_GET['del'];
   
    $qry="delete from city where id='$id'";
    $ex=mysqli_query($con,$qry);
    if($ex>0)
    {
        
        echo ("<script language='javascript'>
           window.alert('Record Deleted Successfully')
           window.location.href='news_latter.php';
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
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Email Subscribers</li>
            </ol>
<div class="container-fluid page-content">
    <div class="row">
        <div class="col-sm-4"><button class="btn btn-warning reset" type="reset"><i class="fas fa-redo-alt"></i> Reset</button> <button onclick="myFunction()" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
        <!--<button type="button" class="btn btn-success" data-toggle="modal" data-target="#add212"><span class="fa fa-plus"></span> Add New </button>-->
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
        
           <div class="col-md-6 form-group group">
              <label class="control-label">Email:</label>
              <input class="form-control"  type="text" onChange="changePagination(this.value);" id="email" autocomplete="off">
            </div>
        
          <div class="col-md-3 form-group group">
              <label class="control-label">Date:</label>
              <input class="form-control"  type="text" onChange="changePagination(this.value);" id="datepicker1" autocomplete="off">
            </div>
                
            <div class="col-md-3 form-group group">
              <label class="control-label">To Date:</label>
              <input class="form-control"  type="text" onChange="changePagination(this.value);" id="datepicker2" autocomplete="off">
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
                "search[email]":$("#email").val(),
                "search[by_date]":$("#datepicker1").val(),
                "by_todate":$("#datepicker2").val()},
		beforeSend: function(){$("#overlay").show();},
		success: function(data){
		$("#pagination-result").html(data);
		setInterval(function() {$("#overlay").hide(); },500);
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
		getresult("desp_news_latter.php");
	}
}
    
    $(document).on('click', '.reset', function(){  
        $('#SearchForm')[0].reset();
        getresult("desp_news_latter.php");
      });
</script>

	<div id="pagination-result">
	<input type="hidden" name="rowcount" id="rowcount" />
	</div>
</div>
<script>
getresult("desp_news_latter.php");
</script>
</div>
<script>
$(document).ready(function(e){
    $("#SubmitForm").on('submit', function(e){
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'ajax_news_latter.php',
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            beforeSend: function(){$("#overlay").show();},
            success:function(data)  
                {
                    
                     $('.statusMsg').html(data);
                     $('#SubmitForm')[0].reset();
                     setInterval(function() {$("#overlay").hide(); },500);
                     getresult("desp_news_latter.php");
                }
        });
    });
    });
    
    </script>			
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