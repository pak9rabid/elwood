<?php
	require_once "ajaxAccessControl.php";
	require_once "FirewallFilterRule.class.php";
	
	$id			= trim($_REQUEST['id']);
	$protocol	= trim($_REQUEST['protocol']);
	$srcAddr	= trim($_REQUEST['src_addr']);
	$srcPort	= trim($_REQUEST['sport']);
	$dstAddr	= trim($_REQUEST['dst_addr']);
	$dstPort	= trim($_REQUEST['dport']);
	$connStates	= trim($_REQUEST['state']);
	$fragmented	= trim($_REQUEST['fragmented']);
	$icmpType	= trim($_REQUEST['icmp_type']);
	$target		= trim($_REQUEST['target']);
	
	// Validate input
	$errors = array();
	$rule = new FirewallFilterRule();
	
	// Rule id
	if (!empty($id))
		$rule->setAttribute("id", $id);
	
	// Protocol
	if (NetUtils::isValidProtocol($protocol))
		$rule->setAttribute("protocol", $protocol);
	else if ($protocol != "any")
		$errors[] = "Invalid network protocol specified";
	
	// Source address
	if (NetUtils::isValidIp($srcAddr) || NetUtils::isValidNetwork($srcAddr))
		$rule->setAttribute("src_addr", $srcAddr);
	else if (!empty($srcAddr))
		$errors[] = "Invalid source address specified";
		
	// Source port
	if (($protocol == "tcp" || $protocol == "udp") && NetUtils::isValidIanaPortNumber($srcPort))
		$rule->setAttribute("sport", $srcPort);
	else if (!empty($srcPort) && ($protocol == "tcp" || $protocol == "udp"))
		$errors[] = "Invalid source port specified";

	// Destination address
	if (NetUtils::isValidIp($dstAddr) || NetUtils::isValidNetwork($dstAddr))
		$rule->setAttribute("dst_addr", $dstAddr);
	else if (!empty($dstAddr))
		$errors[] = "Invalid destination address specified";
		
	// Destination port
	if (($protocol == "tcp" || $protocol == "udp") && NetUtils::isValidIanaPortNumber($dstPort))
		$rule->setAttribute("dport", $srcPort);
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
		
	// Return result
	if (empty($errors))
		$result = (object) array("result" => true, "html" => $rule->out());
	else
		$result = (object) array("result" => false, "errors" => $errors);
		
	echo json_encode($result);
?>