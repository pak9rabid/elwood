<?php
	require_once "DataHash.class.php";
	require_once "SettingNotFoundException.class.php";

	class RouterSettings
	{
		public static $ELWOOD_CFG_DIR = "/etc/elwood";
		
		public static function getSetting($key)
		{
			$selectHash = new DataHash("settings");
			$selectHash->setAttribute("key", $key);
			
			$results = $selectHash->executeSelect();
			
			if (count($results) <= 0)
				throw new SettingNotFoundException("The specified setting (" . $key . ") does not exist");
				
			return $results[0];
		}

		public static function getSettingValue($key)
		{
			return self::getSetting($key)->getAttribute("value");
		}

		public static function getAllSettings()
		{
			// returns all entries from the 'settings' database table in a convenient
			// associative array (aka hash map)
			$settings = array();
			$selectHash = new DataHash("settings");
			
			foreach ($selectHash->executeSelect() as $settingHash)
				$settings[$settingHash->getAttribute("key")] = $settingHash->getAttribute("value");
				
			return $settings;
		}

		public static function saveSetting($key, $value)
		{
			try
			{
				// update existing setting
				$setting = self::getSetting($key);				
				$setting->setAttribute("value", $value);
				$setting->executeUpdate();
			}
			catch (SettingNotFoundException $ex)
			{
				// setting doesn't exist...insert it as new
				$setting = new DataHash("settings");				
				$setting->setAttribute("key", $key);
				$setting->setAttribute("value", $value);
				$setting->executeInsert();
			}
		}
		
		public static function deleteSetting($key)
		{
			self::getSetting($key)->executeDelete();
		}
	}
?>
