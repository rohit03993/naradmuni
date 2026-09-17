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
if (!function_exists('nm_admin_team_id')) {
    require_once __DIR__ . '/admin_helpers.php';
}
$nmLinkedTeamId = function_exists('nm_admin_team_id') ? (int) nm_admin_team_id($con) : 0;

/** Escape for HTML attributes / textarea (keeps </textarea> in body from breaking the form). */
if (!function_exists('nm_h')) {
function nm_h($v)
{
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
}

if (!function_exists('nm_cat_label')) {
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
}

// Accept eid (correct) or id (common mistake from bookmarks)
$srid = isset($_GET['eid']) ? $_GET['eid'] : (isset($_GET['id']) ? $_GET['id'] : '');
$srid = preg_replace('/\D+/', '', (string) $srid);

if ($srid === '') {
    nm_js_notice('Missing news id', 'news.php', 'error');
    exit;
}

$qry = "SELECT * FROM `news` WHERE newsid='" . mysqli_real_escape_string($con, $srid) . "' LIMIT 1";
$ex = mysqli_query($con, $qry);
$rs = ($ex instanceof mysqli_result) ? mysqli_fetch_assoc($ex) : null;

if (!$rs) {
    nm_js_notice('News not found', 'news.php', 'error');
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

if (!isset($errors) || !is_array($errors)) {
    $errors = array();
}

if (isset($_POST['update'])) {
    $keep = function ($key, $default = '') use ($rs, $con) {
        $v = isset($rs[$key]) ? (string) $rs[$key] : $default;
        return mysqli_real_escape_string($con, $v);
    };

    $title = mysqli_real_escape_string($con, isset($_POST['title']) ? (string) $_POST['title'] : '');
    $latest_news = (isset($_POST['latest_news']) && $_POST['latest_news'] === 'Yes') ? 'Yes' : 'No';
    $latest_news = mysqli_real_escape_string($con, $latest_news);
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
    $category = mysqli_real_escape_string($con, isset($_POST['category']) ? (string) $_POST['category'] : '');
    $team_id = mysqli_real_escape_string($con, isset($_POST['team_id']) ? (string) $_POST['team_id'] : '0');
    if (($team_id === '' || $team_id === '0') && $nmLinkedTeamId > 0) {
        $team_id = (string) $nmLinkedTeamId;
    }

    // Hidden Add-News leftovers: keep stored values so a lean form cannot blank them.
    $linkname = $keep('newsurl');
    $foldername = $keep('folder');
    $link = $keep('seolink');
    $metat = $keep('metat');
    $metad = $keep('metad');
    $short_description = $keep('short_description');
    $hashtags = $keep('hashtags');
    $img_abt = $keep('img_abt');
    $img_source = $keep('img_source');
    $slider = $keep('slider', 'No');
    $slider_priority = $keep('slider_priority', '0');
    $latest_priority = $keep('latest_priority', '0');
    $show_home = $keep('show_home', 'No');
    $newstype = $keep('newstype', 'Content');
    $video_id = $keep('videoid');
    $name = $keep('video_file');
    if ($short_description === '' && $title !== '') {
        $short_description = $title;
    }
    if ($metat === '' && $title !== '') {
        $metat = $title;
    }
    if ($metad === '' && $title !== '') {
        $metad = $title;
    }

    $pub_date_time = isset($_POST['pub_date_time']) ? trim((string) $_POST['pub_date_time']) : '';
    $existingPub = isset($rs['pub_date_time']) ? (string) $rs['pub_date_time'] : '';
    $sched = nm_resolve_publish_schedule($_POST, $pub_date_time !== '' ? $pub_date_time : $existingPub);
    if ($sched['error']) {
        array_push($errors, $sched['error']);
    }
    $status = mysqli_real_escape_string($con, $sched['status']);
    $pub_date_time = mysqli_real_escape_string($con, $sched['pub_date_time']);

    $post_image = $keep('image');
    if (!empty($_FILES['image']['tmp_name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $post_image = md5(uniqid() . rand()) . '.' . $ext;
        $news_img_dir = __DIR__ . '/../images/news/';
        if (!is_dir($news_img_dir)) {
            @mkdir($news_img_dir, 0755, true);
        }
        move_uploaded_file($_FILES['image']['tmp_name'], $news_img_dir . $post_image);
    }

    if ($title === '') {
        array_push($errors, 'Kindly fill news title');
    }
    if ($linkname === '') {
        array_push($errors, 'News URL is missing on this article.');
    }
    if ($category === '' || $category === '0') {
        array_push($errors, 'Kindly fill category');
    }
    if (!$postedHasText && !$existingHasText) {
        array_push($errors, 'Kindly fill the full article Description (CKEditor). Click Update after the text appears.');
    }

    if (count($errors) == 0) {
        $up = "UPDATE `news` SET `latest_news`='$latest_news',`metat`='$metat',`metad`='$metad',`slider`='$slider',`title`='$title',`short_description`='$short_description',`description`='$description',`image`='$post_image',`img_abt`='$img_abt',`img_source`='$img_source',`newstype`='$newstype',`category`='$category',`video_file`='$name',`videoid`='$video_id', `show_home`='$show_home', `slider_priority`='$slider_priority', `latest_priority`='$latest_priority', `team_id`='$team_id', `hashtags`='$hashtags', `pub_date_time`='$pub_date_time', `status`='$status' WHERE newsid='$srid'";
        $exUp = mysqli_query($con, $up);

        if ($exUp) {
            $oldStatus = isset($rs['status']) ? (string) $rs['status'] : '';
            if ($sched['status'] === 'Published' && $oldStatus !== 'Published') {
                include_once __DIR__ . '/push_news.php';
                if (function_exists('naradmuni_send_news_push')) {
                    naradmuni_send_news_push($con, $title, $short_description, $post_image, $linkname);
                }
            }
            if ($category !== '' && $category !== '0') {
                $hasHome = mysqli_query($con, "SELECT news_id FROM `news_cat` WHERE `news_id`='$srid' AND `category`='$category' LIMIT 1");
                if (!($hasHome instanceof mysqli_result) || !mysqli_fetch_assoc($hasHome)) {
                    mysqli_query($con, "INSERT INTO `news_cat`(`category`, `news_id`) VALUES('$category','$srid')");
                }
            }

            $okMsg = ($sched['status'] === 'Scheduled') ? 'Scheduled successfully' : 'Updated successfully';
            nm_js_notice($okMsg, 'news.php');
            exit;
        }
        array_push($errors, 'Sorry, there was an error: ' . mysqli_error($con));
    }

    $rs['title'] = isset($_POST['title']) ? $_POST['title'] : $rs['title'];
    $rs['description'] = $postedHasText ? $descriptionRaw : $rs['description'];
    $rs['category'] = isset($_POST['category']) ? $_POST['category'] : $rs['category'];
    $rs['team_id'] = isset($_POST['team_id']) ? $_POST['team_id'] : $rs['team_id'];
    $rs['latest_news'] = $latest_news === 'Yes' ? 'Yes' : 'No';
    $teamId = isset($rs['team_id']) ? (string) $rs['team_id'] : $teamId;
    $catIdHome = isset($rs['category']) ? (string) $rs['category'] : $catIdHome;
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

      <form id="SubmitForm" class="nm-news-form" method="post" enctype="multipart/form-data">
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
        $selTeam = (int) $teamId;
        $liveSlug = isset($rs['newsurl']) ? (string) $rs['newsurl'] : '';
        ?>

        <section class="nm-form-section">
          <h2 class="nm-form-section__title">Story</h2>
          <div class="nm-form-grid">
            <div class="nm-form-field nm-form-field--full">
              <label class="control-label" for="nm-title">Title</label>
              <input class="form-control" id="nm-title" type="text" name="title" value="<?php echo nm_h(isset($rs['title']) ? $rs['title'] : ''); ?>" required>
            </div>
            <div class="nm-form-field nm-form-field--full">
              <label class="control-label">News URL</label>
              <input class="form-control" type="text" value="<?php echo nm_h($liveSlug); ?>" readonly>
              <p class="nm-form-hint">Locked so the live page stays <code>/news/<?php echo nm_h($liveSlug); ?></code>. Changing it would break Google rankings.</p>
            </div>
            <div class="nm-form-field nm-form-field--full">
              <label class="control-label">Image (850×565)</label>
              <input class="form-control" type="file" name="image" accept="image/*">
              <?php if (!empty($rs['image'])) { ?>
                <div class="nm-current-img">
                  <img src="../images/news/<?php echo nm_h($rs['image']); ?>" alt="">
                  <span class="nm-form-hint" style="margin:0;">Leave empty to keep the current photo.</span>
                </div>
              <?php } ?>
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
                if ($tq) {
                    while ($tr = $tq->fetch_assoc()) {
                        $sel = ((int) $tr['t_id'] === $selTeam) ? ' selected' : '';
                        echo '<option value="' . (int) $tr['t_id'] . '"' . $sel . '>' . nm_h($tr['name']) . '</option>';
                    }
                }
                ?>
              </select>
              <p class="nm-form-hint">Public story byline. Change anytime.</p>
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
                if ($query) {
                    while ($row = $query->fetch_assoc()) {
                        $sel = ((string) $row['id'] === (string) $catIdHome) ? ' selected' : '';
                        echo '<option value="' . (int) $row['id'] . '"' . $sel . '>' . nm_h(nm_cat_label($row)) . '</option>';
                    }
                }
                ?>
              </select>
              <p class="nm-form-hint">Pick the real place/topic (e.g. Indore, Bhopal, नीमच). Required.</p>
            </div>
            <div class="nm-form-field nm-form-field--full">
              <label class="checkbox-inline" style="font-weight:600;">
                <input type="checkbox" name="latest_news" value="Yes" <?php echo (!empty($rs['latest_news']) && $rs['latest_news'] === 'Yes') ? 'checked' : ''; ?>>
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
            <textarea class="ckeditor form-control" id="description" name="description"><?php echo nm_h(isset($rs['description']) ? $rs['description'] : ''); ?></textarea>
          </div>
        </section>

        <section class="nm-form-section">
          <h2 class="nm-form-section__title">Publish</h2>
          <div class="nm-form-grid">
            <div class="nm-form-field nm-form-field--full">
              <label class="control-label">When to publish</label>
              <div style="display:flex;flex-wrap:wrap;gap:16px;align-items:center;margin-top:6px;">
                <label class="checkbox-inline" style="font-weight:600;margin:0;">
                  <input type="radio" name="publish_mode" value="now" <?php echo $isScheduled ? '' : 'checked'; ?>> Publish now
                </label>
                <label class="checkbox-inline" style="font-weight:600;margin:0;">
                  <input type="radio" name="publish_mode" value="schedule" id="nm-publish-schedule" <?php echo $isScheduled ? 'checked' : ''; ?>> Schedule for later
                </label>
              </div>
            </div>
            <div class="nm-form-field nm-form-field--full" id="nm-schedule-wrap" style="<?php echo $isScheduled ? '' : 'display:none;'; ?>">
              <label class="control-label" for="pub_date_time">Go live at (IST)</label>
              <input class="form-control" type="datetime-local" id="pub_date_time" name="pub_date_time" value="<?php echo nm_h($pubLocal); ?>">
              <p class="nm-form-hint">Story stays hidden until this time, then goes live automatically.</p>
            </div>
          </div>
        </section>

        <div class="nm-form-actions">
          <button type="submit" name="update" value="update" class="btn btn-info" id="nm-publish-btn"><?php echo $isScheduled ? 'Schedule' : 'Update'; ?></button>
          <a class="btn btn-outline-secondary" href="news.php">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include "footer.php"; ?>
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
  var scheduleRadio = document.getElementById('nm-publish-schedule');
  if (!wrap || !scheduleRadio) return;
  function sync() {
    var schedule = scheduleRadio.checked;
    wrap.style.display = schedule ? 'block' : 'none';
    if (btn) btn.textContent = schedule ? 'Schedule' : 'Update';
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
</body>
</html>
