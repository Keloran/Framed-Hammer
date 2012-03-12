CREATE  TABLE IF NOT EXISTS `news_comments` (
  `iCommentID` INT NOT NULL AUTO_INCREMENT,
  `iNewsID` INT NOT NULL,
  `cComment` TEXT NULL DEFAULT NULL,
  `tsDate` INT NULL DEFAULT NULL,
  `iUserID` INT NULL DEFAULT NULL,
  PRIMARY KEY (`iCommentID`),
  INDEX `fk_news_comments_news` (`iNewsID` ASC),
  INDEX (`iNewsID`),
  INDEX (`iUserID`)
) ENGINE = InnoDB;

