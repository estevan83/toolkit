UPDATE atalanta_crm.vtiger_field SET columnname='nn_datarichiesta', tablename='vtiger_contactscf', fieldname='nn_datarichiesta' WHERE columnname='cf_1607' and tablename='vtiger_contactscf';
ALTER TABLE atalanta_crm.vtiger_contactscf ADD COLUMN nn_datarichiesta date;
UPDATE atalanta_crm.vtiger_contactscf INNER JOIN atalanta_crm.vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactscf.contactid SET vtiger_contactscf.nn_datarichiesta = vtiger_contactscf.cf_1607;
ALTER TABLE atalanta_crm.vtiger_contactscf DROP COLUMN cf_1607;
