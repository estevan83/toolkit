UPDATE estefanc_cfg.vtiger_field SET columnname='costo_mdo', tablename='vtiger_troubletickets', fieldname='costo_mdo' WHERE columnname='cf_2627' and tablename='vtiger_ticketcf';
ALTER TABLE estefanc_cfg.vtiger_troubletickets ADD COLUMN costo_mdo decimal(36,8);
UPDATE estefanc_cfg.vtiger_troubletickets AS a INNER JOIN estefanc_cfg.vtiger_ticketcf AS b ON a.ticketid = b.ticketid SET a.costo_mdo = b.cf_2627;
ALTER TABLE estefanc_cfg.vtiger_ticketcf DROP COLUMN cf_2627;
