<?php
	require_once "Page.class.php";
	require_once "NetUtils.class.php";
	require_once "User.class.php";
	require_once "FirewallChain.class.php";
	
	class NATPortForwardingPage extends Page
	{
		// Override
		public function id()
		{
			return "NATPortForwarding";
		}
		
		// Override
		public function name()
		{
			return "Port Forwarding";
		}
		
		// Override
		public function head(array $parameters)
		{
			return <<<END
			
			<script src="js/jquery.tablednd_0_5.js" type="text/javascript"></script>
			<link rel="StyleSheet" type="text/css" href="css/tablednd.css" />
END;
		}
		
		// Override
		public function style(array $parameters)
		{
			
		}
		
		// Override
		public function javascript(array $parameters)
		{
			$isAdminUser = User::getUser()->isAdminUser();
			
			return <<<END
			
			var isAdminUser = "$isAdminUser" == 1 ? true : false;
			
			$(document).ready(function()
			{
				$.initElwoodPopups();
				
				if (!isAdminUser)
				{
					$("#addRuleBtn").attr("disabled", "disabled");
					$(".editRuleBtn").attr("disabled", "disabled");
				}
					
				// event handlers
				$(".natInInput").change(showSaveBtn);
				
				$("#addRuleBtn").click(function(){addEditRuleDlg(false);});
				
				$("#cancelBtn").click(function()
				{
					$("#addRulePopup").closeElwoodPopup();
				});
				
				$("#deleteBtn").click(function()
				{
					$("#" + $("#ruleId").val()).remove();
					$("#addRulePopup").closeElwoodPopup();
					showSaveBtn();
				});
				
				$("#saveAsNewBtn").click(function()
				{
					$("#ruleId").val("");
					$("#saveRuleBtn").click();
				});
				
				$("#saveRuleBtn").click(function()
				{
					var params =	{
										handler: "AddEditNATIncomingRule",
										parameters:
										{
											id: $("#ruleId").val(),
											protocol: $("#protocol").val(),
											port: $("#port").val(),
											dstAddr: $("#dstAddr").val(),
											dstPort: $("#dstPort").val()
										}
									};
									
					$.getJSON("ajax/ajaxRequest.php", params, function(response)
					{
						if (response.errors.length > 0)
						{
							$("#addIncomingNatRuleMsgs")
								.css("color", "red")
								.html("<ul><li>" + response.errors.join("</li><li>") + "</li></ul>");
						}
						else
						{
							var row =	"<tr id='" + response.responseText + "' class='portForwardRule'>" +
											"<td class='protocol'>" + params.parameters.protocol + "</td>" +
											"<td class='port'>" + params.parameters.port + "</td>" +
											"<td class='destination'>" + params.parameters.dstAddr + ":" + params.parameters.dstPort + "</td>" +
											"<td><button class='editRuleBtn' type='button'>Edit</button></td>" +
										"</tr>";
										
							if (params.parameters.id.length > 0)
								$("#" + params.parameters.id).replaceWith(row);
							else
								$("#incomingNatTable tbody").append(row);
								
							makeRulesEditable();
							$("#addRulePopup").closeElwoodPopup();
							showSaveBtn();
						}
					});
				});
				
				$("#saveBtn").click(function()
				{					
					var saveButton = $(this);
					
					saveButton
						.html("Saving...&nbsp;<img src='images/loading.gif' />")
						.attr("disabled", "disabled");
						
					var params =	{
										handler: "ApplyPortForwardRules",
										parameters:
										{
											rules: getPortForwardRules()
										}
									};
									
					$.post("ajax/ajaxRequest.php", params, function(response)
					{
						if (response.errors.length > 0)
						{
							$("#messages")
								.css("color", "red")
								.html("<ul><li>" + response.errors.join("</li><li>") + "</li></ul>")
								.show();
								
							saveButton
								.html("Save")
								.removeAttr("disabled");
						}
						else
						{
							$("#saveBtn").hide();
							$("#messages")
								.css("color", "green")
								.html("Port Forwarding settings saved successfully")
								.show()
								.fadeOut(3000);
						}
					});
				});
				
				makeRulesEditable();
					
				// initialize input
				$("#saveBtn").hide();
			});
			
			function resetForm()
			{
				$("#addIncomingNatRuleForm").each(function()
				{
					this.reset();
				});
				
				$("#protocol").val("tcp");
			}
			
			function addEditRuleDlg(ruleId)
			{
				$("#saveAsNewBtn").attr("disabled", "disabled");
				$("#deleteBtn").attr("disabled", "disabled");
				$("#addIncomingNatRuleMsgs").html("");
				resetForm();
				
				if (ruleId)
				{
					// Edit existing rule
					$("#saveAsNewBtn").removeAttr("disabled");
					$("#deleteBtn").removeAttr("disabled");
					
					// Set values
					var row = $("#" + ruleId);
					var protocol = row.children("td.protocol").html();
					var port = row.children("td.port").html();
					var dstAddr = row.children("td.destination").html().split(":")[0];
					var dstPort = row.children("td.destination").html().split(":")[1];
					
					$("#ruleId").val(ruleId);
					$("#protocol").val(protocol);
					$("#port").val(port);
					$("#dstAddr").val(dstAddr);
					$("#dstPort").val(dstPort);
				}
				else
					$("#ruleId").val("");
					
				$("#addRulePopup").openElwoodPopup();
			}
			
			function showSaveBtn()
			{
				if (!$("#saveBtn").is(":visible"))
				{
					$("#saveBtn")
						.html("Save")
						.removeAttr("disabled")
						.fadeIn();
				}
			}
			
			function makeRulesEditable()
			{
				$("button.editRuleBtn").click(function()
				{
					addEditRuleDlg($(this).parent().parent().attr("id"));
				});
				
				if (isAdminUser)
				{
					$("#incomingNatTable").tableDnD(
					{
						onDragClass: "tableRowMove",
						onDrop: function(table, row){showSaveBtn();}
					});
				}
			}
			
			function getPortForwardRules()
			{
				var rules = [];
				
				$("tr.portForwardRule").each(function()
				{
					var destination = $(this).children("td.destination").html();
					
					var rule =	{
									protocol: $(this).children("td.protocol").html(),
									dport: $(this).children("td.port").html(),
									target: "DNAT --to-destination " + destination
								};
								
					rules.push(rule);
				});
				
				return rules;
			}
END;
		}
		
		// Override
		public function content(array $parameters)
		{
			$natPreroutingChain = new FirewallChain("nat", "port_forward");
			$natPreroutingChain->load();
			
			$content = <<<END
					
			<button type="button" id="addRuleBtn">Add Rule</button>
			<br /><br />
			<table id="incomingNatTable" class="nat-table" style="width: 600px;">
				<tr class="nodrag nodrop">
					<th>Protocol</th><th>Port</th><th>Destination</th><th>&nbsp</th>
				</tr>
END;
			foreach ($natPreroutingChain->getRules() as $rule)
			{
				$target = $rule->getAttributeDisp("target");
				list($temp1, $temp2, $destination) = explode(" ", $target);
				
				$content .= <<<END
				
				<tr id="{$rule->getAttributeDisp("id")}" class="portForwardRule">
					<td class="protocol">{$rule->getAttributeDisp("protocol")}</td>
					<td class="port">{$rule->getAttributeDisp("dport")}</td>
					<td class="destination">$destination</td>
					<td><button type="button" class="editRuleBtn">Edit</button></td>
				</tr>
END;
			}
			
			$content .= <<<END
			
			</table>
END;

			return $content;
		}
		
		// Override
		public function popups(array $parameters)
		{
			$content = <<<END
			
			<div id="addRulePopup" class="elwoodPopup">
				<div id="addIncomingNatRuleMsgs"></div>
				<form id="addIncomingNatRuleForm">
					<input type="hidden" id="ruleId" name="ruleId" value="" />
					<table>
						<!-- Protocol -->
						<tr>
							<td class="tabInputLabel">Protocol:</td>
							<td class="tabInputValue">
								<select id="protocol" name="protocol">
END;
			foreach (array("tcp", "udp") as $protocol)
			{
				$content .= <<<END
									<option value="$protocol">$protocol</option>
END;
			}
			
			return $content .= <<<END
								</select>
							</td>
						</tr>
								
						<!-- Port -->
						<tr>
							<td class="tabInputLabel">Port:</td>
							<td class="tabInputValue"><input class="textfield" type="text" id="port" name="port" size="7" maxlength="5" /></td>
						</tr>
						
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
														
						<!-- Destination Address -->
						<tr>
							<td class="tabInputLabel">Destination Address:</td>
							<td class="tabInputValue"><input class="textfield" type="text" id="dstAddr" name="dstAddr" size="17" maxlength="15" /></td>
						</tr>
						
						<!-- Destination Port -->
						<tr>
							<td class="tabInputLabel">Destination Port:</td>
							<td class="tabInputValue"><input class="textfield" type="text" id="dstPort" name="dstPort" size="7" maxlength="5" /></td>
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