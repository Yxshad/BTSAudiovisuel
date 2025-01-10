<?php

/**
 * Fonction principale qui execute le transfert des fichiers des NAS ARCH et PAD vers le NAS MPEG
 * Alimente aussi la base de données avec les métadonnées techniques des vidéos transférées 
 */
function fonctionTransfert(){
	$COLLECT_PAD = [];
	$COLLECT_ARCH = [];
	$COLLECT_MPEG = [];
	//-----------------------   répertoire NAS_PAD      ------------------------
	$COLLECT_PAD = recupererCollectNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_VIDEOS_A_ANALYSER, $COLLECT_PAD, URI_RACINE_NAS_PAD);
	//-----------------------   répertoire NAS_ARCH      ------------------------
	$COLLECT_ARCH = recupererCollectNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_VIDEOS_A_ANALYSER, $COLLECT_ARCH, URI_RACINE_NAS_ARCH);
	//Remplir $COLLECT_MPEG
	$COLLECT_MPEG = remplirCollect_MPEG($COLLECT_PAD, $COLLECT_ARCH, $COLLECT_MPEG);
	//Alimenter le NAS MPEG
	$COLLECT_MPEG = alimenterNAS_MPEG($COLLECT_MPEG);

	//Mettre à jour la base avec $COLLECT_MPEG
	insertionCollect_MPEG($COLLECT_MPEG);
    ajouterLog(LOG_SUCCESS, "Fonction de transfert effectuée avec succès.");
}


/**
* - Fonction qui récupère l'ensemble des métadonnées techniques des vidéos d'un NAS (collectPAD ou collectARCH)
* On télécharge les vidéos dans un $URI_VIDEOS_A_ANALYSER si celles-ci ne sont pas présentes dans la BD
* - On remplit CollectNAS pour chaque vidéo
* - On vide le répertoire local $URI_VIDEOS_A_ANALYSER
*/
function recupererCollectNAS($ftp_server, $ftp_user, $ftp_pass, $URI_VIDEOS_A_ANALYSER, $COLLECT_NAS, $URI_NAS_RACINE){
	
	$conn_id = connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass);

	// Lister les fichiers sur le serveur FTP
	$fichiers_NAS = listerFichiersCompletFTP($conn_id, $URI_NAS_RACINE);

	foreach ($fichiers_NAS as $fichier) {
        $nom_fichier = basename($fichier);

		$extension = substr(pathinfo($fichier, PATHINFO_EXTENSION), -3);

		//Si le fichier est une vidéo
		if ($nom_fichier !== '.' && $nom_fichier !== '..'
			&& ($extension == 'mxf' || $extension == 'mp4')) {

			// Si le fichier n'est pas présent en base
			if (!fichierEnBase($nom_fichier)) {

				//Chemin distant
				$cheminFichier = dirname($fichier) . '/';

				//RECUPERATION VIA TELECHARGEMENT FTP --ABANDONNE CAR BESOIN DE TELECHARGER LA VIDEO
				/*$fichierDesination = $URI_VIDEOS_A_ANALYSER . '/' . $nom_fichier;
				telechargerFichier($conn_id, $fichierDesination, $fichier);
				$listeMetadonneesVideos = recupererMetadonneesViaVideoLocale($nom_fichier, $URI_VIDEOS_A_ANALYSER);
				unlink($fichierDesination);*/

				//RECUPERATION VIA LECTURE FTP
				$listeMetadonneesVideos = recupererMetadonneesVideoViaFTP($ftp_server, $ftp_user, $ftp_pass, $cheminFichier, $nom_fichier);

				$COLLECT_NAS[] = array_merge($listeMetadonneesVideos, [MTD_URI => $cheminFichier]);
			}
		}
    }
	ftp_close($conn_id);
	return $COLLECT_NAS;
}


/**
* Fonction qui remplit $COLLECT_MPEG avec les metadonnées de chaque vidéo présentes dans $COLLECT_PAD ET $COLLECT_ARCH
* - Vide les vidéos de $COLLECT_PAD et $COLLECT_ARCH qui sont ajoutées dans $COLLECT_MPEG (passage les collections par référence)
* Traite les vidéos isolées 
*/
function remplirCollect_MPEG(&$COLLECT_PAD, &$COLLECT_ARCH, $COLLECT_MPEG){

	foreach ($COLLECT_PAD as $key_PAD => $ligneCollect_PAD) {
		foreach ($COLLECT_ARCH as $key_ARCH => $ligneCollect_ARCH) {
			//Si les deux $ligneCollect correspondent exactement (hors URI) (pathinfo pour ne pas tenir compte de l'extension)
			if (verifierCorrespondanceMdtTechVideos($ligneCollect_PAD, $ligneCollect_ARCH)){

				//Remplir $COLLECT_MPEG
				$COLLECT_MPEG[] = [
					MTD_TITRE => $ligneCollect_ARCH[MTD_TITRE],
					MTD_URI_NAS_PAD => $ligneCollect_PAD[MTD_URI],
					MTD_URI_NAS_ARCH => $ligneCollect_ARCH[MTD_URI],
					MTD_FORMAT => $ligneCollect_PAD[MTD_FORMAT],
					MTD_FPS => $ligneCollect_PAD[MTD_FPS],
					MTD_RESOLUTION => $ligneCollect_PAD[MTD_RESOLUTION],
					MTD_DUREE => $ligneCollect_PAD[MTD_DUREE]
				];

				//Retirer $ligneCollect_ARCH et $ligneCollect_PAD de COLLECT_ARCH et $COLLECT_PAD
				unset($COLLECT_PAD[$key_PAD]);
                unset($COLLECT_ARCH[$key_ARCH]);
				break;
			}
		}
	}
	//Traitement des fichiers isolés
	foreach ($COLLECT_PAD as $key_PAD => $ligneCollect_PAD) {
		$COLLECT_MPEG[] = [
			MTD_TITRE => $ligneCollect_PAD[MTD_TITRE],
			MTD_URI_NAS_PAD => $ligneCollect_PAD[MTD_URI],
			MTD_URI_NAS_ARCH => null,
			MTD_FORMAT => $ligneCollect_PAD[MTD_FORMAT],
			MTD_FPS => $ligneCollect_PAD[MTD_FPS],
			MTD_RESOLUTION => $ligneCollect_PAD[MTD_RESOLUTION],
			MTD_DUREE => $ligneCollect_PAD[MTD_DUREE]
		];
		unset($COLLECT_PAD[$key_PAD]);
	}
	foreach ($COLLECT_ARCH as $key_ARCH => $ligneCollect_ARCH) {
		$COLLECT_MPEG[] = [
			MTD_TITRE => $ligneCollect_ARCH[MTD_TITRE],
			MTD_URI_NAS_PAD => null,
			MTD_URI_NAS_ARCH => $ligneCollect_ARCH[MTD_URI],
			MTD_FORMAT => $ligneCollect_ARCH[MTD_FORMAT],
			MTD_FPS => $ligneCollect_ARCH[MTD_FPS],
			MTD_RESOLUTION => $ligneCollect_ARCH[MTD_RESOLUTION],
			MTD_DUREE => $ligneCollect_ARCH[MTD_DUREE]
		];
		unset($COLLECT_ARCH[$key_ARCH]);
	}
	return $COLLECT_MPEG;
}


function alimenterNAS_MPEG($COLLECT_MPEG){

	foreach($COLLECT_MPEG as &$video){
		//Téléchargement du fichier dans le répertoire local
		$fichierDesination = URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION . $video[MTD_TITRE];

		//Savoir dans quel NAS chercher la vidéo. Si on a le choix, on prend le NAS ARCH
		if($video[MTD_URI_NAS_ARCH] != null && $video[MTD_URI_NAS_ARCH] != ""){
			$conn_id = connexionFTP_NAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH);
			$fichierSource = $video[MTD_URI_NAS_ARCH] . $video[MTD_TITRE];
			telechargerFichier($conn_id, $fichierDesination, $fichierSource);
			ftp_close($conn_id);
			$URI_NAS = $video[MTD_URI_NAS_ARCH];
		}
		elseif($video[MTD_URI_NAS_PAD] != null && $video[MTD_URI_NAS_PAD] != ""){
			$conn_id = connexionFTP_NAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD);
			$fichierSource = $video[MTD_URI_NAS_PAD] . $video[MTD_TITRE];
			telechargerFichier($conn_id, $fichierDesination, $fichierSource);
			ftp_close($conn_id);
			$URI_NAS = $video[MTD_URI_NAS_PAD];
		}
		else{
			ajouterLog(LOG_FAIL, "Erreur, la vidéo $video n'est présente dans aucun des 2 NAS .");
            exit();
		}
		
		decouperVideo($video[MTD_TITRE], $video[MTD_DUREE]);
		convertirVideo($video[MTD_TITRE]);
		fusionnerVideo($video[MTD_TITRE]);

		// Forcer l'extension à .mp4
		$nomFichierSansExtension = pathinfo($video[MTD_TITRE], PATHINFO_FILENAME);
		$video[MTD_TITRE] = $nomFichierSansExtension . '.mp4'; // Forcer l'extension à .mp4

		$fichierSource = URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD . $video[MTD_TITRE];
		$cheminDestination = URI_RACINE_NAS_MPEG .$URI_NAS;
		$fichierDestination = $video[MTD_TITRE];

		//Créer le dossier dans le NAS si celui-ci n'existe pas déjà.
		$nomFichierSansExtension = pathinfo($fichierSource, PATHINFO_FILENAME);
		$dossierVideo = $cheminDestination . PREFIXE_DOSSIER_VIDEO . $nomFichierSansExtension . '/';
		$conn_id = connexionFTP_NAS(NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG);
		creerDossierFTP($conn_id, $cheminDestination);
		creerDossierFTP($conn_id, $dossierVideo);
		ftp_close($conn_id);

		//Export de la vidéo dans le NAS MPEG
		exporterFichierVersNAS(URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD, $dossierVideo, $video[MTD_TITRE], NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG);

		//Générer la miniature de la vidéo
		$miniature = genererMiniature($fichierSource, $video[MTD_DUREE]);

		exporterFichierVersNAS(URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD, $dossierVideo, $miniature, NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG);

		//Supprimer la vidéo de l'espace local et sa miniature
		unlink($fichierSource);
		unlink(URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD.$miniature);

		//Ajouter l'URI du NAS MPEG à $video dans collectMPEG
		$video[MTD_URI_NAS_MPEG] = $dossierVideo;
	}

	return $COLLECT_MPEG;
}


/**
 * Fonction qui affiche à l'écran une collection passée en paramètre sous forme de tableau
 * $titre : Le titre de la collection à afficher
 * $COLLECT_NAS : Un tableau de métadonnées des vidéos présentes dans le NAS
 */
function afficherCollect($titre, $COLLECT_NAS) {
    echo "<h2>$titre</h2>";
    if (empty($COLLECT_NAS)) {
        echo "<p>Tableau vide</p>";
        return;
    }
    $first_item = reset($COLLECT_NAS); //Récupère le 1er élément, merci le chat j'avais une erreur
    // Vérification si le tableau est vide ou ne contient pas d'éléments valides
    if (!$first_item) {
        echo "<p>Aucun élément valide dans le tableau</p>";
        return;
    }
    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>";
    // En-têtes des colonnes
    foreach ($first_item as $key => $value) {
        echo "<th>$key</th>";
    }
    echo "</tr>";
    //Lignes pour chaque élément
    foreach ($COLLECT_NAS as $item) {
        echo "<tr>";
        foreach ($item as $key => $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }
    echo "</table><br><br>";
}


/**
 * Fonction qui vérifie la correspondance de toutes les métadonnées techniques entre 2 vidéos passées en paramètre
 * Une vidéo est un tableau qui contient les métadonnées techniques d'une vidéo (titre, durée, ...)
 * (pathinfo pour ne pas tenir compte de l'extension)
 */
function verifierCorrespondanceMdtTechVideos($video_1, $video_2){
    
    if (pathinfo($video_1[MTD_TITRE], PATHINFO_FILENAME) == pathinfo($video_2[MTD_TITRE], PATHINFO_FILENAME)
        && $video_1[MTD_FORMAT] == $video_2[MTD_FORMAT]
        && $video_1[MTD_FPS] == $video_2[MTD_FPS]
        && $video_1[MTD_RESOLUTION] == $video_2[MTD_RESOLUTION]
        && $video_1[MTD_DUREE] == $video_2[MTD_DUREE]
        && $video_1[MTD_URI] == $video_2[MTD_URI]) {
        return true;
    }
    else {
        return false;
    }
}


/**
 * Fonction qui vérifie la correspondance des noms des 2 vidéos passées en paramètre
 * On compare les noms des fichiers sans tenir compte de leur extension (video.mp4 = video.mxf)
 * (pathinfo pour ne pas tenir compte de l'extension)
 * On prend cependant compte du chemin du fichier
 */
function verifierCorrespondanceNomsVideos($nomVideo_1, $nomVideo_2) {

    $cheminFichier_1 = pathinfo($nomVideo_1, PATHINFO_DIRNAME);
    $cheminFichier_2 = pathinfo($nomVideo_2, PATHINFO_DIRNAME);

    $nomFichier_1 = pathinfo($nomVideo_1, PATHINFO_FILENAME);
    $nomFichier_2 = pathinfo($nomVideo_2, PATHINFO_FILENAME);

    if ($cheminFichier_1 == $cheminFichier_2 && $nomFichier_1 == $nomFichier_2) {
        return true;
    } else {
        return false;
    }
}


/**
 * Fonction qui permet de comparer le contenu des deux NAS pour trouver les vidéos qui ne sont présentes que dans un seul emplacement
 */
function fonctionReconciliation() {
	// Algorithme qui vérifie la présence des vidéos dans les 2 NAS.
	// Si une vidéo n'est pas présente dans les 2 NAS, une alerte est lancée

	// #RISQUE : Incomprehension sur les spec de la fonction de réconciliation
	// SelectALL en BD pour récupérer tous les noms des vidéos -- Dans les faits on les récupère dans les NAS
	$listeVideos_NAS_1 = [];
	$listeVideos_NAS_2 = [];
	$listeVideos_NAS_1 = recupererNomsVideosNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_RACINE_NAS_PAD, $listeVideos_NAS_1);
	$listeVideos_NAS_2 = recupererNomsVideosNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_RACINE_NAS_ARCH, $listeVideos_NAS_2);

	$listeVideosManquantes = [];
	$listeVideosManquantes = trouverVideosManquantes(NAS_PAD, NAS_ARCH, $listeVideos_NAS_1, $listeVideos_NAS_2, $listeVideosManquantes);

	// #RIQUE : Affichage pas encore implémenté
	//Pour chaque vidéo manquante, afficher un message d'information

    ajouterLog(LOG_SUCCESS, "Fonction de réconciliation effectuée avec succès.");
}


/**
 * Fonction qui permet de rechercher les vidéos présentes dans un NAS mais pas dans l'autre
 * Prend en paramètre les noms des deux NAS, les listes des noms des vidéos des deux NAS et une liste vide de vidéos manquantes.
 * Retourne $listeVideosManquantes valorisée
 */
function trouverVideosManquantes($nomNAS_1, $nomNAS_2, $nomsVideos_NAS1, $nomsVideos_NAS2, $listeVideosManquantes) {
    foreach ($nomsVideos_NAS1 as $key1 => $nomVideoNAS1) {
        $videoManquanteDansNAS2 = true;
        foreach ($nomsVideos_NAS2 as $key2 => $nomVideoNAS2) {

            if (verifierCorrespondanceNomsVideos($nomVideoNAS1, $nomVideoNAS2)) {
				unset($nomsVideos_NAS1[$key1]);
                unset($nomsVideos_NAS2[$key2]);
                $videoManquanteDansNAS2 = false;
                break;
            }
        }
		if ($videoManquanteDansNAS2) {
            $listeVideosManquantes[] = [
                MTD_TITRE => $nomVideoNAS1,
                EMPLACEMENT_MANQUANT => $nomNAS_2
            ];
			unset($nomsVideos_NAS1[$key1]);
        }
    }
    // Ajouter les vidéos restantes dans NAS2 qui ne sont pas dans NAS1
    foreach ($nomsVideos_NAS2 as $nomVideoNAS2Restant) {
        $listeVideosManquantes[] = [
            MTD_TITRE => $nomVideoNAS2Restant,
            EMPLACEMENT_MANQUANT => $nomNAS_1
        ];
    }
    return $listeVideosManquantes;
}

/**
 * Fonction qui permet d'afficher la liste des vidéos manquantes dans un des deux NAS.
 * Prend en paramètre $listeVideosManquantes
 */
function afficherVideosManquantes($listeVideosManquantes) {
    echo "<h2>Tableau des vidéos manquantes :</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
	echo "<tr>";
		echo "<th>".MTD_TITRE."</th>";
		echo "<th>".EMPLACEMENT_MANQUANT."</th>";
    echo "</tr>";
    // Parcours de la liste des vidéos manquantes
    foreach ($listeVideosManquantes as $video) {
		$nomVideo = $video[MTD_TITRE];
        $emplacementManquant = $video[EMPLACEMENT_MANQUANT];
		//Lignes pour chaque élément
		echo "<tr>";
		echo "<td>$nomVideo</td>";
		echo "<td>$emplacementManquant</td>";
		echo "</tr>";
    }
    echo "</table>";
}


/**
 * Fonction qui permet d'ajouter un log dans le fichier de log
 * Prend en paramètre : Type du log et message
 * Si le fichier n'existe pas, le créé
 */
function ajouterLog($typeLog, $message){
    $repertoireLog = URI_FICHIER_LOG;
    $fichierLog = $repertoireLog . NOM_FICHIER_LOG;

    // Vérifier si le fichier log.log existe, sinon le créer
    if (!file_exists($fichierLog)) {
        file_put_contents($fichierLog, "");
    }
    $horodatage = date('Y-m-d H:i:s');
    $log = "[$horodatage] $typeLog : $message" . PHP_EOL;
    $handleFichier = fopen($fichierLog, 'a');
    fwrite($handleFichier, $log);
    fclose($handleFichier);
}

/**
 * Fonction qui permet de trouver le nom de la miniature d'une vidéo
 * Prend en paramètre le nom de la vidéo à trouver
 * Renvoie le nom de la miniature
 */
function trouverNomMiniature($titreVideo) {
    $nomSansExtension = pathinfo($titreVideo, PATHINFO_FILENAME);
    return $nomSansExtension . SUFFIXE_MINIATURE_VIDEO;
}


/**
 * Fonction qui permet de trouver le nom d'une vidéo à partir d'une miniature
 * Prend en paramètre le nom de la miniature pour laquelle in faut trouver la vidéo
 * Renvoie le nom de la vidéo
 */
function trouverNomVideo($titreMiniature) {
    $nomSansExtension = str_replace(SUFFIXE_MINIATURE_VIDEO, '', $titreMiniature);
    return $nomSansExtension . SUFFIXE_VIDEO;
}


/**
 * Fonction qui permet de créer un dossier local sans erreur
 * Prend en paramètre l'URI du dossier à créer, et un booléen qui indique si on créé de manière incrémentale
 * Création incrémentale : si le dossier "nomDossier" existe deja, on créé le dossier "nomDossier(1)"
 */
function creerDossier(&$cheminDossier, $creationIncrementale){
	
	// Vérifie si le dossier existe, sinon le crée
	if (!is_dir($cheminDossier)) {
		if (!(mkdir($cheminDossier, 0777, true))) {
			ajouterLog(LOG_FAIL, "Échec lors de la création du dossier $cheminCourant.");
			exit();
		}
	}
	//Si le dossier n'existe pas, on regarde si on créé de manière incrémentale
	else {
        if ($creationIncrementale) {
            $i = 1;
            $nouveauChemin = $cheminDossier . '(' . $i . ')';
            while (is_dir($nouveauChemin)) {
                $i++;
                $nouveauChemin = $cheminDossier . '(' . $i . ')';
            }
            if (!(mkdir($nouveauChemin, 0777, true))) {
                ajouterLog(LOG_FAIL, "Échec lors de la création du dossier $nouveauChemin.");
                exit();
            }
			//Pour le passage par référence
			$cheminDossier = $nouveauChemin;
        }
    }
}


function fichierEnBase($fichier){
	return false;
}

function insertionCollect_MPEG($COLLECT_MPEG){
	foreach($COLLECT_MPEG as $ligneMetadonneesTechniques){
		insertionDonneesTechniques($ligneMetadonneesTechniques);
	}
}

/**
 * Fonction qui permet de récupérer des URIS et titre de X vidéos situées dans le NAS MPEG
 * Prend en paramètre le nombre d'URIS et titres à récupérer
 * Retourne un tableau d'URIS
 */
function recupererURIEtTitreVideos(){
	$tabURIsEtTitres = getUriNASetTitreMPEG();
	return $tabURIsEtTitres;
}

/**
 * Fonction qui permet de charger une miniature dans l'espace local
 * Prend en paramètre un URI d'un dossier d'un serveur NAS, le titre de la vidéo
 * 	pour laquelle trouver l'URI et les logins FTP
 * Retourne le cheminLocalComplet de la miniature
 */
function chargerMiniature($uriServeurNAS, $titreVideo, $ftp_server, $ftp_user, $ftp_pass){

	//Définition du chemin complet de la miniature
	$miniature = trouverNomMiniature($titreVideo);
	$cheminDistantComplet = $uriServeurNAS . $miniature;

	//Création d'un dossier dans l'espace local
	$nomSansExtension = pathinfo($titreVideo, PATHINFO_FILENAME);
	$cheminDossier = URI_VIDEOS_A_LIRE . $uriServeurNAS;

	// #RISQUE : On peut créer énormément de dossiers similaires. --Problème résolu normalement

	//Pas de création de dossier incrementale
	creerDossier($cheminDossier, false);
	$cheminLocalComplet = $cheminDossier . '/' . $miniature;
	
	$conn_id = connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass);
	telechargerFichier($conn_id, $cheminLocalComplet, $cheminDistantComplet);
    ftp_close($conn_id);

	return $cheminLocalComplet;
	exit();
}

?>