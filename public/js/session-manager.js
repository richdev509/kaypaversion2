// Gestionnaire d'expiration de session
(function() {
    // Durée de session en minutes (doit correspondre à SESSION_LIFETIME)
    const SESSION_LIFETIME = 480; // 8 heures en minutes
    const WARNING_TIME = 5; // Afficher l'alerte 5 minutes avant expiration

    let lastActivity = Date.now();
    let warningShown = false;

    // Mettre à jour le timestamp de dernière activité
    function updateActivity() {
        lastActivity = Date.now();
        warningShown = false;
    }

    // Vérifier l'expiration de la session
    function checkSession() {
        const elapsed = (Date.now() - lastActivity) / 1000 / 60; // en minutes
        const remaining = SESSION_LIFETIME - elapsed;

        // Si moins de WARNING_TIME minutes restantes, afficher alerte
        if (remaining <= WARNING_TIME && !warningShown) {
            warningShown = true;
            const minutes = Math.floor(remaining);

            if (confirm(`Votre session expirera dans ${minutes} minute(s). Voulez-vous rester connecté ?`)) {
                // Faire une requête pour garder la session active
                fetch('/dashboard', {
                    method: 'HEAD',
                    credentials: 'same-origin'
                }).then(() => {
                    updateActivity();
                    alert('Session prolongée avec succès.');
                }).catch(() => {
                    alert('Erreur lors de la prolongation. Veuillez recharger la page.');
                });
            }
        }

        // Si session expirée, rediriger
        if (remaining <= 0) {
            alert('Votre session a expiré. Vous allez être redirigé vers la page de connexion.');
            window.location.href = '/login';
        }
    }

    // Écouter les événements d'activité
    ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(event => {
        document.addEventListener(event, updateActivity, true);
    });

    // Vérifier toutes les minutes
    setInterval(checkSession, 60000);

    // Intercepter les erreurs 401 (non authentifié)
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        return originalFetch.apply(this, args).then(response => {
            if (response.status === 401) {
                alert('Votre session a expiré. Vous allez être redirigé vers la page de connexion.');
                window.location.href = '/login';
            }
            return response;
        });
    };
})();
