<?php


/**
 * Fonction principale qui execute le transfert des fichiers des NAS ARCH et PAD vers le NAS MPEG
 * Alimente aussi la base de données avec les métadonnées techniques des vidéos transférées 
 */
function fonctionTransfert(){
	ajouterLog(LOG_INFORM, "Lancement de la fonction de transfert.");
	$COLLECT_PAD = [];
	$COLLECT_ARCH = [];
	$COLLECT_MPEG = [];
	//-----------------------   répertoire NAS_PAD      ------------------------
	$COLLECT_PAD = recupererCollectNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_VIDEOS_A_ANALYSER, $COLLECT_PAD, URI_RACINE_NAS_PAD);
	ajouterLog(LOG_INFORM, "Récupération des vidéos du NAS PAD. " . count($COLLECT_PAD) . " fichiers trouvés.");
	//-----------------------   répertoire NAS_ARCH      ------------------------
	$COLLECT_ARCH = recupererCollectNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_VIDEOS_A_ANALYSER, $COLLECT_ARCH, URI_RACINE_NAS_ARCH);
	ajouterLog(LOG_INFORM, "Récupération des vidéos du NAS ARCH. " . count($COLLECT_ARCH) . " fichiers trouvés.");
	//Remplir $COLLECT_MPEG
	$COLLECT_MPEG = remplirCollect_MPEG($COLLECT_PAD, $COLLECT_ARCH, $COLLECT_MPEG);
	//Alimenter le NAS MPEG
	ajouterLog(LOG_INFORM, "Alimentation du NAS MPEG avec " . count($COLLECT_MPEG) . " fichiers." );
	$COLLECT_MPEG = alimenterNAS_MPEG($COLLECT_MPEG);
	//Mettre à jour la base avec $COLLECT_MPEG
	ajouterLog(LOG_INFORM, "Insertion des informations dans la base de données.");
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
	$fichiersNAS = listerFichiersCompletFTP($conn_id, $URI_NAS_RACINE);

	foreach ($fichiersNAS as $cheminFichierComplet) {

        $nomFichier = basename($cheminFichierComplet);
		$cheminFichier = dirname($cheminFichierComplet) . '/';	
		$extensionFichier = recupererExtensionFichier($nomFichier);

		//Si le fichier est une vidéo
		if ($nomFichier !== '.' && $nomFichier !== '..'
			&& ($extensionFichier == 'mxf' || $extensionFichier == 'mp4')) {

			// Si le fichier n'est pas présent en base
			if (!verifierFichierPresentEnBase($cheminFichier, $nomFichier, $extensionFichier)) {

				//RECUPERATION VIA LECTURE FTP
				$listeMetadonneesVideos = recupererMetadonneesVideoViaFTP($ftp_server, $ftp_user, $ftp_pass, $cheminFichier, $nomFichier);

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


	/*
	// calcul du nombre de processus 
	$numSublists = (int) ceil((int) shell_exec('nproc') / 3);
	if ($numSublists < 1) {
		// force la création d'au moins une liste
		$numSublists = 1; 
	}

	$totalElements = count($COLLECT_MPEG);
	$sublistSize = (int) ceil($totalElements / $numSublists);

	// se répartis les vidéos dans les listes disponibles
	$sublists = [];
	for ($i = 0; $i < $numSublists; $i++) {
		$startIndex = $i * $sublistSize; // Starting index for the current sublist
		$sublists[] = array_slice($COLLECT_MPEG, $startIndex, $sublistSize); // Slice and add the sublist
	}

	$processus = [];

	foreach($sublists as $list){
		$pid = pcntl_fork();

		if ($pid == -1) {
			ajouterLog(LOG_FAIL, "Erreur, impossible de créer un processus.");
		} else if ($pid == 0){

			ajouterLog(LOG_INFORM, "Création du processus n° " . getmypid());
			$string = json_encode($list);
			ajouterLog(LOG_INFORM, "Le processus n° " . getmypid() . " va s'occuper des vidéos " . $string);
			
			//traiter vidéo
			foreach($list as &$video){

			}
			sleep(rand(2, 5));

			ajouterLog(LOG_INFORM, "Le processus n° " . getmypid() . " a fini sa tache!");
			exit(0);
		} else{
			$processus[$pid] = $pid;
			ajouterLog(LOG_INFORM, "Le processus parent va attendre le processus n° ". $pid);
		}
		
	}

	// attente de la fin des enfants
	$status = false;
	foreach($processus as $enfant){
		do {
			$status = pcntl_wifexited($status);
			sleep(1);
		} while ($status == false);
	}
	ajouterLog(LOG_INFORM, "Fin de l'attente");
	*/

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
		} elseif($video[MTD_URI_NAS_PAD] != null && $video[MTD_URI_NAS_PAD] != ""){
			$conn_id = connexionFTP_NAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD);
			$fichierSource = $video[MTD_URI_NAS_PAD] . $video[MTD_TITRE];
			telechargerFichier($conn_id, $fichierDesination, $fichierSource);
			ftp_close($conn_id);
			$URI_NAS = $video[MTD_URI_NAS_PAD];
		} else{
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
		$cheminDestination = URI_RACINE_NAS_MPEG . $URI_NAS;
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
		
		//On retire la racine du NAS MPEG
		if (strpos($dossierVideo, URI_RACINE_NAS_MPEG) == 0) {
			$dossierVideo = substr($dossierVideo, strlen(URI_RACINE_NAS_MPEG));
		}
		
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
function verifierCorrespondanceMdtTechVideos($donneesVideo1, $donneesVideo2){
    
    if (pathinfo($donneesVideo1[MTD_TITRE], PATHINFO_FILENAME) == pathinfo($donneesVideo2[MTD_TITRE], PATHINFO_FILENAME)
        && $donneesVideo1[MTD_FORMAT] == $donneesVideo2[MTD_FORMAT]
        && $donneesVideo1[MTD_FPS] == $donneesVideo2[MTD_FPS]
        && $donneesVideo1[MTD_RESOLUTION] == $donneesVideo2[MTD_RESOLUTION]
        && $donneesVideo1[MTD_DUREE] == $donneesVideo2[MTD_DUREE]
        && $donneesVideo1[MTD_URI] == $donneesVideo2[MTD_URI]) {
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
function verifierCorrespondanceNomsVideos($cheminFichierComplet1, $cheminFichierComplet2) {

    $cheminFichier1 = pathinfo($cheminFichierComplet1, PATHINFO_DIRNAME);
    $cheminFichier2 = pathinfo($cheminFichierComplet2, PATHINFO_DIRNAME);

    $nomFichier1 = pathinfo($cheminFichierComplet1, PATHINFO_FILENAME);
    $nomFichier2 = pathinfo($cheminFichierComplet2, PATHINFO_FILENAME);

    if ($cheminFichier1 == $cheminFichier2 && $nomFichier1 == $nomFichier2) {
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
	// Il faudra pouvoir comparer un fichier et ses infos dans la base de données

	$listeVideosNAS_1 = [];
	$listeVideosNAS_2 = [];
	$listeVideosNAS_1 = recupererNomsVideosNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_RACINE_NAS_PAD, $listeVideosNAS_1);
	$listeVideosNAS_2 = recupererNomsVideosNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_RACINE_NAS_ARCH, $listeVideosNAS_2);

	$listeVideosManquantes = [];
	$listeVideosManquantes = trouverVideosManquantes(NAS_PAD, NAS_ARCH, $listeVideosNAS_1, $listeVideosNAS_2, $listeVideosManquantes);

	// #RIQUE : Affichage pas encore implémenté
	//Pour chaque vidéo manquante, afficher un message d'information

    ajouterLog(LOG_SUCCESS, "Fonction de réconciliation effectuée avec succès.");
}


/**
 * Fonction qui permet de rechercher les vidéos présentes dans un NAS mais pas dans l'autre
 * Prend en paramètre les noms des deux NAS, les listes des noms des vidéos des deux NAS et une liste vide de vidéos manquantes.
 * Retourne $listeVideosManquantes valorisée
 */
function trouverVideosManquantes($nomNAS_1, $nomNAS_2, $nomsVideosNAS_1, $nomsVideosNAS_2, $listeVideosManquantes) {
    foreach ($nomsVideosNAS_1 as $key1 => $nomVideoNAS1) {
        $videoManquanteDansNAS2 = true;
        foreach ($nomsVideosNAS_2 as $key2 => $nomVideoNAS2) {

            if (verifierCorrespondanceNomsVideos($nomVideoNAS1, $nomVideoNAS2)) {
				unset($nomsVideosNAS_1[$key1]);
                unset($nomsVideosNAS_2[$key2]);
                $videoManquanteDansNAS2 = false;
                break;
            }
        }
		if ($videoManquanteDansNAS2) {
            $listeVideosManquantes[] = [
                MTD_TITRE => $nomVideoNAS1,
                EMPLACEMENT_MANQUANT => $nomNAS_2
            ];
			unset($nomsVideosNAS_1[$key1]);
        }
    }
    // Ajouter les vidéos restantes dans NAS2 qui ne sont pas dans NAS1
    foreach ($nomsVideosNAS_2 as $nomVideoNAS2Restant) {
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
function trouverNomMiniature($nomFichierVideo) {
	$nomFichierSansExtension = recupererNomFichierSansExtension($nomFichierVideo);
    return $nomFichierSansExtension . SUFFIXE_MINIATURE_VIDEO;
}


/**
 * Fonction qui permet de trouver le nom d'une vidéo à partir d'une miniature
 * Prend en paramètre le nom de la miniature pour laquelle in faut trouver la vidéo
 * Renvoie le nom de la vidéo
 */
function trouverNomVideo($nomFichierMiniature) {
    $nomFichierSansExtension = str_replace(SUFFIXE_MINIATURE_VIDEO, '', $nomFichierMiniature);
    return $nomFichierSansExtension . SUFFIXE_VIDEO;
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

/**
 * Fonction qui véfifie la présence l'un fichier dans la base de données (dans URI_NAS_MPEG)
 * Prend en paramètre le chemin du fichier et son nom
 * Retourne true si le fichier est présent, false sinon
 */
function verifierFichierPresentEnBase($cheminFichier, $nomFichier){
	$cheminFichierNAS_MPEG = trouverCheminNAS_MPEGVideo($cheminFichier, $nomFichier);
	
	// Forcer l'extension à .mp4 (si des vidéos sont présentes en .mxf)
	$nomFichier = forcerExtensionMp4($nomFichier);

	$videoPresente = verifierPresenceVideoNAS_MPEG($cheminFichierNAS_MPEG, $nomFichier);
	return $videoPresente;
}

/**
 * Fonction qui permet de récupérer le chemin d'un fichier dans le NAS MPEG à partir du chemin dans un autre NAS
 * prend en paramètre le chemin d'un fichier situé dans le NAS PAD ou ARCH
 * Retourne le chemin du fichier dans le NAS MPEG
 */
function trouverCheminNAS_MPEGVideo($cheminFichier, $nomFichier){
	$nomFichierSansExtension = recupererNomFichierSansExtension($nomFichier);
	$cheminFichierNAS_MPEG = $cheminFichier . PREFIXE_DOSSIER_VIDEO . $nomFichierSansExtension . '/';
	return $cheminFichierNAS_MPEG;
}


function insertionCollect_MPEG($COLLECT_MPEG){
	foreach($COLLECT_MPEG as $ligneMetadonneesTechniques){
		insertionDonneesTechniques($ligneMetadonneesTechniques);
	}
}

/*
* Fonction qui permet à la page transferts.php de savoir quels videos sont en train de se faire découper
* Ne prend aucun paramètre
* Retourne une liste avec les noms des vidéos en train de se faire découper
*/
function scanDossierDecoupeVideo() {
    $listeVideoDownload = array_diff(scandir(URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION), ['.', '..','.gitkeep']);
    $listeVideoDecoupage = array_diff(scandir(URI_VIDEOS_A_CONVERTIR_EN_COURS_DE_CONVERSION), ['.', '..','.gitkeep']);
    $listeVideoConversion = array_diff(scandir(URI_VIDEOS_A_UPLOAD_EN_COURS_DE_CONVERSION), ['.', '..','.gitkeep']);
    $listeVideoUpload = array_diff(scandir(URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD), ['.', '..','.gitkeep']);
	
    $listeVideoDownload = array_map(function($e) { return substr($e, 0, -4); }, $listeVideoDownload);
    $listeVideoDecoupage = array_map(function($e) { return substr($e, 0, -10); }, $listeVideoDecoupage);
    $listeVideoConversion = array_map(function($e) { return substr($e, 0, -10); }, $listeVideoConversion);
    $listeVideoUpload = array_map(function($e) { return substr($e, 0, -4); }, $listeVideoUpload);

    $listeVideo = array_unique(array_merge($listeVideoDownload, $listeVideoDecoupage, $listeVideoConversion, $listeVideoUpload));

    $result = [];
    foreach ($listeVideo as $video) {
        if (in_array($video, $listeVideoUpload)) {
            $status = "En cours d'upload";
        } elseif (in_array($video, $listeVideoConversion)) {
            $status = "En cours de conversion";
        } elseif (in_array($video, $listeVideoDecoupage)) {
            $status = "En cours de découpe";
        } else {
            $status = "En cours de téléchargement";
        }
        $result[] = [
            'nomVideo' => $video,
            'poidsVideo' => 'XX mb',
            'status' => $status
        ];
    }
    echo json_encode($result);
}


/**
 * Fonction qui permet de charger une miniature dans l'espace local
 * Prend en paramètre un URI d'un dossier d'un serveur NAS, le titre de la vidéo
 * 	pour laquelle trouver l'URI et les logins FTP
 * Retourne le cheminFichierLocalComplet de la miniature
 */
function chargerMiniature($URIServeurNAS, $nomFichierVideo, $ftp_server, $ftp_user, $ftp_pass){

	//Définition du chemin complet de la miniature
	$nomFichierMiniature = trouverNomMiniature($nomFichierVideo);
	$cheminFichierDistantComplet = $URIServeurNAS . $nomFichierMiniature;

	//Création d'un dossier dans l'espace local
	$cheminDossier = URI_VIDEOS_A_LIRE . $URIServeurNAS;

	//Pas de création de dossier incrementale
	creerDossier($cheminDossier, false);
	$cheminFichierLocalComplet = $cheminDossier . '/' . $nomFichierMiniature;
	
	$conn_id = connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass);
	telechargerFichier($conn_id, $cheminFichierLocalComplet, $cheminFichierDistantComplet);
    ftp_close($conn_id);

	return $cheminFichierLocalComplet;
}

/*
*	Fonction qui retourne le titre de la vidéo
*   Prend en paramètre le nom d'un fichier et retourne le titre sans l'année, le projet et l'extension
*   Si le nom du fichier ne contient pas le bon format, retourne le nom du fichier passé en paramètre
*/
function recupererTitreVideo($nomFichier){
	$titreVideo = [];
    if (preg_match("/^[^_]*_[^_]*_(.*)(?=\.)/", $nomFichier, $titreVideo)) {
        if (isset($titreVideo[1]) && !empty($titreVideo[1])) {
            return $titreVideo[1];
        }
    }
	else{
		//Si le fichier a un nom particulier, on retourne son nom sans extension
		$nomFichierSansExtension = recupererNomFichierSansExtension($nomFichier);
	}
    return $nomFichierSansExtension;
}

function recupererExtensionFichier($nomFichier){
	return substr(pathinfo($nomFichier, PATHINFO_EXTENSION), -3);
}

function recupererNomFichierSansExtension($nomFichier){
	return pathinfo($nomFichier, PATHINFO_FILENAME);
}

function forcerExtensionMp4($nomFichier){
	$nomFichierSansExtension = recupererNomFichierSansExtension($nomFichier);
	return $nomFichierSansExtension . '.mp4';
}

/*
* Fonction qui permet de modifier les métadonnées éditoriales d'une vidéo
* Prend en paramètre l'id de la vidéo, le nom d'un réalisateur, l'année de la promotion, le nom du projet, 
* le nom de l'acteur 1, le role de l'acteur 1, le nom de l'acteur 2 et le role de l'acteur 2
*/

function miseAJourMetadonneesVideo(
    $idVid, 
	$profReferent, 
	$realisateur, 
	$promotion, 
	$projet, 
	$cadreur, 
	$responsableSon){

		// #RISQUE : Une seule requête d'insertion sur des rôles multiples. Là c'est criminel.
		//A voir au moment de l'ajout de multiples personnes pour un même rôle

	if (!$profReferent == "") {
		assignerProfReferent($idVid, $profReferent);
	}
	if (!$realisateur == "") {
		assignerRealisateur($idVid, $realisateur);
	}
	if (!$promotion == "") {
		assignerPromotion($idVid, $promotion);
	}
	if (!$projet == "") {
		assignerProjet($idVid, $projet);
	}
	if (!$cadreur == "") {
		assignerCadreur($idVid, $cadreur);
	}
	if (!$responsableSon == "") {
		assignerResponsable($idVid, $responsableSon);
	}
	ajouterLog(LOG_SUCCESS, "Modification des métadonnées éditoriales de la vidéo n° $idVid.");
}

//récupère toutes les metadonneesEditoriales de la vidéo à partir de son id
function getMetadonneesEditorialesVideo($video){

	$projet = getProjetIntitule($video["projet"]);
	$nomPrenom = getProfNomPrenom($video["professeurReferent"]);
	$nomPrenom = implode(" ", $nomPrenom);
	$eleve = getParticipants($video["id"]); 
	
	$mtdEdito = [
		"projet" => $projet,
		"professeur" => $nomPrenom,
		"realisateur" => $eleve[0],
		"cadreur" => $eleve[1],
		"responsableSon" => $eleve[2]
	];

	return $mtdEdito;
}
?>