CREATE TABLE feature_flags (
  id int(11) unsigned NOT NULL auto_increment,
  `key` varchar(190) NOT NULL,
  description text,
  created_at datetime DEFAULT NULL,
  updated_at datetime DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_key (`key`)
);

CREATE TABLE feature_flag_states (
  id int(11) unsigned NOT NULL auto_increment,
  feature_flag_id int(11) unsigned NOT NULL,
  environment varchar(50) NOT NULL,
  enabled tinyint(1) NOT NULL,
  updated_at datetime DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_flag_env (feature_flag_id, environment),
  KEY idx_flag (feature_flag_id)
);
