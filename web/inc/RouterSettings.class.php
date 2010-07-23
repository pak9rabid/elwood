<?php
	require_once "Database.class.php";
	require_once "DataHash.class.php";
	require_once "DbQueryPreper.class.php";

	class RouterSettings
	{
		public static function getSetting($key)
		{
			$selectHash = new DataHash("settings");
			$selectHash->setAttribute("key", $key);
			
			$results = $selectHash->executeSelect();
			
			if (count($results) <= 0)
				throw new Exception("Specified setting does not exist");
				
			return $results[0];
		}

		public static function getSettingValue($key)
		{
			return self::getSetting($key)->getAttribute("value");
		}

		public static function getAllSettings()
		{
			$selectHash = new DataHash("settings");
			return $selectHash->executeSelect();
		}

		public static function saveSetting($key, $value)
		{
			try
			{
				$setting = self::getSetting($key);
				$setting->setAttribute("value", $value);
				$setting->executeUpdate();
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}
	}
?>
