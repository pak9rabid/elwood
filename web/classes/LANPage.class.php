<?php 
	require_once "Page.class.php";
	require_once "NetworkInterface.class.php";
	require_once "Service.class.php";
	
	class LANPage extends Page
	{
		private $lanInt;
		private $dhcpService;
		
		function __construct()
		{
			$this->lanInt = NetworkInterface::getInstance("LAN");
			$this->dhcpService = Service::getInstance("dhcp");
			$this->dhcpService->load();
		}
		
		// Override
		public function id()
		{
			return "LAN";
		}
		
		// Override
		public function name()
		{
			return "LAN Setup";
		}
		
		// Override
		public function style(array $parameters)
		{
		}
		
		// Override
		public function head(array $parameters)
		{
		}
		
		// Override
		public function javascript(array $parameters)
		{
			$isDhcpEnabled = $this->dhcpService->getAttribute("is_enabled") == "Y" ? "true" : "false";
			
			return <<<END
			
			var showSaveButton = function()
			{
				if (!$("#saveLanSettingsBtn").is(":visible"))
				{
					$("#saveLanSettingsBtn")
						.html("Save")
						.removeAttr("disabled")
						.fadeIn();
				}
			}
			
			$(document).ready(function()
			{
				// event handlers				
				$(".lanInput").change(showSaveButton);
				
				$("#addNameserverBtn").click(function()
				{
					var html =	"<tr class='removeable'>" +
									"<td><input class='textfield dhcpInput nameserver lanInput' size='15' maxlength='15' /></td>" +
									"<td width='30px'><button type='button' class='removeBtn' title='Remove nameserver'>-</button></td>" +
								"</tr>";
					
					addRemoveableField(html, $("#nameservers > tbody"));
				});
				
				$("#addDhcpRangeBtn").click(function()
				{
					var html =	"<tr class='removeable'>" +
									"<td><input class='textfield dhcpInput dhcpRangeStart lanInput' size='15' maxlength='15' /></td>" +
									"<td>-</td>" +
									"<td><input class='textfield dhcpInput dhcpRangeEnd lanInput' size='15' maxlength='15' /></td>" +
									"<td width='30x'><button type='button' class='removeBtn' title='Remove IP range'>-</button></td>" +
								"</tr>";
								
					addRemoveableField(html, $("#dhcpIpRanges > tbody"));
				});
				
				$("#addStickyIpBtn").click(function()
				{
					var html =	"<div class='removeable'>" +
									"<table style='width: 100%'>" +
										"<tr>" +
											"<td width='40%' align='right'>Name:</td>" +
											"<td align='left'><input class='textfield dhcpInput stickyName lanInput' size='20' maxlength='32' /></td>" +
											"<td></td>" +
										"</tr>" +
										"<tr>" +
											"<td align='right'>MAC:</td>" +
											"<td align='left'><input class='textfield dhcpInput stickyMac lanInput' size='20' maxlength='17' /></td>" +
											"<td><button type='button' class='removeBtn' title='Remove sticky IP'>-</button>" +
										"</tr>" +
										"<tr>" +
											"<td align='right'>IP:</td>" +
											"<td align='left'><input class='textfield dhcpInput stickyIp lanInput' size='20' maxlength='15' /></td>" +
										"</tr>" +
									"</table>" +
									"<br />" +
								"</div>";
								
					addRemoveableField(html, $("#stickyIps"));
				});
				
				$("#saveLanSettingsBtn").click(function()
				{
					var saveButton = $(this);
					
					saveButton
						.html("Saving...&nbsp;<img class='loading' src='images/loading.gif' />")
						.attr("disabled", "disabled");

					var nameservers = new Array();
					var ipRanges = new Array();
					var stickyIps = new Array();
					
					$(".nameserver").each(function()
					{
						nameservers.push($(this).val());
					});
					
					$(".dhcpRangeStart").each(function()
					{
						var ipRange =	{
											startIp: $(this).val(),
											endIp: $(this).parent().parent().find("input.dhcpRangeEnd").val()
										};
										
						ipRanges.push(ipRange);
					});
					
					$(".stickyName").each(function()
					{
						var stickyIp =	{
											name: $(this).val(),
											mac: $(this).parentsUntil("table").find("input.stickyMac").val(),
											ip: $(this).parentsUntil("table").find("input.stickyIp").val()
										};
										
						stickyIps.push(stickyIp);
					});
						
					var params =	{
										handler: "SaveLanSettings",
										parameters:
										{
											ipAddress: $("#ip").val(),
											mtu: $("#mtu").val(),
											isDhcpServerEnabled: $("#dhcpEnabled").is(":checked"),
											domain: $("#domain").val(),
											nameservers: nameservers,
											ipRanges: ipRanges,
											stickyIps: stickyIps
										}
									};
									
					$.post("ajaxRequest.php", params, function(response)
					{
						if (response.errors.length > 0)
						{
							$("#lanMessages")
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
							$("#lanMessages")
								.css("color", "green")
								.html("LAN settings saved successfully")
								.show()
								.fadeOut(3000);
						}
					});
				});
									
				// initialize
				$(".removeBtn").hide();
				$("#saveLanSettingsBtn").hide();
				$("#lanMessages").hide();
				
				if ($isDhcpEnabled)
					$("#dhcpEnabled").click();
				else
					$("#dhcpDisabled").click();
									
				makeRemoveable($(".removeable"));
			});
			
			function makeRemoveable(element)
			{
				element
					.mouseover(function()
					{
						$(this).find("button.removeBtn").show();
					})
					
					.mouseout(function()
					{
						$(this).find("button.removeBtn").hide();
					});
					
				element.find("button.removeBtn").click(function()
				{
					$(this).parentsUntil(".removeable").parent().remove();
					showSaveButton();
				});
			}
			
			function addRemoveableField(what, where)
			{
				where.append(what);
				$(".removeBtn").hide();
				makeRemoveable(where.find(".removeable:last"));
								
				where.find("input.lanInput").change(showSaveButton);
			}
END;
		}
		
		// Override
		public function content(array $parameters)
		{
			$out = <<<END

			<table class="ip-table" style="width: 400px;">
				<tr>
					<th colspan="2">Interface</th>
				</tr>
				<tr>
					<td align="right">IP Address:</td>
					<td align="left"><input name="ip" id="ip" class="textfield lanInput" size="15" maxlength="15" value="{$this->lanInt->getAddress()}" /></td>
				</tr>
				<tr>
					<td align="right">MTU:</td>
					<td align="left"><input name="mtu" id="mtu" class="textfield lanInput" size="4" maxlength="4" value="{$this->lanInt->getMtu()}" /></td>
				</tr>
			</table>
			<br />
			<table class="ip-table" style="width: 400px;">
				<tr>
					<th colspan="2">DHCP Server</th>
				</tr>
				<tr>
					<td align="right">DHCP Server:</td>
					<td align="left">
						<input type="radio" name="dhcpServer" class="lanInput" id="dhcpEnabled" />&nbsp;Enabled<br />
						<input type="radio" name="dhcpServer" class="lanInput" id="dhcpDisabled" />&nbsp;Disabled
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td align="right">Domain:</td>
					<td align="left"><input name="domain" id="domain" class="textfield dhcpInput lanInput" size="20" maxlength="64" value="{$this->dhcpService->getDomain()}" /></td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
					<td colspan="2">
						<fieldset>
							<legend>Nameservers</legend>
							<button type="button" id="addNameserverBtn" class="dhcpInput" title="Add nameserver">+</button>
							<br /><br />
							<table id="nameservers" align="center" style="border-collapse: collapse;">
								<tbody>
END;
			foreach ($this->dhcpService->getNameservers() as $nameserver)
			{
				$out .= <<<END
				
									<tr class="removeable">
										<td><input class="textfield dhcpInput nameserver lanInput" size="15" maxlength="15" value="$nameserver" /></td>
										<td width="30px"><button type="button" class="removeBtn" title="Remove nameserver">-</button></td>
									</tr>
END;
			}
						
				$out .= <<<END
								</tbody>                                                    
							</table>
						</fieldset>
					</td>
				<tr>
					<td colspan="2">
						<fieldset>
							<legend>IP Ranges</legend>
							<button type="button" id="addDhcpRangeBtn" class="dhcpInput" title="Add IP range">+</button><br />
							<br />
							<table id="dhcpIpRanges" align="center" style="border-collapse: collapse;">
								<tbody>
END;
			foreach ($this->dhcpService->getIpRanges() as $dhcpRange)
			{
				$out .= <<<END
									<tr class="removeable">
										<td><input class="textfield dhcpInput dhcpRangeStart lanInput" size="15" maxlength="15" value="{$dhcpRange->startIp}" /></td>
										<td>-</td>
										<td><input class="textfield dhcpInput dhcpRangeEnd lanInput" size="15" maxlength="15" value="{$dhcpRange->endIp}" /></td>
										<td width="30x"><button type="button" class="removeBtn" title="Remove IP range">-</button></td>
									</tr>
END;
			}
			
			$out .= <<<END
			
								</tbody>
							</table>
						</fieldset>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<fieldset id="stickyIps">
							<legend>Sticky IPs</legend>
							<button type="button" id="addStickyIpBtn" class="dhcpInput" title="Add sticky IP">+</button></br />
							<br /><br />
END;

			foreach ($this->dhcpService->getStickyIps() as $stickyIp)
			{
				$out .= <<<END
							<div class="removeable">
								<table style="width: 100%;">
									<tr>
										<td width="40%" align="right">Name:</td>
										<td align="left"><input class="textfield dhcpInput stickyName lanInput" size="20" maxlength="32" value="{$stickyIp->name}" /></td>
										<td></td>
									</tr>
							 		<tr>
							 			<td align="right">MAC:</td>
							 			<td align="left"><input class="textfield dhcpInput stickyMac lanInput" size="20" maxlength="17" value="{$stickyIp->mac}" /></td>
							 			<td><button type="button" class="removeBtn" title="Remove sticky IP">-</button></td>
							 		</tr>
							 		<tr>
							 			<td align="right">IP:</td>
							 			<td align="left"><input class="textfield dhcpInput stickyIp lanInput" size="20" maxlength="15" value="{$stickyIp->ip}" /></td>
							 			<td></td>
							 		</tr>
							 	</table>
							 	<br />
							</div>
END;
			}
						
			return $out .= <<<END
			
						</fieldset>
					</td>
				</tr>
			</table>
			<button type="button" id="saveLanSettingsBtn" style="margin-top: 5px;">Save</button>
			<br />
			<div id="lanMessages"></div>
END;
		}
		
		// Override
		public function popups(array $parameters)
		{
			
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>
