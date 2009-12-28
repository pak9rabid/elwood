<?
   ################################################
   # File:          portforward.inc               #
   # Author:        Patrick Griffin               #
   # Last Modified: 7-23-2005                     #
   #                                              #
   # Description:   Provides classes and          #
   #                functions to retrieve and     #
   #                store current port-forwarding #
   #                rules on the router.          #
   ################################################

   # Include required files
   require_once "RouterSettings.class.php";

   class portForwardRules
   # Provides an object to view the current port-forwarding rules on the system
   {
      # Arrays used to store port-forwarding rules
      var $portsList;
      var $protocolsList;
      var $destinationIPsList;
      var $destinationPortsList;

      function portForwardRules()
      # Default constructor: initializes arrays and populates them with 
      # the current port-forwarding rules
      {
         # Get elwood.conf settings
         $BIN_PREFIX = RouterSettings::getSettingValue("ELWOOD_WEBROOT") . "/bin";

         # Initialize arrays used to store port-forwarding rules info
         $portsList            = array();
         $protocolsList        = array();
         $destinationIPsList   = array();
         $destinationPortsList = array();

         # Commands to get current port-forwarding rules
         $SHOWPORTFORWARDING = "sudo $BIN_PREFIX/show_port_forwarding";

         # Current port-forwarding rules
         $getPortsCmd            = "$SHOWPORTFORWARDING | grep to | awk '{print $11}' | cut -f2 -d':'";
         $getProtocolsCmd        = "$SHOWPORTFORWARDING | grep to | awk '{print $4}'";
         $getDestinationIPsCmd   = "$SHOWPORTFORWARDING | grep to | awk '{print $12}' | cut -f2 -d':'";
         $getDestinationPortsCmd = "$SHOWPORTFORWARDING | grep to | awk '{print $12}' | cut -f3 -d':'";

         # Clear arrays
         unset($this->portsList, $this->protocolsList, $this->destinationIPsList, $this->destinationPortsList);

         # Execute shell commands to obtain firewall rules, store results in appropriate arrays
         exec($getPortsCmd,            $this->portsList);
         exec($getProtocolsCmd,        $this->protocolsList);
         exec($getDestinationIPsCmd,   $this->destinationIPsList);
         exec($getDestinationPortsCmd, $this->destinationPortsList);
      }
     
      #############
      # Accessors #
      #############

      function getPort($ruleNum)
      # Preconditions:  None
      # Postconditions: Returns the port of the specified $ruleNum
      {
         $index = $ruleNum - 1;
         return $this->portsList[$index];
      }

      function getProtocol($ruleNum)
      # Preconditions:  None
      # Postconditions: Returns the protocol for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         return $this->protocolsList[$index];
      }

      function getDestinationIP($ruleNum)
      # Preconditions:  None
      # Postconditions: Returns the destination IP for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         return $this->destinationIPsList[$index];
      }

      function getDestinationPort($ruleNum)
      # Preconditions:  None
      # Postconditions: Returns the destination port for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         return $this->destinationPortsList[$index];
      }
   
      function getPortsList()
      # Preconditions:  None
      # Postconditions: Returns an array of ports
      {
         return $this->portsList;
      }

      function getProtocolsList()
      # Preconditions:  None
      # Postconditions: Returns an array of protocols
      {
         return $this->protocolsList;
      }

      function getDestinationIPsList()
      # Preconditions:  None
      # Postconditions: Returns an array of IP destinations
      {
         return $this->destinationIPsList;
      }

      function getDestinationPortsList()
      # Preconditions:  None
      # Postconditions: Returns an array of port destinations
      {
         return $this->destinationPortsList;
      }

      #############
      # Modifiers #
      #############

      function setPort($ruleNum, $value)
      # Preconditions:  None
      # Postconditions: Sets port to $value for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         $this->portsList[$index] = $value;
      }

      function setProtocol($ruleNum, $value)
      # Preconditions:  None
      # Postconditions: Sets protocol to $value for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         $this->protocolsList[$index] = $value;
      }

      function setDestinationIP($ruleNum, $value)
      # Preconditions:  None
      # Postconditions: Sets destination IP to $value for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         $this->destinationIPsList[$index] = $value;
      }

      function setDestinationPort($ruleNum, $value)
      # Preconditions:  None
      # Postconditions: Sets destination port to $value for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         $this->destinationPortsList[$index] = $value;
      }
   }
?>
