#include <stdio.h>
#include <unistd.h>
#include "config.h"

#define SHOW_DHCPD_PID_CMD "cat /var/run/dhcpd.pid"

int main()
{
   setreuid(geteuid(), geteuid());
   system(SHOW_DHCPD_PID_CMD);

   return(0);
}
