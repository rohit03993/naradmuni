<?php
$useragent=$_SERVER['HTTP_USER_AGENT'];

if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
header('Location: mobile.php');

include"admin/config.php"; 
require_once("admin/dbcontroller.php");
$db_handle = new DBController();
$now = date('y-m-d H:i');
$query = "SELECT * from news as n INNER JOIN categories as c ON c.id=n.category where n.status='Published' AND n.slider='Yes' GROUP BY n.newsid ORDER BY n.newsid DESC, n.slider_priority ASC LIMIT 5";
$bnews = $db_handle->runQuery($query);

$lnsql = "SELECT * from news as n INNER JOIN categories as c ON c.id=n.category where n.status='Published' AND n.newstype != 'Video' GROUP BY n.newsid order by n.newsid DESC LIMIT 15";
$ltnews = $db_handle->runQuery($lnsql);

$stqry = "SELECT p.*,c.hindi_name  FROM news AS p 
JOIN news_cat AS pc
ON p.newsid = pc.news_id
JOIN categories AS c ON c.id=p.category
WHERE pc.category='1' AND p.status='Published' order by p.newsid DESC LIMIT 12";
$stnews = $db_handle->runQuery($stqry);

$gmqry = "SELECT p.*,c.hindi_name  FROM news AS p 
JOIN news_cat AS pc
ON p.newsid = pc.news_id
JOIN categories AS c ON c.id=p.category
WHERE pc.category='11' AND p.status='Published' order by p.newsid DESC LIMIT 2";
$gmnews = $db_handle->runQuery($gmqry);

$tnews1 = mysqli_query($con,"SELECT p.team_id,p.newsid,p.videoid,c.hindi_name, p.title, p.image, p.newsurl, p.date, p.time, p.metat, p.metad, p.metad FROM news p INNER JOIN news_cat pc ON p.newsid = pc.news_id INNER JOIN categories c ON p.category=c.id WHERE pc.category = '24' AND p.status='Published' order by p.newsid DESC LIMIT 1");

$row=mysqli_fetch_array($tnews1);

$vnews="SELECT p.team_id,p.newsid,p.videoid,c.hindi_name, p.title, p.image, p.newsurl, p.date, p.time, p.metat, p.metad, p.metad FROM news p INNER JOIN news_cat pc ON p.newsid = pc.news_id INNER JOIN categories c ON p.category=c.id WHERE pc.category = '10' AND p.newsid!='".$row['newsid']."' AND p.status='Published' order by p.newsid DESC LIMIT 3";

$enews="SELECT p.team_id,p.newsid,c.hindi_name, p.title, p.short_description, p.image, p.newsurl, p.date, p.time, p.metat, p.metad, p.metad FROM news p INNER JOIN news_cat pc ON p.newsid = pc.news_id INNER JOIN categories c ON p.category=c.id WHERE pc.category = '78' AND p.status='Published' order by p.newsid DESC LIMIT 2";


$pnews="SELECT p.team_id,p.newsid,c.hindi_name, p.title, p.short_description, p.image, p.newsurl, p.date, p.time, p.metat, p.metad, p.metad FROM news p INNER JOIN news_cat pc ON p.newsid = pc.news_id INNER JOIN categories c ON p.category=c.id WHERE pc.category = '25' AND p.status='Published' order by p.newsid DESC LIMIT 5";

header( 'Content-Type: text/html; charset=utf-8' );
?>
<!doctype html>
<html lang="en">
<head>
	<!-- Basic Page Needs =====================================-->
	<meta charset="utf-8">

	<!-- Mobile Specific Metas ================================-->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Site Title- -->
	<title>Latest and breaking news in Hindi (हिंदी न्यूज़ ) from and Central India | The Naradmuni  </title>

    <meta type="description" content="The Naradmuni- Brings you Hindi News (हिंदी न्यूज़) from Madhya Pradesh, Chhattisgarh and entire Central India.  Get Latest News in Hindi (हिंदी समाचार) and Hindi News Live." />
	<!-- CSS
   ==================================================== -->
	<?php include"css.php"; ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXLBRKDEVS"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-XXLBRKDEVS');
</script>
</head>

<body class="body-color">
	<!-- header nav start-->
    <?php include"header.php"; ?>
	<!-- header nav end-->

	<!-- block post area start-->
	<section class="block-wrapper mt-15">
		<div class="container">
			<div class="row">
				<div class="col-lg-7 col-md-12">
					<div id="featured-slider" class="owl-carousel ts-overlay-style ts-featured">
					<?php $i=1; foreach($bnews as $bn){ extract($bn);?>
						<div class="item" style="background-image:url(<?php echo $urlroot; ?>images/news/<?php echo $bn["image"]; ?>)" >
							<a class="post-cat ts-orange-bg" href="<?php echo $urlroot.'news/'.$bn['newsurl'];?>"><?php echo $hindi_name;?></a>
							<div class="overlay-post-content">
								<div class="post-content">
									<h2 class="post-title lg">
										<a href="<?php echo $urlroot.'news/'.$bn['newsurl'];?>"><?php echo $title;?></a>
									</h2>
									<?php
									$auth1 = mysqli_query($con,"SELECT `name`,`image`,`email` FROM `team` WHERE `t_id`='".$bn['team_id']."'");
									$au1 = mysqli_fetch_array($auth1);
									?>
									<ul class="post-meta-info">
										<li class="author">
											<a href="#">
												<img src="<?php echo $urlroot.'team/'.$au1['image']; ?>" alt="<?php echo $au1['name']; ?>"> <?php echo $au1['name']; ?>
											</a>
										</li>
										<!--<li>
											<i class="fa fa-clock-o"></i>
											<?php echo $date;?>
										</li>-->
										<!--
										<li class="active">
											<i class="icon-fire"></i>
											3,005
										</li>-->
									</ul>
								</div>
							</div>
							<!--/ Featured post end -->

						</div>
					<?php $i++; }?>
						<!-- Item 1 end -->
						
					</div>
					<!-- Featured owl carousel end-->
				</div>
				<!-- col end-->

				<div class="col-lg-5">
				<div class="post-list-box bs-grid-box" id="nav-tab" role="tablist">
                <?php  $lnsql1 = "SELECT p.newsid,c.hindi_name, p.title, p.title, p.image, p.newsurl, p.description, p.date, p.time, p.metat, p.metad, p.metad FROM news p INNER JOIN news_cat pc ON p.newsid = pc.news_id INNER JOIN categories c ON p.category=c.id WHERE p.status='Published' AND p.latest_news='Yes' AND p.slider='No' AND p.newstype != 'Video' GROUP BY p.newsid ORDER BY p.latest_priority ASC LIMIT 3 ";
				$ltnews1 = $db_handle->runQuery($lnsql1);
				$k = 1;
				foreach($ltnews1 as $tn){ extract($tn);
				?>	

				<a class="nav-item nav-link" href = '<?php echo $urlroot;?>news/<?php echo $tn['newsurl'];?>'>
				<div class="post-content media">
				<img class="d-flex" src="<?php echo $urlroot; ?>images/news/<?php echo $tn["image"]; ?>" alt="">
				<div class="media-body align-self-center">
				<h4 class="post-title" style="height: 82px;overflow: hidden;">
				<?php echo $tn["title"]; ?>
				</h4>
				</div>
				</div>
				</a>
				<?php  $k++;} ?>
				</div>
				
				</div>
				<!-- col end-->

				<!-- col end-->
			</div>
			<!-- row end-->
		</div>
		<!-- container end-->
	</section>
	<!-- block area end-->


	<!-- post wraper start-->
	<section class="block-wrapper">
		<div class="container">
			<div class="row">
				<div class="col-lg-3 col-md-4">
					<div class="posts-ad">
                        <?php
                            $ads = mysqli_query($con,"SELECT `link`,`image`,`title` FROM `ads` WHERE `position`='1' ORDER BY `ad_id` DESC LIMIT 1");
                            $ad = mysqli_fetch_array($ads);
                        ?>
						<a target="_blank" href="<?php echo $ad['link']; ?>">
							<img src="<?php echo $urlroot.'ads/'.$ad['image']; ?>" alt="<?php echo $ad['title']; ?>">
						</a>
					</div>
				</div>
				<!-- col end -->
				<div class="col-lg-9 col-md-8">
					<div class="ts-grid-box most-populer-item">
						<h2 class="ts-title">ताज़ा खबर</h2>

						<div class="most-populers owl-carousel">
						<?php $i=1; foreach($ltnews as $ltn){ extract($ltn);
						if($i>3){?>
							
							<div class="item">
								<a class="post-cat ts-orange-bg" href="#"><?php echo $ltn['hindi_name'];?></a>
								<div class="ts-post-thumb">
									<a href="<?php echo $urlroot;?>news/<?php echo $ltn['newsurl'];?>">
										<img class="img-fluid" src="<?php echo $urlroot; ?>images/news/<?php echo $ltn["image"]; ?>" alt="">
									</a>
								</div>
								<div class="post-content">
									<h3 class="post-title">
										<a href="<?php echo $urlroot;?>news/<?php echo $ltn['newsurl'];?>"><?php echo implode(' ', array_slice(explode(' ', $ltn['title']), 0, 15)) . "..."; ?></a>
									</h3>
									<!--<span class="post-date-info">
										<i class="fa fa-clock-o"></i>
										<?php echo $ltn['date'];?>
									</span>-->
								</div>
							</div>
						<?php }$i++; }?>
							<!-- ts-grid-box end-->

						</div>
						<!-- most-populers end-->
					</div>
					<!-- ts-populer-post-box end-->
				</div>
				<!-- col end-->
			</div>
			<!-- row end-->
		</div>
		<!-- container end-->
	</section>
	<!-- post wraper end-->

	<!-- post wraper start-->
	<section class="block-wrapper mb-30 hot-topics-heighlight">
		<div class="container">

			<div class="ts-grid-box">
				<h2 class="ts-title">राज्य</h2>

				<div class="owl-carousel" id="hot-topics-slider">
				<?php $i=1; foreach($stnews as $stn){ extract($stn);?>
				
					<div class="item ts-blue-light-heighlight heighlight">
						<div class="ts-post-thumb" style="height: 140px;">
							<a href="<?php echo $urlroot;?>news/<?php echo $stn['newsurl'];?>">
								<img class="img-fluid" src="<?php echo $urlroot; ?>images/news/<?php echo $stn["image"]; ?>" alt="">
							</a>
							<a class="post-cat" href="<?php echo $urlroot;?>news/<?php echo $stn['newsurl'];?>"><?php echo $stn['hindi_name'];?></a>
						</div>

						<div class="post-content" style="height: 118px;">

							<h3 class="post-title">
								<a href="<?php echo $urlroot;?>news/<?php echo $stn['newsurl'];?>"><?php echo mb_substr($stn['title'], 0, 90) . "..."; ?></a>
							</h3>
							<!--<span class="post-date-info">
								<i class="fa fa-clock-o"></i>
								<?php echo $stn['date'];?>
							</span>-->
						</div>
					</div>
				<?php $i++; } ?>	
					<!-- ts-grid-box end-->

					
					<!-- ts-grid-box end-->
				</div>
				<!-- most-populers end-->
			</div>
			<!-- ts-populer-post-box end-->
		</div>
		<!-- container end-->
	</section>
	<!-- post wraper end-->

	<!-- ad banner 2 start-->
	<section class="block-wrapper">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<div class="banner-img text-center">
                        <?php
                            $ads = mysqli_query($con,"SELECT `link`,`image`,`title` FROM `ads` WHERE `position`='2' ORDER BY `ad_id` DESC LIMIT 1");
                            $ad = mysqli_fetch_array($ads);
                        ?>
						<a target="_blank" href="<?php echo $ad['link']; ?>">
                            <img class="img-fluid" src="<?php echo $urlroot.'ads/'.$ad['image']; ?>" alt="<?php echo $ad['title']; ?>">
						</a>
					</div>
				</div>
				<!-- col end -->
			</div>
			<!-- row  end -->
		</div>
		<!-- container end -->
	</section>
	<!-- ad banner 2 end-->

	<!-- watch now start-->
	<section class="block-wrapper">
		<div class="container">

			<div class="row">
				<div class="col-lg-9">
					<div class="ts-grid-box watch-post mb-30">
						<h2 class="ts-title"> वीडियो </h2>
						<div class="row">
							<div class="col-lg-7">
									<div class="tab-content featured-post" id="nav-tabContent">
							
								<?php
$vnews2=mysqli_query($con,$vnews);					
$k=1;
while($tn=mysqli_fetch_array($vnews2)) {
	
	$newsurl=$tn['newsurl'];	
$title=$tn['title'];	
$videoid=$tn['videoid'];	
$image=$tn['image'];	
$date=$tn['date'];	
$time=$tn['time'];	
$hindi_name=$tn['hindi_name'];	
$newsurl=$tn['newsurl'];	
$newsid=$tn['newsid'];	
?>										<div class="tab-pane ts-overlay-style fade <?php if($k==1) { echo "show";} ?> <?php if($k==1) { echo "active";} ?> " id="nav-<?php echo $tn["newsid"]; ?>" role="tabpanel" aria-labelledby="nav-<?php echo $tn["newsid"]; ?>-tab">

										<div class="item" style="background-image: url(<?php echo $urlroot; ?>images/news/<?php echo $tn["image"]; ?>)">

											<a class="post-cat ts-orange-bg" href="#"><?php echo $tn["hindi_name"]; ?></a>
											<a href="https://www.youtube.com/watch?v=<?php echo $tn["videoid"]; ?>" class="ts-video-btn">
												<i class="fa fa-play-circle-o"></i>
											</a>
											<div class="overlay-post-content">
												<div class="post-content">
													<h3 class="post-title md">
														<a href="<?php echo $urlroot;?>news/<?php echo $tn['newsurl'];?>"><?php echo $tn["title"]; ?></a>
													</h3>
													<ul class="post-meta-info">
														<li class="author">
<?php
$auth3 = mysqli_query($con,"SELECT `name`,`image`,`email` FROM `team` WHERE `t_id`='".$tn['team_id']."'");
$au3 = mysqli_fetch_array($auth3);
?>
															<a href="#">
																<img src="<?php echo $urlroot.'team/'.$au3['image']; ?>" alt="<?php echo $au3['name']; ?>"> <?php echo $au3['name']; ?>
															</a>
														</li>
														<!--<li>
															<i class="fa fa-clock-o"></i>
															<?php echo $tn["date"]; ?>, <?php echo $tn["time"]; ?>
														</li>-->
														 
													</ul>
												</div>
											</div>
											<!-- overlay post content-->
										</div>
										<!-- item end-->
									</div>
<?php $k++;  }?>

								 </div>
								</div>
							<!-- col end-->

							<div class="col-lg-5">
									
								<div class="nav post-list-box" id="nav-tab" role="tablist">
								<?php 
$k=1;
$vnews3=mysqli_query($con,$vnews);
while ($tn=mysqli_fetch_array($vnews3)) {


					?>			
									<a class="nav-item nav-link <?php if($k==1) { echo "active";} ?>" id="nav-<?php echo $tn["newsid"]; ?>-tab" data-toggle="tab" href="#nav-<?php echo $tn["newsid"]; ?>" role="tab" aria-controls="nav-<?php echo $tn["newsid"]; ?>"
									 aria-selected="<?php if($k==1) { echo "true";} else {echo "false";}?>">
									  <div class="post-content media">
											<img class="d-flex" src="<?php echo $urlroot; ?>images/news/<?php echo $tn["image"]; ?>" alt="">
											<div class="media-body align-self-center">
												<h4 class="post-title" style="height: 82px;overflow: hidden;">
												<?php echo $tn["title"]; ?>
												</h4>
												<!--<span class="post-date-info">
													<i class="fa fa-clock-o"></i>
												 <?php echo $tn["date"]; ?> , <?php echo $tn["time"]; ?>
												</span>-->
											</div>
										</div>
									</a>
									
									
								<?php  $k++;} ?>
								</div>
								
								<!-- watch list post end-->
							</div>
							<!-- col end -->
						</div>
						<!-- row end-->
					</div>
					<!-- ts-populer-post-box end-->

					<!-- tranding post start -->
					<div class="row category-style">
						<div class="col-lg-4">
							<div class="ts-grid-box ts-col-box">
								<h2 class="ts-title">खेल</h2>
								<?php $i=1; foreach($gmnews as $gmn){ extract($gmn);?>
								
								<div class="item">
									<div class="ts-post-thumb">
										<a class="post-cat ts-pink-bg" href="<?php echo $urlroot;?>news/<?php echo $gmn['newsurl'];?>"><?php echo $gmn['hindi_name'];?></a>
										<a href="<?php echo $urlroot;?>news/<?php echo $gmn['newsurl'];?>">
											<img class="img-fluid" src="<?php echo $urlroot; ?>images/news/<?php echo $gmn["image"]; ?>" alt="">
										</a>
									</div>
									<div class="post-content">
										<h3 class="post-title">
											<a href="<?php echo $urlroot;?>news/<?php echo $gmn['newsurl'];?>"><?php echo $gmn['title'];?></a>
										</h3>
									</div>
								</div>
								<?php $i++; } ?>
								<!-- ts-grid-box end-->

							
								
							</div>
							<!-- ts-populer-post-box end-->
						</div>
						<!-- col end-->
						<div class="col-lg-8">
							<div class="ts-grid-box ts-tranding-post">
								<h2 class="ts-title"> अजब - गजब </h2>
								<!-- arrow start -->
								<div class="ts-arrow">
									<a class="control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
										<span class="fa fa-angle-left" aria-hidden="true"></span>
									</a>
									<a class="control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
										<span class="fa fa-angle-right" aria-hidden="true"></span>
									</a>
								</div>
								<!-- arrow end -->

								<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
									<div class="carousel-inner">
<?php 
$enews1=mysqli_query($con,$enews);
$i=1;
while($mn=mysqli_fetch_array($enews1)) {
	
	$newsurl=$mn['newsurl'];	
$title=$mn['title'];	 	
$image=$mn['image'];	
$date=$mn['date'];	
$time=$mn['time'];	
$hindi_name=$mn['hindi_name'];	
$newsurl=$mn['newsurl'];	 
$short_description=$mn['short_description'];	

?>									
									
									<div class="carousel-item <?php if($i==1) {echo "active";}?>">
											<div class="ts-overlay-style">
												<div class="item">
													<div class="ts-post-thumb">
														<a href="#">
															<img class="img-fluid" src="<?php echo $urlroot; ?>images/news/<?php echo $mn["image"]; ?>" style="width:720px;height:390px;" alt="">
														</a>
													</div>
													<div class="overlay-post-content">
														<div class="post-content">
															<h3 class="post-title md">
																<a href="<?php echo $urlroot;?>news/<?php echo $mn['newsurl'];?>"><?php echo $mn["title"]; ?></a>
															</h3>
															<ul class="post-meta-info">
																<li class="author">
<?php
$auth4 = mysqli_query($con,"SELECT `name`,`image`,`email` FROM `team` WHERE `t_id`='".$mn['team_id']."'");
$au4 = mysqli_fetch_array($auth4);
?>
															<a href="#">
																<img src="<?php echo $urlroot.'team/'.$au4['image']; ?>" alt="<?php echo $au4['name']; ?>"> <?php echo $au4['name']; ?>
															</a>
																</li>
																<!--<li>
																	<i class="fa fa-clock-o"></i>
																	<?php echo $mn["date"]; ?> , <?php echo $mn["time"]; ?>
																</li>-->
																 
															</ul>
														</div>
													</div>
												</div>
											</div>

										</div>
										
<?php  $i++;  } ?>  					<!-- ts-overlay-style end-->
										</div>
									<!-- carousel-inner end -->

									<!-- slider dot start -->
									<ol class="slider-indicators carousel-indicators heighlight clearfix" style="z-index:1;">
									
				<?php	$enews2=mysqli_query($con,$enews);
$j=1;
while($mn=mysqli_fetch_array($enews2)) {
	
?>							 <li data-target="#carouselExampleIndicators" data-slide-to="<?php if($j==1	){ echo "0";} else { echo "1";}?>" class="<?php if($j==1){ echo "active";}?>">
											<div class="post-content media">
												<div class="d-flex post-count"><?php if($j==1){ echo "1";} else { echo "2";}?></div>
												<div class="media-body align-self-center">
													<h4 class="post-title" style="height:50px;padding-top:1%;overflow:hidden;">
														<a href="#">
														<?php  echo $mn['short_description'];  ?>
														</a>
													</h4>
													<!--<span class="post-date-info">
														<i class="fa fa-clock-o"></i>
														 <?php echo $mn["date"]; ?> , <?php echo $mn["time"]; ?>
													</span>-->
												</div>
											</div>
										</li>
										
<?php $j++; }?>

									</ol>
									<!-- slider dot end -->
								</div>
								<!-- watch now content end-->
							</div>
						</div>
					</div>
					<!-- tranding post end -->

				</div>
				<!-- col end-->
				<div class="col-lg-3">
					<?php include"sidebar.php";?>
					<!-- right sidebar end-->
				</div>
				<!-- col end-->
			</div>

		</div>
		<!-- container end-->
	</section>
	<!-- watch now end-->

	<!-- post wraper start-->
	<section class="block-wrapper mb-45" id="more-news-section">
		<div class="container">
			<div class="ts-grid-box ts-grid-box-heighlight">
				<h2 class="ts-title"> शख्सियत </h2>

				<div class="owl-carousel" id="more-news-slider">
				
	<?php
$pnews2=mysqli_query($con,$pnews);					
$x=1;
while($pn=mysqli_fetch_array($pnews2)) {
	
$newsurl=$pn['newsurl'];	
$title=$pn['title'];	 	
$image=$pn['image'];	
$date=$pn['date'];	
$time=$pn['time'];	
$hindi_name=$pn['hindi_name'];	
$newsurl=$pn['newsurl'];	
$newsid=$pn['newsid'];	
?>				

				<div class="ts-overlay-style">
						<div class="item">
							<div class="ts-post-thumb">
								<a href="<?php echo $urlroot;?>news/<?php echo $pn['newsurl'];?>">
									<img class="img-fluid" src="<?php echo $urlroot; ?>images/news/<?php echo $pn["image"]; ?>" alt="">
								</a>
							</div>
							<a class="post-cat ts-green-bg" href="<?php echo $urlroot;?>news/<?php echo $pn['newsurl'];?>"><?php echo $pn["hindi_name"]; ?></a>
							<div class="overlay-post-content">
								<div class="post-content">
									<h3 class="post-title">
										<a href="<?php echo $urlroot;?>news/<?php echo $pn['newsurl'];?>"><?php echo $pn["title"]; ?></a>
									</h3>
									<!--<span class="post-date-info">
										<i class="fa fa-clock-o"></i>
										<?php echo $pn["date"]; ?> , <?php echo $pn["time"]; ?>
									</span>-->
								</div>
							</div>
						</div>
						<!-- end item-->
					</div>
					
					

					
<?php $x++; } ?>
					
				  
				</div>
				<!-- most-populers end-->
			</div>
			<!-- ts-populer-post-box end-->
		</div>
		<!-- container end-->
	</section>
	<!-- post wraper end-->

	<!-- footer social list start-->
    <?php include"footer.php"; ?>
	<!-- footer end -->




	<!-- javaScript Files
	=============================================================================-->
<?php include"js.php"; ?>
	
</body>
</html>