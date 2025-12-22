<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Retrait - Bénéficiaire</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', monospace;
            width: 58mm;
            margin: 0;
            padding: 2mm;
            font-size: 9pt;
            line-height: 1.3;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 2mm 0; }
        .section { margin: 2mm 0; }
        .row { display: flex; justify-content: space-between; margin: 1mm 0; }
        .big { font-size: 11pt; font-weight: bold; }
        .huge { font-size: 13pt; font-weight: bold; }
        .small { font-size: 8pt; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="center bold">KAYPA</div>
    <div class="center small">RECU DE RETRAIT</div>
    <div class="center small">{{ $transfer->paid_at->format('d/m/Y H:i') }}</div>
    <div class="line"></div>

    <div class="center huge">PAYE</div>
    <div class="center huge">{{ $transfer->transfer_number }}</div>
    <div class="line"></div>

    <div class="section">
        <div class="bold">EXPEDITEUR</div>
        <div>{{ $transfer->sender_name }}</div>
        <div class="small">{{ $transfer->sender_country_code }} {{ $transfer->sender_phone }}</div>
        <div class="small">Envoi: {{ $transfer->created_at->format('d/m/Y H:i') }}</div>
    </div>
    <div class="line"></div>

    <div class="section">
        <div class="bold">BENEFICIAIRE</div>
        <div>{{ $transfer->receiver_name }}</div>
        <div class="small">{{ $transfer->receiver_country_code }} {{ $transfer->receiver_phone }}</div>
        <div class="small">NINU: {{ $transfer->receiver_ninu }}</div>
    </div>
    <div class="line"></div>

    <div class="section">
        <div class="row big">
            <span>MONTANT RECU:</span>
            <span>{{ number_format($transfer->amount, 0) }} GDS</span>
        </div>
    </div>
    <div class="line"></div>

    <div class="section small">
        <div>Agence retrait: {{ $transfer->paidAtBranch->name }}</div>
        <div>Agent: {{ $transfer->paidBy->name }}</div>
    </div>
    <div class="line"></div>

    <div class="section small" style="margin-top: 5mm;">
        <div class="center">Signature beneficiaire:</div>
        <div style="height: 10mm; border-bottom: 1px solid #000; margin: 2mm 0;"></div>
    </div>

    <div class="center small" style="margin-top: 3mm;">
        Merci de votre confiance
    </div>

    <!-- Page de signature pour administration -->
    <div style="page-break-after: always; margin-top: 5mm; padding-top: 5mm; border-top: 2px solid #000;">
        <div class="center bold" style="margin-bottom: 3mm;">ADMINISTRATION</div>
        <div class="center small">Transfert: {{ $transfer->transfer_number }}</div>
        <div class="line"></div>

        <div class="section">
            <div class="bold">BENEFICIAIRE</div>
            <div>{{ $transfer->receiver_name }}</div>
            <div class="small">{{ $transfer->receiver_country_code }} {{ $transfer->receiver_phone }}</div>
            <div class="small">NINU: {{ $transfer->receiver_ninu }}</div>
        </div>
        <div class="line"></div>

        <div class="section">
            <div class="row big">
                <span>MONTANT:</span>
                <span>{{ number_format($transfer->amount, 0) }} GDS</span>
            </div>
        </div>
        <div class="line"></div>

        <div class="section small">
            <div class="row">
                <span>Date retrait:</span>
                <span>{{ $transfer->paid_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="row">
                <span>Agence:</span>
                <span>{{ $transfer->paidAtBranch->name }}</span>
            </div>
            <div class="row">
                <span>Agent:</span>
                <span>{{ $transfer->paidBy->name }}</span>
            </div>
        </div>
        <div class="line"></div>

        <div class="section small" style="margin-top: 5mm;">
            <div class="center">Vérification et sceau d'administration:</div>
            <div style="height: 15mm; border-bottom: 1px solid #000; margin: 3mm 0;"></div>
        </div>

        <div class="center small" style="margin-top: 3mm;">
            Document réservé à l'administration
        </div>
    </div>

    <!-- Modal de sélection d'imprimante (invisible sur impression 58mm) -->
    <div id="printerModal" class="no-print" style="
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 10000;
        font-family: Arial, sans-serif;
    ">
        <div style="
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 10001;
            max-width: 400px;
            width: 90%;
        ">
            <h2 style="margin: 0 0 20px 0; color: #333; font-size: 18px; font-weight: bold;">
                🖨️ Sélectionner l'imprimante
            </h2>
            <p style="margin: 0 0 25px 0; color: #666; font-size: 14px;">
                Le reçu sera imprimé en 2 copies avec page d'administration.
            </p>

            <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                <!-- Option Imprimante Bluetooth 58mm -->
                <button onclick="connectBluetoothPrinter()" style="
                    padding: 15px;
                    background-color: #3b82f6;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    font-size: 14px;
                    font-weight: bold;
                    cursor: pointer;
                    transition: background-color 0.3s;
                ">
                    📱 Imprimante Bluetooth 58mm
                </button>

                <!-- Option Imprimante Standard -->
                <button onclick="printStandard()" style="
                    padding: 15px;
                    background-color: #6b7280;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    font-size: 14px;
                    font-weight: bold;
                    cursor: pointer;
                    transition: background-color 0.3s;
                ">
                    🖨️ Autre imprimante
                </button>

                <!-- Option Annuler -->
                <button onclick="cancelPrint()" style="
                    padding: 15px;
                    background-color: #ef4444;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    font-size: 14px;
                    font-weight: bold;
                    cursor: pointer;
                    transition: background-color 0.3s;
                ">
                    ❌ Annuler
                </button>
            </div>

            <!-- Statut de connexion Bluetooth -->
            <div id="bluetoothStatus" style="
                display: none;
                padding: 15px;
                background-color: #f3f4f6;
                border-radius: 6px;
                font-size: 13px;
                color: #333;
            "></div>
        </div>
    </div>

    <script>
        let printCount = 0;
        const totalPrints = 2;

        // Fonction pour imprimer sur imprimante Bluetooth
        async function connectBluetoothPrinter() {
            const statusDiv = document.getElementById('bluetoothStatus');
            statusDiv.style.display = 'block';
            statusDiv.innerHTML = '⏳ Connexion à l\'imprimante Bluetooth...';

            try {
                // Vérifier la disponibilité de l'API Web Bluetooth
                if (!navigator.bluetooth) {
                    statusDiv.innerHTML = '❌ Bluetooth non supporté par ce navigateur. Veuillez utiliser Chrome, Edge ou Opera.';
                    return;
                }

                // Demander l'accès aux appareils Bluetooth
                const device = await navigator.bluetooth.requestDevice({
                    filters: [
                        { name: /^PRINTER/ },
                        { name: /^RP/ },
                        { name: /Thermal Printer/ },
                        { name: /58mm/ }
                    ],
                    optionalServices: ['generic_access', 'device_information']
                });

                statusDiv.innerHTML = '⏳ Connexion à ' + (device.name || 'l\'imprimante') + '...';

                const server = await device.gatt.connect();
                statusDiv.innerHTML = '✅ Connecté! Impression en cours... (1/2)';

                // Imprimer 2 fois
                for (let i = 0; i < totalPrints; i++) {
                    const receiptContent = generateReceiptForBluetooth();
                    await sendToBluetoothPrinter(server, receiptContent);
                    
                    if (i < totalPrints - 1) {
                        statusDiv.innerHTML = '✅ Première copie imprimée. Impression 2/2...';
                        await new Promise(resolve => setTimeout(resolve, 1000));
                    }
                }

                statusDiv.innerHTML = '✅ Impression complète! (2 copies + page admin)';
                
                // Redirection après 2 secondes
                setTimeout(() => {
                    window.location.href = '{{ route("transfers.index") }}';
                }, 2000);

            } catch (error) {
                if (error.name === 'NotFoundError') {
                    statusDiv.innerHTML = '❌ Aucune imprimante Bluetooth trouvée. Assurez-vous que:<br/>• L\'imprimante est allumée<br/>• L\'imprimante est visible (pairing)<br/>• Vous êtes à proximité';
                } else if (error.name === 'NotSupportedError') {
                    statusDiv.innerHTML = '❌ Bluetooth non supporté. Utilisez Chrome, Edge ou Opera.';
                } else if (error.name === 'NotAllowedError') {
                    statusDiv.innerHTML = '❌ Accès Bluetooth refusé. Veuillez réessayer.';
                } else {
                    statusDiv.innerHTML = '❌ Erreur: ' + (error.message || 'Impossible de se connecter');
                }
                console.error('Erreur Bluetooth:', error);
            }
        }

        // Générer le contenu du reçu formaté pour imprimante 58mm
        function generateReceiptForBluetooth() {
            return `
╔════════════════════════════╗
║        KAYPA RECU           ║
║    RECU DE RETRAIT          ║
╚════════════════════════════╝

PAYE
{{ $transfer->transfer_number }}

────────────────────────────

EXPEDITEUR:
{{ $transfer->sender_name }}
{{ $transfer->sender_country_code }} {{ $transfer->sender_phone }}
Envoi: {{ $transfer->created_at->format('d/m/Y H:i') }}

────────────────────────────

BENEFICIAIRE:
{{ $transfer->receiver_name }}
{{ $transfer->receiver_country_code }} {{ $transfer->receiver_phone }}
NINU: {{ $transfer->receiver_ninu }}

────────────────────────────

MONTANT RECU:
{{ number_format($transfer->amount, 0) }} GDS

────────────────────────────

Agence: {{ $transfer->paidAtBranch->name }}
Agent: {{ $transfer->paidBy->name }}

────────────────────────────

Signature bénéficiaire:


────────────────────────────

Merci de votre confiance
            `;
        }

        // Envoyer les données à l'imprimante Bluetooth
        async function sendToBluetoothPrinter(server, content) {
            try {
                // Convertir le contenu en bytes
                const encoder = new TextEncoder();
                const data = encoder.encode(content);
                
                // Chercher le service et la caractéristique de l'imprimante
                const services = await server.getPrimaryServices();
                let written = false;

                for (let service of services) {
                    try {
                        const characteristics = await service.getCharacteristics();
                        
                        for (let char of characteristics) {
                            if (char.properties.write || char.properties.writeWithoutResponse) {
                                // Envoyer par chunks si nécessaire
                                const chunkSize = 20;
                                for (let i = 0; i < data.length; i += chunkSize) {
                                    const chunk = data.slice(i, i + chunkSize);
                                    await char.writeValue(chunk);
                                }
                                written = true;
                                break;
                            }
                        }
                        if (written) break;
                    } catch (e) {
                        // Continuer avec le prochain service
                    }
                }

                if (!written) {
                    throw new Error('Impossible de trouver la caractéristique d\'écriture');
                }
            } catch (error) {
                console.error('Erreur lors de l\'envoi:', error);
                throw error;
            }
        }

        // Imprimer avec le dialogue standard du navigateur (2 fois)
        function printStandard() {
            document.getElementById('printerModal').style.display = 'none';
            printCount = 0;
            performPrint();
        }

        function performPrint() {
            setTimeout(() => {
                window.print();
                printCount++;
                if (printCount < totalPrints) {
                    // Attendre avant la deuxième copie
                    setTimeout(performPrint, 2000);
                } else {
                    // Redirection après les 2 impressions
                    setTimeout(() => {
                        window.location.href = '{{ route("transfers.index") }}';
                    }, 1000);
                }
            }, 100);
        }

        // Annuler l'impression
        function cancelPrint() {
            document.getElementById('printerModal').style.display = 'none';
        }

        // Afficher la modal au chargement
        if (!sessionStorage.getItem('printed_{{ $transfer->id }}')) {
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('printerModal').style.display = 'block';
            });
        }

        // Rediriger après impression standard
        window.onafterprint = function() {
            if (printCount >= totalPrints) {
                window.location.href = '{{ route("transfers.index") }}';
            }
        };
    </script>
</body>
</html>
