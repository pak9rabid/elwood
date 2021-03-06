<?php
	require_once "SystemProfile.class.php";
	require_once "DataHash.class.php";
	require_once "NetUtils.class.php";
	require_once "Console.class.php";
	require_once "NetworkInterfaceNotFoundException.class.php";
	require_once "NetworkInterfaceAlreadyUsedException.class.php";
	
	abstract class NetworkInterface
	{
		private $interfaceHash;
		
		abstract public function apply();
		
		public static function getInstance($interfaceName = "")
		{
			if (empty($interfaceName))
				throw new Exception("Interface not specified");
				
			$profile = SystemProfile::getProfile();			
			$className = $profile->ClassMappings->NetworkInterface->class;
			
			require_once "$className.class.php";
			
			$interface = new $className();
			
			if (!$interface instanceof self)
				throw new Exception("$className is not a subclass of NetworkInterface");
							
			try
			{
				$interface->load($interfaceName);
			}
			catch (NetworkInterfaceNotFoundException $ex)
			{
				$interface->interfaceHash = new DataHash("interfaces");
				$interface->interfaceHash->setAttribute("name", $interfaceName);
			}
							
			return $interface;
		}
		
		public static function getAvailableWiredInterfaces()
		{
			return Console::execute("ip link show | egrep 'eth[0-9].*' | awk '{print $2}' | sed 's/://'", true);
		}
		
		public static function getAvailableWirelessInterfaces()
		{
			return Console::execute("iw dev | grep Interface | awk '{print $2}' | grep -v mon.", true);
		}
		
		public static function getAvailableBridgeInterfaces()
		{
			return Console::execute("brctl show | sed '1 d' | egrep '^\b' | awk '{print $1}'", true);
		}
		
		public static function getAvailableInterfaces()
		{
			return array_merge	(
									self::getAvailableWiredInterfaces(),
									self::getAvailableWirelessInterfaces(),
									self::getAvailableBridgeInterfaces()
								);
		}
		
		public function save()
		{				
			$id = $this->interfaceHash->getAttribute("id");
			
			if (!empty($id))
				$this->interfaceHash->executeUpdate();
			else
			{
				$this->interfaceHash->executeInsert();
				
				// retrieve the newly-inserted interface from the database
				// (this will effectively set the 'id' attribute for the interfaceHash,
				// which is unknown until inserted into the database)
				$this->load($this->getName());
			}
		}
		
		public function delete()
		{
			$id = $this->interfaceHash->getAttribute("id");
			
			if (empty($id))
				throw new Exception("Interface " . $this->getName() . " cannot be deleted because it doesn't exist");
				
			$this->interfaceHash->executeDelete();
			$this->interfaceHash->removeAttribute("id");
		}
		
		public function load($interfaceName)
		{
			// loads the specified $interfaceName settings from the database
			$selectHash = new DataHash("interfaces");
			$selectHash->setAttribute("name", $interfaceName);
			
			$results = $selectHash->executeSelect(true);
			
			if (empty($results))
				throw new NetworkInterfaceNotFoundException("Interface $interfaceName does not exist");
				
			foreach ($results as $result)
				$this->interfaceHash = $result;
		}
						
		public function getName()
		{
			return $this->interfaceHash->getAttribute("name");
		}
		
		public function getPhysicalInterface()
		{
			return $this->interfaceHash->getAttribute("physical_int");
		}
		
		public function getBridgedInterfaces()
		{
			$bridgedInts = $this->interfaceHash->getAttribute("bridged_ints");
			
			return empty($bridgedInts) ? array() : explode(",", $bridgedInts);
		}
		
		public function usesDhcp()
		{
			return $this->interfaceHash->getAttribute("uses_dhcp") == "Y" ? true : false;
		}
		
		public function getAddress()
		{
			return $this->interfaceHash->getAttribute("address");
		}
		
		public function getIp()
		{
			$address = $this->interfaceHash->getAttribute("address");
			
			if (empty($address))
				return "";
			
			list($ip, $netmask) = explode("/", $address);
			
			return $ip;
		}
		
		public function getNetmask($isCidrNotation = false)
		{
			$address = $this->interfaceHash->getAttribute("address");
			
			if (empty($address))
				return "";
				
			list($ip, $netmask) = explode("/", $address);
			
			if ($isCidrNotation)
				return $netmask;
				
			return NetUtils::cidr2Mask($netmask);
		}
		
		public function getMtu()
		{
			return $this->interfaceHash->getAttribute("mtu");
		}
		
		public function getGateway()
		{
			return $this->interfaceHash->getAttribute("gateway");
		}
		
		public function getDescription()
		{
			return $this->interfaceHash->getAttribute("description");
		}
				
		public function setName($name)
		{
			if (empty($name))
				throw new Exception("Interface name must be specified");
				
			if (NetUtils::isInterfaceNameUsed($name))
				throw new Exception("Interface name is already being used");
				
			$this->interfaceHash->setAttribute("name", $name);
		}
		
		public function setPhysicalInterface($interface)
		{
			if (empty($interface))
				throw new Exception("Physical interface name cannot be empty");
				
			if (NetUtils::isInterfaceUsed($interface))
				throw new NetworkInterfaceAlreadyUsedException("The specified physical interface is already being used");
			
			$this->interfaceHash->setAttribute("physical_int", $interface);
		}
		
		public function setBridgedInterfaces(array $interfaces)
		{
			if (empty($interfaces))
				$this->interfaceHash->setAttribute("bridged_ints", null);
			else
			{
				foreach ($interfaces as $interface)
				{
					if (NetUtils::isInterfaceUsed($interface))
						throw new NetworkInterfaceAlreadyUsedException("The specified physical interface is already being used");
				}
				
				$this->interfaceHash->setAttribute("bridged_ints", implode(",", $interfaces));
			}
		}
		
		public function setUsesDhcp($usesDhcp)
		{
			if ($usesDhcp)
				$this->interfaceHash->setAttribute("uses_dhcp", "Y");
			else
				$this->interfaceHash->setAttribute("uses_dhcp", null);
		}
		
		public function setAddress($address)
		{
			if (empty($address))
				$this->interfaceHash->setAttribute("address", null);
			else
			{
				if (!NetUtils::isValidAddress($address))
					throw new Exception("Invalid address specified");
					
				foreach ($this->getAliases() as $alias)
				{
					if ($address == $alias)
						throw new Exception("Address $address is already being used as an alias for interface " . $this->getPhysicalInterface());
				}
				
				$this->interfaceHash->setAttribute("address", $address);
			}
		}
		
		public function setMtu($mtu)
		{			
			if (empty($mtu))
				$this->interfaceHash->setAttribute("mtu", null);
			else
			{
				if (!NetUtils::isValidMtu($mtu))
					throw new Exception("Invalid MTU specified");
					
				$this->interfaceHash->setAttribute("mtu", $mtu);
			}
		}
		
		public function setGateway($gateway)
		{
			if (empty($gateway))
				$this->interfaceHash->setAttribute("gateway", null);
			else
			{
				if (!NetUtils::isValidIp($gateway))
					throw new Exception("Invalid gateway specified");
					
				$this->interfaceHash->setAttribute("gateway", $gateway);
			}
		}
		
		public function setDescription($description)
		{
			if (empty($description))
				$this->interfaceHash->setAttribute("description", null);
			else
				$this->interfaceHash->setAttribute("description", $description);
		}
		
		public function getAliases()
		{
			$aliases = $this->interfaceHash->getAttribute("address_aliases");
			
			return empty($aliases) ? array() : explode(",", $aliases);
		}
				
		public function addAlias($address)
		{
			if (!NetUtils::isValidAddress($address))
				throw new Exception("Invalid address specified for alias");
				
			$aliases = $this->getAliases();
			
			// ensure $address doesn't already exist as the main address or as an alias
			if ($address == $this->getAddress())
				throw new Exception("Alias address $address is already being used as the physical interface's address");
			
			foreach ($aliases as $alias)
			{
				if ($alias == $address)
					throw new Exception("Alias for interface " . $this->getPhysicalInterface() . " with address $address already exists");
			}
			
			$aliases[] = $address;
						
			$this->interfaceHash->setAttribute("address_aliases", implode(",", $aliases));
		}
				
		public function removeAlias($address)
		{
			$aliases = $this->getAliases();
			
			foreach ($aliases as $key => $alias)
			{
				if ($alias == $address)
				{
					unset($aliases[$key]);
					
					// should be  the only alias with that address..no need to continue checking the rest
					break;
				}
			}
						
			$this->interfaceHash->setAttribute("address_aliases", implode(",", $aliases));
		}
		
		public function clearAliases()
		{
			$this->interfaceHash->setAttribute("address_aliases", null);
		}
	}
?>