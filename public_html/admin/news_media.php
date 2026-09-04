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
    try {
        $q = mysqli_query($con, "SELECT COUNT(*) AS c FROM `news_views` WHERE `newsid`='$newsid'");
        $r = $q ? mysqli_fetch_assoc($q) : null;
        return $r ? (int) $r["c"] : 0;
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * Batch view counts for a small id list (page / delete batch). Fast with index on news_views.newsid.
 * Do NOT use correlated COUNT(*) across all old news — that hangs Apache on large news_views.
 * @param int[] $ids
 * @return array<int,int> newsid => view count
 */
function nm_batch_view_counts($con, array $ids) {
    $out = array();
    $ids = array_values(array_unique(array_filter(array_map("intval", $ids))));
    if (!$ids) {
        return $out;
    }
    $in = implode(",", $ids);
    try {
        $q = mysqli_query($con, "SELECT `newsid`, COUNT(*) AS c FROM `news_views` WHERE `newsid` IN ($in) GROUP BY `newsid`");
        while ($q && ($r = mysqli_fetch_assoc($q))) {
            $out[(int) $r["newsid"]] = (int) $r["c"];
        }
    } catch (Throwable $e) {
        // missing table / error → treat as 0 views
    }
    foreach ($ids as $id) {
        if (!isset($out[$id])) {
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
 * @return array{ok:bool, message:string, files_removed:int, newsid:int}
 */
function nm_delete_news_article($con, $newsid) {
    $newsid = (int) $newsid;
    if ($newsid <= 0) {
        return array("ok" => false, "message" => "Invalid id", "files_removed" => 0, "bytes_freed" => 0, "newsid" => 0);
    }

    $res = mysqli_query($con, "SELECT `newsid`,`image`,`description`,`video_file`,`newsurl` FROM `news` WHERE `newsid`='$newsid' LIMIT 1");
    $row = $res ? mysqli_fetch_assoc($res) : null;
    if (!$row) {
        return array("ok" => false, "message" => "Not found", "files_removed" => 0, "bytes_freed" => 0, "newsid" => $newsid);
    }

    // Hard rule: 3000+ views → never delete (any age)
    $views = nm_news_view_count($con, $newsid);
    if ($views >= nm_min_views_keep()) {
        return array(
            "ok" => false,
            "message" => "Protected: news #$newsid has $views views (keep ≥ " . nm_min_views_keep() . ")",
            "files_removed" => 0,
            "bytes_freed" => 0,
            "newsid" => $newsid,
        );
    }

    $analysis = nm_analyze_media($row["image"], $row["description"], $row["video_file"]);
    $removed = 0;
    $bytes = 0;
    foreach ($analysis["disk_files"] as $rel) {
        $freed = nm_safe_unlink($rel);
        if ($freed > 0) {
            $removed++;
            $bytes += $freed;
        }
    }

    @mysqli_query($con, "DELETE FROM `news_cat` WHERE `news_id`='$newsid'");
    @mysqli_query($con, "DELETE FROM `comments` WHERE `newsid`='$newsid'");
    @mysqli_query($con, "DELETE FROM `news_views` WHERE `newsid`='$newsid'");

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
