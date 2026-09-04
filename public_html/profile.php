<?php include"admin/config.php"; 
header( 'Content-Type: text/html; charset=utf-8' );
$url='profiles';
$qry="select * from categories where cat_url='$url'";
$ex=mysqli_query($con,$qry);
$rs=mysqli_fetch_array($ex);
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
   <title>Profile</title>

   <!-- CSS
   ==================================================== -->
   <?php include"css.php"; ?>
</head>

<body class="body-color">
   <div class="body-inner-content category-layout-4">
      <?php include"header.php"; ?>
      <!-- header nav end-->

      <!-- block post area start-->
      <section class="block-wrapper mt-15">
         <div class="container">
            <div class="row mb-30">
               <div class="col-lg-12">
                  <div class="ts-grid-box">
                        <ol class="ts-breadcrumb">
                           <li>
                              <a href="index.php">
                                 <i class="fa fa-home"></i>
                                 Home
                              </a>
                           </li>
                           <li>
                              <a href="#"><?php echo $rs['hindi_name']; ?></a>
                           </li>
      
                        </ol>
                  </div>
               </div>
            </div>
           
		   <div class="row">
			<select style="display:none;" onChange="changePagination(this.value);" id="pagination-setting">
          <option value="all-links">Display All Page Link</option>
          <option value="prev-next">Display Prev Next Only</option>
        </select>
		
		<div class="col-lg-9" id="pagination-result"></div>
		
               

               <div class="col-lg-3">
                  <div class="right-sidebar">
                     <div class="ts-grid-box widgets social-widget">
                        <h2 class="widget-title">Follow us</h2>
                        <ul class="ts-social-list">
                           <li class="ts-facebook">
                              <a href="#">
                                 <i class="fa fa-facebook"></i>
                                 <b>12.5 k </b>
                                 <span>Likes</span>
                              </a>
   
                           </li>
                           <li class="ts-google-plus">
                              <a href="#">
                                 <i class="fa fa-google-plus"></i>
                              </a>
                              <b>12.5 k </b>
                              <span>Follwers</span>
                           </li>
                           <li class="ts-twitter">
                              <a href="#">
                                 <i class="fa fa-twitter"></i>
                              </a>
                              <b>12.5 k </b>
                              <span>Follwers</span>
                           </li>
                           <li class="ts-pinterest">
                              <a href="#">
                                 <i class="fa fa-pinterest-p"></i>
                              </a>
                              <b>12.5 k </b>
                              <span>Photos</span>
                           </li>
                           <li class="ts-linkedin">
                              <a href="#">
                                 <i class="fa fa-linkedin"></i>
                              </a>
                              <b>12.5 k </b>
                              <span>Follwers</span>
                           </li>
                           <li class="ts-youtube">
                              <a href="#">
                                 <i class="fa fa-youtube"></i>
                              </a>
                              <b>12.5 k </b>
                              <span>Follwers</span>
                           </li>
                        </ul>
                     </div>
                     <!-- widgets end-->
   
                     <div class="widgets widget-banner">
                        <a href="#">
                           <img class="img-fluid" src="images/banner/sidebar-banner4.jpg" alt="">
                        </a>
                     </div>
                     <!-- widgets end-->
                     <div class="post-list-item widgets">
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                           <li role="presentation">
                              <a class="active" href="#home" aria-controls="home" role="tab" data-toggle="tab">
                                 <i class="fa fa-clock-o"></i>
                                 Recent
                              </a>
                           </li>
                           <li role="presentation">
                              <a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">
                                 <i class="fa fa-heart"></i>
                                 Favorites
                              </a>
                           </li>
                        </ul>
   
                        <!-- Tab panes -->
                        <div class="tab-content">
                           <div role="tabpanel" class="tab-pane active ts-grid-box post-tab-list" id="home">
                              <div class="post-content media">
                                 <img class="d-flex sidebar-img" src="images/news/sports/sports2.jpg" alt="">
                                 <div class="media-body">
                                    <span class="post-tag">
                                       <a href="#" class="green-color"> sports</a>
                                    </span>
                                    <h4 class="post-title">
                                       <a href="#">18 month old shoots himself by gun </a>
                                    </h4>
                                 </div>
                              </div>
                              <!--post-content end-->
                              <div class="post-content media ">
                                 <img class="d-flex sidebar-img" src="images/news/tech/tech4.jpg" alt="">
                                 <div class="media-body">
                                    <span class="post-tag">
                                       <a href="#" class="yellow-color"> Technology</a>
                                    </span>
                                    <h4 class="post-title">
                                       <a href="#">Beats did announce something today</a>
                                    </h4>
                                 </div>
                              </div>
                              <!--post-content end-->
                              <div class="post-content media">
                                 <img class="d-flex sidebar-img" src="images/news/sports/sports3.jpg" alt="">
                                 <div class="media-body">
                                    <span class="post-tag">
                                       <a href="#" class="blue-color"> Lifestyle</a>
                                    </span>
                                    <h4 class="post-title">
                                       <a href="#">18 month old shoots himself by gun </a>
                                    </h4>
                                 </div>
                              </div>
                              <!--post-content end-->
                              <div class="post-content media">
                                 <img class="d-flex sidebar-img" src="images/news/fashion/fashion4.jpg" alt="">
                                 <div class="media-body">
                                    <span class="post-tag">
                                       <a href="#" class="pink-color"> Fashion</a>
                                    </span>
                                    <h4 class="post-title">
                                       <a href="#">Beats did announce something today</a>
                                    </h4>
                                 </div>
                              </div>
                              <!--post-content end-->
                              <div class="post-content  media">
                                 <img class="d-flex sidebar-img" src="images/news/travel/travel6.jpg" alt="">
                                 <div class="media-body">
                                    <span class="post-tag">
                                       <a href="#" class="yellow-color"> Travel</a>
                                    </span>
                                    <h4 class="post-title">
                                       <a href="#">18 month old shoots himself by gun </a>
                                    </h4>
                                 </div>
                              </div>
                              <!--post-content end-->
   
                           </div>
                           <!--ts-grid-box end -->
   
                           <div role="tabpanel" class="tab-pane ts-grid-box post-tab-list" id="profile">
                              <div class="post-content media">
                                 <img class="d-flex sidebar-img" src="images/news/sports/sports2.jpg" alt="">
                                 <div class="media-body">
                                    <span class="post-tag">
                                       <a href="#" class="green-color"> sports</a>
                                    </span>
                                    <h4 class="post-title">
                                       <a href="#">Beats did announce something today</a>
                                    </h4>
                                 </div>
                              </div>
                              <!--post-content end-->
                              <div class="post-content media ">
                                 <img class="d-flex sidebar-img" src="images/news/tech/tech4.jpg" alt="">
                                 <div class="media-body">
                                    <span class="post-tag">
                                       <a href="#" class="yellow-color"> Technology</a>
                                    </span>
                                    <h4 class="post-title">
                                       <a href="#">18 month old shoots himself by gun </a>
                                    </h4>
                                 </div>
                              </div>
                              <!--post-content end-->
                              <div class="post-content media">
                                 <img class="d-flex sidebar-img" src="images/news/sports/sports2.jpg" alt="">
                                 <div class="media-body">
                                    <span class="post-tag">
                                       <a href="#" class="blue-color"> Lifestyle</a>
                                    </span>
                                    <h4 class="post-title">
                                       <a href="#">Beats did announce something today</a>
                                    </h4>
                                 </div>
                              </div>
                              <!--post-content end-->
                              <div class="post-content media">
                                 <img class="d-flex sidebar-img" src="images/news/fashion/fashion4.jpg" alt="">
                                 <div class="media-body">
                                    <span class="post-tag">
                                       <a href="#" class="pink-color"> Fashion</a>
                                    </span>
                                    <h4 class="post-title">
                                       <a href="#">18 month old shoots himself by gun </a>
                                    </h4>
                                 </div>
                              </div>
                              <!--post-content end-->
                              <div class="post-content  media">
                                 <img class="d-flex sidebar-img" src="images/news/travel/travel6.jpg" alt="">
                                 <div class="media-body">
                                    <span class="post-tag">
                                       <a href="#" class="yellow-color"> Travel</a>
                                    </span>
                                    <h4 class="post-title">
                                       <a href="#">Beats did announce something today</a>
                                    </h4>
                                 </div>
                              </div>
                              <!--post-content end-->
                           </div>
                           <!--ts-grid-box end -->
                        </div>
                        <!-- tab content end-->
                     </div>
                     <!-- widgets end-->
                     <div class="ts-grid-box widgets category-list-item">
                        <h2 class="widget-title">Categories</h2>
                        <ul class="category-list">
                           <li>
                              <a href="#">Travel
                                 <span class="ts-orange-bg">10</span>
                              </a>
                           </li>
                           <li>
                              <a href="#">Sports
                                 <span class="ts-green-bg">25</span>
                              </a>
                           </li>
                           <li>
                              <a href="#">Travel
                                 <span class="ts-orange-bg">10</span>
                              </a>
                           </li>
                           <li>
                              <a href="#">Fashion
                                 <span class="ts-pink-bg">10</span>
                              </a>
                           </li>
                           <li>
                              <a href="#">Technology
                                 <span class="ts-blue-bg">10</span>
                              </a>
                           </li>
                           <li>
                              <a href="#">Music
                                 <span class="ts-pink-bg">10</span>
                              </a>
                           </li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
            <!-- row end-->
         </div>
         <!-- container end-->
      </section>
      <!-- block area end-->

      <!-- newslater start -->
      <section class="ts-newslatter">
         <div class="container">
            <div class="row">
               <div class="col-lg-6">
                  <div class="ts-newslatter-content">
                     <h2>
                        Sign up for the Newsletter
                     </h2>
                     <p>
                        Join our newsletter and get updates in your inbox. We won’t spam you and we respect your
                        privacy.
                     </p>
                  </div>
               </div>
               <!-- col end-->

               <div class="col-lg-6 align-self-center">
                  <div class="newsletter-form">
                     <form action="#" method="post" class="media align-items-end">
                        <div class="email-form-group media-body">
                           <i class="fa fa-paper-plane" aria-hidden="true"></i>
                           <input type="email" name="email" id="newsletter-form-email" class="form-control" placeholder="Enter Your Email"
                              autocomplete="off" required>
                        </div>
                        <div class="d-flex ts-submit-btn">
                           <button class="btn btn-primary">Subscribe</button>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <!-- newslater end -->
      
      <!-- footer social list start-->
       <?php include"footer.php"; ?>
      <!-- footer end -->


   </div>


   <!-- javaScript Files
	=============================================================================-->
<?php include"js.php"; ?>
        <script>
$(document).on('click', '.reset', function(){  
        $('#SearchForm')[0].reset();
        getresult("desp_profile.php");
        });
getresult("desp_profile.php");
function getresult(url) {
	$.ajax({
		url: url,
		type: "GET",
		data:  {rowcount:$("#rowcount").val(),
                "pagination_setting":$("#pagination-setting").val(),
                "actions":("<?php echo $rs['id']; ?>")},
		beforeSend: function(){$("#overlay").show();},
		success: function(data){
		$("#pagination-result").html(data);
		$("#overlay").hide();
		},
		error: function() 
		{} 	        
   });
} 
function changePagination(option) {
	if(option!= "") {
		getresult("desp_profile.php");
	}
}
</script>
<script>
getresult("desp_profile.php");
</script>
</body>
</html>