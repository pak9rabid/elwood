<?php
	require_once "RouterSettings.class.php";
	
	class SystemProfile
	{		
		public static function getProfile()
		{			
			return json_decode(file_get_contents(RouterSettings::getSettingValue("ELWOOD_CFG_DIR") .
				"/profiles/" . RouterSettings::getSettingValue("SYSTEM_PROFILE")));
		}
		
		public static function getAvailableProfiles()
		{
			return array_filter(scandir(RouterSettings::getSettingValue("ELWOOD_CFG_DIR") . "/profiles"), array("self", "scandirFilter"));
		}
		
		private static function scandirFilter($filename)
		{
			return $filename != "." && $filename != "..";
		}
	}
?>