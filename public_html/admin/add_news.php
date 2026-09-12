<?php

    include"config.php";
 
	if (!isset($_SESSION['aemail'])) {
		$_SESSION['msg'] = "You must log in first";
		header('location: ../manage.php');
	}

	if (isset($_GET['logout'])) {
		session_destroy();
		unset($_SESSION['aemail']);
		header("location: ../manage.php");
	}

 if(!isset($_SESSION['aemail']))
 {
  echo ("<script language='javascript'>
                   window.location.href='logout.php';
                        </script>");
 }

$productsession=$_SESSION['aemail'];

$res=mysqli_query($con,"SELECT * FROM admin WHERE aemail='$productsession'");

$userRow=mysqli_fetch_array($res,MYSQLI_ASSOC);
if (!function_exists('nm_admin_team_id')) {
	require_once __DIR__ . '/admin_helpers.php';
}
$nmLinkedTeamId = function_exists('nm_admin_team_id') ? (int) nm_admin_team_id($con) : 0;


if(isset($_POST['add']))
                    {
                            // Optional fields only exist for Video type (ajaxVid.php) — never assume they are posted
                            $post = function($key, $default = '') use ($con) {
                                return mysqli_real_escape_string($con, isset($_POST[$key]) ? $_POST[$key] : $default);
                            };

                            $title = $post('title');
                            $latest_news = (isset($_POST['latest_news']) && $_POST['latest_news'] === 'Yes') ? 'Yes' : 'No';
                            $latest_news = mysqli_real_escape_string($con, $latest_news);
                            $descriptionRaw = isset($_POST['description']) ? (string) $_POST['description'] : '';
                            if (function_exists('nm_clean_description_html')) {
                                $descriptionRaw = nm_clean_description_html($descriptionRaw);
                            }
                            $description = mysqli_real_escape_string($con, $descriptionRaw);
                            $newsurl = $post('newsurl');
                            $metat = $post('metat');
                            $metad = $post('metad');
                            $category = $post('category');
                            $top_side_bar = $post('top_side_bar', 'No');
                            $slider = $post('slider', 'No');
                            $slider_priority = $post('slider_priority', '0');
                            $latest_priority = $post('latest_priority', '0');
                            $show_home = $post('show_home', 'No');
                            $short_description = $post('short_description');
                            $date = date("d-m-Y");
                            $time = date('H:i');
                            $newstype = $post('newstype', 'Content');
                            $img_source = $post('img_source');
                            $img_abt = $post('img_abt');
                            $v_link = $post('videolink');
                            $team_id = $post('team_id', '0');
                            if (($team_id === '' || $team_id === '0') && $nmLinkedTeamId > 0) {
                                $team_id = (string) $nmLinkedTeamId;
                            }
                            $hashtags = $post('hashtags');
                            $pub_date_time = isset($_POST['pub_date_time']) ? trim((string) $_POST['pub_date_time']) : '';
                            $sched = nm_resolve_publish_schedule($_POST, $pub_date_time);
                            if ($sched['error']) {
                                array_push($errors, $sched['error']);
                            }
                            $status = $sched['status'];
                            $pub_date_time = mysqli_real_escape_string($con, $sched['pub_date_time']);
                            $name = (isset($_FILES['video_file']['name']) ? $_FILES['video_file']['name'] : '');
                            $video_id = '';
                            $post_image = '';

                            // Lean form: fill SEO/summary from title when not posted
                            if ($short_description === '' && $title !== '') {
                                $short_description = $title;
                            }
                            if ($metat === '' && $title !== '') {
                                $metat = $title;
                            }
                            if ($metad === '' && $title !== '') {
                                $metad = $title;
                            }

                            if (empty($title)) { array_push($errors, "Kindly fill news title"); }
                            if (empty($description) || trim(strip_tags($description)) === '') {
                                array_push($errors, "Kindly fill the full article Description");
                            }
                            if (empty($short_description)) { array_push($errors, "Kindly fill Short Description"); }
                            if (empty($newsurl)) { array_push($errors, "Kindly fill news url"); }
                            if (empty($metat)) { array_push($errors, "Kindly fill meta title"); }
                            if (empty($category) || $category === '0') { array_push($errors, "Kindly fill news category"); }
                            if (empty($_FILES['image']['tmp_name'])) { array_push($errors, "Kindly add image"); }

                            /* Linkname */
                            $replace = array(" ",",",".","'","&","-","_",":","(",")","+",";","#","!","*","{","}","[","]","?","/","\"","|","@","%","$");
                            $linkStr_Replace = str_replace($replace, "-", trim($newsurl));
                            $linkStr_Replace = str_replace(array("----","---","--"), "-", $linkStr_Replace);
                            $linkname = $linkStr_Replace;

                            $folderStr_Replace = str_replace($replace, "-", trim($category));
                            $folderStr_Replace = str_replace(array("----","---","--"), "-", $folderStr_Replace);
                            $foldername = $folderStr_Replace . "/";
                            $link = $foldername . $linkname . "/";

                            if (!empty($linkname)) {
                                $res_u = mysqli_query($con, "SELECT newsid FROM `news` WHERE `newsurl`='$linkname' LIMIT 1");
                                if ($res_u && mysqli_num_rows($res_u) > 0) {
                                    array_push($errors, "Sorry... News URL already Exixts");
                                }
                            }

                        if (count($errors) == 0) {

                            if (!empty($v_link)) {
                                $video_parts = explode("?v=", $v_link);
                                if (empty($video_parts[1])) {
                                    $video_parts = explode("/v/", $v_link);
                                }
                                if (empty($video_parts[1])) {
                                    $video_parts = explode("youtu.be/", $v_link);
                                }
                                if (!empty($video_parts[1])) {
                                    $video_parts = explode("&", $video_parts[1]);
                                    $video_id = $video_parts[0];
                                }
                            }

                            if (!empty($name) && !empty($_FILES['video_file']['tmp_name'])) {
                                $target_dir = __DIR__ . "/../videos/";
                                if (!is_dir($target_dir)) {
                                    @mkdir($target_dir, 0755, true);
                                }
                                move_uploaded_file($_FILES['video_file']['tmp_name'], $target_dir . $name);
                            }

                            $tmp_file = $_FILES['image']['tmp_name'];
                            $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                            $rand = md5(uniqid().rand());
                            $post_image = $rand.".".$ext;
                            $news_img_dir = __DIR__ . "/../images/news/";
                            if (!is_dir($news_img_dir)) {
                                @mkdir($news_img_dir, 0755, true);
                            }
                            move_uploaded_file($tmp_file, $news_img_dir . $post_image);

                            if ($slider_priority !== '' && $slider_priority !== '0') {
                                $pri = mysqli_query($con,"SELECT `slider_priority`,`newsid` FROM `news` WHERE `slider`='Yes' AND `slider_priority` >= '$slider_priority' ORDER BY `slider_priority` ASC");
                                while($pr = mysqli_fetch_array($pri)){
                                    $new_slider_priority = $pr['slider_priority']+1;
                                    mysqli_query($con,"UPDATE `news` SET `slider_priority`='$new_slider_priority' WHERE `newsid`='".$pr['newsid']."'");
                                }
                            }

                            if ($latest_priority !== '' && $latest_priority !== '0') {
                                mysqli_query($con, "UPDATE `news` SET `latest_priority`='0' WHERE `latest_priority`='$latest_priority'");
                            }

                            if(empty($slider_priority)){ $slider_priority = '0'; }
                            if(empty($latest_priority)){ $latest_priority = '0'; }

                            $qry="insert into `news` (`newsurl`, `folder`, `seolink`, `metat`, `metad`, `title`, `description`, `short_description`, `image`, `img_abt`, `img_source`, `newstype`, `latest_news`, `category`, `top_side_bar`, `slider`, `date`, `time` , `videoid`, `video_file`, `show_home`, `status`, `slider_priority`, `latest_priority`, `team_id`,`hashtags`, `pub_date_time`) values('$linkname','$foldername','$link','$metat','$metad','$title','$description','$short_description','$post_image','$img_abt','$img_source','$newstype','$latest_news','$category','$top_side_bar','$slider','$date','$time','$video_id','$name','$show_home','$status','$slider_priority','$latest_priority','$team_id','$hashtags','$pub_date_time')";

                             $ex=mysqli_query($con,$qry);
                             $lastInsertId = mysqli_insert_id($con);
                              if($ex>0) {

                            // Mirror Home Category into news_cat so listings/APIs that join news_cat still work
                            if (!empty($category) && $category !== '0') {
                              mysqli_query($con, "INSERT INTO `news_cat`(`category`, `news_id`) VALUES('$category','$lastInsertId')");
                            }
                                   echo("<script language='javascript'>
                                  window.alert('" . ($status === 'Scheduled' ? 'Scheduled successfully' : 'Published successfully') . "')
                                  window.location.href='news.php';
                                  </script>");
                              }
                              else{ array_push($errors, "Sorry, there was an error: " . mysqli_error($con)); }
                             }

                            }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../include/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="../include/css/style.css">
<link rel="stylesheet" href="../include/css/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.5/css/responsive.dataTables.min.css">
<script src="../include/js/jquery.min.js"></script>
</head>
<body>
<div id="overlay"><div><img src="img/loading.gif" width="64px" height="64px"/></div></div>
    <div class="wrapper">
        <!-- Sidebar  -->
        <?php include"sidebar.php"; ?>

        <!-- Page Content  -->
<div id="content">
            <?php include"header.php"; ?>
            
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Add News</li>
            </ol>
<div class="container-fluid page-content">
                            <?php include('errors.php'); ?>
                            <?php include('sucsess.php'); ?>
            <form id="SubmitForm" class="nm-news-form" method="post" enctype="multipart/form-data">

              <section class="nm-form-section">
                <h2 class="nm-form-section__title">Story</h2>
                <div class="nm-form-grid">
                  <div class="nm-form-field nm-form-field--full">
                    <label class="control-label" for="nm-title">Title</label>
                    <input class="form-control" id="nm-title" type="text" name="title" value="<?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['title']); } ?>" required>
                  </div>
                  <div class="nm-form-field nm-form-field--full">
                    <label class="control-label" for="nm-newsurl">News URL</label>
                    <div class="nm-url-row">
                      <input class="form-control" id="nm-newsurl" type="text" name="newsurl" value="<?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['newsurl']); } ?>" required autocomplete="off">
                      <button type="button" class="btn btn-outline-secondary btn-sm" id="nm-url-resync" title="Fill URL from title again">From title</button>
                    </div>
                    <p class="nm-form-hint">Auto-converts Hindi/English title to an SEO slug. Edit anytime. Path: <code>/news/{slug}</code></p>
                  </div>
                  <div class="nm-form-field nm-form-field--full">
                    <label class="control-label">Image (850×565)</label>
                    <input class="form-control" type="file" name="image" accept="image/*" required>
                  </div>
                </div>
              </section>

              <section class="nm-form-section">
                <h2 class="nm-form-section__title">Author (byline)</h2>
                <div class="nm-form-grid">
                  <div class="nm-form-field nm-form-field--full">
                    <label class="control-label" for="team_id">Shows as By Name / The Naradmuni</label>
                    <select class="custom-select" id="team_id" name="team_id" required>
                      <option value="0">select author</option>
                      <?php
                      $tq = $con->query("SELECT `t_id`,`name` FROM `team` ORDER BY `name` ASC");
                      $selDefault = $nmLinkedTeamId > 0 ? $nmLinkedTeamId : 0;
                      if ($tq) {
                        while ($tr = $tq->fetch_assoc()) {
                          $sel = ((int) $tr['t_id'] === (int) $selDefault) ? ' selected' : '';
                          echo '<option value="' . (int) $tr['t_id'] . '"' . $sel . '>' . htmlspecialchars((string) $tr['name']) . '</option>';
                        }
                      }
                      ?>
                    </select>
                    <p class="nm-form-hint">Defaults to your linked profile when set. Change anytime.</p>
                  </div>
                </div>
              </section>

              <section class="nm-form-section">
                <h2 class="nm-form-section__title">Category</h2>
                <div class="nm-form-grid">
                  <div class="nm-form-field nm-form-field--full">
                    <label class="control-label" for="category">Home Category</label>
                    <select class="custom-select" id="category" name="category" required>
                      <option value="0">select</option>
                      <?php
                      $query = $con->query("SELECT id, hindi_name, maincat FROM `categories` ORDER BY id ASC");
                      if (!$query) {
                          $query = $con->query("SELECT id, hindi_name FROM `categories` ORDER BY id ASC");
                      }
                      if (!$query) {
                          $query = $con->query("SELECT id, maincat FROM `categories` ORDER BY id ASC");
                      }
                      $rowCount = $query ? $query->num_rows : 0;
                      if($rowCount > 0){
                          while($row = $query->fetch_assoc()){
                              $label = !empty($row['hindi_name']) ? $row['hindi_name'] : (!empty($row['maincat']) ? $row['maincat'] : ('Cat #'.$row['id']));
                              echo '<option value="'.(int)$row['id'].'">'.htmlspecialchars((string)$label).'</option>';
                          }
                      }else{
                          echo '<option value="0">no data available</option>';
                      }
                      ?>
                    </select>
                    <p class="nm-form-hint">Pick the real place/topic (e.g. Indore, Bhopal, नीमच). Required.</p>
                  </div>
                  <div class="nm-form-field nm-form-field--full">
                    <label class="checkbox-inline" style="font-weight:600;">
                      <input type="checkbox" name="latest_news" value="Yes" <?php echo (isset($_POST['latest_news']) && $_POST['latest_news'] === 'Yes') ? 'checked' : ''; ?>>
                      Breaking news — show in homepage top list (latest 5)
                    </label>
                    <p class="nm-form-hint">Add-on only. Story still belongs to the category above.</p>
                  </div>
                </div>
              </section>

              <section class="nm-form-section">
                <h2 class="nm-form-section__title">Full article</h2>
                <div class="nm-form-field nm-form-field--full">
                  <label class="control-label" for="description">Description</label>
                  <textarea class="ckeditor form-control" id="description" name="description"><?php if(isset($_POST['add'])){ echo htmlspecialchars($_POST['description']); } ?></textarea>
                </div>
              </section>

              <section class="nm-form-section">
                <h2 class="nm-form-section__title">Publish</h2>
                <div class="nm-form-grid">
                  <div class="nm-form-field nm-form-field--full">
                    <label class="control-label">When to publish</label>
                    <div style="display:flex;flex-wrap:wrap;gap:16px;align-items:center;margin-top:6px;">
                      <label class="checkbox-inline" style="font-weight:600;margin:0;">
                        <input type="radio" name="publish_mode" value="now" checked> Publish now
                      </label>
                      <label class="checkbox-inline" style="font-weight:600;margin:0;">
                        <input type="radio" name="publish_mode" value="schedule" id="nm-publish-schedule"> Schedule for later
                      </label>
                    </div>
                  </div>
                  <div class="nm-form-field nm-form-field--full" id="nm-schedule-wrap" style="display:none;">
                    <label class="control-label" for="pub_date_time">Go live at (IST)</label>
                    <input class="form-control" type="datetime-local" id="pub_date_time" name="pub_date_time" value="">
                    <p class="nm-form-hint">Story stays hidden until this time, then goes live automatically.</p>
                  </div>
                </div>
              </section>

              <div class="nm-form-actions">
                <button type="submit" name="add" class="btn btn-info" id="nm-publish-btn">Publish</button>
                <a class="btn btn-outline-secondary" href="news.php">Cancel</a>
              </div>
        </form>
</div>
</div>			

    

</div>
<?php include"footer.php"; ?>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script type="text/javascript">
<?php echo nm_ckeditor_js('description'); ?>
document.getElementById('SubmitForm').addEventListener('submit', function () {
  for (var name in CKEDITOR.instances) {
    if (CKEDITOR.instances.hasOwnProperty(name)) {
      CKEDITOR.instances[name].updateElement();
    }
  }
});
(function () {
  var wrap = document.getElementById('nm-schedule-wrap');
  var btn = document.getElementById('nm-publish-btn');
  var input = document.getElementById('pub_date_time');
  function sync() {
    var schedule = document.getElementById('nm-publish-schedule').checked;
    wrap.style.display = schedule ? 'block' : 'none';
    if (btn) btn.textContent = schedule ? 'Schedule' : 'Publish';
    if (input) input.required = schedule;
  }
  var radios = document.querySelectorAll('input[name="publish_mode"]');
  for (var i = 0; i < radios.length; i++) {
    radios[i].addEventListener('change', sync);
  }
  sync();
})();
</script>
<script type="text/javascript">
        $(document).ready(function () {
            $('#sidebar').toggleClass('');
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="../include/js/bootstrap.min.js"></script>
  
  <!-- Font Awesome JS -->
    <script src="js/all.js"></script>
<script src="../include/js/jquery-ui.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.5/js/dataTables.responsive.min.js"></script>
    <script>
    $("#newstype").on('change', function(){
			$.ajax({
						type: "POST",
						url: "ajaxVid.php",
						data:{newstype:$("#newstype").val()},
						beforeSend:function(){
						    $('#vid').html("<p>Loading....</p>");
                          },
						success: function(data){
							$('#vid').html(data);
						}
			});
	});
    
    // #videotype is injected later — must use delegated binding
    $(document).on('change', '#videotype', function(){
			$.ajax({
						type: "POST",
						url: "ajaxVid.php",
						data:{videotype:$("#videotype").val()},
						beforeSend:function(){
						    $('#vid2').html("<p>Loading....</p>");
                          },
						success: function(data){
							$('#vid2').html(data);
						}
			});
	});
</script>
<script>
(function () {
  var titleEl = document.getElementById("nm-title");
  var urlEl = document.getElementById("nm-newsurl");
  var resyncBtn = document.getElementById("nm-url-resync");
  if (!titleEl || !urlEl) return;

  var urlManual = false;

  var VOWELS = {
    "अ": "a", "आ": "aa", "इ": "i", "ई": "ee", "उ": "u", "ऊ": "oo",
    "ए": "e", "ऐ": "ai", "ओ": "o", "औ": "au", "ऋ": "ri"
  };
  var MATRAS = {
    "ा": "aa", "ि": "i", "ी": "ee", "ु": "u", "ू": "oo",
    "े": "e", "ै": "ai", "ो": "o", "ौ": "au", "ृ": "ri",
    "ं": "n", "ँ": "n", "ः": "h"
  };
  var CONSONANTS = {
    "क": "k", "ख": "kh", "ग": "g", "घ": "gh", "ङ": "ng",
    "च": "ch", "छ": "chh", "ज": "j", "झ": "jh", "ञ": "ny",
    "ट": "t", "ठ": "th", "ड": "d", "ढ": "dh", "ण": "n",
    "त": "t", "थ": "th", "द": "d", "ध": "dh", "न": "n",
    "प": "p", "फ": "ph", "ब": "b", "भ": "bh", "म": "m",
    "य": "y", "र": "r", "ल": "l", "व": "v", "श": "sh",
    "ष": "sh", "स": "s", "ह": "h", "ळ": "l", "ऱ": "r",
    "क़": "q", "ख़": "kh", "ग़": "g", "ज़": "z", "ड़": "d",
    "ढ़": "dh", "फ़": "f", "य़": "y"
  };
  var DIGITS = {
    "०": "0", "१": "1", "२": "2", "३": "3", "४": "4",
    "५": "5", "६": "6", "७": "7", "८": "8", "९": "9"
  };
  var VIRAMA = "्";

  function transliterateDevanagari(text) {
    var out = "";
    var i = 0;
    var s = String(text || "");
    while (i < s.length) {
      var ch = s.charAt(i);
      var next = s.charAt(i + 1);

      if (DIGITS[ch]) {
        out += DIGITS[ch];
        i += 1;
        continue;
      }
      if (VOWELS[ch]) {
        out += VOWELS[ch];
        i += 1;
        continue;
      }
      if (CONSONANTS[ch]) {
        out += CONSONANTS[ch];
        i += 1;
        if (next === VIRAMA) {
          i += 1; // conjunct: no inherent vowel
        } else if (MATRAS[next]) {
          out += MATRAS[next];
          i += 1;
        } else {
          out += "a"; // inherent अ
        }
        continue;
      }
      if (MATRAS[ch]) {
        out += MATRAS[ch];
        i += 1;
        continue;
      }
      if (ch === VIRAMA) {
        i += 1;
        continue;
      }
      out += ch;
      i += 1;
    }
    return out;
  }

  function slugify(text) {
    var s = transliterateDevanagari(String(text || ""));
    s = s.toLowerCase().trim();
    s = s.replace(/[,\.'&_\-:()+\";#!*{}\[\]?\/\\|@%\s$]+/g, "-");
    s = s.replace(/[^a-z0-9-]+/g, "-");
    s = s.replace(/-+/g, "-").replace(/^-+|-+$/g, "");
    // Keep SEO slugs usable (avoid ultra-long URLs)
    if (s.length > 120) s = s.slice(0, 120).replace(/-+$/g, "");
    return s;
  }

  function fillFromTitle() {
    var slug = slugify(titleEl.value);
    if (slug) urlEl.value = slug;
  }

  titleEl.addEventListener("input", function () {
    if (!urlManual) fillFromTitle();
  });

  urlEl.addEventListener("input", function () {
    urlManual = true;
  });

  if (resyncBtn) {
    resyncBtn.addEventListener("click", function () {
      urlManual = false;
      fillFromTitle();
      urlEl.focus();
    });
  }

  if (!urlEl.value && titleEl.value) fillFromTitle();
})();
</script>
</body>
</html>