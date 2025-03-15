// #RISQUE : Dégager ce truc DOMContentLoaded
document.addEventListener("DOMContentLoaded", function(event) {
    
    

    if(document.querySelector('.transferts')){
        // Fonction pour déplacer une ligne vers le haut
        function moveUp(button) {
            const ligne = button.parentElement.parentElement; // Trouver la ligne actuelle
            const previousLigne = ligne.previousElementSibling; // Trouver la ligne précédente
    
            if (previousLigne.classList.contains('ligne')) {
                let infoLigne = ligne.innerHTML
                let infoPreviousLigne = previousLigne.innerHTML
    
                ligne.innerHTML = infoPreviousLigne;
                previousLigne.innerHTML = infoLigne;
            }
    
            document.querySelectorAll('.fleche-haut').forEach(button => {
                button.addEventListener('click', function () {
                    moveUp(this); // Passer le bouton cliqué à la fonction
                });
            });
    
            document.querySelectorAll('.fleche-bas').forEach(button => {
                button.addEventListener('click', function () {
                    moveDown(this); // Passer le bouton cliqué à la fonction
                });
            });
        }
    
        // Fonction pour déplacer une ligne vers le bas
        function moveDown(button) {
            const ligne = button.parentElement.parentElement; // Trouver la ligne actuelle
            const nextLigne = ligne.nextElementSibling; // Trouver la ligne suivante
    
            if (nextLigne.classList.contains('ligne')) {
                let infoLigne = ligne.innerHTML
                let infoNextLigne = nextLigne.innerHTML
    
                ligne.innerHTML = infoNextLigne;
                nextLigne.innerHTML = infoLigne;
            }
    
            document.querySelectorAll('.fleche-haut').forEach(button => {
                button.addEventListener('click', function () {
                    moveUp(this); // Passer le bouton cliqué à la fonction
                });
            });
    
            document.querySelectorAll('.fleche-bas').forEach(button => {
                button.addEventListener('click', function () {
                    moveDown(this); // Passer le bouton cliqué à la fonction
                });
            });
        }
    
        // Ajouter des gestionnaires d'événements à toutes les flèches
        document.querySelectorAll('.fleche-haut').forEach(button => {
            button.addEventListener('click', function () {
                moveUp(this); // Passer le bouton cliqué à la fonction
            });
        });
    
        document.querySelectorAll('.fleche-bas').forEach(button => {
            button.addEventListener('click', function () {
                moveDown(this); // Passer le bouton cliqué à la fonction
            });
        });
    }


});

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
        spaceBetween: 20,
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
          'airplay', // Airplay (currently Safari only)
          'download', // Custom download button
          'fullscreen' // Toggle fullscreen
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
        console.log(this.responseText);
    }
    xhttp.open("POST", "../fonctions/controleur.php");
    
    xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhttp.send("action=createDatabaseSave");
}

function scanDossierDecoupeVideo() {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        const videos = JSON.parse(this.responseText);
        const lignesContainer = document.querySelector('.transferts .lignes');
        lignesContainer.innerHTML = '';
        videos.forEach(video => {
            const ligne = document.createElement('div');
            ligne.classList.add('ligne');
            ligne.innerHTML = `
                <div class="fleches">
                    <a class="fleche-haut">
                        <img src="../ressources/Images/arrow.png" alt="flèche">
                    </a>
                    <a class="fleche-bas">
                        <img src="../ressources/Images/arrow.png" alt="flèche">
                    </a>
                </div>
                <div class="imgVideo">
                    <img src="../ressources/Images/imgVideo.png" alt="">
                </div>
                <div class="info">
                    <p class="nomVideo">${video.nomVideo}</p>
                    <p class="poidsVideo">${video.poidsVideo}</p>
                </div>
                <div class="progress">${video.status}</div>
                <div class="bouton">
                    <a class="pause">
                        <img src="../ressources/Images/pause.png" alt="pause">
                    </a>
                </div>
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
    setInterval( scanDossierDecoupeVideo , 5000);
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
/*
function afficherPopUp(titre, description, btn1, btn2){
    btn1PHP = JSON.stringify(btn1);
    btn2PHP = JSON.stringify(btn2);

    fetch('../../fonctions/controleur.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=popup&titre=${encodeURIComponent(titre)}&description=${description}&btn1=${btn1PHP}&btn2=${btn2PHP}`
    })
    .then(response => response.text())
    .then(data => {
        document.querySelector('body').innerHTML += data;
        boutonsPopUp(btn1, btn2);
    });

    
}

function retirerPopUp(){
    document.querySelector(".popup").remove()
    document.querySelector(".voile-popup").remove()
}

function boutonsPopUp(btn1, btn2){
    if(document.querySelector('#btn1')){
        document.querySelector('#btn1').addEventListener('click', function(){
            if (btn1) {
                if ("arguments" in btn1) {
                    let stringBody = btn1["arguments"].map((argument, index) => {
                        return argument.join('=');
                    }).join('&');

                    fetch('../../fonctions/controleur.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: stringBody
                    })
                    .then(response => response.text())
                    .then(data => {
                        document.querySelector('body').innerHTML += data;
                        boutonsPopUp(btn1, btn2);
                    });
                    retirerPopUp();
                    btn1 = null;
                    btn2 = null;
                } else {
                    retirerPopUp();
                }
            } else{
                retirerPopUp();
            }
        })
    }

    if (document.querySelector('#btn2')) {
        document.querySelector('#btn2').addEventListener('click', function(){
            if (btn2) {
                if ("arguments" in btn2) {
                    let stringBody = btn2["arguments"].map((argument, index) => {
                        return argument.join('=');
                    }).join('&');

                    fetch('../../fonctions/controleur.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: stringBody
                    })
                    .then(response => response.text())
                    .then(data => {
                        document.querySelector('body').innerHTML += data;
                        boutonsPopUp(btn1, btn2);
                    });
                    retirerPopUp();
                    btn1 = null;
                    btn2 = null;
                } else {
                    retirerPopUp();
                }
            } else{
                retirerPopUp();
            }
        })
    }
}
*/
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