<?php
	require_once "Page.class.php";
	require_once "User.class.php";
	require_once "RouterSettings.class.php";
	require_once "DNSSettings.class.php";
	require_once "SystemProfile.class.php";
	require_once "NetworkInterface.class.php";
	require_once "InputElement.class.php";
	require_once "ComboBox.class.php";
	require_once "RadioButtonGroup.class.php";
	require_once "TextField.class.php";
	require_once "Button.class.php";
	require_once "SaveButton.class.php";
	require_once "RemoveableTextField.class.php";
	require_once "SettingNotFoundException.class.php";
	
	class SetupPage extends Page
	{
		private $dnsServerInputs = array();
		private $searchDomainInputs = array();
		private $isInitialized = false;
		
		public function __construct()
		{
			$this->isInitialized = RouterSettings::getSettingValue("IS_INITIALIZED");
			
			$profiles = SystemProfile::getAvailableProfiles();
			$interfaces = NetworkInterface::getAvailableInterfaces();
			$wlanInterfaces = NetworkInterface::getAvailableWirelessInterfaces();
			
			$this->addElement(new TextField("elwoodWebRoot", RouterSettings::getSettingValue("ELWOOD_WEBROOT")));
			$this->addElement(new TextField("wanAddress"));
			$this->addElement(new TextField("wanGateway"));
			$this->addElement(new TextField("lanAddress"));
			$this->addElement(new ComboBox("sysProfile", array_combine($profiles, $profiles)));
			$this->addElement(new ComboBox("wanInterface", array_combine($interfaces, $interfaces)));
			$this->addElement(new ComboBox("lanInterface", array_combine($interfaces, $interfaces)));
			$this->addElement(new Button("addNameserverBtn", "+"));
			$this->addElement(new Button("addSearchDomainBtn", "+"));
			$this->addElement(new SaveButton("saveBtn", "Save"));
			$this->addElement(new RadioButtonGroup("dns", array("Obtain DNS information automatically" => "auto", "Specify DNS information" => "static")));
			$this->addElement(new RadioButtonGroup("wanIpType", array("Obtain IP address automatically" => "auto", "Specify IP address" => "static")));
			
			$this->getElement("wanIpType")->addHandler("click", "toggleWanDhcpOptions");
			$this->getElement("dns")->addHandler("click", "toggleDnsOptions");
			$this->getElement("addNameserverBtn")->addHandler("click", "addNameserver");
			$this->getElement("addSearchDomainBtn")->addHandler("click", "addSearchDomain");
			$this->getElement("saveBtn")->addHandler("click", "saveSettings")->addStyle("margin-top", "10px")->addStyle("display", "none");
			$this->getElement("wanIpType")->setValue("auto");
			
			if (!empty($wlanInterfaces))
			{
				$this->addElement(new ComboBox("apInterface", array_combine($wlanInterfaces, $wlanInterfaces)));
				
				if ($this->isInitialized)
					$this->setInterface($this->getElement("apInterface"), "AP");
			}
			
			$nameservers = DNSSettings::getNameservers();
			$searchDomains = DNSSettings::getSearchDomains();
			
			try
			{
				if (RouterSettings::getSettingValue("DNS_MODE") == "auto")
					$this->getElement("dns")->setValue("auto");
				else
					$this->getElement("dns")->setValue("static");
			}
			catch (SettingNotFoundException $ex)
			{
				$this->getElement("dns")->setValue("auto");
			}
			
			$i = 0;
			
			foreach ($nameservers as $nameserver)
			{
				$dnsServerInput = new RemoveableTextField("nameserver" . $i++, $nameserver);
				$dnsServerInput->addClass("nameserverInput")->setAttribute("size", "15")->setAttribute("maxlength", "15")->getRmButton()->addHandler("click", "showSaveButton");
				$this->dnsServerInputs[] = $dnsServerInput;
				$this->addElement($dnsServerInput);
			}
			
			$i = 0;
			
			foreach ($searchDomains as $searchDomain)
			{
				$searchDomainInput = new RemoveableTextField("searchDomain" . $i++, $searchDomain);
				$searchDomainInput->addClass("searchDomainInput")->getRmButton()->addHandler("click", "showSaveButton");
				$this->searchDomainInputs[] = $searchDomainInput;
				$this->addElement($searchDomainInput);
			}
							
			if ($this->isInitialized)
			{
				$wanInt = NetworkInterface::getInstance("WAN");
				$lanInt = NetworkInterface::getInstance("LAN");
				
				$this->setInterface($this->getElement("wanInterface"), "WAN");
				$this->setInterface($this->getElement("lanInterface"), "LAN");
				$this->getElement("sysProfile")->setValue(RouterSettings::getSettingValue("SYSTEM_PROFILE"));
				
				if ($wanInt->usesDhcp())
				{
					$this->getElement("wanAddress")->setAttribute("disabled", "disabled");
					$this->getElement("wanGateway")->setAttribute("disabled", "disabled");
				}
				else
				{
					$this->getElement("wanIpType")->setValue("static");
					$this->getElement("dns")->setValue("static");
				}
					
				$this->getElement("wanAddress")->setValue($wanInt->getAddress());
				$this->getElement("wanGateway")->setValue($wanInt->getGateway());
				$this->getElement("lanAddress")->setValue($lanInt->getAddress());
			}
			
			if ($this->getElement("dns")->getValue() == "auto")
			{
				$this->getElement("addNameserverBtn")->setAttribute("disabled", "disabled");
				$this->getElement("addSearchDomainBtn")->setAttribute("disabled", "disabled");
				
				foreach ($this->dnsServerInputs as $dnsServerInput)
					$dnsServerInput->setAttribute("disabled", "disabled");
				
				foreach ($this->searchDomainInputs as $searchDomainInput)
					$searchDomainInput->setAttribute("disabled", "disabled");
			}
			
			$user = User::getUser();
						
			foreach ($this->getElements() as $element)
			{
				if ($user != null && !$user->isAdminUser())
					$element->setAttribute("disabled", "disabled");
					
				$element->addHandler("change", "showSaveButton");
			}
		}
		
		// Override
		public function id()
		{
			return "Setup";
		}
		
		// Override
		public function name()
		{
			return "Setup";
		}
		
		// Override
		public function head(array $parameters)
		{
		}
		
		// Override
		public function style(array $parameters)
		{
			return <<<END
			
			fieldset
			{
				margin: 10px;
				padding: 10px;
			}
END;
		}
		
		// Override
		public function javascript(array $parameters)
		{
			return parent::javascript($parameters) . <<<END
			
			var newNameservers = 0;
			var newSearchDomains = 0;
			
			function toggleDnsOptions()
			{
				var mode = $(this).val();
				
				if (mode == "static")
				{
					$("#addNameserverBtn").removeAttr("disabled");
					$("#addSearchDomainBtn").removeAttr("disabled");
					$(".nameserverInput").removeAttr("disabled");
					$(".searchDomainInput").removeAttr("disabled");
				}
				else if (mode == "auto")
				{
					$("#addNameserverBtn").attr("disabled", "disabled");
					$("#addSearchDomainBtn").attr("disabled", "disabled");
					$(".nameserverInput").attr("disabled", "disabled");
					$(".searchDomainInput").attr("disabled", "disabled");
				}
			}
			
			function toggleWanDhcpOptions()
			{
				var mode = $(this).val();
				
				if (mode == "static")
				{
					$('#wanAddress').removeAttr('disabled');
					$('#wanGateway').removeAttr('disabled');
					$('#dnsstatic').click();
					$('#dnsauto').attr('disabled', 'disabled');
					$('#dnsstatic').attr('disabled', 'disabled');
				}
				else if (mode == "auto")
				{
					$('#wanAddress').attr('disabled', 'disabled');
					$('#wanGateway').attr('disabled', 'disabled');
					$('#dnsauto').removeAttr('disabled');
					$('#dnsstatic').removeAttr('disabled');
				}
			}
			
			function addNameserver()
			{	
				var params =	{
									handler: "GetNewElement",
									parameters:
									{
										type: "RemoveableTextField",
										name: "newNameserver" + ++newNameservers,
										attributes: JSON.stringify({size: 15, maxlength: 15}),
										addClasses: "nameserverInput"
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
					}
					else
					{
						$("#nameserverInputs").append(response.responseText.html);
						eval(response.responseText.js);
						showSaveButton();
					}
				});
			}
			
			function addSearchDomain()
			{
				var params =	{
									handler: "GetNewElement",
									parameters:
									{
										type: "RemoveableTextField",
										name: "newSearchDomain" + ++newSearchDomains,
										attributes: JSON.stringify({size: 15, maxlength: 15}),
										addClasses: "searchDomainInput"
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
					}
					else
					{
						$("#searchDomainInputs").append(response.responseText.html);
						eval(response.responseText.js);
						showSaveButton();
					}
				});
			}
			
			function saveSettings()
			{
				var saveButton = $("#saveBtn");
				var nameservers = new Array();
				var searchDomains = new Array();
				
				$(".nameserverInput").each(function()
				{
					nameservers.push($(this).val());
				});
				
				$(".searchDomainInput").each(function()
				{
					searchDomains.push($(this).val());
				});
				
				var params =	{
									handler: "SaveSetupSettings",
									parameters:
									{
										sysProfile: $("#sysProfile").val(),
										elwoodWebRoot: $("#elwoodWebRoot").val(),
										wanInterface: $("#wanInterface").val(),
										wanIpType: $("#wanIpTypeauto").is(":checked") ? "auto" : "static",
										wanAddress: $("#wanAddress").val(),
										wanGateway: $("#wanGateway").val(),
										lanInterface: $("#lanInterface").val(),
										lanAddress: $("#lanAddress").val(),
										apInterface: ($("#apInterface").size() != 0 ? $("#apInterface").val() : ""),
										dnsMode: ($("#dnsauto").is(":checked") ? "auto" : "static"),
										nameservers: nameservers,
										searchDomains: searchDomains
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
						saveButton.hide();
						
						$("#messages")
							.css("color", "green")
							.html(response.responseText)
							.show()
							.fadeOut(3000);
					}
				});
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
END;
		}
		
		// Override
		public function content(array $parameters)
		{
			$out = <<<END
			
			<table class="ip-table" style="width: 450px;">
				<tr>
					<th colspan="3">General Settings</th>
				</tr>
				<tr>
					<td class="tabInputLabel">System Profile:</td>
					<td class="tabInputValue">{$this->getElement("sysProfile")}</td>
				</tr>
				<tr>
					<td class="tabInputLabel">Elwood Web Root Dir:</td>
					<td class="tabInputValue">{$this->getElement("elwoodWebRoot")}
				</tr>
			</table>
			<br>
			<table class="ip-table" style="width: 450px;">
				<tr>
					<th>Interface Settings</th>
				</tr>
				<tr>
					<td>
						<fieldset>
							<legend>WAN Interface</legend>
							<table>
								<tr>
									<td colspan="2" align="left">Interface:&nbsp;{$this->getElement("wanInterface")}</td>
								</tr>
								<tr>
									<td colspan="2" align="left">{$this->getElement("wanIpType")}</td>
								</tr>
								<tr>
									<td style="padding-left: 50px;" align="right">IP Address:</td>
									<td>{$this->getElement("wanAddress")}</td>
								</tr>
								<tr>
									<td style="padding-left: 50px;" align="right"">Gateway:</td>
									<td>{$this->getElement("wanGateway")}</td>
								</tr>
							</table>
						</fieldset>
					</td>
				</tr>
				<tr>
					<td>
						<fieldset>
							<legend>LAN Interface</legend>
							<table>
								<tr>
									<td class="tabInputLabel">Interface:</td>
									<td class="tabInputValue">{$this->getElement("lanInterface")}</td>
								</tr>
								<tr>
									<td class="tabInputLabel">IP Address:</td>
									<td class="tabInputValue">{$this->getElement("lanAddress")}</td>
								</tr>
							</table>
						</fieldset>
					</td>
				</tr>
END;

			if ($this->getElement("apInterface") != null)
			{
				$out .= <<<END
				<tr>
					<td>
						<fieldset>
						<legend>AP Interface</legend>
						AP Interface:&nbsp;{$this->getElement("apInterface")}
					</td>
				</tr>
END;
			}
			
			$out .= <<<END
			</table>
			<br>
			<table class="ip-table" style="width: 450px;">
				<tr>
					<th>DNS Settings</th>
				</tr>
				<tr>
					<td class="tabInputValue">{$this->getElement("dns")}</td>
				</tr>
				<tr>
					<td>
						<fieldset>
							<legend>Nameservers</legend>
							{$this->getElement("addNameserverBtn")}
							<div id="nameserverInputs" style="padding-top: 10px">
END;
			foreach ($this->dnsServerInputs as $nameserverInput)
			{
				$out .= <<<END
				{$nameserverInput}
END;
			}
			
			$out .= <<<END
							</div>
						</fieldset>
					</td>
				</tr>
				<tr>
					<td>
						<fieldset>
							<legend>Search Domains</legend>
							{$this->getElement("addSearchDomainBtn")}
							<div id="searchDomainInputs" style="padding-top: 10px">
END;
			foreach ($this->searchDomainInputs as $searchDomainInput)
			{
				$out .= <<<END
				{$searchDomainInput}
END;
			}
			
			return $out .= <<<END
						</fieldset>
					</td>
				</tr>
			</table>
			{$this->getElement("saveBtn")}
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
		
		private function setInterface(InputElement $element, $interfaceName)
		{
			$element->setValue(NetworkInterface::getInstance($interfaceName)->getPhysicalInterface());
		}
	}
?>