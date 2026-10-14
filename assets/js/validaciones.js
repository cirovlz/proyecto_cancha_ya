// Mostrar / ocultar contraseña
document.querySelectorAll('[data-toggle-clave]').forEach(function (boton) {
    boton.addEventListener('click', function () {
        var input = document.getElementById(boton.dataset.toggleClave);
        var visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        boton.textContent = visible ? 'Mostrar' : 'Ocultar';
    });
});

// Aviso en vivo si las contraseñas no coinciden (el servidor vuelve a validar)
var clave = document.getElementById('clave');
var clave2 = document.getElementById('clave2');
if (clave && clave2) {
    var comparar = function () {
        clave2.setCustomValidity(clave2.value && clave.value !== clave2.value ? 'Las contraseñas no coinciden' : '');
    };
    clave.addEventListener('input', comparar);
    clave2.addEventListener('input', comparar);
}