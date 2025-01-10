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
function decouperVideo($titre, $duree) {
    
    $total = formaterDuree($duree);
    
    // Vérifier si la durée totale est inférieure à 100 secondes
    if ($total < 100) {
        $dureePartie = 2; // Durée de chaque partie en secondes
        $nombreParties = ceil($total / $dureePartie); // Nombre total de parties
    } else {
        $nombreParties = 100; // Diviser en 100 parties
        $dureePartie = $total / $nombreParties; // Durée de chaque partie
    }
    // Créer le dossier de sortie
    $chemin_dossier = URI_VIDEOS_A_CONVERTIR_EN_COURS_DE_CONVERSION . $titre . '_parts';
    creerDossier($chemin_dossier, false);
    for ($i = 0; $i < $nombreParties; $i++) {
        // Calculer le temps de début pour chaque partie
        $start_time = $i * $dureePartie;
        // Formater le temps de début avec une précision correcte
        $start_time_formatted = gmdate("H:i:s", intval($start_time)) . sprintf(".%03d", ($start_time - floor($start_time)) * 1000);
        // Déterminer la durée effective de la partie (dernier segment peut être plus court)
        $current_part_duration = ($i == $nombreParties - 1) ? max(($total - $start_time), 0.01) : $dureePartie;
        // Chemin de sortie pour l'extrait
        if (substr($titre, -1) == "4" ) {
            $output_path = $chemin_dossier . '/' . $titre . '_part_' . sprintf('%03d', $i + 1) . '.mp4';
        } else{
            $output_path = $chemin_dossier . '/' . $titre . '_part_' . sprintf('%03d', $i + 1) . '.mxf';
        }
        // Construire la commande ffmpeg
        $command = "ffmpeg -i \"" . URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION . '/' . $titre . "\"" .
                   " -ss " . $start_time_formatted .
                   " -t " . $current_part_duration .
                   " -c copy \"" . $output_path . "\" -y";
        // Exécuter la commande ffmpeg
        exec($command, $output, $return_var);
        // #RISQUE
        if ($return_var == 1) {
            ajouterLog(LOG_CRITICAL, "Erreur lors du découpage de la partie".($i + 1)."de la vidéo $titre.");
        }
    }
    // Supprimer le fichier original
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
                ajouterLog(LOG_CRITICAL, "Erreur lors de la conversion de la partie".($i + 1)."de la vidéo $titre.");
            }
        }
    }
    // On supprime le dossier des morceaux de vidéos à convertir 
    $files = scandir($chemin_dossier_origine);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            unlink($chemin_dossier_origine . "/" . $file);
        }
        
    }
    rmdir($chemin_dossier_origine);
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