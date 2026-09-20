<?php
/**
 * CKEditor in-article image upload. Login required. Max 600 KB.
 * Saves under public_html/images/news/ so Next already serves the file.
 */
ob_start();
include __DIR__ . '/config.php';
require_once __DIR__ . '/admin_helpers.php';
ob_end_clean();

$funcNum = isset($_GET['CKEditorFuncNum']) ? preg_replace('/[^0-9]/', '', (string) $_GET['CKEditorFuncNum']) : '0';
$nmCkeWantJson = isset($_GET['format']) && $_GET['format'] === 'json';

function nm_cke_upload_done($funcNum, $url, $message)
{
	global $nmCkeWantJson;
	if (!empty($nmCkeWantJson)) {
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(array(
			'ok' => ($url !== ''),
			'url' => $url,
			'error' => $message,
		));
		exit;
	}
	header('Content-Type: text/html; charset=utf-8');
	$fn = (int) $funcNum;
	$u = json_encode((string) $url);
	$m = json_encode((string) $message);
	echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(' . $fn . ', ' . $u . ', ' . $m . ');</script>';
	exit;
}

if (!isset($_SESSION['aemail']) || $_SESSION['aemail'] === '') {
	nm_cke_upload_done($funcNum, '', 'Please log in again.');
}

$file = null;
if (isset($_FILES['upload']) && is_array($_FILES['upload'])) {
	$file = $_FILES['upload'];
} elseif (isset($_FILES['NewFile']) && is_array($_FILES['NewFile'])) {
	$file = $_FILES['NewFile'];
}

if (!$file || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
	nm_cke_upload_done($funcNum, '', 'Choose an image to upload.');
}

if (!empty($file['error'])) {
	nm_cke_upload_done($funcNum, '', 'Upload failed. Try a smaller file.');
}

if ((int) $file['size'] > 600 * 1024) {
	nm_cke_upload_done($funcNum, '', 'Image must be under 600 KB. Compress it and try again.');
}

$info = @getimagesize($file['tmp_name']);
if ($info === false || empty($info[2])) {
	nm_cke_upload_done($funcNum, '', 'Use a JPG, PNG, GIF, or WebP photo.');
}

$typeMap = array(
	IMAGETYPE_JPEG => 'jpg',
	IMAGETYPE_PNG => 'png',
	IMAGETYPE_GIF => 'gif',
);
if (defined('IMAGETYPE_WEBP')) {
	$typeMap[IMAGETYPE_WEBP] = 'webp';
}
$type = (int) $info[2];
if (!isset($typeMap[$type])) {
	nm_cke_upload_done($funcNum, '', 'Use a JPG, PNG, GIF, or WebP photo.');
}
$ext = $typeMap[$type];

$dir = dirname(__DIR__) . '/images/news/';
if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
	nm_cke_upload_done($funcNum, '', 'Could not create the image folder.');
}

$rand = function_exists('random_bytes') ? bin2hex(random_bytes(3)) : str_replace('.', '', uniqid('', true));
$name = 'inline_' . date('Ymd_His') . '_' . $rand . '.' . $ext;
$dest = $dir . $name;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
	nm_cke_upload_done($funcNum, '', 'Could not save the image.');
}

$url = '/naradmuni/images/news/' . $name;
nm_cke_upload_done($funcNum, $url, '');
