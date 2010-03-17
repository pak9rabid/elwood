<?php
	require_once "ajaxAccessControl.php";
	require_once "FirewallFilterSettings.class.php";
	require_once "ClassFactory.class.php";
	
	header("Content-Type: application/json");
	$direction = trim($_REQUEST['dir']);
	$rulesOrder = explode(",", trim($_REQUEST['order']));
	
	try
	{
		FirewallFilterSettings::orderRules($rulesOrder);
		$fwTranslator = ClassFactory::getFwFilterTranslator();
		$fwTranslator->setSystemFromDb(true);
		$json = "{\"result\":true}";
	}
	catch (Exception $ex)
	{
		$temp = $ex->getMessage();
		$json = "{\"result\":false}";
	}
	
	echo $json;
?>