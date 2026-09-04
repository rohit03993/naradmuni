<?php
include "../admin/config.php";
require_once("../admin/dbcontroller.php");
require_once("pagination.class.php");
$db_handle = new DBController();
$perPage = new PerPage();

$folder=$_GET['actions'];

$queryCondition =" WHERE pc.category = '$folder' AND p.status='Published' AND p.pub_date_time <= '$now'";

$qry="select * from categories where id='$folder'";
$ex=mysqli_query($con,$qry);
$rs=mysqli_fetch_array($ex);


$orderby = " GROUP BY p.newsid ORDER BY p.newsid desc";
$sql = "SELECT p.newsid, p.title, p.image, p.newsurl, p.short_description, p.newstype, p.date, p.time, p.metat, p.metad, p.metad FROM news p
INNER JOIN news_cat pc
ON p.newsid = pc.news_id" . $queryCondition;

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
<div class="block category-listing">
<!--
<h3 class="utf_block_title"><span><?php echo $rs['maincat']; ?></span></h3>
<ul class="subCategory unstyled">
    <?php
    /*
    $qry77 = mysqli_query($con,"SELECT `maincat`,`cat_url` FROM `categories` WHERE `menu`='No' AND `parent`='$folder' ORDER BY `short`");
     
    if(mysqli_num_rows($qry77)>0){
        while ($row55 = mysqli_fetch_array($qry77)){  
     echo '<li><a href="'.$urlroot.'category/'.$row55['cat_url'].'">'.$row55['maincat'].'</a></li>'; }
    }else{
        
        $qry71 = mysqli_query($con,"SELECT `maincat`,`cat_url` FROM `categories` WHERE `menu`='No' AND `parent`='".$rs['parent']."' AND `id`!='".$rs['id']."' ORDER BY `short`");
        while ($row51 = mysqli_fetch_array($qry71)){ 
     echo '<li><a href="'.$urlroot.'category/'.$row51['cat_url'].'">'.$row51['maincat'].'</a></li>';
    } 
        
    }*/
    
    ?>-->
</ul>    
<div class="post-list">                             
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
echo '<!-- ts title end-->
							<div class="row mb-10">
								<div class="col-md-4">
									<div class="ts-post-thumb">
										<a href="'.$urlroot.'news/'.$faq[$k]['newsurl'].'">
											<img class="img-fluid" src="'.$urlroot.'images/news/'.$faq[$k]['image'].'" alt="">
										</a>
									</div>
								</div>
								<!-- col lg end-->
								<div class="col-md-8">
									<div class="post-content">
										<h3 class="post-title md">
											<a href="'.$urlroot.'news/'.$faq[$k]['newsurl'].'">'.$faq[$k]['title'].'</a>
										</h3>
										<ul class="post-meta-info">
											<li>
												<a href="#">Devid Ronald</a>
											</li>
											<li>
												<i class="fa fa-clock-o"></i>
												'.$faq[$k]['date'].'
											</li>
										</ul>
										<p>'.$faq[$k]['short_description'].'</p>
									</div>
								</div>
							</div>
							<!-- row end-->';
}
echo '</div>';
if(!empty($perpageresult)) {
$output .= '<div class="ts-pagination text-center mt-15 md-mb-30">
            <ul class="pagination">
              ' . $perpageresult . '
            </ul>
          </div>';
}
echo '
          </div>';
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