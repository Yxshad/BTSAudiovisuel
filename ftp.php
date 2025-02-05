<?php

/**
 * Fonction qui établit une connexion FTP.
 * Prend en paramètre : nom du serveur / login / password (ex : NAS_H264, user2, pass2)
 * Retourne $conn_id. Il sera nécessaire de fermer la connexion avec "ftp_close($conn_id)"
 */
function connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass){
    $conn_id = ftp_connect($ftp_server);
    if (!$conn_id) {
        ajouterLog(LOG_FAIL, "Impossible de se connecter au serveur FTP : $ftp_server.");
        exit();
    }
    elseif (!ftp_login($conn_id, $ftp_user, $ftp_pass)) {
        ajouterLog(LOG_FAIL, "Échec de la connexion au serveur FTP $ftp_server pour l'utilisateur $ftp_user.");
        exit();
    }
    ftp_pasv($conn_id, true); //activation du mode passif pour le pare-feu
    return $conn_id;
}

/**
 * Fonction qui télécharge un fichier dans un répertoire local
 * Prend en paramètre l'id de connexion, le fichier à obtenir en local et le fichier sutué dans le NAS
 */
function telechargerFichier($conn_id, $local_file, $ftp_file){

    if (file_exists($local_file)) {
        ajouterLog(LOG_INFORM, "Le fichier $local_file existe déjà. Téléchargement ignoré.");
        return;
    }

    if ((ftp_get($conn_id, $local_file, $ftp_file, FTP_BINARY))) {
        ajouterLog(LOG_SUCCESS, "Fichier $ftp_file téléchargé avec succès dans $local_file.");
    }
    else{
        ajouterLog(LOG_FAIL, "Échec du téléchargement du fichier $ftp_file.");
    }
}


/**
 * Fonction qui exporte un fichier  local vers un serveur NAS.
 * Prend en paramètre : chemin du fichier local, chemin distant sur le NAS de destination et nom du fichier.
 */
function exporterFichierVersNAS($cheminLocal, $cheminDistantNAS, $nomFichier, $ftp_server, $ftp_user, $ftp_pass) {
    // Construire le chemin complet de destination pour le fichier
    $cheminDistantNASComplet = $cheminDistantNAS . $nomFichier;
    $cheminLocalComplet = $cheminLocal . $nomFichier;
    // Envoyer le fichier
    return exporterFichierVersNASAvecCheminComplet($cheminLocalComplet, $cheminDistantNASComplet, $ftp_server, $ftp_user, $ftp_pass);
}

/**
 * Fonction qui exporte un fichier  local vers un NAS distant.
 * Prend en paramètre les chemins complet des fichiers 
 * Prend en paramètre : chemin du fichier local, chemin distant sur le NAS et nom du fichier.
 */
function exporterFichierVersNASAvecCheminComplet($cheminLocalComplet, $cheminDistantNASComplet, $ftp_server, $ftp_user, $ftp_pass) {
    $conn_id = connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass);
    // Envoyer le fichier
    $exportSucces = true;
    if (!(ftp_put($conn_id, $cheminDistantNASComplet, $cheminLocalComplet, FTP_BINARY))){
        ajouterLog(LOG_FAIL, "Échec de l'export du fichier $cheminLocalComplet vers $cheminDistantNASComplet");
        $exportSucces = false;
    }
    else{
        ajouterLog(LOG_SUCCESS, "Fichier $cheminLocalComplet exporté avec succès dans $cheminDistantNASComplet.");
    }
    ftp_close($conn_id);
    return $exportSucces;
}


/**
 * Fonction qui permet de créer un dossier via FTP
 */
function creerDossierFTP($conn_id, $cheminDossier) {
    $cheminDossier = rtrim($cheminDossier, '/');
    $dossiers = explode('/', $cheminDossier);
    $cheminCourant = '';

    foreach ($dossiers as $dossier) {
        $cheminCourant .= $dossier . '/';
        // Vérifie si le dossier existe, sinon le crée
        if (!@ftp_chdir($conn_id, $cheminCourant)) {
            if (!(ftp_mkdir($conn_id, $cheminCourant))) {
                ajouterLog(LOG_FAIL, "Échec lors de la création du dossier $cheminCourant.");
                exit();
            }
        }
    }
}


/**
 * Fonction qui retourne un tableau de fichiers avec les chemins complets.
 * Prend en paramètre l'id de connexion et le repertoire à partir duquel analyser (normalement la racine).
 * exemple : 2024-2025/video.mp4
 * Si une vidéo est située à la racine, elle se nomme video.mp4
 */
function listerFichiersCompletFTP($conn_id, $repertoire) {
    if (!$conn_id) {
        ajouterLog(LOG_FAIL, "Impossible de se connecter au serveur FTP : $ftp_server.");
        exit();
    }
    $pile = [$repertoire];
    $fichiersComplet = [];
    
    while (!empty($pile) && count($fichiersComplet) < 5) {
        $dossierCourant = array_pop($pile);

        // Utilisation de ftp_mlsd pour récupérer des informations détaillées
        $elements = ftp_mlsd($conn_id, $dossierCourant);
        
        if ($elements !== false) {
            foreach ($elements as $element) {
                // $element est un tableau avec des clés 'name' et 'type'
                $nomFichier = $element['name'];
                $typeElement = $element['type'];

                // Ignorer les éléments '.' et '..'
                if ($nomFichier === '.' || $nomFichier === '..') {
                    continue;
                }

                // Créer le chemin complet
                if ($dossierCourant === '/') {
                    $elementComplet = ltrim($nomFichier, '/');
                } else {
                    $elementComplet = rtrim($dossierCourant, '/') . '/' . ltrim($nomFichier, '/');
                }

                // Si c'est un répertoire, on l'ajoute à la pile pour exploration
                if ($typeElement === 'dir') {
                    $pile[] = $elementComplet;
                } else {
                    // Si c'est un fichier, on l'ajoute à la liste des fichiers
                    $fichiersComplet[] = $elementComplet;
                }
            }
            unset($elements); // Libération de la mémoire
        } else {
            echo "Erreur lors de la récupération des éléments du répertoire.";
        }
    }

    return $fichiersComplet;
}




/**
 * Fonction qui récupère les noms des vidéos situées dans un NAS ($ftp_server). Créé une connexion FTP
 */
function recupererNomsVideosNAS($ftp_server, $ftp_user, $ftp_pass, $URI_NAS, $nomsVideos_NAS){
	
	$conn_id = connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass);

	// Lister les fichiers sur le serveur FTP
	$fichiers_NAS = listerFichiersCompletFTP($conn_id, $URI_NAS);

	foreach ($fichiers_NAS as $fichier) {
        $nom_fichier = basename($fichier); // Récupérer uniquement le nom du fichier
		if ($nom_fichier !== '.' && $nom_fichier !== '..') {

			$nomsVideos_NAS[] = $fichier;
		}
    }
	ftp_close($conn_id);
	return $nomsVideos_NAS;
}


?>