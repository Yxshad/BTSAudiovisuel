<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/video.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>
    
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />

<?php
    require_once '../ressources/Templates/header.php';
    require_once '../fonctions/fonctions.php';
    require_once '../fonctions/ftp.php';
    require_once '../ressources/constantes.php';
    require_once '../fonctions/ffmpeg.php';
    require_once '../fonctions/modele.php';

    //Récupération de l'URI NAS de la vidéo
    if (isset($_GET['v'])) {
        $id = $_GET['v'];
    }
    $video = fetchAll("SELECT * FROM Media WHERE id=$id;");
    $video = $video[0];
    $nomFichier = $video["mtd_tech_titre"];
    
    //charge la minitature
    $miniature = $nomFichier . "_miniature.png";
    $cheminMiniature = URI_VIDEOS_A_LIRE . $video["URI_NAS_MPEG"] . $miniature;

    //prépare la video
    $cheminLocal = URI_VIDEOS_A_LIRE . $video["URI_NAS_MPEG"] . $video["mtd_tech_titre"];
    $cheminDistant = URI_RACINE_NAS_MPEG . $video["URI_NAS_MPEG"] . $video["mtd_tech_titre"]; 
    $conn_id = connexionFTP_NAS(NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG);
    telechargerFichier($conn_id, $cheminLocal, $cheminDistant);
    ftp_close($conn_id);

    //prépare titre
    $titreVideo = recupererTitreVideo($video["mtd_tech_titre"]);

    //prépare metadonnées editoriales
    $meta = getMetadonneesEditorialesVideo($video);
?>
<div class="container">
    <div class="lecteurVideo">
    <video class="player" id="player" playsinline controls data-poster=<?php echo $cheminMiniature; ?>>
        <source src="<?php echo $cheminLocal; ?>" type="video/mp4"/>
    </video>
</div>
    <h1 class="titre"><?php echo $nomFichier; ?></h1>
    <h2><?php echo $titreVideo; ?></h2>
    <div class="colonnes">
        <div class="colonne-1">
            <p class="description"><?php echo $video["Description"]; ?></p>
            <p class="meta">
                <strong>Durée : </strong><?php echo $video["mtd_tech_duree"]; ?>
            </p>
            <p class="meta">
                <strong>Image par secondes : </strong><?php echo $video["mtd_tech_fps"]; ?> fps
            </p>
            <p class="meta">
                <strong>Résolution : </strong><?php echo $video["mtd_tech_resolution"]; ?>
            </p>
            <p class="meta">
                <strong>Format : </strong><?php echo $video["mtd_tech_format"]; ?>
            </p>
            <p class="meta">
                <strong>Projet : </strong><?php echo $meta["projet"]; ?>
            </p>
            <p class="meta">
                <strong>Professeur : </strong><?php echo $meta["professeur"]; ?>
            </p>
            <p class="meta">
                <strong>Réalisateur : </strong><?php echo $meta["realisateur"]; ?>
            </p>
            <p class="meta">
                <strong>Cadreur : </strong><?php echo $meta["cadreur"]; ?>
            </p>
            <p class="meta">
                <strong>Responsable Son : </strong><?php echo $meta["responsableSon"]; ?>
            </p>
            
           
        </div>
        <div class="colonne-2">
            <a href="<?php echo $cheminLocal; ?>" download="<?php echo $video["mtd_tech_titre"]; ?>" class="btnVideo">
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
            <a href="formulaire.php?v=<?php echo $id; ?>" class="btnVideo">
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
</script>
