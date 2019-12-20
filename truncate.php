<?php

/* eseguio questa query e creo lo script 

SET @dbname = 'vtigerdb';

SELECT 'SET FOREIGN_KEY_CHECKS = 0;' as schemaresult union
SELECT
    CONCAT('DROP TABLE  ', @dbname,'.',TABLE_NAME,';')

FROM
    information_schema.tables

WHERE
    table_schema = @dbname
UNION
SELECT 'SET FOREIGN_KEY_CHECKS = 1;

*/