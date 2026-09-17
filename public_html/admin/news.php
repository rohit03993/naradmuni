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
if (!function_exists('nm_cms_identity')) {
    require_once __DIR__ . '/admin_helpers.php';
}
$nmMe = nm_cms_identity($con, $userRow);
$nmIsAdmin = !empty($nmMe['is_admin']);
$nmAuthorFilter = isset($_GET['author']) ? trim((string) $_GET['author']) : '';
$nmStatusFilter = isset($_GET['status']) ? trim((string) $_GET['status']) : '';
if (!in_array($nmStatusFilter, array('Published', 'Scheduled', 'Unpublished'), true)) {
    $nmStatusFilter = '';
}

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

                                    {
                                        if (!function_exists('nm_js_notice')) {
                                            require_once __DIR__ . '/admin_helpers.php';
                                        }
                                        nm_js_notice('Added Successfully.', 'news.php');
                                    } else {
                                        if (!function_exists('nm_js_notice')) {
                                            require_once __DIR__ . '/admin_helpers.php';
                                        }
                                        nm_js_notice('Sorry, there was an error uploading your file.', 'news.php', 'error');
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
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> News</li>
            </ol>
<div class="container-fluid page-content">
        <?php
        $tabAll = $nmAuthorFilter === '';
        $tabMe = $nmAuthorFilter === 'me';
        $tabOthers = $nmAuthorFilter === 'others';
        $tabId = ($nmAuthorFilter !== '' && ctype_digit($nmAuthorFilter));
        ?>
        <?php if ($nmStatusFilter !== '') { ?>
        <p class="text-muted" style="margin:0 0 10px;">Showing <strong><?php echo htmlspecialchars($nmStatusFilter); ?></strong> only. <a href="news.php<?php echo $nmAuthorFilter !== '' ? ('?author=' . rawurlencode($nmAuthorFilter)) : ''; ?>">Clear status</a></p>
        <?php } ?>
        <?php if ($nmIsAdmin) { ?>
        <div class="nm-news-tabs">
          <a class="<?php echo $tabAll ? 'is-on' : ''; ?>" href="news.php">All news</a>
          <?php if ((int) $nmMe['team_id'] > 0) { ?>
          <a class="<?php echo $tabMe ? 'is-on' : ''; ?>" href="news.php?author=me">By me</a>
          <a class="<?php echo $tabOthers ? 'is-on' : ''; ?>" href="news.php?author=others">By others</a>
          <?php } ?>
          <?php if ($tabId) {
              $tn = '';
              $tid = (int) $nmAuthorFilter;
              $tq = @mysqli_query($con, "SELECT `name` FROM `team` WHERE `t_id`='$tid' LIMIT 1");
              if ($tq instanceof mysqli_result) {
                  $tr = mysqli_fetch_assoc($tq);
                  if ($tr) {
                      $tn = $tr['name'];
                  }
              }
              echo '<a class="is-on" href="news.php?author=' . $tid . '">' . htmlspecialchars($tn !== '' ? $tn : ('Author #' . $tid)) . '</a>';
          } ?>
        </div>
        <?php } else { ?>
        <p class="text-muted" style="margin:0 0 12px;">Showing only your stories. Edit or schedule from the list below.</p>
        <?php } ?>
        <div class="row">
        <div class="col-sm-4"><button class="btn btn-warning reset" type="reset"><i class="fas fa-redo-alt"></i> Reset</button> <button onclick="myFunction()" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
        <a href="add_news.php" class="btn btn-success"><span class="fa fa-plus"></span> Add New </a>
        <?php if ($nmIsAdmin) { ?>
        <a href="cleanup_news.php" class="btn btn-outline-danger"><i class="fas fa-broom"></i> Cleanup old</a>
        <?php } ?>
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
        
           <div class="col-md-8 form-group group">
              <label class="control-label">News title:</label>
              <input class="form-control"  type="text" onChange="changePagination(this.value);" id="title" autocomplete="off">
            </div>
                
            <div class="col-md-4 form-group group">
              <label class="control-label">Category:</label>
              <select class="custom-select" onChange="changePagination(this.value);" id="category">
                <option value="0">select</option>
            	<?php
                
                $query = $con->query("SELECT id, hindi_name FROM `categories` ORDER BY id ASC");
                if (!$query) {
                    $query = $con->query("SELECT id, maincat AS hindi_name FROM `categories` ORDER BY id ASC");
                }
    
                //Count total number of rows
                $rowCount = $query ? $query->num_rows : 0;

                //City option list
                if($rowCount > 0){

                    while($row = $query->fetch_assoc()){ 
                        $label = !empty($row['hindi_name']) ? $row['hindi_name'] : ('Cat #'.$row['id']);
                        echo '<option value="'.htmlspecialchars((string)$row['id']).'">'.htmlspecialchars((string)$label).'</option>';
                    }
                }else{
                    echo '<option value="0">no data available</option>';
                }
                
                ?>
              </select>
            </div>
            
            <div class="col-md-3 form-group group group">
            <label class="control-label">Slider</label>
            <select class="custom-select" id="slider" onChange="changePagination(this.value);">
                <option value="0">select</option>
                <option>No</option>
            	<option>Yes</option>
            </select>
            </div>
            
            <div class="col-md-3 form-group group group">
            <label class="control-label">Latest News</label>
            <select class="custom-select" id="latest_news" onChange="changePagination(this.value);">
                <option value="0">select</option>
                <option>No</option>
            	<option>Yes</option>
            </select>
            </div>
    </form>
        </div>
    </div>
    <hr>
<script>
function myFunction() {
  var x = document.getElementById("SearchForm");
  if (!x) return;
  if (x.style.display === "none" || x.style.display === "") {
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
                "search[category]":$("#category").val(),
                "author": <?php echo json_encode($nmAuthorFilter); ?>,
                "status": <?php echo json_encode($nmStatusFilter); ?>},
		beforeSend: function(){$("#overlay").show();},
		success: function(data){
		$("#pagination-result").html(data);
		$("#overlay").hide();
		},
		error: function(xhr) {
			$("#overlay").hide();
			$("#pagination-result").html(
				'<div class="alert alert-danger">News list failed to load' +
				(xhr && xhr.status ? ' (HTTP ' + xhr.status + ')' : '') +
				'. Check admin DB config / PHP error log.</div>'
			);
		}
   });
}
function changePagination(option) {
	if(option!= "") {
		getresult("desp_news.php");
	}
}

$(document).on('click', '.nm-news-delete', function (e) {
  e.preventDefault();
  e.stopPropagation();
  var $btn = $(this);
  var id = $btn.attr("data-newsid") || $btn.data("newsid");
  if (!id) {
    nmAlert("Delete failed: missing article id.");
    return false;
  }
  nmConfirm("Delete this news and its photo from the server? This cannot be undone.", {
    title: "Delete news",
    okText: "Delete"
  }).then(function (ok) {
    if (!ok) {
      return;
    }
    $btn.prop("disabled", true).addClass("disabled");
    $.ajax({
      url: "ajax_news.php",
      method: "POST",
      dataType: "json",
      data: { id: id, actions: "delete" },
      success: function (res) {
        if (res && res.ok) {
          $btn.closest("tr").fadeOut(200, function () { $(this).remove(); });
          nmToast(res.message || "Deleted.", "success");
        } else {
          $btn.prop("disabled", false).removeClass("disabled");
          nmAlert((res && res.message) ? res.message : "Delete failed.");
        }
      },
      error: function (xhr) {
        $btn.prop("disabled", false).removeClass("disabled");
        nmAlert("Delete failed" + (xhr && xhr.status ? " (HTTP " + xhr.status + ")" : "") + ".");
      }
    });
  });
  return false;
});
    
    $(document).on('click', '.reset', function(){  
        $('#SearchForm')[0].reset();
        getresult("desp_news.php");
      });
</script>
    <div id="pagination-result">
	<input type="hidden" name="rowcount" id="rowcount" />
	</div>
</div>
<script>
getresult("desp_news.php");
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
                 getresult("desp_news.php");
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