<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-900 via-blue-800 to-orange-500 py-12 px-6">
        <div class="w-full max-w-md bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl p-8">
            
            <!-- Logo ou Titre principal -->
            <div class="flex flex-col items-center mb-6">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-16 h-16 mb-3">
                <h2 class="text-2xl font-bold text-gray-800">Réinitialiser le mot de passe</h2>
                <p class="text-sm text-gray-600 text-center mt-2">
                    Entrez votre adresse email et recevez un lien pour créer un nouveau mot de passe.
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Formulaire -->
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Adresse email')" />
                    <x-text-input id="email"
                        class="block mt-1 w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-center mt-6">
                    <x-primary-button class="w-full justify-center bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 rounded-lg shadow-md transition">
                        {{ __('Envoyer le lien de réinitialisation') }}
                    </x-primary-button>
                </div>
            </form>

            <!-- Retour connexion -->
            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-orange-600 font-medium transition">
                    ⬅️ Retour à la page de connexion
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
