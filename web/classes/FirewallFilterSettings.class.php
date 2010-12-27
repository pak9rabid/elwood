<?php
	require_once "TempDatabase.class.php";
	require_once "FirewallChain.class.php";
	require_once "FirewallFilterRule.class.php";
	require_once "RouterStateStatus.class.php";
	require_once "DbQueryPreper.class.php";
	
	class FirewallFilterSettings
	{
		public static function getChain($chainName, TempDatabase $tempDb)
		{
			$selectHash = new FirewallChain();
			$selectHash->setConnection($tempDb);
			$selectHash->setAttribute("chain_name", $chainName);
			
			$results = $selectHash->executeSelect();
			
			if (count($results) <= 0)
				throw new Exception("Specified chain does not exist");
				
			return $results[0];
		}
		
		public static function getChains(TempDatabase $tempDb)
		{
			$selectHash = new FirewallChain();
			$selectHash->setConnection($tempDb);
			return $selectHash->executeSelect();
		}
				
		public static function getRules($chain, TempDatabase $tempDb)
		{
			$selectHash = new FirewallFilterRule();
			$selectHash->setConnection($tempDb);
			$selectHash->setAttribute("chain_name", $chain);
			return $selectHash->executeSelect();
		}
		
		public static function clearRules($chain, TempDatabase $tempDb)
		{
			$deleteHash = new FirewallFilterRule();
			$deleteHash->setConnection($tempDb);
			$deleteHash->setAttribute("chain_name", $chain);
			$deleteHash->executeDelete();
		}
	}
?>