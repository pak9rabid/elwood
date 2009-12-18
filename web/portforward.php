<?php
   ################################################
   # File :       portforward.php                 #
   #                                              #
   # Author:      Patrick Griffin                 #
   #                                              #
   # Date:        10-17-2005                      #
   #                                              #
   # Description: Display current port forwarding #
   #              settings and provide an         #
   #              interface to change portforward #
   #              settings.                       #
   ################################################

   # Include required files
   require_once "formatting.inc";
   require_once "portforward.inc";
   require_once "routertools.inc";

   # Find what group the current user is associated with
   $currentUser = getUser();
   $userGroup   = getGroup($currentUser);
?>

<html>
   <head>
      <title>Port Forwarding Setup</title>
      <link rel="StyleSheet" type="text/css" href="routerstyle.css">
      <script language="JavaScript" src="inc/portforward.js" type="text/javascript"></script>
   </head>

   <body>
      <div id="container">
         <div id="title">
            <?php echo printTitle("Port Forwarding"); ?>
         </div>
         <?php
            printNavigation();
         ?>
         <div id="content">
            <?php
               # Get current port forwarding rules
               $portForwardRules = new portForwardRules;

               # Display port forwarding settings
               printRules($portForwardRules->getPortsList(),
                          $portForwardRules->getProtocolsList(),
                          $portForwardRules->getDestinationIPsList(),
                          $portForwardRules->getDestinationPortsList(),
                          $userGroup);

               #######################################
               ############## Functions ##############
               #######################################

               function printRules(&$portsList, &$protocolsList, &$destinationIPsList, &$destinationPortsList, $userGroup)
               # Preconditions:  $portsList, $protocolsList, $destinationIPsList, and $destinationPortsList
               # must be arrays
               # Postconditions: Displays current port forwarding rules in a formatted table
               {
                  # Print port forwarding rules table
echo <<<END
                  <table id="port-forward-table">
                  <tr>
                     <th colspan="3">Port Forwarding Rules</th></tr>
                  </tr>
                  <tr>
                     <th width="33%">Port</th>
                     <th width="33%">Protocol</th>
                     <th width="33%">Destination</th>
                  </tr>
END;
                  if ($protocolsList[0] != "")
                  {
                     foreach ($portsList as $key => $value)
                     {
                        # Filter out non-port values in $portsList, change them to N/A
                        $regEx = "(^[0-9]{1,5}$)";

                        if (ereg($regEx, $portsList[$key]))
                           $port = $portsList[$key];
                        else
                           $port = "N/A";

                        $protocolString = toProtocolString($protocolsList[$key]);
                        $ruleNum = $key + 1;
                        $url = "scripts/admin/change_portforward.php?action=rm&index=$ruleNum";

                        $tableRow = "<tr><td>$port</td><td>$protocolString</td><td>$destinationIPsList[$key]:$destinationPortsList[$key]</td>";

                        # Print option to remove rule if the user is in the admins group
                        if ($userGroup == "admins")
                           $tableRow .= "<td><a id='smalllink' href='$url'>[Remove]</a></td>";
                        $tableRow .= "</tr>";
                        echo $tableRow;
                     }
                  }
                  else
                     echo "<tr><td colspan='3'>None</td></tr>";

                  $url = "scripts/add_portforwarding_rule.php";
                  $winProps = "height=220, width=450";
                  
                  # Print option to add a new rule if the user is part of the admins group
                  if ($userGroup == "admins")
                     echo "<tr><td colspan='3'><a href='$url' onClick=\"return popUp('$url', '$winProps')\">Add Rule</a></td></tr>";
                  echo "</table>";
               }
            ?>
         </div>
      </div>
   </body>
</html>
