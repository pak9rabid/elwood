<?php
	require_once "ajaxAccessControl.php";
	require_once "FirewallFilterSettings.class.php";
	
	$id = $_REQUEST['id'];

	try
	{
		$rule = FirewallFilterSettings::getRule($id);
		echo $rule->toJson();
	}
	catch (Exception $ex)
	{
		echo json_encode(false);
	}
?>