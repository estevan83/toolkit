UPDATE estefanc_cfg.vtiger_field SET columnname='ricavo_teorico_pezzi', tablename='vtiger_algcommessa', fieldname='ricavo_teorico_pezzi' WHERE columnname='cf_2834' and tablename='vtiger_algcommessacf';
ALTER TABLE estefanc_cfg.vtiger_algcommessa ADD COLUMN ricavo_teorico_pezzi decimal(30,6);
UPDATE estefanc_cfg.vtiger_algcommessa AS a INNER JOIN estefanc_cfg.vtiger_algcommessacf AS b ON a.algcommessaid = b.algcommessaid SET a.ricavo_teorico_pezzi = b.cf_2834;
ALTER TABLE estefanc_cfg.vtiger_algcommessacf DROP COLUMN cf_2834;
