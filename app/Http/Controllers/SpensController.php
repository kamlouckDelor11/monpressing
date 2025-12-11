<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Spens; // Catégories de dépense
use App\Models\ItemSpens; // Transactions de dépense
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class SpensController extends Controller
{
    /**
     * Helper pour obtenir le token du pressing de l'utilisateur.
     * @return string
     */
    private function getPressingToken()
    {
        // Assurez-vous que votre modèle User a bien ce champ
        return Auth::user()->pressing_token;
    }

    /**
     * Affiche l'interface de gestion des dépenses.
     */
    public function index()
    {
        return view('manager.spenses.index');
    }

    // --- 1. GESTION DES CATÉGORIES (CRUD) ---

    /**
     * Stocke une nouvelle catégorie de dépense.
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'nature' => ['required', 'string', Rule::in(['fixed', 'variable'])],
            'default_amount' => 'nullable|numeric|min:0',
        ]);

        Spens::create([
            // 'token' => Str::uuid(),
            'user_token' => Auth::user()->token,
            'pressing_token' => $this->getPressingToken(),
            'description' => $request->description,
            'nature' => $request->nature,
            'default_amount' => $request->nature === 'fixed' ? (float)$request->default_amount : null,
        ]);

        return response()->json(['message' => 'Catégorie créée avec succès.'], 201);
    }

    /**
     * Met à jour une catégorie existante.
     */
    public function updateCategory(Request $request, Spens $spens)
    {
        // Vérification que la catégorie appartient bien au pressing de l'utilisateur
        if ($spens->pressing_token !== $this->getPressingToken()) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $request->validate([
            'description' => 'required|string|max:255',
            'nature' => ['required', 'string', Rule::in(['fixed', 'variable'])],
            'default_amount' => 'nullable|numeric|min:0',
        ]);

        $spens->update([
            'description' => $request->description,
            'nature' => $request->nature,
            'default_amount' => $request->nature === 'fixed' ? (float)$request->default_amount : null,
        ]);

        return response()->json(['message' => 'Catégorie mise à jour avec succès.'], 200);
    }

    /**
     * Récupère les données des catégories pour affichage paginé (API pour la vue).
     */
    public function getCategoriesData(Request $request)
    {
        // 1. Initialisation de la requête de base
        $query = Spens::where('pressing_token', $this->getPressingToken())
                    ->orderBy('description', 'asc');

        // 2. Application du filtre de recherche (pour l'interface de gestion)
        if ($request->search) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // 💡 AJOUT : Vérification du paramètre 'all' pour éviter la pagination
        if ($request->has('all') && $request->boolean('all')) {
            // Retourne TOUTES les catégories sans pagination (pour le sélecteur JS)
            return $query->get(['token', 'description', 'nature', 'default_amount']);
        }

        // 3. Pagination par défaut (pour l'interface de gestion Catégories)
        return $query->paginate(10);
    }

    // --- 2. GESTION DE LA COMPTABILISATION (Panier) ---

    /**
     * Traite le panier de dépenses et crée les documents ItemSpens.
     */
    public function comptabiliserDepense(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.category_token' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.date' => 'required|date',
            'payment_method' => 'required|string',
        ]);

        $pressingToken = $this->getPressingToken();
        $userToken = Auth::user()->token;
        $paymentMethod = $request->payment_method;

        $itemSpensData = [];
        // $uniqueDepensesToken = Str::uuid(); // Token parent si on voulait une table Depenses (panier)

        // Préparation des données pour l'insertion en masse dans item_spens
        foreach ($request->items as $item) {
            $itemSpensData[] = [
                'token' => Str::uuid(),
                'spens_token' => $item['category_token'],
                'pressing_token' => $pressingToken,
                'user_token' => $userToken,
                'description' => $item['description'] ?? 'Dépense comptabilisée.',
                'amount_spens' => (float)$item['amount'],
                'date_spens' => $item['date'],
                'payment_mode' => $paymentMethod, // Utiliser le mode de paiement du formulaire global
                'status' => 'validated', // Statut initial
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insertion en masse
        if (!empty($itemSpensData)) {
            ItemSpens::insert($itemSpensData);
        } else {
            return response()->json(['message' => 'Aucun article valide dans le panier.'], 400);
        }

        return response()->json(['message' => 'Dépenses comptabilisées et validées avec succès.'], 201);
    }

    // --- 3. GESTION DES HISTORIQUES ET VALIDATION/ANNULATION ---

    /**
     * Récupère l'historique des transactions pour une catégorie donnée.
     */
    public function getTransactionsHistory(Spens $spens, Request $request)
    {
        // Vérification de la propriété
        if ($spens->pressing_token !== $this->getPressingToken()) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $query = ItemSpens::where('spens_token', $spens->token)
                          ->orderBy('date_spens', 'desc')
                          ->orderBy('created_at', 'desc');

        // Filtres par date
        if ($request->start_date) {
            $query->where('date_spens', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('date_spens', '<=', $request->end_date);
        }

        // Pour la première fois, on affiche seulement les 5 derniers, sinon on pagine.
        if (!$request->start_date && !$request->end_date && $request->page < 2) {
            return $query->take(5)->get();
        }
        
        // Pagination si les filtres sont utilisés ou si on demande la page suivante
        return $query->paginate(10)->items();
    }

    /**
     * Annule (ou valide) une transaction spécifique.
     */
    public function cancelTransaction(Request $request)
    {
        $request->validate([
            'item_token' => 'required|string|exists:item_spens,token',
        ]);

        $itemSpens = ItemSpens::where('token', $request->item_token)
                              ->where('pressing_token', $this->getPressingToken())
                              ->first();

        if (!$itemSpens) {
            return response()->json(['message' => 'Transaction introuvable ou accès refusé.'], 404);
        }

        // Toggle du statut
        $newStatus = $itemSpens->status === 'validated' ? 'canceled' : 'validated';
        $itemSpens->status = $newStatus;
        $itemSpens->save();

        $action = $newStatus === 'canceled' ? 'annulée' : 'validée';
        return response()->json(['message' => "Transaction de dépense {$action} avec succès."], 200);
    }
}