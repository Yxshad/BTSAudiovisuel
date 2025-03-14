<?php 
	session_start(); 
	require_once '../fonctions/controleur.php';
    controleurVerifierAccesPage(ACCES_ADMINISTRATION);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/favicon_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/sauvegarde.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

<?php require_once '../ressources/Templates/header.php';?>

<div class="container">
    <div class="colonnes">

        <div class="colonne-1">
            <h1>Paramètre des sauvegardes</h1>
            <div class="intervalSauvegarde">
                <p>Sauvegarder toutes les </p>
                <input type="number" name="" id="">
            </div>
            <div class="options">
                <input type="radio" name="drone" id=""> Jours
            </div>
            <div class="options">
                <input type="radio" name="drone" id=""> Mois
            </div>
            <div class="options">
                <input type="radio" name="drone" id=""> Années
            </div>

            <div class="dateSauvegarde">
                <p>à partir du : </p>
                <input type="date" name="" id="">
            </div>

            <a href="#" class="btn parametre">Enregistrer les paramètres</a>
            <a href="#" class="btn manuelle">Réaliser une sauvegarde manuelle</a>

        </div>
        <div class="colonne-2">
            <h2>Log des sauvegardes</h2>
            <div class="nomColonne">
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
</div>

<?php require_once '../ressources/Templates/footer.php'; ?>