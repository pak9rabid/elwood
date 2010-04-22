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
				throw new Exception(self::DB_FILE . " does not exist");

			try
			{				
				$conn = new PDO("sqlite:" . self::DB_FILE);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$conn->query("PRAGMA foreign_keys = ON");
				
				$stmt = $conn->prepare($prep->getQuery());
				$stmt->execute($prep->getBindVars());
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
			catch (Exception $ex)
			{
				throw new Exception("Error executing SQL query: '" . $prep->getQuery() . "'");
			}
		}
		
		public static function executeSelect(DataHash $data, $isTemp = false)
		{
			// Select rows from the database
			$prep = new DbQueryPreper("SELECT * FROM " . $data->getTable());
			
			if (count($data->getAttributeKeys()) > 0)
				$prep->addSql(" WHERE ");

			$prep->addSql(implode(" AND ", array_map(array("self", "datahashToParamaterizedWhereClause"), $data->getAttributeKeys())));
			$prep->addVariablesNoPlaceholder($data->getAttributeValues());
			
			try
			{
				if ($isTemp)
					$result = TempDatabase::executeQuery($prep);
				else
					$result = self::executeQuery($prep);
					
				$resultHashes = array();
				
				foreach ($result as $row)
				{
					$resultHash = new DataHash($data->getTable());
					$resultHash->setAllAttributes($row);
					$resultHashes[] = $resultHash;
				}
				
				return $resultHashes;
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}
		
		public static function datahashToParamaterizedWhereClause($key)
		{
			return " $key = ? ";
		}

		public static function executeInsert(DataHash $data, $isTemp = false)
		{
			// Insert new row into the database
			$prep = new DbQueryPreper("INSERT INTO " . $data->getTable() . " (");
			$prep->addSql(implode(",", $data->getAttributeKeys()) . ") VALUES (");
			$prep->addVariables($data->getAttributeValues());
			$prep->addSql(")");
			
			try
			{
				if ($isTemp)
					TempDatabase::executeQuery($prep);
				else
					self::executeQuery($prep);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}

		public static function executeUpdate(DataHash $data, $isTemp = false)
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
				if ($isTemp)
					TempDatabase::executeQuery($prep);
				else
					self::executeQuery($prep);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}

		public static function executeDelete(DataHash $data, $isTemp = false)
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
				if ($isTemp)
					TempDatabase::executeQuery($prep);
				else
					self::executeQuery($prep);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}
		
		public static function getDbPath()
		{
			return self::DB_FILE;
		}
	}
?>
