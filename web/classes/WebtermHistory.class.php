<?php
	require_once "Database.class.php";
	require_once "DataHash.class.php";
	require_once "DbQueryPreper.class.php";

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
			$prep = new DbQueryPreper("SELECT * FROM webterm_history " .
										  "WHERE user = ");
			$prep->addVariable($this->user);
			$prep->addSql(" ORDER BY time");
				
			$results = Database::executeQuery($prep);

			$history = array();
			
			foreach ($results as $row)
			{
				$dataHash = new DataHash("Webterm_history");
				$dataHash->setAllAttributes($row);
				$history[] = $dataHash;
			}
			
			return $history;
		}

		public function addEntry($command)
		{
			$prep = new DbQueryPreper("INSERT INTO webterm_history VALUES (null, ");
			$prep->addVariables(array($command, $this->user));
			$prep->addSql(",datetime('now'))");
				
			Database::executeQuery($prep);
		}
	}
?>
