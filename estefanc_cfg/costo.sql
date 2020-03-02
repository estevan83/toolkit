UPDATE estefanc_cfg.vtiger_field SET columnname='costo', tablename='vtiger_instplant', fieldname='costo' WHERE columnname='cf_2824' and tablename='vtiger_instplantcf';
ALTER TABLE estefanc_cfg.vtiger_instplant ADD COLUMN costo decimal(36,8);
UPDATE estefanc_cfg.vtiger_instplant AS a INNER JOIN estefanc_cfg.vtiger_instplantcf AS b ON a.instplantid = b.instplantid SET a.costo = b.cf_2824;
ALTER TABLE estefanc_cfg.vtiger_instplantcf DROP COLUMN cf_2824;
