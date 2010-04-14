<?php
	require_once "ajaxAccessControl.php";
	require_once "FirewallFilterSettings.class.php";
	require_once "FirewallFilterTable.class.php";
	
	header("Content-Type: application/json");
	
	$ruleId = trim($_REQUEST['ruleId']);
	
	try
	{
		$rule = FirewallFilterSettings::getRule($ruleId);
		$rule->executeDelete(true);
		FirewallFilterSettings::setHasChanges(true);
		
		$filterTable = new FirewallFilterTable();
		$result = (object) array	(	"result" => true,
										"fwFilterTableHtml" => $filterTable->out(preg_replace("/forward_/", "", $rule->getAttribute("chain_name")))
									);
	}
	catch (Exception $ex)
	{
		$result = (object) array	(
										"result" => false,
										"error" => $ex->getMessage()
									);
	}
	
	echo json_encode($result);
?>
