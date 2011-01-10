<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "FirewallChain.class.php";
	require_once "FirewallRule.class.php";
	require_once "FileUtils.class.php";
	require_once "User.class.php";
	
	class ApplyFwFilterRulesAjaxRequestHandler implements AjaxRequestHandler
	{
		private $response;
		
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
			{
				$this->response = new AjaxResponse("", array("Only admin users are allowed to make changes to the firewall"));
				return;
			}
			
			$direction = trim($parameters['direction']);
			
			if (!in_array($direction, array("in", "out")))
			{
				$this->response = new AjaxResponse("", array("Invalid direction specified"));
				return;
			}
			
			$rulesIn = isset($parameters['rules']) ? $parameters['rules'] : array();
			$chain = new FirewallChain("filter", "forward_$direction");
			$rule = new FirewallRule();
			
			foreach ($rulesIn as $ruleIn)
			{
				foreach ($ruleIn as $key => $value)
				{
					if ($value != "*" && !preg_match("/^new/", $value))
						$rule->setAttribute($key, $value);
				}
				
				$chain->add($rule);
			}
			
			$chain->save();
			$chain->apply();
			
			$this->response = new AjaxResponse();
		}
		
		// Override
		public function getResponse()
		{
			return $this->response;
		}
	}
?>