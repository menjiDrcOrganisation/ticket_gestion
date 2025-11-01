<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-900 via-blue-800 to-orange-500 py-12 px-6">
        <div class="w-full max-w-md bg-white/95 backdrop-blur-lg rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">🔐 Réinitialiser votre mot de passe</h2>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Adresse email')" />
                    <x-text-input id="email"
                        class="block mt-1 w-full"
                        type="email"
                        name="email"
                        :value="old('email', $request->email)"
                        required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4 relative">
                    <x-input-label for="password" :value="__('Nouveau mot de passe')" />
                    <x-text-input id="password"
                        class="block mt-1 w-full pr-10"
                        type="password"
                        name="password"
                        required autocomplete="new-password" />

                    <!-- Toggle Visibility -->
                    <button type="button" onclick="togglePassword('password', this)" class="absolute right-3 top-9 text-gray-500 hover:text-gray-700">
                        👁️
                    </button>

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-4 relative">
                    <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
                    <x-text-input id="password_confirmation"
                        class="block mt-1 w-full pr-10"
                        type="password"
                        name="password_confirmation"
                        required autocomplete="new-password" />

                    <!-- Toggle Visibility -->
                    <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute right-3 top-9 text-gray-500 hover:text-gray-700">
                        👁️
                    </button>

                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-center mt-6">
                    <x-primary-button class="w-full justify-center bg-orange-600 hover:bg-orange-700 text-white py-2 rounded-lg shadow-md transition">
                        {{ __('Réinitialiser le mot de passe') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(id, button) {
            const input = document.getElementById(id);
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.textContent = isHidden ? '🙈' : '👁️';
        }
    </script>
</x-guest-layout>
