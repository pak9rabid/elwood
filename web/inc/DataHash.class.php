<?php
	require_once "Database.class.php";

	class DataHash
	{
		// Attributes
		protected $table = "";
		protected $primaryKey = "";
		protected $hashMap = array();

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

		public function executeInsert()
		{
			try
			{
				Database::executeInsert($this);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}

		public function executeUpdate()
		{
			try
			{
				Database::executeUpdate($this);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}

		public function executeDelete()
		{
			try
			{
				Database::executeDelete($this);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}
	}
?>
