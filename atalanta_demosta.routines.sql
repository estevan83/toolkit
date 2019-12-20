DELIMITER $$
DROP PROCEDURE if exists atalanta_demosta.setsmownerid;
CREATE PROCEDURE atalanta_demosta.setsmownerid(pquotesid int) 
BEGIN

DECLARE nomegruppo VARCHAR(255);
DECLARE idgruppo INT DEFAULT (SELECT MAX(groupid)+1 FROM vtiger_groups);
DECLARE agent1 INT DEFAULT (SELECT agent1 
									FROM vtiger_quotes
									WHERE quoteid=pquotesid);
DECLARE agent2 INT DEFAULT (SELECT agent2 
									FROM vtiger_quotes
									WHERE quoteid=pquotesid);
DECLARE nome1 VARCHAR(255) DEFAULT(SELECT first_name FROM vtiger_users WHERE id=agent1);
DECLARE nome2 VARCHAR(255) DEFAULT(SELECT first_name FROM vtiger_users WHERE id=agent2);
DECLARE cognome1 VARCHAR(255) DEFAULT(SELECT last_name FROM vtiger_users WHERE id=agent1);
DECLARE cognome2 VARCHAR(255) DEFAULT(SELECT last_name FROM vtiger_users WHERE id=agent2);


if (agent1=agent2 AND agent1 IS NOT NULL) then 
	UPDATE vtiger_crmentity SET smownerid=agent1 WHERE crmid=pquotesid AND setype='Quotes';
ELSEIF (agent1 IS NULL AND agent2 IS NOT NULL) then
	UPDATE vtiger_crmentity SET smownerid=agent2 WHERE crmid=pquotesid AND setype='Quotes';
ELSEIF (agent2 IS NULL AND agent1 IS NOT NULL) then
	UPDATE vtiger_crmentity SET smownerid=agent1 WHERE crmid=pquotesid AND setype='Quotes';
ELSEIF (agent1 IS NULL AND agent2 IS NULL) then
	UPDATE vtiger_crmentity SET smownerid=1 WHERE crmid=pquotesid AND setype='Quotes';
ELSE
	if agent1>agent2 then
		SET @temp=nome1;
		SET nome1=nome2;
		SET nome2=@temp;
		SET @temp=cognome1;
		SET cognome1=cognome2;
		SET cognome2=@temp;
	END if;
	SET nomegruppo =CONCAT(SUBSTRING(nome1,1,1),UPPER(SUBSTRING(cognome1,1,2)),'-',SUBSTRING(nome2,1,1),UPPER(SUBSTRING(cognome2,1,2)));
	SET @temp=(SELECT groupid FROM vtiger_groups WHERE groupname=nomegruppo);
	if @temp IS NULL then 
		INSERT INTO vtiger_groups (groupid, groupname, description) VALUES (idgruppo, nomegruppo, CONCAT(nomegruppo,' ',NOW()));
		INSERT INTO vtiger_users2group (groupid, userid) VALUES (idgruppo,agent1);
		INSERT INTO vtiger_users2group (groupid, userid) VALUES (idgruppo,agent2);
		SET @temp=idgruppo;
	END if;
	UPDATE vtiger_crmentity SET smownerid = @temp WHERE crmid=pquotesid  AND setype='Quotes';
END if;
END$$

DELIMITER $$
DROP PROCEDURE if exists atalanta_demosta.setsmownerid;
CREATE PROCEDURE atalanta_demosta.setsmownerid(pquotesid int) 
BEGIN

DECLARE nomegruppo VARCHAR(255);
DECLARE idgruppo INT DEFAULT (SELECT MAX(groupid)+1 FROM vtiger_groups);
DECLARE agent1 INT DEFAULT (SELECT agent1 
									FROM vtiger_quotes
									WHERE quoteid=pquotesid);
DECLARE agent2 INT DEFAULT (SELECT agent2 
									FROM vtiger_quotes
									WHERE quoteid=pquotesid);
DECLARE nome1 VARCHAR(255) DEFAULT(SELECT first_name FROM vtiger_users WHERE id=agent1);
DECLARE nome2 VARCHAR(255) DEFAULT(SELECT first_name FROM vtiger_users WHERE id=agent2);
DECLARE cognome1 VARCHAR(255) DEFAULT(SELECT last_name FROM vtiger_users WHERE id=agent1);
DECLARE cognome2 VARCHAR(255) DEFAULT(SELECT last_name FROM vtiger_users WHERE id=agent2);


if (agent1=agent2 AND agent1 IS NOT NULL) then 
	UPDATE vtiger_crmentity SET smownerid=agent1 WHERE crmid=pquotesid AND setype='Quotes';
ELSEIF (agent1 IS NULL AND agent2 IS NOT NULL) then
	UPDATE vtiger_crmentity SET smownerid=agent2 WHERE crmid=pquotesid AND setype='Quotes';
ELSEIF (agent2 IS NULL AND agent1 IS NOT NULL) then
	UPDATE vtiger_crmentity SET smownerid=agent1 WHERE crmid=pquotesid AND setype='Quotes';
ELSEIF (agent1 IS NULL AND agent2 IS NULL) then
	UPDATE vtiger_crmentity SET smownerid=1 WHERE crmid=pquotesid AND setype='Quotes';
ELSE
	if agent1>agent2 then
		SET @temp=nome1;
		SET nome1=nome2;
		SET nome2=@temp;
		SET @temp=cognome1;
		SET cognome1=cognome2;
		SET cognome2=@temp;
	END if;
	SET nomegruppo =CONCAT(SUBSTRING(nome1,1,1),UPPER(SUBSTRING(cognome1,1,2)),'-',SUBSTRING(nome2,1,1),UPPER(SUBSTRING(cognome2,1,2)));
	SET @temp=(SELECT groupid FROM vtiger_groups WHERE groupname=nomegruppo);
	if @temp IS NULL then 
		INSERT INTO vtiger_groups (groupid, groupname, description) VALUES (idgruppo, nomegruppo, CONCAT(nomegruppo,' ',NOW()));
		INSERT INTO vtiger_users2group (groupid, userid) VALUES (idgruppo,agent1);
		INSERT INTO vtiger_users2group (groupid, userid) VALUES (idgruppo,agent2);
		SET @temp=idgruppo;
	END if;
	UPDATE vtiger_crmentity SET smownerid = @temp WHERE crmid=pquotesid  AND setype='Quotes';
END if;
END$$

DELIMITER $$
DROP PROCEDURE if exists atalanta_demosta.setsmownerid;
CREATE PROCEDURE atalanta_demosta.setsmownerid(pquotesid int) 
BEGIN

DECLARE nomegruppo VARCHAR(255);
DECLARE idgruppo INT DEFAULT (SELECT MAX(groupid)+1 FROM vtiger_groups);
DECLARE agent1 INT DEFAULT (SELECT agent1 
									FROM vtiger_quotes
									WHERE quoteid=pquotesid);
DECLARE agent2 INT DEFAULT (SELECT agent2 
									FROM vtiger_quotes
									WHERE quoteid=pquotesid);
DECLARE nome1 VARCHAR(255) DEFAULT(SELECT first_name FROM vtiger_users WHERE id=agent1);
DECLARE nome2 VARCHAR(255) DEFAULT(SELECT first_name FROM vtiger_users WHERE id=agent2);
DECLARE cognome1 VARCHAR(255) DEFAULT(SELECT last_name FROM vtiger_users WHERE id=agent1);
DECLARE cognome2 VARCHAR(255) DEFAULT(SELECT last_name FROM vtiger_users WHERE id=agent2);


if (agent1=agent2 AND agent1 IS NOT NULL) then 
	UPDATE vtiger_crmentity SET smownerid=agent1 WHERE crmid=pquotesid AND setype='Quotes';
ELSEIF (agent1 IS NULL AND agent2 IS NOT NULL) then
	UPDATE vtiger_crmentity SET smownerid=agent2 WHERE crmid=pquotesid AND setype='Quotes';
ELSEIF (agent2 IS NULL AND agent1 IS NOT NULL) then
	UPDATE vtiger_crmentity SET smownerid=agent1 WHERE crmid=pquotesid AND setype='Quotes';
ELSEIF (agent1 IS NULL AND agent2 IS NULL) then
	UPDATE vtiger_crmentity SET smownerid=1 WHERE crmid=pquotesid AND setype='Quotes';
ELSE
	if agent1>agent2 then
		SET @temp=nome1;
		SET nome1=nome2;
		SET nome2=@temp;
		SET @temp=cognome1;
		SET cognome1=cognome2;
		SET cognome2=@temp;
	END if;
	SET nomegruppo =CONCAT(SUBSTRING(nome1,1,1),UPPER(SUBSTRING(cognome1,1,2)),'-',SUBSTRING(nome2,1,1),UPPER(SUBSTRING(cognome2,1,2)));
	SET @temp=(SELECT groupid FROM vtiger_groups WHERE groupname=nomegruppo);
	if @temp IS NULL then 
		INSERT INTO vtiger_groups (groupid, groupname, description) VALUES (idgruppo, nomegruppo, CONCAT(nomegruppo,' ',NOW()));
		INSERT INTO vtiger_users2group (groupid, userid) VALUES (idgruppo,agent1);
		INSERT INTO vtiger_users2group (groupid, userid) VALUES (idgruppo,agent2);
		SET @temp=idgruppo;
	END if;
	UPDATE vtiger_crmentity SET smownerid = @temp WHERE crmid=pquotesid  AND setype='Quotes';
END if;
END$$

