<?php
	require_once "ajaxAccessControl.php";
	require_once "AjaxResponse.class.php";
	require_once "AjaxRequestHandler.class.php";
	
	$requestHandler = $_REQUEST['handler'];
	$requestParams = $_REQUEST['parameters'];
	
	try
	{
		if (empty($requestHandler))
		{
			$response = new AjaxResponse("No ajax request handler specified", true);
		
			echo $response->toJson();
			exit;
		}
	
		$requestHandlerClass = $requestHandler . "AjaxRequestHandler";
		@include_once "$requestHandlerClass.class.php";
	
		if (!class_exists($requestHandlerClass))
		{
			$response = new AjaxResponse("Specified ajax request handler ($requestHandler) does not exist", true);
		
			echo $response->toJson();
			exit;
		}
	
		$requestHandlerObj = new $requestHandlerClass();
	
		if (!($requestHandlerObj instanceof AjaxRequestHandler))
		{
			$response = new AjaxResponse("Specified ajax request handler ($requestHandler) does not implement the AjaxRequestHandler interface", true);
		
			echo $response->toJson();
			exit;
		}
	
		$requestHandlerObj->processRequest($requestParams);
		echo $requestHandlerObj->getResponse()->toJson();
	}
	catch (Exception $ex)
	{
		$response = new AjaxResponse("Exception: " . $ex->getMessage(), true);
		echo $response->toJson();
	}
?>