<?php
	require_once("DataHash.class.php");

	class Database
	{
		// Constants
		const DB_FILE = "/etc/elwood/elwood.db";
	
		// Public methods
		public static function executeQuery($query)
		{
			// Open database at DB_FILE, execute $query and return
			// resultset as a 2D array, or false if any errors are
			// encountered
			if (!file_exists(self::DB_FILE))
				throw new Exception(self::DB_FILE . "doesn't exist");

			if (!$dbHandle = @sqlite_open(self::DB_FILE))
				throw new Exception("Failed to open database");
			
			$result = @sqlite_query($dbHandle, $query, $placeholder, $error);
			@sqlite_close($dbHandle);

			if (!empty($error))
				throw new Exception($error);

			return $result ? sqlite_fetch_all($result) : $result;
		}

		public static function executeInsert(DataHash $data)
		{
			// Insert $data into the database
			$query = "INSERT INTO " . $data->getTable() . " ('";
			$query .= implode("', '", $data->getAttributeKeys()) . "') VALUES ('";
			$query .= implode("', '", $data->getAttributeValues()) . "')";

			try
			{
				self::executeQuery($query);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}

		public static function executeUpdate(DataHash $data)
		{
			// Update row in the database
			$primaryKey = $data->getPrimaryKey();
			$primaryKeyValue = $data->getAttribute($primaryKey);

			if (empty($primaryKey) || empty($primaryKeyValue))
				throw new Exception("Primary key not specified and/or set");

			$query = "UPDATE " . $data->getTable() . " SET ";
			$updateList = array();

			foreach ($data->getAttributeMap() as $key => $value)
			{
				if ($key != $primaryKey)
					$updateList[] = "$key = '$value'";
			}

			$query .= implode(", ", $updateList) . " WHERE $primaryKey = '$primaryKeyValue'";

			try
			{
				self::executeQuery($query);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}

		public static function executeDelete(DataHash $data)
		{
			# Delete row from the database
			$primaryKey = $data->getPrimaryKey();
			$primaryKeyValue = $data->getAttribute($primaryKey);

			if (empty($primaryKey) || empty($primaryKeyValue))
				throw new Exception("Primary key not specified and/or set");

			$query= "DELETE FROM " . $data->getTable() . " WHERE $primaryKey = '$primaryKeyValue'";

			try
			{
				self::executeQuery($query);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}
	}
?>
