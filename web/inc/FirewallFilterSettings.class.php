<?php
	require_once "TempDatabase.class.php";
	require_once "FirewallChain.class.php";
	require_once "FirewallFilterRule.class.php";
	require_once "RouterStateStatus.class.php";
	require_once "DbQueryPreper.class.php";
	
	class FirewallFilterSettings
	{
		public static function getChain($chainName)
		{
			$selectHash = new FirewallChain();
			$selectHash->setAttribute("chain_name", $chainName);
			
			$results = $selectHash->executeSelect(true);
			
			if (count($results) <= 0)
				throw new Exception("Specified chain does not exist");
				
			return $results[0];
		}
		
		public static function getChains()
		{
			$selectHash = new FirewallChain();
			return $selectHash->executeSelect(true);
		}
		
		public static function getRule($id)
		{
			$selectHash = new FirewallFilterRule();
			$selectHash->setAttribute("id", $id);
			
			$results = $selectHash->executeSelect(true);
			
			if (count($results) <= 0)
				throw new Exception("Specified firewall rules does not exist");
				
			return $results[0];
		}
		
		public static function getRules($chain)
		{
			$selectHash = new FirewallFilterRule();
			$selectHash->setAttribute("chain_name", $chain);
			return $selectHash->executeSelect(true);
		}
		
		public static function clearRules($chain)
		{
			$deleteHash = new FirewallFilterRule();
			$deleteHash->setAttribute("chain_name", $chain);
			$deleteHash->executeDelete(true);
		}
	}
?>