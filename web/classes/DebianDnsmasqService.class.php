<?php
	require_once "Service.class.php";
	require_once "DHCPService.class.php";
	require_once "Console.class.php";
	require_once "FileUtils.class.php";
	require_once "NetworkInterface.class.php";
	require_once "NetUtils.class.php";
	require_once "FirewallRule.class.php";
	
	class DebianDnsmasqService extends Service implements DHCPService
	{
		private $ipRanges = array();
		private $stickyIps = array();
		private $domain;
		private $nameservers = array();
		
		public function __construct()
		{
			parent::__construct();
		}
		
		// Override
		public function stop()
		{
			if ($this->isRunning())
			{
				Console::execute("sudo /etc/init.d/dnsmasq stop");
				sleep(3);
			}
		}
		
		// Override
		public function start()
		{
			if ($this->isRunning())
				return;
			
			try
			{
				Console::execute("sudo /etc/init.d/dnsmasq start");
				sleep(3);
			}
			catch (Exception $ex)
			{
				throw new Exception("The dnsmasq service failed to start");
			}
		}
		
		// Override
		public function restart()
		{
			try
			{
				Console::execute("sudo /etc/init.d/dnsmasq restart");
				sleep(3);
			}
			catch (Exception $ex)
			{
				throw new Exception("The dnsmasq service failed to restart");
			}
		}
		
		// Override
		public function save()
		{
			parent::save();
			
			$lanInt = NetworkInterface::getInstance("LAN");
			
			$out =	"interface=" . $lanInt->getPhysicalInterface() . "\n" .
					"no-hosts\n" .
					"no-resolv\n" .
					"domain=" . $this->domain . "\n";
			
			foreach ($this->ipRanges as $ipRange)
				$out .= "dhcp-range=" . implode(",", (array)$ipRange) . "\n";
				
			$out .=	"dhcp-option=3," . $lanInt->getIp() . "\n";
		
			if (!empty($this->nameservers))
				$out .= "dhcp-option=6," . implode(",", $this->nameservers) . "\n";
			
			foreach ($this->stickyIps as $stickyIp)
			{
				$out .=	"\ndhcp-host=" . $stickyIp->mac . "," . $stickyIp->name . "," . $stickyIp->ip;
			}
			
			$out .= "\ndhcp-leasefile=" . $this->service->leasefile;
			
			FileUtils::writeToFile($this->service->config, $out);
		}
		
		// Override
		public function load()
		{
			parent::load();
			
			$config = FileUtils::readFileAsArray($this->service->config);
			
			foreach ($config as $line)
			{
				list($optionName, $parameters) = explode("=", $line);
				
				switch ($optionName)
				{
					case "domain":
						$this->domain = $parameters;
						break;
					case "dhcp-range":
						list($startIp, $endIp) = explode(",", $parameters);
						$this->ipRanges[] = (object) array	(
																"startIp" => $startIp,
																"endIp" => $endIp
															);
						break;
					case "dhcp-option":
						list($dhcpOption, $value) = explode(",", $parameters, 2);
						
						if ($dhcpOption == "6")
							$this->nameservers = explode(",", $value);
						break;
					case "dhcp-host":
						list($mac, $name, $ip) = explode(",", $parameters);
						$this->stickyIps[] = (object) array	(
																"mac" => $mac,
																"name" => $name,
																"ip" => $ip
															);
						break;
				}
			}
		}
		
		// Override
		public function isRunning()
		{
			return file_exists($this->service->pid);
		}
		
		// Override
		public function getIpRanges()
		{
			return $this->ipRanges;
		}
		
		// Override
		public function getStickyIps()
		{
			return $this->stickyIps;
		}
		
		// Override
		public function getDomain()
		{
			return $this->domain;
		}
		
		// Override
		public function getNameservers()
		{
			return $this->nameservers;
		}
		
		// Override
		public function setIpRanges(array $ipRanges)
		{
			if (empty($ipRanges))
				throw new Exception("At least one IP range must be specified");	
			
			$temp = array();
			
			foreach ($ipRanges as $ipRange)
			{
				$ipRange = (object) $ipRange;
				
				if (empty($ipRange->startIp) && empty($ipRange->endIp))
					continue;
				
				if (!NetUtils::isValidIp($ipRange->startIp) || !NetUtils::isValidIp($ipRange->endIp))
					throw new Exception("Invalid IP entered for ip range: " . $ipRange->startIp . " - " . $ipRange->endIp);
					
				$temp[] = $ipRange;
			}
			
			$this->ipRanges = $temp;
		}
		
		// Override
		public function setStickyIps(array $stickyIps)
		{
			$temp = array();
			
			foreach ($stickyIps as $stickyIp)
			{
				$stickyIp = (object) $stickyIp;
				
				if (empty($stickyIp->name) && empty($stickyIp->mac) && empty($stickyIp->ip))
					continue;
				
				if (!NetUtils::isValidIp($stickyIp->ip) || !NetUtils::isValidMac($stickyIp->mac))
					throw new Exception("Invalid IP or MAC entered for sticky IP");
					
				$temp[] = $stickyIp;
			}
			
			$this->stickyIps = $temp;
		}
		
		// Override
		public function setDomain($domain)
		{
			$this->domain = $domain;
		}
		
		// Override
		public function setNameservers(array $nameservers)
		{
			$temp = array();
			
			foreach ($nameservers as $nameserver)
			{
				if (empty($nameserver))
					continue;
					
				if (!NetUtils::isValidIp($nameserver))
					throw new Exception("Invalid IP entered for nameserver");
					
				$temp[] = $nameserver;
			}
			
			$this->nameservers = $temp;
		}
		
		// Override
		public function getDefaultAccessRules()
		{
			$defaultRule = new FirewallRule();
			
			$defaultRule->setAllAttributes(array	(
														"service_id" => $this->getAttribute("id"),
														"int_in" => NetworkInterface::getInstance("LAN")->getPhysicalInterface(),
														"protocol" => "udp",
														"dport" => 67,
														"target" => "ACCEPT"
													));
													
			return array($defaultRule);
		}
	}
?>