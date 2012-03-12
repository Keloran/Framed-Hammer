ALTER TABLE twitter_details ADD INDEX (`iUserID`);
ALTER TABLE twitter_tweets ADD INDEX (`iUserID`), ADD INDEX (`iTweetID`);