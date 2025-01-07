<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/video.css" rel="stylesheet">

    <!-- Intégration de Plyr -->
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />

    <?php include '../ressources/Templates/header.php'; ?>
    <title>Lecteur vidéo avec Plyr</title>
</head>
<body>
<div class="container">
    <!-- Lecteur vidéo avec Plyr -->
    <div class="lecteurVideo">
        <video id="player" class="plyr" controls>

            Votre navigateur ne supporte pas les vidéos HTML5.
        </video>
    </div>
    
    <!-- Informations supplémentaires -->
    <h1 class="titre">Titre de la vidéo</h1>
    <div class="colonnes">
        <div class="colonne-1">
            <p class="description">Lorem ipsum dolor sit amet.</p>
            <p class="meta">15 fps, 1920x1080, 16:9</p>
            <?php
            $i = 0;
            while ($i < 3) { // Simuler des métadonnées éditoriales
                echo "<p>Acteur : José</p>";
                $i++;
            }
            ?>
        </div>
        <div class="colonne-2">
            <a href="<?= $videoPath ?>" download class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/download.webp" alt="Télécharger">
                </div>
                <p>Télécharger</p>
            </a>
            <a href="#" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/antenne.png" alt="Diffuser">
                </div>
                <p>Diffuser</p>
            </a>
            <a href="formulaire.php" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/modif.png" alt="Modifier">
                </div>
                <p>Modifier</p>
            </a>
            <a href="#" class="btnVideo">
                <div class="logo-btnvideo">
                    <img src="../ressources/Images/trash.png" alt="Supprimer">
                </div>
                <p>Supprimer</p>
            </a>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/plyr/3.7.8/plyr.min.js"></script>
<script>
    // Initialisation sécurisée de Plyr
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Plyr !== "undefined") {
            const player = new Plyr('#player', {
                controls: ['play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen']
            });
        } else {
            console.error("Le script Plyr n'a pas pu être chargé.");
        }
    });
</script>
</body>
</html>
