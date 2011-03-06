<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "User.class.php";
	require_once "NetUtils.class.php";
	
	class AddEditNATOutgoingRuleAjaxRequestHandler implements AjaxRequestHandler
	{
		private $response;
		
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
			{
				$this->response = new AjaxResponse("", array("Only admin users are allowed to add or edit firewall rules"));
				return;
			}
			
			$id = $parameters['id'];
			$srcAddr = $parameters['srcAddr'];
			$dstAddr = $parameters['dstAddr'];
			$snatAuto = $parameters['snatAuto'];
			$snatManual = $parameters['snatManual'];
			$snatTo = $parameters['snatTo'];
			
			$errors = array();
			
			if (!empty($srcAddr) && (!NetUtils::isValidIp($srcAddr) && !NetUtils::isValidNetwork($srcAddr)))
				$errors[] = "Invalid source address specified";
				
			if (!empty($dstAddr) && (!NetUtils::isValidIp($dstAddr) && !NetUtils::isValidNetwork($dstAddr)))
				$errors[] = "Invalid destination address specified";
				
			if (!$snatAuto && !$snatManual)
				$errors[] = "No NAT method specified";
				
			if ($snatManual && !NetUtils::isValidIp($snatTo))
				$errors[] = "Invalid SNAT to address specified";
				
			if (!empty($errors))
			{
				$this->response = new AjaxResponse("", $errors);
				return;
			}
			
			$this->response = new AjaxResponse(empty($id) ? "new" . uniqid() : $id, array());
		}
		
		// Override
		public function getResponse()
		{
			return $this->response;
		}
	}
?>