ALTER TABLE  `ipv4_mac` CHANGE  `interface_id`  `port_id` INT( 11 ) NOT NULL;
ALTER TABLE  `juniAtmVp` CHANGE  `interface_id`  `port_id` INT( 11 ) NOT NULL;
ALTER TABLE  `ospf_nbrs` CHANGE  `interface_id`  `port_id` INT( 11 ) NOT NULL;
ALTER TABLE  `mac_accounting` CHANGE  `interface_id`  `port_id` INT( 11 ) NOT NULL;
ALTER TABLE  `ospf_ports` CHANGE  `interface_id`  `port_id` INT( 11 ) NOT NULL;
ALTER TABLE  `ports_adsl` CHANGE  `interface_id`  `port_id` INT( 11 ) NOT NULL;
ALTER TABLE  `ports_perms` CHANGE  `interface_id`  `port_id` INT( 11 ) NOT NULL;
ALTER TABLE  `ports_stack` CHANGE  `interface_id_high`  `port_id_high` INT( 11 ) NOT NULL;
ALTER TABLE  `ports_stack` CHANGE  `interface_id_low`  `port_id_low` INT( 11 ) NOT NULL;
ALTER TABLE  `ports_vlans` CHANGE  `interface_id`  `port_id` INT( 11 ) NOT NULL;
ALTER TABLE  `pseudowires` CHANGE  `interface_id`  `port_id` INT( 11 ) NOT NULL;