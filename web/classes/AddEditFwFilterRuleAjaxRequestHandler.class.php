<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "FirewallRule.class.php";
	require_once "FirewallRulesTable.class.php";
	require_once "NetUtils.class.php";
	require_once "User.class.php";
	
	class AddEditFwFilterRuleAjaxRequestHandler implements AjaxRequestHandler
	{
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
				return new AjaxResponse("", array("Only admin users are allowed to add or edit firewall rules"));
			
			$id			= trim($parameters['id']);
			$protocol	= trim($parameters['protocol']);
			$srcAddr	= trim($parameters['src_addr']);
			$srcPort	= trim($parameters['sport']);
			$dstAddr	= trim($parameters['dst_addr']);
			$dstPort	= trim($parameters['dport']);
			$connStates	= trim($parameters['state']);
			$fragmented	= trim($parameters['fragmented']);
			$icmpType	= trim($parameters['icmp_type']);
			$target		= trim($parameters['target']);
			
			// Validate input
			$errors = array();
			$rule = new FirewallRule();
	
			// Rule id
			if (!empty($id))
				$rule->setAttribute("id", $id);
			else
				$rule->setAttribute("id", "new" . uniqid());
	
			// Protocol
			if (NetUtils::isValidProtocol($protocol))
				$rule->setAttribute("protocol", $protocol);
			else if ($protocol != "any")
				$errors[] = "Invalid network protocol specified";
	
			// Source address
			if (empty($srcAddr) || NetUtils::isValidIp($srcAddr) || NetUtils::isValidNetwork($srcAddr))
				$rule->setAttribute("src_addr", $srcAddr);
			else if (!empty($srcAddr))
				$errors[] = "Invalid source address specified";
		
			// Source port
			if (($protocol == "tcp" || $protocol == "udp") && NetUtils::isValidIanaPortNumber($srcPort))
				$rule->setAttribute("sport", $srcPort);
			else if (!empty($srcPort) && ($protocol == "tcp" || $protocol == "udp"))
				$errors[] = "Invalid source port specified";

			// Destination address
			if (empty($dstAddr) || NetUtils::isValidIp($dstAddr) || NetUtils::isValidNetwork($dstAddr))
				$rule->setAttribute("dst_addr", $dstAddr);
			else if (!empty($dstAddr))
				$errors[] = "Invalid destination address specified";
		
			// Destination port
			if (($protocol == "tcp" || $protocol == "udp") && NetUtils::isValidIanaPortNumber($dstPort))
				$rule->setAttribute("dport", $dstPort);
			else if (!empty($dstPort) && ($protocol == "tcp" || $protocol == "udp"))
				$errors[] = "Invalid destination port specified";
		
			// Connection states
			if (NetUtils::isValidConnectionStates(explode(",", $connStates)))
				$rule->setAttribute("state", $connStates);
			else if (!empty($connStates))
				$errors[] = "Invalid connection state entered";
		
			// Fragmented
			if ($fragmented == "Y" || $fragmented == "N")
				$rule->setAttribute("fragmented", $fragmented);
			else if ($fragmented != "any")
				$errors[] = "Invalid fragmented value specified";
		
			// ICMP type
			if ($protocol == "icmp" && NetUtils::isValidIcmpType($icmpType))
				$rule->setAttribute("icmp_type", $icmpType);
			else if (!empty($icmpType) && $protocol == "icmp")
				$errors[] = "Invalid icmp type specified";
		
			// Target		
			if ($target == "DROP" || $target == "ACCEPT")
				$rule->setAttribute("target", $target);
			else
				$errors[] = "Invalid target specified";
				
			if (!empty($errors))
				return new AjaxResponse("", $errors);
			
			$firewallTableRow = FirewallRulesTable::firewallRuleToTableRow($rule);
					
			return new AjaxResponse	(	(object) array	(
															"row" =>	$firewallTableRow->content(),
															"div" =>	"<div id=\"" . $rule->getAttribute("id") . "details\" class=\"fwRuleDetails\">" .
																			FirewallRulesTable::firewallRuleToTable($rule) .
																		"</div>",
															"js" =>		$firewallTableRow->javascript()
														)
									);
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>