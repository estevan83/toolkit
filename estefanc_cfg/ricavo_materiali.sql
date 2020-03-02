UPDATE estefanc_cfg.vtiger_field SET columnname='ricavo_materiali', tablename='vtiger_instplant', fieldname='ricavo_materiali' WHERE columnname='cf_2832' and tablename='vtiger_instplantcf';
ALTER TABLE estefanc_cfg.vtiger_instplant ADD COLUMN ricavo_materiali decimal(36,8);
UPDATE estefanc_cfg.vtiger_instplant AS a INNER JOIN estefanc_cfg.vtiger_instplantcf AS b ON a.instplantid = b.instplantid SET a.ricavo_materiali = b.cf_2832;
ALTER TABLE estefanc_cfg.vtiger_instplantcf DROP COLUMN cf_2832;
