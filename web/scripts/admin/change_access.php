<?php
   ################################################
   # File: change_access.php                      #
   # Author: Patrick Griffin                      #
   # Date: 12-16-2005                             #
   # Description: Takes access rules from calling #
   #              form and sets firewall rules.   #
   ################################################

   # Include files to provide required functions
   require_once "routertools.inc";
   require_once "usertools.inc";
   require_once "routersettings.inc";

   # Firewall file to modify controlling remote access
   $fwFile = getFirewallDir() . "/firewall.access";

   # Location of firewall script to be executed upon completion
   $fwScript = getFirewallDir() . "/rc.firewall";

   # Get info passed from the calling form
   $httpwan   = $_POST[httpwan];
   $httplan   = $_POST[httplan];
   $sshwan    = $_POST[sshwan];
   $sshlan    = $_POST[sshlan];
   $telnetwan = $_POST[telnetwan];
   $telnetlan = $_POST[telnetlan];

   # Process results and structure iptables commands
   $httpFwCmds   = createFwRules($httpwan, $httplan, 8080);
   $sshFwCmds    = createFwRules($sshwan, $sshlan, 22);
   $telnetFwCmds = createFwRules($telnetwan, $telnetlan, 23);

   # Combine all commands to one string for output

   # Get the current user
   $currentUser = getUser();

   # Begin construction of the output string with time stamp
   $date = trim(`date`);
   $outToFile = "# This file controls where the router can be accessed from\n" .
                "# This file was generated automatically on $date by user $currentUser\n\n";
   
   # Combine all commands
   if ($httpFwCmds != "")
      $outToFile .= $httpFwCmds;
   if ($sshFwCmds != "")
      $outToFile .= $sshFwCmds;
   if ($telnetFwCmds != "")
      $outToFile .= $telnetFwCmds;

   # Append completion message to end of output
   $outToFile .= "\necho -e \"Completed firewall.access\"\n";

   # Write commands to file if able
   writeToFile($fwFile, $outToFile);

   # Execute new firewall script
   $scriptOutput = `$fwScript`;

   # Redirect to calling page (access.php)
   header("Location: ../../access.php");
   
   #####################################################
   #################### Functions ######################
   #####################################################

   function createFwRules($wan, $lan, $port)
   # Preconditions:  $wan and $lan must be either 1 or "",
   #                 $port must be a positive integer
   # Postconditions: Returns a string of iptables firewall commands
   {
      $fwCmds = "";

      if ($wan == 1 || $lan == 1)
      {
         $fwCmds = "\$SETACCESS ";
         if ($wan == 1 && $lan == 1)
         {
            $fwCmds .= "\$EXTIF $port ACCEPT\n";
            $fwCmds .= "\$SETACCESS \$INTIF $port ACCEPT\n";
         }
         else if ($wan == "" && $lan == 1)
            $fwCmds .= "\$INTIF $port ACCEPT\n";
         else if ($wan == 1 && $lan == "")
            $fwCmds .= "\$EXTIF $port ACCEPT\n";
      }

      return $fwCmds;
   }
?>
