<?php

require_once "../ressources/constantes.php";
require_once "./ftp.php";
require_once "./ffmpeg.php";
require_once "./modele.php";
require_once "./fonctions.php";

fonctionTransfert();

/**
 * \fn fonctionTransfert()
 * \brief Fonction principale qui execute le transfert des fichiers des NAS ARCH et PAD vers le stockage local
 * Alimente aussi la base de données avec les métadonnées techniques des vidéos transférées 
 */
function fonctionTransfert(){
    
	ajouterLog(LOG_INFORM, "Lancement de la fonction de transfert.");
	$COLLECT_PAD = [];
	$COLLECT_ARCH = [];
	$COLLECT_STOCK_LOCAL = [];
	//-----------------------   répertoire NAS_PAD      ------------------------
	$COLLECT_PAD = recupererCollectNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_VIDEOS_A_ANALYSER, $COLLECT_PAD, URI_RACINE_NAS_PAD);
	ajouterLog(LOG_INFORM, "Récupération des vidéos du NAS PAD. " . count($COLLECT_PAD) . " fichiers trouvés.");
	//-----------------------   répertoire NAS_ARCH      ------------------------
	$COLLECT_ARCH = recupererCollectNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_VIDEOS_A_ANALYSER, $COLLECT_ARCH, URI_RACINE_NAS_ARCH);
	ajouterLog(LOG_INFORM, "Récupération des vidéos du NAS ARCH. " . count($COLLECT_ARCH) . " fichiers trouvés.");
	//Remplir $COLLECT_STOCK_LOCAL
	$COLLECT_STOCK_LOCAL = remplirCOLLECT_STOCK_LOCAL($COLLECT_PAD, $COLLECT_ARCH, $COLLECT_STOCK_LOCAL);
	//Alimenter le Stockage local
	ajouterLog(LOG_INFORM, "Alimentation du stockage local avec " . count($COLLECT_STOCK_LOCAL) . " fichiers." );
	$COLLECT_STOCK_LOCAL = alimenterStockageLocal($COLLECT_STOCK_LOCAL);
	//Mettre à jour la base avec $COLLECT_STOCK_LOCAL
	ajouterLog(LOG_INFORM, "Insertion des informations dans la base de données.");
	insertionCOLLECT_STOCK_LOCAL($COLLECT_STOCK_LOCAL);
    ajouterLog(LOG_SUCCESS, "Fonction de transfert effectuée avec succès.");
}


/**
 * \fn recupererCollectNAS($ftp_server, $ftp_user, $ftp_pass, $URI_VIDEOS_A_ANALYSER, $COLLECT_NAS, $URI_NAS_RACINE)
 * \brief Fonction qui récupère l'ensemble des métadonnées techniques des vidéos d'un NAS (collectPAD ou collectARCH)
 * On télécharge les vidéos dans un $URI_VIDEOS_A_ANALYSER si celles-ci ne sont pas présentes dans la BD
 * - On remplit CollectNAS pour chaque vidéo
 * - On vide le répertoire local $URI_VIDEOS_A_ANALYSER
 * \param ftp_server - Le nom du serveur ftp auquel on veut accéder
 * \param ftp_user - Nom de l'utilisateur qui se connecte sur le serveur ftp
 * \param ftp_pass - Mot de passe de l'utilisateur se connectant sur le serveur ftp
 * \param URI_VIDEOS_A_ANALYSER - - URI de la vidéo qui doit être analysée
 * \param COLLECT_NAS - Toutes les vidéos collectées sur les NAS
 * \param URI_NAS_RACINE - URI de la vidéo sur le NAS racine
 */
function recupererCollectNAS($ftp_server, $ftp_user, $ftp_pass, $URI_VIDEOS_A_ANALYSER, $COLLECT_NAS, $URI_NAS_RACINE){
	
	$conn_id = connexionFTP_NAS($ftp_server, $ftp_user, $ftp_pass);

	// Lister les fichiers sur le serveur FTP
	$fichiersNAS = listerFichiersCompletFTP($conn_id, $URI_NAS_RACINE);

	foreach ($fichiersNAS as $cheminFichierComplet) {

        $nomFichier = basename($cheminFichierComplet);
		$cheminFichier = dirname($cheminFichierComplet) . '/';

		if($cheminFichier != "./"){
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
    }
	ftp_close($conn_id);
	return $COLLECT_NAS;
}


/**
 * \fn remplirCOLLECT_STOCK_LOCAL(&$COLLECT_PAD, &$COLLECT_ARCH, $COLLECT_STOCK_LOCAL)
 * \brief Fonction qui remplit $COLLECT_STOCK_LOCAL avec les metadonnées de chaque vidéo présentes dans $COLLECT_PAD ET $COLLECT_ARCH
 * - Vide les vidéos de $COLLECT_PAD et $COLLECT_ARCH qui sont ajoutées dans $COLLECT_STOCK_LOCAL (passage les collections par référence)
 * Traite les vidéos isolées
 * \param COLLECT_PAD - Vidéos collectées sur le NAS PAD
 * \param COLLECT_ARCH - Vidéos collectées sur le NAS Archivage
 * \param COLLECT_STOCK_LOCAL - Tableau qui sera rempli de données à l'issue de la fonction
 * \return COLLECT_STOCK_LOCAL - Tableau contenant toutes les vidéos qui doivent être stockées sur le serveur local
*/
function remplirCOLLECT_STOCK_LOCAL(&$COLLECT_PAD, &$COLLECT_ARCH, $COLLECT_STOCK_LOCAL){

	foreach ($COLLECT_PAD as $key_PAD => $ligneCollect_PAD) {
		foreach ($COLLECT_ARCH as $key_ARCH => $ligneCollect_ARCH) {
			//Si les deux $ligneCollect correspondent exactement (hors URI) (pathinfo pour ne pas tenir compte de l'extension)
			if (verifierCorrespondanceMdtTechVideos($ligneCollect_PAD, $ligneCollect_ARCH)){
                
				//Remplir $COLLECT_STOCK_LOCAL
				$COLLECT_STOCK_LOCAL[] = [
					MTD_TITRE => $ligneCollect_ARCH[MTD_TITRE],
					MTD_URI_NAS_PAD => $ligneCollect_PAD[MTD_URI],
					MTD_URI_NAS_ARCH => $ligneCollect_ARCH[MTD_URI],
					MTD_FORMAT => $ligneCollect_PAD[MTD_FORMAT],
					MTD_FPS => $ligneCollect_PAD[MTD_FPS],
					MTD_RESOLUTION => $ligneCollect_PAD[MTD_RESOLUTION],
					MTD_DUREE => $ligneCollect_PAD[MTD_DUREE],
                    MTD_DUREE_REELLE => $ligneCollect_PAD[MTD_DUREE_REELLE]
				];

                ajouterLog(LOG_CRITICAL, implode(", ", $COLLECT_STOCK_LOCAL));

				//Retirer $ligneCollect_ARCH et $ligneCollect_PAD de COLLECT_ARCH et $COLLECT_PAD
				unset($COLLECT_PAD[$key_PAD]);
                unset($COLLECT_ARCH[$key_ARCH]);
				break;
			}
		}
	}
	//Traitement des fichiers isolés
	foreach ($COLLECT_PAD as $key_PAD => $ligneCollect_PAD) {
		$COLLECT_STOCK_LOCAL[] = [
			MTD_TITRE => $ligneCollect_PAD[MTD_TITRE],
			MTD_URI_NAS_PAD => $ligneCollect_PAD[MTD_URI],
			MTD_URI_NAS_ARCH => null,
			MTD_FORMAT => $ligneCollect_PAD[MTD_FORMAT],
			MTD_FPS => $ligneCollect_PAD[MTD_FPS],
			MTD_RESOLUTION => $ligneCollect_PAD[MTD_RESOLUTION],
			MTD_DUREE => $ligneCollect_PAD[MTD_DUREE],
            MTD_DUREE_REELLE => $ligneCollect_PAD[MTD_DUREE_REELLE]
		];
		unset($COLLECT_PAD[$key_PAD]);
	}
	foreach ($COLLECT_ARCH as $key_ARCH => $ligneCollect_ARCH) {
		$COLLECT_STOCK_LOCAL[] = [
			MTD_TITRE => $ligneCollect_ARCH[MTD_TITRE],
			MTD_URI_NAS_PAD => null,
			MTD_URI_NAS_ARCH => $ligneCollect_ARCH[MTD_URI],
			MTD_FORMAT => $ligneCollect_ARCH[MTD_FORMAT],
			MTD_FPS => $ligneCollect_ARCH[MTD_FPS],
			MTD_RESOLUTION => $ligneCollect_ARCH[MTD_RESOLUTION],
			MTD_DUREE => $ligneCollect_ARCH[MTD_DUREE],
            MTD_DUREE_REELLE => $ligneCollect_ARCH[MTD_DUREE_REELLE]
		];
		unset($COLLECT_ARCH[$key_ARCH]);
	}
	return $COLLECT_STOCK_LOCAL;
}

/**
 * \fn alimenterStockageLocal($COLLECT_STOCK_LOCAL)
 * \brief Alimente le stockage en local des différentes vidéos récupérées dans les autres NAS
 * Les logs sont mis en commentaire en cas d'erreur, ils simplifieront le débogage
 * \param COLLECT_STOCK_LOCAL - Collection des vidéos à stocker sur le serveur local
 * \return COLLECT_STOCK_LOCAL - Liste des vidéos qui ont été implémentées dans le stockage local
 */
function alimenterStockageLocal($COLLECT_STOCK_LOCAL) {
    $tailleDuTableau = count($COLLECT_STOCK_LOCAL);
    $elementsParProcessus = ceil($tailleDuTableau / NB_MAX_PROCESSUS_TRANSFERT);

    $PIDsEnfants = [];

    for ($i = 0; $i < NB_MAX_PROCESSUS_TRANSFERT; $i++) {

        $pid = pcntl_fork();

        if ($pid == -1) {
            ajouterLog(LOG_CRITICAL, "Erreur critique sur le multithreading.");
            die('Duplication impossible');
        } elseif ($pid) {
            // Processus parent : on enregistre le PID du fils
            //ajouterLog(LOG_INFORM, "Processus parent - Fils lancé avec PID : $pid");
            $PIDsEnfants[] = $pid;
        } else {
            // **PROCESSUS ENFANT**
            $debut = $i * $elementsParProcessus;
            $fin = min(($i + 1) * $elementsParProcessus, $tailleDuTableau);

            for ($j = $debut; $j < $fin; $j++) {
                $video = $COLLECT_STOCK_LOCAL[$j];
                //ajouterLog(LOG_INFORM, "Le fils PID " . getmypid() . " travaille sur la vidéo : " . $video[MTD_TITRE]);

                // **Téléchargement**
                $cheminFichierDestination = URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION . $video[MTD_TITRE];

                if (!empty($video[MTD_URI_NAS_ARCH])) {
                    $conn_id = connexionFTP_NAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH);
                    $cheminFichierSource = $video[MTD_URI_NAS_ARCH] . $video[MTD_TITRE];
                } elseif (!empty($video[MTD_URI_NAS_PAD])) {
                    $conn_id = connexionFTP_NAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD);
                    $cheminFichierSource = $video[MTD_URI_NAS_PAD] . $video[MTD_TITRE];
                } else {
                    ajouterLog(LOG_FAIL, "Erreur, la vidéo " . $video[MTD_TITRE] . " n'est présente dans aucun NAS.");
                    exit(0);
                }

                telechargerFichier($conn_id, $cheminFichierDestination, $cheminFichierSource);
                ftp_close($conn_id);

                // **Découpe / Conversion / Fusion**
                traiterVideo($video[MTD_TITRE], $video[MTD_DUREE_REELLE]);
                fusionnerVideo($video[MTD_TITRE]);

                $video[MTD_TITRE] = forcerExtensionMp4($video[MTD_TITRE]);

                // **Export dans stockage local**
                $cheminCompletFichierSource = URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD . $video[MTD_TITRE];
                $cheminFichierDestination = URI_RACINE_STOCKAGE_LOCAL . ($video[MTD_URI_NAS_ARCH] ?? $video[MTD_URI_NAS_PAD]);

                $dossierVideo = $cheminFichierDestination . PREFIXE_DOSSIER_VIDEO . recupererNomFichierSansExtension($video[MTD_TITRE]) . '/';
                creerDossier($cheminFichierDestination, false, false);
                creerDossier($dossierVideo, false);

                copy($cheminCompletFichierSource, $dossierVideo . $video[MTD_TITRE]);

                // **Miniature**
                $miniature = genererMiniature($cheminCompletFichierSource, $video[MTD_DUREE]);
                copy(URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD . $miniature, $dossierVideo . $miniature);

                // **Nettoyage**
                unlink($cheminCompletFichierSource);
                unlink(URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD . $miniature);

                // **Stockage de l'URI**
                if (strpos($dossierVideo, URI_RACINE_STOCKAGE_LOCAL) === 0) {
                    $dossierVideo = substr($dossierVideo, strlen(URI_RACINE_STOCKAGE_LOCAL));
                }
                $video[MTD_URI_STOCKAGE_LOCAL] = $dossierVideo;

                // Écrire les modifications dans un fichier temporaire (accessible par le père)
                $tempFile = sys_get_temp_dir() . '/video_' . getmypid() . '_' . $j . '.tmp';
                file_put_contents($tempFile, serialize($video));

                //ajouterLog(LOG_INFORM, "Le fils PID " . getmypid() . " a terminé la vidéo : " . $video[MTD_TITRE]);
            }

            //ajouterLog(LOG_INFORM, "Le fils PID " . getmypid() . " termine.");
            exit(0);
        }
    }

    //ajouterLog(LOG_CRITICAL, "Partie reservée au processus père : attente des fils");
    while (count($PIDsEnfants) > 0) {
        //ajouterLog(LOG_CRITICAL, count($PIDsEnfants));
        $pidTermine = pcntl_waitpid(-1, $status);
        if ($pidTermine > 0) {
            // Supprime le PID terminé du tableau
            $PIDsEnfants = array_diff($PIDsEnfants, [$pidTermine]);
            //ajouterLog(LOG_INFORM, "Père : Processus fils PID $pidTermine terminé.");

            // Lire les fichiers temporaires créés par le processus enfant
            foreach (glob(sys_get_temp_dir() . '/video_' . $pidTermine . '_*.tmp') as $tempFile) {
                $video = unserialize(file_get_contents($tempFile));
                $index = intval(substr(basename($tempFile), strrpos(basename($tempFile), '_') + 1, -4));
                $COLLECT_STOCK_LOCAL[$index] = $video;
                unlink($tempFile);
            }
        }
        //ajouterLog(LOG_CRITICAL, print_r($PIDsEnfants, true));
    }
    ajouterLog(LOG_INFORM, "Tous les processus fils ont terminé. Le processus de transfert des vidéos est terminé.");
    return $COLLECT_STOCK_LOCAL;
}

/**
 * \fn insertionCOLLECT_STOCK_LOCAL($COLLECT_STOCK_LOCAL)
 * \brief Lance l'insertion des métadonnées techniques en bd de toutes les vidéos ajoutées dans le serveur
 * \param COLLECT_STOCK_LOCAL - Collection des vidéos à ajouter sur le serveur
 */
function insertionCOLLECT_STOCK_LOCAL($COLLECT_STOCK_LOCAL){
	foreach($COLLECT_STOCK_LOCAL as $ligneMetadonneesTechniques){
		insertionDonneesTechniques($ligneMetadonneesTechniques);
	}
}

?>