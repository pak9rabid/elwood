<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "FirewallRule.class.php";
	require_once "FirewallChain.class.php";
	require_once "User.class.php";
	require_once "RouterSettings.class.php";
	
	class ApplySNATRulesAjaxRequestHandler implements AjaxRequestHandler
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
			
			$chain = new FirewallChain("nat", "ip_masquerade");
			$natOutEnabledSetting = RouterSettings::getSetting("ENABLE_IPMASQUERADE");
			$natOutCustEnabledSetting = RouterSettings::getSetting("ENABLE_IPMASQUERADE_CUSTOM");
			
			if (isset($parameters['natOutEnabled']) && $parameters['natOutEnabled'])
			{
				$natOutEnabledSetting->setAttribute("value", "true");
				$rule = new FirewallRule();
				
				if (isset($parameters['natOutCustEnabled']) && $parameters['natOutCustEnabled'])
				{
					$natOutCustEnabledSetting->setAttribute("value", "true");
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
						{
							$this->response = new AjaxResponse("", array("One or more of the NAT rules contains invalid data"));
							return;
						}
				
						$chain->add($rule);
					}
				}
				else
				{
					$natOutCustEnabledSetting->setAttribute("value", "false");
					$rule->setAttribute("target", "MASQUERADE");
					$chain->add($rule);
				}
			}
			else
			{
				$natOutEnabledSetting->setAttribute("value", "false");
				$natOutCustEnabledSetting->setAttribute("value", "false");
			}

			$natOutEnabledSetting->executeUpdate();
			$natOutCustEnabledSetting->executeUpdate();
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