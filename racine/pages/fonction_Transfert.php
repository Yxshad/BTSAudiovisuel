<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/home.css" rel="stylesheet">
	<script src="../ressources/Script/script.js"></script>
	<title>Fonction de transfert</title>
</head>
<body>
	<h1> Fonction de transfert </h1>
	<form method="post">
		<button type="submit" name="declencherTransfert">Déclencher la fonction de transfert</button>
	</form>
</body>
</html>

<?php

require '../fonctions/fonctions.php';
require '../fonctions/ftp.php';
require '../ressources/constantes.php';
require '../fonctions/ffmpeg.php';
require '../fonctions/modele.php';
require '../ressources/Templates/header.php';


if (isset($_POST['declencherTransfert'])) {
	fonctionTransfertAffiche();
}

function fonctionTransfertAffiche(){

	$COLLECT_PAD = [];
	$COLLECT_ARCH = [];
	$COLLECT_MPEG = [];

    echo("<h2> Lancement de l'algorithme </h2>");

	//-----------------------   répertoire NAS_PAD      ------------------------
	$COLLECT_PAD = recupererCollectNAS(NAS_PAD, LOGIN_NAS_PAD, PASSWORD_NAS_PAD, URI_VIDEOS_A_ANALYSER, $COLLECT_PAD, URI_RACINE_NAS_PAD);

	//-----------------------   répertoire NAS_ARCH      ------------------------
	$COLLECT_ARCH = recupererCollectNAS(NAS_ARCH, LOGIN_NAS_ARCH, PASSWORD_NAS_ARCH, URI_VIDEOS_A_ANALYSER, $COLLECT_ARCH, URI_RACINE_NAS_ARCH);

	afficherCollect("COLLECT_PAD", $COLLECT_PAD);
	afficherCollect("COLLECT_ARCH", $COLLECT_ARCH);

	//Remplir $COLLECT_MPEG
	$COLLECT_MPEG = remplirCollect_MPEG($COLLECT_PAD, $COLLECT_ARCH, $COLLECT_MPEG);

	afficherCollect("COLLECT_MPEG", $COLLECT_MPEG);
	afficherCollect("COLLECT_PAD", $COLLECT_PAD);
	afficherCollect("COLLECT_ARCH", $COLLECT_ARCH);

	//Alimenter le NAS MPEG
	$COLLECT_MPEG = alimenterNAS_MPEG($COLLECT_MPEG);

	//Mettre à jour la base avec $COLLECT_MPEG
	insertionCollect_MPEG($COLLECT_MPEG);

	$COLLECT_MPEG_après_alimentation = [];
	$COLLECT_MPEG_après_alimentation = recupererCollectNAS(NAS_MPEG, LOGIN_NAS_MPEG, PASSWORD_NAS_MPEG, URI_VIDEOS_A_ANALYSER, $COLLECT_MPEG_après_alimentation, URI_RACINE_NAS_MPEG);
	afficherCollect("NAS MPEG après remplissage", $COLLECT_MPEG_après_alimentation);

	ajouterLog(LOG_SUCCESS, "Fonction de transfert effectuée avec succès.");
}

?>