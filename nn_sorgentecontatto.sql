UPDATE atalanta_crm.vtiger_field SET columnname='nn_sorgentecontatto', tablename='vtiger_contactscf', fieldname='nn_sorgentecontatto' WHERE columnname='cf_1615' and tablename='vtiger_contactscf';
ALTER TABLE atalanta_crm.vtiger_contactscf ADD COLUMN nn_sorgentecontatto varchar(255);
UPDATE atalanta_crm.vtiger_contactscf INNER JOIN atalanta_crm.vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactscf.contactid SET vtiger_contactscf.nn_sorgentecontatto = vtiger_contactscf.cf_1615;
ALTER TABLE atalanta_crm.vtiger_contactscf DROP COLUMN cf_1615;
