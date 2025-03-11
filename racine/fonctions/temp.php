<?php

$logFile = "/var/log/backup.log"; // Fichier de log

file_put_contents($logFile, date("Y-m-d H:i:s") . " - Exécution du script\n", FILE_APPEND);

// Définition des variables
$nouvelleHeure = "16:20"; // Remplace avec l'heure désirée (HH:MM)
$scriptPath = "/var/www/html/fonctions/backup.php"; // Chemin du script à exécuter
$cronFile = "/var/www/html/cronjob.txt"; // Fichier cron

// Transformer HH:MM en format cron
list($heure, $minute) = explode(":", $nouvelleHeure);
$nouvelleLigneCron = "$minute $heure * * * php $scriptPath\n";

// Modifier le fichier cron
file_put_contents($cronFile, $nouvelleLigneCron);
chmod($cronFile, 0644);

// Recharger cron
exec("crontab $cronFile && service cron reload", $output, $returnVar);

if ($returnVar !== 0) {
    echo "Erreur lors de la mise à jour de la tâche cron !\n";
    exit(1);
}


echo "Tâche cron mise à jour avec succès pour $nouvelleHeure !\n";
?>
