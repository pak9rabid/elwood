<?php
	require_once("Database.class.php");
	require_once("DataHash.class.php");

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
				$result = Database::executeQuery("SELECT value FROM settings WHERE key = '$key'");
			}
			catch (Exception $ex)
			{
				throw $ex;
			}

			if (sqlite_num_rows($result) <= 0)
				throw new Exception("Setting $key does not exist");

			$row = sqlite_fetch_array($result, SQLITE_ASSOC);
			return $row['value'];
		}

		public static function getAllSettings()
		{
			$results = array();

			try
			{
				$result = Database::executeQuery("SELECT * FROM settings");
			}
			catch (Exception $ex)
			{
				throw $ex;
			}

			while (($row = sqlite_fetch_array($result)) == true)
			{
				$dataHash = new DataHash("settings");
				$dataHash->setAttribute("KEY", $row['key']);
				$dataHash->setAttribute("VALUE", $row['value']);

				$results[] = $dataHash;
			}

			return $results;
		}

		public static function saveSetting($key, $value)
		{
			try
			{
				$setting = self::getSetting($key);
				$setting->setAttribute("value", $value);
				Database::executeUpdate($setting);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}
	}
?>
