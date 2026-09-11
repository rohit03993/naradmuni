<?php
/**
 * Shared admin DB helper.
 * Reuses $con from config.php when available so production credentials stay in one place.
 */
if (!function_exists('nm_h')) {
	require_once __DIR__ . '/admin_helpers.php';
}
class DBController {
	private $conn;

	function __construct() {
		global $con;
		if (isset($con) && $con instanceof mysqli) {
			$this->conn = $con;
			return;
		}
		// Fallback only if config was not included (should not happen in admin pages).
		$this->conn = mysqli_connect("localhost", "thenaradmunicom_db", "RIzx63ZTUNeqx", "thenaradmunicom_db");
		if ($this->conn) {
			mysqli_set_charset($this->conn, "utf8");
		}
	}

	function runQuery($query) {
		if (!$this->conn) {
			return array();
		}
		$result = mysqli_query($this->conn, $query);
		if (!$result) {
			return array();
		}
		$resultset = array();
		while ($row = mysqli_fetch_assoc($result)) {
			$resultset[] = $row;
		}
		return $resultset;
	}

	function numRows($query) {
		if (!$this->conn) {
			return 0;
		}
		$result = mysqli_query($this->conn, $query);
		if (!$result) {
			return 0;
		}
		return (int) mysqli_num_rows($result);
	}

	function escape($value) {
		if (!$this->conn) {
			return addslashes((string) $value);
		}
		return mysqli_real_escape_string($this->conn, (string) $value);
	}
}
?>
