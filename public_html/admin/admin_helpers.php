<?php
/**
 * Shared admin helpers — escape HTML, category labels, CKEditor config.
 * Included from config.php so every admin page can use them.
 */
if (!function_exists('nm_h')) {
	function nm_h($v)
	{
		return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}

if (!function_exists('nm_cat_label')) {
	function nm_cat_label($row)
	{
		if (!is_array($row)) {
			return '';
		}
		if (!empty($row['hindi_name'])) {
			return (string) $row['hindi_name'];
		}
		if (!empty($row['maincat'])) {
			return (string) $row['maincat'];
		}
		return isset($row['id']) ? ('Cat #' . $row['id']) : '';
	}
}

/** Load categories with hindi_name preferred; falls back if columns differ. */
if (!function_exists('nm_categories_result')) {
	function nm_categories_result($con, $onlyWithUrl = false)
	{
		$urlSql = $onlyWithUrl ? " WHERE cat_url IS NOT NULL AND cat_url != ''" : '';
		$queries = array(
			"SELECT id, hindi_name, maincat, cat_url FROM categories{$urlSql} ORDER BY id ASC",
			"SELECT id, hindi_name, cat_url FROM categories{$urlSql} ORDER BY id ASC",
			"SELECT id, maincat, cat_url FROM categories{$urlSql} ORDER BY id ASC",
			"SELECT id, hindi_name, maincat FROM categories ORDER BY id ASC",
			"SELECT id, hindi_name FROM categories ORDER BY id ASC",
			"SELECT id, maincat FROM categories ORDER BY id ASC",
		);
		foreach ($queries as $sql) {
			$q = mysqli_query($con, $sql);
			if ($q instanceof mysqli_result) {
				return $q;
			}
		}
		return false;
	}
}

/** English slug for /category/{slug}. Does not rename existing rows. */
if (!function_exists('nm_category_slug')) {
	function nm_category_slug($raw)
	{
		$replace = array(" ",",",".","'","&","-","_",":","(",")","+",";","#","!","*","{","}","[","]","?","/","\"","|","@","%","$");
		$s = str_replace($replace, "-", trim((string) $raw));
		while (strpos($s, "--") !== false) {
			$s = str_replace("--", "-", $s);
		}
		return trim($s, "-");
	}
}

if (!function_exists('nm_category_letter')) {
	function nm_category_letter($slug)
	{
		$slug = (string) $slug;
		if ($slug === "") {
			return "";
		}
		return strtoupper(substr($slug, 0, 1));
	}
}

if (!function_exists('nm_category_url_taken')) {
	function nm_category_url_taken($con, $slug, $exceptId = 0)
	{
		$esc = mysqli_real_escape_string($con, $slug);
		$sql = "SELECT id FROM categories WHERE LOWER(cat_url) = LOWER('$esc')";
		if ((int) $exceptId > 0) {
			$sql .= " AND id != " . (int) $exceptId;
		}
		$sql .= " LIMIT 1";
		$q = @mysqli_query($con, $sql);
		return ($q instanceof mysqli_result) && $q->num_rows > 0;
	}
}

/** Strip CKEditor pastebin id so it never wraps a saved article as disposable markup. */
if (!function_exists('nm_clean_description_html')) {
	function nm_clean_description_html($html)
	{
		$html = (string) $html;
		$html = preg_replace('/\s*\bid\s*=\s*(["\']?)cke_pastebin\1/i', '', $html);
		return $html === null ? '' : $html;
	}
}

/**
 * Resolve publish mode from POST: now | schedule.
 * Returns array(status, pub_date_time, error|null)
 */
if (!function_exists('nm_resolve_publish_schedule')) {
	function nm_resolve_publish_schedule($post, $defaultPub = '')
	{
		$mode = isset($post['publish_mode']) ? trim((string) $post['publish_mode']) : 'now';
		if ($mode !== 'schedule') {
			$mode = 'now';
		}
		$raw = isset($post['pub_date_time']) ? trim((string) $post['pub_date_time']) : '';
		if ($raw === '' && $defaultPub !== '') {
			$raw = $defaultPub;
		}

		if ($mode === 'now') {
			$when = $raw !== '' ? $raw : date('Y-m-d H:i');
			$ts = strtotime(str_replace('T', ' ', $when));
			if ($ts === false) {
				$ts = time();
			}
			return array(
				'status' => 'Published',
				'pub_date_time' => date('Y-m-d H:i', $ts),
				'error' => null,
			);
		}

		if ($raw === '') {
			return array(
				'status' => 'Scheduled',
				'pub_date_time' => '',
				'error' => 'Choose a schedule date and time.',
			);
		}
		$ts = strtotime(str_replace('T', ' ', $raw));
		if ($ts === false) {
			return array(
				'status' => 'Scheduled',
				'pub_date_time' => '',
				'error' => 'Invalid schedule date/time.',
			);
		}
		if ($ts <= time()) {
			return array(
				'status' => 'Published',
				'pub_date_time' => date('Y-m-d H:i', $ts),
				'error' => null,
			);
		}
		return array(
			'status' => 'Scheduled',
			'pub_date_time' => date('Y-m-d H:i', $ts),
			'error' => null,
		);
	}
}

/**
 * Ensure admin login accounts support role + linked public Team profile.
 * Adds columns safely if missing. Never fatals if ALTER is denied.
 */
if (!function_exists('nm_ensure_admin_accounts')) {
	function nm_ensure_admin_accounts($con)
	{
		static $done = false;
		if ($done || !($con instanceof mysqli)) {
			return;
		}
		$done = true;

		$cols = nm_admin_column_map($con);
		try {
			if (empty($cols['role'])) {
				@mysqli_query(
					$con,
					"ALTER TABLE `admin` ADD COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'Admin'"
				);
			}
			$cols = nm_admin_column_map($con);
			if (empty($cols['team_id'])) {
				@mysqli_query(
					$con,
					"ALTER TABLE `admin` ADD COLUMN `team_id` INT(11) NOT NULL DEFAULT 0"
				);
			}
			$cols = nm_admin_column_map($con);
			if (!empty($cols['role'])) {
				@mysqli_query($con, "UPDATE `admin` SET `role`='Admin' WHERE `role`='' OR `role` IS NULL");
			}
		} catch (Throwable $e) {
			// ALTER may be denied on some hosts — keep CMS up; treat as Admin-only.
		}
	}
}

if (!function_exists('nm_admin_column_map')) {
	function nm_admin_column_map($con)
	{
		$cols = array();
		if (!($con instanceof mysqli)) {
			return $cols;
		}
		try {
			$q = @mysqli_query($con, "SHOW COLUMNS FROM `admin`");
			if ($q) {
				while ($row = mysqli_fetch_assoc($q)) {
					$cols[strtolower((string) $row['Field'])] = true;
				}
			}
		} catch (Throwable $e) {
			return $cols;
		}
		return $cols;
	}
}

if (!function_exists('nm_admin_row')) {
	function nm_admin_row($con, $email = null)
	{
		nm_ensure_admin_accounts($con);
		if ($email === null) {
			$email = isset($_SESSION['aemail']) ? (string) $_SESSION['aemail'] : '';
		}
		$email = trim($email);
		if ($email === '') {
			return null;
		}
		$esc = mysqli_real_escape_string($con, $email);
		try {
			$q = @mysqli_query($con, "SELECT * FROM `admin` WHERE `aemail`='$esc' LIMIT 1");
		} catch (Throwable $e) {
			return null;
		}
		if (!$q) {
			return null;
		}
		$row = mysqli_fetch_assoc($q);
		return $row ? $row : null;
	}
}

if (!function_exists('nm_admin_role')) {
	function nm_admin_role($con, $email = null)
	{
		$row = nm_admin_row($con, $email);
		if (!$row) {
			return 'Admin';
		}
		$role = isset($row['role']) ? trim((string) $row['role']) : 'Admin';
		return ($role === 'Author') ? 'Author' : 'Admin';
	}
}

if (!function_exists('nm_require_admin')) {
	function nm_require_admin($con)
	{
		if (nm_is_admin($con)) {
			return;
		}
		if (!headers_sent()) {
			header('Location: dashboard.php?denied=1');
		} else {
			echo '<script>location.replace("dashboard.php?denied=1");</script>';
		}
		exit;
	}
}

if (!function_exists('nm_is_admin')) {
	function nm_is_admin($con, $email = null)
	{
		return nm_admin_role($con, $email) === 'Admin';
	}
}

if (!function_exists('nm_admin_team_id')) {
	function nm_admin_team_id($con, $email = null)
	{
		$row = nm_admin_row($con, $email);
		return $row && isset($row['team_id']) ? (int) $row['team_id'] : 0;
	}
}

/** Display name, role, team id, and photo (team byline photo first, then admin profile/). */
if (!function_exists('nm_cms_identity')) {
	function nm_cms_identity($con, $userRow = null)
	{
		if (!is_array($userRow)) {
			$userRow = nm_admin_row($con);
		}
		$role = 'Admin';
		if (is_array($userRow) && isset($userRow['role']) && trim((string) $userRow['role']) === 'Author') {
			$role = 'Author';
		}
		$teamId = is_array($userRow) && isset($userRow['team_id']) ? (int) $userRow['team_id'] : 0;
		$teamName = '';
		$teamImg = '';
		if ($teamId > 0) {
			$tq = @mysqli_query($con, "SELECT `name`,`image` FROM `team` WHERE `t_id`='$teamId' LIMIT 1");
			if ($tq instanceof mysqli_result) {
				$tr = mysqli_fetch_assoc($tq);
				if (is_array($tr)) {
					$teamName = trim((string) ($tr['name'] ?? ''));
					$teamImg = trim((string) ($tr['image'] ?? ''));
				}
			}
		}
		$name = $teamName;
		if ($name === '' && is_array($userRow) && !empty($userRow['aname'])) {
			$name = (string) $userRow['aname'];
		}
		if ($name === '' && is_array($userRow) && !empty($userRow['aemail'])) {
			$name = (string) $userRow['aemail'];
		}
		if ($name === '') {
			$name = $role === 'Author' ? 'Author' : 'Admin';
		}
		$avatar = '';
		$publicDir = dirname(__DIR__);
		if ($teamImg !== '' && is_file($publicDir . '/team/' . $teamImg)) {
			$avatar = '../team/' . $teamImg;
		}
		if ($avatar === '' && is_array($userRow) && !empty($userRow['image'])) {
			$adminImg = (string) $userRow['image'];
			if (is_file(__DIR__ . '/profile/' . $adminImg)) {
				$avatar = 'profile/' . $adminImg;
			}
		}
		$initial = strtoupper(substr($name, 0, 1));
		if ($initial === '') {
			$initial = 'N';
		}
		return array(
			'name' => $name,
			'role' => $role,
			'is_admin' => ($role === 'Admin'),
			'team_id' => $teamId,
			'avatar' => $avatar,
			'initial' => $initial,
		);
	}
}

/** SQL fragment to limit news. Authors: own team_id. Admins: optional ?author=me|others|id */
if (!function_exists('nm_news_scope_clause')) {
	function nm_news_scope_clause($con)
	{
		$isAdmin = nm_is_admin($con);
		$mine = nm_admin_team_id($con);
		$author = '';
		if (isset($_GET['author'])) {
			$author = trim((string) $_GET['author']);
		} elseif (isset($_GET['search']['author'])) {
			$author = trim((string) $_GET['search']['author']);
		}
		if (!$isAdmin) {
			return $mine > 0 ? ("`team_id`='" . $mine . "'") : "`team_id`='-1'";
		}
		if ($author === 'me' && $mine > 0) {
			return "`team_id`='" . $mine . "'";
		}
		if ($author === 'others' && $mine > 0) {
			return "(`team_id` IS NULL OR `team_id`=0 OR `team_id`<>'" . $mine . "')";
		}
		if ($author !== '' && ctype_digit($author) && (int) $author > 0) {
			return "`team_id`='" . (int) $author . "'";
		}
		return '';
	}
}

if (!function_exists('nm_sql_and')) {
	function nm_sql_and(&$queryCondition, $clause)
	{
		$clause = trim((string) $clause);
		if ($clause === '') {
			return;
		}
		$queryCondition .= ($queryCondition === '' ? ' WHERE ' : ' AND ') . $clause;
	}
}

if (!function_exists('nm_can_manage_news')) {
	function nm_can_manage_news($con, $newsid)
	{
		$newsid = (int) $newsid;
		if ($newsid < 1) {
			return false;
		}
		if (nm_is_admin($con)) {
			return true;
		}
		$mine = nm_admin_team_id($con);
		if ($mine < 1) {
			return false;
		}
		$q = @mysqli_query($con, "SELECT `team_id` FROM `news` WHERE `newsid`='$newsid' LIMIT 1");
		$row = ($q instanceof mysqli_result) ? mysqli_fetch_assoc($q) : null;
		return is_array($row) && (int) $row['team_id'] === $mine;
	}
}

/**
 * Actual news_views totals for a small id list (current News page only).
 * Returns newsid => count, or null if the query failed.
 * @param int[] $ids
 * @return array<int,int>|null
 */
if (!function_exists('nm_page_view_counts')) {
	function nm_page_view_counts($con, array $ids)
	{
		$ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
		$out = array();
		foreach ($ids as $id) {
			$out[$id] = 0;
		}
		if (!$ids) {
			return $out;
		}
		$list = implode(',', $ids);
		$q = @mysqli_query(
			$con,
			"SELECT `newsid`, COUNT(*) AS c FROM `news_views` WHERE `newsid` IN ($list) GROUP BY `newsid`"
		);
		if (!($q instanceof mysqli_result)) {
			return null;
		}
		while ($row = mysqli_fetch_assoc($q)) {
			$out[(int) $row['newsid']] = (int) $row['c'];
		}
		return $out;
	}
}

/** Pick success vs error styling for admin dialogs. */
if (!function_exists('nm_notice_kind')) {
	function nm_notice_kind($message)
	{
		$s = strtolower((string) $message);
		if (preg_match('/sorry|error|fail|missing|not found|not correct|required|already in use/', $s)) {
			return 'error';
		}
		return 'success';
	}
}

/**
 * Replace native browser alert()+redirect with the shared admin dialog.
 * If $href is set and output has not started, this prints a small page and exits.
 */
if (!function_exists('nm_js_notice')) {
	function nm_js_notice($message, $href = '', $type = '')
	{
		$kind = $type !== '' ? $type : nm_notice_kind($message);
		$payload = json_encode(array(
			'message' => (string) $message,
			'href' => (string) $href,
			'type' => (string) $kind,
		), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$css = 'css/admin-modern.css?v=22';
		$js = 'js/nm-dialog.js?v=1';
		if ($href !== '' && !headers_sent()) {
			echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin</title>';
			echo '<link rel="stylesheet" href="' . $css . '"></head><body class="nm-dialog-page">';
			echo '<script src="' . $js . '"></script>';
			echo '<script>window.nmReadyNotice(' . $payload . ');</script></body></html>';
			exit;
		}
		echo '<link rel="stylesheet" href="' . $css . '">';
		echo '<script src="' . $js . '"></script>';
		echo '<script>window.nmReadyNotice(' . $payload . ');</script>';
	}
}

/** Relative CKEditor filebrowser config (works when $urlroot is wrong on production). */
if (!function_exists('nm_ckeditor_js')) {
	function nm_ckeditor_js($fieldId = 'description')
	{
		$id = json_encode((string) $fieldId);
		return <<<JS
CKEDITOR.replace({$id}, {
  width: '100%',
  filebrowserBrowseUrl: 'ckeditor/filemanager/browser/default/browser.html?Connector=ckeditor/filemanager/connectors/php/connector.php',
  filebrowserImageBrowseUrl: 'ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector=ckeditor/filemanager/connectors/php/connector.php',
  filebrowserFlashBrowseUrl: 'ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector=ckeditor/filemanager/connectors/php/connector.php',
  filebrowserUploadUrl: 'ckeditor/filemanager/connectors/php/upload.php?Type=File',
  filebrowserImageUploadUrl: 'ckeditor/filemanager/connectors/php/upload.php?Type=Image',
  filebrowserFlashUploadUrl: 'ckeditor/filemanager/connectors/php/upload.php?Type=Flash'
});
JS;
	}
}
