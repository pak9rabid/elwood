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

CREATE TABLE firewall_chains
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	table_name VARCHAR(32) NOT NULL,
	chain_name VARCHAR(32) NOT NULL,
	policy VARCHAR(32)
);

CREATE TABLE firewall_filter_rules
(
	id VARCHAR(13) PRIMARY KEY UNIQUE NOT NULL,
	chain_name VARCHAR(32) NOT NULL,
	rule_number INTEGER NOT NULL,
	src_addr VARCHAR(64),
	dst_addr VARCHAR(64),
	state VARCHAR(32),
	fragmented VARCHAR(1),
	in_interface VARCHAR(16),
	out_interface VARCHAR(16),
	protocol VARCHAR(16),
	dport VARCHAR(16),
	sport VARCHAR(16),
 	icmp_type VARCHAR(16),
	target VARCHAR(16)
);

CREATE TABLE firewall_dnat_rules
(
	in_port VARCHAR(16) UNIQUE NOT NULL,
	out_address VARCHAR(64) NOT NULL,
	out_port VARCHAR(16)
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
INSERT INTO settings VALUES ('ENABLE_IPMASQUERADE', 'true');
