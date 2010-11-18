<?php
	require_once "Page.class.php";
	require_once "TempDatabase.class.php";
	require_once "FirewallFilterTable.class.php";
	require_once "NetUtils.class.php";
	
	class FirewallPage implements Page
	{
		// Override
		public function name()
		{
			return "Firewall";
		}
		
		// Override
		public function head()
		{
			return <<<END
			
			<link rel="StyleSheet" type="text/css", href="css/elwoodpopup.css" />
			<script src="js/jquery.tablednd_0_5.js" type="text/javascript"></script>
			<script src="js/jquery.elwoodpopup.js" type="text/javascript"></script>
			<script src="js/firewall.js.php" type="text/javascript"></script>
END;
		}
		
		// Override
		public function content(array $parameters)
		{
			TempDatabase::create();
			
			$fwFilter = new FirewallFilterTable();
			$direction = $parameters['dir'] == null ? "in" : $parameters['dir'];
			
			return <<<END
			
			<a href="elwoodPage.php?page=Firewall&dir=in">Incoming </a>
			&nbsp;
			<a href="elwoodPage.php?page=Firewall&dir=out">Outgoing</a>
			<br /><br />
			<button id="addRuleBtn">Add Rule</button>
			<button id="changePolicyBtn">Change Policy</button>
			<div id="fwTable">
				{$fwFilter->out($direction)}
			</div>
			<div id="fwActions">
				<input id="saveBtn" type="button" value="Save Rules" />
				<div id="fwResults"></div>
			</div>
END;
		}
		
		// Override
		public function popups(array $parameters)
		{
			$direction = $parameters['dir'] == null ? "in" : $parameters['dir'];
			$out = <<<END
			<div id="addEditRulePopup" class="elwoodPopup">
				<div id="fwAddEditFilterRuleMsgs"></div>
				<form id="addEditRuleForm">
					<input type="hidden" id="ruleId" name="ruleId" value="" />
					<input type="hidden" id="dir" name="dir" value="$direction" />
					<table>
						<tr>
							<!-- Protocol -->
							<td class="tabInputLabel">Protocol:</td>
							<td class="tabInputValue">
								<select id="protocol">
									<option value="any">any</option>
END;
			foreach (NetUtils::getNetworkProtocols() as $protocol)
			{
				$out .= <<<END
				
									<option value="$protocol">$protocol</option>
END;
			}
			
			$out .= <<<END
			
								</select>
							</td>
						</tr>
						<tr>
						<!-- ICMP Type -->
							<td class="tabInputLabel">ICMP Type:</td>
							<td class="tabInputValue">
								<select id="icmpType">
END;
				 
			foreach (array_values(NetUtils::getIcmpTypes()) as $icmpType)
			{
				$out .= <<<END
				
				<option value="$icmpType">$icmpType</option>
END;
			}
				$out .= <<<END
								</select>
							</td>
						</tr>
						<tr>
							<!-- Source Address -->
							<td class="tabInputLabel">Source Address:</td>
							<td class="tabInputValue"><input type="text" id="srcAddr" name="srcAddr" size="20" maxlength="20" /></td>
						</tr>
						<tr>
							<!-- Source Port -->
							<td class="tabInputLabel">Source Port:</td>
							<td class="tabInputValue"><input type="text" id="srcPort" name="srcPort" size="20" maxlength="5" /></td>
						</tr>
						<tr>
							<!-- Destination Address -->
							<td class="tabInputLabel">Destination Address:</td>
							<td class="tabInputValue"><input type="text" id="dstAddr" name="dstAddr" size="20" maxlength="20" /></td>
						</tr>
						<tr>
							<!-- Destination Port -->
							<td class="tabInputLabel">Destination Port:</td>
							<td class="tabInputValue"><input type="text" id="dstPort" name="dstPort" size="20" maxlength="5" /></td>
						</tr>
						<tr>
							<!-- Connection State -->
							<td class="tabInputLabel">Connection State:</td>
							<td class="tabInputValue">
END;
				foreach (NetUtils::getConnectionStates() as $connState)
				{
					$out .= <<<END
				
					<input type="checkbox" class="connState" value="$connState" />$connState
END;
				}
			
				return $out .= <<<END
				
							</td>
						</tr>
						<tr>
							<!-- Fragmented -->
							<td class="tabInputLabel">Fragmented:</td>
							<td class="tabInputValue">
								<select id="fragmented">
									<option value="any" checked>any</option>
									<option value="Y">Yes</option>
									<option value="N">No</option>
								</select>
							</td>
						</tr>
						<tr>
							<!-- Target -->
							<td class="tabInputLabel">Target:</td>
							<td class="tabInputValue">
								<select id="target">
									<option value="DROP">DROP</option>
									<option value="ACCEPT">ACCEPT</option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="2" align="center">
								<button id="saveRuleBtn" type="button">Save</button>
								<button id="saveAsNewBtn" type="button">Save As New</button>
								<button id="deleteBtn" type="button">Delete</button>
								<button id="cancelBtn" type="button">Cancel</button>
							</td>
						</tr>
					</table>
				</form>
			</div>
END;
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>