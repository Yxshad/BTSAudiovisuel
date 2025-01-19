<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/transfert.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

<?php 
include '../ressources/Templates/header.php';
?>

<?php
$code = isset($_GET['code']);
// #RISQUE : Plus tard, faire des constantes pour les codes et messages d'erreur
switch ($code) {
    case 404:
        $message = "Erreur 404 : La ressource demandée est introuvable.";
        break;
    case 403:
        $message = "Erreur 403 : Accès refusé.";
        break;
    case 500:
        $message = "Erreur 500 : Erreur interne du serveur.";
        break;
    default:
        $message = "Une erreur inconnue est survenue.";
        break;
}

echo ($message);
?>



<footer>
<?php require_once '../ressources/Templates/footer.php';?>
</footer>