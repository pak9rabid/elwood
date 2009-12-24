<?php
	class DataHash
	{
		// Attributes
		private $table = "";
		private $primaryKey = "";
		private $hashMap = array();

		// Constructors
		public function __construct($table)
		{
			$this->table = $table;
		}

		// Methods
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
	}
?>
