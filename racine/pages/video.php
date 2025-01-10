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
    <div class="colonnes">
        <div class="colonne-1">
            <p class="description">Lorem ipsum</p>
            <p class="meta">15 fps, 1920x1080, 16:9</p>
            <?php $i = 0;
            while($i < 3){ //tant qu'on trouve des metadonnées editoriales ?>
                <p>Acteur : José</p>
                <?php $i++;
            } ?>
        </div>
        <div class="colonne-2">
            <a href="./bamboulo.mp4" download="bamboulo.mp4" class="btnVideo">
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
            <a href="formulaire.php" class="btnVideo">
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