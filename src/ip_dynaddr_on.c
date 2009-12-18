#include <stdio.h>
#include <unistd.h>

#define RUN_CMD "echo 1 > /proc/sys/net/ipv4/ip_dynaddr"

int main()
{
   setreuid(geteuid(), geteuid());
   system(RUN_CMD);

   return(0);
}
