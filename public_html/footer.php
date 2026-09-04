<section class="ts-footer-social-list" >
		<div class="container">
			<div class="row">
				<div class="col-lg-6">
					<div class="footer-logo">
						<a href="<?php echo $urlroot; ?>">
							<img src="<?php echo $urlroot; ?>images/logo/Logo%20@1x.png" alt="">
						</a>
					</div>
					<!-- footer logo end-->
				</div>
				<!-- col end-->

				<div class="col-lg-6 align-self-center">
					<ul class="footer-social">
						<li class="ts-facebook">
							<a href="https://www.facebook.com/The-Naradmuni-100115665387257" target="_blank">
								<i class="fa fa-facebook"></i>
								<span>Facebook</span>
							</a>
						</li>
						 
						<li class="ts-twitter">
							<a href="https://twitter.com/the_naradmuni" target="_blank"> 
								<i class="fa fa-twitter"></i>
								<span>Twitter</span>
							</a>
						</li>
						<li class="ts-whatsapp">
							<a href="https://api.whatsapp.com/send?phone=+917415716541&text=व्हाट्सप्प पर खबरें भेजें" target="_blank" title="व्हाट्सप्प पर खबरें पाएं ">
								<i class="fa fa-whatsapp"></i>
								<span>Whatsapp</span>
							</a>
						</li>
					 
					</ul>
				</div></div>
				<!-- col end-->
	<div class="row" style="padding-top:8px;">
				<div class="col-lg-6">
					<div class="ts-newslatter-content">
						<h2>
							Download The Naradmuni App.
						</h2>
						 
					</div>
				</div>
				<!-- col end-->

				<div class="col-lg-6 align-self-right">
			        <div class="right-sidebar">
                       
                	<div class=" " >
							 <ul class="ts-social-list"  >
								<li class="">
									<a href="#">
										  <img class="img-fluid" src="<?php echo $urlroot; ?>images/icon/iapp.png" alt="">
									</a>
									 
								</li>
								 &nbsp; &nbsp; &nbsp; &nbsp;
								<li class="">
									<a target="_blank" href="https://play.google.com/store/apps/details?id=com.thenaradmuni.news">
										  <img class="img-fluid" src="<?php echo $urlroot; ?>images/icon/playstore.png" alt="">
									</a>
									 
								</li>
								 
							</ul>
						</div>
					  </div>
               	</div>
			</div>
		
			
		</div>
	</section>
	<!-- footer social list end-->
 
	<!-- footer start -->
	<footer class="ts-footer">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<div class="footer-menu text-center">
						<ul>
                            <?php
                                $page = mysqli_query($con,"SELECT `page`, `page_url` FROM `pages` ORDER BY `p_id` ASC");
                                 while ($pg = mysqli_fetch_array($page)){
                                     echo '<li><a href="'.$urlroot.'page/'.$pg['page_url'].'">'.$pg['page'].'</a></li>';
                                 }
                            ?>
							<li>
								<a href="#">Our Team </a>
							</li>
						</ul>
					</div>
					<div class="copyright-text text-center">
						<p>&copy;
						<script>
						  var CurrentYear = new Date().getFullYear()
						  document.write(CurrentYear)
						</script> ,
						 
						 Narad Muni. All rights reserved</p>
					</div>
				</div><!-- col end -->
			</div><!-- row end -->
			<div id="back-to-top" class="back-to-top">
				<button class="btn btn-primary" title="Back to Top">
					<i class="fa fa-angle-up"></i>
				</button>
			</div><!-- Back to top end -->
		</div><!-- Container end-->
	</footer>
	<script>
/* When the user clicks on the button, 
toggle between hiding and showing the dropdown content */
function myFunction() {
	
	 $("#myDropdown").addClass("show_dropdown");
	if($("#myDropdown").hasClass("show_dropdown")){
  $("#myDropdown").addClass("show_dropdown");
	}else
	{
	//	 $("#myDropdown").removeClass("show_dropdown");
	}
}


</script>