CREATE TABLE feature_flags (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(190) NOT NULL UNIQUE,
  description TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE feature_flag_states (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  feature_flag_id BIGINT UNSIGNED NOT NULL,
  environment VARCHAR(50) NOT NULL,
  enabled TINYINT(1) NOT NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_flag_env (feature_flag_id, environment),
  CONSTRAINT fk_feature_flag_states_flag FOREIGN KEY (feature_flag_id)
    REFERENCES feature_flags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
