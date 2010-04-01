<?php
	require_once "ajaxAccessControl.php";
	require_once "FirewallFilterSettings.class.php";
	require_once "ClassFactory.class.php";
	
	header("Content-Type: application/json");
	$direction = trim($_REQUEST['dir']);
	$rulesOrder = explode(",", trim($_REQUEST['order']));
	
	try
	{
		if ($direction == "in")
			$chainName = "forward_in";
		else if ($direction == "out")
			$chainName = "forward_out";
		else
			throw new Exception("Invalid direction specified");
			
		FirewallFilterSettings::orderRules($rulesOrder, $chainName);
		$fwTranslator = ClassFactory::getFwFilterTranslator();
		$fwTranslator->setSystemFromDb(true);
		$json = "{\"result\":true}";
	}
	catch (Exception $ex)
	{
		$json = "{\"result\":false}";
	}
	
	echo $json;
?>