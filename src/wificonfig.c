#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include "config.h"

int main(int argc, char** argv)
{
   // Set uid
   setreuid(geteuid(), geteuid());

   // If minimum args are not met,
   // don't construct or execute ifconfig command
   if (argc > 4)
   {
      // Construct ifconfig command to configure
      // specified interface
      char ifconfigCmd[128] = IFCONFIG;
      strcat(ifconfigCmd, " ");
      strcat(ifconfigCmd, argv[1]);
      strcat(ifconfigCmd, " ");
      strcat(ifconfigCmd, argv[2]);
      strcat(ifconfigCmd, " netmask ");
      strcat(ifconfigCmd, argv[3]);
      strcat(ifconfigCmd, " broadcast ");
      strcat(ifconfigCmd, argv[4]);

      // Append MTU if it's specified
      if (argc > 5)
      {
         strcat(ifconfigCmd, " mtu ");
	 strcat(ifconfigCmd, argv[5]);
      }

      // Execute command
      //system(ifconfigCmd);
      printf("\n\nCommand: %s\n\n", ifconfigCmd);
   }
   else
      // Print usage synopsis
      printf("\nUsage: intconfig <interface> <ip> <netmask> <broadcast> [mtu]\n\n");

   return 0;
}
