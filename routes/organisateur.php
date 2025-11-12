 
 <?php
use App\Http\Controllers\DemandeEvenementController;
use App\Http\Controllers\TypeEvenementController;

use App\Http\Controllers\organisateur\BilletController;
use App\Http\Controllers\EvenementBilletTypeBilletController;
use App\Http\Controllers\organisateur\DashboardController as OrganisateurDashboardController;
use Illuminate\Support\Facades\Route;


Route::get('billet/achat/{evenementId}', [EvenementBilletTypeBilletController::class,  'achatbillet'])->name('billets.index');

Route::get('billet/{evenementId}', [BilletController::class,  'index'])->name('billet.all');

Route::get('dashboard/organisateur', [OrganisateurDashboardController::class, 'index'])
    ->name('dashboard_orginasateur.show')->middleware(['auth']);