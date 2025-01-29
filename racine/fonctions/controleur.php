<?php
require_once "../ressources/constantes.php";
require_once "ftp.php";
require_once "ffmpeg.php";
require_once "modele.php";
require_once "fonctions.php";

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
        $cheminLocal = $_POST['cheminLocal'];
        controleurDiffuserVideo($cheminLocal);
    }
}

/**
 * Fonction qui permet de récupérer des URIS, titres et id de X vidéos situées dans le NAS MPEG
 * Prend en paramètre le nombre d'URIS et titres à récupérer
 * Retourne un tableau d'URIS/titres/id et cheminMiniature
 */
function controleurRecupererTitreIdVideo() {
    $tabURIS = getUriNASetTitreMPEGEtId(10);
    $videos = [];
    if (!$tabURIS) {
        return $videos;
    }
    ajouterLog(LOG_INFORM, "Récupération des informations à afficher sur la page d'accueil.");
    foreach ($tabURIS as $video) {
        $id = $video['id'];
        $URIEspaceLocal = '/stockage/' .$video['URI_STOCKAGE_LOCAL'];
        $titreSansExtension = recupererNomFichierSansExtension($video['mtd_tech_titre']);

        $nomFichierMiniature = trouverNomMiniature($video['mtd_tech_titre']);
        $cheminMiniatureComplet = $URIEspaceLocal . $nomFichierMiniature;
        
        $videos[] = [
            'id' => $id,
            'URIEspaceLocal' => $URIEspaceLocal,
            'titre' => $titreSansExtension,
            'cheminMiniatureComplet' => $cheminMiniatureComplet
        ];
    }
    return $videos;
}

function controleurTelechargerFichier($cheminDistantVideo, $nomFichier){
    // Télécharge la vidéo depuis le serveur FTP
    $cheminLocalComplet = URI_VIDEOS_A_LIRE . $cheminDistantVideo . $nomFichier;
    $cheminDistantComplet = URI_RACINE_NAS_MPEG . $cheminDistantVideo . $nomFichier;
    $conn_id = connexionFTP_NAS(NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG);
    telechargerFichier($conn_id, $cheminLocalComplet, $cheminDistantComplet);
    ftp_close($conn_id);
    return $cheminLocalComplet;
}

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
    ];
}

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

function controleurRecupererListeProfesseurs() {
    $listeProfesseurs = getAllProfesseurs();
    $resultat = array_map(function($item) {
        return $item['nom'] . " " . $item['prenom'];
    }, $listeProfesseurs);
    return $resultat;
}

function controleurVerifierVideoParametre(){
    // Vérifie si le paramètre 'v' est présent dans l'URL
    if (!isset($_GET['v']) || empty($_GET['v']) || !is_numeric($_GET['v'])) {
        header('Location: erreur.php?code=404');
        exit();
    }
    $idVideo = intval($_GET['v']);

    return $idVideo;
}

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

        header('Location: home.php');
        exit();
    }
}

// Si l'utilisateur n'a pas les autorisations pour accèder à la page, il est alors renvoyé sur la page d'accueil
// $rolesAutorises est une liste des roles autorisé
function controleurVerifierAcces($rolesAutorises){
    if ((!isset($_SESSION["role"])) || (!in_array($_SESSION["role"], $rolesAutorises))) {
        header('Location: home.php');
        exit();
    }
}

/**
 * Fonction qui permet de diffuser une vidéo dont l'id est passé en paramètre sur le NAS DIFF.
 */
function controleurDiffuserVideo($cheminLocalComplet){
    // Récupération de la vidéo en qualité optimale
    // - Récupération des URIS en base avec $ID
    // - Téléchargement du fichier dans videoADiffuser
    
    // #RISQUE : Changement des répertoires du NAS de diffusion

    $nomFichier = basename($cheminLocalComplet);
    $cheminDistantComplet = URI_RACINE_NAS_DIFF . $nomFichier;
    $exportSucces = exporterFichierVersNASAvecCheminComplet($cheminLocalComplet, $cheminDistantComplet, NAS_DIFF, LOGIN_NAS_DIFF, PASSWORD_NAS_DIFF);
    if($exportSucces){
        // #RISQUE : Message de validation à l'utilisateur
        return;
    }
    else{
        //Message d'erreur
        return;
    }
}

?>