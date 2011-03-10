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

CREATE TABLE services
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	service_name VARCHAR(32) NOT NULL,
	is_enabled VARCHAR(1) NOT NULL,

	CHECK (is_enabled IN ('Y', 'N'))
);

CREATE TABLE firewall_rules
(
	id INTEGER PRIMARY KEY UNIQUE NOT NULL,
	service_id INTEGER,
	table_name VARCHAR(32) NOT NULL,
	chain_name VARCHAR(32) NOT NULL,
	rule_number INTEGER,
	int_in VARCHAR(16),
	int_out VARCHAR(16),
	src_addr VARCHAR(64),
	dst_addr VARCHAR(64),
	state VARCHAR(32),
	fragmented VARCHAR(1),
	protocol VARCHAR(16),
	dport VARCHAR(16),
	sport VARCHAR(16),
 	icmp_type VARCHAR(16),
	target VARCHAR(16),

	CONSTRAINT u_chain_rulenum UNIQUE (table_name, chain_name, rule_number),
	CHECK (table_name IN ('filter', 'nat', 'mangle')),
	CHECK (fragmented IN ('Y', 'N')),
	FOREIGN KEY(service_id) REFERENCES services(id) ON DELETE CASCADE ON UPDATE CASCADE
);

/* Create triggers */
CREATE TRIGGER set_firewall_rule_num AFTER INSERT ON firewall_rules
BEGIN
	UPDATE firewall_rules
	SET rule_number =	CASE (SELECT count(*) FROM firewall_rules WHERE table_name = new.table_name AND chain_name = new.chain_name)
				WHEN 1 THEN 1
				ELSE	(
						SELECT max(rule_number)
						FROM firewall_rules
						WHERE table_name = new.table_name AND chain_name = new.chain_name
					) + 1
				END
	WHERE rowid = new.rowid;
END;

CREATE TRIGGER renumber_firewall_rules AFTER DELETE ON firewall_rules
BEGIN
	UPDATE firewall_rules
	SET rule_number = rule_number - 1
	WHERE table_name = old.table_name AND chain_name = old.chain_name AND rule_number > old.rule_number;
END;

/* Initialize settings table */
INSERT INTO settings VALUES (null, 'SYSTEM_PROFILE', 'debian4');
INSERT INTO settings VALUES (null, 'IS_INITIALIZED', 0);
INSERT INTO settings VALUES (null, 'LAN_ETH', 'eth1');
INSERT INTO settings VALUES (null, 'LAN_WLAN', 'wlan0');
INSERT INTO settings VALUES (null, 'EXTIF', 'eth0');
INSERT INTO settings VALUES (null, 'INTIF', 'br0');
INSERT INTO settings VALUES (null, 'ELWOOD_CFG_DIR', '/etc/elwood');
INSERT INTO settings VALUES (null, 'ELWOOD_WEBROOT', '/var/www');
INSERT INTO settings VALUES (null, 'ENABLE_IPMASQUERADE', 'true');
INSERT INTO settings VALUES (null, 'ENABLE_IPMASQUERADE_CUSTOM', 'false');

/* Initialize services table */
INSERT INTO services VALUES (null, "http", "Y");
INSERT INTO services VALUES (null, "ssh", "Y");
INSERT INTO services VALUES (null, "dhcp", "Y");
INSERT INTO services VALUES (null, "wlan", "Y");
INSERT INTO services VALUES (null, "network", "Y");
INSERT INTO services VALUES (null, "icmp", "Y");

/* Initialize firewall_rules table */

/* filter table */
INSERT INTO firewall_rules (table_name, chain_name, state, target) VALUES ('filter', 'INPUT', 'ESTABLISHED,RELATED', 'ACCEPT');
INSERT INTO firewall_rules (service_id, table_name, chain_name, protocol, dport, target) VALUES ((SELECT id FROM services WHERE service_name = 'http'), 'filter', 'INPUT', 'tcp', 80, 'ACCEPT');
INSERT INTO firewall_rules (service_id, table_name, chain_name, protocol, dport, target) VALUES ((SELECT id FROM services WHERE service_name = 'ssh'), 'filter', 'INPUT', 'tcp', 22, 'ACCEPT');
INSERT INTO firewall_rules (service_id, table_name, chain_name, protocol, target) VALUES ((SELECT id FROM services WHERE service_name = 'icmp'), 'filter', 'INPUT', 'icmp', 'ACCEPT');
INSERT INTO firewall_rules (service_id, table_name, chain_name, protocol, dport, target) VALUES ((SELECT id FROM services WHERE service_name = 'dhcp'), 'filter', 'INPUT', 'udp', 67, 'ACCEPT');
INSERT INTO firewall_rules (table_name, chain_name, int_in, target) VALUES ('filter', 'FORWARD', (SELECT value FROM settings WHERE key = 'EXTIF'), 'forward_in');
INSERT INTO firewall_rules (table_name, chain_name, int_in, target) VALUES ('filter', 'FORWARD', (SELECT value FROM settings WHERE key = 'INTIF'), 'forward_out');
INSERT INTO firewall_rules (table_name, chain_name, state, target) VALUES ('filter', 'forward_in', 'ESTABLISHED,RELATED', 'ACCEPT');
INSERT INTO firewall_rules (table_name, chain_name, target) VALUES ('filter', 'forward_out', 'ACCEPT');

/* nat table */
INSERT INTO firewall_rules (table_name, chain_name, int_in, target) VALUES ('nat', 'PREROUTING', (SELECT value FROM settings WHERE key = 'EXTIF'), 'one2one_in');
INSERT INTO firewall_rules (table_name, chain_name, int_in, target) VALUES ('nat', 'PREROUTING', (SELECT value FROM settings WHERE key = 'EXTIF'), 'port_forward');
INSERT INTO firewall_rules (table_name, chain_name, int_out, target) VALUES ('nat', 'POSTROUTING', (SELECT value FROM settings WHERE key = 'EXTIF'), 'one2one_out');
INSERT INTO firewall_rules (table_name, chain_name, int_out, target) VALUES ('nat', 'POSTROUTING', (SELECT value FROM settings WHERE key = 'EXTIF'), 'ip_masquerade');
INSERT INTO firewall_rules (table_name, chain_name, target) VALUES ('nat', 'ip_masquerade', 'MASQUERADE');


/* Initialize users */
INSERT INTO users VALUES (null, 'admin', 'admins', 'da942a52feff28ee63725f388318641d67a4dbe4');
