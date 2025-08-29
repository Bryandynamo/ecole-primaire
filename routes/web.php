<?php

use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.attempt');
Route::post('logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Public user registration (create account from login page)
Route::get('users/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
Route::post('users', [App\Http\Controllers\RegistrationRequestController::class, 'store'])->name('users.store');

// Registration approval flow (signed URLs)
Route::get('registration-requests/{id}/approve', [App\Http\Controllers\RegistrationRequestController::class, 'approve'])->name('registration.approve');
Route::get('registration-requests/{id}/reject', [App\Http\Controllers\RegistrationRequestController::class, 'reject'])->name('registration.reject');

// Protected application routes
Route::middleware(['auth'])->group(function () {
    Route::resource('sessions', App\Http\Controllers\SessionController::class);
    Route::resource('classes', App\Http\Controllers\ClasseController::class);
    Route::resource('enseignants', App\Http\Controllers\EnseignantController::class);
    Route::resource('eleves', App\Http\Controllers\EleveController::class);
    Route::resource('bulletins', App\Http\Controllers\BulletinController::class);
    // Route dédiée pour le bulletin annuel
    Route::get('registre/{classe}/bulletins-annuels', [App\Http\Controllers\AnnualReportController::class, 'generateAnnualReports'])->name('bulletins.annuels');
    // Routes génération automatique bulletins
    Route::get('registre/{session}/{classe}/bulletins', [App\Http\Controllers\BulletinController::class,'trimestre'])->name('bulletins.trimestre');
    Route::get('registre/{session}/{classe}/bulletins/evaluation/{evaluation}', [App\Http\Controllers\BulletinController::class,'evaluation'])->name('bulletins.evaluation');
    Route::get('bulletins/{id}/export-pdf', [App\Http\Controllers\BulletinController::class, 'exportPdf'])->name('bulletins.exportPdf');

    // Registre (workflow enseignant)
    Route::middleware(['enseignant.scope'])->group(function () {
        Route::get('registre-repertoire', [App\Http\Controllers\RegistreController::class, 'repertoire'])->name('registre.repertoire');
        Route::get('registre', [App\Http\Controllers\RegistreController::class, 'index'])->name('registre.index');
        Route::post('/registre/saisie', [App\Http\Controllers\RegistreController::class, 'saisie'])->name('registre.saisie'); // Affiche le registre complet pour la classe/session/trimestre
        Route::get('/registre/saisie', function() {
            return redirect()->route('registre.index');
        });
        Route::post('registre/generer', [App\Http\Controllers\RegistreController::class, 'generer'])->name('registre.generer');
        Route::post('/registre/updateNotes', [App\Http\Controllers\RegistreController::class, 'updateNotes'])->name('registre.updateNotes');
        Route::post('/registre/ajaxNote', [App\Http\Controllers\RegistreController::class, 'ajaxNote'])->name('registre.ajaxNote');
        Route::get('registre/export-excel/{session_id}/{classe_id}/{evaluation}', [App\Http\Controllers\RegistreController::class, 'exportExcel'])->name('registre.exportExcel');

        // Registre - Routes principales
        Route::get('registre/{session_id}/{classe_id}/{evaluation?}', [App\Http\Controllers\RegistreController::class, 'show'])->name('registre.show');
        Route::get('registre/{session_id}/{classe_id}/statistiques/{periode?}/{type?}', [App\Http\Controllers\RegistreController::class, 'statistiques'])->name('registre.statistiques');
        Route::get('registre/{session_id}/{classe_id}/{evaluation?}/export-pdf', [App\Http\Controllers\RegistreController::class, 'exportPdf'])->name('registre.exportPdf');

        // Synthèse des compétences par sous-compétence (AJAX)
        Route::get('registre/{session_id}/{classe_id}/{evaluation_id}/synthese-competences', [App\Http\Controllers\RegistreController::class, 'syntheseCompetences'])->name('registre.syntheseCompetences');

        // Page dédiée à la synthèse des compétences
        Route::get('registre/{session_id}/{classe_id}/{evaluation_id}/synthese-competences-page', [App\Http\Controllers\RegistreController::class, 'syntheseCompetencesPage'])->name('registre.syntheseCompetencesPage');
        // Export PDF de la synthèse des compétences
        Route::get('registre/{session_id}/{classe_id}/{evaluation_id}/synthese-competences-pdf', [App\Http\Controllers\RegistreController::class, 'syntheseCompetencesPdf'])->name('registre.syntheseCompetencesPdf');
        // Export Excel de la synthèse des compétences
        Route::get('registre/{session_id}/{classe_id}/{evaluation_id}/synthese-competences-excel', [App\Http\Controllers\RegistreController::class, 'syntheseCompetencesExcel'])->name('registre.syntheseCompetencesExcel');

        // Alternative statistiques (protégée)
        Route::get('statistiques/{session_id}/{classe_id}/{periode?}', [App\Http\Controllers\RegistreController::class, 'statistiques'])->name('registre.statistiques.alt');

        // Export statistiques (protégés)
        Route::post('/registre/statistiques/pdf', [App\Http\Controllers\RegistreController::class, 'exportStatistiquesPdf'])->name('registre.statistiques.pdf');
        Route::post('/registre/statistiques/excel', [App\Http\Controllers\RegistreController::class, 'exportStatistiquesExcel'])->name('registre.statistiques.excel');
    });

    Route::resource('competences', App\Http\Controllers\CompetenceController::class);
    Route::resource('sous-competences', App\Http\Controllers\SousCompetenceController::class);

    // Routes couverture de leçons
    Route::get('couverture/{classe}/recapitulatif', [App\Http\Controllers\CouvertureController::class, 'showRecapitulatif'])->name('couverture.recapitulatif');
    Route::get('couverture/{classe}/recapitulatif/pdf', [App\Http\Controllers\CouvertureController::class, 'exportRecapPdf'])->name('couverture.recap.pdf');
    Route::get('couverture/{classe}/{evaluation}', [App\Http\Controllers\CouvertureController::class, 'show'])->name('couverture.show');
    Route::post('couverture/save', [App\Http\Controllers\CouvertureController::class, 'updateCouverture'])->name('couverture.save');
    Route::post('couverture/update-couverts', [App\Http\Controllers\CouvertureController::class, 'updateCouverts'])->name('couverture.updateCouverts');
    Route::get('couverture/{classe}/{evaluation}/export-pdf', [App\Http\Controllers\CouvertureController::class, 'exportPdf'])->name('couverture.exportPdf');
    // Export Excel fiche de couverture (UA)
    Route::get('couverture/{classe}/{evaluation}/export-excel', [App\Http\Controllers\CouvertureController::class, 'exportExcel'])->name('couverture.exportExcel');
    // Export Excel récapitulatif de couverture
    Route::get('couverture/{classe}/recapitulatif/excel', [App\Http\Controllers\CouvertureController::class, 'exportRecapExcel'])->name('couverture.recap.excel');
    Route::resource('modalites', App\Http\Controllers\ModaliteController::class);
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return auth()->check() ? redirect()->route('registre.index') : redirect()->route('login');
});

// (routes statistiques déplacées sous auth + enseignant.scope)
