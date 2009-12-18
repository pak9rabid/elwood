#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include "config.h"

int main(int argc, char** argv)
{
   // Set uid
   setreuid(geteuid(), geteuid());

   // If minimum args are not met,
   // don't construct or execute dhcpd command
   if ( (argc > 1) &&
	(strcmp(argv[1], "start") == 0 ||
	 strcmp(argv[1], "stop") == 0 ||
	 strcmp(argv[1], "restart") == 0))
   {
      // Construct command to start/stop/restart dhcp server
      char dhcpdCmd[128] = "/etc/init.d/dhcp ";

      strcat(dhcpdCmd, argv[1]);

      // Execute command
      system(dhcpdCmd);
   }
   else
      // Print usage synopsis
      printf("\nUsage: dhcpdctrl <start|stop|restart>\n\n");

   return 0;
}
