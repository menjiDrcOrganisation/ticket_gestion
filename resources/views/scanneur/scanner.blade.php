 <!-- ENTÊTE -->
 
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
    .pulse { animation:pulse 2s infinite; } @keyframes pulse {0%{opacity:0.7}50%{opacity:1}100%{opacity:0.7}}
  </style>
</head>
<body class="bg-blue-50 min-h-screen flex flex-col items-center justify-start">

  <!-- Modal résultat -->
  <div id="resultModal" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-60 z-50 p-4">
    <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-lg text-center">
      <h2 class="text-2xl font-bold mb-2">Résultat du scan</h2>
      <p id="resultMessage" class="mb-4"></p>
      
      <form id="scanForm">
        <div id="billetDetails" class="text-left text-gray-700 mb-4"></div>
        @csrf
        <button type="submit" id="valide_code" class="bg-blue-500 text-white px-4 py-2 rounded-lg mr-2">Valider</button>
        <button type="button" id="closeModalBtn" class="bg-gray-200 px-4 py-2 rounded-lg">Fermer</button>
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
      <div class="w-10"></div> <!-- Élément vide pour équilibrer l'espace -->
    </div>
  </header>
  <!-- Scanner -->
  <div class="p-6 w-full max-w-xl mx-auto mt-8 bg-white rounded-2xl shadow-lg">
    <h1 class="text-xl font-bold mb-4 text-center">Scanner un QR Code</h1>
    <div class="flex justify-center mb-6 scanner-frame">
      <div id="reader" class="w-64 h-64 bg-white"></div>
      <div class="scanner-corner scanner-corner-tl"></div>
      <div class="scanner-corner scanner-corner-tr"></div>
      <div class="scanner-corner scanner-corner-bl"></div>
      <div class="scanner-corner scanner-corner-br"></div>
    </div>
    <div class="flex justify-center gap-4">
      <button id="startBtn" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Démarrer scan</button>
      <button id="uploadBtn" class="bg-orange-500 text-white px-4 py-2 rounded-lg">Scanner image</button>
      <input type="file" id="fileInput" accept="image/*" class="hidden"/>
    </div>
    <div id="scannerStatus" class="text-center mt-4">Prêt à scanner</div>
  </div>

<script>
const verifyUrl = "{{ url('/scanneur/scanne-preview') }}";
const storeUrl = "{{ route('scanneur.processScan') }}";
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

let html5QrCode = null;
let currentCameraId = null;

// Elements
const resultModal = document.getElementById('resultModal');
const resultMessage = document.getElementById('resultMessage');
const billetDetails = document.getElementById('billetDetails');
const closeModalBtn = document.getElementById('closeModalBtn');
const startBtn = document.getElementById('startBtn');
const uploadBtn = document.getElementById('uploadBtn');
const fileInput = document.getElementById('fileInput');
const scannerStatus = document.getElementById('scannerStatus');
const scanForm = document.getElementById('scanForm');

// Mettre à jour le statut
function updateStatus(msg,type='info'){
  const colors={info:'blue',success:'green',error:'red'};
  const color=colors[type]||'blue';
  scannerStatus.innerHTML=`<span class="inline-flex items-center px-3 py-1 rounded-full text-${color}-700 bg-${color}-50">
    <span class="w-2 h-2 rounded-full mr-2 ${type==='info'?'pulse':''} bg-${color}-500"></span>${msg}</span>`;
}

// Charger caméra
Html5Qrcode.getCameras().then(cameras=>{
  if(!cameras.length){ updateStatus("Aucune caméra détectée","error"); return;}
  const back=cameras.find(cam=>cam.label.toLowerCase().includes('back')||cam.label.toLowerCase().includes('environment')) || cameras[0];
  currentCameraId=back.id;
  updateStatus("Caméra prête. Cliquez sur 'Démarrer scan'");
}).catch(err=>{updateStatus("Erreur caméra","error"); console.error(err);});

// Démarrer scan
startBtn.addEventListener('click',()=>{
  if(!currentCameraId) return updateStatus("Aucune caméra disponible","error");
  if(html5QrCode) html5QrCode.stop().catch(()=>{});
  html5QrCode = new Html5Qrcode("reader");
  html5QrCode.start(
    {deviceId:{exact:currentCameraId}},
    {fps:15, qrbox:250},
    handleScan,
    errorMessage=>{}
  ).then(()=>updateStatus("Recherche QR Code..."))
  .catch(err=>{updateStatus("Impossible de démarrer scanner","error"); console.error(err);});
});

// Scanner image
uploadBtn.addEventListener('click',()=>fileInput.click());
fileInput.addEventListener('change', async e=>{
  const file = e.target.files[0];
  if(!file) return;
  updateStatus("Analyse image...");
  const scanner=new Html5Qrcode("reader");
  try{
    const result = await scanner.scanFile(file,true);
    handleScan(result);
  }catch(err){updateStatus("Impossible de lire QR Code","error"); console.error(err);}
});

// Traitement scan
function handleScan(decodedText) {
    if (!decodedText) return;
    updateStatus("QR Code détecté...", "success");

    // Réafficher le bouton Valider
    document.getElementById('valide_code').classList.remove('hidden');

    fetch(verifyUrl, {
        method: 'POST',
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
        body: JSON.stringify({ code: decodedText })
    })
    .then(res => res.json())
    .then(data => {
        console.log(data);
        resultMessage.textContent = data.message || "✓ Code scanné";
        billetDetails.innerHTML = `
            <p><strong>Auteur :</strong> ${data.nom || "N/A"}</p>
            <p><strong>Quantité restante :</strong> ${data.quantite_fictif ?? 0}</p>
            <div class="mt-2">
                <label class="block text-gray-700 font-medium mb-1">Quantité à utiliser :</label>
                <input type="hidden" name="code" value="${data.code ?? 0}">
                <input type="number" name="quantite" value="1" min="1" max="${data.quantite_fictif ?? 0}" 
                    class="w-full border p-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        `;
        resultModal.classList.remove('hidden');
    })
    .catch(err => {
        resultMessage.textContent = "❌ Erreur serveur";
        billetDetails.innerHTML = "";
        resultModal.classList.remove('hidden');
        console.error(err);
    });
}

// Fermer modal
closeModalBtn.addEventListener('click',()=>{
  resultModal.classList.add('hidden');
  billetDetails.innerHTML="";
  updateStatus("Prêt à scanner");
});

// Soumission formulaire
scanForm.addEventListener('submit',e=>{
  e.preventDefault();
  const formData = new FormData(scanForm);
  fetch(storeUrl,{
    method:'POST',
    headers:{"X-CSRF-TOKEN":csrfToken},
    body:formData
  }).then(res=>res.json())
    .then(data=>{
      console.log(data);
      resultMessage.textContent=data.message||"✅ Billet validé";
      document.getElementById('valide_code').classList.add('hidden');
      

      
      billetDetails.innerHTML="";
    }).catch(err=>{
      resultMessage.textContent="❌ Erreur validation";
      console.error(err);
    });
});
</script>

</body>
</html>
