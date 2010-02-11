#                                               -*- Autoconf -*-
# Process this file with autoconf to produce a configure script.
PACKAGE = elwood
VERSION = 2.0

AC_PREREQ(2.61)
AC_INIT($PACKAGE, $VERSION, [pak9rabid@yahoo.com])
AM_INIT_AUTOMAKE($PACKAGE, $VERSION)

# Checks for programs.
AC_PROG_CXX
AC_PROG_CC

AC_CHECK_PROG(SQLITE, sqlite, yes, no)
if test "x$SQLITE" = "xno"; then
   echo "Error: sqlite is required" && exit
fi

AC_CHECK_PROG(WAKEONLAN, wakeonlan, yes, no)
if test "x$WAKEONLAN" = "xno"; then
   echo "Error: wakeonlan is required" && exit
fi

AC_CHECK_PROG(SUDO, sudo, yes, no)
if test "x$SUDO" = "xno"; then
   echo "Error: sudo is required" && exit
fi

AC_CHECK_PROG(IPTABLES, iptables, yes, no)
if test "x$IPTABLES" = "xno"; then
   echo "Error: iptables is required" && exit
fi

# Checks for libraries.

# Checks for header files.
AC_HEADER_STDC
AC_CHECK_HEADERS([stdlib.h string.h unistd.h])

# Checks for typedefs, structures, and compiler characteristics.
AC_HEADER_STDBOOL
AC_C_CONST

# Checks for library functions.

AC_CONFIG_FILES([Makefile
                 firewall/Makefile
                 httpd/Makefile
                 inet.d/Makefile
                 src/Makefile
                 sudo/Makefile
                 web/Makefile
                 web/inc/Makefile
                 web/scripts/Makefile
                 web/scripts/admin/Makefile])
AC_OUTPUT
