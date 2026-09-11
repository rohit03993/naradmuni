<?php
/**
 * Shared helpers for counting / deleting news media safely.
 * Only touches files under images/news, userfiles, videos.
 */

function nm_project_root() {
    return realpath(__DIR__ . "/..");
}

function nm_file_bytes($relativeFromPublicHtml) {
    $root = nm_project_root();
    if (!$root) {
        return 0;
    }
    $relativeFromPublicHtml = str_replace("\\", "/", $relativeFromPublicHtml);
    $relativeFromPublicHtml = ltrim($relativeFromPublicHtml, "/");
    if (!preg_match('#^(images/news|userfiles|videos)/#i', $relativeFromPublicHtml)) {
        return 0;
    }
    if (strpos($relativeFromPublicHtml, "..") !== false) {
        return 0;
    }
    $full = $root . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $relativeFromPublicHtml);
    if (is_file($full)) {
        return (int) @filesize($full);
    }
    return 0;
}

function nm_format_bytes($bytes) {
    $bytes = (float) $bytes;
    if ($bytes < 1024) {
        return round($bytes) . " B";
    }
    if ($bytes < 1048576) {
        return round($bytes / 1024, 1) . " KB";
    }
    if ($bytes < 1073741824) {
        return round($bytes / 1048576, 2) . " MB";
    }
    return round($bytes / 1073741824, 2) . " GB";
}

/** Popular posts (by news_views) are never cleaned up, regardless of age. */
function nm_min_views_keep() {
    return 3000;
}

function nm_news_view_count($con, $newsid) {
    $newsid = (int) $newsid;
    $cap = nm_min_views_keep();
    // Stop at keep-threshold (avoids counting millions of rows for popular posts)
    try {
        $q = mysqli_query($con, "SELECT 1 FROM `news_views` WHERE `newsid`='$newsid' LIMIT $cap");
        $n = 0;
        while ($q && mysqli_fetch_row($q)) {
            $n++;
        }
        return $n;
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * Batch view counts capped at keep-threshold (enough to decide KEEP vs delete).
 * @param int[] $ids
 * @return array<int,int>
 */
function nm_batch_view_counts($con, array $ids) {
    $out = array();
    $ids = array_values(array_unique(array_filter(array_map("intval", $ids))));
    $cap = nm_min_views_keep();
    foreach ($ids as $id) {
        $out[$id] = 0;
    }
    if (!$ids) {
        return $out;
    }
    // Per-id capped read is safer than GROUP BY COUNT(*) on huge table without index
    foreach ($ids as $id) {
        try {
            $q = mysqli_query($con, "SELECT 1 FROM `news_views` WHERE `newsid`='$id' LIMIT $cap");
            $n = 0;
            while ($q && mysqli_fetch_row($q)) {
                $n++;
            }
            $out[$id] = $n;
        } catch (Throwable $e) {
            $out[$id] = 0;
        }
    }
    return $out;
}

function nm_is_view_protected($views) {
    return (int) $views >= nm_min_views_keep();
}

/**
 * Age-only filter (table `news`, no alias).
 * View protection is enforced in PHP (batch counts + nm_delete_news_article) — not in SQL.
 */
function nm_age_where_sql($months) {
    $months = max(1, (int) $months);
    return "STR_TO_DATE(`date`,'%d-%m-%Y') IS NOT NULL"
        . " AND STR_TO_DATE(`date`,'%d-%m-%Y') < DATE_SUB(CURDATE(), INTERVAL $months MONTH)";
}

/**
 * Bytes for featured + video only (fast). Body embeds skipped for stats speed.
 */
function nm_featured_bytes($image, $videoFile = "") {
    $bytes = 0;
    $image = trim((string) $image);
    if ($image !== "" && $image !== "null") {
        $bytes += nm_file_bytes("images/news/" . basename($image));
    }
    $videoFile = trim((string) $videoFile);
    if ($videoFile !== "" && $videoFile !== "null") {
        $bytes += nm_file_bytes("videos/" . basename($videoFile));
    }
    return $bytes;
}

function nm_safe_unlink($relativeFromPublicHtml) {
    $root = nm_project_root();
    if (!$root) {
        return 0;
    }
    $relativeFromPublicHtml = str_replace("\\", "/", $relativeFromPublicHtml);
    $relativeFromPublicHtml = ltrim($relativeFromPublicHtml, "/");
    if (!preg_match('#^(images/news|userfiles|videos)/#i', $relativeFromPublicHtml)) {
        return 0;
    }
    if (strpos($relativeFromPublicHtml, "..") !== false) {
        return 0;
    }
    $full = $root . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $relativeFromPublicHtml);
    $real = realpath($full);
    if ($real === false || strpos($real, $root) !== 0) {
        return 0;
    }
    if (is_file($real)) {
        $sz = (int) @filesize($real);
        if (@unlink($real)) {
            return $sz;
        }
    }
    return 0;
}

/**
 * @return array{disk_files:string[], featured:?string, video:?string, base64_embeds:int, disk_count:int, total_display:int}
 */
function nm_analyze_media($image, $description, $videoFile = "") {
    $disk = array();
    $featured = null;
    $video = null;
    $base64 = 0;

    $image = trim((string) $image);
    if ($image !== "" && $image !== "null") {
        $featured = $image;
        $disk["images/news/" . basename($image)] = true;
    }

    $videoFile = trim((string) $videoFile);
    if ($videoFile !== "" && $videoFile !== "null") {
        $video = $videoFile;
        $disk["videos/" . basename($videoFile)] = true;
    }

    $html = (string) $description;
    if ($html !== "") {
        if (preg_match_all('/data:image\/[a-zA-Z0-9+.-]+;base64,/i', $html, $m)) {
            $base64 = count($m[0]);
        }
        // src="/.../images/news/file.jpg" or images/news/file.jpg
        if (preg_match_all('/(?:src|href)=["\']([^"\']*?(?:images\/news|userfiles)\/[^"\']+)["\']/i', $html, $m2)) {
            foreach ($m2[1] as $url) {
                $url = str_replace("\\", "/", $url);
                if (preg_match('#((?:images/news|userfiles)/[^?#]+)#i', $url, $mm)) {
                    $rel = $mm[1];
                    // strip any accidental leading path junk
                    $rel = preg_replace('#^.*?((?:images/news|userfiles)/)#i', '$1', $rel);
                    $disk[$rel] = true;
                }
            }
        }
        // url(...images/news/...)
        if (preg_match_all('/url\((["\']?)([^)\'"]*?(?:images\/news|userfiles)\/[^)\'"]+)\1\)/i', $html, $m3)) {
            foreach ($m3[2] as $url) {
                $url = str_replace("\\", "/", $url);
                if (preg_match('#((?:images/news|userfiles)/[^?#]+)#i', $url, $mm)) {
                    $rel = preg_replace('#^.*?((?:images/news|userfiles)/)#i', '$1', $mm[1]);
                    $disk[$rel] = true;
                }
            }
        }
    }

    $files = array_keys($disk);
    $diskCount = count($files);
    return array(
        "disk_files" => $files,
        "featured" => $featured,
        "video" => $video,
        "base64_embeds" => $base64,
        "disk_count" => $diskCount,
        "total_display" => $diskCount + ($base64 > 0 ? $base64 : 0),
    );
}

function nm_format_media_badge($analysis) {
    $parts = array();
    $parts[] = (int) $analysis["disk_count"] . " file" . ($analysis["disk_count"] == 1 ? "" : "s");
    if (!empty($analysis["base64_embeds"])) {
        $parts[] = (int) $analysis["base64_embeds"] . " base64";
    }
    return implode(" · ", $parts);
}

/**
 * Delete one news article + related rows + media files on disk.
 * @param mixed $knownViews null = count views; int = use this count; true skip_view_check via $opts
 * @param array $opts skip_view_check=true for intentional admin trash (fast; cleanup tool keeps protection)
 * @return array{ok:bool, message:string, files_removed:int, newsid:int}
 */
function nm_delete_news_article($con, $newsid, $knownViews = null, $opts = array()) {
    $newsid = (int) $newsid;
    if ($newsid <= 0) {
        return array("ok" => false, "message" => "Invalid id", "files_removed" => 0, "bytes_freed" => 0, "newsid" => 0);
    }

    $res = mysqli_query($con, "SELECT `newsid`,`image`,`video_file`,`newsurl` FROM `news` WHERE `newsid`='$newsid' LIMIT 1");
    $row = $res ? mysqli_fetch_assoc($res) : null;
    if (!$row) {
        return array("ok" => false, "message" => "Not found", "files_removed" => 0, "bytes_freed" => 0, "newsid" => $newsid);
    }

    $skipViewCheck = !empty($opts["skip_view_check"]);
    if (!$skipViewCheck) {
        // Hard rule for bulk cleanup: 3000+ views → never delete
        $views = ($knownViews !== null) ? (int) $knownViews : nm_news_view_count($con, $newsid);
        if ($views >= nm_min_views_keep()) {
            return array(
                "ok" => false,
                "message" => "Protected: news #$newsid has $views views (keep ≥ " . nm_min_views_keep() . ")",
                "files_removed" => 0,
                "bytes_freed" => 0,
                "newsid" => $newsid,
            );
        }
    }

    // Fast path: featured image + video only (skip parsing huge HTML bodies)
    $removed = 0;
    $bytes = 0;
    $image = trim((string) $row["image"]);
    if ($image !== "" && $image !== "null") {
        $freed = nm_safe_unlink("images/news/" . basename($image));
        if ($freed > 0) {
            $removed++;
            $bytes += $freed;
        }
    }
    $videoFile = trim((string) (isset($row["video_file"]) ? $row["video_file"] : ""));
    if ($videoFile !== "" && $videoFile !== "null") {
        $freed = nm_safe_unlink("videos/" . basename($videoFile));
        if ($freed > 0) {
            $removed++;
            $bytes += $freed;
        }
    }

    @mysqli_query($con, "DELETE FROM `news_cat` WHERE `news_id`='$newsid'");
    @mysqli_query($con, "DELETE FROM `comments` WHERE `newsid`='$newsid'");
    // Skip per-row DELETE on news_views (millions of rows, often no index) — orphans cleaned later via CLI/orphan purge.

    $ex = mysqli_query($con, "DELETE FROM `news` WHERE `newsid`='$newsid' LIMIT 1");
    if ($ex) {
        return array(
            "ok" => true,
            "message" => "Deleted news #" . $newsid . " (" . $row["newsurl"] . "), removed $removed file(s), " . nm_format_bytes($bytes),
            "files_removed" => $removed,
            "bytes_freed" => $bytes,
            "newsid" => $newsid,
        );
    }
    return array("ok" => false, "message" => "DB delete failed: " . mysqli_error($con), "files_removed" => $removed, "bytes_freed" => $bytes, "newsid" => $newsid);
}
