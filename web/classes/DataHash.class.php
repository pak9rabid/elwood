<?php
	require_once "Database.class.php";
	require_once "TempDatabase.class.php";

	class DataHash
	{
		// Attributes
		protected $table = "";
		protected $primaryKey = "id";
		protected $hashMap = array();
		protected $conn;

		// Constructors
		public function __construct($table)
		{
			$this->table = $table;
		}

		// Methods
		public function __toString()
		{
			$elements = array();

			foreach ($this->hashMap as $key => $value)
				$elements[] = "$key=$value";

			return "{" . implode(", ", $elements) . "}";
		}
		
		public function toJson()
		{
			return json_encode($this->hashMap);
		}
		
		public function setConnection(Database $conn)
		{
			$this->conn = $conn;
		}

		public function setTable($table)
		{
			$this->table = $table;
		}

		public function getTable()
		{
			return $this->table;
		}

		public function setPrimaryKey($primaryKey)
		{
			$this->primaryKey = $primaryKey;
		}
	
		public function getPrimaryKey()
		{
			return $this->primaryKey;
		}

		public function getAttribute($key)
		{
			return $this->hashMap[$key];
		}

		public function getAttributeMap()
		{
			return $this->hashMap;
		}

		public function getAttributeKeys()
		{
			return array_keys($this->hashMap);
		}

		public function getAttributeValues()
		{
			return array_values($this->hashMap);
		}

		public function setAttribute($key, $value)
		{
			$this->hashMap[$key] = $value;
		}

		public function setAllAttributes($hashMap)
		{
			$this->hashMap = $hashMap;
		}

		public function removeAttribute($key)
		{
			unset($this->hashMap[$key]);
		}

		public function clear()
		{
			$this->hashMap = array();
		}
		
		public function executeSelect()
		{			
			if (!empty($this->conn))
				return $this->conn->executeSelect($this);
			else
			{
				$db = new Database();
				return $db->executeSelect($this);
			}
		}

		public function executeInsert()
		{			
			if (!empty($this->conn))
				$this->conn->executeInsert($this);
			else
			{
				$db = new Database();
				$db->executeInsert($this);
			}
		}

		public function executeUpdate()
		{			
			if (!empty($this->conn))
				$this->conn->executeUpdate($this);
			else
			{
				$db = new Database();
				$db->executeUpdate($this);
			}
		}

		public function executeDelete()
		{			
			if (!empty($this->conn))
				$this->conn->executeDelete($this);
			else
			{
				$db = new Database();
				$db->executeDelete($this);
			}
		}
		
		public function getAttributeDisp($attribute)
		{
			return $this->getAttribute($attribute) == null ? "*" : $this->getAttribute($attribute);
		}
	}
?>
