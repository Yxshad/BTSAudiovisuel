<?php 
session_start();
require_once '../fonctions/controleur.php';
$tabVideos = controleurRecupererTitreIdVideo();
$tabDernierProjet = controleurRecupererDernierProjet();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/favicon_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <!--<link href="../ressources/Style/menuFiltres.css" rel="stylesheet">-->
    <link href="../ressources/Style/menuArbo.css" rel="stylesheet">
    <link href="../ressources/Style/home.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link href="../ressources/lib/Swiper/swiper-bundle.min.css" rel="stylesheet">
    <script src="../ressources/lib/Swiper/swiper-bundle.min.js"></script>

<?php require_once '../ressources/Templates/header.php'; ?>

<?php //require_once '../ressources/Templates/menuFiltres.php'; ?>
<?php require_once '../ressources/Templates/menuArbo.php'; ?>

<div class="container">
    <div class="sliderVideo">
        <h2>Vos vidéos</h2>
        <div class="swiperVideo">
            <div class="swiper-wrapper">
                <?php
                    foreach ($tabVideos as $video) {
                        $id = $video['id'];
                        $titre = $video['titre'];
                        $titreVideo = $video['titreVideo'];
                        $cheminMiniatureComplet = $video['cheminMiniatureComplet'];
                        echo "<div class='swiper-slide'>";
                            echo "<a href='video.php?v=$id'>";
                                echo "<div class='miniature'>";
                                    echo "<img src='$cheminMiniatureComplet' alt='Miniature de la vidéo' class='imageMiniature'/>";
                                echo "</div>";
                                echo "<h3>$titre</h3>";
                                echo "<h4>$titreVideo</h4>";
                            echo "</a>";
                        echo "</div>";
                    }
                ?>
            </div>
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
    
    <?php if (!empty($tabDernierProjet)) { ?>
        <div class="sliderVideoProjet">
        <h2><?php echo $tabDernierProjet[0]["projet"]; ?></h2>
        <div class="swiperVideo">
            <div class="swiper-wrapper">
                <?php
                    foreach ($tabDernierProjet as $video) {
                        $id = $video['id'];
                        $titre = $video['titre'];
                        $titreVideo = $video['titreVideo'];
                        $cheminMiniatureComplet = $video['cheminMiniatureComplet'];
                        echo "<div class='swiper-slide'>";
                            echo "<a href='video.php?v=$id'>";
                                echo "<div class='miniature'>";
                                    echo "<img src='$cheminMiniatureComplet' alt='Miniature de la vidéo' class='imageMiniature'/>";
                                echo "</div>";
                                echo "<h3>$titre</h3>";
                                echo "<h4>$titreVideo</h4>";
                            echo "</a>";
                        echo "</div>";
                    }
                ?>
            </div>
        </div>
        <div class="swiper-projet-button-next"></div>
        <div class="swiper-projet-button-prev"></div>
    </div>
    <?php } ?>
    
</div>

<?php require_once '../ressources/Templates/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        //affichageFiltres();
        initCarrousel();
    });
</script>
