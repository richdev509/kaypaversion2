<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Transfert - Expéditeur</title>
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
    <div class="center small">TRANSFERT LOCAL</div>
    <div class="center small">{{ $transfer->created_at->format('d/m/Y H:i') }}</div>
    <div class="line"></div>

    <div class="center huge">{{ $transfer->transfer_number }}</div>
    <div class="line"></div>

    <div class="section">
        <div class="bold">EXPEDITEUR</div>
        <div>{{ $transfer->sender_name }}</div>
        <div class="small">{{ $transfer->sender_country_code }} {{ $transfer->sender_phone }}</div>
    </div>
    <div class="line"></div>

    <div class="section">
        <div class="bold">BENEFICIAIRE</div>
        <div>{{ $transfer->receiver_name }}</div>
        <div class="small">{{ $transfer->receiver_country_code }} {{ $transfer->receiver_phone }}</div>
    </div>
    <div class="line"></div>

    <div class="section">
        <div class="row">
            <span>Montant:</span>
            <span class="bold">{{ number_format($transfer->amount, 0) }} GDS</span>
        </div>
        <div class="row">
            <span>Frais:</span>
            <span>{{ number_format($transfer->fees, 0) }} GDS</span>
        </div>
        @if($transfer->discount > 0)
        <div class="row">
            <span>Reduction:</span>
            <span>-{{ number_format($transfer->discount, 0) }} GDS</span>
        </div>
        @endif
        <div class="line"></div>
        <div class="row big">
            <span>TOTAL:</span>
            <span>{{ number_format($transfer->total_amount, 0) }} GDS</span>
        </div>
    </div>
    <div class="line"></div>

    <div class="section small">
        <div>Agence: {{ $transfer->branch->name }}</div>
        <div>Agent: {{ $transfer->createdBy->name }}</div>
    </div>
    <div class="line"></div>

    <div class="center small">
        Presentez ce numero pour retrait
    </div>
    <div class="center small">
        NINU obligatoire
    </div>
    <div class="center small" style="margin-top: 3mm;">
        Merci de votre confiance
    </div>

    <script>
        if (!sessionStorage.getItem('printed_{{ $transfer->id }}')) {
            setTimeout(() => {
                window.print();
                sessionStorage.setItem('printed_{{ $transfer->id }}', 'true');
            }, 1000);
        }

        // Rediriger vers la page d'accueil des transferts après impression
        window.onafterprint = function() {
            window.location.href = '{{ route("transfers.index") }}';
        };
    </script>
</body>
</html>
