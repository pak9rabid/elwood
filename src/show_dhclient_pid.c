#include <stdio.h>
#include <unistd.h>
#include "config.h"

int main(int argc, char **argv)
{
   // Check for min args
   if (argc > 1)
   {
      // Set user permissions
      setreuid(geteuid(), geteuid());

      // Set command to be executed
      char showDhclientPidCmd[128] = "cat /var/run/dhclient.";

      strcat(showDhclientPidCmd, argv[1]);
      strcat(showDhclientPidCmd, ".pid");

      // Execute command
      system(showDhclientPidCmd);
   }
   else
      // Print usage synopsis
      printf("\nUsage: show_dhclient_pid <interface>\n\n");

   return(0);
}
