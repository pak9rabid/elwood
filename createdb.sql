/* Create tables */
DROP TABLE settings;

CREATE TABLE settings
(
	key VARCHAR (128) NOT NULL,
	value VARCHAR (128)
);

DROP TABLE webterm_history;

CREATE TABLE webterm_history
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	command VARCHAR (256) NOT NULL,
	user VARCHAR (32) NOT NULL,
	time TIMESTAMP NOT NULL
);

/* Initialize settings table */
INSERT INTO settings VALUES ('IS_INITIALIZED', 'false');
INSERT INTO settings VALUES ('LAN_ETH', 'eth1');
INSERT INTO settings VALUES ('LAN_WLAN', null);
INSERT INTO settings VALUES ('EXTIF', 'eth0');
INSERT INTO settings VALUES ('INTIF', 'br0');
INSERT INTO settings VALUES ('ELWOOD_WEBROOT', '/var/www');
INSERT INTO settings VALUES ('DHCPD_CONF', '/etc/dhcp3/dhcpd.conf');
INSERT INTO settings VALUES ('DHCPD_PID_PATH', '/var/run/dhcpd.pid');
INSERT INTO settings VALUES ('DHCLIENT_PID_PATH', '/var/run/dhclient.' || (SELECT value FROM settings WHERE key = 'EXTIF') || '.pid');
INSERT INTO settings VALUES ('DHCPCD_DIR', '/var/lib/dhcpc');
INSERT INTO settings VALUES ('INETD_DIR', '/etc/elwood/inet.d');
INSERT INTO settings VALUES ('HTTPD_DIR', '/etc/elwood/httpd');
INSERT INTO settings VALUES ('PROTOCOLS', '/etc/protocols');
INSERT INTO settings VALUES ('WOL', '/usr/bin/wol');
INSERT INTO settings VALUES ('FIREWALL_DIR', '/etc/elwood/firewall');
INSERT INTO settings VALUES ('ELWOOD_HISTORY', '/etc/elwood/history');
