<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de Transferts</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #2563eb;
        }
        .header h1 {
            font-size: 20px;
            color: #1e40af;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 11px;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
            background: #f3f4f6;
            padding: 10px;
            border-radius: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .info-item {
            display: flex;
        }
        .info-label {
            font-weight: bold;
            color: #374151;
            min-width: 100px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
        }
        .stat-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead {
            background: #2563eb;
            color: white;
        }
        th {
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        td {
            padding: 6px 5px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        .status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            padding: 10px 0;
            border-top: 1px solid #e5e7eb;
        }
        .amount {
            font-weight: bold;
            color: #059669;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>RAPPORT DE TRANSFERTS</h1>
        <p>Période: {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</p>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <!-- Informations du rapport -->
    <div class="info-section">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Période:</span>
                <span>{{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</span>
            </div>
            @if($branch)
            <div class="info-item">
                <span class="info-label">Branche:</span>
                <span>{{ $branch->name }}</span>
            </div>
            @endif
            @if($status)
            <div class="info-item">
                <span class="info-label">Statut:</span>
                <span>
                    @if($status === 'pending') En attente
                    @elseif($status === 'paid') Payé
                    @else Annulé
                    @endif
                </span>
            </div>
            @endif
            <div class="info-item">
                <span class="info-label">Total transferts:</span>
                <span>{{ number_format($stats['total_count']) }}</span>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">En Attente</div>
            <div class="stat-value">{{ number_format($stats['pending_count']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Payés</div>
            <div class="stat-value">{{ number_format($stats['paid_count']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Annulés</div>
            <div class="stat-value">{{ number_format($stats['cancelled_count']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Montant</div>
            <div class="stat-value">{{ number_format($stats['total_amount'], 2) }} GDS</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Frais Collectés</div>
            <div class="stat-value">{{ number_format($stats['total_fees'], 2) }} GDS</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Revenu Total</div>
            <div class="stat-value">{{ number_format($stats['total_revenue'], 2) }} GDS</div>
        </div>
    </div>

    <!-- Liste des transferts -->
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Numéro</th>
                <th style="width: 10%;">Date</th>
                <th style="width: 18%;">Expéditeur</th>
                <th style="width: 18%;">Bénéficiaire</th>
                <th style="width: 10%;">Montant</th>
                <th style="width: 8%;">Frais</th>
                <th style="width: 10%;">Statut</th>
                <th style="width: 16%;">Branche</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfers as $transfer)
                <tr>
                    <td>{{ $transfer->transfer_number }}</td>
                    <td>{{ $transfer->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        {{ $transfer->sender_name }}<br>
                        <span style="color: #6b7280;">{{ $transfer->sender_country_code }} {{ $transfer->sender_phone }}</span>
                    </td>
                    <td>
                        {{ $transfer->receiver_name }}<br>
                        <span style="color: #6b7280;">{{ $transfer->receiver_country_code }} {{ $transfer->receiver_phone }}</span>
                    </td>
                    <td class="amount">{{ number_format($transfer->amount, 2) }} GDS</td>
                    <td>{{ number_format($transfer->fees, 2) }} GDS</td>
                    <td>
                        @if($transfer->status === 'pending')
                            <span class="status status-pending">EN ATTENTE</span>
                        @elseif($transfer->status === 'paid')
                            <span class="status status-paid">PAYÉ</span>
                        @else
                            <span class="status status-cancelled">ANNULÉ</span>
                        @endif
                    </td>
                    <td>{{ $transfer->branch->name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pied de page -->
    <div class="footer">
        <p>Kaypa - Rapport de Transferts | Confidentiel</p>
    </div>
</body>
</html>
