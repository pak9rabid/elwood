/* Create tables */
CREATE TABLE settings
(
	key VARCHAR (128) NOT NULL,
	value VARCHAR (128)
);

CREATE TABLE webterm_history
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	command VARCHAR (256) NOT NULL,
	user VARCHAR (32) NOT NULL,
	time TIMESTAMP NOT NULL
);

CREATE TABLE firewall_filter_chains
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	chain VARCHAR(32) NOT NULL,
	policy VARCHAR(32)
);

CREATE TABLE firewall_filter_general
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	operation VARCHAR (1) NOT NULL,
	chain VARCHAR(32) NOT NULL,
	options VARCHAR(512) NOT NULL
);

CREATE TABLE firewall_filter_input
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	operation VARCHAR (1) NOT NULL,
	options VARCHAR(512) NOT NULL
);

CREATE TABLE firewall_filter_forward_in
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	operation VARCHAR (1) NOT NULL,
	options VARCHAR(512) NOT NULL
);

CREATE TABLE firewall_filter_forward_out
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	operation VARCHAR (1) NOT NULL,
	options VARCHAR(512) NOT NULL
);

CREATE TABLE firewall_nat_chains
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	chain VARCHAR(32) NOT NULL,
	policy VARCHAR(32)
);


CREATE TABLE firewall_nat_general
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	operation VARCHAR (1) NOT NULL,
	chain VARCHAR(32) NOT NULL,
	options VARCHAR(512) NOT NULL
);

CREATE TABLE firewall_nat_prerouting
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	operation VARCHAR (1) NOT NULL,
	options VARCHAR(512) NOT NULL
);

CREATE TABLE firewall_nat_postrouting
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	operation VARCHAR (1) NOT NULL,
	options VARCHAR(512) NOT NULL
);

/* Create triggers */
CREATE TRIGGER webterm_history_limiter AFTER INSERT ON webterm_history
BEGIN
	DELETE FROM webterm_history WHERE id IN
		(SELECT id FROM webterm_history WHERE user =
			(SELECT user from webterm_history WHERE rowid = last_insert_rowid())
		ORDER BY time DESC LIMIT 50 OFFSET 50);
END;

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

/* Initialize firewall tables */
INSERT INTO firewall_filter_chains VALUES (null, 'INPUT', 'DROP');
INSERT INTO firewall_filter_chains VALUES (null, 'FORWARD', 'DROP');
INSERT INTO firewall_filter_chains VALUES (null, 'OUTPUT', 'ACCEPT');
INSERT INTO firewall_filter_chains VALUES (null, 'forward_in', null);
INSERT INTO firewall_filter_chains VALUES (null, 'forward_out', null);
INSERT INTO firewall_filter_general VALUES (null, 'A', 'FORWARD', '-i ' || (SELECT value FROM settings WHERE key = 'EXTIF') || ' -j forward_in');
INSERT INTO firewall_filter_general VALUES (null, 'A', 'FORWARD', '-i ' || (SELECT value FROM settings WHERE key = 'INTIF') || ' -j forward_out');
INSERT INTO firewall_filter_input VALUES (null, 'A', '-i ' || (SELECT value FROM settings WHERE key = 'INTIF') || ' -p tcp --dport 80 -j ACCEPT');
INSERT INTO firewall_filter_input VALUES (null, 'A', '-i ' || (SELECT value FROM settings WHERE key = 'INTIF') || ' -p tcp --dport 22 -j ACCEPT');
INSERT INTO firewall_filter_forward_in VALUES (null, 'A', '-m state --state RELATED,ESTABLISHED -j ACCEPT');
INSERT INTO firewall_filter_forward_out VALUES (null, 'A', '-j ACCEPT');
INSERT INTO firewall_nat_chains VALUES (null, 'PREROUTING' , 'ACCEPT');
INSERT INTO firewall_nat_chains VALUES (null, 'POSTROUTING', 'ACCEPT');
INSERT INTO firewall_nat_chains VALUES (null, 'OUTPUT', 'ACCEPT');
INSERT INTO firewall_nat_postrouting VALUES (null, 'A', '-o ' || (SELECT value FROM settings WHERE key = 'EXTIF') || ' -j MASQUERADE');
