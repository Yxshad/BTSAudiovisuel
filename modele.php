<?php

 /**
 * \file modele.php
 * \version 1.1
 * \brief Fonctions php liées aux manipulations (insertions, suppressions...) sur la base de données
 * \author Elsa Lavergne
 * */

/**
* \fn connexionBD
* \brief Permet de se connecter en base de données et de checker au passage s'il y a eu des erreurs de connexion
* \return mysqlClient - Objet de connexion à la base de données
*/
function connexionBD()
{
    try
    {
        $dsn = "mysql:host=" . BD_HOST . ";port=" . BD_PORT . ";dbname=" . BD_NAME;
        $mysqlClient = new PDO($dsn, BD_USER, BD_PASSWORD);
        $mysqlClient->beginTransaction(); // Ne pas réassigner à $connexion ici !
        $mysqlClient->exec("SET NAMES 'utf8mb4'");
        return $mysqlClient;
    }
    catch (Exception $e)
    {
        die('Erreur : ' . $e->getMessage());
    }
}

#########################################
# 
#        INSERTIONS DANS LA BD
#
 ########################################

 /**
  * \fn insertionDonneesTechniques($listeMetadonnees)
  * \brief crée la vidéo en base de données et insère les métadonnées techniques associées 
  * \param listeMetadonnees - liste des metadonnées techniques à insérer
  */
  function insertionDonneesTechniques($listeMetadonnees)
  {
      $connexion = connexionBD();
      // Construction de la requête
      $videoAAjouter = $connexion->prepare(
          'INSERT INTO Media (
              URI_NAS_PAD, 
              URI_NAS_ARCH, 
              URI_STOCKAGE_LOCAL,
              mtd_tech_titre,
              mtd_tech_duree,
              mtd_tech_resolution,
              mtd_tech_fps,
              mtd_tech_format
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
      );
      try {
        if(!getVideo($listeMetadonnees[MTD_URI_STOCKAGE_LOCAL]))
          // Ajout des paramètres
          $videoAAjouter->execute([
            $listeMetadonnees[MTD_URI_NAS_PAD],
            $listeMetadonnees[MTD_URI_NAS_ARCH],
              $listeMetadonnees[MTD_URI_STOCKAGE_LOCAL],
              $listeMetadonnees[MTD_TITRE],
              $listeMetadonnees[MTD_DUREE],
              $listeMetadonnees[MTD_RESOLUTION],
              $listeMetadonnees[MTD_FPS],
              $listeMetadonnees[MTD_FORMAT]
          ]);
          $connexion->commit();
          $connexion = null;
      } catch (Exception $e) {
          $connexion->rollback();
          $connexion = null;
      }
  }


/**
  * \fn insertionProjet($projet)
  * \brief gère l'insertion des projets si celui-ci n'est pas déjà dans la bd
  * \param projet - Projet que l'on veut insérer
  */
function insertionProjet($projet)
{
    $connexion = connexionBD();     
    try{
        if(!getProjet($projet))
        {
            $ajoutProjet = $connexion->prepare('INSERT INTO Projet (Intitule) VALUES (?)');
            $ajoutProjet->execute([$projet]); 
            $connexion->commit();
        }
        else {
            $connexion = null;
        }
    }
    catch(Exception $e)
    {
        $connexion->rollback();
        $connexion = null;
    }
}

/**
  * \fn insertionEtudiant($etudiant)
  * \brief Gère l'insertion des étudiants et lie le professeur à un/des projets
  * \param etudiant - Nom et prenom de l'étudiant
 */
function insertionEtudiant($etudiant)
{
    if ($etudiant != "") {
        $connexion = connexionBD();  
        try{
            $verif = $connexion->prepare('INSERT INTO ETUDIANT (nomComplet) VALUES (?)');
            $etudiantAAjouter= $verif->execute([
                $etudiant]);          
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            $connexion->rollback();
            $connexion = null;
        }
    }
}

#########################################
 
#          ASSIGNER DANS LA BD
  
 ########################################

/**
 * \fn assignerProjet($idVideo, $projet)
 * \brief Permet d'assigner un projet au média
 * \param idVideo - l'id de la vidéo à laquelle on assigne le projet
 * \param projet - libelle du projet
 */
 function assignerProjet($idVideo, $projet) {
    $connexion = connexionBD();
    try {
        $idProjet = getProjet($projet);
        if (!$idProjet) {
            insertionProjet($projet);
            $idProjet = getProjet($projet);
        }
        $setIDProjet = $connexion->prepare('UPDATE media 
                                          SET projet = ?, date_modification = CURRENT_TIMESTAMP
                                          WHERE id = ?');
        $setIDProjet->execute([
            $idProjet,
            $idVideo
        ]);
        $connexion->commit();
        $connexion = null;
    } catch (Exception $e) {
        if ($connexion) {
            $connexion->rollback();
        }
        $connexion = null;
    }
}

 /**
 * \fn assignerProfReferent($idVideo, $prof)
 * \brief Permet d'assigner un professeur référent au projet
 * \param idVideo - l'id de la vidéo à laquelle on assigne le professeur
 * \param prof - nomComplet du professeur
 */

 function assignerProfReferent($idVideo, $prof) {
    $lastSpacePos = strrpos($prof, ' ');
    // Vérifier si un espace existe
    if ($lastSpacePos !== false) {
        // Séparer le prénom du nom
        $profNom = substr($prof, 0, $lastSpacePos);  
        $profPrenom = substr($prof, $lastSpacePos + 1);  
        $connexion = connexionBD();
        try {
            $idProf = getProfId($profNom, $profPrenom);

            if(!$idProf)
            {
                $setIDProf = $connexion->prepare('UPDATE media 
                SET professeurReferent = NULL, date_modification = CURRENT_TIMESTAMP
                WHERE id = ?');
                $setIDProf->execute([
                $idVideo
                ]);
                $connexion = null;
            }
            else {
                // Mettre à jour la table `media` avec l'ID du professeur
            $setIDProf = $connexion->prepare('UPDATE media 
            SET professeurReferent = ?, date_modification = CURRENT_TIMESTAMP
            WHERE id = ?');
            $setIDProf->execute([
            $idProf,
            $idVideo
            ]);
            $connexion->commit();
            $connexion = null;
            }
        } catch (Exception $e) {
            if ($connexion) {
                $connexion->rollback();
            }
            $connexion = null;
        }
    }
}

/**
 * \fn assignerCadreur($idVideo, $listeCadreurs)
 * \brief Permet d'assigner un ou des cadreurs au projet
 * \param idVideo - l'id de la vidéo à laquelle on assigne le professeur
 * \param listeCadreurs - supposément une chaîne de caractères contenant tous les cadreurs
 */
 function assignerCadreur($idVideo, $listeCadreurs){

    if ($listeCadreurs != "") {
        $connexion = connexionBD();
        // #RISQUE : Si ce n'est pas une chaîne c'est mort le preg_split car ça explose la chaîne en tableau en fonction des chars donnés
        // Normaliser et séparer les cadreurs
        $listeCadreurs = trim(preg_replace('/\s*,\s*/', ', ', $listeCadreurs));
        $tabCadreur = explode(', ', $listeCadreurs);
        try{
            //On efface toutes les données cadreurs pour éviter d'avance les doublons, réinsertions et modifier plus facilement
            $cadreur = $connexion->prepare('DELETE FROM Participer 
                        WHERE (idMedia = ? AND idRole = ?)');
                    $cadreur->execute([$idVideo, 1]);            
            for ($i=0; $i < count($tabCadreur); $i++) { 
                if(!etudiantInBD($tabCadreur[$i]))
                {
                    insertionEtudiant($tabCadreur[$i]);
                }
                // Récupérer l'ID de l'élève
                $idEtudiant = getIdEtudiant($tabCadreur[$i]);
                // Insertion si non existant
                $cadreur = $connexion->prepare('INSERT INTO Participer (idMedia, idEtudiant, idRole) 
                    VALUES (?, ?, ?)');
                $cadreur->execute([$idVideo, $idEtudiant, 1]);
            }
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            $connexion->rollback();
            $connexion = null;
        }
    }
 }

/**
 * \fn assignerResponsable($idVideo, $listeResponsable)
 * \brief Permet d'assigner un ou des responsables sons
 * \param idVideo - l'id de la vidéo à laquelle on assigne l'élève
 * \param listeResponsable - supposément une chaîne de caractères contenant tous les responsables son
 */
 function assignerResponsable($idVideo, $listeResponsable){

    if ($listeResponsable != "") {
        $connexion = connexionBD();
        // #RISQUE : Si ce n'est pas une chaîne c'est mort le preg_split car ça explose la chaîne en tableau en fonction des chars donnés
        // Normaliser et séparer les responsables son
        $listeResponsable = trim(preg_replace('/\s*,\s*/', ', ', $listeResponsable));
        $tabResponsable = explode(', ', $listeResponsable);
        try{
            //On efface toutes les données responsables son pour éviter d'avance les doublons, réinsertions et modifier plus facilement
            $cadreur = $connexion->prepare('DELETE FROM Participer 
                        WHERE (idMedia = ? AND idRole = ?)');
                    $cadreur->execute([$idVideo, 3]);
            for ($i=0; $i < count($tabResponsable); $i++) { 
                if(!etudiantInBD($tabResponsable[$i]))
                {
                    insertionEtudiant($tabResponsable[$i]);
                }
                // Récupérer l'ID de l'élève
                $idEtudiant = getIdEtudiant($tabResponsable[$i]);
                // Insertion si non existant
                $cadreur = $connexion->prepare('INSERT INTO Participer (idMedia, idEtudiant, idRole) 
                    VALUES (?, ?, ?)');
                $cadreur->execute([$idVideo, $idEtudiant, 3]);
            }
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            $connexion->rollback();
            $connexion = null;
        }
    }
 }

 /**
 * \fn assignerRealisateur($idVideo, $listeRealisateurs)
 * \brief Permet d'assigner un ou des réalisateurs
 * \param idVideo - l'id de la vidéo à laquelle on assigne l'élève
 * \param listeRealisateur - supposément une chaîne de caractères contenant tous les réalisateurs
 */
 function assignerRealisateur($idVideo, $listeRealisateurs){
    if ($listeRealisateurs != "") {
        $connexion = connexionBD();
        // #RISQUE : Si ce n'est pas une chaîne c'est mort le preg_split car ça explose la chaîne en tableau en fonction des chars donnés
        // Normaliser et séparer les cadreurs
        $listeRealisateurs = trim(preg_replace('/\s*,\s*/', ', ', $listeRealisateurs));
        $tabRealisateur = explode(', ', $listeRealisateurs);
        try{
            //On efface toutes les données cadreurs pour éviter d'avance les doublons, réinsertions et modifier plus facilement
            $cadreur = $connexion->prepare('DELETE FROM Participer 
                        WHERE (idMedia = ? AND idRole = ?)');
                    $cadreur->execute([$idVideo, 2]);
            for ($i=0; $i < count($tabRealisateur); $i++) { 
                if(!etudiantInBD($tabRealisateur[$i]))
                {
                    insertionEtudiant($tabRealisateur[$i]);
                }
                // Récupérer l'ID de l'élève
                $idEtudiant = getIdEtudiant($tabRealisateur[$i]);
                // Insertion si non existant
                $cadreur = $connexion->prepare('INSERT INTO Participer (idMedia, idEtudiant, idRole) 
                    VALUES (?, ?, ?)');
                $cadreur->execute([$idVideo, $idEtudiant, 2]);
            }
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            $connexion->rollback();
            $connexion = null;
        }
    }
 }

 /**
 * \fn assignerPromotion($idVid, $valPromo)
 * \brief Assigne la promotion durant laquelle la vidéo a été produite
 * \param idVid - ID de la vidéo
 * \param valPromo - La promotion qu'on va assigner
 */
 function assignerPromotion($idVid, $valPromo)
 {
    $connexion = connexionBD();  
    try{
        $cadreur = $connexion->prepare('UPDATE Media SET promotion = ?, date_modification = CURRENT_TIMESTAMP WHERE id = ?');
        $cadreur->execute([$valPromo, $idVid]);
        $connexion->commit();  
        $connexion = null;
    }
    catch(Exception $e)
    {
        $connexion->rollback();
        $connexion = null;
    }
 }

#################################################
 
#    FONCTIONS "GETTERS" DE RECHERCHE SUR LES TABLES
 
####################################################*/

/**
* \fn getProjet($projet)
* \brief Renvoie le projet lié à une vidéo
* \param projet - nom du projet
*/
 function getProjet($projet)
 {
    $connexion = connexionBD();
    $requeteProj = $connexion->prepare('SELECT * 
    FROM Projet
    WHERE Projet.intitule = ?');                                                 
    try{
        $requeteProj->execute([$projet]);
        $projet = $requeteProj->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        if ($projet) {
            return $projet['id'];
           } 
           else {
               return False;
           }
    }
    catch(Exception $e)
    {
        $connexion = null;
    }
 }

/**
 * \fn getIdEtudiant($etudiant)
 * \brief Renvoie l'id d'un élève
 * \param etudiant - Nom complet de l'étudiant
 */
 function getIdEtudiant($etudiant)
 {
    $connexion = connexionBD();
    $requeteEtudiant = $connexion->prepare('SELECT id 
    FROM Etudiant
    WHERE nomComplet = ?');                                                 
    try{
        $requeteEtudiant->execute([$etudiant]);
        $etudiantCherche = $requeteEtudiant->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        return $etudiantCherche['id'];
    }
    catch(Exception $e)
    {
        $connexion->rollback();
        $connexion = null;
    }
 }

 /**
 * \fn getVideo($path)
 * \brief Renvoie l'id d'une vidéo
 * \param path - chemin de l'espace local de la vidéo
 */
function getVideo($path)
{
   $connexion = connexionBD();
   $requeteVid = $connexion->prepare('SELECT id 
   FROM Media
   WHERE URI_STOCKAGE_LOCAL = ?');                                                 
   try{
       $requeteVid->execute([$path]);
       $vidID = $requeteVid->fetch(PDO::FETCH_ASSOC);
       $connexion = null;
       if ($vidID) {
        return $vidID['id'];
       } 
       else {
           return false;
       }   
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       $connexion = null;
   }
}

/**
 * \fn getInfosVideo($idVideo)
 * \brief Renvoie toutes les informations d'une vidéo
 * \param idVideo - ID de la vidéo dont on veut les informations
 */
function getInfosVideo($idVideo)
{
   $connexion = connexionBD();
   $requeteVid = $connexion->prepare('SELECT * 
   FROM Media
   WHERE id = ?
   AND archive = FALSE');                                                 
   try{
       $requeteVid->execute([$idVideo]);
       $infosVideo = $requeteVid->fetch(PDO::FETCH_ASSOC);
       $connexion = null;
       if ($infosVideo) {
        return $infosVideo;
       } 
       else {
           return false;
       }
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       $connexion = null;
   }
}


function getURISVideo($idVideo)
{
   $connexion = connexionBD();
   $requeteVid = $connexion->prepare('SELECT URI_NAS_PAD, URI_NAS_ARCH
                                        FROM Media
                                        WHERE id = ?');                                                 
   try{
       $requeteVid->execute([$idVideo]);
       $infosVideo = $requeteVid->fetch(PDO::FETCH_ASSOC);
       $connexion = null;
       if ($infosVideo) {
        ajouterLog(LOG_INFORM, "ok");
        return $infosVideo;
       } 
       else {
        ajouterLog(LOG_INFORM, "pas ok");
           return false;
       }
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       ajouterLog(LOG_INFORM, "err");
       $connexion = null;
   }
}

/**
 * \fn getTitreURIEtId($nbMaxVideo)
 * \brief Renvoie tous les titres, ids des vidéos et leur chemin d'accès dans la machine locale
 * \param nbMaxVideo - Le nombre maximum de vidéos dont on veut obtenir les informations
 * \return resultat - Tableau des vidéos et informations obtenues
 */
function getTitreURIEtId($nbMaxVideo) {
    try {
        $connexion = connexionBD();
        $requeteVid = $connexion->prepare('SELECT id,
        URI_STOCKAGE_LOCAL, mtd_tech_titre
        FROM Media
        WHERE archive = FALSE
        ORDER BY date_modification DESC
        LIMIT :nbVideo');
        $requeteVid->bindParam(":nbVideo", $nbMaxVideo,PDO::PARAM_INT);
        $requeteVid->execute();
        $resultat = $requeteVid->fetchAll(PDO::FETCH_ASSOC);
        $connexion = null;
        if (!empty($resultat)) {
            return $resultat; // Retourne un tableau
        } else {
            return false; // Aucun résultat trouvé
        }
    } catch (Exception $e) {
        ajouterLog(LOG_CRITICAL, "Erreur SQL: " . $e->getMessage());
        if ($connexion) {
            $connexion->rollback();
        }
        $connexion = null;
        error_log('Erreur dans getTitreURIEtId: ' . $e->getMessage());
        return false;
    }
}

/**
 * \fn getProfId($profNom, $profPrenom)
 * \brief Renvoie l'id du professeur ayant le nom et prenom spécifié
 * \param profNom - Nom du professeur
 * \param profPrenom - Prénom du professeur
 * \return profCherche - Données sur le professeur obtenu 
 */
function getProfId($profNom, $profPrenom)
{
   $connexion = connexionBD();
   $requeteProf = $connexion->prepare('SELECT identifiant 
   FROM Professeur
   WHERE nom = ? AND prenom = ?');                                                 
   try{
       $requeteProf->execute([$profNom, $profPrenom]);
       $profCherche = $requeteProf->fetch(PDO::FETCH_ASSOC);
       $connexion = null;
       return $profCherche['identifiant'] ?? null;
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       $connexion = null;
   }
}

/**
 * \fn getProjetIntitule($idProjet)
 * \brief Renvoie l'intitulé du projet
 * \param idProjet - Id du projet qu'on recherche
 * \return projet - Données du projet
 */
function getProjetIntitule($idProjet){
    $connexion = connexionBD();
    
    if (!$connexion) {
        return false; // Retourner false si la connexion échoue
    }
    try {
        $requeteProjet = $connexion->prepare('SELECT intitule FROM Projet WHERE id = ?');
        $requeteProjet->execute([$idProjet]);
        $projet = $requeteProjet->fetch(PDO::FETCH_ASSOC);
        return $projet ? $projet["intitule"] : false;
    } catch (Exception $e) {
        error_log("Erreur lors de la récupération du projet: " . $e->getMessage());
        return false;
    } finally {
        $connexion = null; // Fermeture propre de la connexion
    }
}

/**
 * @getIdProjetVideo
 * @return array|false Renvoie le projet associé à la vidéo via un ID, false si aucun projet n'est attribué
 */
function getIdProjetVideo($idVideo) {
    try {
        $connexion = connexionBD();
        $requeteVid = $connexion->prepare('SELECT projet FROM `Media` WHERE id=:idVideo;');
        $requeteVid->bindParam(":idVideo", $idVideo,PDO::PARAM_INT);
        $requeteVid->execute();
        $resultat = $requeteVid->fetch(PDO::FETCH_ASSOC)["projet"];
        $connexion = null;
        if (!empty($resultat)) {
            return $resultat; // Retourne un tableau
        } else {
            return false; // Aucun résultat trouvé
        }
    } catch (Exception $e) {
        ajouterLog(LOG_CRITICAL, "Erreur SQL: " . $e->getMessage());
        if ($connexion) {
            $connexion->rollback();
        }
        $connexion = null;
        error_log('Erreur dans getTitreURIEtId: ' . $e->getMessage());
        return false;
    }
}

/**
 * \fn getProfNomPrenom($identifiant)
 * \brief Renvoie le nom et le prénom du professeur
 * \param identifiant - identifiant du professeur dont on recherche les informations
 * \return profCherche - Données du prof recherché
 */
function getProfNomPrenom($identifiant)
{
   $connexion = connexionBD();
   $requeteProf = $connexion->prepare('SELECT nom, prenom 
   FROM Professeur
   WHERE identifiant = ?');                                                 
   try{
       $requeteProf->execute([$identifiant]);
       $profCherche = $requeteProf->fetch(PDO::FETCH_ASSOC);
       $connexion = null;
       return $profCherche ? [$profCherche['nom'], $profCherche['prenom']] : ["", ""];
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       $connexion = null;
   }
}

/**
 * \fn getAllProfesseurs()
 * \brief Renvoie la liste de tous les professeurs (nom + prenom)
 * \return professeurs - Liste de tous les professeurs
 */
function getAllProfesseurs(){
    $connexion = connexionBD();
   $requeteProf = $connexion->prepare('SELECT nom, prenom 
   FROM Professeur');                                                 
   try{
       $requeteProf->execute();
       $professeurs = $requeteProf->fetchAll(PDO::FETCH_ASSOC);
       $connexion = null;
       return $professeurs;
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       $connexion = null;
   }
}

/**
 * \fn getParticipants($idVid)
 * \brief Retourne tous les participants d'un projet
 * \param idVid - La vidéo dont on veut connaître tous les participants pour sa production
 * \return [realisateur,cadreur,son] - Tableau des participants dans chaque rôle
 */
function getParticipants($idVid) {
    $connexion = connexionBD();
    
    // Requête pour le réalisateur
    $requeteRealisateur = $connexion->prepare('SELECT Etudiant.nomComplet FROM Etudiant JOIN Participer ON Etudiant.id = Participer.idEtudiant WHERE Participer.idMedia = ? AND Participer.idRole = ?');
    $requeteRealisateur->execute([$idVid, 2]);
    $realisateur = $requeteRealisateur->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour le cadreur
    $requeteCadreur = $connexion->prepare('SELECT Etudiant.nomComplet FROM Etudiant JOIN Participer ON Etudiant.id = Participer.idEtudiant WHERE Participer.idMedia = ? AND Participer.idRole = ?');
    $requeteCadreur->execute([$idVid, 1]);
    $cadreur = $requeteCadreur->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour le son
    $requeteSon = $connexion->prepare('SELECT Etudiant.nomComplet FROM Etudiant JOIN Participer ON Etudiant.id = Participer.idEtudiant WHERE Participer.idMedia = ? AND Participer.idRole = ?');
    $requeteSon->execute([$idVid, 3]);
    $son = $requeteSon->fetchAll(PDO::FETCH_ASSOC);

    // Fermeture de la connexion
    $connexion = null;

    // #RISQUE traitement des variables si plusieurs personnes ont le même rôle
    return [
        $realisateur ? $realisateur[0]["nomComplet"] : "",
        $cadreur ? $cadreur[0]["nomComplet"] : "",
        $son ? $son[0]["nomComplet"] : ""
    ];
}

###########################
#     TRUE / FALSE
############################


/**
 * \fn etudiantInBD($etudiant)
 * \brief Renvoie un boléen si l'élève est dans la base de données
 * \param etudiant - Etudiant recherché
 * \return boolean (Vrai ou Faux)
 */

function etudiantInBD($etudiant)
{
    $connexion = connexionBD(); 
    $requeteEtudiant = $connexion->prepare('SELECT 1 
    FROM Etudiant
    WHERE etudiant.nomComplet = ?');                                                 
    try{
        $requeteEtudiant->execute([$etudiant]);
        $resultatEtudiant = $requeteEtudiant->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        if(!$resultatEtudiant){
            return False;
        }
        else {
            return True;
        }
    }
    catch(Exception $e)
    {
        $connexion->rollback();
        $connexion = null;
    }
}

/**
 * \fn fetchAll($sql)
 * \brief fetchAll("SELECT * FROM Media"); va renvoyer toutes les infos des vidéos
 * NE PAS UTILISER SI POSSIBLE. TOUJOURS PRÉFÉRER LA CRÉATION D'UNE NOUVELLE FONCTION
 * \param sql - Requête qu'on aimerait exécuter
 * \return resultat - Données récupérées via la requête
 */
function fetchAll($sql){
    try {
        $connexion = connexionBD();
        $requete = $connexion->prepare($sql);
        $requete->execute();
        $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
        $connexion = null;
        if (!empty($resultat)) {
            return $resultat;
        } else {
            return false;
        }
    } catch (Exception $e) {
        if ($connexion) {
            $connexion->rollback();
        }
        $connexion = null;
        error_log('Erreur : ' . $e->getMessage());
        return false;
    }
}

/**
 * \fn verifierPresenceVideoStockageLocal($cheminFichier, $nomFichier)
 * \brief Vérifie qu'une vidéo existe bien dans le stockage local. Renvoie 1 si une vidéo existe
 * \param cheminFichier - chemin de l'espace local de la vidéo
 * \param nomFichier - nom du fichier
 * \return boolean
 */
function verifierPresenceVideoStockageLocal($cheminFichier, $nomFichier)
{
   $connexion = connexionBD();
   $requeteVid = $connexion->prepare('SELECT 1
   FROM Media
   WHERE URI_STOCKAGE_LOCAL = ?
   AND mtd_tech_titre = ?');                                                 
   try{
       $requeteVid->execute([$cheminFichier, $nomFichier]);
       $vidPresente = $requeteVid->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        return (bool)$vidPresente;
   }
   catch(Exception $e)
   {
       $connexion = null;
   }
}

/**
 * \fn connexionProfesseur($loginUser, $passwordUser)
 * \brief Fonction qui regarde si un prof existe pour un couple login/mdp passé en paramètre
 * \param loginUser - Identifiant de connexion du professeur
 * \param passwordUser - Mot de passe de l'utilisateur
 * \return resultatRequeteConnexion - Données retournées par la requête de connexion
 */
function connexionProfesseur($loginUser, $passwordUser){
   $connexion = connexionBD();                     
   try{
        $requeteConnexion = $connexion->prepare('SELECT role
        FROM Professeur P
        WHERE P.identifiant = ?
        AND P.motdepasse = ?');   
        $requeteConnexion->execute([$loginUser, $passwordUser]);
        $resultatRequeteConnexion = $requeteConnexion->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        return $resultatRequeteConnexion;
   }
   catch(Exception $e)
   {
       $connexion = null;
   }
}

/**
 * \fn recupererProjetDerniereVideoModifiee()
 * \brief Fonction qui récupère le projet contenant la dernière vidéo modifiée
 * \return resultatRequeteConnexion - Données retournées par la requête de connexion
 */
function recupererProjetDerniereVideoModifiee(){
    $connexion = connexionBD();                     
    try{
         $requeteConnexion = $connexion->prepare('SELECT projet FROM Media
            WHERE projet IS NOT NULL
            ORDER BY date_modification DESC
            LIMIT 1;');   
         $requeteConnexion->execute();
         $resultatRequeteConnexion = $requeteConnexion->fetch(PDO::FETCH_ASSOC);
         $connexion = null;
         if (!empty($resultatRequeteConnexion["projet"])) {
            return $resultatRequeteConnexion["projet"];
        } else {
            return false;
        }
    }
    catch(Exception $e)
    {
        $connexion = null;
    }
 }

/**
 * Fonction qui renvoie la liste des vidéos transférées récemment
 * en attente de métadonnées
 */
function recupererDernieresVideosTransfereesSansMetadonnees($nb_videos_historique_transfert){
    $connexion = connexionBD();                  
    try{
         $requeteConnexion = $connexion->prepare('SELECT id,date_creation,mtd_tech_titre FROM Media
			WHERE `professeurReferent` IS NULL AND `promotion` IS NULL AND `Description` IS NULL AND `theme` IS NULL
            ORDER BY date_creation DESC
            LIMIT :nb_videos_historique_transfert');
         $requeteConnexion->bindParam(":nb_videos_historique_transfert", $nb_videos_historique_transfert,PDO::PARAM_INT);
         $requeteConnexion->execute();
         $resultatRequeteConnexion = $requeteConnexion->fetchAll(PDO::FETCH_ASSOC);
         $connexion = null;
         if (!empty($resultatRequeteConnexion)) {
            return $resultatRequeteConnexion;
        } else {
            return false;
        }
    }
    catch(Exception $e)
    {
        $connexion = null;
    }
 }

/**
 * \fn recupererUriTitreVideosMemeProjet($idProjet)
 * \brief Fonction qui retourne id, URI_STOCKAGE_LOCAL, mtd_tech_titre et projet selon un projet
 * \param idProjet - Identifiant d'un projet
 * \return resultatRequeteConnexion - Retourne une vidéo si trouvé
 */
 function recupererUriTitreVideosMemeProjet($idProjet){
    $connexion = connexionBD();                     
    try{
        //#RISQUE : Ajouter une LIMIT avec la constante NB_VIDE_PAR_SLIDER
         $requeteConnexion = $connexion->prepare('SELECT id, URI_STOCKAGE_LOCAL, mtd_tech_titre, projet
            FROM Media
            WHERE projet = ?
            AND archive = FALSE
            ORDER BY date_modification DESC');   
         $requeteConnexion->execute([$idProjet]);
         $resultatRequeteConnexion = $requeteConnexion->fetchAll(PDO::FETCH_ASSOC);
         $connexion = null;
         if (!empty($resultatRequeteConnexion)) {
            return $resultatRequeteConnexion;
        } else {
            return false;
        }
    }
    catch(Exception $e)
    {
        $connexion = null;
    }
 }
 
 /**
 * \fn supprimerVideoDeBD($idVideo)
 * \brief Indique dans la base de données que la vidéo est supprimée
 * \param idVideo - L'ID de la vidéo qu'on veut supprimer
 */
function supprimerVideoDeBD($idVideo){
    $connexion = connexionBD();
    $requeteConnexion=$connexion->prepare('UPDATE MEDIA SET archive = TRUE, date_modification = CURRENT_TIMESTAMP WHERE id = ?');
    $requeteConnexion->execute([$idVideo]);
    $connexion->commit();
}

function recupererAutorisationsProfesseurs(){
    $connexion = connexionBD();
    try{
            $requeteConnexion = $connexion->prepare('SELECT Professeur.nom, Professeur.prenom, Autorisation.professeur, Autorisation.modifier, Autorisation.supprimer, Autorisation.diffuser, Autorisation.administrer
                FROM Autorisation
                JOIN Professeur ON Professeur.identifiant = Autorisation.professeur');
            $requeteConnexion->execute();
            $resultatRequeteConnexion = $requeteConnexion->fetchAll(PDO::FETCH_ASSOC);
            $connexion = null;
            return $resultatRequeteConnexion;
    }
    catch(Exception $e)
    {
        $connexion = null;
    }
}

function recupererAutorisationsProfesseur($identifiant){
    $connexion = connexionBD();
    try{
        $requeteConnexion = $connexion->prepare('SELECT modifier, diffuser, supprimer, administrer
            FROM Autorisation
            WHERE professeur = ?');
        $requeteConnexion->execute([$identifiant]);
        $resultatRequeteConnexion = $requeteConnexion->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        return $resultatRequeteConnexion;
    }
    catch(Exception $e)
    {
        $connexion = null;
    }
}

function mettreAJourAutorisations($prof, $colonne, $etat){
    $valeur = ($etat == "true") ? 1 : 0 ;
    $connexion = connexionBD();
    try{
        $requete = $connexion->prepare('UPDATE Autorisation SET ' . $colonne . ' = ? WHERE professeur = ?');
        $requete->execute([$valeur, $prof]);
        $connexion->commit();
        $connexion = null;
    }
    catch(Exception $e)
    {
        $connexion->rollback();
        $connexion = null;
    }
}
?>