<?php

/**
 * Fonction qui retourne la liste des métadonnées techniques d'une vidéo passée en paramètre
 * Vidéo située sur un espace local
 * $fichier : le titre de la vidéo dont on veut récupérer les métadonnées
 * $URI_ESPACE_LOCAL : le chemin d'accès à la vidéo par exemple : " videos/videosAConvertir/attenteDeConvertion "
 */
function recupererMetadonneesViaVideoLocale($fichier, $URI_ESPACE_LOCAL){
	$fichier_source = $URI_ESPACE_LOCAL . '/' . $fichier;
    $command = "ffmpeg -i $fichier_source 2>&1";
    exec($command, $output);
    $meta = implode($output);
    return recupererMetadonnees($meta, $fichier);
}

/**
 * Fonction qui retourne la liste des métadonnées techniques d'une vidéo passée en paramètre
 * Vidéo située sur un NAS distant, connexion via FTP
 * $fichier : le titre de la vidéo dont on veut récupérer les métadonnées
 * $URI_ESPACE_LOCAL : le chemin d'accès à la vidéo par exemple : " videos/videosAConvertir/attenteDeConvertion "
 */
function recupererMetadonneesVideoViaFTP($ftp_server, $ftp_user, $ftp_pass, $cheminFichier, $nomFichier) {
    $fileUrl = "ftp://$ftp_user:$ftp_pass@$ftp_server/$cheminFichier/$nomFichier";
    $command = "ffmpeg -i \"$fileUrl\" 2>&1";
    exec($command, $output);
    $meta = implode($output);
    return recupererMetadonnees($meta, $nomFichier);
}

/**
 * Fonction de récupération des métadonnées d'un $meta (bloc de métadonnées) via REGEX
 * #RISQUE : Changment des REGEX selon les vidéos
 */
function recupererMetadonnees($meta, $fichier){
    preg_match("/'[^']*\/(.*)'/",$meta,$nom);
    preg_match("/(\d+(.\d+)?)(?= fps)/", $meta, $fps);
    preg_match("/(\d{2,4}x\d{2,4})/", $meta, $resolution);
    preg_match("/(?<=Duration: )(\d{2}:\d{2}:\d{2}.\d{2})/", $meta, $duree);
    preg_match("/(?<=DAR )([0-9]+:[0-9]+)/", $meta, $format);
    // #RISQUE : Attention aux duree des vidéos qui varient selon l'extension-  J'ai arrondi mais solution partiellement viable
    $dureeFormatee = preg_replace('/\.\d+/', '', $duree[1]); //Arrondir pour ne pas tenir compte des centièmes
    $liste = [MTD_TITRE => $fichier,
                MTD_FPS => $fps[0],
                MTD_RESOLUTION => $resolution[0],
                MTD_DUREE => $dureeFormatee,
                MTD_FORMAT => $format[1]
                ];
    return $liste;
}


/**
 * Fonction qui permet de découper une vidéo située dans un espace local en plusieurs fragments
 * Prend en paramètre le titre et la durée d'une vidéo
 */
<<<<<<< HEAD
function decouperVideo($titre, $duree) {
    $total = formaterDuree($duree);

    // Vérifier si la vidéo est trop courte
    if ($total < 100) {
        ajouterLog(LOG_INFO, "Pas de découpage, vidéo trop courte");
        return;
    }

    // Nombre total de parties à créer
    $nombreParties = 100;
    $dureePartie = $total / $nombreParties;

    // Créer le dossier de sortie
    $chemin_dossier = URI_VIDEOS_A_CONVERTIR_EN_COURS_DE_CONVERSION . $titre . '_parts';
    creerDossier($chemin_dossier, false);
    ajouterLog(LOG_SUCCESS, "Création du dossier");
    // Nombre de processus fils à créer
    $nombreProcessus = 4; // Diviser les tâches en 4 processus fils
    $partiesParProcessus = ceil($nombreParties / $nombreProcessus);

    for ($p = 0; $p < $nombreProcessus; $p++) {
        $pid = pcntl_fork(); // Créer un processus fils
        ajouterLog(LOG_SUCCESS, "Création d'un processus");
        if ($pid == -1) {
            ajouterLog(LOG_CRITICAL, "Erreur lors de la création du processus");
            die("Erreur de fork");
        } elseif ($pid == 0) {
            // Processus fils : Gérer sa part des découpages
            $startPartie = $p * $partiesParProcessus;
            $endPartie = min($startPartie + $partiesParProcessus, $nombreParties);

            for ($i = $startPartie; $i < $endPartie; $i++) {
                $start_time = $i * $dureePartie;
                $start_time_formatted = gmdate("H:i:s", intval($start_time)) . sprintf(".%03d", ($start_time - floor($start_time)) * 1000);

                // Déterminer la durée effective de la partie
                $current_part_duration = ($i == $nombreParties - 1) ? max(($total - $start_time), 0.01) : $dureePartie;

                // Chemin de sortie pour l'extrait
                $extension = (substr($titre, -1) == "4") ? '.mp4' : '.mxf';
                $output_path = $chemin_dossier . '/' . $titre . '_part_' . sprintf('%03d', $i + 1) . $extension;

                // Commande ffmpeg
                $command = "ffmpeg -i \"" . URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION . '/' . $titre . "\"" .
                           " -ss " . $start_time_formatted .
                           " -t " . $current_part_duration .
                           " -c copy \"" . $output_path . "\" -y";

                ajouterLog(LOG_SUCCESS, "Partie " . $i+1 . " découpé" );

                // Exécuter la commande ffmpeg
                exec($command, $output, $return_var);

                // Vérifier les erreurs
                if ($return_var != 0) {
                    ajouterLog(LOG_CRITICAL, "Erreur lors du découpage de la partie " . ($i + 1) . " de la vidéo $titre.");
                }
            }
            // Le processus fils termine son exécution
            exit(0);
        }
    }
    ajouterLog(LOG_SUCCESS, "attente des procs fils");
    // Processus parent : attendre la fin de tous les processus fils
    while (pcntl_wait($status) > 0);
    ajouterLog(LOG_SUCCESS, "suppression du dossier");
    // Supprimer le fichier original après le découpage
    unlink(URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION . '/' . $titre);
}




/**
 * Fonction qui converti l'ensemble des parties de vidéo situées dans URI_VIDEOS_EN_ATTENTE_DE_CONVERSION et les place dans URI_VIDEOS_EN_COURS_DE_CONVERSION (à upload)
 * Prend en paramètre une $vidéo
 */
function convertirVideo($video){
    // Chemin pour accéder aux dossiers des vidéos
    $chemin_dossier_origine = URI_VIDEOS_A_CONVERTIR_EN_COURS_DE_CONVERSION . $video . '_parts';
    $chemin_dossier_destination = URI_VIDEOS_A_UPLOAD_EN_COURS_DE_CONVERSION . $video . "_parts";
    // Création du dossier qui va stocker les morceaux de videos compressées    
    creerDossier($chemin_dossier_destination, false);
    // On récupère toutes les morceaux de vidéos à convertir
    $files = scandir($chemin_dossier_origine);
    // Pour chaque fichier on le converti en MPEG
    foreach ($files as $file) {
        if($file != '.' && $file != '..'){
            $chemin_fichier_origine = $chemin_dossier_origine . '/' . $file;
            $chemin_fichier_destination = $chemin_dossier_destination . '/' . pathinfo($file, PATHINFO_FILENAME) . '.mp4';
            
            // Commande pour convertir la vidéo avec des paramètres de qualité très réduits
            $command = "ffmpeg -i \"$chemin_fichier_origine\" " .
                       "-c:v libx264 -preset ultrafast -crf 35 " .  // CRF élevé pour réduire la qualité vidéo
                       "-c:a aac -b:a 64k " .                      // Bitrate audio réduit à 64 kbps
                       "-movflags +faststart " .                   // Optimisation pour le streaming
                       "\"$chemin_fichier_destination\"";
            exec($command, $output, $return_var);
            if ($return_var == 1) {
                ajouterLog(LOG_CRITICAL, "Erreur lors de la conversion de la partie".($files[$file] + 1)."de la vidéo $chemin_fichier_origine.");
=======
function traiterVideo($titre, $duree) {
    // Démarrer le timer
    $startTime = microtime(true);
    ajouterLog(LOG_DEBUG, "Début du traitement de la vidéo : $titre");

    // Convertir la durée en secondes
    $total = formaterDuree($duree);
    ajouterLog(LOG_DEBUG, "Durée totale de la vidéo (en secondes) : $total");

    // Calculer le découpage en parties
    if ($total < 100) {
        $dureePartie = 2;
        $nombreParties = ceil($total / $dureePartie);
    } else {
        $nombreParties = 50;
        $dureePartie = $total / $nombreParties;
    }

    ajouterLog(LOG_DEBUG, "Découpage en $nombreParties parties, durée par partie : $dureePartie secondes");

    // Créer le dossier pour stocker les parties
    $chemin_dossier_destination = URI_VIDEOS_A_UPLOAD_EN_COURS_DE_CONVERSION . $titre . '_parts';
    creerDossier($chemin_dossier_destination, false);
    ajouterLog(LOG_DEBUG, "Dossier créé pour les parties de la vidéo : $chemin_dossier_destination");

    // Nombre maximal de processus parallèles
    $maxParallelProcesses = shell_exec('nproc') ?: 8;
    ajouterLog(LOG_DEBUG, "Nombre maximum de processus parallèles : $maxParallelProcesses");

    $processes = [];

    // Boucle de découpage et de traitement
    for ($i = 0; $i < $nombreParties; $i++) {
        $start_time = $i * $dureePartie;
        $start_time_formatted = gmdate("H:i:s", intval($start_time)) . sprintf(".%03d", ($start_time - floor($start_time)) * 1000);
        $current_part_duration = ($i == $nombreParties - 1) ? max(($total - $start_time), 0.01) : $dureePartie;

        $input_path = URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION . '/' . $titre;
        $output_path = $chemin_dossier_destination . '/' . $titre . '_part_' . sprintf('%03d', $i + 1) . '.mp4';

        // Commande optimisée ffmpeg
        $command = "ffmpeg -i \"$input_path\" " .
                   "-ss $start_time_formatted " .
                   "-t $current_part_duration " .
                   "-c:v libx264 -preset veryfast -crf 40 " .
                   "-c:a aac -b:a 64k " .
                   "-movflags +faststart " .
                   "\"$output_path\" -y";

        ajouterLog(LOG_DEBUG, "Commande générée pour la partie " . ($i + 1) . " : $command");

        // Lancer le processus avec proc_open
        $descriptors = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];
        $process = proc_open($command, $descriptors, $pipes);

        if (is_resource($process)) {
            ajouterLog(LOG_DEBUG, "Processus lancé pour la partie " . ($i + 1));
            $processes[] = $process;
        } else {
            ajouterLog(LOG_ERROR, "Échec du lancement du processus pour la partie " . ($i + 1));
            continue;
        }

        // Gestion des processus parallèles
        while (count($processes) >= $maxParallelProcesses) {
            foreach ($processes as $key => $p) {
                $status = proc_get_status($p);
                if (!$status['running']) {
                    proc_close($p); // Terminer le processus
                    unset($processes[$key]); // Retirer de la liste
                    ajouterLog(LOG_DEBUG, "Processus terminé, slot libéré.");
                }
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
            }
            usleep(50000); // Pause optimisée (50 ms)
        }
    }

    // Terminer les processus restants
    ajouterLog(LOG_DEBUG, "Attente de la fin des processus restants...");
    foreach ($processes as $p) {
        proc_close($p);
    }
    ajouterLog(LOG_DEBUG, "Tous les processus terminés");

    // Supprimer le fichier original
    $originalFile = URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION . '/' . $titre;
    if (file_exists($originalFile)) {
        unlink($originalFile);
        ajouterLog(LOG_DEBUG, "Fichier original supprimé : $originalFile");
    } else {
        ajouterLog(LOG_WARNING, "Le fichier original $titre n'existe pas ou a déjà été supprimé.");
    }

    // Calculer le temps d'exécution
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    ajouterLog(LOG_DEBUG, "Traitement terminé en " . round($executionTime, 2) . " secondes.");
    echo "Traitement terminé en " . round($executionTime, 2) . " secondes.\n";
}



/**
 * Fonction qui permet de fisionner tous les morceaux d'une vidéo en un seul fichier
 * Prend en paramètre la $video à fusionner (nom du dossier)
 */
function fusionnerVideo($video){
    // Chemin pour accéder aux dossiers des vidéos
    $chemin_dossier_origine = URI_VIDEOS_A_UPLOAD_EN_COURS_DE_CONVERSION . $video . '_parts';
    $chemin_dossier_destination = URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD;

    // On récupère toutes les morceaux de vidéos à convertir
    $files = scandir($chemin_dossier_origine);
    // On trie les fichier avec l'ordre naturel (ex:  vid_1, vid_10, vid_2 -> vid_1, vid_2, vid_10)
    natsort($files);
    // On met le nom de chaques vidéos dans un fichier txt pour ffmpeg
    $fileListContent = "";
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            $fileListContent .= "file '" . $file . "'\n";
        }
    }
    // On donne le fichier txt à ffmpeg pour qu'il fusionne toutes les vidéos suivant l'ordre naturel, LE TXT N'EST PAS OPIONEL
    $fileListPath = $chemin_dossier_origine . '/file_list.txt';
    file_put_contents($fileListPath, $fileListContent);
    $outputFile = $chemin_dossier_destination . "/" . $video;
    $command = "ffmpeg -v verbose -f concat -safe 0 -i " . $fileListPath .
               " -c copy " . substr($outputFile, 0, -3) . "mp4";
    exec($command, $output, $returnVar);


    // On supprime le dossier qui contient les morceaux convertis
    $files = scandir($chemin_dossier_origine);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            unlink($chemin_dossier_origine . "/" . $file);
        }
    }
    rmdir($chemin_dossier_origine);
}

/**
 * Fonction qui créé une miniature dans un espace local.
 * Prend en paramètre une vidéo et sa durée
 * Retourne le nom de la miniature
 */
function genererMiniature($video, $duree){

    $total = formaterDuree($duree);

    $timecode = floor($total / 2);

    $videoSansExtension = rtrim($video, ".mp4");

    $miniature = $videoSansExtension . SUFFIXE_MINIATURE_VIDEO;

    $command = "ffmpeg -i " . $video . 
               " -ss " . $timecode . 
               " -vframes 1 " . $miniature;
        
    exec($command, $output, $returnVar);
    ajouterLog(LOG_SUCCESS, "Miniature de la vidéo $video générée avec succès.");
    $miniature = basename($miniature);
    return $miniature;
}

/**
 * Fonction qui permet de convertir une durée totale en secondes
 * Prend en paramètre une $duree sous la forme hh:mm:ss.mm
 * Retourne la durée totale en seconde
 */
function formaterDuree($duree){
    $heures = (int)substr($duree, 0, 2);
    $minutes = (int)substr($duree, 3, 2);
    $secondes = (int)substr($duree, 6, 2);
    $milisecondes = (int)substr($duree, 9, 2);

    // Convertir la durée totale en secondes
    $total = $heures * 3600 + $minutes * 60 + $secondes + $milisecondes / 1000;
    return $total;
}
?>