<?php
	require_once "FileUtils.class.php";
	
	class RouterStats
	{
		public static function getUptime()
		{
			list($uptime, $idleTime) = explode(" ", FileUtils::readFileAsString("/proc/uptime"));
			return $uptime;
		}
	}
?>