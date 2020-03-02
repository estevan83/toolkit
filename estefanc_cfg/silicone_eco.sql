UPDATE estefanc_cfg.vtiger_field SET columnname='silicone_eco', tablename='vtiger_instplant', fieldname='silicone_eco' WHERE columnname='cf_2846' and tablename='vtiger_instplantcf';
ALTER TABLE estefanc_cfg.vtiger_instplant ADD COLUMN silicone_eco int(11);
UPDATE estefanc_cfg.vtiger_instplant AS a INNER JOIN estefanc_cfg.vtiger_instplantcf AS b ON a.instplantid = b.instplantid SET a.silicone_eco = b.cf_2846;
ALTER TABLE estefanc_cfg.vtiger_instplantcf DROP COLUMN cf_2846;
