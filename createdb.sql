/* Create tables */
CREATE TABLE settings
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	key VARCHAR(32) UNIQUE NOT NULL,
	value VARCHAR (128)
);

CREATE TABLE users
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	uid INTEGER UNIQUE NOT NULL,
	username VARCHAR(32) UNIQUE NOT NULL,
	passwd VARCHAR(40)  NOT NULL
);

CREATE TABLE groups
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	gid INTEGER UNIQUE NOT NULL,
	name VARCHAR(32) UNIQUE NOT NULL
);

CREATE TABLE user_groups
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	uid INTEGER UNIQUE NOT NULL,
	gid INTEGER NOT NULL,
	FOREIGN KEY (uid) REFERENCES users(uid),
	FOREIGN KEY (gid) REFERENCES groups(gid)
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
	policy VARCHAR(32),
	CONSTRAINT u_table_chain UNIQUE (table_name, chain_name)
);

CREATE TABLE firewall_filter_rules
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
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
	target VARCHAR(16),
	CONSTRAINT u_chain_rulenum UNIQUE (chain_name, rule_number)
);

CREATE TABLE firewall_dnat_rules
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	in_port VARCHAR(16) UNIQUE NOT NULL,
	out_address VARCHAR(64) NOT NULL,
	out_port VARCHAR(16),
	CONSTRAINT u_forward_rule UNIQUE (in_port, out_address, out_port)
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
INSERT INTO settings VALUES (null, 'SYSTEM_PROFILE', 'Debian4');
INSERT INTO settings VALUES (null, 'IS_INITIALIZED', 'false');
INSERT INTO settings VALUES (null, 'LAN_ETH', 'eth1');
INSERT INTO settings VALUES (null, 'LAN_WLAN', null);
INSERT INTO settings VALUES (null, 'EXTIF', 'eth0');
INSERT INTO settings VALUES (null, 'INTIF', 'br0');
INSERT INTO settings VALUES (null, 'ELWOOD_WEBROOT', '/var/www');
INSERT INTO settings VALUES (null, 'DHCPD_CONF', '/etc/dhcp3/dhcpd.conf');
INSERT INTO settings VALUES (null, 'DHCPD_PID_PATH', '/var/run/dhcpd.pid');
INSERT INTO settings VALUES (null, 'DHCLIENT_PID_PATH', '/var/run/dhclient.' || (SELECT value FROM settings WHERE key = 'EXTIF') || '.pid');
INSERT INTO settings VALUES (null, 'DHCPCD_DIR', '/var/lib/dhcpc');
INSERT INTO settings VALUES (null, 'INETD_DIR', '/etc/elwood/inet.d');
INSERT INTO settings VALUES (null, 'HTTPD_DIR', '/etc/elwood/httpd');
INSERT INTO settings VALUES (null, 'PROTOCOLS', '/etc/protocols');
INSERT INTO settings VALUES (null, 'WOL', '/usr/bin/wol');
INSERT INTO settings VALUES (null, 'FIREWALL_DIR', '/etc/elwood/firewall');
INSERT INTO settings VALUES (null, 'ENABLE_IPMASQUERADE', 'true');

/* Initialize users and groups */
INSERT INTO users VALUES (null, 0, 'admin', '87a40f51477eb2699f8694e521b75405320cab21');
INSERT INTO groups VALUES (null, 0, 'admins');
INSERT INTO groups VALUES (null, 1, 'users');
INSERT INTO user_groups VALUES (null, 0, 0);
