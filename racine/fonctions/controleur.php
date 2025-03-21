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
            controleurLancerFonctionTransfert();
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
        if ($_POST["action"] == "diffuserVideo" && isset($_POST["URI_COMPLET_NAS_PAD"])) {
            $URI_COMPLET_NAS_PAD = $_POST['URI_COMPLET_NAS_PAD'];
            controleurDiffuserVideo($URI_COMPLET_NAS_PAD);
        }
        if ($_POST["action"] == "supprimerVideo" && isset($_POST["NAS"])) {
            $idVideo = $_POST['idVideo'];
            $NAS = $_POST["NAS"];
            controleurSupprimerVideo($idVideo, $NAS);
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
        if($_POST["action"] == "createDatabaseSave"){
            controleurcreateDBDumpLauncher();
        }
        if($_POST["action"] == "changeWhenToSaveDB"){
            //DATA
            if ($_POST['minute'] == 'NaN') {
                $_POST['minute'] = '*';
            }
            if ($_POST['heure'] == 'NaN') {
                $_POST['heure'] = '*';
            }
            $minute = $_POST['minute'] ?? '*';
            $heure = $_POST['heure'] ?? '*';
            $jour = $_POST['day'] ?? '*';
            $month = $_POST['month'] ?? '*';
            controleurChangeDBDumpLauncher($minute, $heure, '*', $month, $jour);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['path']) && isset($_POST['menuType'])) {
            $path = $_POST['path'];
            $menuType = $_POST['menuType'];
        
            switch ($menuType) {
                case 'ESPACE_LOCAL':
                    echo controleurArborescence($path, ESPACE_LOCAL);
                    break;
                case 'PAD':
                    echo controleurArborescence($path, NAS_PAD);
                    break;
                case 'ARCH':
                    echo controleurArborescence($path, NAS_ARCH);
                    break;
                default:
                    echo "Type de menu non reconnu.";
                    break;
            }
            exit;
        }
        if($_POST["action"] == "mettreAJourParametres"){
            controleurMettreAJourParametres();
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
    $mtdRoles = assemblerRolesEtParticipantsDeVideo($idVideo);
    $promotion = $video["promotion"];
    $description = $video["description"];

    // Ajout des URIS des 2 NAS avec gestion d'erreur
    $URIS = [
        "URI_NAS_PAD" => !empty($video["URI_NAS_PAD"]) ? URI_RACINE_NAS_PAD . $video["URI_NAS_PAD"] : "",
        "URI_NAS_ARCH" => !empty($video["URI_NAS_ARCH"]) ? URI_RACINE_NAS_ARCH . $video["URI_NAS_ARCH"] : ""
    ];

    $URIEspaceLocal = '/stockage/' . $video['URI_STOCKAGE_LOCAL'];
    $nomFichierMiniature = trouverNomMiniature($video['mtd_tech_titre']);
    $cheminMiniatureComplet = $URIEspaceLocal . $nomFichierMiniature;
    $cheminVideoComplet = $URIEspaceLocal . $nomFichier;
    return [
        "idVideo" => $idVideo,
        "mtdTech" => $video,
        "nomFichier" => recupererNomFichierSansExtension($nomFichier),
        "cheminMiniatureComplet" => $cheminMiniatureComplet,
        "cheminVideoComplet" => $cheminVideoComplet,
        "titreVideo" => $titreVideo,
        "description" => $description,
        "mtdEdito" => $mtdEdito,
        "mtdRoles" => $mtdRoles,
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
        isset($_POST["promotion"]) || 
        isset($_POST["projet"]) || 
        isset($_POST["description"]) || 
        isset($_POST["roles"]) 
    ) {
        
        // Récupération des champs obligatoires
        $profReferent = $_POST["profReferent"];
        $promotion = $_POST["promotion"];
        $projet = $_POST["projet"];
        $description = $_POST["description"];

        // Récupération des rôles dynamiques
        $roles = isset($_POST["roles"]) ? $_POST["roles"] : [];
        miseAJourMetadonneesVideo(
            $idVideo, 
            $profReferent, 
            $promotion, 
            $projet, 
            $description,
            $roles
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
 * \fn controleurVerifierAcces($accesAVerifier)
 * \brief Vérifie l'autorisation d'accès à une fonctionnalité (diffuser, administrer, ...) d'un utilisateur
 * \param accesAVerifier - un type d'accès
 * \return true si l'accès est présent en session
 */
function controleurVerifierAcces($accesAVerifier){
    return ( isset($_SESSION["autorisation"][$accesAVerifier]) && $_SESSION["autorisation"][$accesAVerifier] == 1 );
}

/**
 * \fn controleurVerifierAccesPage($accesAVerifier)
 * \brief Vérifie l'autorisation d'accès de l'utilisateur et le renvoie sur la page d'accueil si accès non-autorisé. 
 * \param accesAVerifier - un type d'accès à une fonctionnalité (diffuser, administrer, ...)
 */
function controleurVerifierAccesPage($accesAVerifier){
    if(!controleurVerifierAcces($accesAVerifier)){
        header('Location: home.php');
    }
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
        exit();
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
        ajouterLog(LOG_SUCCESS, "Diffusion de la vidéo " . $URI_COMPLET_NAS_PAD . " effectuée avec succès.");
        echo "1";
        exit();
    }
    else{
        echo "La vidéo a déjà été diffusée.";
        exit();
    }
}

/**
 * \fn controleurAfficherLogs($filename, $lines)
 * \brief Fonction qui permet de récupérer les logs dans un fichier de log
 * \param filename - Le nom du fichier à récupérer
 * \param lines - Nombre de lignes à récupérer
 * \return les logs du fichier choisi
 */
function controleurAfficherLogs($filename, $lines) {
    if (!file_exists($filename)) {
        return ["Fichier introuvable."];
    }

    $file = fopen($filename, "rb"); // Mode binaire pour compatibilité Windows/Linux
    if (!$file) {
        return ["Impossible d'ouvrir le fichier."];
    }

    $buffer = [];
    while (!feof($file)) {
        $line = fgets($file);
        if ($line !== false) {
            $buffer[] = rtrim($line); // Supprime les retours à la ligne inutiles
            if (count($buffer) > $lines) {
                array_shift($buffer);
            }
        }
    }
    fclose($file);

    return $buffer; // Retourne les logs sous forme de tableau
}


function controleurReconciliation() {
    $listeVideos_NAS_1 = recupererNomsVideosNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_RACINE_NAS_PAD, []);
    $listeVideos_NAS_2 = recupererNomsVideosNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_RACINE_NAS_ARCH, []);

    ob_start(); // Capture la sortie pour éviter les erreurs de header
    echo "<h2>Vidéos présentes sur " . NAS_PAD . ":</h2>";
    echo "<ul>";
    foreach ($listeVideos_NAS_1 as $video) {
        echo "<li>" . htmlspecialchars($video) . "</li>";
    }
    echo "</ul>";

    echo "<h2>Vidéos présentes sur " . NAS_ARCH . ":</h2>";
    echo "<ul>";
    foreach ($listeVideos_NAS_2 as $video) {
        echo "<li>" . htmlspecialchars($video) . "</li>";
    }
    echo "</ul>";
    
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
        $projetIntitule = getProjetIntitule($listeVideos[0]["projet"]);
        $listeVideosFormatees = [];
        // Vérifier si $listeVideos est un tableau valide
       if (is_array($listeVideos) && ($projetIntitule != NULL)) {
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
function controleurSupprimerVideo($idVideo, $NAS){
    $video = getURISVideo($idVideo);
    if ($NAS == "local") {
        $allFiles = scandir(URI_RACINE_STOCKAGE_LOCAL . $video['URI_STOCKAGE_LOCAL']);
        foreach($allFiles as $file){
            if(! is_dir($file)){
            unlink(URI_RACINE_STOCKAGE_LOCAL . $video['URI_STOCKAGE_LOCAL'] . $file);
            }
        }
        rmdir(URI_RACINE_STOCKAGE_LOCAL . $video['URI_STOCKAGE_LOCAL']);
        supprimerVideoDeBD($idVideo);
        echo "1"; //on renvoit 1 quand tout se passe bien
        exit(0);  
    } elseif($NAS == "ARCH"){
        $conn_id = connexionFTP_NAS(NAS_ARCH, LOGIN_NAS_ARCH_SUP, PASSWORD_NAS_ARCH_SUP);
        $lienVideo = $video['URI_NAS_ARCH'] . $video['mtd_tech_titre'];
        if($video['URI_NAS_ARCH']!=null){
            ftp_delete($conn_id, $lienVideo);
            supprimerVideoNASARCH($idVideo);
            ajouterLog(LOG_SUCCESS, "La vidéo ". $video['mtd_tech_titre'] . " dans le NAS $NAS a été supprimée avec succès");
            echo "1"; //on renvoit 1 quand tout se passe bien
        }
        else{
            ajouterLog(LOG_FAIL, "La vidéo". $video['mtd_tech_titre'] . " n'existe pas dans le NAS $NAS");
            echo "La vidéo n'est pas dans le NAS $NAS";
        }
        exit(0); 
    }elseif($NAS == "PAD"){
        $conn_id = connexionFTP_NAS(NAS_PAD, LOGIN_NAS_PAD_SUP, PASSWORD_NAS_PAD_SUP);
        $lienVideo = $video['URI_NAS_PAD'] . $video['mtd_tech_titre'];
        if($video['URI_NAS_PAD'] != null){
            ftp_delete($conn_id, $lienVideo);
            supprimerVideoNASPAD($idVideo);
            ajouterLog(LOG_SUCCESS, "La vidéo ". $video['mtd_tech_titre'] . " dans le NAS $NAS a été supprimée avec succès");
            echo "1"; //on renvoit 1 quand tout se passe bien
        }
        else{
            echo "La vidéo n'est pas dans le NAS $NAS";
            ajouterLog(LOG_FAIL, "La vidéo ". $video['mtd_tech_titre'] . " n'existe pas dans le NAS $NAS");
        }
        exit(0); 
    }   
}

/**
 * \fn controleurRecupererAutorisationsProfesseurs()
 * \brief Récupère les autorisations des professeurs
 */
function controleurRecupererAutorisationsProfesseurs(){
    return recupererAutorisationsProfesseurs();
}

/**
 * \fn controleurMettreAJourAutorisations($prof, $colonne, $etat)
 * \brief Appelle la fonction met à jour les autorisations des utilisateurs
 * \param prof - utilisateur (professeur) dont on meut modifier les informations
 * \param colonne - Type de l'autorisation à modifier (modifier, diffuser, ...)
 * \param etat - Booléen : 1 si case cochée
 */
function controleurMettreAJourAutorisations($prof, $colonne, $etat){
    mettreAJourAutorisations($prof, $colonne, $etat);
    ajouterLog(LOG_INFORM, "Mise à jour des autorisations du professeur " . $prof);
}

/**
 * \fn controleurChangeDBDumpLauncher
 * \brief Controleur pour changer l'heure de sauvegarde
 */
function controleurChangeDBDumpLauncher($minute = '*', $heure = '*', $annee = '*', $mois = '*', $jour = '*'){
    changeWhenToSaveDB($minute, $heure, $annee, $mois, $jour);
}


/**
 * \fn controleurArborescence($directory, $ftp_server)
 * \brief Lance la fonction qui scan le répertoire local
 * \param directory - Racine de l'endroit qu'on veut scanner
 * \param ftp_server - Serveur dans lequel la fonction va chercher les fichiers
 */
function controleurArborescence($directory, $ftp_server){
    if($ftp_server == NAS_PAD || $ftp_server == NAS_ARCH){
        if ($ftp_server == NAS_PAD) {
            $conn_id = connexionFTP_NAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD);
        } else {
            $conn_id = connexionFTP_NAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH);
        }
        $fichiers_NAS = ftp_nlist($conn_id, $directory);
        foreach ($fichiers_NAS as $item) {
            if ($item !== '.' && $item !== '..' && $item !== '.gitkeep') {
                $path = $directory . '/' . $item;
                if (@ftp_chdir($conn_id, $path)) {
                    afficherDossier($path, $item);
                } elseif (isVideo($item)) {
                    $directory_id = substr($directory, 1) . '/';
                    $item_id = forcerExtensionMP4($item);
                    $id = getIdVideoURIetTitre($directory_id, $item_id, $ftp_server);
                    afficherVideo($path, $item, $id);
                } else {
                    afficherFichier($path, $item);
                }
            }
        }
        ftp_close($conn_id);
    } else {
        $itemsLocal = scandir($directory);
        foreach ($itemsLocal as $item) {
            if ($item !== '.' && $item !== '..' && $item !== '.gitkeep') {
                $path = $directory . '/' . $item;
                if (is_dir($path)) {
                    afficherDossier($path, $item);
                } elseif (isVideo($item)) {
                    preg_match("/(?<=stockage\/).*/", $directory, $matches);
                    $directory_id = $matches[0] . "/";
                    $id = getIdVideoURIetTitre($directory_id, $item, $ftp_server);
                    afficherVideo($path, $item, $id);
                } else {
                    afficherFichier($path, $item);
                }
            }
        }
    }    
}

/**
 * \fn controleucontroleurLancerFonctionTransfertrSupprimerVideo()
 * \brief Lance la fonction de transfert via une commande exec
 */
function controleurLancerFonctionTransfert(){
    exec('php /var/www/html/fonctions/scriptFonctionTransfert.php > /dev/null 2>&1 &');
    //#RISQUE : Afficher un message d'erreur si le script a renvoyé un output d'erreur.
}

 /**
 * \fn controleurcreateDBDumpLauncher()
 * \brief Appelle la fonction qui créé la sauvegarde de la base de données
 */
function controleurcreateDBDumpLauncher(){
    $exitCode = createDatabaseSave();
    echo $exitCode;
}

function controleurMettreAJourParametres(){
    mettreAJourParametres();
    header("Refresh:0");
    exit();
}

function chargerPopup($nouveauTitre = null, $nouveauTexte = null){
    require_once '../ressources/Templates/popup.php';
}

?>
