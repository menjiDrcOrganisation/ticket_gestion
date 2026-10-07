<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use App\Support\TypeFichier;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Requête plus lourde que post_max_size : PHP vide le formulaire avant toute
        // validation, et la session n'est pas encore démarrée (pas de back()->withErrors()).
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $message = 'Le fichier envoyé est trop volumineux. Taille maximale : '
                .TypeFichier::get('affiche')->tailleLisible().'.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 413);
            }

            return response()->view('errors.413', ['message' => $message], 413);
        });
    })->create();
