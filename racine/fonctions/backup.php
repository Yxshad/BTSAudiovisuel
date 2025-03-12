<?php

// Inclure le fichier de constantes
require_once "/var/www/html/ressources/constantes.php";
require_once "/var/www/html/fonctions/fonctions.php";

$backupDir = "/var/www/html/ressources/datas/dumpBD";
$fileName = $backupDir . "/backup_" . date("Y-m-d_H-i-s") . ".sql";
$commandSql = 'mysqldump --user='.BD_USER.' --password='.BD_PASSWORD.' --host=mysql '.BD_NAME.' > '.$fileName;
$operationSucces = exec($commandSql);
ajouterLog(LOG_INFORM, "Création d'une sauvegarde manuelle de la base le ". date("j-m-Y_H-i-s").".", NOM_FICHIER_LOG_SAUVEGARDE);
?>