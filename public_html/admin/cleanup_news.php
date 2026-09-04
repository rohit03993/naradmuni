<?php
include "config.php";

if (!isset($_SESSION["aemail"])) {
	$_SESSION["msg"] = "You must log in first";
	header("location: ../manage.php");
	exit;
}

if (isset($_GET["logout"])) {
	session_destroy();
	unset($_SESSION["aemail"]);
	header("location: ../manage.php");
	exit;
}

$usersession = $_SESSION["aemail"];
$res = mysqli_query($con, "SELECT * FROM admin WHERE aemail='$usersession'");
$userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);

$months = isset($_GET["months"]) ? max(1, (int) $_GET["months"]) : 6;
$minViews = 3000;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Cleanup old news</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../include/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="../include/css/style.css">
  <script src="../include/js/jquery.min.js"></script>
  <style>
    .cleanup-box { background:#fff3cd; border:1px solid #ffc107; padding:14px 16px; border-radius:6px; margin-bottom:16px; }
    .cleanup-danger { background:#f8d7da; border:1px solid #f5c6cb; padding:14px 16px; border-radius:6px; margin:16px 0; }
    .cleanup-stats { background:#e8f5e9; border:1px solid #a5d6a7; padding:14px 16px; border-radius:6px; margin-bottom:16px; }
    .cleanup-stats .big { font-size:22px; font-weight:700; margin-right:6px; }
    #cleanup-log { max-height:220px; overflow:auto; font-size:13px; background:#111; color:#d1fae5; padding:10px; border-radius:6px; display:none; margin-top:12px; }
    #stats-progress { height:8px; background:#c8e6c9; border-radius:4px; margin-top:8px; overflow:hidden; }
    #stats-progress > span { display:block; height:100%; background:#2e7d32; width:0%; }
    .delete-count { font-size:18px; font-weight:700; }
  </style>
</head>
<body>
<div id="overlay"><div><img src="img/loading.gif" width="64" height="64" alt=""/></div></div>
<div class="wrapper">
  <?php include "sidebar.php"; ?>
  <div id="content">
    <?php include "header.php"; ?>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i>
        <a href="news.php">News</a> <i class="fa fa-angle-right"></i> Cleanup old news</li>
    </ol>
    <div class="container-fluid page-content">

      <div class="cleanup-box">
        <strong>One cleanup action.</strong>
        Choose age → Preview → confirm the number → delete.
        <br><strong>Rule for everything on this page:</strong> posts with
        <strong><?php echo number_format($minViews); ?>+ views are never deleted</strong>
        (they show as <em>KEEP</em> in the list). Newer posts (outside the age filter) are never touched.
      </div>

      <!-- Step 1: filter -->
      <div class="row" style="margin-bottom:12px;">
        <div class="col-md-3">
          <label><strong>1. Older than</strong></label>
          <select id="months" class="custom-select">
            <?php foreach (array(3, 6, 12, 18, 24) as $m) { ?>
              <option value="<?php echo $m; ?>" <?php echo $m === $months ? "selected" : ""; ?>><?php echo $m; ?> months</option>
            <?php } ?>
          </select>
        </div>
        <div class="col-md-8" style="padding-top:28px;">
          <button type="button" class="btn btn-primary" id="btn-preview"><i class="fas fa-search"></i> 2. Preview matching posts</button>
          <a href="news.php" class="btn btn-link">← Back to Manage News</a>
        </div>
      </div>

      <!-- Step 2: counts -->
      <div class="cleanup-stats" id="stats-box">
        <div><span class="big" id="stat-posts">—</span> posts match this age filter</div>
        <div><span class="big" id="stat-images">—</span> with a featured image/video</div>
        <div><span class="big" id="stat-size">…</span> estimated disk (featured + videos)</div>
        <div class="text-muted" style="font-size:12px;margin-top:6px;" id="stat-note">Click Preview to load count and size.</div>
        <div id="stats-progress"><span></span></div>
      </div>

      <!-- Step 3: one delete place -->
      <div class="cleanup-danger" id="delete-box">
        <p style="margin-bottom:8px;"><strong>3. Delete matching posts</strong></p>
        <p class="mb-2">
          Will process up to
          <span class="delete-count" id="delete-count">—</span>
          posts older than <strong id="delete-months">?</strong> months.
          <br>
          Of those, any with <strong><?php echo number_format($minViews); ?>+ views are kept</strong>; the rest are deleted with their files.
          Newer posts are not included.
        </p>
        <label>Type <code>DELETE</code> to enable</label>
        <div class="form-inline" style="gap:8px;flex-wrap:wrap;">
          <input type="text" id="confirm-delete" class="form-control" placeholder="DELETE" autocomplete="off" style="min-width:140px;">
          <button type="button" class="btn btn-danger" id="btn-delete" disabled>
            <i class="fas fa-trash-alt"></i> Delete matching posts
          </button>
        </div>
        <small class="text-muted d-block mt-2">Keep this tab open. Runs in small batches. Closing the tab stops further batches (already deleted stay deleted).</small>
        <div id="cleanup-log"></div>
      </div>

      <!-- Review list (same filter; not a second delete mode) -->
      <h5 style="margin-top:8px;">Matching posts (review only)</h5>
      <p class="text-muted" style="font-size:13px;">Same age filter as above. <em>KEEP</em> = <?php echo number_format($minViews); ?>+ views, will not be deleted.</p>
      <div id="summary" class="text-muted" style="margin-bottom:10px;"></div>
      <div id="pagination-result"></div>
    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="../include/js/bootstrap.min.js"></script>
<script src="js/all.js"></script>
<script>
var statsRunning = false;
var deleteStop = false;
var previewReady = false;
var MIN_VIEWS_KEEP = <?php echo (int) $minViews; ?>;

function formatBytes(n) {
  n = Number(n) || 0;
  if (n < 1024) return Math.round(n) + " B";
  if (n < 1048576) return (n / 1024).toFixed(1) + " KB";
  if (n < 1073741824) return (n / 1048576).toFixed(2) + " MB";
  return (n / 1073741824).toFixed(2) + " GB";
}

function matchCount() {
  return Number($("#stat-posts").text().replace(/,/g, "")) || Number($("#rowcount").val()) || 0;
}

function syncDeleteSummary() {
  var n = matchCount();
  var m = $("#months").val();
  $("#delete-count").text(n ? n.toLocaleString() : "—");
  $("#delete-months").text(m || "?");
  updateDeleteBtn();
}

function updateDeleteBtn() {
  var n = matchCount();
  var ok = previewReady && n > 0 && $("#confirm-delete").val().trim() === "DELETE";
  $("#btn-delete").prop("disabled", !ok);
}

function getresult() {
  previewReady = false;
  updateDeleteBtn();
  $("#overlay").show();
  $.ajax({
    url: "desp_cleanup_news.php",
    type: "GET",
    data: {
      months: $("#months").val(),
      rowcount: $("#rowcount").val() || "",
      page: 1
    },
    success: function (data) {
      $("#pagination-result").html(data);
      $("#overlay").hide();
      previewReady = true;
      syncDeleteSummary();
      startStatsScan();
    },
    error: function () {
      $("#overlay").hide();
      alert("Failed to load preview");
    }
  });
}

function startStatsScan() {
  if (statsRunning) return;
  statsRunning = true;
  var months = $("#months").val();
  var totalBytes = 0;
  var totalFiles = 0;
  var offset = 0;
  var postsTotal = 0;
  var withFeatured = 0;
  $("#stat-size").text("scanning…");
  $("#stat-note").text("Measuring featured image + video file sizes…");
  $("#stats-progress > span").css("width", "2%");

  function step() {
    $.ajax({
      url: "ajax_cleanup_stats.php",
      type: "POST",
      dataType: "json",
      data: { months: months, offset: offset, limit: 400 },
      success: function (res) {
        if (!res.ok) {
          statsRunning = false;
          $("#stat-note").text(res.message || "Stats failed");
          return;
        }
        if (offset === 0) {
          postsTotal = res.posts || 0;
          withFeatured = res.with_featured || 0;
          $("#stat-posts").text(postsTotal.toLocaleString());
          $("#stat-images").text(withFeatured.toLocaleString());
          syncDeleteSummary();
        }
        totalBytes += res.chunk_bytes || 0;
        totalFiles += res.chunk_files || 0;
        offset = res.next_offset || offset;
        $("#stat-size").text(formatBytes(totalBytes));
        var pct = postsTotal ? Math.min(99, Math.round((offset / postsTotal) * 100)) : 50;
        if (res.done) pct = 100;
        $("#stats-progress > span").css("width", pct + "%");
        $("#stat-note").text(
          res.done
            ? ("Ready. Up to " + postsTotal.toLocaleString() + " posts in this age filter will be processed; those with " + MIN_VIEWS_KEEP.toLocaleString() + "+ views are kept. ~" + totalFiles.toLocaleString() + " featured/video files on disk (~" + formatBytes(totalBytes) + ").")
            : ("Scanned " + offset.toLocaleString() + " / " + postsTotal.toLocaleString() + " posts…")
        );
        if (res.done) {
          statsRunning = false;
          syncDeleteSummary();
        } else {
          step();
        }
      },
      error: function () {
        statsRunning = false;
        $("#stat-note").text("Size scan failed — you can still delete using the post count above.");
        syncDeleteSummary();
      }
    });
  }
  step();
}

$(document).on("input change", "#confirm-delete", updateDeleteBtn);

$("#btn-preview").on("click", function () {
  $("#rowcount").remove();
  getresult();
});
$("#months").on("change", function () {
  previewReady = false;
  $("#rowcount").remove();
  $("#stat-posts").text("—");
  $("#delete-count").text("—");
  $("#confirm-delete").val("");
  syncDeleteSummary();
  getresult();
});

$("#btn-delete").on("click", function () {
  var n = matchCount();
  var months = $("#months").val();
  if (!previewReady || n <= 0 || $("#confirm-delete").val().trim() !== "DELETE") return;

  var msg =
    "CONFIRM DELETE\n\n" +
    "Posts to process: " + n.toLocaleString() + "\n" +
    "Filter: older than " + months + " months\n" +
    "Kept: any post with " + MIN_VIEWS_KEEP.toLocaleString() + "+ views\n" +
    "Disk estimate: " + $("#stat-size").text() + "\n\n" +
    "Only posts matching this filter are deleted.\n" +
    "This cannot be undone. Continue?";

  if (!window.confirm(msg)) return;

  var $log = $("#cleanup-log").show().empty();
  var totalFiles = 0, totalOk = 0, totalBytes = 0, totalSkipped = 0;
  var afterId = 0;
  var planned = n;
  deleteStop = false;
  $("#overlay").show();
  $("#btn-delete").prop("disabled", true);

  function next() {
    if (deleteStop) {
      $("#overlay").hide();
      return;
    }
    $.ajax({
      url: "ajax_cleanup_news.php",
      type: "POST",
      dataType: "json",
      data: {
        mode: "all",
        months: months,
        confirm: "CONFIRM",
        limit: 25,
        after_id: afterId
      },
      success: function (res) {
        totalOk += res.deleted || 0;
        totalSkipped += res.skipped || 0;
        totalFiles += res.files_removed || 0;
        totalBytes += res.bytes_freed || 0;
        if (res.after_id != null) afterId = res.after_id;
        var rem = res.remaining != null ? res.remaining : "?";
        $log.prepend(
          "<div>" + (res.message || "") +
          " | deleted " + totalOk.toLocaleString() + " / up to " + planned.toLocaleString() +
          " | freed " + formatBytes(totalBytes) + "</div>"
        );
        $("#stat-posts").text(Number(rem).toLocaleString());
        $("#delete-count").text(Number(rem).toLocaleString());
        if (res.done || rem === 0) {
          $log.prepend(
            "<div><b>Finished.</b> Deleted " + totalOk.toLocaleString() +
            ", kept (3,000+ views) " + totalSkipped.toLocaleString() +
            ", files " + totalFiles.toLocaleString() +
            ", freed " + formatBytes(totalBytes) + "</div>"
          );
          $("#confirm-delete").val("");
          $("#overlay").hide();
          getresult();
          return;
        }
        next();
      },
      error: function (xhr) {
        $log.prepend("<div style='color:#fca5a5'>Batch error — retrying in 2s… " + (xhr.responseText || "") + "</div>");
        setTimeout(next, 2000);
      }
    });
  }
  next();
});

$(function () { getresult(); });
</script>
</body>
</html>
