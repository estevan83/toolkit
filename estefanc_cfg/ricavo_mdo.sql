UPDATE estefanc_cfg.vtiger_field SET columnname='ricavo_mdo', tablename='vtiger_troubletickets', fieldname='ricavo_mdo' WHERE columnname='cf_2631' and tablename='vtiger_ticketcf';
ALTER TABLE estefanc_cfg.vtiger_troubletickets ADD COLUMN ricavo_mdo decimal(36,8);
UPDATE estefanc_cfg.vtiger_troubletickets AS a INNER JOIN estefanc_cfg.vtiger_ticketcf AS b ON a.ticketid = b.ticketid SET a.ricavo_mdo = b.cf_2631;
ALTER TABLE estefanc_cfg.vtiger_ticketcf DROP COLUMN cf_2631;
