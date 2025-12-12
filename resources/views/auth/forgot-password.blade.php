<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié | Ticket Gestion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-900 via-blue-700 to-orange-500">

    <div class="bg-white/95 backdrop-blur-md shadow-2xl rounded-2xl p-8 w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">
            Mot de passe oublié
        </h2>

        <p class="text-center text-gray-600 mb-4 text-sm">
            Entrez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.
        </p>

        <!-- Message de statut (si email envoyé) -->
        @if (session('status'))
            <div class="mb-4 p-3 text-green-700 bg-green-100 border border-green-300 rounded-lg text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Adresse e-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm
                              focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none" />

                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bouton envoyer -->
            <button type="submit"
                class="w-full py-2 px-4 bg-gradient-to-r from-blue-700 to-orange-500
                       hover:from-blue-800 hover:to-orange-600 text-white font-semibold rounded-lg
                       shadow-md focus:ring-2 focus:ring-blue-400 transition">
                Envoyer le lien de réinitialisation
            </button>

            <!-- Retour connexion -->
            <p class="mt-6 text-center text-sm text-gray-600">
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">
                    ← Retour à la connexion
                </a>
            </p>
        </form>
    </div>

</body>
</html>
