<?php

    //CONSTANTES DES URIS
	// #RISQUE : Changement des répertoires des NAS
    const URI_RACINE_NAS_PAD = '/';
    const URI_RACINE_NAS_ARCH = '/';
    const URI_RACINE_NAS_MPEG = '/';

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

    const NAS_MPEG = 'NAS_MPEG';
    const LOGIN_NAS_MPEG = 'user3';
    const PASSWORD_NAS_MPEG = 'pass3';

    //CONSTANTES DES METADONNEES
    const MTD_TITRE = 'Titre';
    const MTD_FPS = 'FPS';
    const MTD_DUREE = 'Durée';
    const MTD_RESOLUTION = 'Resolution';
    const MTD_FORMAT = 'Format';

    const MTD_URI = 'URI';
    const MTD_URI_NAS_PAD = 'URI NAS PAD';
    const MTD_URI_NAS_ARCH = 'URI NAS ARCH';
    const MTD_URI_NAS_MPEG = 'URI NAS MPEG';

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
    
    //CONSTANTES DES NOM UTILISATEURS ET DES MOTS DE PASSE
    const ETUDIANT = "Etudiant";
    const ETUDIANT_MDP = "1234";

    const PROF = "Prof";
    const PROF_MDP = "1234";

    const ADMIN = "Admin";
    const ADMIN_MDP = "1234";
?>