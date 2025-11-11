 
 <?php
use App\Http\Controllers\DemandeEvenementController;
use App\Http\Controllers\TypeEvenementController;
use App\Http\Controllers\EvenementBilletTypeBilletController;
use Illuminate\Support\Facades\Route;

 Route::get('achatbillet/{evenementId}', [EvenementBilletTypeBilletController::class,  'achatbillet'])->name('billets.index');



Route::get('dashboard_event/{id}', [EvenementBilletTypeBilletController::class, 'show'])
    ->name('achatbillet.show');
?>