<?php
	require_once "ajaxAccessControl.php";
	require_once "FirewallFilterSettings.class.php";
	require_once "FirewallFilterTable.class.php";
	
	header("Content-Type: application/json");
	
	$direction = trim($_REQUEST['dir']);
	$order = $_REQUEST['order'];
	
	if (!empty($order))
		$order = explode(",", $order);
		
	try
	{
		if ($direction == "in")
			$chainName = "forward_in";
		else if ($direction == "out")
			$chainName = "forward_out";
		else
			throw new Exception("Invalid direction specified");
			
		if (!empty($order))
			FirewallFilterSettings::orderRules($order, $chainName);
			
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