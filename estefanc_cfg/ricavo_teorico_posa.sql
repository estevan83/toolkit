UPDATE estefanc_cfg.vtiger_field SET columnname='ricavo_teorico_posa', tablename='vtiger_algcommessa', fieldname='ricavo_teorico_posa' WHERE columnname='ricavo_teorico' and tablename='vtiger_algcommessa';
ALTER TABLE estefanc_cfg.vtiger_algcommessa ADD COLUMN ricavo_teorico_posa decimal(30,6);
UPDATE estefanc_cfg.vtiger_algcommessa AS a INNER JOIN estefanc_cfg.vtiger_algcommessa AS b ON a.algcommessaid = b.algcommessaid SET a.ricavo_teorico_posa = b.ricavo_teorico;
ALTER TABLE estefanc_cfg.vtiger_algcommessa DROP COLUMN ricavo_teorico;
