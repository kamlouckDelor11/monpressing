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

class OrderController extends Controller
{
    public function index()
    {
        return view('add-order');
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
            'payment_method' => 'required|string',
            'paid_amount' => 'required|numeric|min:0',
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
                'payment_method' => $request->payment_method,
                'paid_amount' => $request->paid_amount,
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
}