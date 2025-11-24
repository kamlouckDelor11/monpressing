<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Article;
use App\Models\Service;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Models\Transaction; 
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index()
    {
        return view('add-order');
    }
    public function manager_order()
    {
        return view('depot.manager');
    }
    
    public function searchClientByPhone(Request $request)
    {
        $pressingToken = Auth::user()->pressing_token;
        $client = Client::where('pressing_token', $pressingToken)
                        ->where('phone', 'like', '%' . $request->input('phone') . '%')
                        ->first();

        if ($client) {
            return response()->json([
                'found' => true,
                'client' => [
                    'name' => $client->name,
                    'token' => $client->token
                ]
            ]);
        }

        return response()->json(['found' => false]);
    }
    
    public function searchArticleByName(Request $request)
    {
        $pressingToken = Auth::user()->pressing_token;
        $articles = Article::where('pressing_token', $pressingToken)
                          ->where('name', 'like', '%' . $request->input('name') . '%')
                          ->get();

        return response()->json($articles);
    }

    public function getServices()
    {
        $pressingToken = Auth::user()->pressing_token;
        $services = Service::where('pressing_token', $pressingToken)->get();
        return response()->json($services);
    }
    
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'client_token' => 'required|uuid|exists:clients,token',
            'type' => 'required|in:LAVOMATIC,PRESSING',
            'deposit_date' => 'required|date',
            'delivery_date' => 'required|date|after_or_equal:deposit_date',
            'total_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            // 'payment_method' => 'required|string',
            // 'paid_amount' => 'required|numeric|min:0',
            // Validation du tableau d'articles
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.item_type' => 'required|string|in:lavomatic,pressing_service',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
            'items.*.article_token' => 'nullable|string', // Rendre nullable
            'items.*.service_token' => 'nullable|string', // Rendre nullable
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pressingToken = Auth::User()->pressing_token;
        $userToken = Auth::User()->token;

        $countLavomatic = Order::where('pressing_token', $pressingToken)
        ->where('type','LAVOMATIC')
        ->count();     

        $countPressing = Order::where('pressing_token', $pressingToken)
        ->where('type','PRESSING')
        ->count();

        if ($request->type === 'LAVOMATIC') {
            $ref = 'LA00'. $countLavomatic + 1;
        } else {
            $ref = 'PR00'. $countPressing + 1;
        }


        try {
            DB::beginTransaction();

            $client = Client::where('token', $request->client_token)->firstOrFail();

            $order = Order::create([
                'pressing_token' => $pressingToken,
                'user_token' => $userToken,
                'client_token' => $client->token,
                'reference' => $ref,
                'type' => $request->type,
                'deposit_date' => $request->deposit_date,
                'delivery_date' => $request->delivery_date,
                'total_amount' => $request->total_amount,
                'discount_amount' => $request->discount_amount,
                // 'payment_method' => $request->payment_method,
                // 'paid_amount' => $request->paid_amount,
            ]);

            foreach ($request->items as $itemData) {
                $orderItem = new OrderItem([
                    'pressing_token' => $pressingToken,
                    'user_token' => $userToken,
                    'item_name' => $itemData['item_name'],
                    'item_type' => $itemData['item_type'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['total_price'],
                    'order_token' => $order->token,
                ]);

                if ($itemData['item_type'] === 'pressing_service') {
                    $article = Article::where('token', $itemData['article_token'])->first();
                    $service = Service::where('token', $itemData['service_token'])->first();
                    if ($article && $service) {
                        $orderItem->article_token = $article->token;
                        $orderItem->service_token = $service->token;
                    }
                }
                $orderItem->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Dépôt enregistré avec succès.',
                'order' => $order
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'enregistrement du dépôt.'. $e,
                'error' => $e->getMessage()
            ], 500);
        }
    } 
    

    /**
     * Récupère les références (token) des commandes pour les listes déroulantes des Modals.
     */
    public function getOrderReferences()
    {
        // 1. Commandes en attente (pour Gestion du Statut)
        $pendingOrders = Order::with('client:token,name')
            ->where('delivery_status', '!=', 'delivered')
            ->where('pressing_token', Auth::User()->pressing_token)
            // Ajout du 'token' pour le JS
            ->select('token', 'reference', 'client_token') 
            ->get()
            ->map(function ($order) {
                return [
                    'token' => $order->token, // Clé utilisée pour la valeur du select
                    'reference' => $order->reference, // Clé utilisée pour l'affichage
                    'client_name' => $order->client->name ?? 'Client Inconnu',
                ];
            });

        // 2. Commandes non intégralement payées (pour Encaissement)
        $nonFullyPaidOrders = Order::with('client:token,name')
            ->whereRaw('total_amount > paid_amount')
            ->where('pressing_token', Auth::User()->pressing_token)
            // Ajout du 'token' pour le JS
            ->select('token', 'reference', 'client_token', 'total_amount', 'paid_amount') 
            ->get()
            ->map(function ($order) {
                return [
                    'token' => $order->token, // Clé utilisée pour la valeur du select
                    'reference' => $order->reference, // Clé utilisée pour l'affichage
                    'client_name' => $order->client->name ?? 'Client Inconnu',
                    'total_amount' => $order->total_amount,
                    'paid_amount' => $order->paid_amount,
                ];
            });

        return response()->json([
            'pending_orders' => $pendingOrders,
            'non_fully_paid_orders' => $nonFullyPaidOrders,
        ]);
    }
    
    // --- 1. GESTION DU STATUT (Delivery Status) ---

    /**
     * Met à jour le statut de livraison d'un dépôt par son token.
     * Le token est passé via l'URL.
     */
    public function updateDeliveryStatus(Request $request, $token)
    {
        $request->validate([
            // Le token est passé par l'URL ($token), le status par le corps ($request->status)
            'status' => 'required|in:ready,delivered,cancelled',
        ]);
        
        // Recherche par 'token' au lieu de 'reference'
        $order = Order::where('token', $token)->firstOrFail(); 
        
        if ($order->delivery_status !== 'pending' && $order->delivery_status !== 'ready') {
             return response()->json(['message' => "Le statut de cette commande est déjà '{$order->delivery_status}' et ne peut pas être modifié."], 403);
        }

        $order->delivery_status = $request->status;
        $order->save();

        return response()->json(['message' => 'Le statut de la commande a été mis à jour avec succès.']);
    }

    // --- 2. ENCAISSEMENT ET GESTION DU STATUT DE PAIEMENT ---

    /**
     * Enregistre une transaction d'encaissement par le token de la commande.
     */
    public function cashIn(Request $request, $token)
    {
        
        $request->validate([
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card',
        ]);

        // Recherche par 'token'
        $order = Order::where('token', $token)->firstOrFail(); 
        $amountPaid = $request->amount_paid;

        // Vérification de la cohérence du montant encaissé
        $remainingAmount = $order->total_amount - $order->paid_amount;
        if ($amountPaid > $remainingAmount) {
            return response()->json(['message' => "Le montant encaissé dépasse le montant restant à payer ({$remainingAmount} €)."], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Sauvegarde dans la table 'transactions'
            Transaction::create([
                // Utilisation de la référence pour l'historique de transaction si nécessaire
                'order_token' => $order->token, // Ajout du token si la table transactions le supporte
                'amount' => $amountPaid,
                'description' => $order->reference, 
                'type' => 'encaissement', 
                'payment_method' => $request->payment_method,
                'payment_date' => Carbon::now(),
                'user_token' => Auth::User()->token, 
            ]);
            
            // 2. Mise à jour du champ 'paid_amount'
            $order->paid_amount += $amountPaid;
            
            // 3. Mise à jour du 'payment_status'
            if (abs($order->paid_amount - $order->total_amount) < 0.01) {
                $order->payment_status = 'paid';
            } elseif ($order->paid_amount > 0) {
                $order->payment_status = 'partially_paid';
            } else {
                 $order->payment_status = 'unpaid';
            }
            
            $order->save();
            
            DB::commit();

            return response()->json(['message' => 'Encaissement enregistré avec succès. Statut de paiement mis à jour.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur transactionnelle lors de l\'enregistrement de l\'encaissement.'], 500);
        }
    }


    /**
     * Récupère et filtre la liste des dépôts (orders) avec pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterOrder(Request $request)
    {
        // 1. Initialisation de la requête
        $query = Order::with(['client', 'user']) // Charge les relations pour afficher les noms
                    ->where('pressing_token', Auth::User()->pressing_token)
                      ->latest('deposit_date'); // Tri par défaut (le plus récent en premier)

        // 2. Application des Filtres Conditionnels
        
        // Filtre par Date de Début (start_date)
        if ($request->filled('start_date')) {
            $query->whereDate('deposit_date', '>=', $request->start_date);
        }

        // Filtre par Date de Fin (end_date)
        if ($request->filled('end_date')) {
            $query->whereDate('deposit_date', '<=', $request->end_date);
        }

        // Filtre par Statut (status: pending, ready, delivered, cancelled)
        if ($request->filled('status') && $request->status !== '') {
            $query->where('delivery_status', $request->status);
        }

        // 3. Exécution de la requête avec Pagination
        // On récupère 15 dépôts par page (vous pouvez ajuster ce nombre)
        $orders = $query->paginate(5);
        
        // 4. Formatage et Réponse
        // L'utilisation d'une Resource est recommandée pour nettoyer et formater la sortie JSON.
        // Si vous n'utilisez pas de Resource, vous pouvez commenter les lignes ci-dessous
        // et retourner $orders directement.
        
        // Exemple avec une Resource
        // $orders = OrderResource::collection($orders)->response()->getData(true);
        
        return response()->json([
            // La clé 'orders' ici correspond à celle que vous utilisez en JS à la ligne 485:
            // populateOrderTable(response.orders);
            'orders' => $orders,
            'message' => 'Dépôts chargés avec succès.',
        ]);
    }
}