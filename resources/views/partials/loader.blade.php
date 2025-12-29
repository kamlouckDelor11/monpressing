<div id="globalPageLoader">
    <div id="loaderContent">
        Chargement<span>.</span><span>.</span><span>.</span>
    </div>
</div>

<style>
    /* 1. Style de l'overlay plein écran */
    #globalPageLoader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.7); /* Fond sombre semi-transparent */
        backdrop-filter: blur(8px);    /* Effet de flou prononcé */
        display: flex;                 /* Centrage parfait */
        justify-content: center;
        align-items: center;
        z-index: 99999;                /* Au-dessus de tout le reste */
        transition: opacity 0.4s ease, visibility 0.4s;
    }

    /* 2. Style du texte */
    #loaderContent {
        color: #ffffff;
        font-weight: bold;
        font-size: 1.5rem;
        font-family: sans-serif;
        text-align: center;
        letter-spacing: 1px;
    }

    /* 3. Animation des points clignotants */
    #loaderContent span {
        display: inline-block;
        animation: loaderBlink 1.5s infinite;
    }
    #loaderContent span:nth-child(2) { animation-delay: 0.2s; }
    #loaderContent span:nth-child(3) { animation-delay: 0.4s; }

    @keyframes loaderBlink {
        0%, 100% { opacity: 0.1; }
        50% { opacity: 1; }
    }

    /* 4. Classe pour masquer le loader */
    .loader-fade-out {
        opacity: 0;
        visibility: hidden;
    }
</style>

<script>
    // 5. Script pour masquer le loader une fois la page chargée
    window.addEventListener('load', function() {
        const loader = document.getElementById('globalPageLoader');
        
        // Petit délai pour une transition fluide
        setTimeout(() => {
            loader.classList.add('loader-fade-out');
        }, 300);
    });
</script>