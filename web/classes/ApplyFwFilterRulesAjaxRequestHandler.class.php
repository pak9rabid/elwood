<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "FirewallChain.class.php";
	require_once "FirewallRule.class.php";
	require_once "FileUtils.class.php";
	require_once "User.class.php";
	
	class ApplyFwFilterRulesAjaxRequestHandler implements AjaxRequestHandler
	{
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
				return new AjaxResponse("", array("Only admin users are allowed to make changes to the firewall"));
			
			$direction = trim($parameters['direction']);
			
			if (!in_array($direction, array("in", "out")))
				return new AjaxResponse("", array("Invalid direction specified"));
			
			$rulesIn = isset($parameters['rules']) ? $parameters['rules'] : array();
			$chain = new FirewallChain("filter", "forward_$direction");
			$rule = new FirewallRule();
			
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
					return new AjaxResponse("", array("One or more of the firewall rules contains invalid data"));
				
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