<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "User.class.php";
	require_once "NetworkInterface.class.php";
	require_once "FirewallChain.class.php";
	
	class ApplyOneToOneNatRulesAjaxRequestHandler implements AjaxRequestHandler
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
			
			$oneToOneNatInChain = new FirewallChain("nat", "one2one_in");
			$oneToOneNatOutChain = new Firewallchain("nat", "one2one_out");
			$rule = new FirewallRule();
			$rulesIn = isset($parameters['rules']) ? $parameters['rules'] : array();
			$errors = array();
			$wanInt = NetworkInterface::getInstance("wan");
			$wanInt->clearAliases();
			
			foreach ($rulesIn as $ruleIn)
			{
				// add IP alias to wan interface
				$wanInt->addAlias($ruleIn['outsideAddr'], $wanInt->getNetmask());
				
				// 1:1 in rule
				$rule->clear();
				$rule->setAllAttributes	(
											array	(
														"dst_addr" => $ruleIn['outsideAddr'],
														"target" => "DNAT --to-destination " . $ruleIn['insideAddr']
													)
										);
				
				$errors = $rule->validate();
				
				if (!empty($errors))
				{
					$this->response = new AjaxResponse("", array("One or more of the NAT rules contains invalid data"));
					return;
				}
										
				$oneToOneNatInChain->add($rule);
				
				// 1:1 out rule
				$rule->clear();
				$rule->setAllAttributes	(
											array	(
														"src_addr" => $ruleIn['insideAddr'],
														"target" => "SNAT --to-source " . $ruleIn['outsideAddr']
													)
										);

				$errors = $rule->validate();
				
				if (!empty($errors))
				{
					$this->response = new AjaxResponse("", array("One or more of the NAT rules contains invalid data"));
					return;
				}
				
				$oneToOneNatOutChain->add($rule);
			}
			
			$wanInt->save();
			$oneToOneNatInChain->save();
			$oneToOneNatOutChain->save();
			
			// this applies rules to the entire nat table, no need to call it again on $oneToOneNatOutChain
			$oneToOneNatInChain->apply();
			
			$wanInt->apply();
			
			$this->response = new AjaxResponse();
		}
		
		// Override
		public function getResponse()
		{
			return $this->response;
		}
	}
?>