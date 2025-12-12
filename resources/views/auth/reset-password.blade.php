<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe | Ticket Gestion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-900 via-blue-700 to-orange-500">

    <div class="bg-white/95 backdrop-blur-md shadow-2xl rounded-2xl p-8 w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Réinitialiser le mot de passe</h2>

        @if(session('status'))
            <div class="mb-4 p-3 text-green-700 bg-green-100 border border-green-300 rounded-lg text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @method("Put")
            @csrf

           
            

            <!-- Nouveau mot de passe -->
            <div class="mb-4 relative">
                <label for="password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none pr-10" />
                <button type="button" id="togglePassword" class="absolute right-3 top-9 text-gray-500 hover:text-gray-700 focus:outline-none">
                    👁️
                </button>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirmation mot de passe -->
            <div class="mb-6 relative">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none pr-10" />
                <button type="button" id="togglePasswordConfirm" class="absolute right-3 top-9 text-gray-500 hover:text-gray-700 focus:outline-none">
                    👁️
                </button>
                @error('password_confirmation')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bouton -->
            <button type="submit"
                class="w-full py-2 px-4 bg-gradient-to-r from-blue-700 to-orange-500 hover:from-blue-800 hover:to-orange-600 text-white font-semibold rounded-lg shadow-md focus:ring-2 focus:ring-blue-400 transition">
                Réinitialiser
            </button>

            <!-- Lien de connexion -->
            <p class="mt-6 text-center text-sm text-gray-600">
                Retour à la connexion ?
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">Se connecter</a>
            </p>
        </form>
    </div>

    <!-- Script pour afficher/masquer les mots de passe -->
    <script>
        const togglePassword = document.querySelector("#togglePassword");
        const passwordInput = document.querySelector("#password");

        const togglePasswordConfirm = document.querySelector("#togglePasswordConfirm");
        const passwordConfirmInput = document.querySelector("#password_confirmation");

        togglePassword.addEventListener("click", () => {
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
            togglePassword.textContent = type === "password" ? "👁️" : "🙈";
        });

        togglePasswordConfirm.addEventListener("click", () => {
            const type = passwordConfirmInput.getAttribute("type") === "password" ? "text" : "password";
            passwordConfirmInput.setAttribute("type", type);
            togglePasswordConfirm.textContent = type === "password" ? "👁️" : "🙈";
        });
    </script>

</body>
</html>
