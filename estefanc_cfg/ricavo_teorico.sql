UPDATE estefanc_cfg.vtiger_field SET columnname='ricavo_teorico', tablename='vtiger_algcommessa', fieldname='ricavo_teorico' WHERE columnname='cf_2828' and tablename='vtiger_algcommessacf';
ALTER TABLE estefanc_cfg.vtiger_algcommessa ADD COLUMN ricavo_teorico decimal(30,6);
UPDATE estefanc_cfg.vtiger_algcommessa AS a INNER JOIN estefanc_cfg.vtiger_algcommessacf AS b ON a.algcommessaid = b.algcommessaid SET a.ricavo_teorico = b.cf_2828;
ALTER TABLE estefanc_cfg.vtiger_algcommessacf DROP COLUMN cf_2828;
