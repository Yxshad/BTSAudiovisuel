<?php

/**
 * \file controleur.php
 * \version 1.1
 * \brief Controleur servant d'intermédiaire entre les pages et les fonctions/méthodes exécutées
 * en fond
 * \author Nicolas Conguisti
 */

require_once "../ressources/constantes.php";
require_once "ftp.php";
require_once "ffmpeg.php";
require_once "modele.php";
require_once "fonctions.php";

/**
 * \fn checkHeader()
 * \brief Regarde le Header de la page pour exécuter les fonctions correspondantes
 */
function checkHeader(){
    if (isset($_POST["action"])) {
      if ($_POST["action"] == "scanDossierDecoupeVideo") {
          header('Content-Type: application/json');
          scanDossierDecoupeVideo(); 
          exit();
      }
      if ($_POST["action"] == "lancerConversion") {
          fonctionTransfert();
      }
      if ($_POST["action"] == "ModifierMetadonnees") {
          $idVideo = $_POST['idVideo'];
          controleurPreparerMetadonnees($idVideo);
      }
      if ($_POST["action"] == "connexionUtilisateur") {
          $loginUser = $_POST['loginUser'];
          $passwordUser = $_POST['passwordUser'];
          controleurIdentifierUtilisateur($loginUser, $passwordUser);
      }
      if ($_POST["action"] == "diffuserVideo") {
          $URI_COMPLET_NAS_PAD = $_POST['URI_COMPLET_NAS_PAD'];
          controleurDiffuserVideo($URI_COMPLET_NAS_PAD);
      }
      if ($_POST["action"] == "supprimerVideo") {
        $idVideo = $_POST['idVideo'];
        $URI_STOCKAGE_LOCAL = $_POST['URI_STOCKAGE_LOCAL'];
        controleurSupprimerVideo($idVideo, $URI_STOCKAGE_LOCAL);
      }
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === "declencherReconciliation") {
        ob_start(); // Démarrer la capture de sortie pour éviter les erreurs de header
        controleurReconciliation();
        ob_end_clean(); // Nettoyer la sortie tamponnée
    
        // Redirection AVANT d'envoyer du contenu
        header("Location: ?tab=reconciliation");
        exit();
      }
      if ($_POST["action"] == "mettreAJourAutorisation") {
            controleurMettreAJourAutorisations($_POST["prof"], $_POST["colonne"], $_POST["etat"]);
      }
  }
}
checkHeader();

/**
 * \fn controleurRecupererTitreIdVideo()
 * \brief Fonction qui permet de récupérer des URIS, titres et id de X vidéos situées dans le stockage local
 * \return un tableau d'URIS/titres/id et cheminMiniature
 */
function controleurRecupererTitreIdVideo() {
    $tabURIS = getTitreURIEtId(NB_VIDEOS_PAR_SWIPER);
    $videos = [];
    if (!$tabURIS) {
        return $videos;
    }
    ajouterLog(LOG_INFORM, "Récupération des informations à afficher sur la page d'accueil.");
    foreach ($tabURIS as $video) {
        $id = $video['id'];
        $URIEspaceLocal = '/stockage/' .$video['URI_STOCKAGE_LOCAL'];
        $titreSansExtension = recupererNomFichierSansExtension($video['mtd_tech_titre']);

        $titreVideo = recupererTitreVideo($video["mtd_tech_titre"]);

        $nomFichierMiniature = trouverNomMiniature($video['mtd_tech_titre']);
        $cheminMiniatureComplet = $URIEspaceLocal . $nomFichierMiniature;
        
        $videos[] = [
            'id' => $id,
            'URIEspaceLocal' => $URIEspaceLocal,
            'titre' => $titreSansExtension,
            'titreVideo' => $titreVideo,
            'cheminMiniatureComplet' => $cheminMiniatureComplet
        ];
    }
    return $videos;
}


/**
 * \fn controleurRecupererInfosVideo()
 * \brief Fonction qui permet de récupérer les métadonnées techniques liées à une vidéo
 * \return tableau de métadonnées techniques
 */
function controleurRecupererInfosVideo() {
    $idVideo = controleurVerifierVideoParametre();
    $video = getInfosVideo($idVideo);
    if ($video == null) {
        header('Location: erreur.php?code=404');
        exit();
    }
    ajouterLog(LOG_INFORM, "Chargement des informations de la vidéo n° $idVideo");
    $nomFichier = $video["mtd_tech_titre"];
    $titreVideo = recupererTitreVideo($video["mtd_tech_titre"]);
    $mtdEdito = getMetadonneesEditorialesVideo($video);
    $promotion = $video["promotion"];

    //Ajout des URIS des 2 NAS avec gestion d'erreur
    $URIS = [];
    if (!empty($video["URI_NAS_PAD"])) {
        $URIS["URI_NAS_PAD"] = URI_RACINE_NAS_PAD . $video["URI_NAS_PAD"];
    }
    else{
        $URIS["URI_NAS_PAD"] = "";
    }
    if (!empty($video["URI_NAS_ARCH"])) {
        $URIS["URI_NAS_ARCH"] = URI_RACINE_NAS_ARCH . $video["URI_NAS_ARCH"];
    }
    else{
        $URIS["URI_NAS_ARCH"] = "";
    }

    $URIEspaceLocal = '/stockage/' .$video['URI_STOCKAGE_LOCAL'];
    $nomFichierMiniature = trouverNomMiniature($video['mtd_tech_titre']);
    $cheminMiniatureComplet = $URIEspaceLocal . $nomFichierMiniature;

    $cheminVideoComplet = $URIEspaceLocal . $nomFichier;
    return [
        "idVideo" => $idVideo,
        "mtdTech" => $video,
        "nomFichier" => $nomFichier,
        "cheminMiniatureComplet" => $cheminMiniatureComplet,
        "cheminVideoComplet" => $cheminVideoComplet,
        "titreVideo" => $titreVideo,
        "mtdEdito" => $mtdEdito,
        "promotion" => $promotion,
        "URIS" => $URIS,
    ];
}

/**
 * \fn controleurPreparerMetadonnees($idVideo)
 * \brief Prépare les métadonnées à mettre dans la vidéo une fois qu'il y a modification
 * \param idVideo - L'Id de la vidéo
 */
function controleurPreparerMetadonnees($idVideo){
    if (
        isset($_POST["profReferent"]) ||
        isset($_POST["realisateur"]) || 
        isset($_POST["promotion"]) || 
        isset($_POST["projet"]) || 
        isset($_POST["cadreur"]) || 
        isset($_POST["responsableSon"])
    ) {
        // Récupération des champs entrés dans le formulaire
        $profReferent = $_POST["profReferent"];
        $realisateur = $_POST["realisateur"];
        $promotion = $_POST["promotion"];
        $projet = $_POST["projet"];
        $cadreur = $_POST["cadreur"];
        $responsableSon = $_POST["responsableSon"];
        miseAJourMetadonneesVideo(
            $idVideo, 
            $profReferent, 
            $realisateur, 
            $promotion, 
            $projet, 
            $cadreur, 
            $responsableSon
        );
    }
}


/**
 * \fn controleurRecupererListeProfesseurs()
 * \brief Renvoie la liste des professeurs
 * \return resultat - la liste des professeurs
 */
function controleurRecupererListeProfesseurs() {
    $listeProfesseurs = getAllProfesseurs();
    $resultat = array_map(function($item) {
        return $item['nom'] . " " . $item['prenom'];
    }, $listeProfesseurs);
    return $resultat;
}

/**
 * \fn controleurVerifierVideoParametre(){
 * \brief Vérifie qu'on a la bonne vidéo passée en paramètre quand on clique dessus
 * \return idVideo - l'id de la vidéo
 */
function controleurVerifierVideoParametre(){
    // Vérifie si le paramètre 'v' est présent dans l'URL
    if (!isset($_GET['v']) || empty($_GET['v']) || !is_numeric($_GET['v'])) {
        header('Location: erreur.php?code=404');
        exit();
    }
    $idVideo = intval($_GET['v']);

    return $idVideo;
}


/**
 * \fn controleurIdentifierUtilisateur($loginUser, $passwordUser)
 * \brief Vérifie les autorisations d'accès de l'utilisateur et le renvoie sur la page correspondante en fonction
 * \param loginUser - identifiant de connexion de l'utilisateur
 * \param passwordUser - mot de passe de connexion de l'utilisateur
 */
function controleurIdentifierUtilisateur($loginUser, $passwordUser){
    
    $passwordHache = hash('sha256', $passwordUser);

    //regarder si login + mdp en base, récupérer le rôle si trouvé. Sinon, message d'erreur
    $role = connexionProfesseur($loginUser, $passwordHache);

    if($role == false){
        ajouterLog(LOG_FAIL, "Erreur d'authentification pour l'utilisateur $loginUser.");
    }
    else{
        ajouterLog(LOG_INFORM, "L'utilisateur $loginUser s'est connecté.");
        $_SESSION["loginUser"] = $loginUser;
        $_SESSION["role"] = $role["role"];

        //on récupère les droits depuis la base et on les insèrent dans la session
        $_SESSION["autorisation"] = recupererAutorisationsProfesseur($_SESSION["loginUser"]);
        header('Location: home.php');
    }
   
}


/**
 * \fn controleurVerifierAcces($rolesAutorises)
 * \brief Vérifie les autorisations d'accès de l'utilisateur et le booléen en fonction du droit l'accès demandé
 * \param $accesAVerifier - Rôles que l'on souhaite modifier
 */
function controleurVerifierAcces($accesAVerifier){
    return ( isset($_SESSION["autorisation"][$accesAVerifier]) && $_SESSION["autorisation"][$accesAVerifier] == 1 );
}

/**
 * \fn controleurDiffuserVideo($URI_COMPLET_NAS_PAD)
 * \brief Fonction qui permet de diffuser une vidéo dont l'id est passé en paramètre sur le NAS DIFF.
 * \param URI_COMPLET_NAS_PAD - Le chemin d'accès à la vidéo du NAS PAD
 */
function controleurDiffuserVideo($URI_COMPLET_NAS_PAD){

    if(!empty($URI_COMPLET_NAS_PAD)){
        //On récupère met le nom à .mxf
        $nomFichier = forcerExtensionMXF($URI_COMPLET_NAS_PAD);
        $cheminFichier = dirname($URI_COMPLET_NAS_PAD) . '/';
        $URI_COMPLET_NAS_PAD = $cheminFichier . $nomFichier;

        $cheminFichierDesination = URI_VIDEOS_A_DIFFUSER . $nomFichier;
        $cheminFichierSource = $URI_COMPLET_NAS_PAD;

        $conn_id = connexionFTP_NAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD);
        telechargerFichier($conn_id, $cheminFichierDesination, $cheminFichierSource);
        ftp_close($conn_id);
    }
    else{
        // #RISQUE : La vidéo n'est pas présente dans le NAS PAD, il faudra juste supprimer le bouton
        return;
    }

    //Inversion des URIs, la source devient la destination
    $cheminTempCopieFichierDestination = $cheminFichierDesination;
    $cheminFichierDesination = $cheminFichierSource;
    $cheminFichierSource = $cheminTempCopieFichierDestination;

    //Création des dossiers dans le NAS de diffusion
    $dossierVideo = dirname($cheminFichierDesination);
    $conn_id = connexionFTP_NAS(NAS_DIFF, LOGIN_NAS_DIFF, PASSWORD_NAS_DIFF);
    creerDossierFTP($conn_id, $dossierVideo);
    ftp_close($conn_id);

    $isExportSucces = exporterFichierVersNASAvecCheminComplet($cheminFichierSource, $cheminFichierDesination, NAS_DIFF, LOGIN_NAS_DIFF, PASSWORD_NAS_DIFF);

    //Supprimer le fichier du dossier videoADiffuser
    unlink($cheminFichierSource);

    if($isExportSucces){
        // #RISQUE : Message de validation à l'utilisateur
        return;
    }
    else{
        // #RISQUE : Message d'erreur
        return;
    }
}

function controleurAfficherLogs($filename, $lines) {
    if (!file_exists($filename)) {
        return ["Fichier introuvable."];
    }
    $file = fopen($filename, "r");
    if (!$file) {
        return ["Impossible d'ouvrir le fichier."];
    }
    $buffer = [];
    while (!feof($file)) {
        $buffer[] = fgets($file);
        if (count($buffer) > $lines) {
            array_shift($buffer);
        }
    }
    fclose($file);
    return array_filter($buffer);
}


function controleurReconciliation() {
    $listeVideos_NAS_1 = recupererNomsVideosNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_RACINE_NAS_PAD, []);
    $listeVideos_NAS_2 = recupererNomsVideosNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_RACINE_NAS_ARCH, []);

    ob_start(); // Capture la sortie pour éviter les erreurs de header
    echo "<h2>Vidéos présentes sur " . NAS_PAD . ":</h2>";
    echo "<pre>" . print_r($listeVideos_NAS_1, true) . "</pre>";

    echo "<h2>Vidéos présentes sur " . NAS_ARCH . ":</h2>";
    echo "<pre>" . print_r($listeVideos_NAS_2, true) . "</pre>";

    $listeVideosManquantes = trouverVideosManquantes(NAS_PAD, NAS_ARCH, $listeVideos_NAS_1, $listeVideos_NAS_2, []);
    afficherVideosManquantes($listeVideosManquantes);

    ajouterLog(LOG_SUCCESS, "Fonction de réconciliation effectuée avec succès.");
    $_SESSION['reconciliation_result'] = ob_get_clean(); // Stocker la sortie pour l'afficher après redirection
}

function controleurRecupererDernierProjet(){
    //recuperer dernière video avec projet
    $id = recupererProjetDerniereVideoModifiee();
    // Vérifier si $id est valide avant de continuer
    if ($id !== false && $id !== null) {
        $listeVideos = recupererUriTitreVideosMemeProjet($id);

        $listeVideosFormatees = [];
        // Vérifier si $listeVideos est un tableau valide
        if (is_array($listeVideos) && getProjetIntitule($listeVideos[0]["projet"] != NULL)) {
            foreach ($listeVideos as $key => $video) {
                $titreSansExtension = recupererNomFichierSansExtension($video['mtd_tech_titre']);
                $listeVideosFormatees[$key]["projet"] = getProjetIntitule($video["projet"]);
                $listeVideosFormatees[$key]["titre"] = $titreSansExtension;
                $listeVideosFormatees[$key]["titreVideo"] = recupererTitreVideo($video["mtd_tech_titre"]);
                $listeVideosFormatees[$key]["cheminMiniatureComplet"] = '/stockage/' . $video['URI_STOCKAGE_LOCAL'] . trouverNomMiniature($video['mtd_tech_titre']);
                $listeVideosFormatees[$key]["id"] = $video["id"];
            }
        } else {
            $listeVideosFormatees = []; // Assurer que $listeVideos est bien un tableau
        }
    } else {
        $listeVideosFormatees = [];
    }

    return $listeVideosFormatees;
}

function getLastLines($filename, $lines) {
    if (!file_exists($filename)) {
        return ["Fichier introuvable."];
    }

    // Utilisation de `tail` si disponible
    if (function_exists('shell_exec')) {
        $output = shell_exec("tail -n " . escapeshellarg($lines) . " " . escapeshellarg($filename));
        return explode("\n", trim($output));
    }

    // Alternative en PHP si `shell_exec` est désactivé
    $file = fopen($filename, "r");
    if (!$file) {
        return ["Impossible d'ouvrir le fichier."];
    }

    $buffer = [];
    while (!feof($file)) {
        $buffer[] = fgets($file);
        if (count($buffer) > $lines) {
            array_shift($buffer);
        }
    }
    fclose($file);
    return array_filter($buffer);
}

function controleurRecupererAutorisationsProfesseurs(){
    return recupererAutorisationsProfesseurs();
}

function controleurMettreAJourAutorisations($prof, $colonne, $etat){
    mettreAJourAutorisations($prof, $colonne, $etat);

function controleurRecupererDernieresVideosTransfereesSansMetadonnees(){
    //recuperer dernières videos sans métadonnées
    $listeVideos = recupererDernieresVideosTransfereesSansMetadonnees(NB_VIDEOS_HISTORIQUE_TRANSFERT);
    // Vérifier si $id est valide avant de continuer
    if ($listeVideos !== false && $listeVideos !== null) {
        $listeVideosFormatees = [];
        // Vérifier si $listeVideos est un tableau valide
        if (is_array($listeVideos)) {
            foreach ($listeVideos as $key => $video) {
                $listeVideosFormatees[$key]["id"] = $video["id"];
                $listeVideosFormatees[$key]["date_creation"] = $video["date_creation"];
                $listeVideosFormatees[$key]["mtd_tech_titre"] = $video["mtd_tech_titre"];
            }
        } else {
            $listeVideosFormatees = []; // Assurer que $listeVideos est bien un tableau
        }
    } else {
        $listeVideosFormatees = [];
    }
    return $listeVideosFormatees;
}

/**
 * \fn controleurSupprimerVideo($idVideo)
 * \brief "Supprime" la vidéo du MAM
 * \param idVideo - Id de la vidéo à supprimer
 */
function controleurSupprimerVideo($idVideo){
    $video = getInfosVideo($idVideo);
    $allFiles = scandir(URI_RACINE_STOCKAGE_LOCAL . $video['URI_STOCKAGE_LOCAL']);
    foreach($allFiles as $file){
        if(! is_dir($file)){
        unlink(URI_RACINE_STOCKAGE_LOCAL . $video['URI_STOCKAGE_LOCAL'] . $file);
        }
    }
    rmdir(URI_RACINE_STOCKAGE_LOCAL . $video['URI_STOCKAGE_LOCAL']);
    supprimerVideoDeBD($idVideo);
    header('Location: home.php');
    exit();
}
?>