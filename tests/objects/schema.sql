
SET FOREIGN_KEY_CHECKS = 0;
    
DROP TABLE IF EXISTS t_data;
CREATE TABLE t_data (
  `data_id` int NOT NULL  auto_increment 
  ,`date` datetime DEFAULT NULL 
  ,`value` int DEFAULT NULL 
  ,`string` varchar(64) DEFAULT NULL 
  ,`text` text DEFAULT NULL 
  ,`enum` enum('red','black') DEFAULT 'red'
  ,`blob` blob DEFAULT NULL 
  ,`real` float DEFAULT NULL 
  ,`dictionary_id` int DEFAULT NULL 

  ,INDEX ix_t_data_dictionary_id(`dictionary_id`)
  
  ,CONSTRAINT c_t_data_data_id PRIMARY KEY(`data_id`)
  
  ,CONSTRAINT c_t_data_dictionary_id FOREIGN KEY(`dictionary_id`)
    REFERENCES t_dictionary(`dictionary_id`)

  ) ENGINE=InnoDB CHARSET cp1251;

  
DROP TABLE IF EXISTS t_dictionary;
CREATE TABLE t_dictionary (
  `dictionary_id` int NOT NULL  auto_increment 
  ,`text` varchar(64) DEFAULT NULL 

  
  ,CONSTRAINT c_t_dictionary_dictionary_id PRIMARY KEY(`dictionary_id`)
  

  ) ENGINE=InnoDB CHARSET cp1251;

  
DROP TABLE IF EXISTS t_link;
CREATE TABLE t_link (
  `link_id` int NOT NULL  auto_increment 
  ,`data_id` int NOT NULL 
  ,`dictionary_id` int NOT NULL 

  ,INDEX ix_t_link_data_id(`data_id`)
  ,INDEX ix_t_link_dictionary_id(`dictionary_id`)
  
  ,CONSTRAINT c_t_link_link_id PRIMARY KEY(`link_id`)
  
  ,CONSTRAINT c_t_link_data_id FOREIGN KEY(`data_id`)
    REFERENCES t_data(`data_id`)
  ,CONSTRAINT c_t_link_dictionary_id FOREIGN KEY(`dictionary_id`)
    REFERENCES t_dictionary(`dictionary_id`) ON DELETE SET NULL

  ) ENGINE=InnoDB CHARSET cp1251;

  
DROP TABLE IF EXISTS t_another_link;
CREATE TABLE t_another_link (
  `another_link_id` int NOT NULL  auto_increment 
  ,`owner_id` int NOT NULL 
  ,`child_id` int NOT NULL 

  ,INDEX ix_t_another_link_owner_id(`owner_id`)
  ,INDEX ix_t_another_link_child_id(`child_id`)
  
  ,CONSTRAINT c_t_another_link_another_link_id PRIMARY KEY(`another_link_id`)
  
  ,CONSTRAINT c_t_another_link_owner_id FOREIGN KEY(`owner_id`)
    REFERENCES t_data(`data_id`)
  ,CONSTRAINT c_t_another_link_child_id FOREIGN KEY(`child_id`)
    REFERENCES t_dictionary(`dictionary_id`) ON DELETE CASCADE

  ) ENGINE=InnoDB CHARSET cp1251;

  
SET FOREIGN_KEY_CHECKS = 1;
  