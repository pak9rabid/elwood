<?php
	require_once "../classes/AjaxResponse.class.php";
	require_once "../classes/AjaxRequestHandler.class.php";
	
	session_start();
	header("Content-Type: application/json");
	
	if (!isset($_SESSION['user']))
	{
		// User not logged in
		$response = new AjaxResponse("", array("User is not logged in"));
		
		echo $response->toJson();
		exit;
	}
	
	$requestHandler = $_REQUEST['handler'];
	$requestParams = isset($_REQUEST['parameters']) ? $_REQUEST['parameters'] : array();
	
	try
	{
		if (empty($requestHandler))
		{
			$response = new AjaxResponse("", array("No ajax request handler specified"));
		
			echo $response->toJson();
			exit;
		}
	
		$requestHandlerClass = $requestHandler . "AjaxRequestHandler";
		@include_once "../classes/$requestHandlerClass.class.php";
	
		if (!class_exists($requestHandlerClass))
		{
			$response = new AjaxResponse("", array("Specified ajax request handler ($requestHandler) does not exist"));
		
			echo $response->toJson();
			exit;
		}
	
		$requestHandlerObj = new $requestHandlerClass();
	
		if (!($requestHandlerObj instanceof AjaxRequestHandler))
		{
			$response = new AjaxResponse("", array("Specified ajax request handler ($requestHandler) does not implement the AjaxRequestHandler interface"));
		
			echo $response->toJson();
			exit;
		}
	
		$requestHandlerObj->processRequest($requestParams);
		echo $requestHandlerObj->getResponse()->toJson();
	}
	catch (Exception $ex)
	{
		$response = new AjaxResponse("", array("Exception: " . $ex->getMessage()));
		echo $response->toJson();
	}
?>