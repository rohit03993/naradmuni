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

/** One pinned homepage lead. Stored in site_settings, not on the news table. */
if (!function_exists('nm_homepage_main_newsid')) {
	function nm_homepage_main_newsid($con)
	{
		if (!($con instanceof mysqli)) {
			return 0;
		}
		if (!function_exists('nm_setting_get')) {
			require_once __DIR__ . '/site_settings_lib.php';
		}
		nm_ensure_site_settings($con);
		return (int) nm_setting_get($con, 'homepage_main_newsid', '0');
	}
}

if (!function_exists('nm_is_homepage_main_news')) {
	function nm_is_homepage_main_news($con, $newsid)
	{
		$id = (int) $newsid;
		return $id > 0 && nm_homepage_main_newsid($con) === $id;
	}
}

if (!function_exists('nm_apply_homepage_main_news')) {
	function nm_apply_homepage_main_news($con, $newsid, $wantPin)
	{
		if (!($con instanceof mysqli)) {
			return;
		}
		$id = (int) $newsid;
		if ($id <= 0) {
			return;
		}
		if (!function_exists('nm_setting_set')) {
			require_once __DIR__ . '/site_settings_lib.php';
		}
		nm_ensure_site_settings($con);
		if ($wantPin) {
			nm_setting_set($con, 'homepage_main_newsid', (string) $id);
			return;
		}
		if (nm_homepage_main_newsid($con) === $id) {
			nm_setting_set($con, 'homepage_main_newsid', '');
		}
	}
}

/** Dark title colours that stay readable on a white page. */
if (!function_exists('nm_title_allowed_colors')) {
	function nm_title_allowed_colors()
	{
		return array(
			'#111111' => 'Black',
			'#b91c1c' => 'Red',
			'#c2410c' => 'Saffron',
			'#1d4ed8' => 'Blue',
			'#15803d' => 'Green',
			'#6d28d9' => 'Purple',
		);
	}
}

if (!function_exists('nm_title_allowed_highlights')) {
	function nm_title_allowed_highlights()
	{
		return array(
			'#fde047' => 'Yellow',
			'#86efac' => 'Green',
			'#f9a8d4' => 'Pink',
			'#fdba74' => 'Orange',
			'#7dd3fc' => 'Sky',
		);
	}
}

if (!function_exists('nm_plain_title')) {
	function nm_plain_title($html)
	{
		$t = html_entity_decode(strip_tags(str_replace(array('<br>', '<br/>', '<br />', '<br/>'), ' ', (string) $html)), ENT_QUOTES, 'UTF-8');
		$t = str_replace("\xc2\xa0", ' ', $t);
		$t = preg_replace('/\s+/u', ' ', $t);
		return trim((string) $t);
	}
}

if (!function_exists('nm_title_nearest_hex')) {
	function nm_title_nearest_hex($raw, $allowed, $fallback = '')
	{
		$raw = strtolower(trim((string) $raw));
		$hex = '';
		if (preg_match('/rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/', $raw, $m)) {
			$hex = sprintf('#%02x%02x%02x', (int) $m[1], (int) $m[2], (int) $m[3]);
		} elseif (preg_match('/^#([0-9a-f]{3})$/', $raw, $m)) {
			$h = $m[1];
			$hex = '#' . $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
		} elseif (preg_match('/^#([0-9a-f]{6})$/', $raw)) {
			$hex = $raw;
		}
		if ($hex === '') {
			return $fallback;
		}
		$best = $fallback !== '' ? $fallback : reset($allowed);
		$bestD = 99999;
		foreach ($allowed as $a) {
			$d = abs(hexdec(substr($hex, 1, 2)) - hexdec(substr($a, 1, 2)))
				+ abs(hexdec(substr($hex, 3, 2)) - hexdec(substr($a, 3, 2)))
				+ abs(hexdec(substr($hex, 5, 2)) - hexdec(substr($a, 5, 2)));
			if ($d < $bestD) {
				$bestD = $d;
				$best = $a;
			}
		}
		return $best;
	}
}

if (!function_exists('nm_title_color_to_allowed')) {
	function nm_title_color_to_allowed($raw)
	{
		return nm_title_nearest_hex($raw, array_keys(nm_title_allowed_colors()), '#111111');
	}
}

if (!function_exists('nm_title_highlight_to_allowed')) {
	function nm_title_highlight_to_allowed($raw)
	{
		return nm_title_nearest_hex($raw, array_keys(nm_title_allowed_highlights()), '');
	}
}

if (!function_exists('nm_sanitize_title_html')) {
	function nm_sanitize_title_html($html)
	{
		$html = (string) $html;
		if (trim($html) === '') {
			return '';
		}
		$html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
		$html = preg_replace('/<font([^>]*)>/i', '<span$1>', $html);
		$html = str_ireplace('</font>', '</span>', $html);
		$html = strip_tags($html, '<span>');
		for ($i = 0; $i < 8; $i++) {
			$html = preg_replace_callback('/<span\b([^>]*)>([^<]*)<\/span>/i', function ($m) {
				$bg = '';
				$fg = '';
				if (preg_match('/background-color\s*:\s*([^;"]+)/i', $m[1], $bm)) {
					$bg = nm_title_highlight_to_allowed($bm[1]);
				}
				if (preg_match('/(?:^|;|\s)color\s*:\s*([^;"]+)/i', $m[1], $cm)) {
					$fg = nm_title_color_to_allowed($cm[1]);
				} elseif (preg_match('/(?:^|\s)color\s*=\s*["\']?([^"\'\s>]+)/i', $m[1], $cm)) {
					$fg = nm_title_color_to_allowed($cm[1]);
				}
				$text = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
				$bits = array();
				if ($fg !== '' && $fg !== '#111111') {
					$bits[] = 'color:' . $fg;
				}
				if ($bg !== '') {
					$bits[] = 'background-color:' . $bg;
				}
				if (!$bits) {
					return $text;
				}
				return '<span style="' . implode(';', $bits) . '">' . $text . '</span>';
			}, $html);
		}
		$out = '';
		$offset = 0;
		if (preg_match_all('/<span style="(?:color:#[0-9a-f]{6};)?(?:background-color:#[0-9a-f]{6})?">.*?<\/span>/i', $html, $mm, PREG_OFFSET_CAPTURE)) {
			foreach ($mm[0] as $hit) {
				$pos = (int) $hit[1];
				$out .= htmlspecialchars(substr($html, $offset, $pos - $offset), ENT_QUOTES, 'UTF-8');
				$out .= $hit[0];
				$offset = $pos + strlen($hit[0]);
			}
		}
		$out .= htmlspecialchars(substr($html, $offset), ENT_QUOTES, 'UTF-8');
		return $out;
	}
}

if (!function_exists('nm_title_color_ui')) {
	function nm_title_color_ui($value = '')
	{
		$safe = nm_sanitize_title_html($value);
		$buttons = '';
		foreach (nm_title_allowed_colors() as $hex => $label) {
			$buttons .= '<button type="button" class="nm-title-swatch" data-nm-title-color="' . $hex . '" title="' . nm_h($label) . '" style="background:' . $hex . '"></button>';
		}
		$hl = '';
		foreach (nm_title_allowed_highlights() as $hex => $label) {
			$hl .= '<button type="button" class="nm-title-swatch nm-title-swatch--hl" data-nm-title-hl="' . $hex . '" title="' . nm_h($label . ' highlight') . '" style="background:' . $hex . '"></button>';
		}
		return '<div class="nm-title-color">'
			. '<div class="nm-title-color-bar"><span>Text colour:</span>'
			. $buttons
			. '<button type="button" class="btn btn-outline-secondary btn-sm" id="nm-title-color-clear">Remove colour</button>'
			. '</div>'
			. '<div class="nm-title-color-bar"><span>Highlighter (optional):</span>'
			. $hl
			. '</div>'
			. '<div id="nm-title-editor" class="form-control nm-title-editor" contenteditable="true" role="textbox">' . $safe . '</div>'
			. '<input type="hidden" name="title" id="nm-title" value="' . nm_h($safe) . '">'
			. '<p class="nm-form-hint">Select words, then a colour and/or highlighter. Highlighter paints behind the words. Google and WhatsApp still get the plain title.</p>'
			. '</div>';
	}
}

if (!function_exists('nm_title_color_js')) {
	function nm_title_color_js()
	{
		return <<<'JS'
(function () {
  var ed = document.getElementById('nm-title-editor');
  var hidden = document.getElementById('nm-title');
  if (!ed || !hidden) return;
  function sync() {
    hidden.value = ed.innerHTML;
  }
  function titlePlain() {
    return (ed.innerText || ed.textContent || '').replace(/\s+/g, ' ').trim();
  }
  ed.addEventListener('input', sync);
  ed.addEventListener('blur', sync);
  var wrap = document.querySelector('.nm-title-color');
  if (wrap) {
    wrap.addEventListener('mousedown', function (e) {
      var t = e.target;
      if (t && t.getAttribute && (t.getAttribute('data-nm-title-color') || t.getAttribute('data-nm-title-hl') || t.id === 'nm-title-color-clear')) {
        e.preventDefault();
      }
    });
    wrap.addEventListener('click', function (e) {
      var t = e.target;
      if (!t || !t.getAttribute) return;
      var color = t.getAttribute('data-nm-title-color');
      var hl = t.getAttribute('data-nm-title-hl');
      if (color) {
        ed.focus();
        try { document.execCommand('styleWithCSS', false, true); } catch (e1) {}
        document.execCommand('foreColor', false, color);
        sync();
      }
      if (hl) {
        ed.focus();
        try { document.execCommand('styleWithCSS', false, true); } catch (e3) {}
        if (!document.execCommand('hiliteColor', false, hl)) {
          document.execCommand('backColor', false, hl);
        }
        sync();
      }
    });
  }
  var clearBtn = document.getElementById('nm-title-color-clear');
  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      ed.focus();
      try { document.execCommand('styleWithCSS', false, true); } catch (e2) {}
      document.execCommand('foreColor', false, '#111111');
      try { document.execCommand('hiliteColor', false, 'transparent'); } catch (e4) {}
      document.execCommand('removeFormat', false, null);
      sync();
    });
  }
  var form = document.getElementById('SubmitForm');
  if (form) {
    form.addEventListener('submit', function () { sync(); });
  }
  window.nmTitlePlain = titlePlain;
})();
JS;
	}
}

/** Button + file picker for in-article photos (Add/Edit News). */
if (!function_exists('nm_ckeditor_photo_ui')) {
	function nm_ckeditor_photo_ui()
	{
		return <<<'HTML'
<div class="nm-inline-photo">
  <button type="button" class="btn btn-info" id="nm-inline-photo-btn">Add photo in article</button>
  <input type="file" id="nm-inline-photo-file" accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp">
  <span class="nm-form-hint" id="nm-inline-photo-status"></span>
</div>
<p class="nm-form-hint">Click <strong>Add photo in article</strong>, pick a JPG or PNG (max 600 KB). Resize buttons appear under the story.</p>
<div class="nm-inline-photo-bar" id="nm-inline-photo-bar" hidden>
  <strong>Resize photo:</strong>
  <button type="button" data-nm-photo="size" data-value="small">Small</button>
  <button type="button" data-nm-photo="size" data-value="medium">Medium</button>
  <button type="button" data-nm-photo="size" data-value="full">Full width</button>
  <span class="nm-inline-photo-bar__sep">Align:</span>
  <button type="button" data-nm-photo="align" data-value="left">Left</button>
  <button type="button" data-nm-photo="align" data-value="center">Center</button>
  <button type="button" data-nm-photo="align" data-value="right">Right</button>
  <button type="button" data-nm-photo="remove">Remove</button>
</div>
HTML;
	}
}

/** Relative CKEditor filebrowser config (works when $urlroot is wrong on production). */
if (!function_exists('nm_ckeditor_js')) {
	function nm_ckeditor_js($fieldId = 'description', $directPhoto = false)
	{
		$id = json_encode((string) $fieldId);
		$direct = $directPhoto ? 'true' : 'false';
		return <<<JS
if (!window.nmCkeditorCssAdded && window.CKEDITOR && CKEDITOR.addCss) {
  window.nmCkeditorCssAdded = true;
  CKEDITOR.addCss('img{max-width:100%;height:auto;cursor:pointer;}');
}
function nmBindInlinePhoto(fieldId) {
  var btn = document.getElementById('nm-inline-photo-btn');
  var input = document.getElementById('nm-inline-photo-file');
  var status = document.getElementById('nm-inline-photo-status');
  var bar = document.getElementById('nm-inline-photo-bar');
  if (!btn || !input) return;
  var selected = null;
  function editor() {
    return CKEDITOR.instances[fieldId];
  }
  function setStatus(msg) {
    if (status) status.textContent = msg || '';
  }
  function hideBar() {
    selected = null;
    if (bar) bar.hidden = true;
  }
  function asCkeEl(el) {
    if (!el) return null;
    if (el.setStyle) return el;
    try { return new CKEDITOR.dom.element(el); } catch (eEl) { return null; }
  }
  function lastImg(ed) {
    if (!ed || !ed.document || !ed.document.\$) return null;
    var list = ed.document.\$.getElementsByTagName('img');
    if (!list || !list.length) return null;
    return asCkeEl(list[list.length - 1]);
  }
  function showBar(el) {
    selected = asCkeEl(el) || lastImg(editor());
    if (bar && selected) bar.hidden = false;
  }
  function openPicker() {
    input.value = '';
    input.click();
  }
  function applySize(kind) {
    if (!selected) selected = lastImg(editor());
    if (!selected) return;
    var w = kind === 'small' ? '40%' : (kind === 'medium' ? '70%' : '100%');
    selected.setStyle('width', w);
    selected.setStyle('height', 'auto');
    selected.removeAttribute('width');
    selected.removeAttribute('height');
  }
  function applyAlign(kind) {
    if (!selected) selected = lastImg(editor());
    if (!selected) return;
    selected.removeStyle('float');
    selected.removeAttribute('align');
    if (kind === 'left') {
      selected.setAttribute('align', 'left');
      selected.setStyle('float', 'left');
      selected.setStyle('display', 'inline');
      selected.setStyle('margin', '6px 16px 12px 0');
    } else if (kind === 'right') {
      selected.setAttribute('align', 'right');
      selected.setStyle('float', 'right');
      selected.setStyle('display', 'inline');
      selected.setStyle('margin', '6px 0 12px 16px');
    } else {
      selected.setStyle('display', 'block');
      selected.setStyle('margin', '12px auto');
    }
  }
  function bindClicks(ed) {
    function onNativeClick(e) {
      var t = e.target;
      if (t && t.nodeName && t.nodeName.toLowerCase() === 'img') {
        showBar(t);
      }
    }
    function attach() {
      if (!ed.document || !ed.document.\$) return;
      var nativeDoc = ed.document.\$;
      if (nativeDoc.nmPhotoBound) return;
      nativeDoc.nmPhotoBound = true;
      if (nativeDoc.addEventListener) nativeDoc.addEventListener('click', onNativeClick, false);
      else if (nativeDoc.attachEvent) nativeDoc.attachEvent('onclick', onNativeClick);
    }
    ed.on('contentDom', attach);
    attach();
    ed.on('doubleclick', function (evt) {
      var el = evt.data.element;
      if (el && el.is && el.is('img')) {
        evt.data.dialog = '';
        showBar(el);
      }
    });
  }
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    openPicker();
  });
  if (bar) {
    bar.addEventListener('click', function (e) {
      var t = e.target;
      if (!t || !t.getAttribute) return;
      var act = t.getAttribute('data-nm-photo');
      if (!act) return;
      if (!selected) selected = lastImg(editor());
      if (!selected) return;
      if (act === 'size') applySize(t.getAttribute('data-value'));
      if (act === 'align') applyAlign(t.getAttribute('data-value'));
      if (act === 'remove') {
        selected.remove();
        hideBar();
      }
    });
  }
  function parseUpload(text) {
    var raw = (text || '').replace(/^\uFEFF/, '').trim();
    try { return JSON.parse(raw); } catch (e1) {}
    var start = raw.indexOf('{');
    var end = raw.lastIndexOf('}');
    if (start >= 0 && end > start) {
      try { return JSON.parse(raw.slice(start, end + 1)); } catch (e2) {}
    }
    return { ok: false, error: 'Upload failed.' };
  }
  function insertPhoto(ed, url) {
    ed.focus();
    var safe = String(url || '').replace(/"/g, '');
    var img = null;
    try {
      img = ed.document.createElement('img');
      img.setAttribute('src', safe);
      img.setAttribute('alt', '');
      img.setStyles({ width: '100%', height: 'auto', display: 'block', margin: '12px auto' });
      ed.insertElement(img);
    } catch (eIns) {
      ed.insertHtml('<p><img src="' + safe + '" alt="" style="width:100%;height:auto;display:block;margin:12px auto;"></p>');
      img = lastImg(ed);
    }
    showBar(img || lastImg(ed));
    return true;
  }
  input.addEventListener('change', function () {
    var file = input.files && input.files[0];
    if (!file) return;
    if (file.size > 600 * 1024) {
      setStatus('Image must be under 600 KB.');
      input.value = '';
      return;
    }
    var ed = editor();
    if (!ed) {
      setStatus('Editor is not ready yet.');
      return;
    }
    btn.disabled = true;
    setStatus('Uploading…');
    var fd = new FormData();
    fd.append('upload', file, file.name || 'photo.jpg');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'ckeditor_image_upload.php?format=json');
    xhr.onload = function () {
      btn.disabled = false;
      input.value = '';
      var data = parseUpload(xhr.responseText);
      if (!data.ok || !data.url) {
        setStatus(data.error || 'Upload failed.');
        return;
      }
      insertPhoto(ed, data.url);
      setStatus('Photo added. Use Small / Medium / Full width under the story to resize.');
    };
    xhr.onerror = function () {
      btn.disabled = false;
      input.value = '';
      setStatus('Upload failed. Try again.');
    };
    xhr.send(fd);
  });
  var readyEd = editor();
  function startEditorHooks(ed) {
    if (!ed || ed.nmPhotoHooked) return;
    ed.nmPhotoHooked = true;
    bindClicks(ed);
    var oldExec = ed.execCommand;
    ed.execCommand = function (cmdName) {
      if (cmdName === 'image' || cmdName === 'imagebutton') {
        openPicker();
        return true;
      }
      return oldExec.apply(this, arguments);
    };
  }
  if (readyEd) {
    readyEd.on('instanceReady', function () { startEditorHooks(readyEd); });
    if (readyEd.document || readyEd.status === 'ready') startEditorHooks(readyEd);
  }
}
CKEDITOR.replace({$id}, {
  width: '100%',
  extraAllowedContent: 'img[src,alt,width,height,border,align]{*}(*)',
  filebrowserBrowseUrl: 'ckeditor/filemanager/browser/default/browser.html?Connector=ckeditor/filemanager/connectors/php/connector.php',
  filebrowserImageBrowseUrl: 'ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector=ckeditor/filemanager/connectors/php/connector.php',
  filebrowserFlashBrowseUrl: 'ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector=ckeditor/filemanager/connectors/php/connector.php',
  filebrowserUploadUrl: 'ckeditor_image_upload.php',
  filebrowserImageUploadUrl: 'ckeditor_image_upload.php',
  filebrowserFlashUploadUrl: 'ckeditor/filemanager/connectors/php/upload.php?Type=Flash'
});
if ({$direct}) {
  nmBindInlinePhoto({$id});
}
JS;
	}
}

/**
 * Insert standard policy pages if missing. Never changes an existing page_url or body.
 */
if (!function_exists('nm_ensure_cms_pages')) {
	function nm_ensure_cms_pages($con)
	{
		if (!($con instanceof mysqli)) {
			return;
		}
		$pages = array(
			array(
				'page' => 'Editorial Policy',
				'page_url' => 'editorial-policy',
				'metat' => 'Editorial Policy | The Naradmuni',
				'metad' => 'The Naradmuni की संपादकीय नीति — स्वतंत्र रिपोर्टिंग, स्रोत और जवाबदेही।',
				'description' => <<<HTML
<p>The Naradmuni मध्य प्रदेश और छत्तीसगढ़ की खबरें जनता के हित में प्रकाशित करता है। यह पेज बताता है कि हम खबर कैसे चुनते और लिखते हैं। टीम इसे कभी भी अपडेट कर सकती है।</p>
<h2>स्वतंत्र संपादन</h2>
<p>संपादकीय फैसला खबर की सार्वजनिक अहमियत, तथ्यों और लोकहित पर आधारित होता है। सत्ता, विपक्ष, विज्ञापनदाता या निजी दबाव से खबर नहीं बदलवाई जाती।</p>
<h2>बायलाइन और स्रोत</h2>
<ul>
<li>जहाँ संभव हो, रिपोर्ट पर रिपोर्टर या डेस्क का नाम रहता है।</li>
<li>तथ्य विश्वसनीय स्रोत से जाँच कर प्रकाशित होते हैं। अनौपचारिक दावे को पुष्टि के बिना खबर नहीं बनाया जाता।</li>
<li>गुमनाम स्रोत तभी, जब जानकारी लोकहित में हो और स्रोत को खतरा हो।</li>
</ul>
<h2>भुगतान वाली सामग्री</h2>
<p>विज्ञापन, स्पॉन्सर्ड या एडवरटोरियल सामग्री को खबर से अलग चिह्नित किया जाता है। पेड न्यूज़ को सामान्य रिपोर्ट की तरह नहीं चलाया जाता।</p>
<h2>निजता और संवेदनशीलता</h2>
<p>अपराध, दुर्घटना और नाबालिगों से जुड़ी खबरों में गरिमा और कानून का ध्यान रखा जाता है। अनुमान या अफवाह को तथ्य नहीं लिखा जाता।</p>
<h2>सुधार</h2>
<p>गलती होने पर हम सुधार करते हैं। तरीका <strong>Correction Policy</strong> पेज पर है।</p>
HTML
			),
			array(
				'page' => 'Fact Check Policy',
				'page_url' => 'fact-check-policy',
				'metat' => 'Fact Check Policy | The Naradmuni',
				'metad' => 'The Naradmuni कैसे दावों की जाँच करता है — स्रोत, सबूत और निष्कर्ष।',
				'description' => <<<HTML
<p>सोशल मीडिया और वायरल मैसेज में गलत सूचना तेज़ी से फैलती है। The Naradmuni फैक्ट चेक में दावे को सबूत से मिलाकर बताता है — राय नहीं, जाँच।</p>
<h2>क्या जाँच करते हैं</h2>
<ul>
<li>वायरल फोटो, वीडियो, आँकड़े और राजनीतिक दावे, जब वे लोकहित में हों।</li>
<li>ऐसे दावे जिनका असर मतदाताओं, स्वास्थ्य या कानून-व्यवस्था पर पड़ सकता है।</li>
</ul>
<h2>क्या नहीं</h2>
<ul>
<li>स्पष्ट व्यंग्य या मीम, जब वे खबर के रूप में न फैले हों।</li>
<li>निजी विवाद जिनका सार्वजनिक असर न हो।</li>
</ul>
<h2>तरीका</h2>
<ol>
<li>मूल दावा साफ लिखा जाता है।</li>
<li>प्राथमिक स्रोत — सरकारी दस्तावेज, आधिकारिक बयान, डेटा, मूल वीडियो — से मिलान होता है।</li>
<li>निष्कर्ष संक्षेप में बताया जाता है: सही, गलत, भ्रामक, या सबूत अधूरा।</li>
</ol>
<h2>पारदर्शिता</h2>
<p>जहाँ संभव हो, स्रोत का लिंक या नाम रिपोर्ट में रहता है। नई जानकारी आने पर फैक्ट चेक अपडेट किया जा सकता है।</p>
HTML
			),
			array(
				'page' => 'Correction Policy',
				'page_url' => 'correction-policy',
				'metat' => 'Correction Policy | The Naradmuni',
				'metad' => 'गलती दिखे तो कैसे सुधार करवाएँ — The Naradmuni की सुधार नीति।',
				'description' => <<<HTML
<p>गलती हो सकती है। हम उसे छिपाते नहीं। यह पेज बताता है कि सुधार कैसे माँगें और हम क्या करते हैं।</p>
<h2>सुधार कैसे भेजें</h2>
<ul>
<li>फुटर में <strong>Contact Us</strong> खोलें और खबर का लिंक, गलत वाक्य और सही तथ्य लिखें।</li>
<li>जहाँ हो, सबूत (आधिकारिक दस्तावेज, फोटो, संपर्क) साथ दें।</li>
</ul>
<h2>हम क्या करते हैं</h2>
<ol>
<li>शिकायत की जाँच संपादकीय टीम करती है।</li>
<li>तथ्य गलत निकले तो खबर में सुधार किया जाता है — नाम, आँकड़ा, जगह या संदर्भ साफ करके।</li>
<li>बड़ी गलती पर खबर में सुधार का संक्षिप्त नोट जोड़ा जा सकता है।</li>
</ol>
<h2>क्या सुधार नहीं</h2>
<p>सहमति न होने मात्र से राय या हेडलाइन नहीं बदली जाती। मानहानि या कानूनी नोटिस अलग प्रक्रिया से देखे जाते हैं।</p>
<p>फैक्ट चेक की विधि <strong>Fact Check Policy</strong> पर है। संपादकीय सिद्धांत <strong>Editorial Policy</strong> पर हैं।</p>
HTML
			),
		);

		foreach ($pages as $p) {
			$slug = mysqli_real_escape_string($con, $p['page_url']);
			$exists = mysqli_query($con, "SELECT `p_id` FROM `pages` WHERE `page_url`='$slug' LIMIT 1");
			if ($exists instanceof mysqli_result && mysqli_num_rows($exists) > 0) {
				continue;
			}
			$name = mysqli_real_escape_string($con, $p['page']);
			$metat = mysqli_real_escape_string($con, $p['metat']);
			$metad = mysqli_real_escape_string($con, $p['metad']);
			$body = mysqli_real_escape_string($con, $p['description']);
			mysqli_query(
				$con,
				"INSERT INTO `pages` (`page`, `description`, `page_url`, `metat`, `metad`) VALUES ('$name', '$body', '$slug', '$metat', '$metad')"
			);
		}
	}
}
