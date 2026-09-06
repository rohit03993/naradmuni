<?php
/**
 * Key/value site settings helpers (YouTube Shorts, etc.).
 */
function nm_ensure_site_settings(mysqli $con) {
	mysqli_query(
		$con,
		"CREATE TABLE IF NOT EXISTS `site_settings` (
			`setting_key` VARCHAR(64) NOT NULL,
			`setting_value` TEXT NOT NULL,
			`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`setting_key`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
	);
}

function nm_setting_get(mysqli $con, $key, $default = "") {
	$key = mysqli_real_escape_string($con, (string) $key);
	$q = mysqli_query($con, "SELECT `setting_value` FROM `site_settings` WHERE `setting_key`='$key' LIMIT 1");
	if ($q && ($row = mysqli_fetch_assoc($q))) {
		return (string) $row["setting_value"];
	}
	return $default;
}

function nm_setting_set(mysqli $con, $key, $value) {
	$keyEsc = mysqli_real_escape_string($con, (string) $key);
	$valEsc = mysqli_real_escape_string($con, (string) $value);
	return (bool) mysqli_query(
		$con,
		"INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('$keyEsc', '$valEsc')
		 ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`)"
	);
}
