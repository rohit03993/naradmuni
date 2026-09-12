<?php
include "config.php";

nm_require_admin($con);

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
    .cleanup-danger {
      background:#f8d7da; border:2px solid #c62828; padding:16px 18px; border-radius:8px; margin:16px 0;
      position: sticky; top: 64px; z-index: 50;
    }
    .cleanup-stats { background:#e8f5e9; border:1px solid #a5d6a7; padding:14px 16px; border-radius:6px; margin-bottom:16px; }
    .cleanup-stats .big { font-size:22px; font-weight:700; margin-right:6px; }
    #cleanup-log { max-height:220px; overflow:auto; font-size:13px; background:#111; color:#d1fae5; padding:10px; border-radius:6px; display:none; margin-top:12px; }
    .delete-count { font-size:18px; font-weight:700; }
    #btn-delete {
      display: inline-block !important;
      visibility: visible !important;
      opacity: 1 !important;
      min-width: 220px;
      padding: 12px 18px !important;
      font-size: 1rem !important;
      font-weight: 700 !important;
    }
    #btn-delete:disabled {
      opacity: 0.55 !important;
      cursor: not-allowed;
    }
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
        <strong>Important:</strong> For large deletes use PowerShell CLI (does not hang Apache):
        <br><code>C:\xampp\php\php.exe public_html\admin\cli_cleanup_old_news.php --months=12 --fast</code>
        <br>This web page is for preview only. Posts with 3,000+ views are only protected in non-fast CLI / normal delete.
      </div>

      <div class="row" style="margin-bottom:12px;">
        <div class="col-md-3">
          <label><strong>1. Older than</strong></label>
          <select id="months" class="custom-select">
            <?php foreach (array(3, 6, 12, 18, 24) as $m) { ?>
              <option value="<?php echo $m; ?>" <?php echo $m === $months ? "selected" : ""; ?>><?php echo $m; ?> months</option>
            <?php } ?>
          </select>
        </div>
        <div class="col-md-9" style="padding-top:28px;">
          <button type="button" class="btn btn-primary" id="btn-preview"><i class="fas fa-search"></i> 2. Preview (count only)</button>
          <button type="button" class="btn btn-outline-secondary" id="btn-size">Optional: estimate disk size</button>
          <a href="news.php" class="btn btn-link">← Back</a>
        </div>
      </div>

      <div class="cleanup-stats" id="stats-box">
        <div><span class="big" id="stat-posts">—</span> posts match this age filter</div>
        <div><span class="big" id="stat-size">—</span> estimated disk (optional)</div>
        <div class="text-muted" style="font-size:12px;margin-top:6px;" id="stat-note">Click Preview to load the post count (fast). Size estimate is optional and slow.</div>
      </div>

      <div class="cleanup-danger" id="delete-box">
        <p style="margin-bottom:8px;"><strong>3. Delete matching posts (fast batches)</strong></p>
        <p class="mb-2">
          Will process up to <span class="delete-count" id="delete-count">—</span>
          posts older than <strong id="delete-months">?</strong> months.
          Keep <?php echo number_format($minViews); ?>+ views. Featured image + DB row deleted (body embeds skipped for speed).
        </p>
        <label>Type <code>DELETE</code> then click the red button</label>
        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-top:8px;">
          <input type="text" id="confirm-delete" class="form-control" placeholder="DELETE" autocomplete="off" style="max-width:180px;">
          <button type="button" class="btn btn-danger" id="btn-delete" disabled>
            <i class="fas fa-trash-alt"></i> Delete matching posts
          </button>
        </div>
        <small class="text-muted d-block mt-2">Keep this tab open. Already-deleted posts stay deleted if you stop.</small>
        <div id="cleanup-log"></div>
      </div>

      <h5 style="margin-top:8px;">Matching posts (review only)</h5>
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
var statsAbort = false;
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
  var fromStat = Number(String($("#stat-posts").text()).replace(/,/g, ""));
  if (fromStat > 0) return fromStat;
  return Number($("#rowcount").val()) || 0;
}

function syncDeleteSummary() {
  var n = matchCount();
  $("#delete-count").text(n ? n.toLocaleString() : "—");
  $("#delete-months").text($("#months").val() || "?");
  updateDeleteBtn();
}

function updateDeleteBtn() {
  var n = matchCount();
  var typed = $("#confirm-delete").val().trim() === "DELETE";
  var ok = n > 0 && typed;
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
      page: 1
    },
    success: function (data) {
      $("#pagination-result").html(data);
      $("#overlay").hide();
      previewReady = true;
      var n = Number($("#rowcount").val()) || 0;
      $("#stat-posts").text(n.toLocaleString());
      $("#stat-note").text("Count ready. Type DELETE and click the red button. (Optional size scan is separate.)");
      syncDeleteSummary();
    },
    error: function () {
      $("#overlay").hide();
      alert("Failed to load preview");
    }
  });
}

function startStatsScan() {
  if (statsRunning) return;
  if (!matchCount()) {
    alert("Click Preview first.");
    return;
  }
  statsRunning = true;
  statsAbort = false;
  var months = $("#months").val();
  var totalBytes = 0;
  var offset = 0;
  var postsTotal = matchCount();
  $("#stat-size").text("scanning…");
  $("#stat-note").text("Optional size scan…");

  function step() {
    if (statsAbort) {
      statsRunning = false;
      return;
    }
    $.ajax({
      url: "ajax_cleanup_stats.php",
      type: "POST",
      dataType: "json",
      data: { months: months, offset: offset, limit: 500 },
      success: function (res) {
        if (statsAbort || !res.ok) {
          statsRunning = false;
          return;
        }
        totalBytes += res.chunk_bytes || 0;
        offset = res.next_offset || offset;
        $("#stat-size").text(formatBytes(totalBytes));
        $("#stat-note").text(res.done ? ("Size estimate done: " + formatBytes(totalBytes)) : ("Size scan " + offset.toLocaleString() + " / " + postsTotal.toLocaleString()));
        if (res.done) statsRunning = false;
        else step();
      },
      error: function () {
        statsRunning = false;
        $("#stat-note").text("Size scan failed — you can still delete.");
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
$("#btn-size").on("click", startStatsScan);
$("#months").on("change", function () {
  previewReady = false;
  $("#rowcount").remove();
  $("#stat-posts").text("—");
  $("#stat-size").text("—");
  $("#confirm-delete").val("");
  syncDeleteSummary();
  getresult();
});

$("#btn-delete").on("click", function () {
  var n = matchCount();
  var months = $("#months").val();
  if (n <= 0 || $("#confirm-delete").val().trim() !== "DELETE") {
    alert("Preview first, then type DELETE exactly.");
    return;
  }

  if (!window.confirm(
    "DELETE up to " + n.toLocaleString() + " posts older than " + months + " months?\n\n" +
    "Keep " + MIN_VIEWS_KEEP.toLocaleString() + "+ view posts.\nThis cannot be undone."
  )) return;

  statsAbort = true;
  statsRunning = false;

  var $log = $("#cleanup-log").show().empty();
  var totalFiles = 0, totalOk = 0, totalBytes = 0, totalSkipped = 0;
  var afterId = 0;
  var planned = n;
  var failStreak = 0;
  var startedAt = Date.now();
  deleteStop = false;
  $("#overlay").hide();
  $("#btn-delete").prop("disabled", true);

  function updateStatus() {
    var rate = totalOk / Math.max(0.1, (Date.now() - startedAt) / 1000);
    var left = Math.max(0, planned - totalOk - totalSkipped);
    var etaMin = rate > 0 ? Math.round(left / rate / 60) : "?";
    $("#delete-count").text(left.toLocaleString());
    $("#stat-posts").text(left.toLocaleString());
    $("#stat-note").html(
      "<b style='color:#c62828'>RUNNING</b> · deleted <b>" + totalOk.toLocaleString() +
      "</b> · kept <b>" + totalSkipped.toLocaleString() +
      "</b> · left ~<b>" + left.toLocaleString() +
      "</b> · freed <b>" + formatBytes(totalBytes) +
      "</b> · ~" + rate.toFixed(1) + "/sec · ETA ~" + etaMin + " min"
    );
  }

  function next() {
    if (deleteStop) {
      $("#btn-delete").prop("disabled", false);
      return;
    }
    $.ajax({
      url: "ajax_cleanup_news.php",
      type: "POST",
      dataType: "json",
      timeout: 180000,
      data: {
        mode: "all",
        months: months,
        confirm: "CONFIRM",
        limit: 10,
        after_id: afterId
      },
      success: function (res) {
        failStreak = 0;
        totalOk += res.deleted || 0;
        totalSkipped += res.skipped || 0;
        totalFiles += res.files_removed || 0;
        totalBytes += res.bytes_freed || 0;
        if (res.after_id != null) afterId = res.after_id;
        updateStatus();
        $log.prepend("<div>" + (res.message || "") + " | total " + totalOk.toLocaleString() + "</div>");
        if (res.done) {
          $log.prepend("<div><b>Finished.</b> Deleted " + totalOk.toLocaleString() + ", kept " + totalSkipped.toLocaleString() + ", freed " + formatBytes(totalBytes) + "</div>");
          $("#confirm-delete").val("");
          $("#stat-note").text("Cleanup finished.");
          $("#btn-delete").prop("disabled", false);
          getresult();
          return;
        }
        setTimeout(next, 50);
      },
      error: function (xhr) {
        failStreak++;
        $log.prepend("<div style='color:#fca5a5'>Error " + failStreak + "/5 — " + (xhr.status || "") + "</div>");
        if (failStreak >= 5) {
          deleteStop = true;
          $("#btn-delete").prop("disabled", false);
          $log.prepend("<div style='color:#fca5a5'><b>Stopped.</b> Restart Apache, refresh, run again. Already deleted: " + totalOk.toLocaleString() + "</div>");
          return;
        }
        setTimeout(next, 3000);
      }
    });
  }
  next();
});

$(function () { getresult(); });
</script>
</body>
</html>
