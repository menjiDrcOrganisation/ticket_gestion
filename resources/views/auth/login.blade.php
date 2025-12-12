<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Ticket Gestion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-white-300">

    <div class="bg-white/95 backdrop-blur-md shadow-2xl rounded-2xl p-8 w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Connexion</h2>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600 bg-green-100 border border-green-300 p-3 rounded-lg">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Adresse e-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none" />
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-4 relative">
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none pr-10" />
                <!-- Bouton œil -->
            <button type="button" id="togglePassword" 
                class="absolute right-3 top-9 text-gray-500 hover:text-gray-700 focus:outline-none cursor-pointer">
                
                <!-- Icône œil (visible par défaut) -->
                <svg id="iconShow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178a1.012 1.012 0 010 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>

                <!-- Icône œil barré (cachée) -->
                <svg id="iconHide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c1.79 0 3.487-.41 5.004-1.146M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.5a10.52 10.52 0 01-1.272 2.64M6.228 6.228L3 3m3.228 3.228l12.544 12.544M17.772 17.772L21 21M12 9a3 3 0 013 3" />
                </svg>
            </button>

                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me + Forgot -->
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-600">
                    <span class="ml-2 text-sm  text-gray-500">Se souvenir de moi</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-red-500 text-blue-600 hover:underline">Mot de passe oublié ?</a>
                @endif
            </div>

            <!-- Button -->
            <button type="submit"
                class="w-full py-2 px-4  bg-red-500  hover:from-blue-800 hover:to-orange-600 text-white font-semibold rounded-lg shadow-md focus:ring-2 focus:ring-blue-400 transition">
                Se connecter
            </button>

           
        </form>
    </div>

    <!-- Script pour voir le mot de passe -->
    <script>
        const togglePassword = document.querySelector("#togglePassword");
        const passwordInput = document.querySelector("#password");

       togglePassword.addEventListener("click", () => {
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);

            // Afficher / cacher les icônes
            if (type === "password") {
                iconShow.classList.remove("hidden");
                iconHide.classList.add("hidden");
            } else {
                iconShow.classList.add("hidden");
                iconHide.classList.remove("hidden");
            }
        });

    </script>

</body>
</html>
