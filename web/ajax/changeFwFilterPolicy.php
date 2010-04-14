<?php
	require_once "ajaxAccessControl.php";
	require_once "FirewallFilterSettings.class.php";
	require_once "FirewallFilterTable.class.php";
	
	header("Content-Type: application/json");
	
	$direction = trim($_REQUEST['dir']);
	
	try
	{
		$forwardChain = FirewallFilterSettings::getChain("FORWARD");
		$forwardChain->setAttribute("policy", $forwardChain->getAttribute("policy") == "ACCEPT" ? "DROP" : "ACCEPT");
		$forwardChain->executeUpdate(true);
		
		$filterTable = new FirewallFilterTable();
		$result = (object) array	(
										"result" => true,
										"fwFilterTableHtml" => $filterTable->out($direction)
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