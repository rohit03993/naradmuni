<?php
include'../admin/config.php';
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
    
    <meta property="og:type" content="RSS">
    <meta property="og:title" content="RSS Feed">
    <meta property="og:url" content="<?php echo $urlroot.'rss/'; ?>">

	<!-- Site Title- -->
	<title>RSS Feed - News World</title>

	<!-- CSS
   ==================================================== -->
    <?php include"../css.php"; ?>
</head>

<body class="body-color">
    <?php include"../header.php"; ?>
	<!-- header nav end-->

	<!-- single post start -->
	<section class="single-post-wrapper">
		<div class="container">
			<div class="row">
				<div class="col-lg-12"> 
					<!-- breadcump end-->
					<div class="ts-grid-box content-wrapper single-post">
						<!-- single post header end-->
						<div class="post-content-area">
							<div class="entry-content">
<table class="table table-striped table-bordered text-white">
<thead class = "bg-dark">
<tr> 
<th scope="col">Title</th>
<th scope="col">RSS Path</th> 
</tr>
</thead>
<tbody class = "text-dark">
<?php 
$qry88 = mysqli_query($con,"SELECT hindi_name,cat_url FROM `categories` ");
while($cat = mysqli_fetch_array($qry88)){
?>
<tr>
<th scope="row"><?php echo $cat['hindi_name']; ?></th>
<td><?php echo '<a href="'.$urlroot.'rssfeed/'.$cat['cat_url'].'.xml" >'.$urlroot.'rssfeed/'.$cat['cat_url'].'.xml</a>'; ?></td> 
</tr> 
 <?php } ?>   
</tbody>
</table>
							</div>
							<!-- entry content end-->
						</div>
						<!-- post content area-->
						<!-- post navigation end-->
					</div>
					<!--single post end -->
					<!-- comment form end-->

				</div> 
				<!-- right sidebar end-->
				<!-- col end-->
			</div>
			<!-- row end-->
		</div>
		<!-- container-->
	</section>
	<!-- single post end-->

	<!-- footer social list start-->
	<?php include"../footer.php"; ?>


	<!-- javaScript Files
	=============================================================================-->

	<?php include"../js.php"; ?>
</body>
</html>