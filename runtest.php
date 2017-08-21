#!/usr/bin/env php
<?php
// bootstrap.php
// Include Composer Autoload (relative to project root).
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;

require_once __DIR__ . '/vendor/autoload.php';

$config = new Configuration();

$connectionParams['mysql55'] = array(
    'url' => 'mysql://mailwatch:mailwatch@127.0.0.1:55055/mailwatch',
);
$connectionParams['mysql56'] = array(
    'url' => 'mysql://mailwatch:mailwatch@127.0.0.1:55056/mailwatch',
);
$connectionParams['mysql57'] = array(
    'url' => 'mysql://mailwatch:mailwatch@127.0.0.1:55057/mailwatch',
);
$connectionParams['mysql80'] = array(
    'url' => 'mysql://mailwatch:mailwatch@127.0.0.1:55080/mailwatch',
);
$connectionParams['mariadb55'] = array(
    'url' => 'mysql://mailwatch:mailwatch@127.0.0.1:51055/mailwatch',
);
$connectionParams['mariadb101'] = array(
    'url' => 'mysql://mailwatch:mailwatch@127.0.0.1:51101/mailwatch',
);
$connectionParams['mariadb102'] = array(
    'url' => 'mysql://mailwatch:mailwatch@127.0.0.1:51102/mailwatch',
);
$connectionParams['mariadb103'] = array(
    'url' => 'mysql://mailwatch:mailwatch@127.0.0.1:51103/mailwatch',
);

foreach ($connectionParams as $version => $connectionParam) {
    try {
        /** @var Connection[] $conn */
        $conn[$version] = \Doctrine\DBAL\DriverManager::getConnection($connectionParam, $config);


        echo $version . ' - ' . $conn[$version]->fetchColumn('SELECT VERSION();') .  PHP_EOL;

        // finish up - delete table
        $sql_delete = 'DROP TABLE IF EXISTS `maillog`;';
        $conn[$version]->executeQuery($sql_delete);

        //create db v1.2.3
        $sql_create = "
    CREATE TABLE IF NOT EXISTS `maillog` (
  `maillog_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id` MEDIUMTEXT COLLATE utf8_unicode_ci,
  `size` BIGINT(20) DEFAULT '0',
  `from_address` MEDIUMTEXT COLLATE utf8_unicode_ci,
  `from_domain` MEDIUMTEXT COLLATE utf8_unicode_ci,
  `to_address` MEDIUMTEXT COLLATE utf8_unicode_ci,
  `to_domain` MEDIUMTEXT COLLATE utf8_unicode_ci,
  `subject` MEDIUMTEXT COLLATE utf8_unicode_ci,
  `clientip` MEDIUMTEXT COLLATE utf8_unicode_ci,
  `archive` MEDIUMTEXT COLLATE utf8_unicode_ci,
  `isspam` TINYINT(1) DEFAULT '0',
  `ishighspam` TINYINT(1) DEFAULT '0',
  `issaspam` TINYINT(1) DEFAULT '0',
  `isrblspam` TINYINT(1) DEFAULT '0',
  `isfp` TINYINT(1) DEFAULT '0',
  `isfn` TINYINT(1) DEFAULT '0',
  `spamwhitelisted` TINYINT(1) DEFAULT '0',
  `spamblacklisted` TINYINT(1) DEFAULT '0',
  `sascore` DECIMAL(7,2) DEFAULT '0.00',
  `spamreport` MEDIUMTEXT COLLATE utf8_unicode_ci,
  `virusinfected` TINYINT(1) DEFAULT '0',
  `nameinfected` TINYINT(1) DEFAULT '0',
  `otherinfected` TINYINT(1) DEFAULT '0',
  `report` MEDIUMTEXT COLLATE utf8_unicode_ci,
  `ismcp` TINYINT(1) DEFAULT '0',
  `ishighmcp` TINYINT(1) DEFAULT '0',
  `issamcp` TINYINT(1) DEFAULT '0',
  `mcpwhitelisted` TINYINT(1) DEFAULT '0',
  `mcpblacklisted` TINYINT(1) DEFAULT '0',
  `mcpsascore` DECIMAL(7,2) DEFAULT '0.00',
  `mcpreport` MEDIUMTEXT COLLATE utf8_unicode_ci,
  `hostname` MEDIUMTEXT COLLATE utf8_unicode_ci,
  `date` DATE DEFAULT NULL,
  `time` TIME DEFAULT NULL,
  `headers` MEDIUMTEXT COLLATE utf8_unicode_ci,
  `quarantined` TINYINT(1) DEFAULT '0',
  `rblspamreport` MEDIUMTEXT COLLATE utf8_unicode_ci DEFAULT NULL,
  `token` CHAR(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `released` TINYINT(1) DEFAULT '0',
  `salearn` TINYINT(1) DEFAULT '0',
  PRIMARY KEY (`maillog_id`),
  KEY `maillog_datetime_idx` (`date`,`time`),
  KEY `maillog_id_idx` (`id`(20)),
  KEY `maillog_clientip_idx` (`clientip`(20)),
  KEY `maillog_from_idx` (`from_address`(191)),
  KEY `maillog_to_idx` (`to_address`(191)),
  KEY `maillog_host` (`hostname`(30)),
  KEY `from_domain_idx` (`from_domain`(50)),
  KEY `to_domain_idx` (`to_domain`(50)),
  KEY `maillog_quarantined` (`quarantined`),
  KEY `timestamp_idx` (`timestamp`)
  /*!50604 , FULLTEXT KEY `subject_idx` (`subject`) */
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ";

        $conn[$version]->executeQuery($sql_create);

        $sql_show = "SHOW COLUMNS FROM maillog LIKE 'timestamp';";

        $before = $conn[$version]->executeQuery($sql_show)->fetchAll();
        echo 'Before -> Default=' . $before[0]['Default'] . ' - Extra:' . $before[0]['Extra'] . PHP_EOL;

        //$sql = "ALTER TABLE `maillog` CHANGE `timestamp` `timestamp` TIMESTAMP NOT NULL DEFAULT 0";
        $sql_1 = "ALTER TABLE `maillog` CHANGE `timestamp` `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
        $conn[$version]->executeQuery($sql_1);

        $middle = $conn[$version]->executeQuery($sql_show)->fetchAll();
        echo 'Middle -> Default=' . $middle[0]['Default'] . ' - Extra:' . $middle[0]['Extra'] . PHP_EOL;

        $sql_2 = "ALTER TABLE `maillog` ALTER COLUMN `timestamp` DROP DEFAULT";
        $conn[$version]->executeQuery($sql_2);

        $after = $conn[$version]->executeQuery($sql_show)->fetchAll();
        echo 'After  -> Default=' . $after[0]['Default'] . ' - Extra:' . $after[0]['Extra'] . PHP_EOL;
    } catch (Exception $e) {
        echo $version . ': non funziona [' . $e->getMessage() . ']' . PHP_EOL;
    }

    echo '------------------------------------------' . PHP_EOL;
}

