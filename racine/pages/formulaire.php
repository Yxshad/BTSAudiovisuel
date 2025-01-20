<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/logo_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/formulaire.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

<?php
<<<<<<< HEAD
    require_once '../ressources/Templates/header.php';
    require_once '../fonctions/fonctions.php';
    require_once '../fonctions/ftp.php';
    require_once '../ressources/constantes.php';
    require_once '../fonctions/modele.php';
=======
    require '../ressources/Templates/header.php';
    require '../fonctions/fonctions.php';
    require '../fonctions/ftp.php';
    require '../ressources/constantes.php';
?>
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b

    // Récupération de l'URI NAS de la vidéo
    if (isset($_GET['v'])) {
        $id = $_GET['v'];
    }

    if (
        isset($_POST["profReferant"]) ||
        isset($_POST["realisateur"]) || 
        isset($_POST["promotion"]) || 
        isset($_POST["projet"]) || 
        isset($_POST["cadreur"]) || 
        isset($_POST["responsableSon"])
    ) {

        // Récupération des champs entrés dans le formulaire

        $profReferant = $_POST["profReferant"];
    
        $realisateur = $_POST["realisateur"];
    
        $promotion = $_POST["promotion"];

        $projet = $_POST["projet"];
    
    
        $cadreur = $_POST["cadreur"];

        $responsableSon = $_POST["responsableSon"];
        

        miseAJourMetadonneesVideo(
            $id, 
            $profReferant, 
            $realisateur, 
            $promotion, 
            $projet, 
            $cadreur, 
            $responsableSon
        );


    }

    $video = fetchAll("SELECT * FROM Media WHERE id=$id;");
    $video = $video[0];
    $titre = substr($video["mtd_tech_titre"], 0, -4);

    $listeMeta = getMetadonneesEditorialesVideo($video);

    // Charge la miniature
    $miniature = $titre . "_miniature.png";
    $cheminMiniature = URI_VIDEOS_A_LIRE . $video["URI_NAS_MPEG"] . $miniature;

    $allProf = getAllProf();
?>

<div class="container">
    <h1>Formulaire des métadonnées</h1>

    <div class="colonnes">
        <div class="colonne-1">
<<<<<<< HEAD
            <div class="img">
                <img src="<?php echo $cheminMiniature; ?>" alt="Miniature de la vidéo" class="imageMiniature">
=======
            <div class='miniature'>
                <img src=<?php  ?> alt='Miniature de la vidéo' class='imageMiniature'/>
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
            </div>
            <h2><?php echo $titre; ?></h2>
            <p><strong>Durée :</strong> <?php echo $video['mtd_tech_duree']; ?></p>
            <p><strong>Images par secondes :</strong> <?php echo $video['mtd_tech_fps']; ?></p>
            <p><strong>Résolution :</strong> <?php echo $video['mtd_tech_resolution']; ?></p>
            <p><strong>Format :</strong> <?php echo $video['mtd_tech_format']; ?></p>
        </div>

        <div class="colonne-2">
            <h2>Équipe</h2>
            <form method="post" action="formulaire.php?v=<?php echo $id; ?>">
                <div class="champ">
                    <label for="profReferant" class="form-label">Professeur référant</label>
                    <select id="profReferant" name="profReferant">
                        <option value="<?php echo $listeMeta["professeur"]; ?>">Professeur actuel : <?php echo $listeMeta["professeur"]; ?></option>
                        <?php foreach ($allProf as $prof) { ?>
                            <option value="<?php echo $prof; ?>"><?php echo $prof; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="champ">
                    <label for="realisateur" class="form-label">Réalisateur</label>
                    <input type="text" id="realisateur" name="realisateur" placeholder="<?php echo $listeMeta["realisateur"]; ?>">
                    
                </div>
                <div class="champ">
                    <label for="promotion">Promotion</label>
                    <input type="text" id="promotion" name="promotion" placeholder="<?php echo $video["promotion"]; ?>">
                </div>
                <div class="champ">
                    <label for="projet">Projet</label>
                    <input type="text" id="projet" name="projet" placeholder="<?php echo $listeMeta["projet"]; ?>">
                </div>
                <div class="champ">
                    <label for="cadreurNom">Cadreur</label>
                    <div class="inputs">
                        <input type="text" id="cadreur" name="cadreur" placeholder="<?php echo $listeMeta["cadreur"]; ?>">
                    </div>
                </div>
                <div class="champ">
                    <label for="responsableSon">Responsable son</label>
                    <div class="inputs">
                        <input type="text" id="responsableSon" name="responsableSon" placeholder="<?php echo $listeMeta["responsableSon"]; ?>">
                    </div>
                </div>
                <button type="submit" class="btn">Confirmer</button> 
            </form>
        </div>
    </div>

    <div class="btns">
        <a href="video.php?v=<?php echo $id; ?>" class="btn">Annuler</a>
        
    </div>
</div>
