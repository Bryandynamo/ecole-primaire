// Fonction pour mettre à jour les calculs d'une ligne de leçon
function updateLessonRow(row) {
    var totalTrimestre = parseInt(row.data('total-trimestre')) || 0;
    var totalAnnee = parseInt(row.data('total-annee')) || 0;
    var nbDejaCouverts = parseInt(row.data('nb-deja-couverts')) || 0;
    var nbCourant = parseInt(row.find('.nb-couverts-input').val()) || 0;

    // Calculs pour la ligne de leçon
    var nbCouvertsTrimestre = nbDejaCouverts + nbCourant;
    var tauxTrimestre = (totalTrimestre > 0) ? ((nbCouvertsTrimestre / totalTrimestre) * 100).toFixed(1) + '%' : '0%';
    row.find('td.taux-trimestriel').text(tauxTrimestre);

    var totalCouvertsAnnee = nbDejaCouverts + nbCourant;
    var tauxAnnuel = (totalAnnee > 0) ? ((totalCouvertsAnnee / totalAnnee) * 100).toFixed(1) + '%' : '0%';
    row.find('td.taux-annuel').text(tauxAnnuel);
}

// Fonction pour mettre à jour la ligne TOTAL d'une sous-compétence
function updateTotalRow(totalRow) {
    var prevRows = totalRow.prevUntil('tr.total-row, tr:has(td[rowspan])');
    var totalTrimestreSC = 0, totalAnneeSC = 0, totalDejaCouvertsSC = 0, totalCourantSC = 0;

    prevRows.each(function() {
        var leconRow = $(this);
        if (leconRow.data('lecon-id')) {
            var totalTrimestre = parseInt(leconRow.data('total-trimestre')) || 0;
            var totalAnnee = parseInt(leconRow.data('total-annee')) || 0;
            var nbDejaCouverts = parseInt(leconRow.data('nb-deja-couverts')) || 0;
            var nbCourant = parseInt(leconRow.find('.nb-couverts-input').val()) || 0;

            totalTrimestreSC += totalTrimestre;
            totalAnneeSC += totalAnnee;
            totalDejaCouvertsSC += nbDejaCouverts;
            totalCourantSC += nbCourant;

            // Mettre à jour les calculs de la ligne de leçon
            updateLessonRow(leconRow);
        }
    });

    var totalCouvertsTrimestreSC = totalDejaCouvertsSC + totalCourantSC;
    var tauxTrimestrielSC = (totalTrimestreSC > 0) ? ((totalCouvertsTrimestreSC / totalTrimestreSC) * 100).toFixed(1) + '%' : '0%';
    
    var totalCouvertsAnneeSC = totalDejaCouvertsSC + totalCourantSC;
    var tauxAnnuelSC = (totalAnneeSC > 0) ? ((totalCouvertsAnneeSC / totalAnneeSC) * 100).toFixed(1) + '%' : '0%';

    totalRow.find('.total-sc-trimestre').text(totalTrimestreSC);
    totalRow.find('.total-sc-couverts').text(totalCourantSC);
    totalRow.find('.total-sc-taux-trimestriel').text(tauxTrimestrielSC);
    totalRow.find('.total-sc-annee').text(totalAnneeSC);
    totalRow.find('.total-sc-deja-couverts').text(totalDejaCouvertsSC);
    totalRow.find('.total-sc-taux-annuel').text(tauxAnnuelSC);
}

// Fonction pour mettre à jour tous les calculs
function updateAll() {
    $('.total-row').each(function() {
        updateTotalRow($(this));
    });
}

$(document).ready(function() {
    // Sauvegarde AJAX et mise à jour des calculs sur changement de valeur
    $('.nb-couverts-input').on('input change', function() {
        var input = $(this);
        var tr = input.closest('tr');
        var data = {
            lecon_id: tr.data('lecon-id'),
            classe_id: {{ $classe->id }},
            evaluation_id: {{ $evaluation->id }},
            nb_couverts: input.val(),
            _token: '{{ csrf_token() }}'
        };

        // Sauvegarde AJAX
        $.post('{{ route('couverture.save') }}', data, function(resp) {
            if (resp.success) {
                // console.log('Saved');
            } else {
                console.error('Save failed:', resp.message);
            }
        }).fail(function() {
            console.error('AJAX error');
        });

        // Mettre à jour les calculs
        updateAll();
    });

    // Initialisation des calculs au chargement de la page
    updateAll();
});
