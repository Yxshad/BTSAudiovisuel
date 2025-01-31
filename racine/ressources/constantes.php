<?php

    //CONSTANTES DES URIS
	// #RISQUE : Changement des répertoires des NAS
    const URI_RACINE_NAS_PAD = '/';
    const URI_RACINE_NAS_ARCH = '/';
    const URI_RACINE_STOCKAGE_LOCAL = '/var/www/html/stockage/';
    const URI_RACINE_NAS_DIFF = '/';

    const PREFIXE_DOSSIER_VIDEO = '_BTSPLAY_';
    const SUFFIXE_MINIATURE_VIDEO = '_miniature.png';
    const SUFFIXE_VIDEO = '.mp4';

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
    // #RISQUE : Changement des informations de la base de données

    const BD_HOST = 'mysql_BTSPlay';
    const BD_PORT = '3306:3306';
    const BD_NAME = 'mydatabase';
    const BD_USER = 'myuser';
    const BD_PASSWORD = 'mypassword';

    //CONSTANTE POUR l'URI DE FFMPEG
    const URI_FFMPEG = "ffmpeg";


    //CONSTANTES DES METADONNEES
    const MTD_TITRE = 'Titre';
    const MTD_FPS = 'FPS';
    const MTD_DUREE = 'Durée';
    const MTD_RESOLUTION = 'Resolution';
    const MTD_FORMAT = 'Format';

    const MTD_URI = 'URI';
    const MTD_URI_NAS_PAD = 'URI NAS PAD';
    const MTD_URI_NAS_ARCH = 'URI NAS ARCH';
    const MTD_URI_STOCKAGE_LOCAL = 'URI STOCKAGE LOCAL';

    //CONSTANTES DE LA FONCTION DE RECONCILIATION
    const EMPLACEMENT_MANQUANT = 'Emplacement manquant';

    //CONSTANTES DES REPERTOIRES DES VIDEOS
    const URI_VIDEOS_A_LIRE = '../videos/videosALire/';
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

    //URI DU FICHIER DE LOG
    const URI_FICHIER_LOG = '../ressources/';
    const NOM_FICHIER_LOG = 'historique.log';

    //NIVEAU D'AUTORISATION
    const AUTORISATION_PROF = ["Professeur", "Administrateur"];
    const AUTORISATION_ADMIN = ["Administrateur"];

    //CONSTANTES DES PAGES
    const NB_VIDEOS_PAR_SWIPER = 10
?>