<?php
	require_once "ajaxAccessControl.php";
	
	require_once "FirewallFilterSettings.class.php";
	require_once "FirewallFilterTable.class.php";
	require_once "FirewallFilterRule.class.php";
	require_once "NetUtils.class.php";
	
	header("Content-Type: application/json");
	
	// Hidden fields
	$ruleId		= trim($_REQUEST['ruleId']);
	$direction	= trim($_REQUEST['dir']);
	
	// User-specified fields
	$protocol	= trim($_REQUEST['protocol']);
	$srcAddr	= trim($_REQUEST['srcAddr']);
	$srcPort	= trim($_REQUEST['srcPort']);
	$dstAddr	= trim($_REQUEST['dstAddr']);
	$dstPort	= trim($_REQUEST['dstPort']);
	$connStates	= $_REQUEST['connState'];
	$fragmented	= trim($_REQUEST['fragmented']);
	$icmpType	= trim($_REQUEST['icmpType']);
	$target		= trim($_REQUEST['target']);
	
	// Validate input
	$errors = array();
	$rule = null;
	
	// Rule ID
	if (!empty($ruleId))
	{
		try
		{
			$rule = FirewallFilterSettings::getRule($ruleId);
		}
		catch (Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
	}
	
	// Direction
	if ($direction != "in" && $direction != "out")
		$errors[] = "Invalid or missing direction specified";
	else
		$chainName = $direction == "in" ? "forward_in" : "forward_out";
		
	// Protocol
	if ($protocol != "any" && !NetUtils::isValidProtocol($protocol))
		$errors[] = "Invalid network protocol specified";
		
	// Source address
	if (!empty($srcAddr) && !NetUtils::isValidIp($srcAddr) && !NetUtils::isValidNetwork($srcAddr))
		$errors[] = "Invalid source address specified";
		
	// Source port
	if (($protocol == "tcp" || $protocol == "udp") && !empty($srcPort) && !NetUtils::isValidIanaPortNumber($srcPort))
		$errors[] = "Invalid source port specified";

	// Destination address
	if (!empty($dstAddr) && !NetUtils::isValidIp($dstAddr) && !NetUtils::isValidNetwork($dstAddr))
		$errors[] = "Invalid destination address specified";
		
	// Destination port
	if (($protocol == "tcp" || $protocol == "udp") && !empty($dstPort) && !NetUtils::isValidIanaPortNumber($dstPort))
		$errors[] = "Invalid destination port specified";
		
	// Connection states
	if (!empty($connStates) && !NetUtils::isValidConnectionStates($connStates))
		$errors[] = "Invalid connection state entered";
		
	// Fragmented
	if ($fragmented != "any" && $fragmented != "Y" && $fragmented != "N")
		$errors[] = "Invalid fragmented value specified";
		
	// ICMP type
	if ($protocol == "icmp" && $icmpType != "any" && !NetUtils::isValidIcmpType($icmpType))
		$errors[] = "Invalid icmp type specified";
		
	// Target
	if ($target != "DROP" && $target != "ACCEPT")
		$errors[] = "Invalid target specified";
		
	if (empty($errors))
	{
		// Add/edit rule
		if (empty($rule))
		{
			$rule = new FirewallFilterRule();
			$isNewRule = true;
		}
		else
			$isNewRule = false;
		
		$rule->setAttribute("chain_name", $chainName);
		setRuleAttribute($rule, "protocol", $protocol, $isNewRule);
		
		if ($protocol == "tcp" || $protocol == "udp")
			setRuleAttribute($rule, "sport", $srcPort, $isNewRule);
		else
			$rule->removeAttribute("sport");
			
		if ($protocol == "tcp" || $protocol == "udp")
			setRuleAttribute($rule, "dport", $dstPort, $isNewRule);
		else
			$rule->removeAttribute("dport");
			
		if ($protocol == "icmp")
			setRuleAttribute($rule, "icmp_type", $icmpType, $isNewRule);
		else
			$rule->removeAttribute("icmp_type");
			
		setRuleAttribute($rule, "src_addr", $srcAddr, $isNewRule);
		setRuleAttribute($rule, "dst_addr", $dstAddr, $isNewRule);
		setRuleAttribute($rule, "state", @implode(",", $connStates), $isNewRule);
		setRuleAttribute($rule, "fragmented", $fragmented, $isNewRule);				
		$rule->setAttribute("target", $target);
		
		try
		{
			if ($isNewRule)
				$rule->executeInsert(true);
			else
				$rule->executeUpdate(true);
		}
		catch (Exception $ex)
		{
			$result = (object) array("result" => false, "errors" => array($ex->getMessage()));
		}
			
		$filterTable = new FirewallFilterTable();
		$result = (object) array("result" => true, "fwFilterTableHtml" => $filterTable->out($direction));
	}
	else
		$result = (object) array("result" => false, "errors" => $errors);
		
	echo json_encode($result);
	
	function setRuleAttribute(FirewallFilterRule &$rule, $key, $value, $isNewRule)
	{
		if ($value == "any")
			$value = null;
			
		if (($isNewRule && !empty($value)) || (!$isNewRule && !empty($value)))
			$rule->setAttribute($key, $value);
		else
			$rule->setAttribute($key, null);
	}
?>