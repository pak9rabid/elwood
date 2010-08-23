<?php
	require_once "Database.class.php";
	require_once "DataHash.class.php";
	require_once "DbQueryPreper.class.php";
	require_once "SessionUtils.class.php";
	
	class User extends DataHash
	{		
		// Constructors
		public function __construct()
		{
			parent::__construct("users");
		}
		
		// Methods
		public function getGroup()
		{
			return $this->getAttribute("usergroup");
		}
		
		public function isAdminUser()
		{
			return $this->getGroup() == "admins";
		}
		
		public function setPassword($password)
		{
			$this->setAttribute("password", self::encryptPassword($password));
		}
				
		public static function getUser()
		{
			return SessionUtils::getUser();
		}
		
		public static function encryptPassword($password)
		{
			return sha1($password);
		}
		
		public function __toString()
		{
			return $this->getAttribute("username");
		}
	}
?>