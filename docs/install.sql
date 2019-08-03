CREATE TABLE IF NOT EXISTS `d3totp` (
    `OXID` CHAR(32) NOT NULL ,
    `OXUSERID` CHAR(32) NOT NULL ,
    `USETOTP` TINYINT(1) NOT NULL  DEFAULT 0,
    `SEED` VARCHAR(256) NOT NULL ,
    `OXTIMESTAMP` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp', 
    PRIMARY KEY (`OXID`) , 
    UNIQUE KEY  `OXUSERID` (`OXUSERID`) 
)  
ENGINE=InnoDB 
COMMENT='totp setting';

CREATE TABLE IF NOT EXISTS `d3totp_backupcodes` (
    `OXID` CHAR(32) NOT NULL ,
    `OXUSERID` CHAR(32) NOT NULL  COMMENT 'user id',
    `BACKUPCODE` VARCHAR(64) NOT NULL  COMMENT 'BackupCode',
    `OXTIMESTAMP` TIMESTAMP   NOT NULL  DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp', 
    PRIMARY KEY (`OXID`) ,  
    KEY  `OXUSERID` (`OXUSERID`) ,  
    KEY  `BACKUPCODE` (`BACKUPCODE`) 
)  
ENGINE=InnoDB
COMMENT='totp backup codes';

INSERT INTO `oxseo` (`OXOBJECTID`, `OXIDENT`, `OXSHOPID`, `OXLANG`, `OXSTDURL`, `OXSEOURL`, `OXTYPE`, `OXFIXED`, `OXEXPIRED`, `OXPARAMS`, `OXTIMESTAMP`) VALUES
('39f744f17e974988e515558698a29df4', '76282e134ad4e40a3578e121a6cb1f6a', 1, 1, 'index.php?cl=d3_account_totp', 'en/2-factor-authintication/', 'static', 0, 0, '', NOW()),
('39f744f17e974988e515558698a29df4', 'c1f8b5506e2b5d6ac184dcc5ebdfb591', 1, 0, 'index.php?cl=d3_account_totp', '2-faktor-authentisierung/', 'static', 0, 0, '', NOW());
