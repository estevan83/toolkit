UPDATE estefanc_cfg.vtiger_field SET columnname='costo_materiali', tablename='vtiger_instplant', fieldname='costo_materiali' WHERE columnname='cf_2830' and tablename='vtiger_instplantcf';
ALTER TABLE estefanc_cfg.vtiger_instplant ADD COLUMN costo_materiali decimal(36,8);
UPDATE estefanc_cfg.vtiger_instplant AS a INNER JOIN estefanc_cfg.vtiger_instplantcf AS b ON a.instplantid = b.instplantid SET a.costo_materiali = b.cf_2830;
ALTER TABLE estefanc_cfg.vtiger_instplantcf DROP COLUMN cf_2830;
