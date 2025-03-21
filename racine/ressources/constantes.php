<?php

/*******************CONSTANTES NON PARAMETRABLES************************/
//CONSTANTES DES URIS
    // #RISQUE : Changement des répertoires des NAS
const PREFIXE_DOSSIER_VIDEO = '_BTSPLAY_';
const SUFFIXE_MINIATURE_VIDEO = '_miniature.png';
const SUFFIXE_VIDEO = '.mp4';

//CONSTANTES DES CONNEXIONS FTP
const ESPACE_LOCAL = 'ESPACE_LOCAL';

//CONSTANTES DES METADONNEES
const MTD_TITRE = 'Titre';
const MTD_FPS = 'FPS';
const MTD_DUREE = 'Durée';
const MTD_RESOLUTION = 'Resolution';
const MTD_FORMAT = 'Format';
const MTD_DUREE_REELLE = "Durée_réel";

const MTD_URI = 'URI';
const MTD_URI_NAS_PAD = 'URI NAS PAD';
const MTD_URI_NAS_ARCH = 'URI NAS ARCH';
const MTD_URI_STOCKAGE_LOCAL = 'URI STOCKAGE LOCAL';

//CONSTANTE POUR l'URI DE FFMPEG
    #PROD : À DECOMMENTER LORS DU PASSAGE EN PROD
//const URI_FFMPEG = '../ressources/lib/ffmepg/ffmpeg.exe';
const URI_FFMPEG = 'ffmpeg';

//CONSTANTES DE LA FONCTION DE RECONCILIATION
const EMPLACEMENT_MANQUANT = 'Emplacement manquant';

//CONSTANTES DES REPERTOIRES DES VIDEOS
const URI_VIDEOS_A_DIFFUSER = '../videos/videosADiffuser/';
const URI_VIDEOS_A_ANALYSER = '../videos/videosAAnalyser/';
const URI_VIDEOS_A_CONVERTIR_EN_ATTENTE_DE_CONVERSION = '../videos/videosAConvertir/attenteDeConversion/';
const URI_VIDEOS_A_CONVERTIR_EN_COURS_DE_CONVERSION = '../videos/videosAConvertir/coursDeConversion/';

const URI_VIDEOS_A_UPLOAD_EN_COURS_DE_CONVERSION = '../videos/videosAUpload/coursDeConversion/';
const URI_VIDEOS_A_UPLOAD_EN_ATTENTE_UPLOAD = '../videos/videosAUpload/attenteDUpload/';

//CONSTANTES DES CODES DES LOGS 
const LOG_SUCCESS = 'SUCCESS';
const LOG_WARN = 'WARNING';
const LOG_INFORM = 'INFO';
const LOG_FAIL = 'FAIL';
const LOG_CRITICAL = 'CRITICAL';

//NIVEAU D'AUTORISATION
//const AUTORISATION_PROF = ["Professeur", "Administrateur"];
//const AUTORISATION_ADMIN = ["Administrateur"];

//NIVEAU D'AUTORISATION
const ACCES_MODIFICATION = 'modifier';
const ACCES_SUPPRESSION = 'supprimer';
const ACCES_DIFFUSION = 'diffuser';
const ACCES_ADMINISTRATION = 'administrer';
const ROLE_ADMINISTRATEUR = 'Administrateur';

/*******************CONSTANTES PRODUCTION************************/
/*

//CONSTANTES DES CONNEXIONS FTP
const NAS_PAD = 'NAS_PAD';
const LOGIN_NAS_PAD = 'user1';
const PASSWORD_NAS_PAD = 'pass1';

const NAS_ARCH = 'NAS_ARCH';
const LOGIN_NAS_ARCH = 'user2';
const PASSWORD_NAS_ARCH = 'pass2';

const NAS_DIFF = 'NAS_DIFF';
const LOGIN_NAS_DIFF = 'user4';
const PASSWORD_NAS_DIFF = 'pass4';

//CONSTANTES DE LA BASE DE DONNEES
const BD_HOST = 'mysql_BTSPlay';
const BD_PORT = '3306:3306';
const BD_NAME = 'mydatabase';
const BD_USER = 'myuser';
const BD_PASSWORD = 'mypassword';

*/

/*******************CONSTANTES PARAMETRABLES************************/
//CONSTANTES DES URIS
    // #RISQUE : Changement des répertoires des NAS
const URI_RACINE_NAS_PAD = '2024-2025/';
const URI_RACINE_NAS_ARCH = '/';
const URI_RACINE_STOCKAGE_LOCAL = '/var/www/html/stockage/';
const URI_RACINE_NAS_DIFF = '/';

//CONNEXION FTP
    //PAD
const NAS_PAD = 'NAS_PAD';
const LOGIN_NAS_PAD = 'user1';
const PASSWORD_NAS_PAD = 'pass1';

const LOGIN_NAS_PAD_SUP = 'user1';
const PASSWORD_NAS_PAD_SUP = 'pass1';

    //ARCH
const LOGIN_NAS_ARCH_SUP = 'user2';
const PASSWORD_NAS_ARCH_SUP = 'pass2';

const NAS_ARCH = 'NAS_ARCH';
const LOGIN_NAS_ARCH = 'user2';
const PASSWORD_NAS_ARCH = 'pass2';
    //DIFF
const NAS_DIFF = 'NAS_DIFF';
const LOGIN_NAS_DIFF = 'user4';
const PASSWORD_NAS_DIFF = 'pass4';

//CONNEXION BASE DE DONNEES
    // #RISQUE : Changement des informations de la base de données
const BD_HOST = 'mysql_BTSPlay';
const BD_PORT = '3306:3306';
const BD_NAME = 'mydatabase';
const BD_USER = 'myuser';
const BD_PASSWORD = 'mypassword';

//URI DES FICHIERS GÉNÉRÉS
const URI_FICHIER_GENERES = '../ressources/datas/';
const URI_DUMP_SAUVEGARDE = '../ressources/datas/dumpBD/';
const URI_CONSTANTES_SAUVEGARDE = '../ressources/datas/sauvegardeConstantes/';

//NOMS DES FICHIERS GÉNÉRÉS
const NOM_FICHIER_LOG_GENERAL = 'historique.log';
const NOM_FICHIER_LOG_SAUVEGARDE = 'sauvegardes.log';
const SUFFIXE_FICHIER_DUMP_SAUVEGARDE = 'sauvegarde.sql';
const SUFFIXE_FICHIER_CONSTANTES_SAUVEGARDE = 'constantes.php';

//CONSTANTES DES PAGES
const NB_VIDEOS_PAR_SWIPER = '10';

//CONSTANTES DE L'HISTORIQUE DU TRANSFERT
const NB_VIDEOS_HISTORIQUE_TRANSFERT = '10';

//CONSTANTES DES LOGS
const NB_LIGNES_LOGS = '500';

//CONSTANTES POUR LE MULTITHREADING
const NB_MAX_PROCESSUS_TRANSFERT = '5';
const NB_MAX_SOUS_PROCESSUS_TRANSFERT = '5';

//CONSTANTES POUR L'AFFICHAGE DES LOGS
const AFFICHAGE_LOGS_PLUS_RECENTS_PREMIERS = 'on';

?>
