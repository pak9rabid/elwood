<?php
	require_once "Database.class.php";
	
	class DbQueryPreper
	{
		// Attribute
		protected $query;
		protected $bindVars = array();
		
		// Constructors
		public function __construct($sql)
		{
			$this->query = $sql;
		}
		
		// Methods
		public function execute()
		{
			try
			{
				return Database::executeQuery($this);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}
		
		public function addSql($sql)
		{
			$this->query .= $sql;
		}
		
		public function addVariable($bindVar)
		{
			$this->query .= "?";
			$this->bindVars[] = $bindVar;
		}
		
		public function addVariables(array $bindVars, $delimiter = ",")
		{
			$this->query .= implode($delimiter, array_pad(array(), count($bindVars), "?"));
			$this->bindVars = array_merge($this->bindVars, $bindVars);
		}
		
		public function getQuery()
		{
			return $this->query;
		}
		
		public function getBindVars()
		{
			return $this->bindVars;
		}
	}
?>