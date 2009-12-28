<?php
   ################################################
   # File :       webterm.php                     #
   #                                              #
   # Author:      Patrick Griffin                 #
   #                                              #
   # Date:        1-1-2006                        #
   #                                              #
   # Description: Provides a web-based terminal   #
   #              emulator to execute Linux       #
   #              commands in.  Script only has   #
   #              the ability to run simple Linux #
   #              commands that are not           #
   #              interactive.                    #
   ################################################

   # Include required files
   require_once "formatting.inc";
   require_once "RouterSettings.class.php";

   # Set the maximum number of commands stored in history file
   define("MAX_CMD_HISTORY", 50);

   # Get the current user
   $currentUser = getUser();

   # Path to webterm history file
   $webtermHistoryFile = RouterSettings::getSettingValue("ELWOOD_HISTORY") . "/$currentUser";

   # Pull commands from form
   $cmd     = $_POST['cmd'];
   $prevCmd = $_POST['prev_cmd'];

   # Convert all single-quotes to double-quotes, if any, in the entered command
   $cmd = preg_replace("/\'/", "\"", $cmd);

   # Create list of previously executed commands
   $getPrevCmdsCmd = "cat $webtermHistoryFile";

   $prevCmdsList = array();
   unset($prevCmdsList);

   exec($getPrevCmdsCmd, $prevCmdsList);

   # Append last entered command to webterm history and execute command if entered
   if ($cmd != "" || $prevCmd != "")
   {
      # Determine which command to execute (the new command or one of the previous
      # commands)
      if ($cmd != "")
         $executeThis = $cmd;
      else
         $executeThis = $prevCmd;


      # Append executed command to the end of the previous commands list
      # if it does not contain any html tags
      $test = strip_tags($executeThis);

      $containsTags = false;
 
      if ($test == $executeThis)
         $prevCmdsList[] = $executeThis;
      else
         $containsTags = true;


      # Determine how many previous commands there are and determine if
      # it exceeds the max number of commands allowed to be stored in the
      # history file
      $prevCmdsCount = count($prevCmdsList);

      if ($prevCmdsCount > MAX_CMD_HISTORY)
         $skipCmdsCount = $prevCmdsCount - MAX_CMD_HISTORY;
      else
         $skipCmdsCount = 0;

      # Write previous commands to history file only if they are within the maximum 
      # allowed range specified by MAX_CMD_HISTORY
      $outToFile = "";
       
      foreach ($prevCmdsList as $key => $value)
      {
         if ($key >= $skipCmdsCount)
         {
            $outToFile .= "$value";

            # If at the last element in the array, do not print a newline
            if ($key != $prevCmdsCount-1)
               $outToFile .= "\n";
         }
      }
      
      $writeToHistoryFileCmd = "echo '$outToFile' > $webtermHistoryFile";
      shell_exec($writeToHistoryFileCmd);

      $output = `$executeThis`;

      # Check to see if there are any </textarea> tags in the $output and
      # replace them with <\textarea> tags so it does not break the webpage
      $output = preg_replace("/<\/textarea>/", "<\\textarea>", $output);
   }

   # Flip the array around so that it reflects the order in which
   # each command was entered in the select list
   $prevCmdsList = array_reverse($prevCmdsList);
?>

<html>
   <head>
      <title>WebTerm</title>
      <link rel="StyleSheet" type="text/css" href="routerstyle.css">
   </head>

   <body onLoad="document.form.cmd.focus()">
      <div id="container">
         <div id="title">
            <?php printTitle("WebTerm"); ?>
         </div>
         <?php
            echo printNavigation();
         ?>
         <div id="content">
            <h4>Command Output</h4>
<textarea readonly>
<? echo $output; ?>
</textarea>
            <div id="cmd-input">
               <?php
                  # If previous command contained tags, print warning message
                  # that the command will not be stored in history
                  if ($containsTags)
                     echo "<font id='error-font'>Warning: Last command was not saved in history because it contained HTML tags</font>";
               ?>
               <form name="form" action="webterm.php" method="POST">
                  <table border="0" align="center">
                     <tr>
                        <th align="right">Linux Command:</th>
                        <td><input id="textfield" name="cmd" size="35" maxlength="255"></td>
                     </tr>
                     <tr>
                        <th align="right">Previous Commands:</th>
                        <td>
                           <select id="cmd-list" name="prev_cmd">
                              <?php
                                 # Print commands as options in the select box
                                 foreach ($prevCmdsList as $value)
                                    echo "<option $value>$value\n";
                              ?>
                           </select>
                        </td>
                     </tr>
                     <tr>
                        <td colspan="2" align="center"><input type="submit" value="Execute"></td>
                     </tr>
                  </table>
               </form>
            </div>
         </div>
      </div>
   </body>
</html>
