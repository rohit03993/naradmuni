<style>
    .logo{width: 100%;
    background: #fff;
    height: 79px;}
</style>
<?php include"menu.php"; ?>  
	<header class="header-default">
		<div class="container">
			<div class="row">
				<div class="col-lg-3 pr-0">
					<div class="logo">
						<a href="<?php echo $urlroot; ?>index.php">
							<img src="<?php echo $urlroot; ?>images/logo/Logo @2x.png" alt="" style="max-width: 85%;">
						</a>
					</div>

				</div>
				<!-- logo end-->
				<div class="col-lg-9 header-nav-item">
					<!--nav top end-->
					<nav class="navigation ts-main-menu ts-menu-sticky navigation-landscape">
						<div class="nav-header">
                            <div class="nav-toggle"></div>
                            
							<a class="nav-brand mobile-logo visible-xs" href="<?php echo $urlroot; ?>index.php">
								<img src="<?php echo $urlroot; ?>images/logo/MobileLogo.png" alt="The Narsdmuni" style="width: 150px;">
							</a>
							
						</div>
						<!--nav brand end-->

						<div class="nav-menus-wrapper clearfix" style="background: #fff;">
							<!--nav right menu start-->
							<ul class="right-menu align-to-right">
								<li>
									<a target="_blank" href="https://api.whatsapp.com/send?phone=+917415716541&text=व्हाट्सप्प पर खबरें भेजें" title="व्हाट्सप्प पर खबरें पाएं ">
										<i class="fa fa-whatsapp"></i>
									</a>
								</li>
								<li class="header-search">
									<div class="nav-search">
										<div class="nav-search-button">
											<i class="icon icon-search"></i>
										</div>
										<form>
											<span class="nav-search-close-button" tabindex="0">✕</span>
											<div class="nav-search-inner">
												<input type="search" name="search" placeholder="Type and hit ENTER">
											</div>
										</form>
									</div>
								</li>
							</ul>
							<!--nav right menu end-->

							<!-- nav menu start-->
							<ul class="nav-menu">
								<li class="active">
								 <a href="<?php echo $urlroot; ?>index.php"><i class="fa fa-home" aria-hidden="true"></i></a>
								</li>
								<?= show_menu() ?>
                                <li class="feed-link">
									<a href="<?php echo $urlroot; ?>aggregator_more.php">  न्यूज़ फीड </a>
								</li>
							</ul>
							<!--nav menu end-->
						</div>
					</nav>
					<!-- nav end-->
				</div>
			</div>
		</div>
	</header>