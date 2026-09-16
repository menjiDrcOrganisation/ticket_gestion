 
 <?php
use App\Http\Controllers\Web\Admin\DemandeEvenementController;
use App\Http\Controllers\Web\Admin\TypeEvenementController;

use App\Http\Controllers\Web\Organisateur\BilletController;
use App\Http\Controllers\Web\Organisateur\EvenementBilletTypeBilletController;
use App\Http\Controllers\Web\Organisateur\DashboardController as OrganisateurDashboardController;
use Illuminate\Support\Facades\Route;



Route::get('billet', [BilletController::class,  'index'])->name('billet.all')->middleware(['auth']);
Route::delete('billet/{id}', [BilletController::class, 'destroy'])
    ->name('billet.destroy')
    ->middleware(['auth']);

Route::get('dashboard/organisateur', [OrganisateurDashboardController::class, 'index'])
    ->name('dashboard_orginasateur.show')->middleware(['auth']);





