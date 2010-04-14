<?php
	require_once "ajaxAccessControl.php";
	require_once "TempDatabase.class.php";
	require_once "FirewallFilterSettings.class.php";
	require_once "ClassFactory.class.php";
	
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
			
		$fwTranslator = ClassFactory::getFwFilterTranslator();
		$fwTranslator->setSystemFromDb(true);
		TempDatabase::destroy();
		$json = "{\"result\":true}";
	}
	catch (Exception $ex)
	{
		$temp = $ex->getMessage();
		$json = "{\"result\":false}";
	}
	
	echo $json;
?>