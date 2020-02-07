UPDATE estefanc_cfg.vtiger_field SET columnname='silicone_polymer', tablename='vtiger_instplant', fieldname='silicone_polymer' WHERE columnname='puntevitipz' and tablename='vtiger_instplant';
ALTER TABLE estefanc_cfg.vtiger_instplant ADD COLUMN silicone_polymer int(11);
UPDATE estefanc_cfg.vtiger_instplant AS a INNER JOIN estefanc_cfg.vtiger_instplant AS b ON a.instplantid = b.instplantid SET a.silicone_polymer = b.puntevitipz;
ALTER TABLE estefanc_cfg.vtiger_instplant DROP COLUMN puntevitipz;
