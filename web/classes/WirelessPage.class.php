<?php
	require_once "Page.class.php";
	require_once "NetUtils.class.php";
	require_once "Service.class.php";
	require_once "WirelessSecurity.class.php";
	
	class WirelessPage implements Page
	{
		private $wlanService;
		
		public function __construct()
		{
			$this->wlanService = Service::getInstance("wlan");
			$this->wlanService->load();
		}
		
		// Override
		public function name()
		{
			return "Wireless Setup";
		}
		
		// Override
		public function head()
		{
		}
		
		// Override
		public function javascript()
		{
			$isWlanEnabled = $this->wlanService->getAttribute("is_enabled") == "Y" ? "true" : "false";
			$isSsidHidden = $this->wlanService->isSsidHidden() ? "true" : "false";
			$wlanMode = $this->wlanService->getMode();
			$channel = $this->wlanService->getChannel();
			$securityMethod = $this->wlanService->getSecurityMethod();
			$channels24 = implode(",", NetUtils::getWirelessChannels24());
			$channels5 = implode(",", NetUtils::getWirelessChannels5());
			$authMethod = $this->wlanService->getAuthMethod();
			$wepKeyIndex = $this->wlanService->getDefaultWepKeyIndex();
			
			$wep = WirelessSecurity::WEP;
			$wpaPsk = WirelessSecurity::WPA_PSK;
			$wpaEap = WirelessSecurity::WPA_EAP;
			$wpa2Psk = WirelessSecurity::WPA2_PSK;
			$wpa2Eap = WirelessSecurity::WPA2_EAP;
			
			return <<<END
			
			$(document).ready(function()
			{
				// initialize
				$("#saveWlanSettingsBtn").hide();
				$("#wlanMessages").hide();
				
				if ($isWlanEnabled)
					$("#wlanEnabled").click();
				else
					$("#wlanDisabled").click();
					
				if ($isSsidHidden)
					$("#hideSsid").click();
					
				$("#mode").val("$wlanMode");
				setChannelOptions($("#mode").val());
				$("#channel").val("$channel");
				$("#secMode").val($securityMethod);
				setSecurityOptions(parseInt($("#secMode").val()));
				$("#authMethod").val($authMethod);
				$(".wepKeyIndex").get($wepKeyIndex).click();
				
				// event handlers
				$(".wlanInput").change(function()
				{
					if (!$("#saveWlanSettingsBtn").is(":visible"))
					{
						$("#saveWlanSettingsBtn")
							.html("Save")
							.removeAttr("disabled")
							.fadeIn();
					}
				});
				
				$("#mode").change(function()
				{
					setChannelOptions($(this).val());
				});
				
				$("#secMode").change(function()
				{
					setSecurityOptions(parseInt($(this).val()));
				});
				
				$("#saveWlanSettingsBtn").click(function()
				{
					var saveButton = $(this);
					
					saveButton
						.html("Please wait...")
						.attr("disabled", "disabled");
						
					var wepKeys = new Array();
					
					$(".wepKey").each(function()
					{
						wepKeys.push($(this).val());
					});
											
					var params =	{
										handler: "SaveWlanSettings",
										parameters:
										{
											isEnabled: $("#wlanEnabled").is(":checked"),
											ssid: $("#ssid").val(),
											hideSsid: $("#hideSsid").is(":checked"),
											mode: $("#mode").val(),
											channel: $("#channel").val(),
											securityMode: $("#secMode").val(),
											wepKeys: wepKeys,
											wepKeyIndex: $(".wepKeyIndex").index($(".wepKeyIndex:checked")),
											wepAuthMode: $("#authMethod").val(),
											wpaPsk: $("#psk").val(),
											wpaAuthServerAddr: $("#radAuthSvr").val(),
											wpaAuthServerPort: $("#radAuthPort").val(),
											wpaAuthServerSec: $("#radAuthSecret").val(),
											wpaAcctServerAddr: $("#radAcctSvr").val(),
											wpaAcctServerPort: $("#radAcctPort").val(),
											wpaAcctServerSec: $("#radAcctSecret").val()
										}
									};
									
					$.post("ajax/ajaxRequest.php", params, function(response)
					{
						if (response.errors.length > 0)
						{
							$("#wlanMessages")
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
							$("#wlanMessages")
								.css("color", "green")
								.html("Wireless settings saved successfully")
								.show()
								.fadeOut(3000);
						}
					});
				});
			});
			
			function setChannelOptions(mode)
			{
				var channels;
				
				if (mode == "a")
					channels = [$channels5];
				else if (mode == "n")
					channels = [$channels24,$channels5];
				else
					channels = [$channels24];
					
				var html = "";
				var i;
				
				for (i in channels)
					html += "<option value='" + channels[i] + "'>" + channels[i] + "</option>";
					
				$("#channel").html(html);
			}
			
			function setSecurityOptions(securityMode)
			{
				$(".secSetting").hide();
				
				switch (securityMode)
				{
					case $wep:
						$(".wepSetting").show();
						break;
					case $wpaPsk:
						$(".wpaPskSetting").show();
						break;
					case $wpaEap:
						$(".wpaEapSetting").show();
						break;
					case $wpa2Psk:
						$(".wpaPskSetting").show();
						break;
					case $wpa2Eap:
						$(".wpaEapSetting").show();
						break;
				}
			}
END;
		}
		
		// Override
		public function content(array $parameters)
		{
			$ssidLength = NetUtils::MAX_SSID_LENGTH;
			$pskLength = NetUtils::MAX_PSK_PASSPHRASE_LENGTH;
			$wepLength = NetUtils::MAX_WEP_KEY_LENGTH;
			$authOpen = WirelessSecurity::AUTH_OPEN;
			$authSharedKey = WirelessSecurity::AUTH_SHARED_KEY;
			$wepKeys = $this->wlanService->getWepKeys();
			
			$out = <<<END
			
			<table class="ip-table" style="width: 400px;">
				<tr>
					<th colspan="2">Basic Settings</th>
				</tr>
				<tr>
					<td align="right">Wireless:</td>
					<td align="left">
						<input name="wlan" id="wlanEnabled" class="wlanInput" type="radio" />&nbsp;Enabled<br />
						<input name="wlan" id="wlanDisabled" class="wlanInput" type="radio" />&nbsp;Disabled
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td align="right">SSID:</td>
					<td align="left"><input id="ssid" class="wlanInput textfield" size="20" maxlength="$ssidLength" value="{$this->wlanService->getSsid()}" />
				</tr>
				<tr>
					<td align="right">Hide SSID:</td>
					<td align="left"><input id="hideSsid" class="wlanInput" type="checkbox" /></td>
				</tr>
				<tr>
					<td align="right">Wireless Mode:</td>
					<td align="left">
						<select id="mode" class="wlanInput">
END;
			foreach (NetUtils::getWirelessModes() as $mode)
			{	
				$out .= <<<END
							
							<option value="$mode">802.11$mode</option>
END;
			}

			$out .= <<<END
			
						</select>
					</td>
				</tr>
				<tr>
					<td align="right">Channel:</td>
					<td align="left">
						<select id="channel" class="wlanInput">
						</select>
					</td>
				</tr>
			</table>
			<br />
			<table class="ip-table" style="width: 400px;">
				<tr>
					<th colspan="2">Security Settings</th>
				</tr>
				<tr>
					<td align="right" style="width: 50%;">Security Method:</td>
					<td align="left" style="width: 50%;">
						<select id="secMode" class="wlanInput">
END;
			foreach (NetUtils::getWirelessSecurityMethods() as $securityMethod)
			{
				$methodValue = "";
				eval("\$methodValue=WirelessSecurity::$securityMethod;");
								
				$matches = array		(
											"/NONE/",
											"/_/",
											"/PSK/",
											"/EAP/",
										);
									
				$replacements = array	(
											"None",
											" ",
											"Personal",
											"Enterprise"
										);
				
				$displaySecurityMethod = preg_replace($matches, $replacements, $securityMethod);
				
				$out .= <<<END
				
							<option value="$methodValue">$displaySecurityMethod</option>
END;
			}

			return $out .= <<<END
			
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr class="wpaPskSetting secSetting">
					<td align="right">Passphrase:</td>
					<td align="left"><input id="psk" class="wlanInput textfield" size="20" maxlength="$pskLength" value="{$this->wlanService->getWpaPassphrase()}" /></td>
				</tr>
				<tr class="wepSetting secSetting">
					<td align="right">Authentication Method:</td>
					<td align="left">
						<select id="authMethod">
							<option value="$authOpen">Open System</option>
							<option value="$authSharedKey">Shared Key (restricted)</option>
						</select>
					</td>
				</tr>
				<tr "class="wepSetting secSetting">
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr class="wepSetting secSetting">
					<td colspan="2"align="left">WEP Keys:</td>
				</tr>
				<tr class="wepSetting secSetting">
					<td colspan="2">
						<input type="radio" name="wepKeySel" class="wepKeyIndex wlanInput" />&nbsp;<input class="wlanInput textfield wepKey" maxlength="$wepLength" style="width: 70%" value="$wepKeys[0]" /><br />
						<input type="radio" name="wepKeySel" class="wepKeyIndex wlanInput" />&nbsp;<input class="wlanInput textfield wepKey" maxlength="$wepLength" style="width: 70%" value="$wepKeys[1]" /><br />
						<input type="radio" name="wepKeySel" class="wepKeyIndex wlanInput" />&nbsp;<input class="wlanInput textfield wepKey" maxlength="$wepLength" style="width: 70%" value="$wepKeys[2]" /><br />
						<input type="radio" name="wepKeySel" class="wepKeyIndex wlanInput" />&nbsp;<input class="wlanInput textfield wepKey" maxlength="$wepLength" style="width: 70%" value="$wepKeys[3]" />
					</td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td align="right">Authentication Server:</td>
					<td align="left"><input id="radAuthSvr" class="wlanInput textfield" size="20" maxlength="15" value="{$this->wlanService->getAuthServerAddr()}" /></td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td align="right">Port:</td>
					<td align="left"><input id="radAuthPort" class="wlanInput textfield" size="5" maxlength="5" value="{$this->wlanService->getAuthServerPort()}" /></td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td align="right">Shared Secret:</td>
					<td align="left"><input id="radAuthSecret" class="wlanInput textfield" size="20" maxlength="64" value="{$this->wlanService->getAuthServerSharedSecret()}" /></td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td align="right">Accounting Server:</td>
					<td align="left"><input id="radAcctSvr" class="wlanInput textfield" size="20" maxlength="15" value="{$this->wlanService->getAcctServerAddr()}" /></td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td align="right">Port:</td>
					<td align="left"><input id="radAcctPort" class="wlanInput textfield" size="5" maxlength="5" value="{$this->wlanService->getAcctServerPort()}" /></td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td align="right">Shared Secret:</td>
					<td align="left"><input id="radAcctSecret" class="wlanInput textfield" size="20" maxlength="64" value="{$this->wlanService->getAcctServerSharedSecret()}" /></td>
				</tr>
			</table>
			<button type="button" id="saveWlanSettingsBtn" style="margin-top: 5px;">Save</button>
			<br />
			<div id="wlanMessages"></div>
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