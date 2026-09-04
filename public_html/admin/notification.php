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

 

if(isset($_POST["send"]))  
{  
                            $title = mysqli_real_escape_string($con,$_POST['title']);
                            $description = mysqli_real_escape_string($con,$_POST['description']);
                            $image = mysqli_real_escape_string($con,$_POST['image']);
                            $link = mysqli_real_escape_string($con,$_POST['link']);
                            $date=date("Y-m-d");
                            $time=date('h:i:s:A');
                            if (empty($title)) { array_push($errors, "Kindly fill title"); }
                            if (empty($description)) { array_push($errors, "Kindly fill description"); }
                        
                            
    if (count($errors) == 0) {
                            
    $qry="INSERT INTO `notification`(`title`, `description`, `image`, `link`, `date`, `time`) VALUES ('$title','$description','$image','$link','$date','$time')";
    $ex=mysqli_query($con,$qry);
                            
    if ($ex>0) { 
    
        	// Get Records from Table
	$sql = "SELECT * FROM `tokens`";
    // Check Results
	$result = mysqli_query($con, $sql);
	
	if (mysqli_num_rows($result) > 0) {
		
		// output data of each row in Array
		$array = [];
		while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row["token"];
		}
		
		// Chunk of Array with 999 Records, I have not taken risk for 1000 users :D
		$final_array = array_chunk($array, 999);
		
		// Loop for Every Sub Array and Send to FCM Server
		foreach($final_array as $array_of_chunk) {

    		#API access key from Google API's Console
			# Kindly Replace it with Your API key and Token
    		define( 'API_ACCESS_KEY', 'AAAAnf23Pf0:APA91bHnaKtqFnWUnu2K0CITgNWFXxWUuT3OVswnRgSJ17XwcGu9TVNClf74cC-APFD2JDw1KjVx7Fbxrqcb62cNv1TLg9nRQqnYkOCz_his7BJs0FSvVUPaRk0ql-xF4HI_o1kRxdZr');
    		$registrationIds = $array_of_chunk;
    
    		#prep the bundle
    		 $msg = array
    			  (
    			    'title' => $title,
                    'body' => $description,
                    'icon' => 'https://www.thenaradmuni.com/images/icon/AppIcon4x.png',
                    'image' => $image,
                    'click_action'=> $link,
    			  );


       
    		$fields = array(
    					'registration_ids' => $registrationIds,
    					'data' => $msg,
                        'notification' => $msg,
    			      );
    			
    		$headers = array
    				(
    					'Authorization: key='.API_ACCESS_KEY,
    					'Content-Type: application/json'
    				);
    				
		    #Send Reponse To FireBase Server	
			$ch = curl_init();
			curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
			$result = curl_exec($ch );
		}
	
	}
        array_push($sucs, "Notification Sent."); }
    else{ array_push($errors, "Sorry, there was an error"); }
    
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
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Send Notification</li>
            </ol>
<div class="container-fluid page-content">
    <div class="row">
        <div class="col-sm-4"><button class="btn btn-warning reset" type="reset"><i class="fas fa-redo-alt"></i> Reset</button> <button onclick="myFunction()" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#add212"><span class="fa fa-plus"></span> Add New </button>
        </div> 
    <div class="EditstatusMsg col-sm-4"></div>
    <div class="col-sm-4"></div>
    </div> <hr>
    <div class="row">
        <div class="col-sm-12">
<?php
include('errors.php');
include('sucsess.php');
?>
            <form id="SearchForm" style="display:none;">
        
    <select name="pagination-setting" onChange="changePagination(this.value);" class="custom-select" id="pagination-setting" hidden="true">
    <option value="all-links">Display All Page Link</option>
    <option value="prev-next">Display Prev Next Only</option>
    </select>
        
           <div class="col-md-6 form-group group">
              <label class="control-label">Title:</label>
              <input class="form-control"  type="text" onChange="changePagination(this.value);" id="title" placeholder="Ex: Rohan Sharma" autocomplete="off">
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
                "search[title]":$("#title").val(),
                "search[by_date]":$("#datepicker1").val(),
                "by_todate":$("#datepicker2").val()},
		beforeSend: function(){$("#overlay").show();},
		success: function(data){
		$("#pagination-result").html(data);
		setInterval(function() {$("#overlay").hide(); },500);
		},
		error: function() 
		{} 	        
   });
}
function changePagination(option) {
	if(option!= "") {
		getresult("desp_notification.php");
	}
}

$(document).on('click', '.delete', function(){  
           var id = $(this).attr("id");  
           if(confirm("Are you sure you want to remove this data?"))  
           {  
                var actions = "delete";  
                $.ajax({  
                     url:"ajax_notification.php",  
                     method:"POST",  
                     data:{id:id, actions:actions},
                     beforeSend: function(){$("#overlay").show();},
                     success:function(data)  
                     {   
                         alert(data); 
                         setInterval(function() {$("#overlay").hide(); },500);
                         getresult("desp_notification.php");
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
        getresult("desp_notification.php");
      });
</script>

	<div id="pagination-result">
	<input type="hidden" name="rowcount" id="rowcount" />
	</div>
</div>
<script>
getresult("desp_notification.php");
</script>
</div>
 <!-- Add Modal -->
  <div class="modal fade" id="add212" role="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Send Notification</h4>
        </div>
        <div class="modal-body">
            <div class="statusMsg"></div>
            <form  method="post" enctype="multipart/form-data"> 
            
                
            <div class="col-md-12 form-group group">
              <label class="control-label">Title:</label>
              <input class="form-control"  type="text" name="title" autocomplete="off">
            </div>
            
            <div class="col-md-12 form-group group">
              <label class="control-label">Image URL:</label>
              <input class="form-control"  type="text" name="image" autocomplete="off">
            </div>
            
            <div class="col-md-12 form-group group">
              <label class="control-label">Notification Link:</label>
              <input class="form-control"  type="text" name="link" autocomplete="off">
            </div>
                
            <div class="col-md-12 form-group group">
              <label class="control-label">Description:</label>
              <textarea class="form-control" name="description" autocomplete="off"> </textarea>
            </div>
                
            <div class="col-md-12 form-group group">
                <input type="text" name="actions" id="actions" class="hidden" value="Add" hidden>
                <button type="submit" name="send" class="btn btn-info">Submit</button>
            </div>
           
        </form>
          </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

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