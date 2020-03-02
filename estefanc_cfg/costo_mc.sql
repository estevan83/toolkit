UPDATE estefanc_cfg.vtiger_field SET columnname='costo_mc', tablename='vtiger_troubletickets', fieldname='costo_mc' WHERE columnname='cf_2840' and tablename='vtiger_ticketcf';
ALTER TABLE estefanc_cfg.vtiger_troubletickets ADD COLUMN costo_mc decimal(36,8);
UPDATE estefanc_cfg.vtiger_troubletickets AS a INNER JOIN estefanc_cfg.vtiger_ticketcf AS b ON a.ticketid = b.ticketid SET a.costo_mc = b.cf_2840;
ALTER TABLE estefanc_cfg.vtiger_ticketcf DROP COLUMN cf_2840;
