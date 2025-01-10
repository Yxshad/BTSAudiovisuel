<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/formulaire.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

<?php
    require '../ressources/Templates/header.php';
    require '../fonctions/fonctions.php';
    require '../fonctions/ftp.php';
    require '../ressources/constantes.php';
?>


<div class="container">

    <h1>Formulaire des métadonnées</h1>

    <div class="colonnes">
        <div class="colonne-1">
            <div class='miniature'>
                <img src=<?php  ?> alt='Miniature de la vidéo' class='imageMiniature'/>
            </div>
            <h2>Titre de la video</h2>
            <p class="duree">duree : </p>
            <p class="fps">image par secondes : </p>
            <p class="resolution">résolution : </p>
            <p class="format">format : </p>
        </div>
        <div class="colonne-2">
            <h2>Équipe</h2>
            <div class="meta-editoriale">
                Réalisteur : <input type="text">
            </div>
            <div class="meta-editoriale">
                Cadreur : <input type="text">
            </div>
            <div class="meta-editoriale">
                Acteur : <input type="text">
            </div>
            <div class="meta-editoriale">
                Acteur 2     : <input type="text">
            </div>
            <hr>
            <div class="promotion">
                Promotion : <input type="text">
            </div>
            <hr>
            <div class="projet">
                Projet : <input type="text">
            </div>
        </div>
    </div>

    <div class="boutons">
        <a href="#" class="annuler">Annuler</a>
        <a href="#" class="confirmer">Confirmer</a>
    </div>

</div>