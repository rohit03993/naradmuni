<?php
include_once '../admin/config.php';
require_once("../admin/dbcontroller.php"); 
$db_handle = new DBController();
header( 'Content-Type: text/html; charset=utf-8' );

$get_url=$_GET['url'];
$qry="SELECT n.*, c.hindi_name from news as n INNER JOIN categories as c ON c.id=n.category WHERE n.newsurl='".$get_url."'";
//$ex1=mysqli_query($con,$qry);
$n_row = $db_handle->runQuery($qry);
foreach($n_row as $nr){ extract($nr);}
//print_r($nr);die;
$auth = mysqli_query($con,"SELECT `name`,`image`,`email`,`fb_link`,`tw_link` FROM `team` WHERE `t_id`='".$nr['team_id']."'");
$au = mysqli_fetch_array($auth);

$qry_comm="SELECT * FROM comments AS s WHERE s.newsid='".$nr['newsid']."' AND s.status='Approved' ORDER BY s.c_id DESC LIMIT 10";
$ex_rss=mysqli_query($con,$qry_comm);
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
<title><?php echo $nr['metat']; ?></title>    
<meta property="og:type" content="news">
<meta property="og:title" content="<?php echo $nr['title']; ?>">
<meta property="og:description" content="<?php echo $nr['short_description']; ?>">
<meta property="og:url" content="<?php echo $urlroot.'news/'.$get_url; ?>">
<meta property="og:image" content="<?php echo $urlroot.'images/news/'.$nr['image']?>">
<meta property="fb:app_id" content="1948582202098235">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@the_naradmuni">
<meta name="twitter:creator" content="@the_naradmuni">
<meta name="twitter:title" content="<?php echo $nr['title']; ?>">
<meta name="twitter:description" content="<?php echo $nr['short_description']; ?>">
<meta name="twitter:image" content="<?php echo $urlroot.'images/news/'.$nr['image']; ?>">
<meta name="twitter:image:alt" content="<?php echo $nr['title']; ?>">

<meta name=description content="<?php echo $nr['metad']; ?>" />
    
<?php include"../css.php"; ?>
<style>
.single-post p span {
    background: #ffffff;
    font-size: 28px;
}
</style>
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
    <?php include"../header.php"; ?>
	<!-- block post area start-->
			<section class="single-post-wrapper">
		<div class="container">
			<div class="row">
				<div class="col-lg-9">
					
					<!-- breadcump end-->
					<div class="ts-grid-box content-wrapper single-post">
						<div class="entry-header">
							<h2 class="post-title lg" style="font-size: 25px;color: #232323;margin-bottom: 17px;font-family: 'NmHindi2Bold';font-weight: 700;"><?php echo $nr['title'];?></h2>
						</div>
						<!-- single post header end-->
						<div class="post-content-area">
							<div class="post-media post-featured-image">
								<a href="<?php echo $urlroot.'images/news/'.$nr['image']?>" class="gallery-popup">
									<img src="<?php echo $urlroot.'images/news/'.$nr['image']?>" class="img-fluid" alt="" style="width: 80%;max-height: 550px;margin-left: 10%;">
								</a>
							</div>
                        <div class="entry-header"><br>
							<ul class="post-meta-info">
								<li class="author">
									<a href="#">
										<img src="<?php echo $urlroot.'team/'.$au['image']; ?>" alt="<?php echo $au['name']; ?>"> <?php echo $au['name']; ?>
									</a>
								</li>
								<li>
									<i class="fa fa-clock-o"></i>
									<?php echo $nr['date'];?>
								</li>
								<!-- Load Facebook SDK for JavaScript -->

								<li class="share-post">
									<a type="button" href="<?php echo $urlroot.'news/'.$get_url; ?>" data-image="<?php echo $nr['image']; ?>" data-title="<?php echo $nr['title']; ?>" data-desc="<?php echo $nr['short_description']; ?>" class="FbShare">
										<i class="fa fa-facebook" style="background: #3b5999;"></i>
								</a>
								</li>
                                <li class="share-post">
									<a target="_blank" href="https://twitter.com/share?url=<?php echo $urlroot.'news/'.$get_url; ?> Download The Naradmuni App Now: https://play.google.com/store/apps/details?id=com.thenaradmuni.news&text=<?php echo $nr['title']; ?>">
										<i class="fa fa-twitter" style="background: #55acee;"></i>
									</a>
								</li>
                                <li class="share-post">
									<a href="https://wa.me/?text=<?php echo $nr['title']; ?> <?php echo $urlroot.'news/'.$get_url; ?> Download The Naradmuni App Now: https://play.google.com/store/apps/details?id=com.thenaradmuni.news" target="_blank">
										<i class="fa fa-whatsapp" style="background: #4caf50;"></i>
									</a>
								</li>
							</ul>
						</div>
                            
							<?php  if($nr['description']!=""){?>
							<div class="entry-content" style="margin-top: 2%;font-family: 'NotoSans-Regular';font-size:18px;line-height: 30px;">
								<?php echo $nr['description']; ?>
							</div>
							<?php }else{?>
							<div class="entry-content" style="margin-top: 2%;">
								<h3><?php echo $nr['short_description']; ?></h3>
                                <h2>खबर अपडेट हो रही है..,</h2>
							</div>
							<?php }?>
							<!-- entry content end-->
						</div>
						<!-- post content area-->
						<div class="author-box">
							<img class="author-img" src="<?php echo $urlroot.'team/'.$au['image']; ?>" alt="<?php echo $au['name']; ?>">
							<div class="author-info">
								<h4 class="author-name"><?php echo $au['name']; ?></h4>
								<div class="authors-social">
									<a href="<?php echo $au['tw_link']; ?>" target="_blank" class="ts-twitter">
										<i class="fa fa-twitter"></i>
									</a>
									<a href="<?php echo $au['fb_link']; ?>" target="_blank" class="ts-facebook">
										<i class="fa fa-facebook"></i>
									</a>
									<a href="https://api.whatsapp.com/send?phone=+917415716541&text=व्हाट्सप्प पर खबरें भेजें" target="_blank" title="व्हाट्सप्प पर खबरें पाएं " class="ts-google-plus">
										<i class="fa fa-whatsapp"></i>
									</a>
								</div>
								<div class="clearfix"></div>
								<p><?php echo $au['email']; ?></p>

							</div>
						</div>
						
					</div>
					<!--single post end -->
					
					<div class="comments-form ts-grid-box">

						<h3 class="comments-counter"> Comments</h3>
						<ul class="comments-list">
							<li>
						<?php while($row=mysqli_fetch_array($ex_rss)) {
							?>
								<div class="comment">
									<img class="comment-avatar float-left" alt="" src="<?php echo $urlroot;?>images/avatar/blank.png">
									<div class="comment-body">
										<div class="meta-data"><!---<span class="float-right"><a class="comment-reply" href="#"><i 	class="fa fa-mail-reply-all"></i> Reply</a></span>--->
											<span class="comment-author"><?php echo $row['name'];?></span><span class="comment-date"><?php echo date('F d, Y',strtotime($row['date']));?></span>
										</div>
										<div class="comment-content">
											<p><?php echo $row['comment'];?></p>
										</div>
									</div>
								</div>
							<?php 
							}
							?>
								<!-- Comments end-->
							<!--	<ul class="comments-reply">
									<li>
										<div class="comment">
											<img class="comment-avatar float-left" alt="" src="images/avatar/author2.html">
											<div class="comment-body reply-bg">
												<div class="meta-data"><span class="float-right"><a class="comment-reply" href="#"><i class="fa fa-mail-reply-all"></i> Reply</a></span>
													<span class="comment-author">Henry kendel</span><span class="comment-date">October 31, 2018</span>
												</div>
												<div class="comment-content">
													<p>There’s such a thing as “too much information”, especially for the companies scaling out their sales operations. That’s why Attentive was born</p>
												</div>
											</div>
										</div>
										
									</li>
								</ul>--->
								<!-- comments-reply end-->
							</li>
							<!-- Comments-list li end-->
							
						</ul>
						<!-- Comments-list ul end-->
           <?php
                if(isset($_POST['post'])){
                    
                    $comment = mysqli_real_escape_string($con, $_POST['comment']);
                    $email = mysqli_real_escape_string($con, $_POST['email']);
                    $name = mysqli_real_escape_string($con, $_POST['name']);
                    $newsid = $nr['newsid'];
                    $date = date("Y-m-d");
                    $time=date('h:i:s:A');
                    $status = 'pending';
                    if (empty($comment)) { array_push($errors, "Kindly type something"); }
                    if (empty($email)) { array_push($errors, "Kindly fill Email"); }
                    if (empty($name)) { array_push($errors, "Kindly fill Name"); }
                    
                    
                    if (count($errors) == 0) {
                       $qry="INSERT INTO `comments`(`newsid`, `u_id`, `name`, `comment`, `date`, `time`, `status`) VALUES('$newsid','$email','$name','$comment','$date','$time','$status')";
                        $ex=mysqli_query($con,$qry);
                        if ($ex>0) {
                            echo "<script>alert('Successful.')</script>"; }
                        else{ echo "<script>alert('Sorry, there was an error!!!')</script>"; }
                        }

                    
        }
?>
						<h3 class="comment-reply-title">Add Comment</h3>
						<?php include"../errors.php"; ?>
						<form role="form"  action="" method="post"  class="ts-form">
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<input class="form-control" name="name" id="name" placeholder="Your Name" type="text" required="">
                                    </div>
                                    <div class="form-group">
                                        <input class="form-control" name="email" id="email" placeholder="Your Email" type="email" required="">
									</div>
									<div class="form-group">
										<textarea class="form-control msg-box" name="comment" id="message" placeholder="Your Comment" rows="10" required=""></textarea>
									</div>
								</div>
								
								<!--<div class="col-md-12">
									<p class="comment-form-cookies-consent">
										<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes">
										<label for="wp-comment-cookies-consent">Save my name, email, and website in this browser for the next time I comment.</label>
									</p>
								</div>--->
							</div>
							<!-- Form row end -->
							<div class="clearfix">
								<button class="comments-btn btn btn-primary" name="post" type="submit">Post Comment</button>
							</div>
						</form>
						<!-- Form end -->
					</div>
				</div>
				<!-- col end -->
				<div class="col-lg-3">
                    <?php include"../sidebar.php"; ?>
				</div>
				<!-- right sidebar end-->
				<!-- col end-->
			</div>
			<!-- row end-->
		</div>
		<!-- container-->
	</section>

	


	<!-- footer social list start-->
    <?php include"../footer.php"; ?>
	<!-- footer end -->




	<!-- javaScript Files
	=============================================================================-->
<?php include"../js.php"; ?>
    <script>
        $(document).ready(function(){
            $.ajax({
						type: "POST",
						url: "../API/insert-news-view.php",
						data:{newsid:'<?php echo $nr['newsid']; ?>'},
						success: function(data){
                            //alert(data);
						}
			});
        });
    </script>
	<script>
window.fbAsyncInit = function(){
FB.init({
    appId: '739461563659883', status: true, cookie: true, xfbml: true }); 
};
(function(d, debug){var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
    if(d.getElementById(id)) {return;}
    js = d.createElement('script'); js.id = id; 
    js.async = true;js.src = "//connect.facebook.net/en_US/all" + (debug ? "/debug" : "") + ".js";
    ref.parentNode.insertBefore(js, ref);}(document, /*debug*/ false));
function postToFeed(title, desc, url, image){
var obj = {method: 'feed',link: url, picture: '<?php echo $urlroot.'images/news/'; ?>'+image,name: title,description: desc};
function callback(response){}
FB.ui(obj, callback);
}
    
$('.FbShare').click(function(){
elem = $(this);
postToFeed(elem.data('title'), elem.data('desc'), elem.prop('href'), elem.data('image'));

return false;
});
</script>
</body>
</html>