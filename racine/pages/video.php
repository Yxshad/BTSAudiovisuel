<<<<<<< HEAD
<?php 
    session_start();
    require_once '../fonctions/controleur.php';
    $infosVideo = controleurRecupererInfosVideo();
    $idVideo = $infosVideo["idVideo"];
    $mtdTech = $infosVideo["mtdTech"];
    $nomFichier = $infosVideo["nomFichier"];
    $cheminMiniature = $infosVideo["cheminMiniature"];
    $cheminLocal = $infosVideo["cheminLocal"];
    $titreVideo = $infosVideo["titreVideo"];
    $mtdEdito = $infosVideo["mtdEdito"];
?>

=======
<?php session_start(); ?>
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/logo_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/video.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>
    
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />

<?php
<<<<<<< HEAD
    require_once '../ressources/Templates/header.php';
?>
<div class="container">
    <div class="lecteurVideo">
    <video class="player" id="player" playsinline controls data-poster=<?php echo $cheminMiniature; ?>>
        <source src="<?php echo $cheminLocal; ?>" type="video/mp4"/>
    </video>
</div>
    <h1 class="titre"><?php echo $nomFichier; ?></h1>
    <h2><?php echo $titreVideo; ?></h2>
=======
    require '../ressources/Templates/header.php';
    require '../fonctions/fonctions.php';
    require '../fonctions/ftp.php';
    require '../ressources/constantes.php';
    require '../fonctions/ffmpeg.php';

    //Récupération de l'URI NAS de la vidéo
    if (isset($_POST['uriNAS']) && isset($_POST['cheminLocalComplet'])) {
        $uriNAS = $_POST['uriNAS'];
        $cheminLocalComplet = $_POST['cheminLocalComplet'];
    }

    //Téléchargement de la vidéo
        //On récupère le chemin complet de la miniature, on le remplace par celui de la vidéo
        $cheminCompletMiniature = $cheminLocalComplet;
        $miniature = basename($cheminLocalComplet);
        $fichierVideo = trouverNomVideo($miniature);

        //Pour le chemin local, on retire de $cheminLocalComplet le nom du fichier miniature
        $cheminLocal = dirname($cheminLocalComplet);

        $cheminDistantComplet = $uriNAS . $fichierVideo;
        $cheminLocalComplet = $cheminLocal . '/' . $fichierVideo;

        $conn_id = connexionFTP_NAS(NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG);
        telechargerFichier($conn_id, $cheminLocalComplet, $cheminDistantComplet);
        ftp_close($conn_id);
?>

<div class="container">
    <div class="lecteurVideo">
    <video class="player" id="player" playsinline controls data-poster=<?php echo $cheminCompletMiniature; ?>>
        <source src="<?php echo $cheminLocalComplet; ?>" type="video/mp4"/>
    </video>
</div>
    <h1 class="titre">Titre de la video</h1>
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
    <div class="colonnes">
        <div class="colonne-1">
            <p class="description"><?php echo $mtdTech["Description"]; ?></p>
            <p class="meta">
                <strong>Durée : </strong><?php echo $mtdTech["mtd_tech_duree"]; ?>
            </p>
            <p class="meta">
                <strong>Image par secondes : </strong><?php echo $mtdTech["mtd_tech_fps"]; ?> fps
            </p>
            <p class="meta">
                <strong>Résolution : </strong><?php echo $mtdTech["mtd_tech_resolution"]; ?>
            </p>
            <p class="meta">
                <strong>Format : </strong><?php echo $mtdTech["mtd_tech_format"]; ?>
            </p>
            <p class="meta">
                <strong>Projet : </strong><?php echo $mtdEdito["projet"]; ?>
            </p>
            <p class="meta">
                <strong>Professeur : </strong><?php echo $mtdEdito["professeur"]; ?>
            </p>
            <p class="meta">
                <strong>Réalisateur : </strong><?php echo $mtdEdito["realisateur"]; ?>
            </p>
            <p class="meta">
                <strong>Cadreur : </strong><?php echo $mtdEdito["cadreur"]; ?>
            </p>
            <p class="meta">
                <strong>Responsable Son : </strong><?php echo $mtdEdito["responsableSon"]; ?>
            </p>
            
        </div>
        <div class="colonne-2">
<<<<<<< HEAD
            <a href="<?php echo $cheminLocal; ?>" download="<?php echo $video["mtd_tech_titre"]; ?>" class="btnVideo">
=======
            <a href="./bamboulo.mp4" download="bamboulo.mp4" class="btnVideo">
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/download.webp" alt="">
                </div>
                <p>Télécharger</p>
            </a>
            <a href="#" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/antenne.png" alt="">
                </div>
                <p>Diffuser</p>
            </a>
            <a href="formulaire.php?v=<?php echo $idVideo; ?>" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/modif.png" alt="">
                </div>
                <p>Modifier</p>
            </a>
            <a href="#" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/trash.png" alt="">
                </div>
                <p>Supprimer</p>
            </a>
        </div>
    </div>
</div>

<footer>
<?php require '../ressources/Templates/footer.php';?>
</footer>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        initLectureVideo();
    });
<<<<<<< HEAD
</script>
=======
</script>
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
