UPDATE estefanc_cfg.vtiger_field SET columnname='ricavo_mc', tablename='vtiger_troubletickets', fieldname='ricavo_mc' WHERE columnname='cf_2842' and tablename='vtiger_ticketcf';
ALTER TABLE estefanc_cfg.vtiger_troubletickets ADD COLUMN ricavo_mc decimal(36,8);
UPDATE estefanc_cfg.vtiger_troubletickets AS a INNER JOIN estefanc_cfg.vtiger_ticketcf AS b ON a.ticketid = b.ticketid SET a.ricavo_mc = b.cf_2842;
ALTER TABLE estefanc_cfg.vtiger_ticketcf DROP COLUMN cf_2842;
