<?php
   # Include required files
   require_once "firewall.inc";
   require_once "routertools.inc";
   require_once "routersettings.inc";
   require_once "usertools.inc";

   # Get interfaces
   $EXTIF = getExtIf();
   $INTIF = getIntIf();

   # Pull universal data from the calling page
   $action = $_GET['action'];

   # Get current user
   $currentUser = getUser();

   # Path to firewall files
   $firewallDir = getFirewallDir();

   $fwFile      = "$firewallDir/firewall.other";
   $fwPolicies  = "$firewallDir/firewall.general";

   # Location of firewall script to be executed upon completion
   $fwScript = "$firewallDir/rc.firewall";

   # Construct file header
   $date = trim(`date`);

   $outToFile = "# This file contains other firewalling options that may be desired\n" .
                "# Automatically generated on $date by user $currentUser\n\n" .
                "# Forwarding firewall rules\n";

   # Get current firewall rules
   $currentFwRules = new firewallRules;

   # Check to see which action is being performed (remove, modify or add rule)
   if ($action == "rm") # Remove rule from firewall
   {
      # Pull data relevant to removing a rule
      $index = $_GET['index'];

      # Specify which rule is to be removed
      $currentFwRules->setInIface($index, "REMOVE");

      # Construct appropriate output to store in firewall config file
      foreach ($currentFwRules->getInIfacesList() as $key => $value)
      {
         $ruleNum = $key + 1;

         # Construct output to send to file
         if ($value != "REMOVE")
         {
            $outToFile .= "\$FORWARDING ";

            # Interfaces
            if ($value == "$EXTIF")
               $outToFile .= "\$EXTIF \$INTIF ";
            else if ($value == "$INTIF")
               $outToFile .= "\$INTIF \$EXTIF ";

            # Protocol
            $outToFile .= $currentFwRules->getProtocol($ruleNum)." ";

            # Port
            $regEx = "(^[0-9]{1,5}$)";
         
             if (ereg($regEx, $currentFwRules->getPort($ruleNum)))
                $port = $currentFwRules->getPort($ruleNum);
             else
                $port = -1;

            # Source, destination, and target
            $outToFile .= "$port " .
                          $currentFwRules->getSource($ruleNum) .
                          " " .
                          $currentFwRules->getDest($ruleNum) .
                          " " .
                          $currentFwRules->getTarget($ruleNum) .
                          " ";

            # State
            $regEx = "(^state.)";

            if (ereg($regEx, $currentFwRules->getState($ruleNum)))
            {
               $currentState = $currentFwRules->getState($ruleNum);
               $states       = `echo $currentState | cut -f2 -d' '`;
            }
            else
               $states = "";
        
            $states     = trim($states);
            $outToFile .= "$states\n";
         }
      }
   }

   # Add a rule to the firewall script
   else if ($action == "add")
   {
      # Pull data from calling page specific to adding a new rule
      $stream    = $_GET['stream'];
      $port      = $_GET['port'];
      $protocol  = $_GET['protocol'];
      $sourceIp  = $_GET['sourceip'];
      $destIp    = $_GET['destip'];
      $job       = $_GET['job'];
      $state_est = $_GET['state_est'];
      $state_rel = $_GET['state_rel'];
      $state_new = $_GET['state_new'];
      $state_inv = $_GET['state_inv'];

      # Construct firewall command to set new rule
      if ($port == "")
         $port = -1;

      # Interfaces
      if ($stream == "Incoming")
         $fwCmd = "\$FORWARDING \$EXTIF \$INTIF ";
      else if ($stream == "Outgoing")
         $fwCmd = "\$FORWARDING \$INTIF \$EXTIF ";

      # Protocol
      if ($protocol == "")
         $fwCmd .= "all ";
      else
         $fwCmd .= "$protocol ";

      # Port
      $fwCmd .= "$port ";

      # Source
      if ($sourceIp == "")
         $fwCmd .= "0.0.0.0/0 ";
      else
         $fwCmd .= "$sourceIp ";

      # Destination
      if ($destIp == "")
         $fwCmd .= "0.0.0.0/0 ";
      else
         $fwCmd .= "$destIp ";

      # Target
      $fwCmd .= "$job ";

      # States
      $statesList = array($state_est, $state_rel, $state_new, $state_inv);
      $states = "";

      foreach ($statesList as $value)
      {
         if ($value != "")
         {
            if ($states == "")
               $states = $value;
            else
               $states .= ",$value";
         }
      }

      $fwCmd .= $states;

      # Test
      echo "<b>$states</b>";

      # Construct appropriate output to store in firewall config file
      foreach ($currentFwRules->getInIfacesList() as $key => $value)
      {
         $ruleNum = $key + 1; 

         $outToFile .= "\$FORWARDING ";

         # Interfaces
         if ($value == "$EXTIF")
            $outToFile .= "\$EXTIF \$INTIF ";
         else if ($value == "$INTIF")
            $outToFile .= "\$INTIF \$EXTIF ";

         # Protocol
         $outToFile .= $currentFwRules->getProtocol($ruleNum)." ";

         # Port
         $regEx = "(^[0-9]{1,5}$)";
         
          if (ereg($regEx, $currentFwRules->getPort($ruleNum)))
             $port = $currentFwRules->getPort($ruleNum);
          else
             $port = -1;

         # Port, source, destination, and target
         $outToFile .= "$port " .
                       $currentFwRules->getSource($ruleNum) .
                       " " .
                       $currentFwRules->getDest($ruleNum) .
                       " " .
                       $currentFwRules->getTarget($ruleNum) .
                       " ";

         # State
         $regEx = "(^state.)";

         if (ereg($regEx, $currentFwRules->getState($ruleNum)))
         {
            $currentState = $currentFwRules->getState($ruleNum);
            $states       = `echo $currentState | cut -f2 -d' '`;
         }
         else
            $states = "";
        
         $states     = trim($states);
         $outToFile .= "$states\n";
      }

      # Append new firewall rule to output
      $outToFile .= "$fwCmd\n";
   }

   # Change forwarding policy
   else if ($action == "policy")
   {
      # Pull data from form relevant to changing forwarding policy
      $changePolicy = $_GET['change'];
      $newPolicy    = $_GET['policy'];

      if ($changePolicy == "yes")
      {
         # Get current chain policies
         $currentPolicies = new chainPolicies;

         # Create file output
         $outToFile = "# This file contains general firewall rules\n" .
                      "# Automatically generated on $date\n" .
                      "# Clear all rules and set default policies\n" .
                      "\$SETPOLICY INPUT " . $currentPolicies->getInputPolicy() . "\n" .
                      "\$FLUSHCHAIN INPUT\n\n" .
                      "\$SETPOLICY OUTPUT " . $currentPolicies->getOutputPolicy() . "\n" .
                      "\$FLUSHCHAIN OUTPUT\n\n" .
                      "\$SETPOLICY FORWARD $newPolicy\n" .
                      "\$FLUSHCHAIN FORWARD\n\n" .
                      "\$SETPOLICY PREROUTING " . $currentPolicies->getPreroutingPolicy(). "\n" .
                      "\$FLUSHCHAIN PREROUTING\n\n" .
                      "\$SETPOLICY POSTROUTING ACCEPT\n" .
                      "\$FLUSHCHAIN POSTROUTING\n\n" .
                      "echo -e \"Completed firewall.general\"\n";

         # Write to policies config file
         writeToFile($fwPolicies, $outToFile);
      }
   }

   # Write to firewall.other
   if ($action == "rm" || $action == "add" || $action == "mod")
   {
      # Append "Completed firewall.other" message to the end of the file
      $outToFile .= "\necho -e \"Completed firewall.other\"";

      # Write to firewall config file
      writeToFile($fwFile, $outToFile);
   }

   # Execute firewall script
   $scriptOutput = `$fwScript`;

   # Kill pop-up window and refresh firewall page
echo <<<END
   <script language='JavaScript' type='text/javascript'>
      window.close();
      opener.window.location.reload();
   </script>
END;
?>
