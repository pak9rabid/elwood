<?php
	require_once "Database.class.php";
	require_once "DbQueryPreper.class.php";
	require_once "SessionUtils.class.php";
	
	class User extends DataHash
	{
		// Attributes
		protected $group;
		
		// Constructors
		public function __construct()
		{
			parent::__construct("users");
		}
		
		// Methods
		public function getGroup()
		{
			return $this->group;
		}
		
		public function setGroup()
		{
			$prep = new DbQueryPreper("SELECT name FROM groups WHERE gid = (SELECT gid from user_groups WHERE uid = ");
			$prep->addVariable($this->getAttribute("uid"));
			$prep->addSql(")");
			
			try
			{
				$result = Database::executeQuery($prep);
				$this->group = $result[0]['name'];
			}
			catch (Exception $ex)
			{
				$this->group = null;
			}
		}
		
		public static function getUser()
		{
			return SessionUtils::getUser();
		}
		
		public function __toString()
		{
			return $this->getAttributDisp("username");
		}
	}
?>