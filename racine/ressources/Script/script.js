//Fonctions spécifiques à la page home.php
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
        spaceBetween: 100,
        slidesPerView: 3,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
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
        sousMenu.hidden = true;
        document.querySelector('.btnSousMenu').addEventListener('click', (e) => {
        if (sousMenu.hidden == true) {
            sousMenu.hidden = false;
        } else {
            sousMenu.hidden = true;
        }
        })
    }
}

function lancerConvertion() {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        console.log(this.responseText);
    }
    xhttp.open("POST", "../fonctions/fonctions.php");
    
    xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhttp.send("action=lancerConvertion");
}