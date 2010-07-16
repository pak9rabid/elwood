<?php
	require_once "ajaxAccessControl.php";
	require_once "AjaxResponse.class.php";
	require_once "AjaxRequestHandler.class.php";
	
	$requestHandler = $_REQUEST['handler'];
	$requestParams = $_REQUEST['parameters'];
	
	if (empty($requestHandler))
	{
		$response = new AjaxResponse(true, "No ajax request handler specified");
		
		echo $response->toJson();
		exit;
	}
	
	$requestHandlerClass = $requestHandler . "AjaxRequestHandler";
	@include_once "$requestHandlerClass.class.php";
	
	if (!class_exists($requestHandlerClass))
	{
		$response = new AjaxResponse(true, "Specified ajax request handler ($requestHandler) does not exist");
		
		echo $response->toJson();
		exit;
	}
	
	$requestHandlerObj = new $requestHandlerClass();
	
	if (!($requestHandlerObj instanceof AjaxRequestHandler))
	{
		$response = new AjaxResponse(true, "Specified ajax request handler ($requestHandler) does not implement the AjaxRequestHandler interface");
		
		echo $response->toJson();
		exit;
	}
	
	$requestHandlerObj->processRequest($requestParams);
	echo $requestHandlerObj->getResponse()->toJson();
?>