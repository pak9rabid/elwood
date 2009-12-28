<?php
   # Include required files
   require_once "usertools.inc";
   require_once "routertools.inc";
   require_once "RouterSettings.class.php";

   # Find what group the current user is associated with
   $currentUser = getUser();
   $userGroup   = getGroup($currentUser);

   # Get internal and external interfaces
   $EXTIF = RouterSettings::getSettingValue("EXTIF");
   $INTIF = RouterSettings::getSettingValue("INTIF");
?>

<html>
   <head>
      <title>Firewall Rule Detail</title>
      <link rel="StyleSheet" type="text/css" href="../fwdetail.css">
   </head>

   <body>
      <div id="content">
         <?php
            # Commonly used commands
            $SHOWFORWARD = "sudo ../bin/show_forward_table";

            # Pull data from form
            $stream    = $_GET['stream'];
            $index     = $_GET['index'];
            $port      = $_GET['port'];
            $protocol  = $_GET['protocol'];
            $job       = $_GET['job'];

            # Construct commands to get source and destination IP lists
            $getSourceListCmd      = "$SHOWFORWARD | egrep '($EXTIF   $INTIF)|($INTIF   $EXTIF)' | cut -b48-63";
            $getDestinationListCmd = "$SHOWFORWARD | egrep '($EXTIF   $INTIF)|($INTIF   $EXTIF)' | cut -b69-84";

            # Construct commands to get rule details list
            $getDetailRulesListCmd = "$SHOWFORWARD | egrep '($INTIF)|($EXTIF)' | cut -b88-200";

            # Initialize arrays used to store firewall data
            $sourceList      = array();
            $destinationList = array();
            $detailRulesList = array();

            # Clear out data in the arrays if any
            unset($sourceList);
            unset($destinationList);
            unset($detailRulesList);

            # Execute commands and store data into arrays
            exec($getSourceListCmd, $sourceList);
            exec($getDestinationListCmd, $destinationList);
            exec($getDetailRulesListCmd, $detailRulesList);

            # Output detailed firewall rule
            $protocolString = toProtocolString($protocol);

            if ($port == "")
               $port = "N/A";

            $key = $index - 1;
            
            # Set font color for job type
            $red   = "#FFAAAA";
            $green = "#99FF99";

            $job = trim($job);
 
            if ($job == "ACCEPT")
               $jobColor = $green;
            else if ($job == "DROP")
               $jobColor = $red;

echo <<<END
            <table id="access-table">
               <tr><th>Stream Type</th><th>Port</th><th>Protocol</th><th>Source</th><th>Destination</th><th>Job</th></tr>
               <tr><td bgcolor='$jobColor'>$stream</td><td bgcolor='$jobColor'>$port</td><td bgcolor='$jobColor'>$protocolString</td><td bgcolor='$jobColor'>$sourceList[$key]</td><td bgcolor='$jobColor'>$destinationList[$key]</td><td bgcolor='$jobColor'>$job</td></tr>
END;
            if ($detailRulesList[$key] != "")
            {
               echo "<tr><th colspan='6'>Optional Rules</th></tr>";
               echo "<tr><td colspan='6' bgcolor='$jobColor'>$detailRulesList[$key]</td></tr>";
            }
echo <<<END
            </table>
            <br>
END;
            # If user is part of the admisn group, display option to remove the rule
            if ($userGroup == "admins")
               echo "<a href='admin/change_firewall.php?action=rm&chain=FORWARD&index=$index'>Remove Rule</a>";
         ?>
      </div>
   </body>
</html>
