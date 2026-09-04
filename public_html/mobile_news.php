<?php
include "admin/config.php";
require_once("admin/dbcontroller.php");
require_once("pagination.class_news.php");
$db_handle = new DBController();
$perPage = new PerPage();

$folder=$_GET['actions'];
$newsid = $_GET['newsid'];
$now = date('y-m-d H:i');
$queryCondition =" WHERE p.newstype != 'Video' AND  p.newsid != '$newsid' AND p.status='Published'";

$orderby = " GROUP BY p.newsid ORDER BY p.newsid DESC";
$sql = "SELECT p.newsid, p.title, p.image, p.newsurl, p.short_description, p.newstype, p.date, p.time, p.metat, p.metad, p.metad FROM news p
INNER JOIN news_cat pc
ON p.newsid = pc.news_id ". $queryCondition .$orderby;
 
 
$paginationlink = "mobile_news.php?page=";	
$pagination_setting = $_GET["pagination_setting"];
				
$page = 1;
if(!empty($_GET["page"])) {
$page = $_GET["page"];
}

$start = ($page-1)*$perPage->perpage;
if($start < 0) $start = 0;

$query =  $sql . " limit " . $start . "," . $perPage->perpage; 

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
?>      <div class="row">                       
    <div class="col-lg-12">                       
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

echo'  	<div class="post-list-item" style="padding:2px;">
						<!-- Nav tabs -->
						<!-- Tab panes -->
						<div class="tab-content" style="padding:0px;">
							<div role="tabpanel" class="tab-pane active ts-grid-box post-tab-list" id="home" style="padding:5px 0px 0px 0px;margin-bottom: 0px;">
								 
								<div class="post-content media" >
									<img class="d-flex sidebar-img" src="'.$urlroot.'images/news/'.$faq[$k]['image'].'" alt="">
									<div class="media-body">
										<p class="post-title" style="height:auto;overflow:hidden;">
											<a href="'.$urlroot.'news/'.$faq[$k]['newsurl'].'">'.$faq[$k]['title'].'</a>
										</p >
                                        <span class="post-tag" style="padding-top: 5px;margin-bottom: 10px;float: right;margin-right: 10px;"> 
                                            <a target="_blank" href="http://www.facebook.com/sharer.php?u='.$urlroot.'news/'.$faq[$k]['newsurl'].'" style="font-size: 20px;color: #445ff7;">
                                                <i class="fa fa-facebook"></i>
                                            </a> &nbsp;  
                                            <a href="https://wa.me/?text='.$faq[$k]['title'].' '.$urlroot.'news/'.$faq[$k]['newsurl'].' Download The Naradmuni App Now: https://play.google.com/store/apps/details?id=com.thenaradmuni.news" target="_blank" style="font-size: 20px;color: #3a8c3a;">
                                            <i class="fa fa-whatsapp"></i>
                                            </a> &nbsp;
                                            <a target="_blank" href="https://twitter.com/share?url='.$urlroot.'news/'.$faq[$k]['newsurl'].' Download The Naradmuni App Now: https://play.google.com/store/apps/details?id=com.thenaradmuni.news&text='.$faq[$k]['title'].'" style="font-size: 20px;color: #03A9F4;">
                                                <i class="fa fa-twitter"></i>
                                            </a>
										</span>
									</div>
								</div> 
								 
								<!--post-content end-->
							 
						</div>
						<!-- tab content end-->
					</div>
					<!-- ts single post item end-->
                    </div>  ';
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
    