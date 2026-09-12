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
 * Adds columns safely if missing (no manual SQL required on deploy).
 */
if (!function_exists('nm_ensure_admin_accounts')) {
	function nm_ensure_admin_accounts($con)
	{
		static $done = false;
		if ($done || !($con instanceof mysqli)) {
			return;
		}
		$done = true;
		$cols = array();
		$q = mysqli_query($con, "SHOW COLUMNS FROM `admin`");
		if ($q) {
			while ($row = mysqli_fetch_assoc($q)) {
				$cols[strtolower((string) $row['Field'])] = true;
			}
		}
		if (empty($cols['role'])) {
			mysqli_query(
				$con,
				"ALTER TABLE `admin` ADD COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'Admin' AFTER `apwd`"
			);
		}
		if (empty($cols['team_id'])) {
			mysqli_query(
				$con,
				"ALTER TABLE `admin` ADD COLUMN `team_id` INT(11) NOT NULL DEFAULT 0 AFTER `role`"
			);
		}
		// Existing single account stays Admin
		mysqli_query($con, "UPDATE `admin` SET `role`='Admin' WHERE `role`='' OR `role` IS NULL");
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
		$q = mysqli_query($con, "SELECT * FROM `admin` WHERE `aemail`='$esc' LIMIT 1");
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

/** Block Authors from Admin-only pages. */
if (!function_exists('nm_require_admin')) {
	function nm_require_admin($con)
	{
		if (nm_is_admin($con)) {
			return;
		}
		header('Location: dashboard.php?denied=1');
		exit;
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
