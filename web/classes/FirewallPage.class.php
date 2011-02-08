<?php
	require_once "Page.class.php";
	require_once "FirewallChain.class.php";
	require_once "NetUtils.class.php";
	require_once "User.class.php";
	
	class FirewallPage implements Page
	{
		// Override
		public function id()
		{
			return "firewall";
		}
		
		// Override
		public function name()
		{
			return "Firewall";
		}
		
		// Override
		public function head()
		{
			return <<<END
			
			<script src="js/jquery.tablednd_0_5.js" type="text/javascript"></script>
			<link rel="StyleSheet" type="text/css" href="css/tablednd.css" />
END;
		}
		
		// Override
		public function style()
		{
			return <<<END
			
			#fwTable
			{
				margin-top: 10px;
			}
			
			#firewall-table
			{
				text-align: center;
				margin-left: auto;
				margin-right: auto;
				width: 90%;
				border-style: solid;
				border: 0px;
}

			#firewall-table th
			{
				background-color: #A8A8A8;
				text-align: center;
			}
			
			#fwActions
			{
				margin-top: 15px;
			}
			
			.firewall-table-protocol-col
			{
				width: 17%;
			}

			.firewall-table-address-col
			{
				width: 25%;
			}

			.firewall-table-port-col
			{
				width: 16%;
			}

			.fwRuleDetails
			{
				background-color: #FFFF00;
				padding: 10px;
				border-style: solid;
				border-width: 1px;
				font-size: 12px;
				z-index: 1;
			}
			
			.fwDetailsTable
			{
				border: 0px;
			}
			
			.fwRuleDrop
			{
				background-color: #FFAAAA;
			}

			.fwRuleAccept
			{
				background-color: #99FF99;
			}
END;
		}
		
		// Override
		public function javascript()
		{
			$isAdminUser = User::getUser()->isAdminUser();
			
			return <<<END
			
			var isAdminUser = "$isAdminUser" == 1 ? true : false;
			
			$(document).ready(function()
			{
				// Initialize elements
				$.initElwoodPopups();
				$("#saveBtn").hide();
				$("#fwResults").hide();
				$(".fwRuleDetails").hide();
				makeFirewallTableEditable();
				addRuleDetailsPopup();
				
				if (!isAdminUser)
				{
					$("#addRuleBtn").attr("disabled", "disabled");
					$("[id $= editRuleBtn]").attr("disabled", "disabled");
				}
			
				// Register event handlers
				$("#cancelBtn").click(function()
				{
					$("#addEditRulePopup").closeElwoodPopup();
				});
			
				$("#deleteBtn").click(function()
				{
					$("#" + $("#ruleId").val()).remove();
					$("#" + $("#ruleId").val() + "details").remove();
					$("#addEditRulePopup").closeElwoodPopup();
					showSaveButton();
				});
			
				$("#saveAsNewBtn").click(function()
				{
					$("#ruleId").val("");
					$("#saveRuleBtn").click();
				});
				
				$("#saveRuleBtn").click(function()
				{
					// Save rule
					var connStates = [];
					$(".connState").each(function()
					{
						if ($(this).attr("checked"))
							connStates.push($(this).val());
					});
			
					var params =	{
										handler: "AddEditFwFilterRule",
										parameters:
										{
											id:			$("#ruleId").val(),
											protocol:	$("#protocol").val(),
											icmp_type:	$("#icmpType").val(),
											src_addr:	$("#srcAddr").val(),
											sport:		$("#srcPort").val(),
											dst_addr:	$("#dstAddr").val(),
											dport:		$("#dstPort").val(),
											state:		connStates.join(","),
											fragmented:	$("#fragmented").val(),
											target:		$("#target").val()
										}
									};
					
					$.getJSON("ajax/ajaxRequest.php", params, function(response)
					{
						if (response.errors.length > 0)
						{	
							$("#fwAddEditFilterRuleMsgs")
								.css("color", "red")
								.html("<ul><li>" + response.errors.join("</li><li>") + "</li></ul>");
						}
						else
						{
							if (params.parameters.id.length > 0)
							{
								$("#" + params.parameters.id).replaceWith(response.responseText.row);
								$("#" + params.parameters.id + "details").replaceWith(response.responseText.div);
							}
							else
							{
								$("#firewall-table tbody").append(response.responseText.row);
								$("#fwTable").append(response.responseText.div);
							}
							
							addRuleDetailsPopup();
							makeFirewallTableEditable();
							$(".fwRuleDetails").hide();
							$("#addEditRulePopup").closeElwoodPopup();
							showSaveButton();
						}
					});
					
				});
						
				$("#addRuleBtn").click(function(){addEditFilterRuleDlg(false);});
			
				$("#saveBtn").click(function()
				{
					// Save firewall rules on the server
					reorderRules();
					
					var params =	{
										handler: "ApplyFwFilterRules",
										parameters:
										{
											direction:	$("#dir").val(),
											rules: 		getClientRules()
										}
									};
					
					$.post("ajax/ajaxRequest.php", params, function(response)
					{
						if (response.errors.length > 0)
						{
							$("#fwResults")
								.css("color", "red")
								.html("<ul><li>" + response.errors.join("</li><li>") + "</li></ul>")
								.show();
						}
						else
						{
							$("#saveBtn").hide();
							$("#fwResults")
								.css("color", "green")
								.html("Firewall settings saved successfully")
								.show()
								.fadeOut(3000);
						}
					});
				});
			});
			
			function getClientRules()
			{
				var rules = [];
			
				$(".fwDetailsTable").each(function()
				{				
					var rule =	{
									src_addr:	$(this).children().children().children("[id $= 'src_addr']").html(),
									dst_addr:	$(this).children().children().children("[id $= 'dst_addr']").html(),
									state:		$(this).children().children().children("[id $= 'state']").html(),
									fragmented:	$(this).children().children().children("[id $= 'fragmented']").html(),
									protocol:	$(this).children().children().children("[id $= 'protocol']").html(),
									dport:		$(this).children().children().children("[id $= 'dport']").html(),
									sport:		$(this).children().children().children("[id $= 'sport']").html(),
									icmp_type:	$(this).children().children().children("[id $= 'icmp_type']").length > 0 ? $(this).children().children().children("[id $= 'icmp_type']").html() : "*",
									target:		$(this).children().children().children("[id $= 'target']").html()
								};
					
					rules.push(rule);
				});
			
				return rules;
			}
			
			function makeFirewallTableEditable()
			{
				$("#firewall-table tr[class ^= 'fwRule']").each(function()
				{
					if ($(this).children("td:last").children("[id $= editRuleBtn]").length == 0)
						$(this).append("<td><button id=\"" + $(this).attr("id") + "editRuleBtn\" type=\"button\">Edit</button></td>");
				});
				
				$("button[id $= 'editRuleBtn']").each(function()
				{
					$(this).click(function(){addEditFilterRuleDlg($(this).parent().parent().attr("id"));});
				});
			
				// Initialize firewall table
				if (isAdminUser)
				{
					$("#firewall-table").tableDnD(
					{
						onDragClass: "tableRowMove",
						onDrop: function(table, row){showSaveButton();}
					});
				}
			}
				
			function addEditFilterRuleDlg(ruleId)
			{	
				$("#saveAsNewBtn").attr("disabled", "disabled");
				$("#deleteBtn").attr("disabled", "disabled");
				$("#fwAddEditFilterRuleMsgs").html("");
				resetAddEditRuleForm();
			
				if (ruleId)
				{
					// Edit existing rule
					$("#saveAsNewBtn").removeAttr("disabled");
					$("#deleteBtn").removeAttr("disabled");
			
					// Set values
					$("#ruleId").val(ruleId);
					$("#protocol").val($("#" + ruleId + "protocol").html() == "*" ? "any" : $("#" + ruleId + "protocol").html());
			
					if ($("#" + ruleId + "icmp_type").length > 0)
						$("#icmpType").val($("#" + ruleId + "icmp_type").html() == "*" ? "any" : $("#" + ruleId + "icmp_type").html());
					
					$("#srcAddr").val($("#" + ruleId + "src_addr").html() == "*" ? "" : $("#" + ruleId + "src_addr").html());
					$("#srcPort").val($("#" + ruleId + "sport").html() == "*" ? "" : $("#" + ruleId + "sport").html());
					$("#dstAddr").val($("#" + ruleId + "dst_addr").html() == "*" ? "" : $("#" + ruleId + "dst_addr").html());
					$("#dstPort").val($("#" + ruleId + "dport").html() == "*" ? "" : $("#" + ruleId + "dport").html());	
					$(".connState").each(function()
					{
						var stateOption = $(this).val();
						var ruleState = $("#" + ruleId + "state").html();
			
						if (ruleState.indexOf(stateOption) != -1)
							$(this).attr("checked", "checked");
					});
					$("#fragmented").val($("#" + ruleId + "fragmented").html() == "*" ? "any" : $("#" + ruleId + "fragmented").html());
					$("#target").val($("#" + ruleId + "target").html());
				}
				else
					$("#ruleId").val("");
				
				$("#addEditRulePopup").openElwoodPopup();
			}
			
			function showSaveButton()
			{
				if (!$("#saveBtn").is(":visible"))
					$("#saveBtn").fadeIn();
			}
			
			function reorderRules()
			{
				$($("#firewall-table tr[class ^= 'fwRule']").get().reverse()).each(function()
				{
					var ruleId = $(this).attr("id");
					$("#firewall-table").after($("#" + ruleId + "details"));
				});
			}
			
			function resetAddEditRuleForm()
			{
				$("#addEditRuleForm").each(function()
				{
					this.reset();
				});
				
				// IE fails to reset these back to their default values, so we'll set them
				// manually
				$("#protocol").val("any");
				$("#icmpType").val("any");
				$("#fragmented").val("any");
				$("#target").val("DROP");
			}
			
			function addRuleDetailsPopup()
			{
				$("#firewall-table tr[class ^= 'fwRule']")
					.mouseover(function(e)
					{
						// Display firewall rule details
						$("#" + $(this).attr("id") + "details").css({position: "absolute",
															 		left: e.pageX,
															 		top: $(this).position().top + $(this).height(),
															 		display: "inline"}).show();
					})
					.mouseout(function()
					{
						// Hide firewall rule details
						$("#" + $(this).attr("id") + "details").hide();
					});
			}
END;
		}
		
		// Override
		public function content(array $parameters)
		{			
			$direction = $parameters['dir'] == null ? "in" : $parameters['dir'];
			$chain = new FirewallChain("filter", "forward_$direction");
			$chain->load();
			
			$inSelect = "";
			$outSelect = "";
			
			switch ($direction)
			{
				case "in":
					$inSelect = "-selected";
					break;
				default:
					$outSelect = "-selected";
					break;
			}
			
			return <<<END
			
			<div style="margin: 15px;">
				<div class="tab-panel">
					<a class="tab$inSelect" href="elwoodPage.php?page=Firewall&dir=in">Incoming</a>
					<a class="tab$outSelect" href="elwoodPage.php?page=Firewall&dir=out">Outgoing</a>
				</div>
				<div class="tab-content">
					<button id="addRuleBtn">Add Rule</button>
					<div id="fwTable">
						{$chain->toHtml(($direction == "in" ? "Incoming " : "Outgoing ") . "Traffic")}
					</div>
					<div id="fwActions">
						<input id="saveBtn" type="button" value="Save Rules" />
						<div id="fwResults"></div>
					</div>
				</div>
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