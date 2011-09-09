<?php
	require_once "Page.class.php";
	require_once "NetUtils.class.php";
	require_once "Service.class.php";
	require_once "WirelessSecurity.class.php";
	require_once "RadioButtonGroup.class.php";
	require_once "TextField.class.php";
	require_once "CheckBox.class.php";
	require_once "ComboBox.class.php";
	require_once "TextFieldRadioButtonGroup.class.php";
	require_once "SaveButton.class.php";
	
	class WirelessPage extends Page
	{
		private $wlanService;
				
		public function __construct()
		{
			$this->wlanService = Service::getInstance("wlan");
			$this->wlanService->load();
			
			$this->addElement(new RadioButtonGroup("wlan", array("Enabled" => "enabled", "Disabled" => "disabled")));
			$this->addElement(new TextField("ssid", $this->wlanService->getSsid()));
			$this->addElement(new CheckBox("hideSsid", $this->wlanService->isSsidHidden()));
			$this->addElement(new ComboBox("mode", array("802.11a" => "a", "802.11b" => "b", "802.11g" => "g")));
			$this->addElement(new ComboBox("channel"));
			$this->addElement(new ComboBox("secMode"));
			$this->addElement(new ComboBox("authMethod"));
			$this->addElement(new TextFieldRadioButtonGroup("wepKeySel"));
			$this->addElement(new TextField("psk", $this->wlanService->getWpaPassphrase()));
			$this->addElement(new TextField("radAuthSvr", $this->wlanService->getAuthServerAddr()));
			$this->addElement(new TextField("radAuthPort", $this->wlanService->getAuthServerPort()));
			$this->addElement(new TextField("radAuthSecret", $this->wlanService->getAuthServerSharedSecret()));
			$this->addElement(new TextField("radAcctSvr", $this->wlanService->getAcctServerAddr()));
			$this->addElement(new TextField("radAcctPort", $this->wlanService->getAcctServerPort()));
			$this->addElement(new TextField("radAcctSecret", $this->wlanService->getAcctServerSharedSecret()));
			$this->addElement(new SaveButton("saveWlanSettingsBtn", "Save"));
			
			$this->getElement("wlan")->setValue($this->wlanService->isEnabled() ? "enabled" : "disabled");
			$this->getElement("ssid")->setAttribute("maxlength", NetUtils::MAX_SSID_LENGTH);
			$this->getElement("mode")->setValue($this->wlanService->getMode());
			
			switch ($this->wlanService->getMode())
			{
				case "a":
					$channels = NetUtils::getWirelessChannels5();
					break;
					
				case "n":
					$channels = array_merge(NetUtils::getWirelessChannels24(), NetUtils::getWirelessChannels5());
					break;
					
				default:
					$channels = NetUtils::getWirelessChannels24();
					break;
			}
			
			$this->getElement("channel")->setOptions(array_combine($channels, $channels));
			$this->getElement("channel")->setValue($this->wlanService->getChannel());
			
			foreach (NetUtils::getWirelessSecurityMethods() as $securityMethod)
			{
				switch ($securityMethod)
				{
					case WirelessSecurity::NONE:
						$this->getElement("secMode")->addOption("None", $securityMethod);
						break;
						
					case WirelessSecurity::WEP:
						$this->getElement("secMode")->addOption("WEP", $securityMethod);
						breka;
						
					case WirelessSecurity::WPA_PSK:
						$this->getElement("secMode")->addOption("WPA Personal", $securityMethod);
						breka;
						
					case WirelessSecurity::WPA_EAP:
						$this->getElement("secMode")->addOption("WPA Enterprise", $securityMethod);
						break;
						
					case WirelessSecurity::WPA2_PSK:
						$this->getElement("secMode")->addOption("WPA2 Personal", $securityMethod);
						break;
						
					case WirelessSecurity::WPA2_EAP:
						$this->getElement("secMode")->addOption("WPA2 Enterprise", $securityMethod);
						break;
				}
			}
			
			$this->getElement("secMode")->setValue($this->wlanService->getSecurityMethod());
			$this->getElement("secMode")->addHandler("change", "setSecurityOptions");
			
			foreach (NetUtils::getWirelessAuthMethods() as $authMethod)
			{
				switch ($authMethod)
				{
					case WirelessSecurity::AUTH_OPEN:
						$this->getElement("authMethod")->addOption("Open System", $authMethod);
						break;
						
					case WirelessSecurity::AUTH_SHARED_KEY:
						$this->getElement("authMethod")->addOption("Shared Key (restricted)", $authMethod);
						break;
				}
			}
			
			$this->getElement("authMethod")->setValue($this->wlanService->getAuthMethod());
			
			$wepKeys = $this->wlanService->getWepKeys();
			$selectedWepKeyIndex = $this->wlanService->getDefaultWepKeyIndex();
			
			for ($i=0 ; $i<4 ; $i++)
			{
				$textField = new TextField("wepKey{$i}", $wepKeys[$i]);
				$textField->addStyle("width", "70%");
				$textField->setAttribute("maxlength", NetUtils::MAX_WEP_KEY_LENGTH);
				$textField->addClass("wepKey");
				$textField->addHandler("change", "showSaveBtn");
				
				$this->getElement("wepKeySel")->addOption($textField);
				
				if ($selectedWepKeyIndex == $i)
					$this->getElement("wepKeySel")->setValue($textField->getValue());
			}
			
			$this->getElement("wepKeySel")->addClass("wepKeyIndex");
			$this->getElement("psk")->setAttribute("maxlength", NetUtils::MAX_PSK_PASSPHRASE_LENGTH);
			$this->getElement("radAuthSvr")->setAttribute("maxlength", "15");
			$this->getElement("radAuthPort")->setAttribute("size", "5");
			$this->getElement("radAuthPort")->setAttribute("maxlength", "5");
			$this->getElement("radAuthSecret")->setAttribute("maxlength", "64");
			$this->getElement("radAcctSvr")->setAttribute("maxlength", "15");
			$this->getElement("radAcctPort")->setAttribute("size", "5");
			$this->getElement("radAcctPort")->setAttribute("maxlength", "5");
			$this->getElement("radAcctSecret")->setAttribute("maxlength", "64");
			$this->getElement("saveWlanSettingsBtn")->addStyle("margin-top", "5px");
			$this->getElement("saveWlanSettingsBtn")->addStyle("display", "none");
			
			$user = User::getUser();
			
			foreach ($this->getElements() as $element)
			{
				if ($user != null && User::getUser()->getGroup() != "admins")
					$element->setAttribute("disabled", "disabled");
					
				if ($element instanceof CheckBox || $element instanceof RadioButtonGroup)
					$element->addHandler("click", "showSaveBtn");
				else
					$element->addHandler("change", "showSaveBtn");
			}
			
			$this->getElement("mode")->addHandler("change", "setChannelOptions");
			$this->getElement("saveWlanSettingsBtn")->addHandler("click", "saveWlanSettings");
		}
		
		// Override
		public function id()
		{
			return "Wireless";
		}
		
		// Override
		public function name()
		{
			return "Wireless Setup";
		}
		
		// Override
		public function head(array $parameters)
		{
		}
		
		// Override
		public function style(array $parameters)
		{
		}
		
		// Override
		public function javascript(array $parameters)
		{
			$channels24 = implode(",", NetUtils::getWirelessChannels24());
			$channels5 = implode(",", NetUtils::getWirelessChannels5());
			$wep = WirelessSecurity::WEP;
			$wpaPsk = WirelessSecurity::WPA_PSK;
			$wpaEap = WirelessSecurity::WPA_EAP;
			$wpa2Psk = WirelessSecurity::WPA2_PSK;
			$wpa2Eap = WirelessSecurity::WPA2_EAP;
			
			return parent::javascript($parameters) . <<<END
			
			$(function()
			{
				setSecurityOptions();
			});
			
			function showSaveBtn()
			{
				if (!$("#saveWlanSettingsBtn").is(":visible"))
				{
					$("#saveWlanSettingsBtn")
						.html("Save")
						.removeAttr("disabled")
						.fadeIn();
				}
			}
			
			function setChannelOptions()
			{
				var channels;
				var mode = $("#mode").val();
				
				switch (mode)
				{
					case "a":
						channels = [$channels5];
						break;
						
					case "n":
						channels = [$channels24,$channels5];
						break;
						
					default:
						channels = [$channels24];
						break;
				}
					
				var html = "";
				var i;
				
				for (i in channels)
					html += "<option value='" + channels[i] + "'>" + channels[i] + "</option>";
					
				$("#channel").html(html);
			}
			
			function setSecurityOptions()
			{				
				$(".secSetting").hide();
				
				switch ($("#secMode").val())
				{
					case "$wep":
						$(".wepSetting").show();
						break;
						
					case "$wpaPsk":
						$(".wpaPskSetting").show();
						break;
						
					case "$wpaEap":
						$(".wpaEapSetting").show();
						break;
						
					case "$wpa2Psk":
						$(".wpaPskSetting").show();
						break;
						
					case "$wpa2Eap":
						$(".wpaEapSetting").show();
						break;
				}
			}
			
			function saveWlanSettings()
			{
				var saveButton = $("#saveWlanSettingsBtn");
				var wepKeys = new Array();
				
				// Testing
				if ($("#secMode").val() == $wep && $("input.wepKeyIndex:checked").siblings(".wepKey").val() == "")
				{
					saveButton
						.html("Save")
						.removeAttr("disabled");
					
					$("#wlanMessages")
						.css("color", "red")
						.html("<ul><li>Selected WEP key must not be blank</li></ul>")
						.show();
						
					return;
				}
				// End Testing
				
				$(".wepKey").each(function()
				{
					wepKeys.push($(this).val());
				});
				
				var params =	{
									handler: "SaveWlanSettings",
									parameters:
									{
										isEnabled: $("#wlanenabled").is(":checked"),
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
								
				$.post("ajaxRequest.php", params, function(response)
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
			}
END;
		}
		
		// Override
		public function content(array $parameters)
		{			
			return <<<END
			
			<table class="ip-table" style="width: 400px;">
				<tr>
					<th colspan="2">Basic Settings</th>
				</tr>
				<tr>
					<td align="right">Wireless:</td>
					<td align="left">
						{$this->getElement("wlan")}
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td align="right">SSID:</td>
					<td align="left">{$this->getElement("ssid")}</td>
				</tr>
				<tr>
					<td align="right">Hide SSID:</td>
					<td align="left">{$this->getElement("hideSsid")}</td>
				</tr>
				<tr>
					<td align="right">Wireless Mode:</td>
					<td align="left">{$this->getElement("mode")}</td>
				</tr>
				<tr>
					<td align="right">Channel:</td>
					<td align="left">{$this->getElement("channel")}</td>
				</tr>
			</table>
			<br>
			<table class="ip-table" style="width: 400px;">
				<tr>
					<th colspan="2">Security Settings</th>
				</tr>
				<tr>
					<td align="right" style="width: 50%;">Security Method:</td>
					<td align="left" style="width: 50%;">{$this->getElement("secMode")}</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr class="wpaPskSetting secSetting">
					<td align="right">Passphrase:</td>
					<td align="left">{$this->getElement("psk")}</td>
				</tr>
				<tr class="wepSetting secSetting">
					<td align="right">Authentication Method:</td>
					<td align="left">{$this->getElement("authMethod")}</td>
				</tr>
				<tr "class="wepSetting secSetting">
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr class="wepSetting secSetting">
					<td colspan="2"align="left">WEP Keys:</td>
				</tr>
				<tr class="wepSetting secSetting">
					<td colspan="2">
						{$this->getElement("wepKeySel")}
					</td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td align="right">Authentication Server:</td>
					<td align="left">{$this->getElement("radAuthSvr")}</td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td align="right">Port:</td>
					<td align="left">{$this->getElement("radAuthPort")}</td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td align="right">Shared Secret:</td>
					<td align="left">{$this->getElement("radAuthSecret")}</td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td align="right">Accounting Server:</td>
					<td align="left">{$this->getElement("radAcctSvr")}</td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td align="right">Port:</td>
					<td align="left">{$this->getElement("radAcctPort")}</td>
				</tr>
				<tr class="wpaEapSetting secSetting">
					<td align="right">Shared Secret:</td>
					<td align="left">{$this->getElement("radAcctSecret")}</td>
				</tr>
			</table>
			{$this->getElement("saveWlanSettingsBtn")}
			<br>
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