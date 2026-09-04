<div class="right-sidebar">
 <div class="ts-grid-box widgets social-widget">
    <h2 class="widget-title text-center text-bold" >राशिफल</h2>
    <ul class="ts-social-list">
      <?php 			
        $rashi=mysqli_query($con,"SELECT * FROM rashifal  ORDER BY r_id ASC LIMIT 12");
        $k=1;		
        while ($rsf=mysqli_fetch_array($rashi)) {
        $r_id=$rsf['r_id'];	
        $title=$rsf['title'];	  
        $image=$rsf['image']; 
?>
						<li class="ts-facebook">
                              <a href="<?php echo $urlroot.'rashifal/'.$rsf['r_id'];?>">
                                <img class="img-fluid" src="<?php echo $urlroot; ?>images/rashifal/<?php  echo $rsf["image"]; ?>" alt="">
                                 <b><?php echo $rsf["title"]; ?></b>
                                  
                              </a>
   
                           </li>
						   
						   
<?php $k++; }?>
                           </ul>
                     </div>
                     <!-- widgets end-->
   
                     <div class="widgets widget-banner">
                         <?php
                            $ads = mysqli_query($con,"SELECT `link`,`image`,`title` FROM `ads` WHERE `position`='3' ORDER BY `ad_id` DESC LIMIT 1");
                            $ad = mysqli_fetch_array($ads);
                        ?>
                        <a target="_blank" href="<?php echo $ad['link']; ?>">
                           <img class="img-fluid" src="<?php echo $urlroot.'ads/'.$ad['image']; ?>" alt="<?php echo $ad['title']; ?>">
                        </a>
                     </div>
                     <!-- widgets end-->
                     
                     <!-- widgets end-->
                	<div class="ts-grid-box widgets">
							<h2 class="ts-title">Follow us</h2>
							<ul class="ts-social-list">
								<li class="ts-facebook">
									<a href="https://www.facebook.com/The-Naradmuni-100115665387257" target="_blank">
										<i class="fa fa-facebook"></i>
									</a>
								</li>
								<!--<li class="ts-google-plus">
									<a href="#">
										<i class="fa fa-google-plus"></i>
									</a>
									<b>12.5 k </b>
									<span>Follwers</span>
								</li> -->
								<li class="ts-twitter">
									<a href="https://twitter.com/the_naradmuni" target="_blank">
										<i class="fa fa-twitter"></i>
									</a>
								</li>
								<!--<li class="ts-pinterest">
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
								</li> -->
								<li class="ts-youtube">
									<a target="_blank" href="https://www.youtube.com/channel/UCFk1xW3Qt_THywQF-rtO9LQ">
										<i class="fa fa-youtube"></i>
									</a>
								</li>
							</ul>
						</div>
					  </div>
               