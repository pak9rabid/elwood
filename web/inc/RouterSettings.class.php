<?php
	require_once "Database.class.php";
	require_once "DataHash.class.php";
	require_once "DbQueryPreper.class.php";

	class RouterSettings
	{
		public static function getSetting($key)
		{
			$prep = new DbQueryPreper("SELECT * FROM settings where key = ");
			$prep->addVariable($key);
			
			try
			{
				$result = Database::executeQuery($prep);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
			
			$resultHash = new DataHash("settings");
			$resultHash->setAllAttributes($result[0]);
			
			return $resultHash;
		}

		public static function getSettingValue($key)
		{
			try
			{
				$setting = self::getSetting($key);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
			
			return $setting->getAttribute("value");
		}

		public static function getAllSettings()
		{
			$settings = array();

			try
			{
				$results = Database::executeQuery(new DbQueryPreper("SELECT * FROM settings"));
			}
			catch (Exception $ex)
			{
				throw $ex;
			}

			foreach ($results as $row)
			{
				$dataHash = new DataHash("settings");
				$dataHash->setAllAttributes($row);
				$settings[] = $dataHash;
			}
			
			return $settings;
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
