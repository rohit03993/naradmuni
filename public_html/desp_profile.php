<?php
include "admin/config.php";
require_once("admin/dbcontroller.php");
require_once("pagination.class.php");
$db_handle = new DBController();
$perPage = new PerPage();

$folder=$_GET['actions'];

$queryCondition =" WHERE pc.category = '$folder' AND p.status='Published'";

$qry="select * from categories where id='$folder'";
$ex=mysqli_query($con,$qry);
$rs=mysqli_fetch_array($ex);


$orderby = " ORDER BY p.newsid desc";
$sql = "SELECT p.newsid, p.title, p.image, p.newsurl, p.short_description, p.newstype, p.date, p.time, p.metat, p.metad, p.metad FROM news p
INNER JOIN news_cat pc
ON p.newsid = pc.news_id" . $queryCondition;

//$queryCondition =" WHERE category = '$folder'";
//$orderby = " ORDER BY newsid desc";
//$sql = "SELECT * FROM news" . $queryCondition;
$paginationlink = "desp_profile.php?page=";	
$pagination_setting = $_GET["pagination_setting"];
				
$page = 1;
if(!empty($_GET["page"])) {
$page = $_GET["page"];
}

$start = ($page-1)*$perPage->perpage;
if($start < 0) $start = 0;

$query =  $sql . $orderby . " limit " . $start . "," . $perPage->perpage; 

$faq = $db_handle->runQuery($query);

if(empty($_GET["rowcount"])) {
$_GET["rowcount"] = $db_handle->numRows($sql);
}

if($pagination_setting == "prev-next") {
	$perpageresult = $perPage->getPrevNext($_GET["rowcount"], $paginationlink,$pagination_setting);	
} else {
	$perpageresult = $perPage->getAllPageLinks($_GET["rowcount"], $paginationlink,$pagination_setting);	
}

$output = '';
?>
<div class="col-lg-12">
<div class="row">                             
<?php
foreach($faq as $k=>$v) {
    
if($faq[$k]['newstype']=="Video"){
    $icon888 = '<i class="fa fa-play-circle-o" aria-hidden="true" style="position: absolute;top: 5%;left: 92%;font-size: 22px;"></i>';
}else{ $icon88 = ''; }

$ky1="SELECT * FROM `comments` WHERE  `newsid`='".$faq[$k]['newsid']."'";
if ($result1=mysqli_query($con,$ky1))
{
$comments=mysqli_num_rows($result1);
}

echo'<div class="col-lg-6 col-md-6 mb-30">
                        <div class="item post-content-box">
                           <div class="ts-post-thumb">
                              <a href="'.$urlroot.'news/'.$faq[$k]['newsurl'].'">
                                 <img class="img-fluid" src="'.$urlroot.'images/news/'.$faq[$k]['image'].'" alt="">
                              </a>
                           </div>
                           <div class="post-content">
                              <a class="post-cat orange-color no-bg" href="#">'.$rs['hindi_name'].'</a>
                              <h3 class="post-title md">
                                 <a href="'.$urlroot.'news/'.$faq[$k]['newsurl'].'">'.$faq[$k]['title'].'</a>
                              </h3>
                           </div>
                        </div>
                     </div>';
}
echo '</div>';
if(!empty($perpageresult)) {
$output .= '  <div class="col-lg-12">
			    
<div class="ts-pagination text-center mb-30">
              <ul class="pagination">   
                         <li>
                          <a href="#">'.$perpageresult.'</a>
                       </li>
                     </ul>
          </div>
		  </div>';
}
echo '</div>';
?>

<?php
print $output;
?>
    
    
							
							
<!--<ul class="pagination">
									<li>
										<a href="#">
											<i class="fa fa-angle-double-left"></i>
										</a>
									</li>
									<li>
										<a href="#">
											<i class="fa fa-angle-left"></i>
										</a>
									</li>
									<li class="active">
										<a href="#">1</a>
									</li>
									<li>
										<a href="#">2</a>
									</li>
									<li>
										<a href="#">3</a>
									</li>
									<li>
										<a href="#">4</a>
									</li>
									<li>
										<a href="#">
											<i class="fa fa-angle-right"></i>
										</a>
									</li>
									<li>
										<a href="#">
											<i class="fa fa-angle-double-right"></i>
										</a>
									</li>
								</ul>-->