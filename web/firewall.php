<?php
   ################################################
   # File :       firewall.php                    #
   #                                              #
   # Author:      Patrick Griffin                 #
   #                                              #
   # Date:        10-17-2005                      #
   #                                              #
   # Description: Display current firewall        #
   #              settings and provide an         #
   #              interface to change firewall    #
   #              settings.                       #
   ################################################

   # Include required files
   require_once "formatting.inc";
   require_once "firewall.inc";
   require_once "routertools.inc";
   require_once "routersettings.inc";

   # Get interfaces
   $EXTIF = getExtIf();
   $INTIF = getIntIf();

   # Create object to get current firewall rules
   $currentFwRules = new firewallRules;

   # Get the group the current user is associated with
   $currentUser = getUser();
   $userGroup   = getGroup($currentUser);
?>

<html>
   <head>
      <title>Firewall Setup</title>
      <link rel="StyleSheet" type="text/css" href="routerstyle.css">
      <script language="JavaScript" src="inc/firewall.js" type="text/javascript"></script>
   </head>

   <body>
      <div id="container">
         <div id="title">
            <?php echo printTitle("Firewall"); ?>
         </div>
         <?php
            printNavigation();
         ?>
         <div id="content">
            <?php
               # Arrays used to store firewall rules
               $incomingPortsList     = array();
               $outgoingPortsList     = array();
               $incomingProtocolsList = array();
               $outgoingProtocolsList = array();
               $incomingRuleNumsList  = array();
               $outgoingRuleNumsList  = array();
               $incomingJobsList      = array();
               $outgoingJobsList      = array();

               # Sort rules into incoming and outgoing groups
               foreach ($currentFwRules->inIfacesList as $key => $value)
               {
                  $ruleNum = $key + 1;

                  if ($value == "$EXTIF" && $currentFwRules->getOutIface($ruleNum) == "$INTIF")
                  {
                     $incomingPortsList[]     = $currentFwRules->getPort($ruleNum);
                     $incomingProtocolsList[] = $currentFwRules->getProtocol($ruleNum);
                     $incomingRuleNumsList[]  = $ruleNum;
                     $incomingJobsList[]      = $currentFwRules->getTarget($ruleNum);
                  }
                  else
                  {
                     $outgoingPortsList[]     = $currentFwRules->getPort($ruleNum);
                     $outgoingProtocolsList[] = $currentFwRules->getProtocol($ruleNum);
                     $outgoingRuleNumsList[]  = $ruleNum;
                     $outgoingJobsList[]      = $currentFwRules->getTarget($ruleNum);
                  }
               }

               # Display firewall settings
               printFwRules($incomingPortsList, $outgoingPortsList, $incomingProtocolsList, 
                            $outgoingProtocolsList, $incomingRuleNumsList, $outgoingRuleNumsList, 
                            $incomingJobsList, $outgoingJobsList, $currentFwRules->getPolicy(),
                            $userGroup);

               #######################################
               ############## Functions ##############
               #######################################

               function printFwRules(&$incomingPortsList, &$outgoingPortsList, &$incomingProtocolsList, 
                                     &$outgoingProtocolsList, &$incomingRuleNumsList, &$outgoingRuleNumsList, 
                                     &$incomingJobsList, &$outgoingJobsList, $forwardChainPolicy, $userGroup)
               # Preconditions:  &$incomingPortsList, &$outgoingPortsList, &$incomingProtocolsList, 
               #                 &$outgoingProtocolsList must be arrays
               # Postconditions: Displays firewall rules
               {
                  echo "<table border='0' align='center'>";
                  echo "<tr><td valign='top'>";
                  printFwTable("Incoming", $incomingPortsList, $incomingProtocolsList, $incomingRuleNumsList, $incomingJobsList, $forwardChainPolicy, $userGroup);
                  echo "</td><td valign='top'>";
                  printFwTable("Outgoing", $outgoingPortsList, $outgoingProtocolsList, $outgoingRuleNumsList, $outgoingJobsList, $forwardChainPolicy, $userGroup);
                  echo "</td></tr>";
	  echo "</table>";
       }

       function printFwTable($streamType, &$portsList, &$protocolsList, &$ruleNumsList, &$jobsList, $chainPolicy, $userGroup)
       # Preconditions:  $portsList and $protocolsList must be passed arrays by reference
       # Postconditions: Displays a specific chain's firewall rules in a formatted table
       {
	  # Determine what color to make the table and rows and what to set $url to
	  $strongred   = "#FF0000";
	  $stronggreen = "#00FF00";
	  $red         = "#FFAAAA";
	  $green       = "#99FF99";

	  if ($chainPolicy == "ACCEPT")
	  {
	     $tableBgColor = $stronggreen;
	     $url = "scripts/change_policy.php?policy=DROP";
	  }
	  else if ($chainPolicy == "DROP")
	  {
	     $tableBgColor = $strongred;
	     $url = "scripts/change_policy.php?policy=ACCEPT";
	  }

	  # Set popup window properties
	  $winProps = "height=90, width=250";

	  # Print firewall rules table
	  echo "<table id='firewall-table'>";
                  # Print option to change policy only if the user is part of the admins group
                  $tableHeader = "<tr><th colspan='2' bgcolor='$tableBgColor'>$streamType Traffic";

                  if ($userGroup == "admins")
                     $tableHeader .= "<br><a href='$url' onClick=\"return popUp('$url', '$winProps')\"><font size='1'>[Change Policy]</font></a>";

                  $tableHeader .= "</th></tr>";
                  echo $tableHeader;
                  echo "<tr><th bgcolor='$tableBgColor'>Port</th><th bgcolor='$tableBgColor'>Protocol</th></tr>";
                  if ($protocolsList[0] != "")
                  {
                     foreach ($portsList as $key => $value)
                     {
                        # Set popup window properties
                        $winProps = "height=200, width=500";

                        # Filter out non-port values in $portsList, change them to N/A
                        $regEx = "(^[0-9]{1,5}$)";

                        if (ereg($regEx, $portsList[$key]))
                           $port = $portsList[$key];
                        else
                           $port = "N/A";

                        $protocolString = toProtocolString($protocolsList[$key]);
                        $url = "scripts/firewall_detail.php?stream=$streamType&index=$ruleNumsList[$key]&port=$port&protocol=$protocolString&job=$jobsList[$key]";

                        # Determine which color to make the table row
                        if ($jobsList[$key] == "ACCEPT")
                           $rowBgColor = $green;
                        else if ($jobsList[$key] == "DROP")
                           $rowBgColor = $red;

                        echo "<tr><td bgcolor='$rowBgColor'><a href='$url' onClick=\"return popUp('$url', '$winProps')\">$port</a></td><td bgcolor='$rowBgColor'>$protocolString</td></tr>";
                     }
                  }
                  else
                     echo "<tr><td colspan='2'>None</td></tr>";

                  $url = "scripts/add_fw_rule.php?stream=$streamType";
                  $winProps = "height=270, width=580";

                  # If user is part of the admins group, print link to add a new rule
                  if ($userGroup == "admins")
                     echo "<tr><td colspan='2' bgcolor='#D0D0D0'><a href='$url' onClick=\"return popUp('$url', '$winProps')\">Add Rule</a></td></tr>";
                  echo "</table>";
               }
            ?>
         </div>
      </div>
   </body>
</html>
