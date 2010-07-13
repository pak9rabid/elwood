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
	username VARCHAR(32) UNIQUE NOT NULL,
	usergroup VARCHAR(32) NOT NULL,
	passwd VARCHAR(40) NOT NULL,

	CHECK (usergroup IN ('admins', 'users'))
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
	rule_number INTEGER,
	src_addr VARCHAR(64),
	dst_addr VARCHAR(64),
	state VARCHAR(32),
	fragmented VARCHAR(1),
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

CREATE TRIGGER set_filter_rule_num AFTER INSERT ON firewall_filter_rules
BEGIN
	UPDATE firewall_filter_rules
	SET rule_number =	CASE (SELECT count(*) FROM firewall_filter_rules WHERE chain_name = new.chain_name)
				WHEN 1 THEN 1
				ELSE	(
						SELECT max(rule_number)
						FROM firewall_filter_rules
						WHERE chain_name = new.chain_name
					) + 1
				END
	WHERE rowid = new.rowid;
END;

/* Initialize settings table */
INSERT INTO settings VALUES (null, 'SYSTEM_PROFILE', 'debian4');
INSERT INTO settings VALUES (null, 'IS_INITIALIZED', 0);
INSERT INTO settings VALUES (null, 'LAN_ETH', 'eth1');
INSERT INTO settings VALUES (null, 'LAN_WLAN', null);
INSERT INTO settings VALUES (null, 'EXTIF', 'eth0');
INSERT INTO settings VALUES (null, 'INTIF', 'br0');
INSERT INTO settings VALUES (null, 'HTTP_PORT', '80');
INSERT INTO settings VALUES (null, 'SSH_PORT', '22');
INSERT INTO settings VALUES (null, 'LAN_HTTP_ENABLED', 1);
INSERT INTO settings VALUES (null, 'WAN_HTTP_ENABLED', 1);
INSERT INTO settings VALUES (null, 'LAN_SSH_ENABLED', 1);
INSERT INTO settings VALUES (null, 'WAN_SSH_ENABLED', 1);
INSERT INTO settings VALUES (null, 'LAN_ICMP_ENABLED', 1);
INSERT INTO settings VALUES (null, 'WAN_ICMP_ENABLED', 1);
INSERT INTO settings VALUES (null, 'ELWOOD_CFG_DIR', '/etc/elwood');
INSERT INTO settings VALUES (null, 'ELWOOD_WEBROOT', '/var/www');
INSERT INTO settings VALUES (null, 'DHCPD_CONF', '/etc/dhcp3/dhcpd.conf');
INSERT INTO settings VALUES (null, 'DHCPD_PID_PATH', '/var/run/dhcpd.pid');
INSERT INTO settings VALUES (null, 'DHCLIENT_PID_PATH', '/var/run/dhclient.' || (SELECT value FROM settings WHERE key = 'EXTIF') || '.pid');
INSERT INTO settings VALUES (null, 'DHCPCD_DIR', '/var/lib/dhcpc');
INSERT INTO settings VALUES (null, 'PROTOCOLS', '/etc/protocols');
INSERT INTO settings VALUES (null, 'WOL', '/usr/bin/wol');
INSERT INTO settings VALUES (null, 'ENABLE_IPMASQUERADE', 'true');

/* Initialize users */
INSERT INTO users VALUES (null, 'admin', 'admins', 'da942a52feff28ee63725f388318641d67a4dbe4');
