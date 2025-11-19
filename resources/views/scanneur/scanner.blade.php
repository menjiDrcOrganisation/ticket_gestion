<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>QR Code Scanner</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-start space-y-6">

  <!-- MODAL RÉSULTAT -->
  <div id="resultModal" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-60 z-50">
    <form id="scanForm">
      @csrf
      <div class="bg-white rounded-xl p-6 max-w-md mx-auto text-center">
        <h2 class="text-2xl font-bold mb-4">Résultat du scan</h2>
        <p id="resultMessage" class="text-lg mb-4 text-gray-700"></p>

        <!-- Détails du billet -->
        <div id="billetDetails" class="text-left text-gray-700"></div>

        <!-- Boutons du formulaire (cachés initialement après validation) -->
        <div id="formButtons" class="mt-6 flex justify-center gap-3">
          <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Valider
          </button>
          <button type="button" id="cancelBtn" class="px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            Annuler
          </button>
        </div>

        <!-- Bouton de fermeture après validation -->
        <div id="closeButton" class="mt-6 hidden justify-center">
          <button type="button" id="closeModalBtn" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Fermer
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- ENTÊTE -->
  <header class="bg-orange-600 text-white p-4 shadow flex justify-between items-center w-full">
    <a href="{{route('billet.all')}}" 
       class="bg-white text-orange-600 px-4 py-2 rounded shadow hover:bg-gray-100 transition text-sm md:text-base flex items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
           <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
      </svg>
      Retour
    </a>
    <h1 class="text-lg font-semibold">Scanner un QR Code</h1>
  </header>

  <!-- CONTENU PRINCIPAL -->
  <div class="p-4 space-y-6 max-w-4xl mx-auto border flex flex-col items-center bg-white rounded-lg shadow-lg mt-6">
    
    <img src="{{ asset('images/codeQr.jpg') }}" alt="Scanner" class="w-24 h-24 mx-auto">

    <input type="hidden" id="id_camera">

    <div class="flex justify-center">
      <div id="reader" class="w-64 h-64 sm:w-72 sm:h-72 bg-white rounded-lg shadow-md overflow-hidden"></div>
    </div>

    <!-- Boutons d'action -->
    <div class="flex flex-col sm:flex-row gap-3 mt-4">
      <button id="startBtn"
              class="bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg px-5 py-2 w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-blue-400">
        Démarrer le scan
      </button>

      <input type="file" id="fileInput" accept="image/*" class="hidden" />
      <button id="uploadBtn"
              class="bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg px-5 py-2 w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-orange-400">
        Scanner une image
      </button>
    </div>
  </div>

  <!-- SCRIPT PRINCIPAL -->
  <script>
    const verifyUrl = "{{ url('/scanneur/scanne-preview') }}";
    const storeUrl = "{{ route('scanneur.processScan') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

    let html5QrCode = null;
    let isShowingResult = false;
    let currentScanData = null;

    const resultModal = document.getElementById('resultModal');
    const resultMessage = document.getElementById('resultMessage');
    const billetDetails = document.getElementById('billetDetails');
    const scanForm = document.getElementById('scanForm');
    const cancelBtn = document.getElementById('cancelBtn');
    const startBtn = document.getElementById('startBtn');
    const uploadBtn = document.getElementById('uploadBtn');
    const fileInput = document.getElementById('fileInput');
    const id_camera = document.getElementById('id_camera');
    const formButtons = document.getElementById('formButtons');
    const closeButton = document.getElementById('closeButton');
    const closeModalBtn = document.getElementById('closeModalBtn');

    // Charger la caméra
    Html5Qrcode.getCameras().then(cameras => {
      if (!cameras || cameras.length === 0) {
        alert("Aucune caméra détectée !");
        return;
      }
      const backCamera = cameras.find(cam =>
        cam.label.toLowerCase().includes("back") || cam.label.toLowerCase().includes("environment")
      );
      id_camera.value = (backCamera ? backCamera.id : cameras[0].id);
    }).catch(err => alert("Erreur accès caméra : " + err));

    // Démarrer le scan
    startBtn.addEventListener('click', async () => {
      if (!id_camera.value) {
        alert("Aucune caméra disponible !");
        return;
      }

      if (html5QrCode) await html5QrCode.stop().catch(() => {});

      startBtn.disabled = true;
      html5QrCode = new Html5Qrcode("reader");

      html5QrCode.start(
        { deviceId: { exact: id_camera.value } },
        { fps: 10, qrbox: 250 },
        decodedText => handleScan(decodedText),
        error => {}
      ).catch(err => {
        alert("Impossible de démarrer le scanner : " + err);
        startBtn.disabled = false;
      });
    });

    // Scanner une image
    uploadBtn.addEventListener("click", () => fileInput.click());

    fileInput.addEventListener("change", async (event) => {
      const file = event.target.files[0];
      if (!file) return;
      const scanner = new Html5Qrcode("reader");
      try {
        const result = await scanner.scanFile(file, true);
        handleScan(result);
      } catch (err) {
        alert("Impossible de lire le QR Code dans l'image sélectionnée !");
        console.error(err);
      }
    });

    // Traitement du QR code
    function handleScan(decodedText) {
      if (isShowingResult) return;
      isShowingResult = true;

      // Arrêter le scanner
      if (html5QrCode) {
        html5QrCode.stop().catch(() => {});
        startBtn.disabled = false;
      }

      // Réinitialiser l'état du modal
      formButtons.classList.remove('hidden');
      closeButton.classList.add('hidden');
      billetDetails.innerHTML = '';

      fetch(verifyUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify({ code: decodedText })
      })
      .then(async res => {
        if (!res.ok) {
          const text = await res.text();
          throw new Error(text);
        }
        return res.json();
      })
      .then(data => {
        const nomAuteur = data.nom || "N/A";
        const quantiteRestante = data.quantite_fictif ?? 0;
        const code = data.code ?? 0;

        resultMessage.textContent = data.message || "✅ Code scanné avec succès !";
        resultMessage.className = "text-lg mb-4 text-gray-700";

        billetDetails.innerHTML = `
            <div class="mt-3 space-y-2">
                <p><strong>Auteur du billet :</strong> ${nomAuteur}</p>
                <p><strong>Quantité restante :</strong> ${quantiteRestante}</p>
                <label class="block mt-3">
                    <span class="text-gray-700">Quantité à utiliser :</span>
                    <input type="hidden" name="code" value="${code}">
                    <input type="number" name="quantite" value="1" id="quantiteInput" min="1" max="${quantiteRestante}" 
                           class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring focus:ring-blue-300">
                </label>
            </div>
        `;

        resultModal.classList.replace("hidden", "flex");
      })
      .catch(err => {
        console.error("Erreur serveur :", err);
        resultMessage.textContent = "❌ Erreur de communication avec le serveur.";
        resultMessage.className = "text-lg mb-4 text-red-600";
        billetDetails.innerHTML = "";
        resultModal.classList.replace("hidden", "flex");
        isShowingResult = false;
      });
    }

    // Soumission du formulaire via AJAX
    scanForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      
      // Désactiver le bouton pendant l'envoi
      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Traitement...';
      
      fetch(storeUrl, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": csrfToken,
          "Accept": "application/json"
        },
        body: formData
      })
      .then(async response => {
        const data = await response.json();
        
        if (response.ok) {
          // Succès
          resultMessage.textContent = data.message || "✅ Opération réussie !";
          resultMessage.className = "text-lg mb-4 text-green-600 font-semibold";
          
          // Masquer les détails du billet
          billetDetails.innerHTML = "";
          
          // Changer les boutons
          formButtons.classList.add('hidden');
          closeButton.classList.remove('hidden');
          
        } else {
          // Erreur
          resultMessage.textContent = data.message || "❌ Erreur lors du traitement";
          resultMessage.className = "text-lg mb-4 text-red-600";
          
          // Réactiver le bouton en cas d'erreur
          submitBtn.disabled = false;
          submitBtn.textContent = 'Valider';
        }
      })
      .catch(error => {
        console.error("Erreur:", error);
        resultMessage.textContent = "❌ Erreur de communication avec le serveur";
        resultMessage.className = "text-lg mb-4 text-red-600";
        
        // Réactiver le bouton en cas d'erreur
        submitBtn.disabled = false;
        submitBtn.textContent = 'Valider';
      });
    });

    // Fermer le modal avec le bouton de fermeture
    closeModalBtn.addEventListener('click', () => {
      closeModal();
    });

    // Annuler
    cancelBtn.addEventListener('click', () => {
      closeModal();
    });

    // Fermer modal en cliquant en dehors
    resultModal.addEventListener('click', (e) => {
      if (e.target === resultModal) {
        closeModal();
      }
    });

    // Fonction pour fermer le modal
    function closeModal() {
      resultModal.classList.add("hidden");
      resultModal.classList.remove("flex");
      billetDetails.innerHTML = "";
      isShowingResult = false;
      scanForm.reset();
      
      // Réinitialiser l'état des boutons
      formButtons.classList.remove('hidden');
      closeButton.classList.add('hidden');
      
      // Réactiver le bouton de soumission
      const submitBtn = scanForm.querySelector('button[type="submit"]');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Valider';
    }
  </script>

</body>
</html>