<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "FirewallFilterSettings.class.php";
	
	class GetActiveFirewallRulesAjaxRequestHandler implements AjaxRequestHandler
	{
		private $response;
		
		// Override
		public function processRequest(array $parameters)
		{
			$simplifiedRules = array();
			$dir = $parameters['dir'];
			$rules = $dir == "out" ? FirewallFilterSettings::getRules("forward_out") : FirewallFilterSettings::getRules("forward_in");
			
			foreach ($rules as $rule)
			{
				$simplifiedRule = array();
				
				$simplifiedRule['id'] = $rule->getAttribute("id");
				$simplifiedRule['protocol'] = $rule->getAttributeDisp("protocol");
				$simplifiedRule['src_addr'] = $rule->getAttributeDisp("src_addr");
				$simplifiedRule['sport'] = $rule->getAttributeDisp("sport");
				$simplifiedRule['dst_addr'] = $rule->getAttributeDisp("dst_addr");
				$simplifiedRule['dport'] = $rule->getAttributeDisp("dport");
				$simplifiedRule['state'] = $rule->getAttributeDisp("state");
				$simplifiedRule['fragmented'] = $rule->getAttributeDisp("fragmented");
				$simplifiedRule['icmp_type'] = $rule->getAttributeDisp("icmp_type");
				$simplifiedRule['target'] = $rule->getAttributeDisp("target");
				
				$simplifiedRules[] = $simplifiedRule;
			}
			
			$this->response = new AjaxResponse($simplifiedRules);
		}
		
		// Override
		public function getResponse()
		{
			return $this->response;
		}
	}
?>