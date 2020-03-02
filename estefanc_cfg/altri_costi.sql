UPDATE estefanc_cfg.vtiger_field SET columnname='altri_costi', tablename='vtiger_instplant', fieldname='altri_costi' WHERE columnname='cf_2850' and tablename='vtiger_instplantcf';
ALTER TABLE estefanc_cfg.vtiger_instplant ADD COLUMN altri_costi decimal(36,8);
UPDATE estefanc_cfg.vtiger_instplant AS a INNER JOIN estefanc_cfg.vtiger_instplantcf AS b ON a.instplantid = b.instplantid SET a.altri_costi = b.cf_2850;
ALTER TABLE estefanc_cfg.vtiger_instplantcf DROP COLUMN cf_2850;
