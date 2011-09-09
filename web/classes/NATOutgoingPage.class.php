<?php
	require_once "Page.class.php";
	require_once "RouterSettings.class.php";
	require_once "FirewallChain.class.php";
	
	class NATOutgoingPage extends Page
	{
		// Override
		public function id()
		{
			return "NATOutgoing";
		}
		
		// Override
		public function name()
		{
			return "IP Masquerade";
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
			$ipMasqEnabled = RouterSettings::getSettingValue("ENABLE_IPMASQUERADE") == "true" ? "true" : "false";
			$ipMasqCustEnabled = RouterSettings::getSettingValue("ENABLE_IPMASQUERADE_CUSTOM") == "true" ? "true" : "false";

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
				$(".natOutInput").change(showSaveBtn);
				
				$("#natOutCustEnabled").change(function()
				{
					if ($(this).is(":checked"))
					{
						$("#natOutCust").show();
					}
					else
					{
						$("#natOutCust").hide();
					}
				});
						
				$("#natOutEnabled").change(function()
				{
					if ($(this).is(":checked"))
						$("#natOutCustEnabled").removeAttr("disabled");
					else
					{
						if ($("#natOutCustEnabled").is(":checked"))
						{
							$("#natOutCustEnabled").removeAttr("checked");
							$("#natOutCustEnabled").change();
						}
						
						$("#natOutCustEnabled").attr("disabled", "disabled");
					}
				});
						
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
					// Add rule
					var params =	{
										handler: "AddEditNATOutgoingRule",
										parameters:
										{
											id: $("#ruleId").val(),
											srcAddr: $("#srcAddr").val(),
											dstAddr: $("#dstAddr").val(),
											snatAuto: $("#snatAuto").is(":checked") ? 1 : 0,
											snatManual: $("#snatManual").is(":checked") ? 1 : 0,
											snatTo: $("#snatTo").val()
										}
									};
									
					$.getJSON("ajaxRequest.php", params, function(response)
					{
						if (response.errors.length > 0)
						{
							$("#addOutgoingNatRuleMsgs")
								.css("color", "red")
								.html("<ul><li>" + response.errors.join("</li><li>") + "</li></ul>");
						}
						else
						{
							var row =	"<tr id='" + response.responseText + "' class='snatRule'>" +
											"<td class='srcAddr'>" + (params.parameters.srcAddr == "" ? "*" : params.parameters.srcAddr) + "</td>" +
											"<td class='dstAddr'>" + (params.parameters.dstAddr == "" ? "*" : params.parameters.dstAddr) + "</td>" +
											"<td class='target'>" + (params.parameters.snatAuto ? 'Auto' : params.parameters.snatTo) + "</td>" +
											"<td><button class='editRuleBtn' type='button'>Edit</button></td>" +
										"</tr>";
										
							if (params.parameters.id.length > 0)
								$("#" + params.parameters.id).replaceWith(row);
							else
								$("#outgoingNatTable tbody").append(row);
								
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
						
					var params = 	{
										handler: "ApplySNATRules",
										parameters:
										{
											natOutEnabled: $("#natOutEnabled").is(":checked") ? 1 : 0,
											natOutCustEnabled: $("#natOutCustEnabled").is(":checked") ? 1 : 0,
											rules: getSnatRules()
										}
									};
									
					$.post("ajaxRequest.php", params, function(response)
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
								.html("NAT settings saved successfully")
								.show()
								.fadeOut(3000);
						}
					});
				});
				
				makeRulesEditable();
						
				// initialize input
				$("#saveBtn").hide();
				
				if ($ipMasqEnabled)
					$("#natOutEnabled").click();
				else
					$("#natOutCustEnabled").attr("disabled", "disabled");
				
				if ($ipMasqCustEnabled)
					$("#natOutCustEnabled").click();
				else
					$("#natOutCust").hide();
			});
								
			function resetForm()
			{
				$("#addOutgoingNatRuleForm").each(function()
				{
					this.reset();
				});
			}
			
			function addEditRuleDlg(ruleId)
			{
				$("#saveAsNewBtn").attr("disabled", "disabled");
				$("#deleteBtn").attr("disabled", "disabled");
				$("#addOutgoingNatRuleMsgs").html("");
				resetForm();
				
				if (ruleId)
				{
					// Edit existing rule
					$("#saveAsNewBtn").removeAttr("disabled");
					$("#deleteBtn").removeAttr("disabled");
					
					// Set values
					var row = $("#" + ruleId);
					var srcAddr = row.children("td.srcAddr").html() == "*" ? "" : row.children("td.srcAddr").html();
					var dstAddr = row.children("td.dstAddr").html() == "*" ? "" : row.children("td.dstAddr").html();
					var snatTo = row.children("td.target").html();
					
					$("#ruleId").val(ruleId);
					$("#srcAddr").val(srcAddr);
					$("#dstAddr").val(dstAddr);
					
					if (snatTo == "Auto")
						$("#snatAuto").click();
					else
					{
						$("#snatManual").click();
						$("#snatTo").val(snatTo);
					}
				}
				else
					$("#ruleId").val("");
					
				$("#addRulePopup").openElwoodPopup();
			}
			
			function makeRulesEditable()
			{
				$("button.editRuleBtn").click(function()
				{
					addEditRuleDlg($(this).parent().parent().attr("id"));
				});
				
				if (isAdminUser)
				{
					$("#outgoingNatTable").tableDnD(
					{
						onDragClass: "tableRowMove",
						onDrop: function(table, row){showSaveBtn();}
					});
				}
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
			
			function getSnatRules()
			{
				var rules = [];
				
				$("tr.snatRule").each(function()
				{
					var target = $(this).children("td.target").html();
					
					var rule =	{
									src_addr: $(this).children("td.srcAddr").html(),
									dst_addr: $(this).children("td.dstAddr").html(),
									target: (target == "Auto" ? "MASQUERADE" : "SNAT --to-source " + target)
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
			$natPostroutingChain = new FirewallChain("nat", "ip_masquerade");
			$natPostroutingChain->load();
					
			$content = <<<END
					
			<table class="ip-table" style="width: 600px;">
				<tr>
					<td align="left" style="padding: 5px;"><input id="natOutEnabled" class="natOutInput"  type="checkbox" name="natOutEnabled" />&nbsp;NAT outbound traffic (IP Masquerade)</td>
				</tr>
				<tr>
					<td align="left" style="padding: 5px;"><input id="natOutCustEnabled" class="natOutInput" type="checkbox" name="natOutCustEnabled" />&nbsp;Use custom NAT rules</td>
				</tr>
				<tr id="natOutCust">
					<td style="padding: 5px;">
						<fieldset>
							<legend>Custom NAT Rules</legend>
							<button type="button" id="addRuleBtn">Add Rule</button>
							<br /><br />
							<table style="width: 100%;" id="outgoingNatTable" class="nat-table">
								<tr class="nodrag nodrop">
									<th>Source</th>
									<th>Destination</th>
									<th>SNAT To</th>
									<th>&nbsp;</th>
								</tr>
END;
			foreach ($natPostroutingChain->getRules() as $rule)
			{
				$snatTo = $rule->getAttributeDisp("target");
				
				if ($snatTo == "MASQUERADE")
					$snatTo = "Auto";
				else
				{
					list($temp1, $temp2, $ip) = explode(" ", $snatTo);
					$snatTo = $ip;
				}
				
				$content .= <<<END
										
								<tr id="{$rule->getAttribute("id")}" class="snatRule">
									<td class="srcAddr">{$rule->getAttributeDisp("src_addr")}</td>
									<td class="dstAddr">{$rule->getAttributeDisp("dst_addr")}</td>
									<td class="target">$snatTo</td>
									<td><button type="button" class="editRuleBtn">Edit</button></td>
								</tr>
END;
			}
			
			$content .= <<<END
									
							</table>
						</fieldset>
					</td>
				</tr>
			</table>
END;
			return $content;
		}
		
		// Override
		public function popups(array $parameters)
		{
			$content = <<<END
					
			<div id="addRulePopup" class="elwoodPopup">
				<div id="addOutgoingNatRuleMsgs"></div>
				<form id="addOutgoingNatRuleForm">
					<input type="hidden" id="ruleId" name="ruleId" value="" />
					<table>								
						<!-- Source -->
						<tr>
							<td class="tabInputLabel">Source Address:</td>
							<td class="tabInputValue"><input class="natOutInput textfield" type="text" id="srcAddr" name="srcAddr" size="20" maxlength="20" /></td>
						</tr>
								
						<!-- Destination -->
						<tr>
							<td class="tabInputLabel">Destination Address:</td>
							<td class="tabInputValue"><input class="natOutInput textfield" type="text" id="dstAddr" name="dstAddr" size="20" maxlength="20" /></td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
								
						<!-- SNAT Action -->
						<tr>
							<td align="left" colspan="2">
								<input class="natOutInput" type="radio" id="snatAuto" name="snatMethod" />&nbsp;Automatically SNAT to outgoing interface's IP
							</td>
						</tr>
						<tr>
							<td align="left" colspan="2">
								<input class="natOutInput" type="radio" id="snatManual" name="snatMethod" />&nbsp;Manually SNAT to <input class="natOutInput textfield" type="text" id="snatTo" name="snatTo" size="15" maxlength="15" />
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
			return $content;
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>