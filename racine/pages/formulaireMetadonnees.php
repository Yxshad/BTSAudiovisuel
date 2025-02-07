<?php 
    session_start();
    require_once '../fonctions/controleur.php';

    //Si il n'est pas autoriser alors, on le renvoit sur la page d'accueil
    if(!controleurVerifierAcces(ACCES_MODIFICATION)){
        header('Location: home.php');
    }

    $infosVideo = controleurRecupererInfosVideo();
    $idVideo = $infosVideo["idVideo"];
    $nomFichier = $infosVideo["nomFichier"];
    $cheminMiniatureComplet = $infosVideo["cheminMiniatureComplet"];
    $titreVideo = $infosVideo["titreVideo"];
    $mtdTech = $infosVideo["mtdTech"];
    $mtdEdito = $infosVideo["mtdEdito"];
    $promotion = $infosVideo["promotion"];
    $listeProfesseurs = controleurRecupererListeProfesseurs();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/favicon_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/formulaire.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <?php require_once '../ressources/Templates/header.php'; ?>

<div class="container">
    <h1>Formulaire des métadonnées</h1>

    <div class="colonnes">
        <div class="colonne-1">
            <div class="img">
                <img src="<?php echo $cheminMiniatureComplet; ?>" alt="Miniature de la vidéo" class="imageMiniature">
            </div>
            <h2 class="titre"><?php echo $nomFichier; ?></h2>
            <h2 class="titre"><?php echo $titreVideo; ?></h2>
            <p><strong>Durée :</strong> <?php echo $mtdTech['mtd_tech_duree']; ?></p>
            <p><strong>Images par secondes :</strong> <?php echo $mtdTech['mtd_tech_fps']; ?></p>
            <p><strong>Résolution :</strong> <?php echo $mtdTech['mtd_tech_resolution']; ?></p>
            <p><strong>Format :</strong> <?php echo $mtdTech['mtd_tech_format']; ?></p>
        </div>

        <div class="colonne-2">
            <h2>Équipe</h2>
            <form method="post" action="#">
                <input type="hidden" name="action" value="ModifierMetadonnees">
                <input type="hidden" name="idVideo" value="<?php echo $idVideo; ?>">
                <div class="champ">
                    <label for="profReferent" class="form-label">Professeur référant</label>
                    <select id="profReferent" name="profReferent">
                        <option value="<?php echo $mtdEdito["professeur"]; ?>">Professeur actuel : <?php echo $mtdEdito["professeur"]; ?></option>
                        <?php foreach ($listeProfesseurs as $prof) { ?>
                            <option value="<?php echo $prof; ?>"><?php echo $prof; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="champ">
                    <label for="realisateur" class="form-label">Réalisateur</label>
                    <input type="text" id="realisateur" name="realisateur" placeholder="<?php echo $mtdEdito["realisateur"]; ?>">
                </div>
                <div class="champ">
                    <label for="promotion">Promotion</label>
                    <input type="text" id="promotion" name="promotion" placeholder="<?php echo $promotion; ?>">
                </div>
                <div class="champ">
                    <label for="projet">Projet</label>
                    <input type="text" id="projet" name="projet" placeholder="<?php echo $mtdEdito["projet"]; ?>">
                </div>
                <div class="champ">
                    <label for="cadreurNom">Cadreur</label>
                    <div class="inputs">
                        <input type="text" id="cadreur" name="cadreur" placeholder="<?php echo $mtdEdito["cadreur"]; ?>">
                    </div>
                </div>
                <div class="champ">
                    <label for="responsableSon">Responsable son</label>
                    <div class="inputs">
                        <input type="text" id="responsableSon" name="responsableSon" placeholder="<?php echo $mtdEdito["responsableSon"]; ?>">
                    </div>
                </div>
                <button type="submit" class="btn">Confirmer</button> 
            </form>
        </div>
    </div>

    <div class="btns">
        <a href="video.php?v=<?php echo $idVideo; ?>" class="btn">Terminer</a>
    </div>
</div>

<?php require_once '../ressources/Templates/footer.php';?>