<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Goal;
use App\Models\User;
use App\Models\Order;
use App\Models\ItemSpens;
use App\Models\Paie;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticController extends Controller
{
    // Nombre d'objectifs par page
    const GOALS_PER_PAGE = 5;
    
    // Définition des types d'objectifs et de périodicités valides
    const GOAL_TYPES = ['deposits', 'revenue', 'deliveries', 'new_clients', 'charges'];
    const PERIODICITIES = ['monthly', 'quarterly', 'annual'];

    /**
     * Affiche la page des statistiques (Vue initiale seulement).
     */
    public function index(Request $request)
    {
        // 1. Définir le token du pressing
        $userPressingToken = Auth::user()->pressing_token;
        
        $selectedYear = (int) $request->input('year', date('Y'));

        // 2. Charger les employés
        $employees = User::where('pressing_token', $userPressingToken)
                         ->where('role', '!=', 'client')
                         ->get(['token', 'name']); 
        
        // 3. Charger les données initiales du graphique et des objectifs
        $chartData = $this->getChartData($userPressingToken, $selectedYear);
        $goalsData = $this->getGoalsData($userPressingToken, 1);
        
        return view('statistics.index', compact('employees', 'selectedYear', 'chartData', 'goalsData'));
    }

    /**
     * Gère les requêtes AJAX pour la pagination et la mise à jour des objectifs.
     * Retourne la vue partielle HTML de la table et des liens de pagination.
     */
    public function goalsTable(Request $request)
    {
        $userPressingToken = Auth::user()->pressing_token;
        $page = (int) $request->input('page', 1); 
        
        $goalsData = $this->getGoalsData($userPressingToken, $page);

        // Retourne la vue partielle (celle qui contient la table et la pagination)
        return response()->json([
            'html' => view('statistics.partials.goals_table_content', $goalsData)->render(),
        ]);
    }
    
    /**
     * NOUVELLE MÉTHODE : Charge le contenu HTML du formulaire d'édition pour la modale statique.
     * C'est la solution au problème de "Erreur lors du chargement du formulaire d'édition.".
     */
    public function getEditForm($token)
    {
        $userPressingToken = Auth::user()->pressing_token;
        
        // 1. Trouver l'objectif en utilisant le token et le pressing_token pour la sécurité
        $goal = Goal::where('pressing_token', $userPressingToken)
                    ->where('token', $token)
                    ->firstOrFail(); // Renvoie 404 si non trouvé ou si token pressing ne correspond pas

        // 2. Charger les employés pour le champ de sélection
        $employees = User::where('pressing_token', $userPressingToken)
                         ->where('role', '!=', 'client')
                         ->get(['token', 'name']); 

        // 3. Renvoyer le HTML de la vue partielle
        return response()->json([
            'html' => view('statistics.partials.edit_goal_modal', compact('goal', 'employees'))->render()
        ]);
    }

    /**
     * Fonction interne pour récupérer les objectifs et calculer la progression.
     */
    private function getGoalsData(string $userPressingToken, int $page): array
    {
        $goals = Goal::where('pressing_token', $userPressingToken)
                     ->with('user') // On utilise 'user' si c'est la relation par défaut (user_token => User)
                     ->orderBy('end_date', 'desc')
                     ->paginate(self::GOALS_PER_PAGE, ['*'], 'page', $page); 

        // Calculer la progression pour chaque objectif après la pagination
        $goals->getCollection()->transform(function ($goal) use ($userPressingToken) {
             // Assigner le résultat du calcul à la propriété dynamique 'progress'
            $progressData = $this->getGoalProgress($goal, $userPressingToken);
            $goal->current_value = $progressData['current_value'];
            $goal->percentage = $progressData['percentage'];
            
            // Logique pour les labels/couleurs (ajoutées pour que la vue partielle fonctionne)
            $this->applyGoalStatusLabels($goal);

            return $goal;
        });
        
        $employees = User::where('pressing_token', $userPressingToken)
                         ->where('role', '!=', 'client')
                         ->get(['token', 'name']); 

        return compact('goals', 'employees');
    }

    /**
     * Applique les labels de statut et les couleurs.
     */
    private function applyGoalStatusLabels(Goal $goal): void
    {
        $progress = $goal->percentage;
        $now = Carbon::now();

        if ($now->greaterThan($goal->end_date)) {
            $goal->status_label = ($progress >= 100) ? 'Atteint (Terminé)' : 'Échoué';
            $goal->status_color = ($progress >= 100) ? 'bg-success' : 'bg-secondary';
        } else {
            $goal->status_label = ($progress >= 100) ? 'Atteint' : 'En Cours';
            $goal->status_color = ($progress >= 100) ? 'bg-success' : 'bg-primary';
        }
        
        $goal->is_monetary = in_array($goal->type, ['revenue', 'charges']);
        
        $labels = [
            'deposits' => 'Dépôts',
            'revenue' => 'Chiffre Affaires',
            'deliveries' => 'Livraisons',
            'new_clients' => 'Nouveaux Clients',
            'charges' => 'Charges',
        ];
        $goal->type_label = $labels[$goal->type] ?? $goal->type;
    }


    /**
     * Stocke un nouvel objectif (Réponse JSON pour AJAX).
     */
    public function storeGoal(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(self::GOAL_TYPES)],
            'periodicity' => ['required', Rule::in(self::PERIODICITIES)],
            'target_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_token' => [
                'nullable', 
                Rule::exists('users', 'token')->where(function ($query) {
                    return $query->where('pressing_token', Auth::user()->pressing_token);
                }),
            ], 
        ]);

        Goal::create(array_merge($validated, [
            'pressing_token' => Auth::user()->pressing_token,
        ]));

        return response()->json(['success' => 'Objectif créé avec succès.']);
    }

    /**
     * Met à jour un objectif existant (Réponse JSON pour AJAX).
     * CORRECTION: Route Model Binding sur le token au lieu de l'ID par défaut.
     */
    public function updateGoal(Request $request, $token)
    {
        // On trouve l'objectif par token et on vérifie l'appartenance au pressing
        $goal = Goal::where('pressing_token', Auth::user()->pressing_token)
                    ->where('token', $token)
                    ->firstOrFail(); 

        $validated = $request->validate([
            // Type et Periodicity peuvent être dans la requête même s'ils sont désactivés dans le formulaire
            'type' => ['required', Rule::in(self::GOAL_TYPES)],
            'periodicity' => ['required', Rule::in(self::PERIODICITIES)],
            'target_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_token' => [
                'nullable', 
                Rule::exists('users', 'token')->where(function ($query) {
                    return $query->where('pressing_token', Auth::user()->pressing_token);
                }),
            ],
        ]);

        $goal->update($validated);

        return response()->json(['success' => 'Objectif mis à jour avec succès.']);
    }
    
    /**
     * Supprime un objectif existant (Réponse JSON pour AJAX).
     * CORRECTION: Route Model Binding sur le token au lieu de l'ID par défaut.
     */
    public function destroyGoal($token)
    {
        // On trouve l'objectif par token et on vérifie l'appartenance au pressing
        $goal = Goal::where('pressing_token', Auth::user()->pressing_token)
                    ->where('token', $token)
                    ->firstOrFail(); 

        $goal->delete();

        return response()->json(['success' => 'Objectif supprimé avec succès.']);
    }

    /**
     * Gère les requêtes AJAX pour le filtrage annuel des graphiques.
     * Retourne les données JSON.
     */
    public function chartData(Request $request)
    {
        $userPressingToken = Auth::user()->pressing_token;
        $selectedYear = (int) $request->input('year', date('Y'));
        
        $chartData = $this->getChartData($userPressingToken, $selectedYear);

        return response()->json($chartData);
    }

    /**
     * Calcule la valeur réelle réalisée pour un objectif donné.
     * @param Goal $goal
     * @param string $pressingToken
     * @return array {'current_value', 'percentage'}
     */
    private function getGoalProgress(Goal $goal, string $pressingToken): array
    {
        $start = $goal->start_date;
        $end = $goal->end_date->endOfDay();
        $currentValue = 0;
        $userToken = $goal->user_token;

        try {
            switch ($goal->type) {
                case 'deposits':
                    $query = Order::where('pressing_token', $pressingToken)
                        ->whereBetween('deposit_date', [$start, $end]);
                    if ($userToken) {
                        $query->where('user_token', $userToken); 
                    }
                    $currentValue = $query->count();
                    break;

                case 'revenue':
                    $query = Order::where('pressing_token', $pressingToken)
                        ->whereBetween('deposit_date', [$start, $end]);
                    if ($userToken) {
                        $query->where('user_token', $userToken);
                    }
                    $currentValue = $query->sum('total_amount') ?? 0;
                    break;

                case 'deliveries':
                    $query = Order::where('pressing_token', $pressingToken)
                        ->where('delivery_status', 'Livré')
                        ->whereBetween('delivery_date', [$start, $end]); 
                    if ($userToken) {
                        $query->where('user_token', $userToken);
                    }
                    $currentValue = $query->count();
                    break;

                case 'new_clients':
                    $query = DB::table('clients')
                        ->where('pressing_token', $pressingToken)
                        ->whereBetween('created_at', [$start, $end]);
                    $currentValue = $query->count();
                    break;

                case 'charges':
                    // Salaires et Charges Sociales
                    $salaries = Paie::where('pressing_token', $pressingToken)
                        ->where('status', 'paid')
                        ->whereBetween('created_at', [$start, $end])
                        ->sum(DB::raw('net_to_pay + total_fiscal_charges + total_patronal_contributions')) ?? 0;

                    // Autres Dépenses
                    $spenses = ItemSpens::where('pressing_token', $pressingToken)
                        ->where('status', 'validated')
                        ->whereBetween('created_at', [$start, $end])
                        ->sum('amount_spens') ?? 0;

                    $currentValue = $salaries + $spenses;
                    break;
                
                default:
                    $currentValue = 0;
            }
            
        } catch (\Exception $e) {
            logger()->error("Goal calculation failed for goal token: " . $goal->token . " Error: " . $e->getMessage());
            $currentValue = 0;
        }

        $percentage = ($goal->target_value > 0) 
            ? round(($currentValue / $goal->target_value) * 100, 1) 
            : ($currentValue > 0 ? 100 : 0); 
            
        // Pour les charges, la progression est l'atteinte de la cible (idéalement 0%) mais on le limite à 100% visuellement si la valeur cible est dépassée
        if ($goal->type === 'charges') {
            // Pour les charges (objectif de minimisation), si la valeur réelle > valeur cible, on considère que l'objectif est manqué ou atteint à 0%, mais ici on veut montrer la progression vers la cible.
            // Si l'objectif est de minimiser, la progression est 100% quand on atteint ou passe sous la cible. Si l'on reste au-dessus, on montre le rapport.
            $percentage = min($percentage, 100); 
        }

        return [
            'current_value' => $currentValue,
            'percentage' => $percentage, 
        ];
    }
    
    /**
     * Calcule les données mensuelles pour les graphiques (CA et Dépôts) pour une année donnée.
     */
    private function getChartData(string $pressingToken, int $year): array
    {
        $months = range(1, 12);
        $revenueData = array_fill_keys($months, 0);
        $depositData = array_fill_keys($months, 0);

        $orders = Order::where('pressing_token', $pressingToken)
            ->whereYear('deposit_date', $year)
            ->select(DB::raw('MONTH(deposit_date) as month'), DB::raw('SUM(total_amount) as total_revenue'), DB::raw('COUNT(*) as total_deposits'))
            ->groupBy(DB::raw('MONTH(deposit_date)'))
            ->orderBy('month')
            ->get();

        foreach ($orders as $order) {
            $revenueData[$order->month] = $order->total_revenue;
            $depositData[$order->month] = $order->total_deposits;
        }

        return [
            'labels' => ['Janv', 'Févr', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'],
            'revenue' => array_values($revenueData),
            'deposits' => array_values($depositData),
        ];
    }
}