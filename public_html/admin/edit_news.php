<?php
include "config.php";

if (!isset($_SESSION['aemail'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: ../manage.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['aemail']);
    header("location: ../manage.php");
    exit;
}

@ini_set('memory_limit', '256M');

$productsession = $_SESSION['aemail'];
$res = mysqli_query($con, "SELECT * FROM admin WHERE aemail='" . mysqli_real_escape_string($con, $productsession) . "'");
$userRow = $res ? mysqli_fetch_array($res, MYSQLI_ASSOC) : null;

/** Escape for HTML attributes / textarea (keeps </textarea> in body from breaking the form). */
function nm_h($v)
{
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function nm_cat_label(array $row)
{
    if (!empty($row['hindi_name'])) {
        return (string) $row['hindi_name'];
    }
    if (!empty($row['maincat'])) {
        return (string) $row['maincat'];
    }
    return 'Cat #' . (isset($row['id']) ? $row['id'] : '');
}

// Accept eid (correct) or id (common mistake from bookmarks)
$srid = isset($_GET['eid']) ? $_GET['eid'] : (isset($_GET['id']) ? $_GET['id'] : '');
$srid = preg_replace('/\D+/', '', (string) $srid);

if ($srid === '') {
    echo "<script>alert('Missing news id'); window.location.href='news.php';</script>";
    exit;
}

$qry = "SELECT * FROM `news` WHERE newsid='" . mysqli_real_escape_string($con, $srid) . "' LIMIT 1";
$ex = mysqli_query($con, $qry);
$rs = ($ex instanceof mysqli_result) ? mysqli_fetch_assoc($ex) : null;

if (!$rs) {
    echo "<script>alert('News not found'); window.location.href='news.php';</script>";
    exit;
}

$au = array('name' => '');
$teamId = isset($rs['team_id']) ? (string) $rs['team_id'] : '0';
$auth = mysqli_query($con, "SELECT `name` FROM `team` WHERE `t_id`='" . mysqli_real_escape_string($con, $teamId) . "' LIMIT 1");
if ($auth instanceof mysqli_result) {
    $auRow = mysqli_fetch_assoc($auth);
    if (is_array($auRow)) {
        $au = $auRow;
    }
}

$catLabel = '';
$catIdHome = isset($rs['category']) ? (string) $rs['category'] : '0';
$q33 = mysqli_query($con, "SELECT id, hindi_name, maincat FROM `categories` WHERE `id`='" . mysqli_real_escape_string($con, $catIdHome) . "' LIMIT 1");
if (!($q33 instanceof mysqli_result)) {
    $q33 = mysqli_query($con, "SELECT id, hindi_name FROM `categories` WHERE `id`='" . mysqli_real_escape_string($con, $catIdHome) . "' LIMIT 1");
}
if (!($q33 instanceof mysqli_result)) {
    $q33 = mysqli_query($con, "SELECT id, maincat FROM `categories` WHERE `id`='" . mysqli_real_escape_string($con, $catIdHome) . "' LIMIT 1");
}
if ($q33 instanceof mysqli_result) {
    $catRow = mysqli_fetch_assoc($q33);
    if (is_array($catRow)) {
        $catLabel = nm_cat_label($catRow);
    }
}

if (isset($_POST['update'])) {
    // Same pattern as add_news.php — optional Video fields may be absent
    $post = function ($key, $default = '') use ($con) {
        return mysqli_real_escape_string($con, isset($_POST[$key]) ? $_POST[$key] : $default);
    };

    $title = $post('title');
    $latest_news = (isset($_POST['latest_news']) && $_POST['latest_news'] === 'Yes') ? 'Yes' : 'No';
    $latest_news = mysqli_real_escape_string($con, $latest_news);
    // Raw description before escape — never wipe a real body with an empty CKEditor shell
    $descriptionRaw = isset($_POST['description']) ? (string) $_POST['description'] : '';
    $existingDesc = isset($rs['description']) ? (string) $rs['description'] : '';
    $postedHasText = trim(strip_tags(str_replace('&nbsp;', ' ', $descriptionRaw))) !== '';
    $existingHasText = trim(strip_tags(str_replace('&nbsp;', ' ', $existingDesc))) !== '';
    if (!$postedHasText && $existingHasText) {
        $descriptionRaw = $existingDesc;
    }
    if (function_exists('nm_clean_description_html')) {
        $descriptionRaw = nm_clean_description_html($descriptionRaw);
    }
    $description = mysqli_real_escape_string($con, $descriptionRaw);
    $newsurl = $post('newsurl');
    $metat = $post('metat');
    $metad = $post('metad');
    $slider = $post('slider', 'No');
    $slider_priority = $post('slider_priority', '0');
    $latest_priority = $post('latest_priority', '0');
    $category = $post('category');
    $team_id = $post('team_id', '0');
    $hashtags = $post('hashtags');
    $short_description = $post('short_description');
    $img_source = $post('img_source');
    $img_abt = $post('img_abt');
    $pub_date_time = isset($_POST['pub_date_time']) ? trim((string) $_POST['pub_date_time']) : '';
    $existingPub = isset($rs['pub_date_time']) ? (string) $rs['pub_date_time'] : '';
    $sched = nm_resolve_publish_schedule($_POST, $pub_date_time !== '' ? $pub_date_time : $existingPub);
    if ($sched['error']) {
        array_push($errors, $sched['error']);
    }
    $status = mysqli_real_escape_string($con, $sched['status']);
    $pub_date_time = mysqli_real_escape_string($con, $sched['pub_date_time']);
    $show_home = $post('show_home', isset($rs['show_home']) ? $rs['show_home'] : 'No');
    $newstype = $post('newstype', isset($rs['newstype']) ? $rs['newstype'] : 'Content');
    $v_link = $post('videolink');

    if ($short_description === '' && $title !== '') {
        $short_description = $title;
    }
    if ($metat === '' && $title !== '') {
        $metat = $title;
    }
    if ($metad === '' && $title !== '') {
        $metad = $title;
    }

    $video_id = isset($rs['videoid']) ? $rs['videoid'] : '';
    if ($v_link !== '') {
        $video_parts = explode('?v=', $v_link);
        if (empty($video_parts[1])) {
            $video_parts = explode('/v/', $v_link);
        }
        if (empty($video_parts[1])) {
            $video_parts = explode('youtu.be/', $v_link);
        }
        if (!empty($video_parts[1])) {
            $video_parts = explode('&', $video_parts[1]);
            $video_id = $video_parts[0];
        }
    }

    $name = isset($rs['video_file']) ? $rs['video_file'] : '';
    if (!empty($_FILES['video_file']['name']) && !empty($_FILES['video_file']['tmp_name'])) {
        $name = $_FILES['video_file']['name'];
        $target_dir = __DIR__ . '/../videos/';
        if (!is_dir($target_dir)) {
            @mkdir($target_dir, 0755, true);
        }
        move_uploaded_file($_FILES['video_file']['tmp_name'], $target_dir . $name);
    }

    $post_image = isset($rs['image']) ? $rs['image'] : '';
    if (!empty($_FILES['image']['tmp_name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $post_image = md5(uniqid() . rand()) . '.' . $ext;
        $news_img_dir = __DIR__ . '/../images/news/';
        if (!is_dir($news_img_dir)) {
            @mkdir($news_img_dir, 0755, true);
        }
        move_uploaded_file($_FILES['image']['tmp_name'], $news_img_dir . $post_image);
    }

    $replace = array(' ', ',', '.', "'", '&', '-', '_', ':', '(', ')', '+', ';', '#', '!', '*', '{', '}', '[', ']', '?', '/', '"', '|', '@', '%', '$');
    $linkname = str_replace($replace, '-', trim($newsurl));
    $linkname = str_replace(array('----', '---', '--'), '-', $linkname);
    $folderStr = str_replace($replace, '-', trim($category));
    $folderStr = str_replace(array('----', '---', '--'), '-', $folderStr);
    $foldername = $folderStr . '/';
    $link = $foldername . $linkname . '/';

    if ($title === '') {
        array_push($errors, 'Kindly fill news title');
    }
    if ($newsurl === '') {
        array_push($errors, 'Kindly fill news url');
    }
    if ($metat === '') {
        array_push($errors, 'Kindly fill meta title');
    }
    if ($category === '' || $category === '0') {
        array_push($errors, 'Kindly fill category');
    }
    if (!$postedHasText && !$existingHasText) {
        array_push($errors, 'Kindly fill the full article Description (CKEditor). Click Update after the text appears.');
    }

    if (count($errors) == 0) {
        if ($slider_priority !== '' && isset($rs['slider_priority']) && $slider_priority !== (string) $rs['slider_priority']) {
            $pri = mysqli_query($con, "SELECT `slider_priority`,`newsid` FROM `news` WHERE `slider`='Yes' AND `slider_priority` >= '$slider_priority' ORDER BY `slider_priority` ASC");
            if ($pri instanceof mysqli_result) {
                while ($pr = mysqli_fetch_array($pri)) {
                    $new_slider_priority = $pr['slider_priority'] + 1;
                    mysqli_query($con, "UPDATE `news` SET `slider_priority`='$new_slider_priority' WHERE `newsid`='" . $pr['newsid'] . "'");
                }
            }
        }

        mysqli_query($con, "UPDATE `news` SET `latest_priority`='0' WHERE `latest_priority`='$latest_priority'");

        $up = "UPDATE `news` SET `newsurl`='$linkname',`latest_news`='$latest_news',`folder`='$foldername',`seolink`='$link',`metat`='$metat',`metad`='$metad',`slider`='$slider',`title`='$title',`short_description`='$short_description',`description`='$description',`image`='$post_image',`img_abt`='$img_abt',`img_source`='$img_source',`newstype`='$newstype',`category`='$category',`video_file`='$name',`videoid`='$video_id', `show_home`='$show_home', `slider_priority`='$slider_priority', `latest_priority`='$latest_priority', `team_id`='$team_id', `hashtags`='$hashtags', `pub_date_time`='$pub_date_time', `status`='$status' WHERE newsid='$srid'";
        $exUp = mysqli_query($con, $up);

        if ($exUp) {
            // If moved to Published from Scheduled/Unpublished, send push (same as status AJAX)
            $oldStatus = isset($rs['status']) ? (string) $rs['status'] : '';
            if ($sched['status'] === 'Published' && $oldStatus !== 'Published') {
                include_once __DIR__ . '/push_news.php';
                if (function_exists('naradmuni_send_news_push')) {
                    naradmuni_send_news_push($con, $title, $short_description, $post_image, $linkname);
                }
            }
            mysqli_query($con, "DELETE FROM `news_cat` WHERE `news_id`='$srid'");
            if (!empty($_POST['cat_id']) && is_array($_POST['cat_id'])) {
                foreach ($_POST['cat_id'] as $cid) {
                    $cid = trim((string) $cid);
                    if ($cid === '') {
                        continue;
                    }
                    $cat_id = mysqli_real_escape_string($con, $cid);
                    mysqli_query($con, "INSERT INTO `news_cat`(`category`, `news_id`) VALUES('$cat_id','$srid')");
                }
            } elseif ($category !== '' && $category !== '0') {
                // Keep home category mirrored if no checkboxes posted
                mysqli_query($con, "INSERT INTO `news_cat`(`category`, `news_id`) VALUES('$category','$srid')");
            }

            echo "<script>alert('Updated Successfully'); window.location.href='news.php';</script>";
            exit;
        }
        array_push($errors, 'Sorry, there was an error: ' . mysqli_error($con));
    }

    // Refresh $rs after failed post so form still shows submitted values
    $rs['title'] = isset($_POST['title']) ? $_POST['title'] : $rs['title'];
    $rs['newsurl'] = isset($_POST['newsurl']) ? $_POST['newsurl'] : $rs['newsurl'];
    $rs['metat'] = isset($_POST['metat']) ? $_POST['metat'] : $rs['metat'];
    $rs['metad'] = isset($_POST['metad']) ? $_POST['metad'] : $rs['metad'];
    $rs['hashtags'] = isset($_POST['hashtags']) ? $_POST['hashtags'] : (isset($rs['hashtags']) ? $rs['hashtags'] : '');
    $rs['short_description'] = isset($_POST['short_description']) ? $_POST['short_description'] : (isset($rs['short_description']) ? $rs['short_description'] : '');
    $rs['description'] = isset($_POST['description']) ? $_POST['description'] : $rs['description'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Edit News</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../include/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="../include/css/style.css">
  <script src="../include/js/jquery.min.js"></script>
</head>
<body>
<div id="overlay"><div><img src="img/loading.gif" width="64px" height="64px" alt=""/></div></div>
<div class="wrapper">
  <?php include "sidebar.php"; ?>
  <div id="content">
    <?php include "header.php"; ?>

    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Edit News</li>
    </ol>

    <div class="container-fluid page-content">
      <?php include 'errors.php'; ?>
      <?php include 'sucsess.php'; ?>

      <form id="SubmitForm" method="post" enctype="multipart/form-data">
        <div class="col-md-12 form-group">
          <p><b>Select Categories:</b></p>
          <?php
          $query = $con->query("SELECT id, hindi_name, maincat, cat_url FROM `categories` WHERE `cat_url` IS NOT NULL AND `cat_url` != '' ORDER BY id ASC");
          if (!$query) {
              $query = $con->query("SELECT id, hindi_name, cat_url FROM `categories` WHERE `cat_url` IS NOT NULL AND `cat_url` != '' ORDER BY id ASC");
          }
          if (!$query) {
              $query = $con->query("SELECT id, maincat, cat_url FROM `categories` WHERE `cat_url` IS NOT NULL AND `cat_url` != '' ORDER BY id ASC");
          }
          if ($query && $query->num_rows > 0) {
              while ($row1 = $query->fetch_assoc()) {
                  $id = (int) $row1['id'];
                  $checked = '';
                  $ex4 = mysqli_query($con, "SELECT category FROM `news_cat` WHERE `category`='$id' AND `news_id`='" . mysqli_real_escape_string($con, $srid) . "' LIMIT 1");
                  if ($ex4 instanceof mysqli_result && mysqli_fetch_assoc($ex4)) {
                      $checked = 'checked';
                  }
                  echo '<label style="margin-right:10px;" class="checkbox-inline"><input type="checkbox" name="cat_id[]" value="' . $id . '" ' . $checked . '> ' . nm_h(nm_cat_label($row1)) . '</label>';
              }
          }
          ?>
        </div>

        <div class="col-md-2 form-group">
          <label class="control-label">News Type</label>
          <select class="custom-select" id="newstype" name="newstype">
            <option><?php echo nm_h(isset($rs['newstype']) ? $rs['newstype'] : 'Content'); ?></option>
            <option>Content</option>
            <option>Video</option>
          </select>
        </div>

        <div class="col-md-3 form-group">
          <label class="control-label">Home Category:</label>
          <select class="custom-select" id="category" name="category">
            <option value="<?php echo nm_h($catIdHome); ?>"><?php echo nm_h($catLabel !== '' ? $catLabel : $catIdHome); ?></option>
            <?php
            $query = $con->query("SELECT id, hindi_name, maincat FROM `categories` ORDER BY id ASC");
            if (!$query) {
                $query = $con->query("SELECT id, hindi_name FROM `categories` ORDER BY id ASC");
            }
            if (!$query) {
                $query = $con->query("SELECT id, maincat FROM `categories` ORDER BY id ASC");
            }
            if ($query && $query->num_rows > 0) {
                while ($row = $query->fetch_assoc()) {
                    echo '<option value="' . (int) $row['id'] . '">' . nm_h(nm_cat_label($row)) . '</option>';
                }
            }
            ?>
          </select>
        </div>

        <div class="col-md-2 form-group">
          <label class="control-label">Show In Slider</label>
          <select class="custom-select" id="slider" name="slider">
            <option><?php echo nm_h(isset($rs['slider']) ? $rs['slider'] : 'No'); ?></option>
            <option>No</option>
            <option>Yes</option>
          </select>
        </div>

        <div class="col-md-2 form-group">
          <label class="control-label">Slider Priority:</label>
          <input class="form-control" type="number" name="slider_priority" value="<?php echo nm_h(isset($rs['slider_priority']) ? $rs['slider_priority'] : '0'); ?>">
        </div>

        <div class="col-md-3 form-group">
          <label class="control-label">When to publish</label>
          <?php
          $curStatus = isset($rs['status']) ? (string) $rs['status'] : 'Published';
          $isScheduled = ($curStatus === 'Scheduled');
          $pubVal = isset($rs['pub_date_time']) ? (string) $rs['pub_date_time'] : '';
          $pubLocal = '';
          if ($pubVal !== '') {
              $pts = strtotime(str_replace('T', ' ', $pubVal));
              if ($pts) {
                  $pubLocal = date('Y-m-d\TH:i', $pts);
              }
          }
          ?>
          <div style="padding-top:6px;">
            <label class="checkbox-inline" style="font-weight:600;margin-right:12px;">
              <input type="radio" name="publish_mode" value="now" <?php echo $isScheduled ? '' : 'checked'; ?>> Publish now
            </label>
            <label class="checkbox-inline" style="font-weight:600;">
              <input type="radio" name="publish_mode" value="schedule" id="nm-publish-schedule" <?php echo $isScheduled ? 'checked' : ''; ?>> Schedule
            </label>
          </div>
          <div id="nm-schedule-wrap" style="<?php echo $isScheduled ? '' : 'display:none;'; ?>margin-top:8px;">
            <label class="control-label" for="pub_date_time">Go live at (IST)</label>
            <input class="form-control" id="pub_date_time" type="datetime-local" name="pub_date_time" value="<?php echo nm_h($pubLocal); ?>">
            <p class="nm-form-hint" style="margin:6px 0 0;">Hidden until this time, then auto-published.</p>
          </div>
        </div>

        <div class="col-md-4 form-group">
          <label class="control-label">Breaking news</label>
          <div style="padding-top:6px;">
            <label class="checkbox-inline" style="font-weight:600;">
              <input type="checkbox" name="latest_news" value="Yes" <?php echo (!empty($rs['latest_news']) && $rs['latest_news'] === 'Yes') ? 'checked' : ''; ?>>
              Show in homepage top list (latest 5)
            </label>
            <p class="nm-form-hint" style="margin:6px 0 0;">Add-on only — keep a real Home Category above.</p>
          </div>
        </div>

        <div class="col-md-4 form-group">
          <label class="control-label">Latest News Priority:</label>
          <input class="form-control" type="number" name="latest_priority" value="<?php echo nm_h(isset($rs['latest_priority']) ? $rs['latest_priority'] : '0'); ?>">
        </div>

        <div class="col-md-4 form-group">
          <label class="control-label">Image (850X565 Pixels):</label>
          <input class="form-control" type="file" name="image" accept="image/*">
          <?php if (!empty($rs['image'])) { ?>
            <p class="text-muted small mt-1">Current: <?php echo nm_h($rs['image']); ?></p>
          <?php } ?>
        </div>

        <?php
        if (isset($rs['newstype']) && $rs['newstype'] == 'Video') {
            echo '<div class="col-md-6 form-group">
            <label class="control-label">Home Page</label>
            <select class="custom-select" id="show_home" name="show_home">
                <option>' . nm_h(isset($rs['show_home']) ? $rs['show_home'] : 'No') . '</option>
                <option>No</option>
                <option>Yes</option>
            </select>
            </div>';

            if (!empty($rs['video_file'])) {
                echo '<div class="col-md-6 form-group">
              <label class="control-label">Select Video:</label>
                <input class="form-control" type="file" name="video_file">
            </div>';
            } else {
                echo '<div class="col-md-6 form-group">
              <label class="control-label">YouTube Link:</label>
              <input class="form-control" type="text" name="videolink" value="https://www.youtube.com/watch?v=' . nm_h(isset($rs['videoid']) ? $rs['videoid'] : '') . '">
            </div>';
            }
        } else {
            echo '<div id="vid"></div><div id="vid2"></div>';
        }
        ?>

        <div class="col-md-4 form-group">
          <label class="control-label">Select Author:</label>
          <select class="custom-select" id="team_id" name="team_id">
            <option value="<?php echo nm_h($teamId); ?>"><?php echo nm_h(isset($au['name']) ? $au['name'] : ''); ?></option>
            <?php
            $query = $con->query("SELECT * FROM `team` ORDER BY t_id ASC");
            if ($query && $query->num_rows > 0) {
                while ($row = $query->fetch_assoc()) {
                    echo '<option value="' . (int) $row['t_id'] . '">' . nm_h($row['name']) . '</option>';
                }
            }
            ?>
          </select>
        </div>

        <div class="col-md-4 form-group">
          <label class="control-label">About Image:</label>
          <input class="form-control" type="text" name="img_abt" value="<?php echo nm_h(isset($rs['img_abt']) ? $rs['img_abt'] : ''); ?>">
        </div>

        <div class="col-md-4 form-group">
          <label class="control-label">Image Source:</label>
          <input class="form-control" type="text" name="img_source" value="<?php echo nm_h(isset($rs['img_source']) ? $rs['img_source'] : ''); ?>">
        </div>

        <div class="col-md-6 form-group">
          <label class="control-label">News URL:</label>
          <input class="form-control" type="text" name="newsurl" value="<?php echo nm_h(isset($rs['newsurl']) ? $rs['newsurl'] : ''); ?>">
        </div>

        <div class="col-md-6 form-group">
          <label class="control-label">Title:</label>
          <input class="form-control" type="text" name="title" value="<?php echo nm_h(isset($rs['title']) ? $rs['title'] : ''); ?>">
        </div>

        <div class="col-md-12 form-group">
          <label class="control-label">Meta Title:</label>
          <input class="form-control" type="text" name="metat" value="<?php echo nm_h(isset($rs['metat']) ? $rs['metat'] : ''); ?>">
        </div>

        <div class="col-md-12 form-group">
          <label class="control-label">Meta Description:</label>
          <textarea class="form-control" cols="20" rows="5" name="metad"><?php echo nm_h(isset($rs['metad']) ? $rs['metad'] : ''); ?></textarea>
        </div>

        <div class="col-md-12 form-group">
          <label class="control-label">#Hashtags For Social Media:</label>
          <textarea class="form-control" cols="20" rows="5" name="hashtags"><?php echo nm_h(isset($rs['hashtags']) ? $rs['hashtags'] : ''); ?></textarea>
        </div>

        <div class="col-md-12 form-group">
          <label class="control-label">Short Description:</label>
          <textarea class="form-control" cols="20" rows="5" name="short_description"><?php echo nm_h(isset($rs['short_description']) ? $rs['short_description'] : ''); ?></textarea>
        </div>

        <div class="col-md-12 form-group">
          <label class="control-label">Description</label>
          <textarea class="ckeditor form-control" id="description" name="description"><?php echo nm_h(isset($rs['description']) ? $rs['description'] : ''); ?></textarea>
        </div>

        <div class="col-md-12 form-group">
          <button type="submit" name="update" value="update" class="btn btn-info">Update</button>
          <a class="btn btn-outline-secondary" href="news.php">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include "footer.php"; ?>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script type="text/javascript">
  CKEDITOR.replace('description', {
    width: '100%',
    extraPlugins: '',
    filebrowserBrowseUrl: 'ckeditor/filemanager/browser/default/browser.html?Connector=ckeditor/filemanager/connectors/php/connector.php',
    filebrowserImageBrowseUrl: 'ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector=ckeditor/filemanager/connectors/php/connector.php',
    filebrowserFlashBrowseUrl: 'ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector=ckeditor/filemanager/connectors/php/connector.php',
    filebrowserUploadUrl: 'ckeditor/filemanager/connectors/php/upload.php?Type=File',
    filebrowserImageUploadUrl: 'ckeditor/filemanager/connectors/php/upload.php?Type=Image',
    filebrowserFlashUploadUrl: 'ckeditor/filemanager/connectors/php/upload.php?Type=Flash'
  });
  // Critical: push CKEditor HTML into <textarea> before PHP receives the POST
  document.getElementById('SubmitForm').addEventListener('submit', function () {
    for (var name in CKEDITOR.instances) {
      if (CKEDITOR.instances.hasOwnProperty(name)) {
        CKEDITOR.instances[name].updateElement();
      }
    }
  });
  (function () {
    var wrap = document.getElementById('nm-schedule-wrap');
    var input = document.getElementById('pub_date_time');
    var scheduleRadio = document.getElementById('nm-publish-schedule');
    if (!wrap || !scheduleRadio) return;
    function sync() {
      var schedule = scheduleRadio.checked;
      wrap.style.display = schedule ? 'block' : 'none';
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
    $('#sidebarCollapse').on('click', function () {
      $('#sidebar').toggleClass('active');
    });
  });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="../include/js/bootstrap.min.js"></script>
<script src="js/all.js"></script>
<script>
  $("#newstype").on('change', function () {
    $.ajax({
      type: "POST",
      url: "ajaxVid.php",
      data: { newstype: $("#newstype").val() },
      beforeSend: function () { $('#vid').html("<p>Loading....</p>"); },
      success: function (data) { $('#vid').html(data); }
    });
  });
</script>
</body>
</html>
