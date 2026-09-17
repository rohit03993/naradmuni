<?php

    include"config.php";
    if (!function_exists('nm_h') || !function_exists('nm_category_slug')) {
        require_once __DIR__ . '/admin_helpers.php';
    }

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

if (isset($_POST['add'])) {
	$hindi_name = trim((string) ($_POST['hindi_name'] ?? ''));
	$linkname = nm_category_slug($_POST['cat_url'] ?? '');
	$menu = (isset($_POST['menu']) && $_POST['menu'] === 'Yes') ? 'Yes' : 'No';
	$parent = isset($_POST['parent']) ? (string) $_POST['parent'] : '0';
	if (!ctype_digit($parent)) {
		$parent = '0';
	}

	$flash = function ($msg) {
		nm_js_notice($msg, 'categories.php');
	};

	if ($hindi_name === '' || $linkname === '') {
		$flash('Hindi name and Category URL are required.');
	}
	if (nm_category_url_taken($con, $linkname)) {
		$flash('That Category URL is already in use. Pick a different English slug so existing pages stay unchanged.');
	}

	$escH = mysqli_real_escape_string($con, $hindi_name);
	$escU = mysqli_real_escape_string($con, $linkname);
	$latter = mysqli_real_escape_string($con, nm_category_letter($linkname));
	$short = '0';
	$main_heading = 'No';
	$metat = $escH;
	$metad = '';
	$maincat = $escH;

	if ($parent === '0') {
		$qry = "insert into categories (short,main_heading,menu,maincat,cat_url,metat,metad,hindi_name,latter) values('$short','$main_heading','$menu','$maincat','$escU','$metat','$metad','$escH','$latter')";
	} else {
		$escP = mysqli_real_escape_string($con, $parent);
		$qry = "insert into categories (main_heading,short,menu,maincat,parent,cat_url,metat,metad,hindi_name,latter) values('$main_heading','$short','$menu','$maincat','$escP','$escU','$metat','$metad','$escH','$latter')";
	}

	$ex = mysqli_query($con, $qry);
	if ($ex) {
		$flash('Added Successfully.');
	}
	$flash('Sorry, the category could not be saved. Try again.');
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
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add New</h4>
        </div>
        <div class="modal-body">
            <div class="statusMsg"></div>
            <form id="SubmitForm" method="post" autocomplete="off">

            <div class="col-md-12 form-group group">
              <label class="control-label">Hindi Name:</label>
              <input type="text" class="form-control" name="hindi_name" required>
            </div>

            <div class="col-md-12 form-group group">
              <label class="control-label">Category URL:</label>
              <input type="text" class="form-control" name="cat_url" required placeholder="e.g. seoni">
              <small class="form-text text-muted">English slug only. Becomes /category/seoni. Existing news and category URLs are not changed.</small>
            </div>

            <div class="col-md-6 form-group group">
              <label class="control-label">Parent Category:</label>
               <select class="custom-select" id="maincat" name="parent">
                <option value="0">None</option>
            	<?php
                $query = nm_categories_result($con);
                if ($query instanceof mysqli_result && $query->num_rows > 0) {
                    while($row = $query->fetch_assoc()){
                        echo '<option value="'.(int)$row['id'].'">'.nm_h(nm_cat_label($row)).'</option>';
                    }
                }
                ?>
            </select>
            </div>

            <div class="col-md-6 form-group group">
            <label class="control-label">Show In Menu</label>
            <select class="custom-select" name="menu">
                <option value="No" selected>No</option>
            	<option value="Yes">Yes</option>
            </select>
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