<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer le mot de passe | Ticket Gestion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-white-300">

    <div class="bg-white/95 backdrop-blur-md shadow-2xl rounded-2xl p-8 w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-2">Changer votre mot de passe</h2>
        <p class="text-sm text-center text-gray-600 mb-6">
            Bonjour {{ auth()->user()->name }}, vous êtes connecté avec un mot de passe temporaire.
            Pour des raisons de sécurité, choisissez un nouveau mot de passe avant de continuer.
        </p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('put')

            <div class="mb-4">
                <label for="current_password" class="block text-sm font-medium text-gray-700">Mot de passe temporaire (reçu par e-mail)</label>
                <x-app-input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                    wrapperClass="mt-1" inputClass="px-4 rounded-lg shadow-sm" />
                @foreach ($errors->updatePassword->get('current_password') as $message)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @endforeach
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                <x-app-input id="password" name="password" type="password" required autocomplete="new-password"
                    wrapperClass="mt-1" inputClass="px-4 rounded-lg shadow-sm" />
                @foreach ($errors->updatePassword->get('password') as $message)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @endforeach
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer le nouveau mot de passe</label>
                <x-app-input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                    wrapperClass="mt-1" inputClass="px-4 rounded-lg shadow-sm" />
            </div>

            <button type="submit"
                class="w-full py-2 px-4 bg-red-500 text-white font-semibold rounded-lg shadow-md focus:ring-2 focus:ring-blue-400 transition">
                Enregistrer le nouveau mot de passe
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:underline">Se déconnecter</button>
        </form>
    </div>

</body>
</html>
