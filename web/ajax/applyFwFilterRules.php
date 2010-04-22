<?php
	require_once "ajaxAccessControl.php";
	require_once "TempDatabase.class.php";
	require_once "ClassFactory.class.php";
	
	header("Content-Type: application/json");
	
	try
	{
		$fwTranslator = ClassFactory::getFwFilterTranslator();
		$fwTranslator->setSystemFromDb(true);
		TempDatabase::destroy();
		
		$result = (object) array	(
										"result" => true
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