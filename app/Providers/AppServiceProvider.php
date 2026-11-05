<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Gate::define('view-audit-logs', function (User $user): bool {
            return $user->isSuperAdmin();
        });

        Gate::define('manage-notifications', fn (User $user): bool => $user->role === 'admin');

        $this->configureRateLimiting();
    }

    /**
     * Limiteurs nommés de l'API, comptés par adresse IP (plafonds : config/ratelimit.php).
     */
    private function configureRateLimiting(): void
    {
        $limiteurs = [
            'api' => 'api',
            'creation-evenement' => 'creation_evenement',
            'paiement' => 'paiement',
            'webhook-paiement' => 'webhook',
        ];

        foreach ($limiteurs as $nom => $cle) {
            RateLimiter::for($nom, fn (Request $request): Limit => $this->limitPerIp($cle, $request));
        }
    }

    private function limitPerIp(string $cle, Request $request): Limit
    {
        // Réponse 429 toujours en JSON (même sans en-tête Accept), avec les en-têtes Retry-After et X-RateLimit-*.
        return Limit::perMinute((int) config("ratelimit.{$cle}"))
            ->by((string) $request->ip())
            ->response(fn (Request $request, array $headers) => response()->json([
                'success' => false,
                'message' => 'Trop de requêtes, veuillez réessayer dans quelques instants.',
            ], 429, $headers));
    }
}
