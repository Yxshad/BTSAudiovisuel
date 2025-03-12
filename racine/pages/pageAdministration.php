<?php 
session_start();
require_once '../fonctions/controleur.php';
controleurVerifierAccesPage(ACCES_ADMINISTRATION);

$listeProfesseurs = controleurRecupererAutorisationsProfesseurs();
$tabDernieresVideos = controleurRecupererDernieresVideosTransfereesSansMetadonnees();
// Appel des logs 
$logFile = URI_FICHIER_GENERES . NOM_FICHIER_LOG_GENERAL; // Chemin du fichier log
$maxLines = NB_LIGNES_LOGS; // Nombre maximum de lignes à afficher dans les logs
$logsGeneraux = controleurAfficherLogs($logFile, $maxLines);

//Pour les logs des sauvegardes de la BD
$logFile = URI_FICHIER_GENERES . NOM_FICHIER_LOG_SAUVEGARDE;
$maxLines = NB_LIGNES_LOGS;
$logsSauvegardesBDD = controleurAfficherLogs($logFile, $maxLines);

if(AFFICHAGE_LOGS_PLUS_RECENTS_PREMIERS){
    $logsGeneraux = array_reverse($logsGeneraux);
    $logsSauvegardesBDD = array_reverse($logsSauvegardesBDD);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../ressources/Images/favicon_BTS_Play.png" type="image/png">
    <link href="../ressources/Style/main.css" rel="stylesheet">
    <link href="../ressources/Style/pageAdministration.css" rel="stylesheet">
    <link href="../ressources/Style/transfert.css" rel="stylesheet">
    <link href="../ressources/Style/sauvegarde.css" rel="stylesheet">
    <script src="../ressources/Script/script.js"></script>

    <link href="../ressources/lib/Swiper/swiper-bundle.min.css" rel="stylesheet">
    <script src="../ressources/lib/Swiper/swiper-bundle.min.js"></script>

<?php require_once '../ressources/Templates/header.php'; ?>

<body>
    <div><h1>Administration du Site</h1></div>
    <div class="tabs">
        <div class="tab" data-tab="database">Base de données</div>
        <div class="tab" data-tab="reconciliation">Réconciliation</div>
        <div class="tab" data-tab="transfer">Fonction de transfert</div>
        <div class="tab" data-tab="settings">Paramétrage du site</div>
        <div class="tab" data-tab="logs">Consulter les logs</div>
        <?php //On cache la page des autorisation si on est pas admin
            if($_SESSION["role"] == ROLE_ADMINISTRATEUR){ ?>
                <div class="tab" data-tab="users">Gérer les utilisateurs</div>
        <?php } ?>
    </div>
    
    <div class="tab-content" id="database">
        <h2>Sauvegarde de la base de données</h2>
        <div class="colonnes">
            <div class="colonne-1">
                <h1>Paramètre des sauvegardes</h1>
                <div class="intervalSauvegarde">
                    <p>Effectuer la sauvegarde à : </p>
                    <input type="time" id="tempsLancement" name="tempsLancement" required />
                </div>

            <!--SELECT DES JOURS-->
            <div>
            <label for="select_Day">Choisir le jour d'exécution:</label>
            <select name="day" id="select_Day">
                <option value="*" selected>Tous les jours</option>
                <option value="0">Lundi</option>
                <option value="1">Mardi</option>
                <option value="2">Mercredi</option>
                <option value="3">Jeudi</option>
                <option value="4">Vendredi</option>
                <option value="5">Samedi</option>
                <option value="6">Dimanche</option>
            </select>
            </div>

            <!--SELECT DU MOIS-->
            <div>
            <label for="select_Month">Choisir le jour d'exécution:</label>
            <select name="month" id="select_Month">
                <option value="*" selected>Tous les mois</option>
                <option value="1">Janvier</option>
                <option value="2">Février</option>
                <option value="3">Mars</option>
                <option value="4">Avril</option>
                <option value="5">Mai</option>
                <option value="6">Juin</option>
                <option value="7">Juillet</option>
                <option value="8">Aout</option>
                <option value="9">Septembre</option>
                <option value="10">Octobre</option>
                <option value="11">Novembre</option>
                <option value="12">Décembre</option>
            </select>
            </div>

                <a onClick="changeDatabaseSaveTime()" class="btn parametre">Enregistrer les paramètres</a>
                <a onClick="createDatabaseSave()" class="btn manuelle">Réaliser une sauvegarde manuelle</a>
            </div>

            <div class="log-container colonne-2">
                <?php foreach ($logsSauvegardesBDD as $line): ?>
                    <div class="log-line"><?php echo htmlspecialchars($line); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="tab-content" id="reconciliation">
        <h2>Fonction de réconciliation</h2>
        <form method="post">
            <input type="hidden" name="action" value="declencherReconciliation">
            <button type="submit">Réconciliation</button>
        </form>
        <?php
        // Affichage du résultat de la réconciliation après redirection
        if (isset($_SESSION['reconciliation_result'])) {
            echo $_SESSION['reconciliation_result'];
            unset($_SESSION['reconciliation_result']); // Nettoyer après affichage
        }
        ?>
    </div>

    <div class="tab-content" id="transfer">
        <h2>Fonction de transfert</h2>
        <div class="container">
            <div class="colonnes">
                <div class="colonne-1">
                    <h1>Transferts</h1>
                    <div class="transferts">
                        <div class="lignes">
                            <!-- Résultat ajax -->
                        </div>
                        <div class="commande">
                            <button class="btn" id="btnConversion" onclick="lancerConversion()">Lancer conversion</button>
                        </div>
                    </div>
                </div>
                <div class="symbole">
                    >
                </div>
                <div class="colonne-2">
                    <h2>Vidéos en attente de métadonnées</h2>
                    <div class="dates">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Fichier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tabDernieresVideos as $video) {
                                $id = $video['id'];
                                $date_creation = $video['date_creation'];
                                $mtd_tech_titre = $video['mtd_tech_titre'];
                                ?>
                                <tr>
                                    <td><a href="video.php?v=<?php echo $id; ?>"><?php echo $date_creation; ?></a></td>
                                    <td><a href="video.php?v=<?php echo $id; ?>"><?php echo $mtd_tech_titre; ?></a></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-content" id="settings">
        <h2>Paramétrage du site</h2>
        <p>Configuration et personnalisation...</p>
    </div>

    <div class="tab-content" id="logs">
        <h2>Consulter les logs</h2>
        <div class="log-container">
            <?php foreach ($logsGeneraux as $line): ?>
                <div class="log-line"><?php echo htmlspecialchars($line); ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php //On cache le contenu de la page si on est pas admin
    if($_SESSION["role"] == ROLE_ADMINISTRATEUR){ ?>
        <div class="tab-content" id="users">
            <h2>Gérer les utilisateurs</h2>
            <table>
                <tr>
                    <th></th>
                    <th>Modifier la vidéo</th>
                    <th>Diffuser la vidéo</th>
                    <th>Supprimer la vidéo</th>
                    <th>Administrer le site</th>
                </tr>
                <?php foreach($listeProfesseurs as $professeur){ ?>
                    <tr>
                        <?php 
                        if ($professeur["role"] == ROLE_ADMINISTRATEUR) {
                            $desactivation = "disabled";
                            $class = "class='gris'";
                        } else {
                            $desactivation = "";
                            $class = "";
                        }
                        ?>

                        <th <?php echo $class; ?>><?php echo($professeur['nom'] . " " . $professeur['prenom']); ?></th>

                        <td>
                            <input <?php echo $desactivation; ?> type="checkbox" data-prof="<?php echo $professeur["professeur"]; ?>" data-colonne="modifier" <?php echo $professeur["modifier"] == 1 ? "checked" : "" ;?>/>
                        </td>
                        <td>
                            <input <?php echo $desactivation; ?> type="checkbox" data-prof="<?php echo $professeur["professeur"]; ?>" data-colonne="diffuser" <?php echo $professeur["diffuser"] == 1 ? "checked" : "" ;?>/>
                        </td>
                        <td>
                            <input <?php echo $desactivation; ?> type="checkbox" data-prof="<?php echo $professeur["professeur"]; ?>" data-colonne="supprimer" <?php echo $professeur["supprimer"] == 1 ? "checked" : "" ;?>/>
                        </td>
                        <td>
                            <input <?php echo $desactivation; ?> type="checkbox" data-prof="<?php echo $professeur["professeur"]; ?>" data-colonne="administrer" <?php echo $professeur["administrer"] == 1 ? "checked" : "" ;?>/>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
    
</body>
</html>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        affichageLogsCouleurs();
        gestionOngletsAdministration();
        appelScanVideo();
        detectionCheckboxes(); 
    });
</script>
