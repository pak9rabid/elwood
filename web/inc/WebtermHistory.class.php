<?php
	require_once "Database.class.php";
	require_once "DataHash.class.php";

	class WebtermHistory
	{
		// Attributes
		private $user;

		// Constructor
		public function __construct($user)
		{
			$this->user = $user;
		}

		// Methods
		public function getHistory()
		{
			// Returns a list of webterm commands executed by
			// $user as an array of DataHashes
			try
			{
				$query = "SELECT * " .
					 "FROM webterm_history " .
					 "WHERE user = '$this->user' " .
					 "ORDER BY time";

				$result = Database::executeQuery($query);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}

			$results = array();

			while (($row = sqlite_fetch_array($result, SQLITE_ASSOC)) == true)
			{
				$dataHash = new DataHash("webterm_history");
				$dataHash->setPrimaryKey("ID");
				$dataHash->setAttribute("ID", $row['id']);
				$dataHash->setAttribute("COMMAND", $row['command']);
				$dataHash->setAttribute("USER", $row['user']);
				$dataHash->setAttribute("TIME", $row['time']);

				$results[] = $dataHash;
			}

			return $results;
		}

		public function addEntry($command)
		{
			try
			{
				Database::executeQuery("INSERT INTO webterm_history VALUES (null, '$command', '$this->user', datetime('now'))");
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
		}
	}
?>
