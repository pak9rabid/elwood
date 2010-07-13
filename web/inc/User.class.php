<?php
	require_once "Database.class.php";
	require_once "DataHash.class.php";
	require_once "DbQueryPreper.class.php";
	require_once "SessionUtils.class.php";
	
	class User extends DataHash
	{
		// Attributes
		//protected $group;
		
		// Constructors
		public function __construct()
		{
			parent::__construct("users");
		}
		
		// Methods
		public function getGroup()
		{
			//return $this->group;
			return $this->getAttribute("usergroup");
		}
				
		public static function getUser()
		{
			return SessionUtils::getUser();
		}
		
		public function __toString()
		{
			return $this->getAttribute("username");
		}
	}
?>