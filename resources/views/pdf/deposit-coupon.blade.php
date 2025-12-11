<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Coupon de Dépôt - {{ $order->reference }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
            color: #333;
        }
        .container {
            width: 100%;
            border: 1px solid #ccc;
            padding: 15px;
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #5d9cec;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header h1 {
            color: #5d9cec;
            font-size: 16px;
            margin: 0;
        }
        .info-box {
            display: inline-block;
            width: 48%;
            vertical-align: top;
            margin-bottom: 10px;
        }
        .info-box.left { text-align: left; }
        .info-box.right { text-align: right; }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #5d9cec;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-top: 10px;
            margin-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .totals {
            margin-top: 10px;
            text-align: right;
            border-top: 2px solid #ccc;
            padding-top: 10px;
        }
        .total-line {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            text-align: center;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header">
            <h1>COUPON DE DÉPÔT PRESSING</h1>
            <p>Réf. : **{{ $order->reference }}** | Date : {{ \Carbon\Carbon::parse($order->deposit_date)->format('d/m/Y H:i') }}</p>
        </div>

        <div class="info-box left">
            <p style="font-weight: bold;">CLIENT : {{ $order->client->name ?? 'Client Inconnu' }}</p>
            <p>Téléphone : {{ $order->client->phone ?? 'N/A' }}</p>
            <p>Statut Livraison : **{{ ucfirst($order->delivery_status) }}**</p>
        </div>

        <div class="info-box right">
            <p>Saisi par : {{ $order->user->name ?? 'Utilisateur Système' }}</p>
            <p>Statut Paiement : **{{ ucfirst($order->payment_status) }}**</p>
            <p>Date de Livraison Prévue : N/A</p>
        </div>

        <div style="clear: both;"></div>

        <div class="section-title">Détails des Articles</div>
        <table>
            <thead>
                <tr>
                    <th>Article & Service</th>
                    <th style="text-align: center;">Qté</th>
                    <th style="text-align: right;">P.U. (XAF)</th>
                    <th style="text-align: right;">Total (XAF)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total_articles = 0;
                    $grand_total = 0;
                @endphp
                @foreach($order->items as $item)
                    @php
                        // Gestion du Nullsafe pour l'affichage (voir corrections précédentes)
                        $articleName = $item->article?->name ?? 'Article Inconnu';
                        $serviceName = $item->service?->name ?? 'Service Inconnu';
                        $itemTotal = $item->quantity * $item->unit_price;
                        $grand_total += $itemTotal;
                        $total_articles += $item->quantity;
                    @endphp
                    <tr>
                        <td>{{ $articleName }} - {{ $serviceName }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                        <td style="text-align: right;">{{ number_format($itemTotal, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <p>Nombre total d'articles : **{{ $total_articles }}**</p>
            <div class="total-line">MONTANT TOTAL DÛ : **{{ number_format($order->total_amount, 0, ',', ' ') }} XAF**</div>
            <div class="total-line" style="color: green;">Montant Payé : {{ number_format($order->paid_amount, 0, ',', ' ') }} XAF</div>
            <div class="total-line" style="color: red;">Reste à Payer : {{ number_format($order->total_amount - $order->paid_amount, 0, ',', ' ') }} XAF</div>
        </div>
        
        <div class="footer">
            <p>Merci de votre confiance. Ce coupon doit être présenté pour le retrait du dépôt.</p>
            <p style="font-weight: bold; font-size: 10px; border-top: 1px solid #000; display: inline-block; padding-top: 5px;">
                {{ $pressingName }}
            </p>
        </div>

    </div>
</body>
</html>