//Fonction qui affiche les logs en couleurs
function affichageLogsCouleurs() {
    document.querySelectorAll(".log-line").forEach(line => {
        let text = line.textContent.toUpperCase();

        if (text.includes("CRITICAL")) line.classList.add("critical");
        if (text.includes("FAIL")) line.classList.add("fail");
        if (text.includes("WARNING")) line.classList.add("warning");
        if (text.includes("SUCCESS")) line.classList.add("success");
        if (text.includes("INFO")) line.classList.add("info");
    });

    // Défilement automatique vers le haut (car les plus récentes sont en haut)
    let logContainer = document.querySelector(".log-container");
    logContainer.scrollTop = 0; 
}

//Fonctions spécifiques à la page home.php et recherche.php

function affichageFiltres(){
    document.querySelector('.afficherFiltres').addEventListener('click', (e) => {
        let filtres = document.querySelector('.filtres');
        let voile = document.querySelector('.voile');
        if(filtres.classList.contains('afficher')){
            filtres.classList.remove('afficher');
            voile.classList.remove('afficher');
        }
        else{
            filtres.classList.add('afficher');
            voile.classList.add('afficher');
        }
    });
}


function initCarrousel(){
    const swiperVideo = new Swiper('.swiperVideo', {
        speed: 400,
        spaceBetween: 0,
        slidesPerView: 4,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
    if (document.querySelector(".swiperVideoProjet")) {
        const swiperVideo = new Swiper('.swiperVideoProjet', {
            speed: 400,
            spaceBetween: 100,
            slidesPerView: 3,
            navigation: {
                nextEl: '.swiper-projet-button-next',
                prevEl: '.swiper-projet-button-prev',
            },
        });
    }
}

//Fonctions spécifiques à la page video.php
function initLectureVideo(){
    const player = new Plyr('#player', {
        controls: [
          'play-large', // The large play button in the center
          'restart', // Restart playback
          'rewind', // Rewind by the seek time (default 10 seconds)
          'play', // Play/pause playback
          'fast-forward', // Fast forward by the seek time (default 10 seconds)
          'progress', // The progress bar and scrubber for playback and buffering
          'current-time', // The current time of playback
          'duration', // The full duration of the media
          'mute', // Toggle mute
          'volume', // Volume control
          'captions', // Toggle captions
          'settings', // Settings menu
          'pip', // Picture-in-picture (currently Safari only)
          'fullscreen', // Toggle fullscreen
          'buffered' // Buffer
        ],
        settings: ['captions', 'quality', 'speed', 'loop'],
        captions: {
          active: true,
          language: 'fr',
          update: true,
        },
    });
}

//Fonctions spécifiques au header.php.
function affichageSousMenu(){
    let sousMenu = document.querySelector('.sousMenu');
    //Si le sous menu n'a pas été chargé car l'utilisateur est déconnecté, on ne fait rien
    if(!(sousMenu == null)){
        sousMenu.style.display = "none";
        document.querySelector('.btnSousMenu').addEventListener('click', (e) => {
            if (sousMenu.style.display == "none") {
                sousMenu.style.display = "block";
            } else {
                sousMenu.style.display = "none";
            }
        })
    }
}

function lancerConversion() {

    //On bloque le bouton pour ne pas spammer la fonction de transfert
    let bouton = document.getElementById("btnConversion");
    bouton.disabled = true;
    bouton.innerText = "Rafraîchir la page pour relancer...";
    bouton.classList.add("boutonGrise");

    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        console.log(this.responseText);
    }
    xhttp.open("POST", "../fonctions/controleur.php");
    
    xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhttp.send("action=lancerConversion");
}

function createDatabaseSave() {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        
        const reponse = xhttp.responseText.trim();
        changerTitrePopup("Sauvegarde");
        if(reponse==1){
            changerTextePopup("Erreur lors du lancement de la sauvegarde manuelle. <br> Contactez votre administrateur.");
        }
        else{
            changerTextePopup("Sauvegarde effectuée avec succès.");
        }
        afficherPopup();
    }
    xhttp.open("POST", "../fonctions/controleur.php");
    
    xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhttp.send("action=createDatabaseSave");
}

function scanDossierDecoupeVideo() {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        const videos = JSON.parse(this.responseText);
        const lignesContainer = document.querySelector('#transfert .lignes');
        lignesContainer.innerHTML = '';
        videos.forEach(video => {
            const ligne = document.createElement('div');
            ligne.classList.add('ligne');
            ligne.innerHTML = `
                <div class="info">
                    <div class="imgVideo">
                        <img src="../ressources/Images/imgVideo.png" alt="">
                    </div>
                    <div class="nom">
                        <p class="nomVideo">${video.nomVideo}</p>
                    </div>
                </div>
                <div class="progress">${video.status}</div>
            `;
            lignesContainer.appendChild(ligne);
        });
    };
    xhttp.open("POST", "../fonctions/controleur.php");
    xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhttp.send("action=scanDossierDecoupeVideo");
}

function detectionCheckboxes(){
    document.querySelectorAll('input[type=checkbox]').forEach(checkbox => {
        checkbox.addEventListener('change', function(e) {
            let prof = this.getAttribute("data-prof")
            let colonne = this.getAttribute("data-colonne")
            let etat = this.checked
            console.log(this.getAttribute("data-prof"), this.getAttribute("data-colonne"), this.checked);
            mettreAJourAutorisation(prof, colonne, etat);
        })
    });
}

function mettreAJourAutorisation(prof, colonne, etat){
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        console.log(this.responseText);
    }
    xhttp.open("POST", "../fonctions/controleur.php");
    xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhttp.send("action=mettreAJourAutorisation&prof=" + prof + "&colonne=" + colonne + "&etat=" + etat);
}

function changeDatabaseSaveTime() {
    let selectedMonth = document.getElementById("select_Month").value;
    let selectedDay = document.getElementById("select_Day").value;
    let tempsLancement = document.getElementById("tempsLancement").value;
    let heure = tempsLancement[0]+tempsLancement[1];
    let minute = tempsLancement[3]+tempsLancement[4];
    console.log(tempsLancement);
    fetch("../fonctions/controleur.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "action=changeWhenToSaveDB&month=" + selectedMonth + "&day=" + selectedDay + "&heure=" + heure + "&minute=" + minute
    })
    .then(response => response.text())
    .then(data => {
        console.log("Réponse du serveur:", data);
        alert("Paramètres enregistrés !");
    })
    .catch(error => console.error("Erreur:", error));
}


function gestionOngletsAdministration() {
    const tabs = document.querySelectorAll('.tab');
    const contents = document.querySelectorAll('.tab-content');
    
    function setActiveTab(tabId) {
        tabs.forEach(t => t.classList.remove('active'));
        contents.forEach(c => c.classList.remove('active'));

        const activeTab = document.querySelector(`.tab[data-tab="${tabId}"]`);
        const activeContent = document.getElementById(tabId);

        if (activeTab && activeContent) {
            activeTab.classList.add('active');
            activeContent.classList.add('active');
        }
    }

    // Vérifie s'il y a un paramètre "tab" dans l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || "database"; // "database" par défaut

    setActiveTab(activeTab);

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabId = tab.dataset.tab;
            setActiveTab(tabId);

            // Met à jour l'URL sans recharger la page
            const newUrl = `${window.location.pathname}?tab=${tabId}`;
            window.history.pushState({ path: newUrl }, '', newUrl);
        });
    });
}

function appelScanVideo () {
    scanDossierDecoupeVideo();
    setInterval( scanDossierDecoupeVideo , 1000);
}

function gestion_click_dossier() {
    const menus = document.querySelectorAll('.menuArbo.local, .menuArbo.PAD, .menuArbo.ARCH');

    menus.forEach(menu => {
        const dossiers = menu.querySelectorAll('.dossier');
        dossiers.forEach(dossier => {
            if (!dossier.hasListener) {
                dossier.addEventListener('click', function(event) {
                    if (event.target === dossier) {
                        const path = dossier.getAttribute('data-path');
                        const menuType = menu.classList.contains('local') ? 'ESPACE_LOCAL' : 
                                        menu.classList.contains('PAD') ? 'PAD' : 
                                        'ARCH'; // Détermine le type de menu

                        console.log(`Menu: ${menuType}, Path: ${path}`);

                        if (!dossier.classList.contains("ouvert")) {
                            dossier.classList.add("ouvert");
                            fetch('../../fonctions/controleur.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `action=fetchPath&path=${encodeURIComponent(path)}&menuType=${menuType}`
                            })
                            .then(response => response.text())
                            .then(data => {
                                dossier.innerHTML += data;
                                gestion_click_dossier(); // Réattacher les écouteurs aux nouveaux dossiers
                            });
                        } else {
                            dossier.classList.remove("ouvert");
                            while (dossier.childElementCount > 0) {
                                dossier.removeChild(dossier.lastChild);
                            }
                            gestion_click_dossier(); // Réattacher les écouteurs après la fermeture
                        }
                    }
                });
                dossier.hasListener = true; // Marquer le dossier comme ayant un écouteur
            }
        });
    });
}

// Permet d'ouvrir et fermer le menu latéral
function ouvrirMenuArbo(){
    let mainMenu = document.querySelector(".main-menuArbo");
    let voile = document.querySelector(".voile");
    if (mainMenu.classList.contains('ouvert')) {
        mainMenu.classList.remove('ouvert');
        voile.classList.remove('ouvert');
    } else {
        mainMenu.classList.add('ouvert');
        voile.classList.add('ouvert');
    }
}


// Gère l'appartition et la suppresion des fichiers dans menuArbo
function gestionOngletsArborescence() {
    const radios = document.querySelectorAll('.radio input[type="radio"]');
    const menus = document.querySelectorAll('.menuArbo');
    
    function setActiveTab(tabId) {
        // Désactiver tous les menus
        menus.forEach(menu => menu.style.display = 'none');
        
        // Activer le menu correspondant à l'onglet sélectionné
        const activeMenu = document.querySelector(`.menuArbo.${tabId}`);
        if (activeMenu) {
            activeMenu.style.display = 'block';
        }
    }

    // Vérifie s'il y a un paramètre "tab" dans l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || "local"; // "local" par défaut

    // Activer l'onglet correspondant au paramètre de l'URL
    setActiveTab(activeTab);

    // Cocher le bouton radio correspondant
    const activeRadio = document.getElementById(activeTab);
    if (activeRadio) {
        activeRadio.checked = true;
    }

    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            const tabId = radio.id;
            setActiveTab(tabId);

            // Met à jour l'URL sans recharger la page
            const newUrl = `${window.location.pathname}?tab=${tabId}`;
            window.history.pushState({ path: newUrl }, '', newUrl);
        });
    });
}


function pageLectureVideo(){
    const descriptionElement = document.querySelector(".description");
    if (descriptionElement) {
        const fullText = descriptionElement.textContent.trim(); // On récupère le texte initial sans balises HTML
        if (fullText.length > 100) {
            const truncatedText = fullText.substring(0, 100);
            const remainingText = fullText.substring(100);

            // On remplace le contenu de `.description` avec le texte tronqué et les éléments interactifs
            descriptionElement.innerHTML = `
                ${truncatedText}<span class="dots">...</span>
                <span class="more-text" style="display: none;">${remainingText}</span>
                <a href="#" class="toggleDescription"> voir plus</a>
            `;

            const toggleButton = descriptionElement.querySelector(".toggleDescription");
            const moreTextElement = descriptionElement.querySelector(".more-text");
            const dots = descriptionElement.querySelector(".dots");

            toggleButton.addEventListener("click", function (event) {
                event.preventDefault();
                const isHidden = moreTextElement.style.display === "none";

                // Basculer entre l'affichage du texte complet et tronqué
                moreTextElement.style.display = isHidden ? "inline" : "none";
                dots.style.display = isHidden ? "none" : "inline";
                toggleButton.textContent = isHidden ? " voir moins" : " voir plus";
            });
        }
    }
}

// Gère l'affichage des mots de passe de la page d'administration
function afficherMotDePasse(inputId, eyeId) {
    var input = document.getElementById(inputId);
    var eyeIcon = document.getElementById(eyeId);

    if (input.type === "password") {
        input.type = "text";
        eyeIcon.src = "../ressources/Images/eye-opened.png";
    } else {
        input.type = "password";
        eyeIcon.src = "../ressources/Images/eye-closed.png";
    }
}

// Vérifie le format de l'URI dans la page d'administration et alerte si champ incorrect
function validerURI(inputId) {
    var input = document.getElementById(inputId);
    var value = input.value.trim();

    if (value !== "/" && (value.startsWith("/") || !value.endsWith("/"))) {
        input.setCustomValidity("Si l'URI est différente de '/', elle doit commencer par un caractère autre que '/' et finir par '/' (par exemple: uri/).");
    } else {
        input.setCustomValidity("");
    }
}

// Met en première lettre capitale les chaines de caractères
function capitalizeWords(str) {
    return str.replace(/\b\w/g, char => char.toUpperCase());
}

// Gère les paramètres plugin de tags Tagify
function initTagify(selector) {
    let input = document.querySelector(selector);
    let tagify = new Tagify(input, {
        enforceWhitelist: false,
        delimiters: ",",
        maxTags: 10,
        trim: true
    });

    // Avant d'ajouter un tag, on corrige la capitalisation
    tagify.on('add', function(e) {
        let formattedValue = capitalizeWords(e.detail.data.value);
        e.detail.data.value = formattedValue;

        // Mise à jour manuelle du tag pour afficher la version corrigée
        tagify.replaceTag(e.detail.tag, { value: formattedValue });
    });

    // Avant l’envoi du formulaire, convertir les tags en une chaîne propre
    input.closest("form").addEventListener("submit", function () {
        let tags = tagify.value.map(tag => capitalizeWords(tag.value)).join(", ");
        input.value = tags;
    });
}

// Permet l'ajout et l'initialisation des rôles dynamiques avec Tagify 
function initFormMetadonnees(){
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".role-input").forEach(function (input) {
            initTagify("#" + input.id);
        });
    });

    document.getElementById("add-role").addEventListener("click", function() {
        let container = document.getElementById("roles-container");
        let newRoleDiv = document.createElement("div");
        newRoleDiv.classList.add("champ", "role");

        let roleLabel = prompt("Nom du rôle :");
        if (!roleLabel) return;

        let roleId = roleLabel.replace(/\s+/g, '').toLowerCase();
        newRoleDiv.innerHTML = `
            <label for="${roleId}">${roleLabel}</label>
            <input type="text" id="${roleId}" name="roles[${roleLabel}]">
        `;
        
        container.appendChild(newRoleDiv);
        initTagify(`#${roleId}`);
    });
}

// Permet l'envoi des données des rôles dynamiques
function envoiMetadonnes(){
    // Quand le document est prêt
    const form = document.getElementById("roleForm");
        
    // Écouter la soumission du formulaire
    form.addEventListener("submit", function(event) {
        event.preventDefault(); // Empêcher le rechargement de la page

        // Créer un objet FormData pour récupérer toutes les données du formulaire
        const formData = new FormData(form);
        
        // Envoyer les données en POST avec fetch()
        fetch("controleur.php", {  // Remplace par le script PHP qui recevra les données
            method: "POST",
            body: "action=ModifierMetadonnees&roles=" + formData
        })
        .then(response => response.json())  // On suppose que la réponse est en JSON
        .then(data => {
            console.log("Réponse du serveur : ", data);
            // Traitement de la réponse si nécessaire
        })
        .catch(error => {
            console.error("Erreur : ", error);
        });
    });
}

function afficherPopup(){
    document.querySelector('.popup').style.display = 'block';
    document.querySelector('.voile-popup').style.display = 'block';
}

function cacherPopup(){
    document.querySelector('.popup').style.display = 'none';
    document.querySelector('.voile-popup').style.display = 'none';
}

function changerTitrePopup(nouveauTitre){
    document.querySelector('.popup h1').innerHTML = nouveauTitre;
}

function changerTextePopup(nouveauTexte){
    document.querySelector('.popup p').innerHTML = nouveauTexte;
}

function changerTexteBtn(nouveauTexte, classe){
    document.querySelector('.popup .' + classe ).innerText = nouveauTexte;
}

function afficherBtn(classe){
    document.querySelector('.popup .' + classe ).style.display = "block";
}

function cacherBtn(classe){
    document.querySelector('.popup .' + classe ).style.display = "none";
}


function attribuerFonctionBtn(fonction, args="", classe){
    document.querySelector('.popup .' + classe ).setAttribute('data-fonctions', fonction);
    document.querySelector('.popup .' + classe ).setAttribute('data-args', args);
}

function btn(classe){
    let fonction = document.querySelector('.' + classe ).dataset["fonctions"];
    let args = document.querySelector('.' + classe ).dataset["args"];
    if (args.includes(', ')) {
        args = args.split(', ').map(String)
        if (fonction != "") {
            window[fonction](...args);
            attribuerFonctionBtn("", "",classe); //détache la fonction pour évité des boucles
        }
    } else{ // le else sert quand il n'y a qu'un argument
        if (fonction != "") {
            window[fonction](args);
            attribuerFonctionBtn("", "", classe); //détache la fonction pour évité des boucles
        }
    }
}

function supprimerVideo(id, NAS){
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        
        const reponse = xhttp.responseText.trim();
        if (reponse == "1") {
            changerTitrePopup("Suppression");
            changerTextePopup("Suppression de la vidéo effectuée.");
            changerTexteBtn("Confirmer", "btn1");
            if(NAS == 'local'){
                attribuerFonctionBtn("redirection","home.php", "btn1")
            }
            else{
                attribuerFonctionBtn("reloading","", "btn1")
            }
            cacherBtn("btn2");
            cacherBtn("btn3");
            afficherPopup();
        } else{
            changerTitrePopup("Suppression");
            changerTextePopup("Echec lors de la suppression de la vidéo. <br/> Erreur : " + reponse);
            changerTexteBtn("Confirmer", "btn1");
            attribuerFonctionBtn("","", "btn1")
            cacherBtn("btn2");
            cacherBtn("btn3");
            afficherPopup();
        }
        
        
    }
    xhttp.open("POST", "../fonctions/controleur.php");
    
    xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhttp.send("action=supprimerVideo&idVideo=" + id + "&NAS=" + NAS);
}

function redirection(page){
    window.location.href = "./" + page;
}

function sendForm(formulaire){
    document.querySelector(formulaire).submit();
}

function lancerDiffusion(uri_nas_pad){
    console.log(uri_nas_pad)
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        
        const reponse = xhttp.responseText.trim();
        if (reponse == "1") {
            changerTitrePopup("Diffusion");
            changerTextePopup("Diffusion effectuée avec succès.");
            changerTexteBtn("Confirmer", "btn1");
            attribuerFonctionBtn("","", "btn1")
            cacherBtn("btn2");
            cacherBtn("btn3");
            cacherBtn("btn4");
            afficherPopup();
        } else{
            changerTitrePopup("Diffusion");
            changerTextePopup("Echec lors de la diffusion. <br/>Erreur : " + reponse);
            changerTexteBtn("Confirmer", "btn1");
            attribuerFonctionBtn("","", "btn1")
            cacherBtn("btn2");
            cacherBtn("btn3");
            cacherBtn("btn4");
            afficherPopup();
        }
        
        
    }
    xhttp.open("POST", "../fonctions/controleur.php");
    
    xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhttp.send("action=diffuserVideo&URI_COMPLET_NAS_PAD=" + uri_nas_pad);
    
}

function reloading(){
    window.location.reload();
}