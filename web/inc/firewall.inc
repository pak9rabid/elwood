<?
   ################################################
   # File:          firewall.inc                  #
   # Author:        Patrick Griffin               #
   # Last Modified: 4-13-2005                     #
   #                                              #
   # Description:   Provides classes and          #
   #                functions to retrieve and     #
   #                store current firewall rules  #
   #                on the router.                #
   ################################################

   # Include required files
   require_once "routersettings.inc";

   class firewallRules
   # Provides an object to view the current firewall rules on the system
   {
      # Arrays used to store firewall rules
      var $inIfacesList;
      var $outIfacesList;
      var $protocolsList;
      var $portsList;
      var $sourcesList;
      var $destsList;
      var $targetsList;
      var $statesList;

      # Store current forward chain policy
      var $policy;

      function firewallRules()
      # Default constructor: initializes arrays and populates them with 
      # the current firewall rules
      {
         # Get elwood.conf settings
         $BIN_PREFIX = getElwoodWebroot() . "/bin";

         # Initialize arrays used to store firewall rules info
         $this->inIfacesList  = array();
         $this->outIfacesList = array();
         $this->protocolsList = array();
         $this->portsList     = array();
         $this->sourcesList   = array();
         $this->destsList     = array();
         $this->targetsList   = array();
         $this->statesList    = array();

         # Commands to get current firewall rules
         $SHOWFORWARD  = "sudo $BIN_PREFIX/show_forward_table";
         $SHOWFORWARD2 = "sudo $BIN_PREFIX/show_forward_table_2";

         # Get interfaces
         $EXTIF = getExtIf();
         $INTIF = getIntIf();

         # Current filewall rules
         $getInIfacesCmd  = "$SHOWFORWARD | egrep '($EXTIF)|($INTIF)' | cut -b33-36";
         $getOutIfacesCmd = "$SHOWFORWARD | egrep '($EXTIF)|($INTIF)' | cut -b40-43";
         $getProtocolsCmd = "$SHOWFORWARD | egrep '($EXTIF)|($INTIF)' | cut -b24-28";
         $getPortsCmd     = "$SHOWFORWARD | egrep '($EXTIF)|($INTIF)' | cut -f2 -d:";
         $getSourcesCmd   = "$SHOWFORWARD | egrep '($EXTIF)|($INTIF)' | cut -b48-67";
         $getDestsCmd     = "$SHOWFORWARD | egrep '($EXTIF)|($INTIF)' | cut -b69-86";
         $getTargetsCmd   = "$SHOWFORWARD | egrep '($EXTIF)|($INTIF)' | cut -b13-22";
         $getStatesCmd    = "$SHOWFORWARD | egrep '($EXTIF)|($INTIF)' | cut -b88-200";
         $getRuleNumsCmd  = "$SHOWFORWARD2 | egrep '($EXTIF)|($INTIF)' | cut -f1 -d' '";

         # Current forward policy
         $getPolicyCmd    = "$SHOWFORWARD | egrep '(Chain)|(policy)' | cut -f4 -d' '";

         # Clear arrays
         unset($this->inIfacesList,
               $this->outIfacesList,
               $this->protocolsList,
               $this->portsList,
               $this->sourcesList,
               $this->destsList,
               $this->targetsList,
               $this->statesList);

         # Execute shell commands to obtain firewall rules, store results in appropriate arrays
         exec($getInIfacesCmd,  $this->inIfacesList);
         exec($getOutIfacesCmd, $this->outIfacesList);
         exec($getProtocolsCmd, $this->protocolsList);
         exec($getPortsCmd,     $this->portsList);
         exec($getSourcesCmd,   $this->sourcesList);
         exec($getDestsCmd,     $this->destsList);
         exec($getTargetsCmd,   $this->targetsList);
         exec($getStatesCmd,    $this->statesList);

         # Execute shell command to obtain forwarding policy, store results in var
         $this->policy = `$getPolicyCmd`;
      }

      #############
      # Accessors #
      #############

      function getInIface($ruleNum)
      # Preconditions:  None
      # Postconditions: Returns the incoming interface for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         return $this->inIfacesList[$index];
      }

      function getOutIface($ruleNum)
      # Preconditions:  None
      # Postconditions: Returns the outgoing interface for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         return $this->outIfacesList[$index];
      }

      function getProtocol($ruleNum)
      # Preconditions:  None
      # Postconditions: Returns the protocol for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         return $this->protocolsList[$index];
      }

      function getPort($ruleNum)
      # Preconditions:  None
      # Postconditions: Returns the port for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         return $this->portsList[$index];
      }

      function getSource($ruleNum)
      # Preconditions:  None
      # Postconditions: Returns the source IP for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         return $this->sourcesList[$index];
      }

      function getDest($ruleNum)
      # Preconditions:  None
      # Postconditions: Returns the destination IP for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         return $this->destsList[$index];
      }

      function getTarget($ruleNum)
      # Preconditions:  None
      # Postconditions: Returns the target for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         return $this->targetsList[$index];
      }

      function getState($ruleNum)
      # Preconditions:  None
      # Postconditions: Returns the state for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         return $this->statesList[$index];
      }

      function getInIfacesList()
      # Preconditions:  None
      # Postconditions: Returns an array of inIfaces
      {
         return $this->inIfacesList;
      }

      function getOutIfacesList()
      # Preconditions:  None
      # Postconditions: Retens an array of outIfaces
      {
         return $this->outIfacesList;
      }

      function getProtocolsList()
      # Preconditions:  None
      # Postconditions: Returns an array of protocols
      {
         return $this->protocolsList;
      }

      function getPortsList()
      # Preconditions:  None
      # Postconditions: Returns an array of ports
      {
         return $this->portsList;
      }

      function getSourcesList()
      # Preconditions:  None
      # Postconditions: Returns an array of source IP's
      {
         return $this->sourcesList;
      }
   
      function getDestsList()
      # Preconditions:  None
      # Postconditions: Returns an array of destination IP's
      {
         return $this->destsList;
      }

      function getTargetsList()
      # Preconditions:  None
      # Postconditions: Returns an array of targets
      {
         return $this->targetsList;
      }

      function getStatesList()
      # Preconditions:  None
      # Postconditions: Returns an array of states
      {
         return $this->statesList;
      }

      function getPolicy()
      # Preconditions:  None
      # Postconditions: Returns current forward chain policy
      {
         return trim($this->policy);
      }

      #############
      # Modifiers #
      #############

      function setInIface($ruleNum, $value)
      # Preconditions:  None
      # Postconditions: Sets incoming interface to $value for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         $this->inIfacesList[$index] = $value;
      }

      function setOutIface($ruleNum, $value)
      # Preconditions:  None
      # Postconditions: Sets outgoing interface to $value for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         $this->outIfacesList[$index] = $value;
      }

      function setProtocol($ruleNum, $value)
      # Preconditions:  None
      # Postconditions: Sets protocol to $value for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         $this->protocolsList[$index] = $value;
      }

      function setPort($ruleNum, $value)
      # Preconditions:  None
      # Postconditions: Sets port to $value for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         $this->portsList[$index] = $value;
      }

      function setSource($ruleNum, $value)
      # Preconditions:  None
      # Postconditions: Sets source IP to $value for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         $this->sourcesList[$index] = $value;
      }

      function setDest($ruleNum, $value)
      # Preconditions:  None
      # Postconditions: Sets destination IP to $value for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         $this->destsList[$index] = $value;
      }

      function setTarger($ruleNum, $value)
      # Preconditions:  None
      # Postconditions: Sets target to $value for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         $this->targetsList[$index] = $value;
      }

      function setState($ruleNum, $value)
      # Preconditions:  None
      # Postconditions: Sets state to $value for the specified $ruleNum
      {
         $index = $ruleNum - 1;
         $this->statesList[$index] = $value;
      }

      function setPolicy($newPolicy)
      # Preconditions:  None
      # Postconditions: Sets $policy to $newPolicy
      {
         $this->policy = $newPolicy;
      }
   }

   class chainPolicies
   # Provides an object to view the current firewall chain policies
   {
      # Vars used to store chain policies
      var $inputPolicy;
      var $outputPolicy;
      var $forwardPolicy;
      var $preroutingPolicy;

      function chainPolicies()
      # Default constructor: Used to populate policy vars with current chain policies
      {
         # Set bin prefix
         $BIN_PREFIX = getElwoodWebroot() . "/bin";

         # Commands used to get current chain policies
         $SHOWINPUT      = "sudo $BIN_PREFIX/show_input_table";
         $SHOWOUTPUT     = "sudo $BIN_PREFIX/show_output_table";
         $SHOWFORWARD    = "sudo $BIN_PREFIX/show_forward_table";
         $SHOWPREROUTING = "sudo $BIN_PREFIX/show_port_forwarding";

         $getInputPolicyCmd      = "$SHOWINPUT | egrep '(Chain)|(policy)' | cut -f4 -d' '";
         $getOutputPolicyCmd     = "$SHOWOUTPUT | egrep '(Chain)|(policy)' | cut -f4 -d' '";
         $getForwardPolicyCmd    = "$SHOWFORWARD | egrep '(Chain)|(policy)' | cut -f4 -d' '";
         $getPreroutingPolicyCmd = "$SHOWPREROUTING | egrep '(Chain)|(policy)' | cut -f4 -d' '";

         # Execute commands and store results into variables
         $this->inputPolicy      = `$getInputPolicyCmd`;
         $this->outputPolicy     = `$getOutputPolicyCmd`;
         $this->forwardPolicy    = `$getForwardPolicyCmd`;
         $this->preroutingPolicy = `$getPreroutingPolicyCmd`;
      }

      function getInputPolicy()
      # Preconditions:  None
      # Postconditions: Returns the status of the input chain policy
      {
         return trim($this->inputPolicy);
      }

      function getOutputPolicy()
      # Preconditions:  None
      # Postconditions: Returns the status of the output chain policy
      {
         return trim($this->outputPolicy);
      }

      function getForwardPolicy()
      # Preconditions:  None
      # Postconditions: Returns the status of the forward chain policy
      {
         return trim($this->forwardPolicy);
      }

      function getPreroutingPolicy()
      # Preconditions:  None
      # Postconditions: Returns the status of the prerouting chain policy
      {
         return trim($this->preroutingPolicy);
      }
   }
?>
