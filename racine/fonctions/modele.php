<?php
/**
 *  @NOM : methodes.php
 *  @DESCRIPTION : Fonctions php liées aux manipulations (insertions, suppressions...) sur la base de données
 *  @CREATION : 19/12/2024
 *  @DERNIERE MODIFICATION : 08/01/2025 - XXHXX
 *  @COLLABORATEURS : Elsa Lavergne
 */

/**
 *  @Nom : connexionBD
  * @Description : Permet de se connecter en base de données et de checker au passage s'il y a eu des erreurs de connexion
 */
function connexionBD()
{
    try
    {
        // #RISQUE : Changement de l'utilisateur BD
        $mysqlClient = new PDO('mysql:host=mysql_BTSPlay;port=3306:3306;dbname=mydatabase', 'myuser', 'mypassword');
        $mysqlClient->beginTransaction(); // Ne pas réassigner à $connexion ici !
        $mysqlClient->exec("SET NAMES 'utf8mb4'");
        return $mysqlClient;
    }
    catch (Exception $e)
    {
        die('Erreur : ' . $e->getMessage());
    }
}


/**#########################################
 *
 *           INSERTIONS DANS LA BD
 * 
 */########################################

 /**
  * @Nom : insertionDonneesTechniques
  * @Description : crée la vidéo en base de données et insère les métadonnées techniques associées 
  * @$listeMetadonnees : liste des metadonnées techniques à insérer
  */
  function insertionDonneesTechniques($listeMetadonnees)
  {
      $connexion = connexionBD(); // Connexion à la BD
      
      // Construction de la requête
      $videoAAjouter = $connexion->prepare(
          'INSERT INTO Media (
              URI_NAS_PAD, 
              URI_NAS_ARCH, 
              URI_NAS_MPEG,
              mtd_tech_titre,
              mtd_tech_duree,
              mtd_tech_resolution,
              mtd_tech_fps,
              mtd_tech_format
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
      );
  
      try {
        if(!getVideo($listeMetadonnees[MTD_URI_NAS_MPEG]))
          // Ajout des paramètres
          $videoAAjouter->execute([
            $listeMetadonnees[MTD_URI_NAS_PAD],
            $listeMetadonnees[MTD_URI_NAS_ARCH],
              $listeMetadonnees[MTD_URI_NAS_MPEG],
              $listeMetadonnees['Titre'],
              $listeMetadonnees['Durée'],
              $listeMetadonnees['Resolution'],
              $listeMetadonnees['FPS'],
              $listeMetadonnees['Format']
          ]);
          $connexion->commit(); // Valider la transaction
          $connexion = null; // Fermeture de la connexion
      } catch (Exception $e) {
          $connexion->rollback(); // Annuler la transaction
          $connexion = null;
      }
  }

/** NOM : Alejandro Boufarti 
 * Prenom : 
*/

/**
* @Nom : insertionProfesseur
* @Description : gère l'insertion des professeurs et lie le professeur à un/des projets
* @nomProf et prenomProf : assez explicite, il serait préférable de renvoyer les deux individuellement pour faire les comparaisons en bd plus facilement mais j'arrangerai ça plus tard au pire
 */
function insertionProfesseur($prof)
{
    $connexion = connexionBD();     
    try{
        if(!profInBD($prof))
        {
            $ajoutProfesseur = $connexion->prepare('INSERT INTO Professeur (nomComplet) VALUES (?)');
            $ajoutProfesseur->execute([$prof]); 
            $connexion->commit();
        }
        else {
            //Sinon rien à faire
            $connexion = null;
        }
        
    }
    catch(Exception $e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
}

/**
 * @Nom : insertionProjet
 * @Description : gère l'insertion des projets et lie le projet à un/des médias
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
            //Sinon rien à faire
            $connexion = null;
        }
        
    }
    catch(Exception $e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
}

/**
* @Nom : insertionEleve
* @Description : gère l'insertion des professeurs et lie le professeur à un/des projets
* @nomProf et prenomProf : assez explicite, il serait préférable de renvoyer les deux individuellement pour faire les comparaisons en bd plus facilement mais j'arrangerai ça plus tard au pire
 */
<<<<<<< HEAD
function insertionEleve($eleve)
{
    if ($eleve != "") {
        $connexion = connexionBD();  
        try{
            $verif = $connexion->prepare('INSERT INTO ELEVE (nomComplet) VALUES (?)');
            $eleveAAjouter= $verif->execute([
                $eleve]);          
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
            $connexion = null;
        }
=======
function insertionEleve($video, $eleve)
{
    $connexion = connexionBD();  
    try{
        $verif = $connexion->prepare('INSERT INTO ELEVE (nomComplet) VALUES (?)');
        $eleveAAjouter= $verif->execute([
            $eleve]);          
        $connexion->commit();  
        $connexion = null;
    }
    catch(Exception $e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
    }
}

/**
* @Nom : insertionDonneesEditoriales
* @Description : insère les métadonnées éditoriales sur la vidéo concernée
 */

 function insertionDonneesEditoriales($videoTitre, $listeEdito)
 {
    $connexion = connexionBD(); // Connexion à la BD
    try{
        $idVid = getVideo($videoTitre);           //Permet d'obtenir l'id exact de la vidéo à partir du titre 
        insertionProfesseur($listeEdito['prof']);
        assignerProfReferent($idVid, $listeEdito['prof']);
        assignerCadreur($idVid, $listeEdito['cadreurs']);
        assignerResponsable($idVid, $listeEdito['responsables']);
        assignerRealisateur($idVid, $listeEdito['realisateurs']);
        insertionProjet($listeEdito['projet']);
        assignerProjet($idVid, $listeEdito['projet']);
        assignerPromotion($idVid, $listeEdito['promotion']);
    }
    catch(Exception $e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }

/**#########################################
 *
 *           ASSIGNER DANS LA BD
 * 
 */########################################

/**
 * assignerProjet
 * Permet d'assigner un projet au média
 * idVideo : l'id de la vidéo à laquelle on assigne le projet
 * projet : libelle du projet
 */

 function assignerProjet($idVideo, $projet) {
    $connexion = connexionBD(); // Connexion à la BD
    try {
<<<<<<< HEAD

        $idProjet = getProjet($projet);
        if (!$idProjet) {
            insertionProjet($projet);
            $idProjet = getProjet($projet);
        }

=======
        // Rechercher le projet par intitule
        $projetAAjouter = $connexion->prepare('SELECT id FROM Projet WHERE intitule = ?');
        $projetAAjouter->execute([$projet]); 
        $projetAjouter = $projetAAjouter->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif
        var_dump($projet, $projetAjouter);
        // Vérifiez si le professeur existe
        if (!$projetAjouter || !isset($projetAjouter['id'])) {
            throw new Exception("Projet non trouvé ou ID manquant pour : $projet");
        }

        // Vérification des types (éviter l'erreur Array to string conversion)
        if (!is_scalar($projetAjouter['id']) || !is_scalar($idVideo)) {
            throw new Exception("Les données fournies ne sont pas scalaires (idProjet ou idVideo).");
        }

        // Mettre à jour la table `media` avec l'ID du professeur
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
        $setIDProjet = $connexion->prepare('UPDATE media 
                                          SET projet = ?
                                          WHERE id = ?');
        
        // Exécution de la mise à jour
        $setIDProjet->execute([
<<<<<<< HEAD
            $idProjet, // Utilisation de l'ID du professeur récupéré
=======
            $projetAjouter['id'], // Utilisation de l'ID du professeur récupéré
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
            $idVideo
        ]);

        // Commit de la transaction
        $connexion->commit();
        $connexion = null;

    } catch (Exception $e) {
        // Gestion des erreurs
        if ($connexion) {
            $connexion->rollback(); // Annule la transaction en cas d'erreur
        }
        $connexion = null;
    }
}

<<<<<<< HEAD
 /**
=======
/**
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
 * assignerProfReferent
 * Permet d'assigner un professeur référent au projet
 * idVideo : l'id de la vidéo à laquelle on assigne le professeur
 * prof : nomComplet du professeur
 */

 function assignerProfReferent($idVideo, $prof) {
<<<<<<< HEAD

    $lastSpacePos = strrpos($prof, ' ');

    // Vérifier si un espace existe
    if ($lastSpacePos !== false) {
        // Séparer le prénom du nom
        $profNom = substr($prof, 0, $lastSpacePos);  
        $profPrenom = substr($prof, $lastSpacePos + 1);  

        $connexion = connexionBD(); // Connexion à la BD
        try {
            $idProf = getProfId($profNom, $profPrenom);

            if(!$idProf)
            {
                $setIDProf = $connexion->prepare('UPDATE media 
                SET professeurReferent = NULL
                WHERE id = ?');

                // Exécution de la mise à jour
                $setIDProf->execute([
                $idVideo
                ]);
                $connexion = null;
            }
            else {
                // Mettre à jour la table `media` avec l'ID du professeur
            $setIDProf = $connexion->prepare('UPDATE media 
            SET professeurReferent = ?
            WHERE id = ?');

            // Exécution de la mise à jour
            $setIDProf->execute([
            $idProf, // Utilisation de l'ID du professeur récupéré
            $idVideo
            ]);

            // Commit de la transaction
            $connexion->commit();
            $connexion = null;
            }
        } catch (Exception $e) {
            // Gestion des erreurs
            if ($connexion) {
                $connexion->rollback(); // Annule la transaction en cas d'erreur
            }
            $connexion = null;
        }
=======
    $connexion = connexionBD(); // Connexion à la BD
    try {
        // Rechercher le professeur par nom complet
        $profAAjouter = $connexion->prepare('SELECT id FROM Professeur WHERE nomComplet = ?');
        $profAAjouter->execute([$prof]); 
        $profAjoute = $profAAjouter->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif

        // Vérifiez si le professeur existe
        if (!$profAjoute || !isset($profAjoute['id'])) {
            throw new Exception("Professeur non trouvé ou ID manquant pour : $prof");
        }

        // Vérification des types (éviter l'erreur Array to string conversion)
        if (!is_scalar($profAjoute['id']) || !is_scalar($idVideo)) {
            throw new Exception("Les données fournies ne sont pas scalaires (idProf ou idVideo).");
        }

        // Mettre à jour la table `media` avec l'ID du professeur
        $setIDProf = $connexion->prepare('UPDATE media 
                                          SET professeurReferent = ?
                                          WHERE id = ?');
        
        // Exécution de la mise à jour
        $setIDProf->execute([
            $profAjoute['id'], // Utilisation de l'ID du professeur récupéré
            $idVideo
        ]);

        // Commit de la transaction
        $connexion->commit();
        $connexion = null;

    } catch (Exception $e) {
        // Gestion des erreurs
        if ($connexion) {
            $connexion->rollback(); // Annule la transaction en cas d'erreur
        }
        $connexion = null;
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
    }
}

/**
 * assignerCadreur
 * Permet d'assigner un ou des cadreurs au projet
 * idVideo : l'id de la vidéo à laquelle on assigne le professeur
 * listeCadreurs : supposément une chaîne de caractères contenant tous les cadreurs
 */

 function assignerCadreur($idVideo, $listeCadreurs){
<<<<<<< HEAD

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

                if(!eleveInBD($tabCadreur[$i]))
                {
                    insertionEleve($tabCadreur[$i]);
                }
                    // Récupérer l'ID de l'élève
                $idEleve = getIdEleve($tabCadreur[$i]);

                // Insertion si non existant
                $cadreur = $connexion->prepare('INSERT INTO Participer (idMedia, idEleve, idRole) 
                    VALUES (?, ?, ?)');
                $cadreur->execute([$idVideo, $idEleve, 1]);
            }
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            $connexion->rollback();             //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
            $connexion = null;
        }
=======
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
            if(!eleveInBD($tabCadreur[$i]))
            {
                insertionEleve($idVideo, $tabCadreur[$i]);
            }
                // Récupérer l'ID de l'élève
            $idEleve = getIdEleve($tabCadreur[$i]);

            // Insertion si non existant
            $cadreur = $connexion->prepare('INSERT INTO Participer (idMedia, idEleve, idRole) 
                VALUES (?, ?, ?)');
            $cadreur->execute([$idVideo, $idEleve, 1]);
        }
        $connexion->commit();  
        $connexion = null;
    }
    catch(Exception $e)
    {
        $connexion->rollback();             //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
    }
 }

/**
 * assignerResponsable
 * Permet d'assigner un ou des responsables sons
 * idVideo : l'id de la vidéo à laquelle on assigne l'élève
 * listeResponsable : supposément une chaîne de caractères contenant tous les cadreurs
 */

 function assignerResponsable($idVideo, $listeResponsable){
<<<<<<< HEAD

    if ($listeResponsable != "") {
        $connexion = connexionBD();

        // #RISQUE : Si ce n'est pas une chaîne c'est mort le preg_split car ça explose la chaîne en tableau en fonction des chars donnés
        // Normaliser et séparer les cadreurs
        $listeResponsable = trim(preg_replace('/\s*,\s*/', ', ', $listeResponsable));
        $tabResponsable = explode(', ', $listeResponsable);

        try{

            //On efface toutes les données cadreurs pour éviter d'avance les doublons, réinsertions et modifier plus facilement
            $cadreur = $connexion->prepare('DELETE FROM Participer 
                        WHERE (idMedia = ? AND idRole = ?)');
                    $cadreur->execute([$idVideo, 3]);

            for ($i=0; $i < count($tabResponsable); $i++) { 
                if(!eleveInBD($tabResponsable[$i]))
                {
                    insertionEleve($tabResponsable[$i]);
                }
                    // Récupérer l'ID de l'élève
                $idEleve = getIdEleve($tabResponsable[$i]);

                // Insertion si non existant
                $cadreur = $connexion->prepare('INSERT INTO Participer (idMedia, idEleve, idRole) 
                    VALUES (?, ?, ?)');
                $cadreur->execute([$idVideo, $idEleve, 3]);
            }
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            $connexion->rollback();             //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
            $connexion = null;
        }
=======
    $connexion = connexionBD();

    // #RISQUE : Si ce n'est pas une chaîne c'est mort le preg_split car ça explose la chaîne en tableau en fonction des chars donnés
    // Normaliser et séparer les cadreurs
    $listeResponsable = trim(preg_replace('/\s*,\s*/', ', ', $listeResponsable));
    $tabResponsable = explode(', ', $listeResponsable);

    try{

        //On efface toutes les données cadreurs pour éviter d'avance les doublons, réinsertions et modifier plus facilement
        $cadreur = $connexion->prepare('DELETE FROM Participer 
                    WHERE (idMedia = ? AND idRole = ?)');
                $cadreur->execute([$idVideo, 3]);

        for ($i=0; $i < count($tabResponsable); $i++) { 
            if(!eleveInBD($tabResponsable[$i]))
            {
                insertionEleve($idVideo, $tabResponsable[$i]);
            }
                // Récupérer l'ID de l'élève
            $idEleve = getIdEleve($tabResponsable[$i]);

            // Insertion si non existant
            $cadreur = $connexion->prepare('INSERT INTO Participer (idMedia, idEleve, idRole) 
                VALUES (?, ?, ?)');
            $cadreur->execute([$idVideo, $idEleve, 3]);
        }
        $connexion->commit();  
        $connexion = null;
    }
    catch(Exception $e)
    {
        $connexion->rollback();             //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
    }
 }

 /**
 * assignerRealisateur
 * Permet d'assigner un ou des réalisateurs
 * idVideo : l'id de la vidéo à laquelle on assigne l'élève
 * listeRealisateur : supposément une chaîne de caractères contenant tous les cadreurs
 */

 function assignerRealisateur($idVideo, $listeRealisateurs){
<<<<<<< HEAD

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
                if(!eleveInBD($tabRealisateur[$i]))
                {
                    insertionEleve($tabRealisateur[$i]);
                }
                    // Récupérer l'ID de l'élève
                $idEleve = getIdEleve($tabRealisateur[$i]);

                // Insertion si non existant
                $cadreur = $connexion->prepare('INSERT INTO Participer (idMedia, idEleve, idRole) 
                    VALUES (?, ?, ?)');
                $cadreur->execute([$idVideo, $idEleve, 2]);
            }
            $connexion->commit();  
            $connexion = null;
        }
        catch(Exception $e)
        {
            $connexion->rollback();             //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
            $connexion = null;
        }
=======
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
            if(!eleveInBD($tabRealisateur[$i]))
            {
                insertionEleve($idVideo, $tabRealisateur[$i]);
            }
                // Récupérer l'ID de l'élève
            $idEleve = getIdEleve($tabRealisateur[$i]);

            // Insertion si non existant
            $cadreur = $connexion->prepare('INSERT INTO Participer (idMedia, idEleve, idRole) 
                VALUES (?, ?, ?)');
            $cadreur->execute([$idVideo, $idEleve, 2]);
        }
        $connexion->commit();  
        $connexion = null;
    }
    catch(Exception $e)
    {
        $connexion->rollback();             //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
    }
 }

 /**
 * assignerPromotion
 * @Assigne la promotion durant laquelle la vidéo a été produite
 */

 function assignerPromotion($idVid, $valPromo)
 {
    $connexion = connexionBD();  
    try{
        // Insertion si non existant
        $cadreur = $connexion->prepare('UPDATE Media SET promotion = ? WHERE id = ?');
<<<<<<< HEAD
        $cadreur->execute([$valPromo, $idVid]);
=======
        $cadreur->execute([$valPromo, $idVideo]);
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
        $connexion->commit();  
        $connexion = null;
    }
    catch(Exception $e)
    {
        $connexion->rollback();             //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }

/** #################################################
 * 
 *    FONCTIONS "GETTERS" DE RECHERCHE SUR LES TABLES
 * 
 *####################################################*/

 /**
* @Nom : getProjet
* @Description : renvoie le projet lié à une vidéo
* @projet : nom du projet
 */

 function getProjet($projet)
 {
    //connexion
    $connexion = connexionBD();
    
    $requeteProj = $connexion->prepare('SELECT * 
    FROM Projet
    WHERE Projet.intitule = ?');                                                 
    try{
        $requeteProj->execute([$projet]);
        $projet = $requeteProj->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif

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
 * getIdEleve
 * renvoie l'id d'un élève
 * Ce code est catastrophique bref
 */
 function getIdEleve($eleve)
 {
    $connexion = connexionBD();                                                         // Connexion à la BD
    $requeteEleve = $connexion->prepare('SELECT id 
    FROM Eleve
    WHERE nomComplet = ?');                                                 
    try{
        $requeteEleve->execute([$eleve]);
        $eleveCherche = $requeteEleve->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif
        $connexion = null;
        return $eleveCherche['id'];
    }
    catch(Exception $e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }

 /**
 * getVideo
 * renvoie l'id d'une vidéo
 * $path : chemin NAS MPEG de la vidéo
 */
function getVideo($path)
{
   $connexion = connexionBD();                                                         // Connexion à la BD
   $requeteVid = $connexion->prepare('SELECT id 
   FROM Media
   WHERE URI_NAS_MPEG = ?');                                                 
   try{
       $requeteVid->execute([$path]);
       $vidID = $requeteVid->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif
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
       $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
       $connexion = null;
   }
}


/**
 * @getUriNASMPEG
 * @return array|false Renvoie la liste des URI NAS MPEG ou false en cas d'échec
 */
function getUriNASMPEG() {
    try {
        // Connexion à la base de données
        $connexion = connexionBD();

        // Préparation de la requête
        $requeteVid = $connexion->prepare('SELECT URI_NAS_MPEG FROM Media');
        
        // Exécution de la requête
        $requeteVid->execute();

        // Récupérer toutes les lignes sous forme de tableau associatif
        $resultat = $requeteVid->fetchAll(PDO::FETCH_ASSOC);

        // Fermer la connexion
        $connexion = null;

        // Vérifier si des résultats existent
        if (!empty($resultat)) {
            return $resultat; // Retourne un tableau des URI_NAS_MPEG
        } else {
            return false; // Aucun résultat trouvé
        }
    } catch (Exception $e) {
        // Gestion des erreurs
        if ($connexion) {
            $connexion->rollback(); // Annule toute transaction si nécessaire
        }
        $connexion = null;

        // Journaliser l'erreur (ou afficher en mode développement)
        error_log('Erreur dans getUriNASMPEG: ' . $e->getMessage());
        return false; // Retourne false en cas d'erreur
    }
}


/**
 * @getUriNASetTitreMPEG
 * @return array|false Renvoie la liste des URI NAS MPEG ou false en cas d'échec
 */
function getUriNASetTitreMPEG() {
    try {
        // Connexion à la base de données
        $connexion = connexionBD();
        // Préparation de la requête
        $requeteVid = $connexion->prepare('SELECT URI_NAS_MPEG, mtd_tech_titre FROM Media');
        // Exécution de la requête
        $requeteVid->execute();
        // Récupérer toutes les lignes sous forme de tableau associatif
        $resultat = $requeteVid->fetchAll(PDO::FETCH_ASSOC);
        // Fermer la connexion
        $connexion = null;
        // Vérifier si des résultats existent
        if (!empty($resultat)) {
            return $resultat; // Retourne un tableau des URI_NAS_MPEG
        } else {
            return false; // Aucun résultat trouvé
        }
    } catch (Exception $e) {
        // Gestion des erreurs
        if ($connexion) {
            $connexion->rollback(); // Annule toute transaction si nécessaire
        }
        $connexion = null;
        // Journaliser l'erreur (ou afficher en mode développement)
        error_log('Erreur dans getUriNASetTitreMPEG: ' . $e->getMessage());
        return false; // Retourne false en cas d'erreur
    }
}

<<<<<<< HEAD
function getUriNASetTitreMPEGEtId($nbMaxVideo) {
    try {
        ajouterLog(LOG_CRITICAL, "$nbMaxVideo");
        // Connexion à la base de données
        $connexion = connexionBD();
       
        // Préparation de la requête
        $requeteVid = $connexion->prepare('SELECT id, URI_NAS_MPEG, mtd_tech_titre FROM Media LIMIT :nbVideo');
        $requeteVid->bindParam(":nbVideo", $nbMaxVideo,PDO::PARAM_INT);
        
        // Exécution de la requête
        $requeteVid->execute();

        // Récupérer toutes les lignes sous forme de tableau associatif
        $resultat = $requeteVid->fetchAll(PDO::FETCH_ASSOC);

        // Fermer la connexion
        $connexion = null;
        // Vérifier si des résultats existent
        if (!empty($resultat)) {
            return $resultat; // Retourne un tableau des URI_NAS_MPEG
        } else {
            return false; // Aucun résultat trouvé
        }
    } catch (Exception $e) {
        ajouterLog(LOG_CRITICAL, "Erreur SQL: " . $e->getMessage());
        // Gestion des erreurs
        if ($connexion) {
            $connexion->rollback(); // Annule toute transaction si nécessaire
        }
        $connexion = null;
        // Journaliser l'erreur (ou afficher en mode développement)
        error_log('Erreur dans getUriNASetTitreMPEG: ' . $e->getMessage());
        return false; // Retourne false en cas d'erreur
    }
}

/**
 * getProfId
 * renvoie l'id d'un prof
 */
function getProfId($profNom, $profPrenom)
{
   $connexion = connexionBD();
   $requeteProf = $connexion->prepare('SELECT identifiant 
   FROM Professeur
   WHERE nom = ? AND prenom = ?');                                                 
   try{
       $requeteProf->execute([$profNom, $profPrenom]);
       $profCherche = $requeteProf->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif
       $connexion = null;
       return $profCherche['identifiant'] ?? null;
   }
   catch(Exception $e)
   {
       $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
       $connexion = null;
   }
}

function getProjetIntitule($idProjet){
    $connexion = connexionBD();
    $requeteProjet = $connexion->prepare('SELECT intitule 
    FROM Projet
    WHERE id = ?');                                                 
    try{
        $requeteProjet->execute([$idProjet]);
        $projet = $requeteProjet->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif
        $connexion = null;
        return $projet ? $projet["intitule"] : "";
    }
    catch(Exception $e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
}

function getProfNomPrenom($identifiant)
{
   $connexion = connexionBD();
   $requeteProf = $connexion->prepare('SELECT nom, prenom 
   FROM Professeur
   WHERE identifiant = ?');                                                 
   try{
       $requeteProf->execute([$identifiant]);
       $profCherche = $requeteProf->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne sous forme de tableau associatif
       $connexion = null;
       return $profCherche ? [$profCherche['nom'], $profCherche['prenom']] : ["", ""];
   }
   catch(Exception $e)
   {
       $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
       $connexion = null;
   }
}

function getParticipants($idVid) {
    $connexion = connexionBD();
    
    // Requête pour le réalisateur
    $requeteRealisateur = $connexion->prepare('SELECT Eleve.nomComplet FROM Eleve JOIN Participer ON Eleve.id = Participer.idEleve WHERE Participer.idMedia = ? AND Participer.idRole = ?');
    $requeteRealisateur->execute([$idVid, 2]);
    $realisateur = $requeteRealisateur->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour le cadreur
    $requeteCadreur = $connexion->prepare('SELECT Eleve.nomComplet FROM Eleve JOIN Participer ON Eleve.id = Participer.idEleve WHERE Participer.idMedia = ? AND Participer.idRole = ?');
    $requeteCadreur->execute([$idVid, 1]);
    $cadreur = $requeteCadreur->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour le son
    $requeteSon = $connexion->prepare('SELECT Eleve.nomComplet FROM Eleve JOIN Participer ON Eleve.id = Participer.idEleve WHERE Participer.idMedia = ? AND Participer.idRole = ?');
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


=======
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
/**###########################
  *     TRUE / FALSE
  ############################*/

  /**   eleveInBD
   *  Renvoie un boléen si l'élève est dans la base de données
   */
    function eleveInBD($eleve)
    {
        $connexion = connexionBD(); 
        $requeteEleve = $connexion->prepare('SELECT nomComplet 
        FROM Eleve
        WHERE eleve.nomComplet = ?');                                                 
        try{
            $requeteEleve->execute([$eleve]);
            $resultatEleve = $requeteEleve->fetch(PDO::FETCH_ASSOC);
            $connexion = null;
            if(!$resultatEleve){
                return False;
            }
            else {
                return True;
            }
        }
        catch(Exception $e)
        {
            $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
            $connexion = null;
        }
    }

/** profInBD
 *  Renvoie un booléen si le professeur est dans la base
 * 
 */

 function profInBD($prof)
 {
    $connexion = connexionBD();                     
    try{
        $requeteProf = $connexion->prepare('SELECT nomComplet 
        FROM Professeur
        WHERE Professeur.nomComplet = ?');   
        $requeteProf->execute([$prof]);
            $resultatProf = $requeteProf->fetchAll();
            $connexion = null;
            if(count($resultatProf) == 0 ){
                return False;
            }
            else {
                return True;
            }
    }
    catch(Exception $e)
    {
        $connexion->rollback();                                                         //En cas d'erreurs, on va essayer de lancer un rollback plutôt que de commit
        $connexion = null;
    }
 }

<<<<<<< HEAD


/*
*  fonction fetchAll couteau suisse
*  ex: fetchAll("SELECT * FROM Media"); va renvoyer toutes les info des vidéos
*/

function fetchAll($sql){

    try {
        // Connexion à la base de données
        $connexion = connexionBD();
        // Préparation de la requête
        $requete = $connexion->prepare($sql);
        // Exécution de la requête
        $requete->execute();
        // Récupérer toutes les lignes sous forme de tableau associatif
        $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
        // Fermer la connexion
        $connexion = null;
        // Vérifier si des résultats existent
        if (!empty($resultat)) {
            return $resultat; // Retourne un tableau des URI_NAS_MPEG
        } else {
            return false; // Aucun résultat trouvé
        }
    } catch (Exception $e) {
        // Gestion des erreurs
        if ($connexion) {
            $connexion->rollback(); // Annule toute transaction si nécessaire
        }
        $connexion = null;
        // Journaliser l'erreur (ou afficher en mode développement)
        error_log('Erreur dans getUriNASetTitreMPEG: ' . $e->getMessage());
        return false; // Retourne false en cas d'erreur
    }
}

/**
 * verifierPresenceVideoNAS_MPEG
 * renvoie 1 si une vidéo existe
 * $cheminFichier : chemin NAS MPEG de la vidéo
 * $nomFichier : nom du fichier
 */
function verifierPresenceVideoNAS_MPEG($cheminFichier, $nomFichier)
{
   $connexion = connexionBD();
   $requeteVid = $connexion->prepare('SELECT 1
   FROM Media
   WHERE URI_NAS_MPEG = ?
   AND mtd_tech_titre = ?');                                                 
   try{
       $requeteVid->execute([$cheminFichier, $nomFichier]);
       $vidPresente = $requeteVid->fetch(PDO::FETCH_ASSOC);
        $connexion = null;
        return (bool)$vidPresente;
   }
   catch(Exception $e)
   {
       $connexion->rollback();
       $connexion = null;
   }
}

=======
>>>>>>> 5a6d86a8dcd38a1359e1d573580493a2221e1f3b
?>