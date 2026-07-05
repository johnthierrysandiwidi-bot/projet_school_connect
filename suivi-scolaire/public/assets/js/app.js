document.addEventListener('DOMContentLoaded', function () {
  // Ouverture / fermeture du menu sur mobile
  var toggle = document.querySelector('[data-menu-toggle]');
  var sidebar = document.querySelector('.sidebar');
  if (toggle && sidebar) {
    toggle.addEventListener('click', function () {
      sidebar.classList.toggle('is-open');
    });
  }

  // Confirmation avant les actions de suppression
  document.querySelectorAll('[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var message = form.getAttribute('data-confirm') || 'Confirmer cette action ?';
      if (!window.confirm(message)) {
        e.preventDefault();
      }
    });
  });

  // Disparition automatique des messages flash après quelques secondes
  document.querySelectorAll('.alert[data-autohide]').forEach(function (alert) {
    setTimeout(function () {
      alert.style.transition = 'opacity .4s ease';
      alert.style.opacity = '0';
      setTimeout(function () { alert.remove(); }, 400);
    }, 4000);
  });
});
