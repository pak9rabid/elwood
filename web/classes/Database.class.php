<?php
	require_once "DataHash.class.php";
	require_once "DbQueryPreper.class.php";

	class Database
	{
		// Constants
		const DB_FILE = "/etc/elwood/elwood.db";
		
		protected $pdo;
		
		public function __construct()
		{
			if (!file_exists(self::DB_FILE))
				throw new Exception(self::DB_FILE . " does not exist");
			
			$this->pdo = new PDO("sqlite:" . self::DB_FILE);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo->exec("PRAGMA foreign_keys = ON");
		}
	
		// Public methods
		public function executeQuery(DbQueryPreper $prep)
		{
			try
			{
				$stmt = $this->pdo->prepare($prep->getQuery());
				$stmt->execute($prep->getBindVars());
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
			catch (Exception $ex)
			{
				throw new Exception("Error executing SQL query: " . $prep->getQueryDebug());
			}
		}
		
		public function executeSelect(DataHash $data)
		{
			$classType = get_class($data);
			
			// Select rows from the database
			$prep = new DbQueryPreper("SELECT * FROM " . $data->getTable());
			
			if (count($data->getAttributeKeys()) > 0)
			{
				$prep->addSql(" WHERE ");
				$prep->addSql(implode(" AND ", array_map(array("self", "datahashToParamaterizedWhereClause"), $data->getAttributeKeys())));
				$prep->addVariablesNoPlaceholder($data->getAttributeValues());
			}
						
			$prep->addSql(" ORDER BY " . implode(", ", $data->getOrderBy()));
			$result = $this->executeQuery($prep);
					
			$resultHashes = array();
				
			foreach ($result as $row)
			{
				$resultHash = new $classType($data->getTable());
				$resultHash->setAllAttributes($row);
				$resultHashes[] = $resultHash;
			}
				
			return $resultHashes;
		}
		
		public static function datahashToParamaterizedWhereClause($key)
		{
			return " $key = ? ";
		}

		public function executeInsert(DataHash $data)
		{
			// Insert new row into the database
			$prep = new DbQueryPreper("INSERT INTO " . $data->getTable() . " (");
			$prep->addSql(implode(",", $data->getAttributeKeys()) . ") VALUES (");
			$prep->addVariables($data->getAttributeValues());
			$prep->addSql(")");			
			$this->executeQuery($prep);
		}
		
		public function executeInserts(array $data)
		{
			$this->pdo->beginTransaction();
						
			try
			{
				foreach ($data as $row)
				{
					if (!($row instanceof DataHash))
						throw new Exception("Invalid type: must be of type DataHash");
						
					$this->executeInsert($row);
				}
			}
			catch (Exception $ex)
			{
				$this->pdo->rollBack();
				throw $ex;
			}
			
			$this->pdo->commit();
		}

		public function executeUpdate(DataHash $data)
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
			$this->executeQuery($prep);
		}

		public function executeDelete(DataHash $data, $isTemp = false)
		{
			// Deletes rows from the database based on the criteria specified in $data
			$prep = new DbQueryPreper("DELETE FROM " . $data->getTable());
			
			if (count($data->getAttributeKeys()) > 0)
			{
				$prep->addSql(" WHERE ");
				$prep->addSql(implode(" AND ", array_map(array("self", "datahashToParamaterizedWhereClause"), $data->getAttributeKeys())));
				$prep->addVariablesNoPlaceHolder($data->getAttributeValues());
			}
						
			$this->executeQuery($prep);
		}
		
		public function getPdo()
		{
			return $this->pdo;
		}
	}
?>
