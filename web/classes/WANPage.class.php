<?php
	require_once "Page.class.php";
	require_once "NetworkInterface.class.php";
	require_once "DNSSettings.class.php";
	
	class WANPage extends Page
	{
		private $wanInt;
		
		public function __construct()
		{
			$this->wanInt = NetworkInterface::getInstance("WAN");
		}
		
		// Override
		public function id()
		{
			return "WAN";
		}
		
		// Override
		public function name()
		{
			return "WAN Setup";
		}
		
		// Override
		public function head(array $parameters)
		{
		}
		
		// Override
		public function style(array $parameters)
		{
		}
		
		public function javascript(array $parameters)
		{
			$isDhcpEnabled = $this->wanInt->usesDhcp() ? "true" : "false";
			
			return <<<END
			
			var showSaveButton = function()
			{
				if (!$("#saveWanSettingsBtn").is(":visible"))
				{
					$("#saveWanSettingsBtn")
						.html("Save")
						.removeAttr("disabled")
						.fadeIn();
				}
			}
			
			$(document).ready(function()
			{
				// event handlers
				$(".wanInput").change(showSaveButton);
			
				$("#ipTypeDhcp").click(function()
				{
					$(".staticIpSetting").each(function()
					{
						$(this).attr("disabled", "disabled");
					});
			
					$("#dnsTypeDhcp").removeAttr("disabled");
				});
			
				$("#ipTypeStatic").click(function()
				{
					$(".staticIpSetting").removeAttr("disabled");
					$("#dnsTypeDhcp").attr("disabled", "disabled");
					$("#dnsTypeStatic").click();
				});
			
				$("#dnsTypeDhcp").click(function()
				{
					$(".nameserverInput").each(function()
					{
						$(this).attr("disabled", "disabled");
					});
			
					$("#addNsBtn").attr("disabled", "disabled");
					$("#nameservers > tbody > tr").removeClass("removeable");
				});
			
				$("#dnsTypeStatic").click(function()
				{
					$(".nameserverInput").removeAttr("disabled");
					$("#addNsBtn").removeAttr("disabled");
					$("#nameservers > tbody > tr").addClass("removeable");
					makeRemoveable($(".removeable"));
				});
			
				$("#addNameserverBtn").click(function()
				{
					var html =	"<tr class='removeable'>" +
									"<td><input class='textfield wanInput nameserverInput nameserver' /></td>" +
									"<td width='30px'><button type='button' class='removeBtn nameserverInput' title='Remove nameserver'>-</button></td>" +
								"</tr>";
								
					addRemoveableField(html, $("#nameservers > tbody"));
				});
			
				$("#saveWanSettingsBtn").click(function()
				{
					var saveButton = $(this);
					
					saveButton
						.html("Saving...&nbsp;<img src='images/loading.gif' />")
						.attr("disabled", "disabled");
						
					var nameservers = new Array();
					
					$(".nameserver").each(function()
					{
						var nameserver = $(this).val().replace(/^\s+|\s+$/g, "");
						
						if (nameserver.length > 0)
							nameservers.push(nameserver);
					});
					
					var params =	{
										handler: "SaveWanSettings",
										parameters:
										{
											ipType: $("#ipTypeDhcp").is(":checked") ? "dhcp" : "static",
											dnsType: $("#dnsTypeDhcp").is(":checked") ? "dhcp" : "static",
											ipAddress: $("#ipAddress").val(),
											gateway: $("#gateway").val(),
											nameservers: nameservers,
											mtu: $("#mtu").val()
										}
									};
			
					$.post("ajax/ajaxRequest.php", params, function(response)
					{
						if (response.errors.length > 0)
						{
							$("#wanMessages")
								.css("color", "red")
								.html("<ul><li>" + response.errors.join("</li><li>") + "</li></ul>")
								.show();
								
							saveButton
								.html("Save")
								.removeAttr("disabled");
						}
						else
						{
							$("#saveWanSettingsBtn").hide();
							$("#wanMessages")
								.css("color", "green")
								.html("WAN settings saved successfully")
								.show()
								.fadeOut(3000);
						}
					});
				});
			
				// initialize
				$(".removeBtn").hide();
				$("#saveWanSettingsBtn").hide();
				$("#wanMessages").hide();
				
				if ($isDhcpEnabled)
				{
					$("#ipTypeDhcp").click();
					$("#dnsTypeDhcp").click();
				}
				else
				{
					$("#ipTypeStatic").click();
					$("#dnsTypeStatic").click();
				}
				
				makeRemoveable($(".removeable"));
			});
			
			function makeRemoveable(element)
			{
				element
					.mouseover(function()
					{
						var rmButton = $(this).find("button.removeBtn");
						
						if (!rmButton.is(":disabled"))
							rmButton.show();
					})
					
					.mouseout(function()
					{
						var rmButton = $(this).find("button.removeBtn");
						
						if (!rmButton.is(":disabled"))
							rmButton.hide();
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
								
				where.find("input.wanInput").change(showSaveButton);
			}
END;
		}
		
		// Override
		public function content(array $parameters)
		{
			$dns = new DNSSettings();
			$out = <<<END
			
			<table class="ip-table" style="width: 400px;">
				<tr><th colspan="3">Interface</th></tr>
				<tr>
					<td align="right"><input class="wanInput" type="radio" id="ipTypeDhcp" name="ipType" value="dynamic" /></td>
					<td align="left" colspan="2">Obtain IP address automatically</td>
				</tr>
				<tr>
					<td align="right"><input class="wanInput" type="radio" id="ipTypeStatic" name="ipType" value="static" /></td>
					<td align="left" colspan="2">Specify IP address:</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td align="right">IP Address:</td>
					<td align="left"><input class="wanInput textfield staticIpSetting" size="20" maxlength="18" id="ipAddress" name="ipAddress" value="{$this->wanInt->getAddress()}" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td align="right">Gateway:</td>
					<td align="left"><input class="wanInput textfield staticIpSetting" size="20" maxlength="15" id="gateway" name="gateway" value="{$this->wanInt->getGateway()}" /></td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td align="right">MTU:</td>
					<td align="left"><input class="wanInput textfield" size="4" maxlength="4" id="mtu" name="mtu" value="{$this->wanInt->getMtu()}" /></td>
				</tr>
			</table>
			<br />
			<table id="dns-table" class="ip-table" style="width: 400px;">
				<tr><th colspan="3">DNS</th></tr>
				<tr>
					<td align="right"><input class="wanInput" type="radio" id="dnsTypeDhcp" name="dnsType" value="dynamic" /></td>
					<td align="left" colspan="2">Obtain DNS information automatically</td>
				</tr>
				<tr>
					<td align="right"><input class="wanInput" type="radio" id="dnsTypeStatic" name="dnsType" value="static" /></td>
					<td align="left" colspan="2">Specify DNS information</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td align="center" colspan="3">
						<fieldset>
							<legend>Nameservers</legend>
							<button type="button" id="addNameserverBtn" class="nameserverInput" title="Add nameserver">+</button>
							<br /><br />
							<table id="nameservers" align="center" style="border-collapse: collapse;">
								<tbody>
END;
			
			foreach ($dns->getNameservers() as $key => $nameserver)
			{
				$out .= <<<END
				
									<tr class="removeable">
										<td><input class="textfield wanInput nameserverInput nameserver" value="$nameserver" /></td>
										<td width="30px"><button type="button" class="removeBtn nameserverInput" title="Remove nameserver">-</button></td>
									</tr>
END;
			}
				
			return $out .= <<<END
			
								</tbody>
							</table>
						</fieldset>
					</td>
				</tr>
			</table>
			<br />
			<button type="button" id="saveWanSettingsBtn" style="margin-top: 5px;">Save</button>
			<br />
			<div id="wanMessages"></div>
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