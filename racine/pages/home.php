<?php session_start(); ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/home.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

<?php
    require '../ressources/Templates/header.php';
    require '../fonctions/fonctions.php';
    require '../fonctions/ftp.php';
    require '../ressources/constantes.php';
    require '../fonctions/modele.php';
?>

<aside class="filtres">
    
    <form action="recherche.php">
        <div>
            <label>Année</label>
            <input type="number">
        </div>

        <div>
            <label>Niveau</label>
            <input type="number">
        </div>
        
        <input value="Rechercher" type="submit">
    </form>

    <button class="afficherFiltres"> > </button>
</aside>

<div class="container">
    <div class="sliderVideo">
        <h2>Vos vidéos</h2>
        <div class="swiperVideo">
            <div class="swiper-wrapper">
                <?php
                    $tabURIS = recupererURIEtTitreVideos();
                    if(!($tabURIS)){
                        $nbVideosARecuperer = 0;
                    }
                    else{
                        $nbVideosARecuperer = count($tabURIS);
                    }
                    for ($i=0; $i < $nbVideosARecuperer; $i++) {

                        $uriNAS = $tabURIS[$i]['URI_NAS_MPEG'];
                        $titre = $tabURIS[$i]['mtd_tech_titre'];
                        $cheminLocalComplet = chargerMiniature($uriNAS, $titre, NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG);

                        // Formulaire caché pour passer l'URI NAS
                        echo("<div class='swiper-slide'>");
                        echo("<form action='video.php' method='POST' id='formVideo_$i' style='display: none;'>");
                        echo("<input type='hidden' name='uriNAS' value='$uriNAS'>");
                        echo("<input type='hidden' name='cheminLocalComplet' value='$cheminLocalComplet'>");
                        echo("</form>");
                        
                        // Lien qui renvoie à la validation du formulaire
                        echo("<a href='#' onclick='document.getElementById(\"formVideo_$i\").submit();'>");
                            echo("<div class='miniature'>");
                                echo("<img src='$cheminLocalComplet' alt='Miniature de la vidéo' class='imageMiniature'/>");
                            echo("</div>");
                            echo("<h3>$titre</h3>");
                        echo("</a>");
                        echo("</div>");
                    }
                ?>
            </div>
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>

<div class="voile"></div>

<footer>
<?php require '../ressources/Templates/footer.php';?>
</footer>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        affichageFiltres();
        initCarrousel();
    });
</script>