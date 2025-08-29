// JS pour mise à jour dynamique du tableau récapitulatif uniquement lors d'une modification dans ce tableau

document.addEventListener('DOMContentLoaded', function () {
    // Cible toutes les cellules éditables du tableau recapitulatif
    document.querySelectorAll('#recap-table input.recap-input').forEach(function(input) {
        input.addEventListener('change', function() {
            // Récupère la ligne et recalcule les totaux de la ligne
            const row = input.closest('tr');
            let totalAnnuel = 0;
            // Recalcule le total annuel pour la ligne
            row.querySelectorAll('input.recap-input[data-ua]').forEach(function(cellInput) {
                totalAnnuel += parseInt(cellInput.value) || 0;
            });
            row.querySelector('.recap-total-annuel').textContent = totalAnnuel;

            // Recalcule les totaux par trimestre
            const trimestres = JSON.parse(document.getElementById('recap-table').dataset.trimestres || '{}');
            Object.keys(trimestres).forEach(function(trim) {
                let sum = 0;
                trimestres[trim].forEach(function(uaNum) {
                    const uaInput = row.querySelector('input.recap-input[data-ua="'+uaNum+'"]');
                    if (uaInput) sum += parseInt(uaInput.value) || 0;
                });
                const trimCell = row.querySelector('.recap-trimestre-'+trim);
                if (trimCell) trimCell.textContent = sum;
            });
        });
    });
});
