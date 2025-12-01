<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>QR Code Scanner</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
  
  <style>
    /* Styles personnalisés pour améliorer l'interface */
    .glass-effect {
      background: rgba(255, 255, 255, 0.25);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.18);
    }
    
    .scanner-frame {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .scanner-frame::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      border: 2px solid rgba(255, 255, 255, 0.8);
      border-radius: 16px;
      pointer-events: none;
      z-index: 10;
    }
    
    .scanner-corner {
      position: absolute;
      width: 30px;
      height: 30px;
      border-color: #3b82f6;
      z-index: 11;
    }
    
    .scanner-corner-tl {
      top: 0;
      left: 0;
      border-top: 4px solid;
      border-left: 4px solid;
      border-top-left-radius: 12px;
    }
    
    .scanner-corner-tr {
      top: 0;
      right: 0;
      border-top: 4px solid;
      border-right: 4px solid;
      border-top-right-radius: 12px;
    }
    
    .scanner-corner-bl {
      bottom: 0;
      left: 0;
      border-bottom: 4px solid;
      border-left: 4px solid;
      border-bottom-left-radius: 12px;
    }
    
    .scanner-corner-br {
      bottom: 0;
      right: 0;
      border-bottom: 4px solid;
      border-right: 4px solid;
      border-bottom-right-radius: 12px;
    }
    
    .pulse-animation {
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0% { opacity: 0.7; }
      50% { opacity: 1; }
      100% { opacity: 0.7; }
    }
    
    .btn-primary {
      @apply bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg px-5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50 transition-all duration-200 shadow-md;
    }
    
    .btn-secondary {
      @apply bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-medium rounded-lg px-5 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-opacity-50 transition-all duration-200 shadow-md;
    }
    
    .btn-outline {
      @apply bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 font-medium rounded-lg px-5 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50 transition-all duration-200 shadow-sm;
    }
    
    .modal-content {
      background: white;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
      animation: modal-appear 0.3s ease-out;
    }
    
    @keyframes modal-appear {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }
    
    .status-success {
      @apply text-green-600 bg-green-50 border border-green-200 rounded-lg p-3;
    }
    
    .status-error {
      @apply text-red-600 bg-red-50 border border-red-200 rounded-lg p-3;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex flex-col items-center justify-start">

  <!-- MODAL RÉSULTAT -->
  <div id="resultModal" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-60 z-50 p-4">
    <div class="modal-content w-full max-w-md">
      <form id="scanForm">
        @csrf
        <div class="p-6 text-center">
          <div class="flex justify-center mb-4">
            <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
          </div>
          
          <h2 class="text-2xl font-bold text-gray-800 mb-2">Résultat du scan</h2>
          <p id="resultMessage" class="text-lg mb-4"></p>

          <!-- Détails du billet -->
          <div id="billetDetails" class="text-left text-gray-700 bg-gray-50 rounded-xl p-4 mt-4"></div>

          <!-- Boutons du formulaire (cachés initialement après validation) -->
          <div id="formButtons" class="mt-6 flex flex-col sm:flex-row justify-center gap-3">
            <button type="submit" class="btn-primary">
              Valider
            </button>
            <button type="button" id="cancelBtn" class="btn-outline">
              Annuler
            </button>
          </div>

          <!-- Bouton de fermeture après validation -->
          <div id="closeButton" class="mt-6 hidden justify-center">
            <button type="button" id="closeModalBtn" class="btn-primary">
              Fermer
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- ENTÊTE -->
  <header class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-4 shadow-md w-full">
    <div class="max-w-4xl mx-auto flex justify-between items-center">
      <a href="{{route('dashboard_orginasateur.show')}}" 
         class="bg-white text-blue-600 px-4 py-2 rounded-lg shadow hover:bg-gray-50 transition-all duration-200 text-sm md:text-base flex items-center gap-2 font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
             <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
        </svg>
        Retour
      </a>
      <h1 class="text-xl font-bold">Scanner un QR Code</h1>
      <div class="w-10"></div> <!-- Élément vide pour équilibrer l'espace -->
    </div>
  </header>

  <!-- CONTENU PRINCIPAL -->
  <div class="p-6 w-full max-w-2xl mx-auto mt-8">
    <div class="glass-effect rounded-2xl p-6 shadow-lg">
      <div class="text-center mb-8">
        <div class="flex justify-center mb-4">
          <div class="w-20 h-20 rounded-full bg-white shadow-md flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
          </div>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Scanner un QR Code</h2>
        <p class="text-gray-600">Scannez un code QR pour valider un billet</p>
      </div>

      <input type="hidden" id="id_camera">

      <div class="flex justify-center mb-6">
        <div class="scanner-frame">
          <div id="reader" class="w-64 h-64 sm:w-80 sm:h-80 bg-white"></div>
          <div class="scanner-corner scanner-corner-tl"></div>
          <div class="scanner-corner scanner-corner-tr"></div>
          <div class="scanner-corner scanner-corner-bl"></div>
          <div class="scanner-corner scanner-corner-br"></div>
        </div>
      </div>

      <!-- Indicateur de statut -->
      <div id="scannerStatus" class="text-center mb-6">
        <div class="inline-flex items-center bg-blue-50 text-blue-700 px-4 py-2 rounded-full text-sm">
          <div class="w-2 h-2 bg-blue-500 rounded-full mr-2 pulse-animation"></div>
          <span id="statusText">Prêt à scanner</span>
        </div>
      </div>

      <!-- Boutons d'action -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button id="startBtn"
                class="btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          Démarrer le scan
        </button>

        <input type="file" id="fileInput" accept="image/*" class="hidden" />
        <button id="uploadBtn"
                class="btn-secondary">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Scanner une image
        </button>
      </div>
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
    const scannerStatus = document.getElementById('scannerStatus');
    const statusText = document.getElementById('statusText');

    // Mettre à jour le statut du scanner
    function updateStatus(message, type = 'info') {
      statusText.textContent = message;
      
      // Mettre à jour les classes en fonction du type
      scannerStatus.className = 'text-center mb-6';
      let statusClasses = 'inline-flex items-center px-4 py-2 rounded-full text-sm';
      
      switch(type) {
        case 'success':
          statusClasses += ' bg-green-50 text-green-700';
          break;
        case 'error':
          statusClasses += ' bg-red-50 text-red-700';
          break;
        case 'warning':
          statusClasses += ' bg-yellow-50 text-yellow-700';
          break;
        default:
          statusClasses += ' bg-blue-50 text-blue-700';
      }
      
      scannerStatus.innerHTML = `
        <div class="${statusClasses}">
          <div class="w-2 h-2 rounded-full mr-2 ${type === 'info' ? 'bg-blue-500 pulse-animation' : type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-yellow-500'}"></div>
          <span>${message}</span>
        </div>
      `;
    }

    // Charger la caméra
    Html5Qrcode.getCameras().then(cameras => {
      if (!cameras || cameras.length === 0) {
        updateStatus("Aucune caméra détectée !", "error");
        return;
      }
      const backCamera = cameras.find(cam =>
        cam.label.toLowerCase().includes("back") || cam.label.toLowerCase().includes("environment")
      );
      id_camera.value = (backCamera ? backCamera.id : cameras[0].id);
      updateStatus("Caméra prête - Cliquez sur 'Démarrer le scan'");
    }).catch(err => {
      updateStatus("Erreur d'accès à la caméra", "error");
      console.error(err);
    });

    // Démarrer le scan
    startBtn.addEventListener('click', async () => {
      if (!id_camera.value) {
        updateStatus("Aucune caméra disponible !", "error");
        return;
      }

      if (html5QrCode) await html5QrCode.stop().catch(() => {});

      startBtn.disabled = true;
      updateStatus("Scanner en cours d'initialisation...", "info");
      
      html5QrCode = new Html5Qrcode("reader");

      html5QrCode.start(
        { deviceId: { exact: id_camera.value } },
        { fps: 10, qrbox: 250 },
        decodedText => handleScan(decodedText),
        error => {}
      ).then(() => {
        updateStatus("Recherche de QR Code...", "info");
      }).catch(err => {
        updateStatus("Impossible de démarrer le scanner", "error");
        console.error(err);
        startBtn.disabled = false;
      });
    });

    // Scanner une image
    uploadBtn.addEventListener("click", () => fileInput.click());

    fileInput.addEventListener("change", async (event) => {
      const file = event.target.files[0];
      if (!file) return;
      
      updateStatus("Analyse de l'image...", "info");
      
      const scanner = new Html5Qrcode("reader");
      try {
        const result = await scanner.scanFile(file, true);
        handleScan(result);
      } catch (err) {
        updateStatus("Impossible de lire le QR Code", "error");
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

      updateStatus("QR Code détecté - Traitement en cours...", "info");

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
        resultMessage.className = "status-success";

        billetDetails.innerHTML = `
            <div class="space-y-3">
                <p class="flex justify-between"><span class="font-medium">Auteur du billet :</span> <span class="text-gray-800">${nomAuteur}</span></p>
                <p class="flex justify-between"><span class="font-medium">Quantité restante :</span> <span class="text-gray-800">${quantiteRestante}</span></p>
                <div class="mt-4">
                    <label class="block text-gray-700 font-medium mb-2">Quantité à utiliser :</label>
                    <input type="hidden" name="code" value="${code}">
                    <input type="number" name="quantite" value="1" id="quantiteInput" min="1" max="${quantiteRestante}" 
                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>
            </div>
        `;

        resultModal.classList.replace("hidden", "flex");
        updateStatus("QR Code validé - En attente de confirmation", "success");
      })
      .catch(err => {
        console.error("Erreur serveur :", err);
        resultMessage.textContent = "❌ Erreur de communication avec le serveur.";
        resultMessage.className = "status-error";
        billetDetails.innerHTML = "";
        resultModal.classList.replace("hidden", "flex");
        isShowingResult = false;
        updateStatus("Erreur lors du traitement", "error");
      });
    }

    // Soumission du formulaire via AJAX
    scanForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      
      // Désactiver le bouton pendant l'envoi
      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Traitement...
      `;
      
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
          resultMessage.className = "status-success";
          
          // Masquer les détails du billet
          billetDetails.innerHTML = "";
          
          // Changer les boutons
          formButtons.classList.add('hidden');
          closeButton.classList.remove('hidden');
          
          updateStatus("Billet validé avec succès", "success");
          
        } else {
          // Erreur
          resultMessage.textContent = data.message || "❌ Erreur lors du traitement";
          resultMessage.className = "status-error";
          
          // Réactiver le bouton en cas d'erreur
          submitBtn.disabled = false;
          submitBtn.textContent = 'Valider';
          
          updateStatus("Erreur lors de la validation", "error");
        }
      })
      .catch(error => {
        console.error("Erreur:", error);
        resultMessage.textContent = "❌ Erreur de communication avec le serveur";
        resultMessage.className = "status-error";
        
        // Réactiver le bouton en cas d'erreur
        submitBtn.disabled = false;
        submitBtn.textContent = 'Valider';
        
        updateStatus("Erreur de connexion", "error");
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
      
      updateStatus("Prêt à scanner", "info");
    }
  </script>

</body>
</html>