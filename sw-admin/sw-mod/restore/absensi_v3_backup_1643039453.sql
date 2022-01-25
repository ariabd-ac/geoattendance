

 USE absensi_v3;



 DROP TABLE IF EXISTS  building;



CREATE TABLE `building` (
  `building_id` int(8) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `address` varchar(100) NOT NULL,
  `building_scanner` varchar(50) NOT NULL,
  PRIMARY KEY (`building_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

INSERT INTO building VALUES("1","SWUKZ/2021","S-widodo.com","Jl. Zainal Abidin Labuhan ratu gg. harapn no. 18 Bandar Lampung","");



 DROP TABLE IF EXISTS  cuty;



CREATE TABLE `cuty` (
  `cuty_id` int(11) NOT NULL AUTO_INCREMENT,
  `employees_id` int(11) NOT NULL,
  `cuty_start` date NOT NULL,
  `cuty_end` date NOT NULL,
  `date_work` date NOT NULL,
  `cuty_total` int(5) NOT NULL,
  `cuty_description` varchar(100) NOT NULL,
  `cuty_status` int(2) NOT NULL,
  PRIMARY KEY (`cuty_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;




 DROP TABLE IF EXISTS  employees;



CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employees_code` varchar(20) NOT NULL,
  `employees_email` varchar(30) NOT NULL,
  `employees_password` varchar(100) NOT NULL,
  `employees_name` varchar(50) NOT NULL,
  `position_id` int(5) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `building_id` int(11) NOT NULL,
  `photo` varchar(100) NOT NULL,
  `created_login` datetime NOT NULL,
  `created_cookies` varchar(70) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;

INSERT INTO employees VALUES("6","OM001-2021","swidodo.com@gmail.com","d060522d419e32b1f5929878c5949c09b2acf30f754954d77644957774f96836","Widodo","2","1","1","OM001-2021-1a9d0a42736063ec60e8833614f44a6d-142948-.jpg","2021-08-10 22:42:02","4c6c453e7a58b5fc11908a3916f944e1");
INSERT INTO employees VALUES("14","123456789","intan@gmail.com","acd2bcf0a751e78ba7a1904d55cb26b00b7b5c21ea1c7a91b373c2cf44ae0b29","Intan","1","1","1","","2021-08-06 00:00:00","6baf05e5de14becf64ed2a919923babc");



 DROP TABLE IF EXISTS  position;



CREATE TABLE `position` (
  `position_id` int(5) NOT NULL AUTO_INCREMENT,
  `position_name` varchar(30) NOT NULL,
  PRIMARY KEY (`position_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;




 DROP TABLE IF EXISTS  presence;



CREATE TABLE `presence` (
  `presence_id` int(11) NOT NULL AUTO_INCREMENT,
  `employees_id` int(11) NOT NULL,
  `presence_date` date NOT NULL,
  `time_in` time NOT NULL,
  `time_out` time NOT NULL,
  `picture_in` text NOT NULL,
  `picture_out` varchar(150) NOT NULL,
  `present_id` int(11) NOT NULL COMMENT 'Masuk,Pulang,Tidak Hadir',
  `latitude_longtitude_in` varchar(100) NOT NULL,
  `latitude_longtitude_out` varchar(100) NOT NULL,
  `information` varchar(50) NOT NULL,
  PRIMARY KEY (`presence_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

INSERT INTO presence VALUES("1","6","2021-08-10","21:48:19","22:45:54","2021-08-10-in-1628606899-6.jpeg","2021-08-10-out-1628610354-6.jpeg","1","-4.5585849,105.40680789999999","-4.5585849,105.40680789999999","");
INSERT INTO presence VALUES("2","6","2021-08-11","00:19:18","00:22:11","2021-08-11-in-1628615958-6.jpeg","2021-08-11-out-1628616131-6.jpeg","1","-4.5585849,105.40680789999999","-4.5585849,105.40680789999999","");



 DROP TABLE IF EXISTS  present_status;



CREATE TABLE `present_status` (
  `present_id` int(6) NOT NULL AUTO_INCREMENT,
  `present_name` varchar(15) NOT NULL,
  PRIMARY KEY (`present_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

INSERT INTO present_status VALUES("1","Hadir");
INSERT INTO present_status VALUES("2","Sakit");
INSERT INTO present_status VALUES("3","Izin");



 DROP TABLE IF EXISTS  shift;



CREATE TABLE `shift` (
  `shift_id` int(11) NOT NULL AUTO_INCREMENT,
  `shift_name` varchar(20) NOT NULL,
  `time_in` time NOT NULL,
  `time_out` time NOT NULL,
  PRIMARY KEY (`shift_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

INSERT INTO shift VALUES("1","FULL TIME","07:30:00","17:00:00");



 DROP TABLE IF EXISTS  sw_site;



CREATE TABLE `sw_site` (
  `site_id` int(4) NOT NULL AUTO_INCREMENT,
  `site_url` varchar(100) NOT NULL,
  `site_name` varchar(50) NOT NULL,
  `site_company` varchar(30) NOT NULL,
  `site_manager` varchar(30) NOT NULL,
  `site_director` varchar(30) NOT NULL,
  `site_phone` char(12) NOT NULL,
  `site_address` text NOT NULL,
  `site_description` text NOT NULL,
  `site_logo` varchar(50) NOT NULL,
  `site_email` varchar(30) NOT NULL,
  `site_email_domain` varchar(50) NOT NULL,
  PRIMARY KEY (`site_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

INSERT INTO sw_site VALUES("1","http://localhost/product/absensi.v3","Absensi v.3","S widodo.com","Intan Permata Sari","S. Widodo","089666665781","Jl. Zainal Bidin Labuhan Ratu gg. Harapan 1 No 18","Absensi v.3","whiteswlogowebpng.png","swidodo.com@gmail.com","info@domain.com");



 DROP TABLE IF EXISTS  user;



CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(40) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `fullname` varchar(40) NOT NULL,
  `registered` datetime NOT NULL,
  `created_login` datetime NOT NULL,
  `last_login` datetime NOT NULL,
  `session` varchar(100) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `browser` varchar(30) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

INSERT INTO user VALUES("1","Widodo","swidodo.com@gmail.com","88222999e01f1910a5ac39fa37d3b8b704973d503d0ff7c84d46305b92cfa0f6","Widodo","2021-02-03 10:22:00","2022-01-24 19:58:49","2021-08-11 09:45:08","-","1","Google Crome","1");



 DROP TABLE IF EXISTS  user_level;



CREATE TABLE `user_level` (
  `level_id` int(4) NOT NULL AUTO_INCREMENT,
  `level_name` varchar(20) NOT NULL,
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

INSERT INTO user_level VALUES("1","Administrator");
INSERT INTO user_level VALUES("2","Operator");

