<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=MYSQL_SERVER_HOST;dbname=digikam',
    'username' => 'MYSQL_USER',
    'password' => 'MYSQL_PWD',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cacheDigiKam',

    'enableQueryCache'=>true,
    'queryCacheDuration'=>3600,
];
