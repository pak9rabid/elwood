<?php
	require_once "Database.class.php";
	require_once "User.class.php";
	require_once "ClassFactory.class.php";
	
	class TempDatabase extends Database
	{
		// Override
		public static function executeQuery(DbQueryPreper $prep)
		{			
			if (!self::exists())
			{
				self::create();
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
		public static function executeInsert(DataHash $data)
		{
			Database::executeInsert($data, true);
		}

		// Override
		public static function executeUpdate(DataHash $data)
		{
			Database::executeUpdate($data, true);
		}

		// Override
		public static function executeDelete(DataHash $data)
		{
			Database::executeDelete($data, true);
		}
		
		// Override
		public static function getDbPath()
		{
			$username = User::getUser()->getAttribute("username");
			return "/tmp/$username.db";
		}
		
		public static function exists()
		{
			return file_exists(self::getDbPath());
		}
		
		public static function create()
		{
			if (!copy(parent::getDbPath(), self::getDbPath()))
				throw new Exception("Error: Unable to copy database file");
				
			$fwTranslator = ClassFactory::getFwFilterTranslator();
			$fwTranslator->setDbFromSystem();
		}
		
		public static function destroy()
		{
			return unlink(self::getDbPath());
		}
	}
?>