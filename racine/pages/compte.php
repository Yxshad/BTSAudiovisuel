<?php
    session_start();
<<<<<<< HEAD
=======
    include '../ressources/constantes.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($username == ETUDIANT && $password == ETUDIANT_MDP) {
            $_SESSION["username"] = $_POST["username"];
            header("Location: home.php");
            exit;
        } elseif($username == PROF && $password == PROF_MDP){
            $_SESSION["username"] = $_POST["username"];
            header("Location: home.php");
            exit;
        }elseif($username == ADMIN && $password == ADMIN_MDP){
            $_SESSION["username"] = $_POST["username"];
            header("Location: home.php");
            exit;
        }else {
            ?> <script>alert("ERREUR");</script> <?php
        }
    }
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/logo_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/compte.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>
<<<<<<< HEAD

<?php include_once '../ressources/Templates/header.php';?>
<?php include_once '../ressources/constantes.php';?>

<div class="container">
    <div>
        <form method="POST" action="home.php">
=======
    
<?php include '../ressources/Templates/header.php'; ?>

<div class="container">
    <div>
        <form method="POST">
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
            <div class="profile_picture">
                <img src="../ressources/Images/account.png" alt="profile-picture">
            </div>
            <div class="username">
                <p>Nom d'utilisateur : </p>
                <input type="text" name="username">
            </div>
            <div class="password">
                <p>Mot de passe : </p>
                <input type="password" name="password">
            </div>
            <div class="confirmer">
                <button>Valider</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelector(".confirmer button").addEventListener("click",function(e) {
        document.querySelector("form").submit();
    }
)
</script>

