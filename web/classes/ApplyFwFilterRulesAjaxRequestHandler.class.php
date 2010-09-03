<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "IPTablesFwFilterTranslator.class.php";
	require_once "TempDatabase.class.php";
	require_once "FirewallFilterRule.class.php";
	require_once "FirewallFilterSettings.class.php";
	require_once "FileUtils.class.php";
	require_once "RouterSettings.class.php";
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
			$policy = trim($parameters['policy']);
			$rulesIn = isset($parameters['rules']) ? $parameters['rules'] : array();
	
			$rules = array();
	
			foreach ($rulesIn as $ruleIn)
			{
				$tempRule = new FirewallFilterRule();
		
				foreach ($ruleIn as $key => $value)
				{
					$tempRule->setAttribute("chain_name", "forward_" . $direction);
			
					if ($value != "*" && !preg_match("/^new/", $value))
						$tempRule->setAttribute($key, $value);
				}
		
				$rules[] = $tempRule;
			}
	
			// Set policy
			$forwardChain = FirewallFilterSettings::getChain("FORWARD");
			$forwardChain->setAttribute("policy", $policy);
			$forwardChain->executeUpdate(true);
		
			// Clear existing rules
			FirewallFilterSettings::clearRules("forward_" . $direction);
		
			// Set rules
			foreach ($rules as $rule)
				$rule->executeInsert(true);

			$iptablesRestore = IPTablesFwFilterTranslator::setSystemFromDb(true);
		
			// Write file
			FileUtils::writeToFile(RouterSettings::getSettingValue("ELWOOD_CFG_DIR") . "/firewall/filter.rules", implode("\n", $iptablesRestore) . "\n");
		
			TempDatabase::destroy();
	
			$this->response = new AjaxResponse();
		}
		
		// Override
		public function getResponse()
		{
			return $this->response;
		}
	}
?>