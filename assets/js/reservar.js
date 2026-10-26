// Calcula total, seña y mitad en vivo (el servidor vuelve a calcular todo al guardar)
(function () {
    var form = document.getElementById('form-reserva');
    if (!form) { return; }
    var sena = parseFloat(form.dataset.sena) || 0;

    function dinero(n) { return '$' + Math.round(n).toLocaleString('es-AR'); }

    function calcular() {
        var total = 0;
        var slot = form.querySelector('input[name="slot"]:checked');
        if (slot) { total += parseFloat(slot.dataset.precio) || 0; }
        form.querySelectorAll('input[data-extra]').forEach(function (i) {
            total += (parseInt(i.value, 10) || 0) * (parseFloat(i.dataset.precio) || 0);
        });
        document.getElementById('r-total').textContent = dinero(total);
        document.getElementById('r-sena').textContent = dinero(total * sena / 100);
        var modalidad = form.querySelector('input[name="modalidad"]:checked');
        var filaMitad = document.getElementById('r-mitad');
        filaMitad.hidden = !(modalidad && modalidad.value === 'mitad');
        document.getElementById('r-mitad-valor').textContent = dinero(total / 2);
    }

    form.addEventListener('input', calcular);
    form.addEventListener('change', calcular);
    calcular();
})();
