#!/usr/bin/env php
<?php
// bootstrap.php
// Include Composer Autoload (relative to project root).
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;

require_once __DIR__ . '/../vendor/autoload.php';

$config = new Configuration();

/** @var array $connectionParams */
$connectionParams = require __DIR__ . '/DBConfig.php';

foreach ($connectionParams as $version => $connectionParam) {
    try {
        /** @var Connection[] $conn */
        $conn[$version] = \Doctrine\DBAL\DriverManager::getConnection($connectionParam, $config);

        echo $version . ' - ' . $conn[$version]->fetchColumn('SELECT VERSION();') . PHP_EOL;

        // clean up - delete table
        $sql_delete = 'DROP TABLE IF EXISTS `users`;';
        $conn[$version]->executeQuery($sql_delete);

        $sql_create_126 = "
CREATE TABLE IF NOT EXISTS `users` (
  `username` VARCHAR(191) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fullname` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` ENUM('A','D','U','R','H') COLLATE utf8_unicode_ci DEFAULT NULL,
  `quarantine_report` TINYINT(1) DEFAULT '0',
  `spamscore` FLOAT DEFAULT '0',
  `highspamscore` FLOAT DEFAULT '0',
  `noscan` TINYINT(1) DEFAULT '0',
  `quarantine_rcpt` VARCHAR(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resetid` VARCHAR(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resetexpire` BIGINT(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastreset` BIGINT(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `login_expiry` BIGINT(20) COLLATE utf8_unicode_ci DEFAULT '-1',
  `last_login` BIGINT(20) COLLATE utf8_unicode_ci DEFAULT '-1',
  `login_timeout` SMALLINT(5) COLLATE utf8_unicode_ci DEFAULT '-1',
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";

        $conn[$version]->executeQuery($sql_create_126);

        $sql_run_wrong = 'ALTER TABLE `users` ADD COLUMN (
            `id` BIGINT NOT NULL AUTO_INCREMENT UNIQUE KEY FIRST;
            );';
        $sql_run = 'ALTER TABLE users ADD id BIGINT NOT NULL AUTO_INCREMENT FIRST, ADD UNIQUE (id);';

        $runQuery = $conn[$version]->executeQuery($sql_run);
        $result = 'KO';
        if ($runQuery instanceof Doctrine\DBAL\Driver\PDOStatement) {
            $result = 'OK';
        }
        echo 'Result  -> ' . $result . PHP_EOL;
    } catch (Exception $e) {
        echo $version . ': doesn\'t work [' . $e->getMessage() . ']' . ' (' . __FILE__ . ' line ' . $e->getLine() . ')' . PHP_EOL;
    }

    echo '------------------------------------------' . PHP_EOL;
}

