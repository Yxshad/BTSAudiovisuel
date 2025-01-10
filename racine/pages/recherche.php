<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/recherche.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

<?php include '../ressources/Templates/header.php';?>

<div class="filtres">
    
    <form action="">
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

    <button class="afficherFiltres">></button>
</div>

<div class="container">
    <?php for ($i=0; $i < 5; $i++) { ?>
        <a href="video.php" class="video">
            <div class="miniature"></div>
            <div class="description">
                <h2 class="titre">Video <?php echo $i; ?></h2>
                <p class="description"> Métadonnées : Lorem Ipsum</p>
            </div>
        </a>
    <?php } ?>
</div>

<div class="voile"></div>

<script src="../ressources/Script/script.js"></script>