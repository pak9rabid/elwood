#include <stdio.h>
#include <unistd.h>
#include <string.h>
#include "config.h"

int main(int argc, char **argv)
{
   // Set uid
   setreuid(geteuid(), geteuid());

   // If minimum args are not met, 
   // don't contruct or execute iptables command
   if (argc > 5)
   {
      // Construct base command
      char fwCmd[128] = IPTABLES;
      strcat(fwCmd, " -t nat -A PREROUTING -p ");
      strcat(fwCmd, argv[2]);
      strcat(fwCmd, " -i ");
      strcat(fwCmd, argv[5]);
      strcat(fwCmd, " --dport ");
      strcat(fwCmd, argv[1]);
      strcat(fwCmd, " -j DNAT --to ");
      strcat(fwCmd, argv[3]);
      strcat(fwCmd, ":");
      strcat(fwCmd, argv[4]);

      // Execute shell command
      system(fwCmd);
   }
   else
      // Print usage synopsis
      printf("\nUsage: setpforwarding <port> <protocol> <destination ip> <destination port> <interface>\n\n");

   return 0;
}
