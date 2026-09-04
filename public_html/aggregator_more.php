<?php include"admin/config.php"; 
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
    <title>The Naradmuni | न्यूज़ फीड</title>

	<!-- CSS
   ==================================================== -->
	<?php include"css.php"; ?>

</head>
<body>
	<div class="body-inner-content category-layout-6">
		<!-- top bar start -->
     <?php include"header.php"; ?>


		<!-- block post area start-->
		<section class="block-wrapper mt-15">
			<div class="container">
				<div class="row mb-30">
					<div class="col-lg-12">
						<div class="">
							<ol class="ts-breadcrumb">
								<li>
									<a href="#">
										<i class="fa fa-home"></i>
										Home
									</a>
								</li>
								<li>
									<a href="#">न्यूज़ फीड</a>
								</li>

							</ol>
							<div class="clearfix entry-cat-header">
								<h2 class="ts-title float-left">न्यूज़ फीड</h2>
									
									 
								
							</div>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-lg-9">
						
						<div class="post-list">
							<!-- ts title end-->
							<style>
							.post-content p a>img{
								width:50px;height:50px;
							}
							.pagination li a {
    display: block;
 width: 100% !important;
    height:100% !important;
     border-radius:   0% !important;
     -webkit-border-radius:  0% !important;
    -ms-border-radius: 0% !important;
    line-height: 31px;
    font-size: 14px;
    color: #888888;
}
							</style>
							<?php
$qry="SELECT * FROM `rss_feed`  ORDER BY sequence ASC";
$ex_rss=mysqli_query($con,$qry);
$k=0; $z=1; while($row=mysqli_fetch_array($ex_rss)) { 
$url=$row['link'];	

$newsoutput = new SimpleXMLElement($url, LIBXML_NOCDATA, true);
$newsoutput = json_decode(json_encode($newsoutput), TRUE);
$i = 0;
foreach ($newsoutput['channel']['item'] as $item) {
$pubDate = date('D, d M Y',strtotime($item['pubDate']));
$now = date('D, d M Y');
if($i>=2) break;
if (strtotime($pubDate) == strtotime($now)){
$title = $item['title'];
$link = $item['link'];
$author = $item['author'];
$description = $item['description'];
$postDate = $item['pubDate'];
?>
							<div class="row mb-10 show_hide_all  show_hide_page<?php echo $z; ?> " style="<?php if($z>1){ echo 'display:none;';}?>">
								<div class="col-md-2">
									<div class="ts-post-thumb">
									<!--	<a href="<?php echo $link; ?>" class="post-cat ts-purple-bg"><?php echo $title; ?></a>--->
										<a href="<?php echo $link; ?>">
											<img class="img-fluid" src="<?php echo $urlroot.'images/gallery/'.$row['image_name']; ?>" alt="">
										</a>
									</div>
								</div>
								<!-- col lg end-->
								<div class="col-md-10">
									<div class="post-content">
										<h3 class="post-title md">
											<a href="<?php echo $link; ?>"><?php echo $title; ?></a>
										</h3>
										<ul class="post-meta-info">
											<li>
												<a href="#"> <?php echo $author;?></a>
											</li>
											<li>
												<i class="fa fa-clock-o"></i>
												<?php echo  $pubDate;?>
											</li>
										</ul>
										<!--<p><a href="<?php echo $link; ?>">
											<?php echo implode(' ', array_slice(explode(' ', $description), 0, 30)) . "..."; ?><span style="color:red;"> Read more </span></a>
										</p>-->
									</div>
								</div>
							</div>
							<?php  } $i++;} }
							
							
							?>
						</div>
						<ul class="pagination ts-category-list float-right" id="pagination"></ul>
					</div>
					<div class="col-lg-3">
                        <?php include"sidebar.php"; ?>
					</div>
				</div>
				<!-- row end-->
			</div>
			<!-- container end-->
		</section>
		<!-- block area end-->

		

    <?php include"footer.php"; ?>


	</div>

<?php include"js.php"; 
?>
 <script src="js/jquery.twbsPagination.js" type="text/javascript"></script>
<script language="javascript">

 $(function () {
        window.pagObj = $('#pagination').twbsPagination({
            totalPages: <?php echo $z;?>,
            visiblePages: 5,
            onPageClick: function (event, page) {
				$('.show_hide_all').hide();
				$('.show_hide_page'+page).show();
                 
				  $('html, body').animate({
         scrollTop: $(".body-inner-content").offset().top
     }, 1000);
				 
            }
        }).on('page', function (event, page) {
           $('.show_hide_all').hide();
				$('.show_hide_page'+page).show();
				  $('html, body').animate({
         scrollTop: $(".body-inner-content").offset().top
     }, 1000);
        });
    });
</script>
</body>
</html>