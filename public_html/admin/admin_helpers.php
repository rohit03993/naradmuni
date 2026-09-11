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
