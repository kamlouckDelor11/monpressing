<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Employe;
use App\Models\Paie;
use App\Models\ItemPaie;
use App\Models\Depense; // Assurez-vous d'importer votre modèle Depense
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PayrollController extends Controller
{
    // Clé étrangère du pressing (à adapter à votre logique réelle)
    private function getPressingToken()
    {
        // Supposons que le token du pressing est stocké sur l'utilisateur connecté
        return Auth::user()->pressing_token; 
    }

    // Vue principale de la gestion de la paie et de l'état du personnel
    public function index(Request $request)
    {
        $pressingToken = $this->getPressingToken();
        
        $employes = Employe::where('pressing_token', $pressingToken)
                            ->paginate(5, ['*'], 'employePage');
                            
        $unpaidPaies = Paie::where('pressing_token', $pressingToken)
                           ->where('status', 'pending')
                           ->orderBy('year', 'asc')
                           ->orderBy('month', 'asc')
                           ->get();

        if ($request->ajax()) {
            // Utilisé pour recharger uniquement la liste des employés
            return response()->json([
                'employes' => $employes,
                'unpaidPaies' => $unpaidPaies
            ]);
        }

        return view('manager.payroll.index', compact('employes', 'unpaidPaies'));
    }

    // --- CRUD Employé ---

    // Récupérer un employé pour pré-remplir le formulaire
    public function getEmploye($token)
    {
        $employe = Employe::where('token', $token)
                          ->where('pressing_token', $this->getPressingToken())
                          ->firstOrFail();

        return response()->json($employe);
    }
    
    // Créer un nouvel employé
    public function storeEmploye(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'function' => 'required|string|max:100',
            'base_salary' => 'required|numeric|min:0',
            'hiring_date' => 'required|date',
        ]);
        
        Employe::create([
            'token' => Str::uuid(),
            'full_name' => $request->full_name,
            'function' => $request->function,
            'base_salary' => $request->base_salary,
            'hiring_date' => $request->hiring_date,
            'pressing_token' => $this->getPressingToken(),
            'user_token' => Auth::user()->token,
        ]);

        return response()->json(['message' => 'Employé créé avec succès!']);
    }

    // Mettre à jour un employé
    public function updateEmploye(Request $request, $token)
    {
        $employe = Employe::where('token', $token)
                          ->where('pressing_token', $this->getPressingToken())
                          ->firstOrFail();

        $request->validate([
            'full_name' => 'required|string|max:255',
            'function' => 'required|string|max:100',
            'base_salary' => 'required|numeric|min:0',
            'hiring_date' => 'required|date',
        ]);

        $employe->update($request->all());

        return response()->json(['message' => 'Profil employé mis à jour avec succès!']);
    }


    // --- GESTION DE LA PAIE ---
    
    // Récupérer les données pour le formulaire de paie (employés + salaires de base)
    public function getPayrollData()
    {
        $employes = Employe::where('pressing_token', $this->getPressingToken())
                           ->select('token', 'full_name', 'base_salary')
                           ->get();

        return response()->json(['employes' => $employes]);
    }

    // Sauvegarder les paies (création Paie + ItemPaies)
    public function storePaie(Request $request)
    {
        // 1. Validation de l'ensemble
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:' . (date('Y') - 5) . '|max:' . (date('Y') + 1),
            'items' => 'required|array|min:1',
            'items.*.employe_token' => 'required|uuid|exists:employes,token',
            'items.*.base_salary' => 'required|numeric|min:0',
            // ... autres validations pour les montants
        ]);
        
        $pressingToken = $this->getPressingToken();
        $userToken = Auth::user()->token;

        // Vérification de l'unicité (paie déjà existante pour ce mois/année)
        // if (Paie::where('pressing_token', $pressingToken)
        //         ->where('month', $request->month)
        //         ->where('year', $request->year)
        //         ->exists()) {
        //     return response()->json(['message' => 'La paie pour ce mois et cette année a déjà été enregistrée.'], 409);
        // }

        // 2. Calcul des totaux
        $totals = $this->calculatePaieTotals($request->items);

        // 3. Création de l'en-tête de la Paie
        $paie = Paie::create(array_merge($totals, [
            'token' => Str::uuid(),
            'month' => $request->month,
            'year' => $request->year,
            'pressing_token' => $pressingToken,
            'user_token' => $userToken,
            'status' => 'pending',
        ]));
        
        // 4. Création des ItemPaies
        $itemsToInsert = [];
        foreach ($request->items as $item) {
            $netPaid = $item['base_salary'] + $item['advantages'] + $item['prime'] - $item['fiscal_retention'] - $item['social_retention'] - $item['exceptional_retention'];
            
            $itemsToInsert[] = [
                'token' => Str::uuid(),
                'paie_token' => $paie->token,
                'employe_token' => $item['employe_token'],
                'base_salary' => $item['base_salary'],
                'advantages' => $item['advantages'] ?? 0,
                'prime' => $item['prime'] ?? 0,
                'fiscal_retention' => $item['fiscal_retention'] ?? 0,
                'social_retention' => $item['social_retention'] ?? 0,
                'patronal_contribution' => $item['patronal_contribution'] ?? 0,
                'fiscal_charge' => $item['fiscal_charge'] ?? 0,
                'exceptional_retention' => $item['exceptional_retention'] ?? 0,
                'net_paid' => $netPaid,
                'pressing_token' => $pressingToken,
                'user_token' => $userToken,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        ItemPaie::insert($itemsToInsert);

        return response()->json(['message' => 'Paie du ' . $request->month . '/' . $request->year . ' enregistrée avec succès!', 'paie' => $paie]);
    }
    
    // Fonction helper pour calculer les totaux
    private function calculatePaieTotals(array $items): array
    {
        $totals = [
            'total_base_salary' => 0,
            'total_advantages' => 0,
            'total_primes' => 0,
            'total_fiscal_retentions' => 0,
            'total_social_retentions' => 0,
            'total_patronal_contributions' => 0,
            'total_fiscal_charges' => 0,
            'total_exceptional_retention' => 0,
            'net_to_pay' => 0,
        ];
        
        foreach ($items as $item) {
            $totals['total_base_salary'] += $item['base_salary'];
            $totals['total_advantages'] += $item['advantages'] ?? 0;
            $totals['total_primes'] += $item['prime'] ?? 0;
            $totals['total_fiscal_retentions'] += $item['fiscal_retention'] ?? 0;
            $totals['total_social_retentions'] += $item['social_retention'] ?? 0;
            $totals['total_patronal_contributions'] += $item['patronal_contribution'] ?? 0;
            $totals['total_fiscal_charges'] += $item['fiscal_charge'] ?? 0;
            $totals['total_exceptional_retention'] += $item['exceptional_retention'] ?? 0;

            // Calcul Net à Payer : SalaireBase + Avantages + Prime - Ret.Fiscale - Ret.Sociale - Ret.Exceptionnelle
            $netPaid = $item['base_salary'] + ($item['advantages'] ?? 0) + ($item['prime'] ?? 0)
                     - ($item['fiscal_retention'] ?? 0) - ($item['social_retention'] ?? 0) 
                     - ($item['exceptional_retention'] ?? 0);
            
            $totals['net_to_pay'] += $netPaid;
        }

        return $totals;
    }


    // --- PAIEMENT DE LA PAIE ---

    // Récupérer les totaux d'une paie impayée pour l'interface de paiement
    public function getUnpaidPaie(Paie $paie)
    {
        // if ($paie->status !== 'pending' || $paie->pressing_token !== $this->getPressingToken()) {
        //      return response()->json(['message' => 'Paie introuvable ou déjà payée.'], 404);
        // }
        return response()->json($paie);
    }

    // Enregistrer le paiement de la paie dans Depense et mettre à jour Paie
    public function payPaie(Request $request)
    {
        $request->validate([
            'paie_token' => 'required|uuid|exists:paies,token',
            'description' => 'required|string|max:255',
            'payment_mode' => 'required|string|max:50',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0', // Le montant net à payer (vérifié côté client/serveur)
        ]);

        $paie = Paie::where('token', $request->paie_token)
                      ->where('status', 'pending')
                      ->where('pressing_token', $this->getPressingToken())
                      ->firstOrFail();

        // Vérification de la cohérence du montant (facultatif mais recommandé)
        if (round($request->amount, 2) !== round($paie->net_to_pay, 2)) {
             return response()->json(['message' => 'Le montant de paiement ne correspond pas au net à payer calculé.'], 422);
        }

        // Enregistrement de la dépense
        Depense::create([
            'token' => Str::uuid(),
            'description' => $request->description,
            'amount' => $request->amount,
            'payment_mode' => $request->payment_mode,
            'transaction_date' => $request->transaction_date,
            'pressing_token' => $this->getPressingToken(),
            'user_token' => Auth::user()->token,
            // Optionnel: 'paie_token' si vous voulez le lier dans la table depenses
        ]);

        // Mise à jour du statut de la paie
        $paie->update(['status' => 'paid']);

        return response()->json(['message' => 'Paie du ' . $paie->month . '/' . $paie->year . ' réglée avec succès!']);
    }
    //--- afficher la vue bulletin ---
    public function selectBulletin(Employe $employe)
    {
        // Vous pouvez optionnellement passer la liste des mois/années disponibles
        // pour cet employé ici, mais pour simplifier, on passe juste l'employé.
        
        return view('manager.payroll.select_bulletin', compact('employe'));
    }

    // App/Http/Controllers/Manager/PayrollController.php

    /**
     * Retourne le HTML brut du bulletin de paie pour l'aperçu AJAX.
     */
    public function previewBulletin(Request $request, Employe $employe)
    {
        // 1. Validation (rapide, car c'est une requête AJAX)
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
        ]);

        $month = $request->month;
        $year = $request->year;

        try {
            // 2. Récupération de l'entête de paie (PaieHeader)
            $paieHeader = Paie::where('pressing_token', $this->getPressingToken())
                                ->where('month', $month)
                                ->where('year', $year)
                                ->firstOrFail();

            // 3. Récupération de l'élément de paie spécifique à l'employé (ItemPaie)
            $itemPaie = ItemPaie::where('paie_token', $paieHeader->token)
                                ->where('employe_token', $employe->token)
                                ->firstOrFail();

            // 4. Retourner le contenu HTML de la vue Blade du bulletin
            $htmlContent = view('manager.payroll.bulletin', compact('employe', 'paieHeader', 'itemPaie'))->render();

            return response()->json([
                'html' => $htmlContent
            ]);

        } catch (ModelNotFoundException $e) {
            // Retourner une erreur JSON si le bulletin n'existe pas
            return response()->json(['message' => "Aucune paie trouvée pour le mois $month de l'année $year."], 404);
        }
    }

    // --- Bulletin de Paie PDF (À implémenter) ---
    public function generateBulletin(Request $request, Employe $employe)
    {

    //     // 1. Validation des paramètres requis (mois et année) via l'URL (GET)
        $request->validate([
            'month' => 'required|integer|between:1,12', 
            'year' => 'required|integer|min:2000', 
        ]);

        $month = $request->month;
        $year = $request->year;

        try {
            // 2. Récupération de l'entête de paie (PaieHeader)
            $paieHeader = Paie::where('pressing_token', $this->getPressingToken())
                                ->where('month', $month)
                                ->where('year', $year)
                                ->firstOrFail();

            // 3. Récupération de l'élément de paie spécifique à l'employé (ItemPaie)
            $itemPaie = ItemPaie::where('paie_token', $paieHeader->token)
                                ->where('employe_token', $employe->token)
                                ->firstOrFail();

            // 4. Préparation et Génération du PDF avec DomPDF
            $fileName = 'Bulletin_Paie_' . 
                        $employe->full_name . '_' . 
                        $month . '-' . 
                        $year . '.pdf';

            $data = compact('employe', 'paieHeader', 'itemPaie');

            $pdf = Pdf::loadView('manager.payroll.bulletin', $data);

            // Diffuser le PDF dans un nouvel onglet
            return $pdf->stream($fileName);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Si la paie n'est pas trouvée (ni PaieHeader, ni ItemPaie)
            return redirect()->back()->with('error', "Aucun bulletin de paie n'a été trouvé pour la période sélectionnée ({$month}/{$year}).");
        }
    }
}