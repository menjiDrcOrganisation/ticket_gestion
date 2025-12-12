<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification email | Ticket Gestion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-900 via-blue-700 to-orange-500">

    <div class="bg-white/95 backdrop-blur-md shadow-2xl rounded-2xl p-8 w-full max-w-md">

        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">
            Vérification de votre adresse email
        </h2>

        <p class="mb-4 text-sm text-gray-600 leading-relaxed">
            Merci pour votre inscription !  
            Avant de commencer, veuillez vérifier votre adresse email en cliquant sur le lien que nous venons de vous envoyer.
            <br><br>
            Si vous n’avez pas reçu l’email, vous pouvez en demander un nouveau.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 p-3 text-green-700 bg-green-100 border border-green-300 rounded-lg text-sm">
                Un nouveau lien de vérification vient d’être envoyé à votre adresse email.
            </div>
        @endif

        <!-- Boutons -->
        <div class="mt-6 flex items-center justify-between">

            <!-- Bouton renvoyer email -->
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                    class="py-2 px-4 bg-gradient-to-r from-blue-700 to-orange-500 hover:from-blue-800 hover:to-orange-600 text-white font-semibold rounded-lg shadow-md focus:ring-2 focus:ring-blue-400 transition">
                    Renvoyer l’email
                </button>
            </form>

            <!-- Déconnexion -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="underline text-sm text-gray-600 hover:text-gray-900 focus:outline-none">
                    Se déconnecter
                </button>
            </form>

        </div>

    </div>

</body>
</html>
