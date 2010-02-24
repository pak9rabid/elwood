<?php
	require_once "Database.class.php";
	require_once "User.class.php";
	
	class TempDatabase extends Database
	{
		// Override
		public static function executeQuery(DbQueryPreper $prep)
		{
			// Open database at /tmp/${username}.db execute $query and return
			// results
			
			if (!file_exists(self::getDbPath()))
			{
				if (!copy(parent::getDbPath(), self::getDbPath()))
					throw new Exception("Error: Unable to copy database file");
			}
			
			try
			{				
				$conn = new PDO("sqlite:" . self::getDbPath());
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
		
		// Override
		public static function getDbPath()
		{
			$username = User::getUser()->getAttribute("username");
			return "/tmp/$username.db";
		}
		
		public static function destroy()
		{
			return unlink(self::getDbPath());
		}
	}
?>