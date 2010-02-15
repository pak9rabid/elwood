<?php
	require_once "Database.class.php";
	require_once "DataHash.class.php";
	require_once "DbQueryPreper.class.php";

	class RouterSettings
	{
		public static function getSetting($key)
		{
			// Query database for specified setting
			// Throws exception on database error or
			// if specified setting doesn't exist
			try
			{
				$value = self::getSettingValue($key);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}

			$resultHash = new DataHash("settings");
			$resultHash->setPrimaryKey("KEY");
			$resultHash->setAttribute("KEY", $key);
			$resultHash->setAttribute("VALUE", $value);

			return $resultHash;
		}

		public static function getSettingValue($key)
		{
			try
			{
				$prep = new DbQueryPreper("SELECT value FROM settings WHERE key = ");
				$prep->addVariable($key);
				
				$result = Database::executeQuery($prep);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}

			if (count($result) <= 0)
				throw new Exception("Setting $key does not exist");
				
			return $result[0]['value'];
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
				$dataHash->setPrimaryKey("KEY");
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
				$setting->setAttribute("VALUE", $value);
				$setting->executeUpdate();
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}
	}
?>
