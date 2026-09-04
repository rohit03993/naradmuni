<?php
include "../admin/config.php";
require_once("../admin/dbcontroller.php");
require_once("pagination.class.php");
$db_handle = new DBController();
$perPage = new PerPage();

$now = date('y-m-d H:i');

$queryCondition =" WHERE `newstype`='Video' AND status='Published' AND pub_date_time <= '$now'";

$orderby = " GROUP BY newsid ORDER BY newsid DESC";
$sql = "SELECT * FROM `news`" . $queryCondition;

//$queryCondition =" WHERE category = '$folder'";
//$orderby = " ORDER BY newsid desc";
//$sql = "SELECT * FROM news" . $queryCondition;
$paginationlink = "desp_categories.php?page=";	
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
<section class="block-wrapper p-30 section-bg">
			<div class="container">
				<!-- title item end-->
				<div class="row ts-grid-item ts-overlay-item ts-grid-style-2">                            
               <?php foreach($faq as $k=>$v) { ?>
                    <div class="col-lg-4">
						<div class="ts-overlay-style">
							<div class="item">
								<div class="ts-post-thumb">

									<a href="#">
										<img class="img-fluid" src="<?php echo $urlroot; ?>images/news/<?php echo $faq[$k]["image"]; ?>" alt="<?php echo $faq[$k]['title']; ?>">
									</a>
									<a href="https://www.youtube.com/watch?v=<?php echo $faq[$k]['videoid']; ?>" class="ts-video-icon">
										<i class="fa fa-play-circle-o "></i>
									</a>
								</div>
								<div class="overlay-post-content">
									<div class="post-content">
										<h3 class="post-title">
											<a href="#"><?php echo $faq[$k]['title']; ?></a>
										</h3>
									</div>
								</div>
							</div>
							<!-- end item-->
						</div>
						<!-- ts overlay style end-->
					</div>
                <?php } ?>
     </div>
				<!-- row end-->
			</div>
			<!-- container end-->
		</section>               
<?php
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

print $output;
?>
<?php include"../js.php"; ?>