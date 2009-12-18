#include <stdio.h>
#include <unistd.h>
#include <string.h>
#include "config.h"

int main(int argc, char **argv)
{
   // Set uid
   setreuid(geteuid(), geteuid());

   // Check for minimal arguments
   if (argc > 7)
   {
      // Construct firewall command and execute
      char fwCmd[128] = IPTABLES;
      strcat(fwCmd, " -A FORWARD -i ");
      strcat(fwCmd, argv[1]);
      strcat(fwCmd, " ");
      strcat(fwCmd, "-o ");
      strcat(fwCmd, argv[2]);
      
      // Include connection states, if specified
      if (argv[8] != '\0')
      {
         strcat(fwCmd, " -m state --state ");
         strcat(fwCmd, argv[8]);
      }

      // If protocol is not 'all' then append to the firewall command
      if ( !(strcmp(argv[3], "all") == 0) )
      {
         strcat(fwCmd, " -p ");
         strcat(fwCmd, argv[3]);
      }
     
      // If port is not -1, then append port number to the firewall commmand
      if (!(strcmp(argv[4], "-1") == 0))
      {
         strcat(fwCmd, " --dport ");
         strcat(fwCmd, argv[4]);
      }

      // Append source ip/network to the firewall command
      strcat(fwCmd, " -s ");
      strcat(fwCmd, argv[5]);

      // Append destination ip/network to the firewall command
      strcat(fwCmd, " -d ");
      strcat(fwCmd, argv[6]);
      
      // Append target (job) to the firewall command
      strcat(fwCmd, " -j ");
      strcat(fwCmd, argv[7]);

      // Execute firewall command
      system(fwCmd);
   }
   else
      // Print usage synopsis
      printf("\nUsage: forwarding <interface in> <interface out> <protocol> <port> <source> <destination> <target> [states]\n\n");

   return(0);
}
