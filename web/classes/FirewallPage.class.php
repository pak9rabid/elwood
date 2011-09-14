<?php
	require_once "Page.class.php";
	require_once "FirewallChain.class.php";
	require_once "NetUtils.class.php";
	require_once "User.class.php";
	require_once "Button.class.php";
	require_once "SaveButton.class.php";
	require_once "ComboBox.class.php";
	require_once "TextField.class.php";
	require_once "CheckBox.class.php";
	require_once "HiddenInput.class.php";
	require_once "FirewallRulesTable.class.php";
	
	class FirewallPage extends Page
	{
		private $direction;
		private $firewallChain;
		
		public function __construct($parameters)
		{
			$this->direction = $parameters['dir'] == "out" ? "out" : "in";
			$this->firewallChain = new FirewallChain("filter", "forward_" . $this->direction);
			$this->firewallChain->load();
			
			$this->addElement(new FirewallRulesTable("firewall-table", $this->firewallChain));
			$this->addElement(new SaveButton("saveBtn"));
			$this->addElement(new Button("addRuleBtn", "Add Rule"));
			$this->addElement(new Button("saveRuleBtn", "Save"));
			$this->addElement(new Button("saveAsNewBtn", "Save As New"));
			$this->addElement(new Button("deleteBtn", "Delete"));
			$this->addElement(new Button("cancelBtn", "Cancel"));
			$this->addElement(new ComboBox("protocol", array_merge(array("any" => "any"), array_combine(NetUtils::getNetworkProtocols(), NetUtils::getNetworkProtocols()))));
			$this->addElement(new ComboBox("icmpType", array_combine(NetUtils::getIcmpTypes(), NetUtils::getIcmpTypes())));
			$this->addElement(new ComboBox("fragmented", array("any" => "any", "Yes" => "Y", "No" => "N")));
			$this->addElement(new ComboBox("target", array("DROP" => "DROP", "ACCEPT" => "ACCEPT")));
			$this->addElement(new TextField("srcAddr"));
			$this->addElement(new TextField("srcPort"));
			$this->addElement(new TextField("dstAddr"));
			$this->addElement(new TextField("dstPort"));
			$this->addElement(new CheckBox("stateEstablished"));
			$this->addElement(new CheckBox("stateInvalid"));
			$this->addElement(new CheckBox("stateNew"));
			$this->addElement(new CheckBox("stateRelated"));
			$this->addElement(new HiddenInput("ruleId"));
			$this->addElement(new HiddenInput("dir", $this->direction));
			
			$this->getElement("protocol")->addHandler("change", "hideShowPorts")->addHandler("change", "hideShowIcmpTypes");
			$this->getElement("stateEstablished")->addClass("connState")->setValue("ESTABLISHED");
			$this->getElement("stateInvalid")->addClass("connState")->setValue("INVALID");
			$this->getElement("stateNew")->addClass("connState")->setValue("NEW");
			$this->getElement("stateRelated")->addClass("connState")->setValue("RELATED");
			$this->getElement("srcAddr")->setAttribute("maxlength", "20");
			$this->getElement("dstAddr")->setAttribute("maxlength", "20");
			$this->getElement("srcPort")->setAttribute("size", "5")->setAttribute("maxlength", "5");
			$this->getElement("dstPort")->setAttribute("size", "5")->setAttribute("maxlength", "5");
			$this->getElement("saveBtn")->addStyle("display", "none")->addHandler("click", "applyFirewallRules");
			$this->getElement("addRuleBtn")->addHandler("click", "addRule");
			$this->getElement("saveRuleBtn")->addHandler("click", "saveRule");
			$this->getElement("saveAsNewBtn")->addHandler("click", "saveRuleAsNew");
			$this->getElement("deleteBtn")->addHandler("click", "deleteRule");
			$this->getElement("cancelBtn")->addHandler("click", "closeAddEditRulePopup");

			$user = User::getUser();
			
			if ($user != null && !$user->isAdminUser())
			{
				foreach ($this->getElements() as $element)
				{
					if ($element instanceof FirewallRulesTable)
					{
						foreach ($element->getTable()->getRows() as $row)
						{
							if ($row instanceof FirewallTableRow)
							{
								$row->getEditButton()->setAttribute("disabled", "disabled");
								$row->updateContent();
							}
						}
					}
					else
						$element->setAttribute("disabled", "disabled");
				}
			}
			
		}
		
		// Override
		public function id()
		{
			return "Firewall";
		}
		
		// Override
		public function name()
		{
			return "Firewall";
		}
		
		// Override
		public function head(array $parameters)
		{
			return <<<END
			
			<script src="js/jquery.tablednd_0_5.js" type="text/javascript"></script>
			<link rel="StyleSheet" type="text/css" href="css/tablednd.css">
END;
		}
		
		// Override
		public function style(array $parameters)
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
				border-collapse: collapse;
			}
			
			#firewall-table tr
			{
				border: 1px solid;
			}
			
			#firewall-table th
			{
				background-color: #A8A8A8;
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
				display: none;
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
		public function javascript(array $parameters)
		{
			$js = parent::javascript($parameters);
			
			if (User::getUser()->isAdminUser())
			{
				$js .= <<<END
				$(makeFirewallTableEditable);
END;
			}
			
			return $js . <<<END
			$(hideShowPorts);
			$(hideShowIcmpTypes);
			
			function hideShowIcmpTypes()
			{
				if ($("#protocol").val() == "icmp")
					$(".icmpField").show();
				else
					$(".icmpField").hide();
			}
			
			function hideShowPorts()
			{
				if ($("#protocol").val() == "tcp" || $("#protocol").val() == "udp")
					$(".portField").show();
				else
					$(".portField").hide();
			}
			
			function makeFirewallTableEditable()
			{
				$("#firewall-table").tableDnD(
				{
					onDragClass: "tableRowMove",
					onDrop: function(table, row){showSaveButton();}
				});
			}
			
			function applyFirewallRules()
			{
				var saveButton = $("#saveBtn");
				
				reorderRules();
				
				var params =	{
									handler: "ApplyFwFilterRules",
									parameters:
									{
										direction: $("#dir").val(),
										rules: getClientRules()
									}
								};
								
				$.post("ajaxRequest.php", params, function(response)
				{
					if (response.errors.length > 0)
					{
						$("#fwResults")
							.css("color", "red")
							.html("<ul><li>" + response.errors.join("</li><li>") + "</li></ul>")
							.show();
							
						saveButton
							.html("Save")
							.removeAttr("disabled");
					}
					else
					{
						saveButton.hide();
						
						$("#fwResults")
							.css("color", "green")
							.html("Firewall settings saved successfully")
							.show()
							.fadeOut(3000);
					}
				});
			}
			
			function reorderRules()
			{
				$($("#firewall-table tr[class ^= 'fwRule']").get().reverse()).each(function()
				{
					var ruleId = $(this).attr("id");
					$("#firewall-table").after($("#" + ruleId + "details"));
				});
			}
			
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
			
			function showSaveButton()
			{
				if (!$("#saveBtn").is(":visible"))
				{
					$("#saveBtn")
						.html("Save")
						.removeAttr("disabled")	
						.fadeIn();
				}
			}
			
			function addRule()
			{
				addEditFilterRuleDlg(false);
			}
			
			function editRule()
			{
				addEditFilterRuleDlg($(this).parent().parent().attr("id"));
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
					
				hideShowPorts();
				hideShowIcmpTypes();
				$("#addEditRulePopup").openElwoodPopup();
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
			
			function saveRule()
			{
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
										id: $("#ruleId").val(),
										protocol: $("#protocol").val(),
										icmp_type: $("#icmpType").val(),
										src_addr: $("#srcAddr").val(),
										sport: $("#srcPort").val(),
										dst_addr: $("#dstAddr").val(),
										dport: $("#dstPort").val(),
										state: connStates.join(","),
										fragmented: $("#fragmented").val(),
										target: $("#target").val()
									}
								};
								
				$.post("ajaxRequest.php", params, function(response)
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
						
						eval(response.responseText.js);
						$(".fwRuleDetails").hide();
						$("#addEditRulePopup").closeElwoodPopup();
						showSaveButton();
						makeFirewallTableEditable();
					}
				});
			}
			
			function saveRuleAsNew()
			{
				$("#ruleId").val("");
				$("#saveRuleBtn").click();
			}
			
			function deleteRule()
			{
				var ruleId = $("#ruleId").val();
				
				$("#" + ruleId).remove();
				$("#" + ruleId + "details").remove();
				$("#addEditRulePopup").closeElwoodPopup();
				showSaveButton();
			}
			
			function closeAddEditRulePopup()
			{
				$("#addEditRulePopup").closeElwoodPopup();
			}
END;
		}
		
		// Override
		public function content(array $parameters)
		{
			$inSelect = "";
			$outSelect = "";
			
			switch ($this->direction)
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
					<a class="tab$inSelect" href="pageRequest.php?page=Firewall&dir=in">Incoming</a>
					<a class="tab$outSelect" href="pageRequest.php?page=Firewall&dir=out">Outgoing</a>
				</div>
				<div class="tab-content">
					{$this->getElement("addRuleBtn")}
					<div id="fwTable">
						{$this->getElement("firewall-table")}
					</div>
					<div id="fwActions">
						{$this->getElement("saveBtn")}
						<div id="fwResults"></div>
					</div>
				</div>
			</div>
END;
		}
		
		// Override
		public function popups(array $parameters)
		{
			return <<<END
			<div id="addEditRulePopup" class="elwoodPopup">
				<div id="fwAddEditFilterRuleMsgs"></div>
				<form id="addEditRuleForm">
					{$this->getElement("ruleId")}
					{$this->getElement("dir")}
					<table>
						<tr>
							<!-- Protocol -->
							<td class="tabInputLabel">Protocol:</td>
							<td class="tabInputValue">
								{$this->getElement("protocol")}
							</td>
						</tr>
						<tr class="icmpField">
						<!-- ICMP Type -->
							<td class="tabInputLabel">ICMP Type:</td>
							<td class="tabInputValue">
								{$this->getElement("icmpType")}
							</td>
						</tr>
						<tr>
							<!-- Source Address -->
							<td class="tabInputLabel">Source Address:</td>
							<td class="tabInputValue">{$this->getElement("srcAddr")}</td>
						</tr>
						<tr class="portField">
							<!-- Source Port -->
							<td class="tabInputLabel">Source Port:</td>
							<td class="tabInputValue">{$this->getElement("srcPort")}</td>
						</tr>
						<tr>
							<!-- Destination Address -->
							<td class="tabInputLabel">Destination Address:</td>
							<td class="tabInputValue">{$this->getElement("dstAddr")}</td>
						</tr>
						<tr class="portField">
							<!-- Destination Port -->
							<td class="tabInputLabel">Destination Port:</td>
							<td class="tabInputValue">{$this->getElement("dstPort")}</td>
						</tr>
						<tr>
							<!-- Connection State -->
							<td class="tabInputLabel">Connection State:</td>
							<td class="tabInputValue">
								{$this->getElement("stateEstablished")}Established
								{$this->getElement("stateInvalid")}Invalid
								{$this->getElement("stateNew")}New
								{$this->getElement("stateRelated")}Related
							</td>
						</tr>
						<tr>
							<!-- Fragmented -->
							<td class="tabInputLabel">Fragmented:</td>
							<td class="tabInputValue">
								{$this->getElement("fragmented")}
							</td>
						</tr>
						<tr>
							<!-- Target -->
							<td class="tabInputLabel">Target:</td>
							<td class="tabInputValue">
								{$this->getElement("target")}
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="2" align="center">
								{$this->getElement("saveRuleBtn")}
								{$this->getElement("saveAsNewBtn")}
								{$this->getElement("deleteBtn")}
								{$this->getElement("cancelBtn")}
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