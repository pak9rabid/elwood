<?php
	require_once "ajaxAccessControl.php";
	require_once "ClassFactory.class.php";
	require_once "TempDatabase.class.php";
	require_once "FirewallFilterRule.class.php";
	require_once "FirewallFilterSettings.class.php";
	require_once "FileUtils.class.php";

	$direction = trim($_REQUEST['direction']);
	$policy = trim($_REQUEST['policy']);
	$rulesIn = isset($_REQUEST['rules']) ? $_REQUEST['rules'] : array();
	
	$rules = array();
	
	foreach ($rulesIn as $ruleIn)
	{
		$tempRule = new FirewallFilterRule();
		
		foreach ($ruleIn as $key => $value)
		{
			$tempRule->setAttribute("chain_name", "forward_" . $direction);
			
			if ($value != "*" && !preg_match("/^new/", $value))
				$tempRule->setAttribute($key, $value);
		}
		
		$rules[] = $tempRule;
	}
	
	try
	{
		// Set policy
		$forwardChain = FirewallFilterSettings::getChain("FORWARD");
		$forwardChain->setAttribute("policy", $policy);
		$forwardChain->executeUpdate(true);
		
		// Clear existing rules
		FirewallFilterSettings::clearRules("forward_" . $direction);
		
		// Set rules
		foreach ($rules as $rule)
			$rule->executeInsert(true);
			
		$fwTranslator = ClassFactory::getFwFilterTranslator();
		$iptablesRestore = $fwTranslator->setSystemFromDb(true);
		
		// Write file
		FileUtils::writeToFile(RouterSettings::getSettingValue("ELWOOD_CFG_DIR") . "/firewall/filter.rules", implode("\n", $iptablesRestore) . "\n");
		
		TempDatabase::destroy();
	}
	catch (Exception $ex)
	{
		$error = $ex->getMessage();
		echo json_encode(false);
	}
	
	echo json_encode(true);
?>