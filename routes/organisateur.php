 
 <?php
use App\Http\Controllers\DemandeEvenementController;
use App\Http\Controllers\TypeEvenementController;
use App\Http\Controllers\EvenementBilletTypeBilletController;
use Illuminate\Support\Facades\Route;

 Route::get('achatbillet/', [EvenementBilletTypeBilletController::class,  'achatbillet'])->name('achatbillet');



Route::get('dashboard_event/{id}', [EvenementBilletTypeBilletController::class, 'show'])
    ->name('achatbillet.show');

Route::get('billets_evenement', [EvenementBilletTypeBilletController::class, 'event_billets'])
    ->name('event_billets.org');
?>