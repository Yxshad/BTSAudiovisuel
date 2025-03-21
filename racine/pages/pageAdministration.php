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

if(AFFICHAGE_LOGS_PLUS_RECENTS_PREMIERS=='on'){
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

<?php
    require_once '../ressources/Templates/header.php';
    chargerPopup();
?>


<body>
    <div><h1>Administration du Site</h1></div>
    <div class="tabs">
        <div class="tab" data-tab="database">Base de données</div>
        <div class="tab" data-tab="reconciliation">Réconciliation</div>
        <div class="tab" data-tab="transfert">Fonction de transfert</div>
        <div class="tab" data-tab="settings">Paramétrage du site</div>
        <div class="tab" data-tab="logs">Consulter les logs</div>
        <?php //On cache la page des autorisation si on est pas admin
            if($_SESSION["role"] == ROLE_ADMINISTRATEUR){ ?>
                <div class="tab" data-tab="users">Gérer les utilisateurs</div>
        <?php } ?>
    </div>
    
    <div class="tab-content database" id="database">
    <h2>Sauvegarde de la base de données</h2>
    <div id="container-saveLog">
        <div id="container-sauvegarde">
        <div class="content">
            <h3>Paramètre des sauvegardes</h3>
            
            <div class="form-group">
                <label for="tempsLancement">Choisir l'heure d'exécution :</label>
                <input type="time" id="tempsLancement" class="sauvegardeInputs" value="00:00"/>
            </div>
            
      
                <div class="form-group">
                    <label for="select_Day">Choisir le jour d'exécution :</label>
                    <select name="day" id="select_Day" class="sauvegardeInputs">
                        <option value="*" selected>Tous les jours</option>
                        <option value="1">Lundi</option>
                        <option value="2">Mardi</option>
                        <option value="3">Mercredi</option>
                        <option value="4">Jeudi</option>
                        <option value="5">Vendredi</option>
                        <option value="6">Samedi</option>
                        <option value="0">Dimanche</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="select_Month">Choisir le mois d'exécution :</label>
                    <select name="month" id="select_Month" class="sauvegardeInputs">
                        <option value="*" selected>Tous les mois</option>
                        <option value="1">Janvier</option>
                        <option value="2">Février</option>
                        <option value="3">Mars</option>
                        <option value="4">Avril</option>
                        <option value="5">Mai</option>
                        <option value="6">Juin</option>
                        <option value="7">Juillet</option>
                        <option value="8">Août</option>
                        <option value="9">Septembre</option>
                        <option value="10">Octobre</option>
                        <option value="11">Novembre</option>
                        <option value="12">Décembre</option>
                    </select>
                </div>
            
            <div class="btn-sauvegarde-container">
                <button onClick="changeDatabaseSaveTime()" class="btn parametre">Enregistrer les paramètres</button>
                <button onClick="createDatabaseSave()" class="btn manuelle">Réaliser une sauvegarde manuelle</button>
            </div>
        </div>
        </div>

            <div class="right-panel">
                <div class="log-container colonne-2">
                    <?php foreach ($logsSauvegardesBDD as $line): ?>
                        <div class="log-line"><?php echo htmlspecialchars($line); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="tab-content" id="reconciliation">
        <!-- Section : Fonction de réconciliation -->
        <div class="reconciliation-section">
            <h2 class="section-title">Fonction de réconciliation</h2>
        </div>

        <!-- Section : Résultat de la réconciliation -->
        <?php if (isset($_SESSION['reconciliation_result'])) : ?>
            <div class="result-section">
                <div class="reconciliation-result">
                    <?php
                    echo $_SESSION['reconciliation_result'];
                    unset($_SESSION['reconciliation_result']); // Nettoyer après affichage
                    ?>
                </div>
            </div>
        <?php endif; ?>
        <form method="post" class="reconciliation-form">
            <input type="hidden" name="action" value="declencherReconciliation">
            <button type="submit" class="reconciliation-button">Lancer la réconciliation</button>
        </form>
    </div>

    <div class="tab-content" id="transfert">
        <h2>Fonction de transfert</h2>
        <div class="container">
            <div class="content-wrapper">
                <!-- Première ligne : Titres -->
                <div class="header-row">
                    <div class="transfers-header">
                        <h2>Transferts</h2>
                    </div>
                    <div class="pending-videos-header">
                        <h2>Vidéos en attente de métadonnées</h2>
                    </div>
                </div>

                <!-- Deuxième ligne : Contenu (bouton, symbole, tableau) -->
                <div class="content-row">
                    <div class="lignes-container">
                        <div class="lignes"><!-- Résultat ajax --></div>
                        <div class="button-container">
                            <button class="btn" id="btnConversion" onclick="lancerConversion()">Lancer conversion</button>
                        </div>
                    </div>
                    <div class="symbol-container">
                        <img src='../ressources/Images/avance-rapide.png' alt="Symbole de transfert">
                    </div>
                    <div class="table-container">
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
        <h2>Paramétrage des constantes</h2>
        
        <!-- Table des Matières -->
        <div class="table-of-contents">
            <h3>Table des Matières</h3>
            <ul>
                <li><a href="#section-uris">URIs</a></li>
                <li><a href="#section-ftp">Connexions FTP</a></li>
                <li><a href="#section-bd">Base de données</a></li>
                <li><a href="#sauvegarde">Sauvegarde</a></li>
                <li><a href="#section-logs">Logs</a></li>
                <li><a href="#section-multiprocessing">Multiprocessing</a></li>
                <li><a href="#personnalisation">Personnalisation</a></li>
            </ul>
        </div>

        <!-- Formulaire -->
        <form method="post" action="#" class="form-container" onsubmit="modifierConstantes();">
            <input type="hidden" name="action" value="mettreAJourParametres">

            <!-- Section URIs -->
            <h3 id="section-uris" class="section-title">URIs</h3>
            <div>
                <label for="uri_racine_nas_pad" class="form-label">URI racine du NAS PAD:</label>
                <input type="text" id="uri_racine_nas_pad" name="uri_racine_nas_pad" value="<?php echo URI_RACINE_NAS_PAD; ?>" 
                    oninput="validerURI('uri_racine_nas_pad')" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="uri_racine_nas_arch" class="form-label">URI racine du NAS ARCH:</label>
                <input type="text" id="uri_racine_nas_arch" name="uri_racine_nas_arch" value="<?php echo URI_RACINE_NAS_ARCH; ?>" 
                    oninput="validerURI('uri_racine_nas_arch')" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="uri_racine_stockage_local" class="form-label">URI racine du stockage local:</label>
                <input type="text" id="uri_racine_stockage_local" name="uri_racine_stockage_local" value="<?php echo URI_RACINE_STOCKAGE_LOCAL; ?>" 
                    pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="uri_racine_nas_diff" class="form-label">URI racine du NAS DIFF:</label>
                <input type="text" id="uri_racine_nas_diff" name="uri_racine_nas_diff" value="<?php echo URI_RACINE_NAS_DIFF; ?>" 
                    oninput="validerURI('uri_racine_nas_diff')" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required>
            </div>

            <!-- Section Connexions FTP -->
            <h3 id="section-ftp" class="section-title">Connexions FTP</h3>
            <div>
                <h4>NAS PAD</h4>
                <label for="nas_pad" class="form-label">IP du serveur:</label>
                <input type="text" id="nas_pad" name="nas_pad" value="<?php echo NAS_PAD; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="login_nas_pad" class="form-label">Identifiant:</label>
                <input type="text" id="login_nas_pad" name="login_nas_pad" value="<?php echo LOGIN_NAS_PAD; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="password_nas_pad" class="form-label">Mot de passe:</label>
                <div class="input-with-icon">
                    <input type="password" id="password_nas_pad" name="password_nas_pad" value="<?php echo PASSWORD_NAS_PAD; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required>
                    <button type="button" onclick="afficherMotDePasse('password_nas_pad', 'eye_pad')" class="password-toggle-button">
                        <img id="eye_pad" src="../ressources/Images/eye-closed.png" alt="Afficher/Masquer" class="eye-icon">
                    </button>
                </div><br>
                <label for="login_nas_pad_sup" class="form-label">Identifiant (accès en modification):</label>
                <input type="text" id="login_nas_pad_sup" name="login_nas_pad_sup" value="<?php echo LOGIN_NAS_PAD_SUP; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="password_nas_pad_sup" class="form-label">Mot de passe (accès en modification):</label>
                <div class="input-with-icon">
                    <input type="password" id="password_nas_pad_sup" name="password_nas_pad_sup" value="<?php echo PASSWORD_NAS_PAD_SUP; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required>
                    <button type="button" onclick="afficherMotDePasse('password_nas_pad_sup', 'eye_pad')" class="password-toggle-button">
                        <img id="eye_pad" src="../ressources/Images/eye-closed.png" alt="Afficher/Masquer" class="eye-icon">
                    </button>
                </div>
                <h4>NAS ARCH</h4>
                <label for="nas_arch" class="form-label">IP du serveur:</label>
                <input type="text" id="nas_arch" name="nas_arch" value="<?php echo NAS_ARCH; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="login_nas_arch" class="form-label">Identifiant:</label>
                <input type="text" id="login_nas_arch" name="login_nas_arch" value="<?php echo LOGIN_NAS_ARCH; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="password_nas_arch" class="form-label">Mot de passe:</label>
                <div class="input-with-icon">
                    <input type="password" id="password_nas_arch" name="password_nas_arch" value="<?php echo PASSWORD_NAS_ARCH; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required>
                    <button type="button" onclick="afficherMotDePasse('password_nas_arch', 'eye_arch')" class="password-toggle-button">
                        <img id="eye_arch" src="../ressources/Images/eye-closed.png" alt="Afficher/Masquer" class="eye-icon">
                    </button>
                </div><br>

                <label for="login_nas_arch_sup" class="form-label">Identifiant (accès en modification):</label>
                <input type="text" id="login_nas_arch_sup" name="login_nas_arch_sup" value="<?php echo LOGIN_NAS_ARCH_SUP; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="password_nas_arch_sup" class="form-label">Mot de passe (accès en modification):</label>
                <div class="input-with-icon">
                    <input type="password" id="password_nas_arch_sup" name="password_nas_arch_sup" value="<?php echo PASSWORD_NAS_ARCH_SUP; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required>
                    <button type="button" onclick="afficherMotDePasse('password_nas_arch_sup', 'eye_arch')" class="password-toggle-button">
                        <img id="eye_arch" src="../ressources/Images/eye-closed.png" alt="Afficher/Masquer" class="eye-icon">
                    </button>
                </div>

                <h4>NAS DIFF</h4>
                <label for="nas_diff" class="form-label">IP du serveur:</label>
                <input type="text" id="nas_diff" name="nas_diff" value="<?php echo NAS_DIFF; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="login_nas_diff" class="form-label">Identifiant:</label>
                <input type="text" id="login_nas_diff" name="login_nas_diff" value="<?php echo LOGIN_NAS_DIFF; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="password_nas_diff" class="form-label">Mot de passe:</label>
                <div class="input-with-icon">
                    <input type="password" id="password_nas_diff" name="password_nas_diff" value="<?php echo PASSWORD_NAS_DIFF; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required>
                    <button type="button" onclick="afficherMotDePasse('password_nas_diff', 'eye_diff')" class="password-toggle-button">
                        <img id="eye_diff" src="../ressources/Images/eye-closed.png" alt="Afficher/Masquer" class="eye-icon">
                    </button>
                </div>
            </div>

            <!-- Section Base de données -->
            <h3 id="section-bd" class="section-title">Base de données</h3>
            <div>
                <label for="bd_host" class="form-label">Serveur de la BD:</label>
                <input type="text" id="bd_host" name="bd_host" value="<?php echo BD_HOST; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="bd_port" class="form-label">Port de la BD:</label>
                <input type="text" id="bd_port" name="bd_port" value="<?php echo BD_PORT; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="bd_name" class="form-label">Nom de la BD:</label>
                <input type="text" id="bd_name" name="bd_name" value="<?php echo BD_NAME; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="bd_user" class="form-label">Utilisateur de la BD:</label>
                <input type="text" id="bd_user" name="bd_user" value="<?php echo BD_USER; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="bd_password" class="form-label">Mot de passe de la BD:</label>
                <div class="input-with-icon">
                    <input type="password" id="bd_password" name="bd_password" value="<?php echo BD_PASSWORD; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required>
                    <button type="button" onclick="afficherMotDePasse('bd_password', 'eye_bd')" class="password-toggle-button">
                        <img id="eye_bd" src="../ressources/Images/eye-closed.png" alt="Afficher/Masquer" class="eye-icon">
                    </button>
                </div>
            </div>

            <!-- Section Sauvegarde -->
            <h3 id="sauvegarde" class="section-title">Sauvegarde</h3>
            <div>
                <label for="uri_fichier_generes" class="form-label">URI des fichiers générés de sauvegarde:</label>
                <input type="text" id="uri_fichier_generes" name="uri_fichier_generes" value="<?php echo URI_FICHIER_GENERES; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="uri_dump_sauvegarde" class="form-label">URI du dump de sauvegarde:</label>
                <input type="text" id="uri_dump_sauvegarde" name="uri_dump_sauvegarde" value="<?php echo URI_DUMP_SAUVEGARDE; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="uri_constantes_sauvegarde" class="form-label">URI des constantes de sauvegarde:</label>
                <input type="text" id="uri_constantes_sauvegarde" name="uri_constantes_sauvegarde" value="<?php echo URI_CONSTANTES_SAUVEGARDE; ?>" pattern="^\S+$" title="Les espaces ne sont pas autorisés" class="form-input" required><br><br>
                
                <label for="suffixe_fichier_dump_sauvegarde" class="form-label">Suffixe du fichier dump de sauvegarde:</label>
                <input type="text" id="suffixe_fichier_dump_sauvegarde" name="suffixe_fichier_dump_sauvegarde" value="<?php echo SUFFIXE_FICHIER_DUMP_SAUVEGARDE; ?>" pattern="^\S*\.sql$" title="Doit se terminer par .sql et ne pas contenir d'espaces" class="form-input" required><br><br>
                
                <label for="suffixe_fichier_constantes_sauvegarde" class="form-label">Suffixe du fichier de constantes de sauvegarde:</label>
                <input type="text" id="suffixe_fichier_constantes_sauvegarde" name="suffixe_fichier_constantes_sauvegarde" value="<?php echo SUFFIXE_FICHIER_CONSTANTES_SAUVEGARDE; ?>" pattern="^\S*\.php$" title="Doit se terminer par .php et ne pas contenir d'espaces" class="form-input" required>
            </div>
            
            <!-- Section Logs -->
            <h3 id="section-logs" class="section-title">Logs</h3>
            <div>
                <label for="nom_fichier_log_general" class="form-label">Nom du fichier de logs général:</label>
                <input type="text" id="nom_fichier_log_general" name="nom_fichier_log_general" value="<?php echo NOM_FICHIER_LOG_GENERAL; ?>" pattern="^\S*\.log$" title="Doit se terminer par .log et ne pas contenir d'espaces" class="form-input" required><br><br>
                
                <label for="nom_fichier_log_sauvegarde" class="form-label">Nom du fichier de logs de sauvegarde:</label>
                <input type="text" id="nom_fichier_log_sauvegarde" name="nom_fichier_log_sauvegarde" value="<?php echo NOM_FICHIER_LOG_SAUVEGARDE; ?>" pattern="^\S*\.log$" title="Doit se terminer par .log et ne pas contenir d'espaces" class="form-input" required><br><br>
                
                <label for="nb_lignes_logs" class="form-label">Nombre de lignes de logs maximal:</label>
                <input type="number" id="nb_lignes_logs" name="nb_lignes_logs" min=0 value="<?php echo NB_LIGNES_LOGS; ?>" class="form-input" required><br><br>
                
                <div class='logRecent'>
                    <label for="affichage_logs_plus_recents_premiers" class="form-label">Afficher les logs les plus récents en premier:</label>
                    <input type="checkbox" id="affichage_logs_plus_recents_premiers" name="affichage_logs_plus_recents_premiers" <?php echo AFFICHAGE_LOGS_PLUS_RECENTS_PREMIERS=='on' ? 'checked' : ''; ?> class="checkbox-input" required>
                </div>
            </div>

            <!-- Section Multiprocessing -->
            <h3 id="section-multiprocessing" class="section-title">Multiprocessing</h3>
            <div>
                <label for="nb_max_processus_transfert" class="form-label">Nombre maximum de processus de transfert:</label>
                <input type="number" id="nb_max_processus_transfert" min=1 max=20 name="nb_max_processus_transfert" value="<?php echo NB_MAX_PROCESSUS_TRANSFERT; ?>" class="form-input" required><br><br>
                
                <label for="nb_max_sous_processus_transfert" class="form-label">Nombre maximum de sous-processus de transfert:</label>
                <input type="number" id="nb_max_sous_processus_transfert" min=1 max=10 name="nb_max_sous_processus_transfert" value="<?php echo NB_MAX_SOUS_PROCESSUS_TRANSFERT; ?>" class="form-input" required><br>
            </div>

            <!-- Section Personnalisation -->
            <h3 id="personnalisation" class="section-title">Personnalisation</h3>
            <div>
                <label for="nb_videos_par_swiper" class="form-label">Nombre de vidéos dans le carrousel de la page d'accueil:</label>
                <input type="number" id="nb_videos_par_swiper" min=0 name="nb_videos_par_swiper" value="<?php echo NB_VIDEOS_PAR_SWIPER; ?>" class="form-input" required><br><br>
                <label for="nb_videos_historique_transfert" class="form-label">Nombre de vidéos dans l'historique:</label>
                <input type="number" id="nb_videos_historique_transfert" min=0 name="nb_videos_historique_transfert" value="<?php echo NB_VIDEOS_HISTORIQUE_TRANSFERT; ?>" class="form-input" required><br>
            </div>
            <!-- Bouton de soumission -->
            <input type="submit" value="Mettre à jour" class="submit-button">
        </form>

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

    function modifierConstantes(){
        event.preventDefault();
        changerTitrePopup("Les constantes ont bien été modifié");
        changerTextePopup("");
        changerTexteBtn("Compris", "btn1");
        attribuerFonctionBtn("sendForm", ".form-container", "btn1");
        cacherBtn("btn2");
        cacherBtn("btn3");
        cacherBtn("btn4");
        afficherPopup();
    }
</script>
