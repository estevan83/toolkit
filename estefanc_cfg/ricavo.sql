UPDATE estefanc_cfg.vtiger_field SET columnname='ricavo', tablename='vtiger_instplant', fieldname='ricavo' WHERE columnname='cf_2826' and tablename='vtiger_instplantcf';
ALTER TABLE estefanc_cfg.vtiger_instplant ADD COLUMN ricavo decimal(36,8);
UPDATE estefanc_cfg.vtiger_instplant AS a INNER JOIN estefanc_cfg.vtiger_instplantcf AS b ON a.instplantid = b.instplantid SET a.ricavo = b.cf_2826;
ALTER TABLE estefanc_cfg.vtiger_instplantcf DROP COLUMN cf_2826;
