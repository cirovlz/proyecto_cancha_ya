// Menú desplegable en pantallas chicas
(function () {
    var boton = document.getElementById('menu-btn');
    var menu = document.getElementById('menu');
    if (!boton || !menu) { return; }
    boton.addEventListener('click', function () {
        var abierto = menu.classList.toggle('abierto');
        boton.setAttribute('aria-expanded', abierto ? 'true' : 'false');
    });
})();
