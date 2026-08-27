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
    /* Styles essentiels */
    .scanner-frame { position: relative; border-radius:16px; overflow:hidden; }
    .scanner-corner { position:absolute; width:30px;height:30px; border-color:#3b82f6; z-index:10;}
    .scanner-corner-tl {top:0;left:0;border-top:4px solid;border-left:4px solid;}
    .scanner-corner-tr {top:0;right:0;border-top:4px solid;border-right:4px solid;}
    .scanner-corner-bl {bottom:0;left:0;border-bottom:4px solid;border-left:4px solid;}
    .scanner-corner-br {bottom:0;right:0;border-bottom:4px solid;border-right:4px solid;}
    .pulse { animation:pulse 2s infinite; } 
    @keyframes pulse {0%{opacity:0.7}50%{opacity:1}100%{opacity:0.7}}
    
    /* Améliorations pour le modal */
    .modal-overlay {
      transition: opacity 0.3s ease;
    }
    .modal-content {
      transform: scale(0.95);
      transition: transform 0.3s ease;
    }
    .modal-overlay.show .modal-content {
      transform: scale(1);
    }
  </style>
</head>
<body class="bg-blue-50 min-h-screen flex flex-col items-center justify-start">

  <!-- Modal résultat -->
  <div id="resultModal" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-60 z-50 p-4 modal-overlay">
    <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-lg modal-content">
      <h2 class="text-2xl font-bold mb-2">Résultat du scan</h2>
      <p id="resultMessage" class="mb-4"></p>
      
      <form id="scanForm">
        <div id="billetDetails" class="text-left text-gray-700 mb-4"></div>
        @csrf
        <div class="flex gap-2 justify-center">
          <button type="submit" id="valide_code" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">Valider</button>
          <button type="button" id="closeModalBtn" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">Fermer</button>
        </div>
      </form>
    </div>
  </div>

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
      <h1 class="text-xl font-bold"></h1>
      <div class="w-10"></div> <!-- Équilibrer l'espace -->
    </div>
  </header>

  <!-- Scanner -->
  <div class="p-6 w-full max-w-xl mx-auto mt-8 bg-white rounded-2xl shadow-lg">
    @if(isset($evenementsOrganisateur) && $evenementsOrganisateur->count() > 0)
      <form method="GET" action="{{ route('scanneur.showScanner') }}" class="mb-4">
        <label for="event_id" class="mb-1.5 block text-sm font-medium text-gray-700">Événement à scanner</label>
        <div class="flex gap-2">
          <select id="event_id" name="event_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            @foreach($evenementsOrganisateur as $eventOption)
              <option value="{{ $eventOption->id }}" @selected((int) $selectedEventId === (int) $eventOption->id)>
                {{ $eventOption->nom }}
              </option>
            @endforeach
          </select>
          <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Appliquer</button>
        </div>
      </form>
    @endif

    @if(isset($evenementsOrganisateur) && $evenementsOrganisateur->count() === 0)
      <p class="mb-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-700">Aucun événement disponible pour ce compte.</p>
    @endif

    <h1 class="text-xl font-bold mb-4 text-center">Scanner un QR Code</h1>
    <div class="flex justify-center mb-6 scanner-frame relative">
      <div id="reader" class="w-64 h-64 bg-white"></div>
      <div class="scanner-corner scanner-corner-tl"></div>
      <div class="scanner-corner scanner-corner-tr"></div>
      <div class="scanner-corner scanner-corner-bl"></div>
      <div class="scanner-corner scanner-corner-br"></div>
    </div>
    <div class="flex justify-center gap-4">
      <button id="startBtn" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">Démarrer scan</button>
      <button id="stopBtn" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">Arrêter scan</button>
      <button id="uploadBtn" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors">Scanner image</button>
      <input type="file" id="fileInput" accept="image/*" class="hidden"/>
    </div>
    <div id="scannerStatus" class="text-center mt-4">Prêt à scanner</div>
  </div>

<script>
const verifyUrl = "{{ url('/scanneur/scanne-preview') }}";
const storeUrl = "{{ route('scanneur.processScan') }}";
const selectedEventId = {{ (int) ($selectedEventId ?? 0) }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

let html5QrCode = null;
let currentCameraId = null;
let isScanning = false;

// Éléments DOM
const resultModal = document.getElementById('resultModal');
const resultMessage = document.getElementById('resultMessage');
const billetDetails = document.getElementById('billetDetails');
const closeModalBtn = document.getElementById('closeModalBtn');
const startBtn = document.getElementById('startBtn');
const stopBtn = document.getElementById('stopBtn');
const uploadBtn = document.getElementById('uploadBtn');
const fileInput = document.getElementById('fileInput');
const scannerStatus = document.getElementById('scannerStatus');
const scanForm = document.getElementById('scanForm');
const valideCodeBtn = document.getElementById('valide_code');

// Variable pour stocker les données du scan actuel
let currentScanData = null;

function updateStatus(msg, type = 'info') {
  const colors = { info: 'blue', success: 'green', error: 'red' };
  const color = colors[type] || 'blue';
  scannerStatus.innerHTML = `<span class="inline-flex items-center px-3 py-1 rounded-full text-${color}-700 bg-${color}-50">
    <span class="w-2 h-2 rounded-full mr-2 ${type === 'info' ? 'pulse' : ''} bg-${color}-500"></span>${msg}</span>`;
}

// Fonction pour fermer le modal et réinitialiser
function closeModal() {
  resultModal.classList.add('hidden');
  resultModal.style.display = 'none';
  
  // Réinitialiser le formulaire
  scanForm.reset();
  billetDetails.innerHTML = '';
  resultMessage.textContent = '';
  currentScanData = null;
  
  // Réactiver le scanner si nécessaire
  if (isScanning && html5QrCode) {
    setTimeout(() => {
      updateStatus("Prêt à scanner un nouveau QR Code", "info");
    }, 500);
  } else {
    updateStatus("Prêt à scanner", "info");
  }
}

// Fonction pour afficher le modal
function showModal() {
  resultModal.classList.remove('hidden');
  resultModal.style.display = 'flex';
}

// Fonction pour arrêter le scanner
async function stopScanner() {
  if (html5QrCode && isScanning) {
    try {
      await html5QrCode.stop();
      isScanning = false;
      updateStatus("Scanner arrêté", "info");
    } catch (err) {
      console.error("Erreur lors de l'arrêt du scanner:", err);
    }
  }
}

// Charger caméra pour Android/Desktop
Html5Qrcode.getCameras().then(cameras => {
  if (!cameras.length) { 
    updateStatus("Aucune caméra détectée", "error");
    return;
  }
  const back = cameras.find(cam => 
    cam.label.toLowerCase().includes('back') || 
    cam.label.toLowerCase().includes('environment')
  ) || cameras[0];
  currentCameraId = back.id;
  updateStatus("Caméra prête. Cliquez sur 'Démarrer scan'");
}).catch(err => {
  updateStatus("Erreur caméra", "error");
  console.error(err);
});

// Fonction pour démarrer le scanner
async function startScanner() {
  if (isScanning) {
    updateStatus("Scanner déjà actif", "info");
    return;
  }
  
  if (!currentCameraId) {
    updateStatus("Caméra non disponible", "error");
    return;
  }
  
  // Arrêter l'ancien scanner s'il existe
  if (html5QrCode) {
    try {
      await html5QrCode.stop();
    } catch (err) {
      console.log("Erreur lors de l'arrêt:", err);
    }
  }
  
  html5QrCode = new Html5Qrcode("reader");
  
  // Détection iOS Safari
  const isiOS = /iPhone|iPad|iPod/.test(navigator.userAgent);
  const constraints = isiOS 
                      ? { facingMode: "environment" } 
                      : { deviceId: { exact: currentCameraId } };

  try {
    await html5QrCode.start(
      constraints,
      { fps: 15, qrbox: 250 },
      handleScan,
      errorMessage => { 
        // Ignorer les erreurs mineures de scanning
        if (!errorMessage.includes("No QR code found")) {
          console.log(errorMessage);
        }
      }
    );
    isScanning = true;
    updateStatus("Recherche QR Code...", "success");
  } catch (err) {
    updateStatus("Impossible de démarrer scanner", "error");
    console.error(err);
    isScanning = false;
  }
}

// Scanner image
uploadBtn.addEventListener('click', () => {
  if (isScanning) {
    stopScanner();
  }
  fileInput.click();
});

fileInput.addEventListener('change', async e => {
  const file = e.target.files[0];
  if (!file) return;
  updateStatus("Analyse image...");
  
  try {
    const scanner = new Html5Qrcode("reader");
    const result = await scanner.scanFile(file, true);
    handleScan(result);
  } catch(err) {
    updateStatus("Impossible de lire QR Code", "error");
    console.error(err);
  }
  
  // Réinitialiser l'input file
  fileInput.value = '';
});

// Traitement scan
async function handleScan(decodedText) {
  if (!decodedText || !isScanning) return;
  
  // Éviter les scans multiples
  if (currentScanData) return;
  
  updateStatus("QR Code détecté, vérification en cours...", "success");
  
  // Arrêter temporairement le scanner pour éviter les scans multiples
  await stopScanner();
  
  try {
    const response = await fetch(verifyUrl, {
      method: 'POST',
      headers: { 
        "Content-Type": "application/json", 
        "X-CSRF-TOKEN": csrfToken 
      },
      body: JSON.stringify({ code: decodedText, event_id: selectedEventId })
    });
    
    const data = await response.json();
    
    if (response.ok) {
      resultMessage.textContent = data.message || "✓ Code scanné";
      resultMessage.className = "mb-4 text-green-600";
      
      currentScanData = data;
      
      billetDetails.innerHTML = `
        <p><strong>Auteur :</strong> ${data.nom || "N/A"}</p>
        <p><strong>Quantité restante :</strong> ${data.quantite_fictif ?? 0}</p>
        <div class="mt-2">
          <label class="block text-gray-700 font-medium mb-1">Quantité à utiliser :</label>
          <input type="hidden" name="code" value="${data.code || ''}">
          <input type="number" name="quantite" value="1" min="1" max="${data.quantite_fictif ?? 0}" 
            class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      `;
    } else {
      resultMessage.textContent = data.message || "❌ Code invalide";
      resultMessage.className = "mb-4 text-red-600";
      billetDetails.innerHTML = '';
      currentScanData = null;
    }
    
    showModal();
  } catch (err) {
    resultMessage.textContent = "❌ Erreur serveur";
    resultMessage.className = "mb-4 text-red-600";
    billetDetails.innerHTML = "";
    currentScanData = null;
    showModal();
    console.error(err);
  }
}

// Soumission formulaire
scanForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  if (!currentScanData) {
    resultMessage.textContent = "❌ Aucune donnée de scan valide";
    resultMessage.className = "mb-4 text-red-600";
    return;
  }
  
  valideCodeBtn.disabled = true;
  valideCodeBtn.textContent = "Validation...";
  
  try {
    const formData = new FormData(scanForm);
    formData.append('event_id', selectedEventId);
    const response = await fetch(storeUrl, {
      method: 'POST',
      headers: { "X-CSRF-TOKEN": csrfToken },
      body: formData
    });
    
    const data = await response.json();
    
    if (response.ok) {
      resultMessage.textContent = data.message || "✅ Billet validé";
      resultMessage.className = "mb-4 text-green-600";
      
      // Désactiver le bouton de validation après succès
      valideCodeBtn.disabled = true;
      valideCodeBtn.textContent = "Validé";
      
      // Masquer le formulaire ou le désactiver
      const quantityInput = billetDetails.querySelector('input[name="quantite"]');
      if (quantityInput) quantityInput.disabled = true;
      
      // Fermer automatiquement après 2 secondes
      setTimeout(() => {
        closeModal();
        // Réactiver le scanner si on veut continuer
        if (confirm("Voulez-vous scanner un autre QR Code ?")) {
          startScanner();
        }
      }, 2000);
    } else {
      resultMessage.textContent = data.message || "❌ Erreur lors de la validation";
      resultMessage.className = "mb-4 text-red-600";
      valideCodeBtn.disabled = false;
      valideCodeBtn.textContent = "Valider";
    }
  } catch (err) {
    resultMessage.textContent = "❌ Erreur réseau";
    resultMessage.className = "mb-4 text-red-600";
    valideCodeBtn.disabled = false;
    valideCodeBtn.textContent = "Valider";
    console.error(err);
  }
});

// Événements
startBtn.addEventListener('click', startScanner);
stopBtn.addEventListener('click', stopScanner);
closeModalBtn.addEventListener('click', closeModal);

// Fermer le modal en cliquant en dehors
resultModal.addEventListener('click', (e) => {
  if (e.target === resultModal) {
    closeModal();
  }
});

// Gestion de la visibilité de la page (arrêter le scanner quand on quitte)
document.addEventListener('visibilitychange', () => {
  if (document.hidden && isScanning) {
    stopScanner();
  } else if (!document.hidden && !isScanning && html5QrCode) {
    // Optionnel: redémarrer automatiquement
    // startScanner();
  }
});

// Nettoyage à la fermeture de la page
window.addEventListener('beforeunload', () => {
  if (html5QrCode && isScanning) {
    html5QrCode.stop().catch(() => {});
  }
});

// Initialisation
updateStatus("Prêt à scanner. Cliquez sur 'Démarrer scan' pour commencer.");
</script>

</body>
</html>