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
   
   if (argc > 7)
   {
      // Construct base command
      char fwCmd[128] = IPTABLES;
      strcat(fwCmd, " -A ");
      strcat(fwCmd, argv[1]);
      strcat(fwCmd, " -p ");
      strcat(fwCmd, argv[2]);

      if ( strcmp(argv[2], "tcp") == 0 || strcmp(argv[2], "udp") == 0 || strcmp(argv[2], "6") == 0 || strcmp(argv[2], "17") == 0 )
      {
         strcat(fwCmd, " --dport ");
         strcat(fwCmd, argv[3]);
      }

      strcat(fwCmd, " -j ");
      strcat(fwCmd, argv[4]);
      strcat(fwCmd, " -s ");
      strcat(fwCmd, argv[5]);
      strcat(fwCmd, " -d ");
      strcat(fwCmd, argv[6]);
      strcat(fwCmd, " -m state --state ");
      strcat(fwCmd, argv[7]);

      // If entered, specify which interface to apply rule to
      if (argc > 8)
      {
         if ( strcmp(argv[1], "INPUT") == 0 )
            strcat(fwCmd, " -i ");
         else
            strcat(fwCmd, " -o ");

         strcat(fwCmd, argv[8]);
      }

      system(fwCmd);
   }
   else
      // Print usage synopsis
      printf("\nUsage: setfirewall <chain> <protocol> <port> <job> <source> <destination> <ip states>[<interface>]\n\n");

   return 0;
}
      
