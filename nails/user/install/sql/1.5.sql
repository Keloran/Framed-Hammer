ALTER TABLE `users` CHANGE COLUMN `cPassword` `cPassword` VARCHAR(65) NOT NULL;

ALTER TABLE `users` CHANGE COLUMN `cRegisterHash` `cRegisterHash` VARCHAR(65) NOT NULL;

ALTER TABLE `users` CHANGE COLUMN `cLoginHash` `cLoginHash` VARCHAR(65) NOT NULL;