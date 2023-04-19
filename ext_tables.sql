#
# Table which holds the "Pause" objects for the various "Probes"
#
CREATE TABLE tx_healthcheck_domain_model_probe_pause (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,

    class_name varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent(pid)
);