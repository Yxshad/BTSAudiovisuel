<?php 
    session_start();
    require_once '../fonctions/controleur.php';
    $infosVideo = controleurRecupererInfosVideo();

    $idVideo = $infosVideo["idVideo"];
    $nomFichier = $infosVideo["nomFichier"];
    $cheminMiniatureComplet = $infosVideo["cheminMiniatureComplet"];
    $cheminVideoComplet = $infosVideo["cheminVideoComplet"];
    $titreVideo = $infosVideo["titreVideo"];
    $description = $infosVideo["description"];
    $mtdTech = $infosVideo["mtdTech"];
    $mtdEdito = $infosVideo["mtdEdito"];
    $promotion = $infosVideo["promotion"];
    $URIS = $infosVideo["URIS"];

    $cheminCompletNAS_PAD = null;
    if(!empty($URIS['URI_NAS_PAD'])){
        $cheminCompletNAS_PAD = $URIS['URI_NAS_PAD'].$nomFichier;
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/favicon_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/video.css" rel="stylesheet">
    <link href="../ressources/Style/menuArbo.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>
    
    <!-- <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" /> -->

    <!-- #RISQUE : Liens CDN utilisés dans la lib plyr.js -->
    <script src="../ressources/lib/Plyr/plyr.js"></script>
    <link rel="stylesheet" href="../ressources/lib/Plyr/plyr.css" />

    <?php require_once '../ressources/Templates/header.php';?>
    <?php require_once '../ressources/Templates/menuArbo.php'; ?>

    <div class="contenu">
        <div class="container_principal">
            <div class="container_video">
                <div class="lecteurVideo">
                    <video class="player" id="player" playsinline controls data-poster=<?php echo $cheminMiniatureComplet; ?>>
                        <source src="<?php echo $cheminVideoComplet; ?>" type="video/mp4"/>
                    </video>
                </div>
            </div>
                <div class="info_video">

                <div class ="titre_nom">
                    <h1 class="titre"><?php echo $nomFichier; ?></h1>
                    <h2 ><?php echo $titreVideo; ?></h2>
                </div>

                <div class="container-button">
                    <!-- Bouton Télécharger -->
                    <button title="Télécharger vidéo" class="btnVideo" onclick="window.location.href='<?php echo $cheminVideoComplet; ?>';">
                        <div class="logo-btnvideo">
                            <img src="../ressources/Images/télécharger_image_blanc.png" alt="">
                        </div>
                    </button>

                    <?php if(controleurVerifierAcces(ACCES_DIFFUSION) && !empty($cheminCompletNAS_PAD)){ ?>
                        <button id="boutonDiffusion" title="Diffuser vidéo" class="btnVideo" onclick="afficherPopUp('Diffusion', 'Voulez-vous vraiment diffuser la vidéo <?php echo htmlspecialchars($nomFichier); ?> ?', {libelle : 'Oui!', arguments : [['action','diffuserVideo'], ['URI_COMPLET_NAS_PAD', '<?php echo htmlspecialchars($cheminCompletNAS_PAD); ?>']]}, {libelle : 'Non!', arguments : []})">
                            <div class="logo-btnvideo">
                                <img src="../ressources/Images/diffuser.png" alt="">
                            </div>
                        </button>
                    <?php } ?>

                    <?php if(controleurVerifierAcces(ACCES_MODIFICATION)){ ?>
                        <button id="boutonModif" title="Modifier vidéo" class="btnVideo" onclick="window.location.href='formulaireMetadonnees.php?v=<?php echo $idVideo; ?>';">
                            <div class="logo-btnvideo">
                                <img src="../ressources/Images/modifier_video_blanc.png" alt="">
                            </div>
                        </button>
                    <?php } 

                    if(controleurVerifierAcces(ACCES_SUPPRESSION)){ ?>             
                        <button title="Supprimer vidéo" class="btnVideo" id="btnSuppr" onclick="afficherPopUp('Suppression', 'Voulez-vous vraiment Supprimer la vidéo <?php echo htmlspecialchars($nomFichier); ?> ?', {libelle : 'Oui!', arguments : [['action','supprimerVideo'], ['idVideo', '<?php echo htmlspecialchars($idVideo); ?>'], ['URI_STOCKAGE_LOCAL', '<?php echo $cheminVideoComplet; ?>']]}, {libelle : 'Non!', arguments : []})">
                            <p>
                                Supprimer
                            </p>
                        </button>
                    <?php } ?>
                </div>

            </div>
            <div class="containerDescription">
                <p class="description">
                    <?php echo htmlspecialchars($mtdTech["Description"]); ?>
                </p>
            </div>
            
        </div>

        <div class="metadata_detaillee">
            <table>
                <?php
                $metadata = [
                    "URI du NAS PAD" => $URIS['URI_NAS_PAD'],
                    "URI du NAS ARCH" => $URIS['URI_NAS_ARCH'],
                    "Durée" => $mtdTech["mtd_tech_duree"],
                    "Image par seconde" => $mtdTech["mtd_tech_fps"] . " fps",
                    "Résolution" => $mtdTech["mtd_tech_resolution"],
                    "Format" => $mtdTech["mtd_tech_format"],
                    "Projet" => $mtdEdito["projet"],
                    "Promotion" => $promotion,
                    "Professeur référent" => $mtdEdito["professeur"],
                    "Réalisateur(s)" => $mtdEdito["realisateur"],
                    "Cadreur(s)" => $mtdEdito["cadreur"],
                    "Responsable(s) Son" => $mtdEdito["responsableSon"]
                ];

                foreach ($metadata as $key => $value) {
                    echo "<tr>";
                    echo "<td><strong>$key</strong></td>";
                    echo "<td>$value</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>


<?php require_once '../ressources/Templates/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        initLectureVideo();
        // appel
        pageLectureVideo();
    });

</script>










