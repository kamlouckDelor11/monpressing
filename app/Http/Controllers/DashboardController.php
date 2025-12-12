<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Payroll;
use App\Models\ItemSpens;
use App\Models\Paie;
use Illuminate\Support\Facades\DB; 

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Initialisation
        $userPressingToken = Auth::user()->pressing_token;
        
        $currentPeriodStart = Carbon::now()->subDays(5)->startOfDay();
        $previousPeriodStart = Carbon::now()->subDays(10)->startOfDay();
        $currentPeriodEnd = Carbon::now()->endOfDay();
        
        $baseOrderQuery = Order::where('pressing_token', $userPressingToken);

        // --- MÉTRIQUE 1 & 2 : DÉPÔTS et CHIFFRE D'AFFAIRES (CA) ---
        
        // Période Actuelle
        $currentOrders = (clone $baseOrderQuery)
            ->where('deposit_date', '>=', $currentPeriodStart)
            ->where('deposit_date', '<=', $currentPeriodEnd);

        $currentCount = $currentOrders->count();
        $currentAmount = $currentOrders->sum('total_amount') ?? 0;

        // Période Précédente
        $previousOrders = (clone $baseOrderQuery)
            ->where('deposit_date', '>=', $previousPeriodStart)
            ->where('deposit_date', '<', $currentPeriodStart);

        $previousCount = $previousOrders->count();
        $previousAmount = $previousOrders->sum('total_amount') ?? 0;

        // Calculs de croissance sécurisés
        $deposits = $this->calculateGrowth($currentCount, $previousCount);
        $ca = $this->calculateGrowth($currentAmount, $previousAmount);
        
        // --- MÉTRIQUE 3 : SOLDE DE TRÉSORERIE ---
        
        // Encaissements (via Order Tokens)
        $orderTokens = (clone $baseOrderQuery)->pluck('token');
        $encaissements = Transaction::whereIn('order_token', $orderTokens)->sum('amount') ?? 0;
        
        // Décaissements Salaires (Somme des 3 colonnes)
        $salariesDecaissements = Paie::where('pressing_token', $userPressingToken)
            ->where('status', 'paid')
            ->sum(DB::raw('net_to_pay + total_fiscal_charges + total_patronal_contributions')) ?? 0;

        // Décaissements Dépenses
        $spensesDecaissements = ItemSpens::where('pressing_token', $userPressingToken)
            ->where('status', 'validated')
            ->sum('amount_spens') ?? 0;

        $totalDecaissements = $salariesDecaissements + $spensesDecaissements;
        
        $treasury = [
            'encaissements' => $encaissements,
            'decaissements' => $totalDecaissements,
            'solde' => $encaissements - $totalDecaissements,
        ];

        // --- MÉTRIQUE 4 : COMMANDES EN COURS ---
        
        $pendingOrdersCount = (clone $baseOrderQuery)->where('delivery_status', 'pending')->count();
        $readyOrdersCount = (clone $baseOrderQuery)->where('delivery_status', 'ready')->count();
        
        // --- DERNIERS DÉPÔTS pour le tableau ---
        $latestOrders = (clone $baseOrderQuery)
            ->with('client')
            ->latest('deposit_date')
            ->take(5)
            ->get();


        return view('dashboard', compact(
            'deposits',
            'ca',
            'treasury',
            'pendingOrdersCount',
            'readyOrdersCount',
            'latestOrders'
        ));
    }

    /**
     * Calcule le taux de croissance et formate le résultat, gérant le cas du dénominateur zéro.
     */
    private function calculateGrowth(float $current, float $previous): array
    {
        $previous = (float) $previous; 

        if ($previous == 0) {
            $growthRate = ($current > 0) ? 100 : 0; 
        } else {
            $growthRate = round((($current - $previous) / $previous) * 100, 1);
        }

        return [
            'current_count' => $current,
            'current_amount' => $current,
            'previous_count' => $previous,
            'growth_rate' => $growthRate,
        ];
    }
}