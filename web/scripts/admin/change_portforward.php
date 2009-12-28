<?php
   # Include files to provide required classes and functions
   require_once "portforward.inc";
   require_once "routertools.inc";
   require_once "RouterSettings.class.php";
   require_once "usertools.inc";

   # Pull universal data from the calling page
   $action = $_GET['action'];

   # Get path to firewall script directory
   $firewallDir = RouterSettings::getSettingValue("FIREWALL_DIR");

   # Get current user
   $currentUser = getUser();

   # Path to port forwarding file
   $portForwardFile = "$firewallDir/firewall.portforward";

   # Location of firewall script to be executed upon completion
   $fwScript = "$firewallDir/rc.firewall";

   # Construct file header
   $date = trim(`date`);

   $outToFile = "# This file contains port forwarding options\n" .
                "# Automatically generated on $date by user $currentUser\n\n" .
                "# Port forwarding rules\n";

   # Get current port forwarding rules
   $portForwardRules = new portForwardRules;

   # Check to see which action is being performed (remove, modify or add rule)
   if ($action == "rm") # Remove rule from port forwarding
   {
      # Pull data relevant to removing a rule
      $index = $_GET['index'];

      # Specify which rule is to be removed
      $portForwardRules->setPort($index, "REMOVE");

      # Construct appropriate output to store in port forwarding config file
      foreach ($portForwardRules->getPortsList() as $key => $value)
      {
         $ruleNum = $key + 1;

         # Construct output to send to file
         if ($value != "REMOVE")
         {
            $outToFile .= "\$SETPFORWARDING " .
                          $portForwardRules->getPort($ruleNum) . " " .
                          $portForwardRules->getProtocol($ruleNum) . " " .
                          $portForwardRules->getDestinationIP($ruleNum) . " " .
                          $portForwardRules->getDestinationPort($ruleNum) .
                          " \$EXTIF\n"; 
         }
      }
   }

   # Add a rule to the firewall script
   else if ($action == "add")
   {
      # Pull data from calling page specific to adding a new rule
      $port            = $_GET['port'];
      $protocol        = $_GET['protocol'];
      $destinationIP   = $_GET['destination_ip'];
      $destinationPort = $_GET['destination_port'];

      # Construct port forward command to set new rule
      $portForwardCmd = "\$SETPFORWARDING $port $protocol $destinationIP $destinationPort \$EXTIF";

      # Construct appropriate output to store in port forward config file
      foreach ($portForwardRules->getPortsList() as $key => $value)
      {
         $ruleNum = $key + 1; 

         $outToFile .= "\$SETPFORWARDING " .
                       $portForwardRules->getPort($ruleNum) . " " .
                       $portForwardRules->getProtocol($ruleNum) . " " .
                       $portForwardRules->getDestinationIP($ruleNum) . " " .
                       $portForwardRules->getDestinationPort($ruleNum) .
                       " \$EXTIF\n";
      }

      # Append new firewall rule to output
      $outToFile .= "$portForwardCmd\n";
   }

   # Append "Completed firewall.other" message to the end of the file
   $outToFile .= "\necho -e \"Completed firewall.portforward\"";

   # Write to firewall config file
   writeToFile($portForwardFile, $outToFile);

   # Execute firewall script
   $scriptOutput = `$fwScript`;

   # Kill pop-up window and refresh firewall page if we are adding a rule
   if ($action == "add")
   {
echo <<<END
   <script language='JavaScript' type='text/javascript'>
      window.close();
      opener.window.location.reload();
   </script>
END;
   }
   else
      header("Location: ../../portforward.php");
?>
