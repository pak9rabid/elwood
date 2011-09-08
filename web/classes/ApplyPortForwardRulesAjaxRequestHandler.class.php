<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "FirewallRule.class.php";
	require_once "FirewallChain.class.php";
	require_once "User.class.php";
	
	class ApplyPortForwardRulesAjaxRequestHandler implements AjaxRequestHandler
	{		
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
				return new AjaxResponse("", array("Only admin users are allowed to make changes to the firewall"));
			
			$chain = new FirewallChain("nat", "port_forward");
			$rule = new FirewallRule();
			$rulesIn = isset($parameters['rules']) ? $parameters['rules'] : array();
			
			foreach ($rulesIn as $ruleIn)
			{
				$rule->clear();
				
				foreach ($ruleIn as $key => $value)
				{
					if ($value != "*" && !preg_match("/^new/", $value))
						$rule->setAttribute($key, $value);
				}
				
				$errors = $rule->validate();
				
				if (!empty($errors))
					return new AjaxResponse("", array("One or more of the NAT rules contains invalid data"));
				
				$chain->add($rule);
			}
			
			$chain->save();
			$chain->apply();
			
			return new AjaxResponse();
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>