<?php
	require_once "Page.class.php";
	require_once "FirewallChain.class.php";
	require_once "User.class.php";
	
	class NATOneToOnePage implements Page
	{
		// Override
		public function id()
		{
			return "NATOneToOne";
		}
		
		// Override
		public function name()
		{
			return "1:1";
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
				$(".natOneToOneInput").change(showSaveBtn);
				
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
										handler: "AddEditNATOneToOneRule",
										parameters:
										{
											id: $("#ruleId").val(),
											outsideAddr: $("#outsideAddr").val(),
											insideAddr: $("#insideAddr").val()
										}
									};
									
					$.getJSON("ajax/ajaxRequest.php", params, function(response)
					{
						if (response.errors.length > 0)
						{
							$("#addOneToOneNatRuleMsgs")
								.css("color", "red")
								.html("<ul><li>" + response.errors.join("</li><li>") + "</li></ul>");
						}
						else
						{
							var row =	"<tr id='" + response.responseText + "' class='oneToOneRule'>" +
											"<td class='outsideAddr'>" + params.parameters.outsideAddr + "</td>" +
											"<td class='insideAddr'>" + params.parameters.insideAddr + "</td>" +
											"<td><button class='editRuleBtn' type='button'>Edit</button></td>" +
										"</tr>";
										
							if (params.parameters.id.length > 0)
								$("#" + params.parameters.id).replaceWith(row);
							else
								$("#oneToOneNatTable tbody").append(row);
								
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
										handler: "ApplyOneToOneNatRules",
										parameters:
										{
											rules: getOneToOneNatRules()
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
								.html("1:1 NAT settings saved successfully")
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
				$("#addOneToOneNatRuleForm").each(function()
				{
					this.reset();
				});
			}
			
			function addEditRuleDlg(ruleId)
			{
				$("#saveAsNewBtn").attr("disabled", "disabled");
				$("#deleteBtn").attr("disabled", "disabled");
				$("#addOneToOneNatRuleMsgs").html("");
				resetForm();
				
				if (ruleId)
				{
					// Edit existing rule
					$("#saveAsNewBtn").removeAttr("disabled");
					$("#deleteBtn").removeAttr("disabled");
					
					// Set values
					var row = $("#" + ruleId);
					var outsideAddr = row.children("td.outsideAddr").html();
					var insideAddr = row.children("td.insideAddr").html();
					
					$("#ruleId").val(ruleId);
					$("#outsideAddr").val(outsideAddr);
					$("#insideAddr").val(insideAddr);
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
					$("#oneToOneNatTable").tableDnD(
					{
						onDragClass: "tableRowMove",
						onDrop: function(table, row){showSaveBtn();}
					});
				}
			}
			
			function getOneToOneNatRules()
			{
				var rules = [];
				
				$("tr.oneToOneRule").each(function()
				{
					var rule =	{
									outsideAddr: $(this).children("td.outsideAddr").html(),
									insideAddr: $(this).children("td.insideAddr").html()
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
			$natOneToOneInChain = new FirewallChain("nat", "one2one_in");
			$natOneToOneInChain->load();
			
			$content =  <<<END
					
			<button type="button" id="addRuleBtn">Add Rule</button>
			<br /><br />
			<table id="oneToOneNatTable" class="nat-table" style="width: 400px;">
				<tr>
					<th>Outside IP</th><th>Inside IP</th><th>&nbsp;</th>
				</tr>
END;

			foreach ($natOneToOneInChain->getRules() as $rule)
			{
				$target = $rule->getAttributeDisp("target");
				list($temp1, $temp2, $destination) = explode(" ", $target);
				
				$content .= <<<END
				
				<tr id="{$rule->getAttributeDisp("id")}" class="oneToOneRule">
					<td class="outsideAddr">{$rule->getAttributeDisp("dst_addr")}</td>
					<td class="insideAddr">$destination</td>
					<td><button type="button" class="editRuleBtn">Edit</button></td>
				</tr>
END;
			}
			
			return $content . <<<END
			
			</table>
END;
		}
		
		// Override
		public function popups(array $parameters)
		{
			return <<<END
			
			<div id="addRulePopup" class="elwoodPopup">
				<div id="addOneToOneNatRuleMsgs"></div>
				<form id="addOneToOneNatRuleForm">
					<input type="hidden" id="ruleId" name="ruleId" value="" />
					<table>
						<!-- Outside IP -->
						<tr>
							<td class="tabInputLabel">Outside IP:</td>
							<td class="tabInputValue"><input id="outsideAddr" class="textfield" type="text" maxlength="15" size="17" name="outsideAddr">
						</tr>
						
						<!-- Inside IP -->
						<tr>
							<td class="tabInputLabel">Inside IP:</td>
							<td class="tabInputValue"><input id="insideAddr" class="textfield" type="text" maxlength="15" size="17" name="insideAddr">
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