<?php
	require_once "Database.class.php";
	require_once "User.class.php";
	
	class TempDatabase extends Database
	{		
		public function __construct()
		{
			$initSql = <<<END
			
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
			
			CREATE TRIGGER set_filter_rule_num AFTER INSERT ON firewall_filter_rules
			BEGIN
        		UPDATE firewall_filter_rules
        		SET rule_number =	CASE (SELECT count(*) FROM firewall_filter_rules WHERE chain_name = new.chain_name)
									WHEN 1 THEN 1
									ELSE    (
												SELECT max(rule_number)
												FROM firewall_filter_rules
												WHERE chain_name = new.chain_name
											) + 1
									END
				WHERE rowid = new.rowid;
			END;
END;

			$this->pdo = new PDO("sqlite::memory:");
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo->exec("PRAGMA foreign_keys = ON");
			$this->pdo->exec($initSql);
		}
	}
?>