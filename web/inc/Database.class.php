<?php
	require_once "DataHash.class.php";
	require_once "DbQueryPreper.class.php";

	class Database
	{
		// Constants
		const DB_FILE = "/etc/elwood/elwood.db";
	
		// Public methods
		public static function executeQuery(DbQueryPreper $prep)
		{
			// Open database at DB_FILE, execute $query and return
			// results
			if (!file_exists(self::DB_FILE))
				throw new Exception(self::DB_FILE . "does not exist");

			try
			{				
				$conn = new PDO("sqlite:" . self::DB_FILE);
				$conn->query("PRAGMA foreign_keys = ON");
				
				$stmt = $conn->prepare($prep->getQuery());
				$stmt->execute($prep->getBindVars());
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}

		public static function executeInsert(DataHash $data)
		{
			// Insert new row into the database
			$prep = new DbQueryPreper("INSERT INTO " . $data->getTable() . " (");
			$prep->addSql(implode(",", $data->getAttributeKeys()) . ") VALUES (");
			$prep->addVariables($data->getAttributeValues());
			$prep->addSql(")");
			
			try
			{
				self::executeQuery($prep);
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

			$prep = new DbQueryPreper("UPDATE " . $data->getTable() . " SET");
			
			foreach ($data->getAttributeMap() as $key => $value)
			{
				if ($key != $primaryKey)
				{
					if (count($prep->getBindVars()) > 0)
						$prep->addSql(",");
					
					$prep->addSql(" $key = ");
					$prep->addVariable($value);
				}
			}
			
			$prep->addSql(" WHERE $primaryKey = ");
			$prep->addVariable($primaryKeyValue);
			
			try
			{
				self::executeQuery($prep);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}

		public static function executeDelete(DataHash $data)
		{
			// Delete specified row in the database
			$primaryKey = $data->getPrimaryKey();
			$primaryKeyValue = $data->getAttribute($primaryKey);

			if (empty($primaryKey) || empty($primaryKeyValue))
				throw new Exception("Primary key not specified and/or set");

			$prep = new DbQueryPreper("DELETE FROM " . $data->getTable() . " WHERE $primaryKey = ");
			$prep->addVariable($primaryKeyValue);
			
			try
			{
				self::executeQuery($prep);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}
	}
?>
