<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/reconciliation.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

<?php include '../ressources/Templates/header.php'; //Appel du header ?>

<div class="container">
    <h1>Reconciliation</h1>
    
    <div class="bouton">
        <a class="btn" href="fonction_reconciliation.php">Lancer la reconciliation</a>
    </div>
    
    <div class="logs">
        <h2>Logs des reconciliation</h2>
        <div class="nomColonnes">
            <p class="date">Date</p>
            <p class="acteur">Acteur</p>
            <p class="statut">Statut</p>
        </div>
        <?php for ($i=0; $i < 5; $i++) { ?>
            <div class="ligne">
                <div>
                    <p class="text-date">04/05/2025 17:42</p>
                    <p class="text-date">Réalisé par professeur 1</p>
                    <p class="text-date">Réussite</p>
                </div>
            </div>
        <?php } ?>
    </div>
</div>