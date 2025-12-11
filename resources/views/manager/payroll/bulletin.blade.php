{{-- resources/views/manager/payroll/bulletin.blade.php --}}

{{--
    Cette vue est le corps du bulletin de paie, utilisée pour l'aperçu et le PDF.
    Les variables $employe, $paieHeader, $itemPaie sont fournies par le contrôleur.
--}}
@php
    // --- 1. Récupération des données via Eloquent (User et Pressing) ---
    $user = auth()->user();
    $pressing = $user->pressing ?? null; // Utilisez la relation pour récupérer l'objet Pressing
    
    // --- 2. Initialisation des variables pour la vue ---
    $month = $paieHeader->month ?? 1;
    $year = $paieHeader->year ?? date('Y');
    
    // Données du Pressing (Accès direct aux propriétés du modèle Pressing)
    $pressingName = $pressing->name ?? 'NOM DU PRESSING (INCONNU)'; 
    $pressingAddress = $pressing->address ?? 'Adresse non spécifiée';
    $months = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
    
    // Données Employé
    $hiringDate = isset($employe->hiring_date) ? \Carbon\Carbon::parse($employe->hiring_date)->format('d/m/Y') : 'N/A';
    
    // Données Paie pour les calculs (sécurité en cas de valeur null)
    $baseSalary = $itemPaie->base_salary ?? 0;
    $advantages = $itemPaie->advantages ?? 0;
    $prime = $itemPaie->prime ?? 0;
    $fiscalRetention = $itemPaie->fiscal_retention ?? 0;
    $socialRetention = $itemPaie->social_retention ?? 0;
    $exceptionalRetention = $itemPaie->exceptional_retention ?? 0;
    $patronalContribution = $itemPaie->patronal_contribution ?? 0;
    $fiscalCharge = $itemPaie->fiscal_charge ?? 0;
    $netToPay = $itemPaie->net_paid ?? 0;
    
    // Calculs agrégés
    $totalRemuneration = $baseSalary + $advantages + $prime;
    $totalRetentions = $fiscalRetention + $socialRetention + $exceptionalRetention;
@endphp

<style>
    /* Styles pour l'aperçu HTML et pour DomPDF */
    .bulletin-content {
        color: #333 !important;
        background-color: white !important;
        padding: 20px;
        border: 1px solid #ccc;
        font-family: Arial, sans-serif;
        font-size: 13px;
        max-width: 850px; 
        margin: 0 auto;
    }
    .header-paie, .footer-paie {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 10px;
    }
    .employee-info, .totals-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .employee-info td {
        padding: 5px 0;
    }
    .totals-table th, .totals-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .totals-table th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    .net-to-pay {
        background-color: #d4edda;
        font-size: 1.2em;
        font-weight: bold;
        text-align: right !important;
        color: #155724;
    }
    .retention-label {
        color: #dc3545;
    }
</style>

<div class="bulletin-content">
    
    <div class="header-paie">
        <div class="pressing-details">
            <h1 style="font-size: 1.5em; color: #007bff;">{{ $pressingName }}</h1> 
            <p style="margin: 0;">{{ $pressingAddress }}</p> 
            <p style="margin: 0;">**Bulletin de Paie**</p>
        </div>
        <div class="paie-period" style="text-align: right;">
            <p style="font-size: 1.1em; font-weight: bold;">Période : {{ $months[$month] ?? 'Inconnu' }} {{ $year }}</p>
            <p style="margin: 0;">Date d'édition : {{ date('d/m/Y') }}</p>
        </div>
    </div>
    
    <table class="employee-info">
        <tr>
            <td style="width: 25%;">**Nom Complet :**</td>
            <td style="width: 25%;">{{ $employe->full_name ?? 'N/A' }}</td>
            <td style="width: 25%;">**Date d'Embauche :**</td>
            <td style="width: 25%;">{{ $hiringDate }}</td>
        </tr>
        <tr>
            <td>**Fonction :**</td>
            <td>{{ $employe->function ?? 'N/A' }}</td>
            <td>**Salaire de Base :**</td>
            <td>{{ number_format($employe->base_salary ?? 0, 2, ',', ' ') }} F</td>
        </tr>
    </table>
    
    <table class="totals-table">
        <thead>
            <tr>
                <th style="width: 30%;">Élément</th>
                <th style="width: 20%;">Montant (XAF)</th>
                <th style="width: 30%;">Élément</th>
                <th style="width: 20%;">Montant (XAF)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>**Salaire de Base**</td>
                <td>{{ number_format($baseSalary, 2, ',', ' ') }}</td>
                <td class="retention-label">Retenue Fiscale</td>
                <td>{{ number_format($fiscalRetention, 2, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>**Avantages**</td>
                <td>{{ number_format($advantages, 2, ',', ' ') }}</td>
                <td class="retention-label">Retenue Sociale</td>
                <td>{{ number_format($socialRetention, 2, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>**Primes**</td>
                <td>{{ number_format($prime, 2, ',', ' ') }}</td>
                <td class="retention-label">Retenue Exceptionnelle</td>
                <td>{{ number_format($exceptionalRetention, 2, ',', ' ') }}</td>
            </tr>
            <tr style="border-top: 2px solid #ccc;">
                <td>**Total Brut**</td>
                <td>{{ number_format($totalRemuneration, 2, ',', ' ') }}</td>
                <td>**Total Retenues**</td>
                <td>{{ number_format($totalRetentions, 2, ',', ' ') }}</td>
            </tr>
            
            {{-- Ligne importante pour le Net --}}
            <tr class="net-to-pay">
                <td colspan="3" style="text-align: right;">**NET À PAYER**</td>
                <td>{{ number_format($netToPay, 2, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer-paie" style="border-top: 1px solid #ccc; padding-top: 10px; font-size: 0.9em;">
        <div>
            <p style="margin: 0;">Charges Patronales : {{ number_format($patronalContribution, 2, ',', ' ') }} XAF</p>
            <p style="margin: 0;">Charges Fiscales : {{ number_format($fiscalCharge, 2, ',', ' ') }} XAF</p>
        </div>
        {{-- <div style="text-align: right;">
            <p style="margin: 0;">*Ceci est un aperçu non contractuel*</p>
        </div> --}}
    </div>
</div>