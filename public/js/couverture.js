// Fonction pour mettre à jour les calculs d'une ligne de leçon
function updateLessonRow(row) {
    var totalTrimestre = parseInt(row.data('total-trimestre')) || 0;
    var nbCourant = parseInt(row.find('.nb-couverts-input').val()) || 0;
    var totalAnnee = parseInt(row.data('total-annee')) || 0;
    var nbDejaCouverts = parseInt(row.data('nb-deja-couverts')) || 0;
    
    // Calculer les non-couverts
    var nonCouvertsTrimestre = totalTrimestre - nbCourant;
    
    // Calculer les taux avec 2 décimales
    var tauxTrimestre = (totalTrimestre > 0) ? ((nbCourant / totalTrimestre) * 100).toFixed(2) + '%' : '0%';
    var tauxAnnuel = (totalAnnee > 0) ? ((nbCourant + nbDejaCouverts) / totalAnnee * 100).toFixed(2) + '%' : '0%';
    
    // Mettre à jour les cellules
    row.find('.taux-trimestriel').text(tauxTrimestre);
    row.find('.taux-annuel').text(tauxAnnuel);
    
    // Mettre à jour les données dans les attributs data
    row.find('.taux-trimestriel').attr('data-taux-trimestre', tauxTrimestre);
    row.find('.taux-annuel').attr('data-taux-annuel', tauxAnnuel);
}

// Fonction pour mettre à jour la ligne TOTAL d'une sous-compétence
function updateTotalRow(totalRow) {
    try {
        // Récupérer toutes les lignes de leçons pour cette discipline
        var prevRows = totalRow.prevUntil('tr.total-row');
        
        // Pour chaque ligne de leçon, additionner les valeurs par colonne
        var totalNonCouverts = 0;
        var totalTrimestre = 0;
        var totalAnnee = 0;
        var totalDejaCouverts = 0;
        var totalCourant = 0;
        var totalNonCouvertsPrecedent = 0;
        
        prevRows.each(function() {
            var leconRow = $(this);
            if (leconRow.data('lecon-id')) {
                // Additionner les valeurs par colonne
                totalNonCouvertsPrecedent += parseInt(leconRow.find('.text-center').eq(1).text()) || 0; // Colonne 2 (0-based index)
                totalTrimestre += parseInt(leconRow.find('.text-center').eq(2).text()) || 0; // Colonne 3
                totalCourant += parseInt(leconRow.find('.nb-couverts-input').val()) || 0; // Colonne 4
                totalAnnee += parseInt(leconRow.find('.text-center').eq(6).text()) || 0; // Colonne 7
                totalDejaCouverts += parseInt(leconRow.find('.text-center').eq(7).text()) || 0; // Colonne 8
            }
        });
        
        // Calculer les non-couverts actuels
        var totalNonCouverts = totalTrimestre - totalCourant;
        
        // Calculer les taux avec arrondi à 2 décimales
        var tauxTrimestriel = (totalTrimestre > 0) ? ((totalCourant / totalTrimestre) * 100).toFixed(2) + '%' : '0%';
        var totalCouvertsAnnee = totalDejaCouverts + totalCourant;
        var tauxAnnuel = (totalAnnee > 0) ? ((totalCouvertsAnnee / totalAnnee) * 100).toFixed(2) + '%' : '0%';
        
        // Mettre à jour les cellules dans l'ordre correct des colonnes
        totalRow.find('[data-nb-non-couverts-precedent]').text(totalNonCouvertsPrecedent).attr('data-nb-non-couverts-precedent', totalNonCouvertsPrecedent);
        totalRow.find('[data-total-trimestre]').text(totalTrimestre).attr('data-total-trimestre', totalTrimestre);
        totalRow.find('[data-nb-couverts]').text(totalCourant).attr('data-nb-couverts', totalCourant);
        totalRow.find('[data-taux-trimestre]').text(tauxTrimestriel).attr('data-taux-trimestre', tauxTrimestriel);
        totalRow.find('[data-total-annee]').text(totalAnnee).attr('data-total-annee', totalAnnee);
        totalRow.find('[data-total-deja-couverts]').text(totalDejaCouverts).attr('data-total-deja-couverts', totalDejaCouverts);
        totalRow.find('[data-taux-annuel]').text(tauxAnnuel).attr('data-taux-annuel', tauxAnnuel);
        
        // Mettre à jour la colonne non-couverts qui est calculée
        totalRow.find('[data-nb-non-couverts]').text(totalNonCouverts).attr('data-nb-non-couverts', totalNonCouverts);

        // Pour chaque ligne de leçon
        prevRows.each(function() {
            var leconRow = $(this);
            if (leconRow.data('lecon-id')) {
                // Récupérer les données de la ligne
                var totalTrimestre = parseInt(leconRow.data('total-trimestre')) || 0;
                var totalAnnee = parseInt(leconRow.data('total-annee')) || 0;
                var nbDejaCouverts = parseInt(leconRow.data('nb-deja-couverts')) || 0;
                var nbCourant = parseInt(leconRow.find('.nb-couverts-input').val()) || 0;

                // Accumuler les totaux
                totalTrimestreSC += totalTrimestre;
                totalAnneeSC += totalAnnee;
                totalDejaCouvertsSC += nbDejaCouverts;
                totalCourantSC += nbCourant;

                // Mettre à jour les calculs de la ligne de leçon
                updateLessonRow(leconRow);
            }
        });

        // Calculer les totaux avec arrondi à 2 décimales
        var tauxTrimestrielSC = (totalTrimestreSC > 0) ? ((totalCourantSC / totalTrimestreSC) * 100).toFixed(2) + '%' : '0%';
        var totalCouvertsAnneeSC = totalDejaCouvertsSC + totalCourantSC;
        var tauxAnnuelSC = (totalAnneeSC > 0) ? ((totalCouvertsAnneeSC / totalAnneeSC) * 100).toFixed(2) + '%' : '0%';
        
        // Mettre à jour les données dans les attributs data
        totalRow.find('[data-taux-trimestre]').attr('data-taux-trimestre', tauxTrimestrielSC);
        totalRow.find('[data-taux-annuel]').attr('data-taux-annuel', tauxAnnuelSC);

        // Mettre à jour les cellules dans l'ordre correct des colonnes
        totalRow.find('[data-nb-non-couverts]').text(totalTrimestreSC - totalDejaCouvertsSC).attr('data-nb-non-couverts', totalTrimestreSC - totalDejaCouvertsSC);
        totalRow.find('[data-total-trimestre]').text(totalTrimestreSC).attr('data-total-trimestre', totalTrimestreSC);
        totalRow.find('[data-nb-couverts]').text(totalCourantSC).attr('data-nb-couverts', totalCourantSC);
        totalRow.find('[data-taux-trimestre]').text(tauxTrimestrielSC).attr('data-taux-trimestre', tauxTrimestrielSC);
        totalRow.find('[data-total-annee]').text(totalAnneeSC).attr('data-total-annee', totalAnneeSC);
        totalRow.find('[data-total-deja-couverts]').text(totalDejaCouvertsSC).attr('data-total-deja-couverts', totalDejaCouvertsSC);
        totalRow.find('[data-taux-annuel]').text(tauxAnnuelSC).attr('data-taux-annuel', tauxAnnuelSC);
    } catch (error) {
        // Supprimer les logs de débogage
    }
}

// Fonction pour mettre à jour tous les calculs
function updateAll() {
    $('.total-row').each(function() {
        var totalRow = $(this);
        var firstLeconRow = totalRow.prevUntil('tr.total-row, tr:has(td[rowspan])').first();
        if (!firstLeconRow.length) {
            return;
        }
        updateTotalRow(totalRow);
    });
}

// Initialiser les données de configuration
var config = {
    classeId: null,
    evaluationId: null,
    csrfToken: null,
    saveUrl: null
};

// Gestionnaire d'événements principal
$(document).ready(function() {
    config.classeId = parseInt($('#classe-id').data('classe-id')) || null;
    config.evaluationId = parseInt($('#evaluation-id').data('evaluation-id')) || null;
    config.csrfToken = $('meta[name="csrf-token"]').attr('content');
    config.saveUrl = $('#save-url').data('url');

    // Sauvegarde AJAX et mise à jour des calculs
    $('.nb-couverts-input').on('change', function() {
        var input = $(this);
        var tr = input.closest('tr');
        
        if (!input.val() || input.val() == '') {
            input.val(0);
        }

        var data = {
            lecon_id: tr.data('lecon-id'),
            classe_id: config.classeId,
            evaluation_id: config.evaluationId,
            nb_couverts: parseInt(input.val()),
            _token: config.csrfToken
        };

        $.ajax({
            url: config.saveUrl,
            method: 'POST',
            data: data,
            success: function(response) {
                updateLessonRow(tr);
                tr.next('.total-row').each(function() {
                    updateTotalRow($(this));
                });
            },
            error: function(xhr, status, error) {
                alert('Une erreur est survenue lors de la sauvegarde');
            }
        });
    });

    updateAll();
});
