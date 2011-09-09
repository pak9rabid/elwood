<?php
	require_once "classes/AjaxResponse.class.php";
	require_once "classes/AjaxRequestHandler.class.php";
	require_once "classes/User.class.php";
	
	session_start();
	header("Content-Type: application/json");
		
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
		@include_once "classes/$requestHandlerClass.class.php";
	
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
		
		if ($requestHandlerObj->isRestricted() && User::getUser() == null)
		{
			$response = new AjaxResponse("", array("You must be logged-in to access the specified ajax request handler"));
			echo $response->toJson();
			exit;
		}
	
		$response = $requestHandlerObj->processRequest($requestParams);
		
		if (!($response instanceof AjaxResponse))
		{
			$response = new AjaxResponse("", array("The specified ajax request handler ($requestHandler) did not return a valid ajax response"));
			echo $response->toJson();
			exit;
		}
		
		echo $response->toJson();
	}
	catch (Exception $ex)
	{
		$response = new AjaxResponse("", array("Exception: " . $ex->getMessage()));
		echo $response->toJson();
	}
?>