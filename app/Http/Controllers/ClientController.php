<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Affiche la liste des clients.
     */
    public function index(Request $request)
    {

        $pressingToken = Auth::user()->pressing_token;
        $query = Client::query()->where('pressing_token', $pressingToken);
        
        // Filtre par nom
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Filtre par numéro de téléphone
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->input('phone') . '%');
        }
        
        $clients = $query->get();

        // Si la requête est une requête AJAX, on renvoie une réponse JSON
        if ($request->ajax()) {
            return response()->json($clients);
        }

        // Sinon, on retourne la vue avec les données
        return view('client.client', ['clients' => $clients]);
    }

/**
     * Stocke un nouveau client.
     */
    public function store(Request $request)
    {
        // Validation des champs reçus de la requête
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation: ' . $validator->errors()->first()], 422);
        }

        // Récupération des données validées
        $validatedData = $validator->validated();

        // Récupération des tokens de l'utilisateur authentifié
        $userToken = Auth::user()->token;
        $pressingToken = Auth::user()->pressing_token;

        // **1. Vérification de l'existence d'un client avec ce numéro de téléphone**
        //    dans le même 'pressing_token'.
        $existingClient = Client::where('pressing_token', $pressingToken)
                                ->where('phone', $validatedData['phone'])
                                ->first();

        if ($existingClient) {
            // Un client avec ce numéro de téléphone existe déjà pour ce pressing.
            return response()->json([
                'message' => 'Un client avec ce numéro de téléphone (' . $validatedData['phone'] . ') existe déjà pour votre pressing.'
            ], 409); // Le code 409 Conflict est souvent approprié pour les problèmes de doublon.
        }

        // **2. Si le client n'existe pas, procéder à la création**
        
        // Ajout des tokens de l'utilisateur authentifié aux données
        $validatedData['user_token'] = $userToken;
        $validatedData['pressing_token'] = $pressingToken;

        // Création du client avec les données complétées
        Client::create($validatedData);

        return response()->json(['message' => 'Client ajouté avec succès.']);
    }


/**
     * Met à jour un client.
     */
    public function update(Request $request, Client $client)
    {
        // Validation des champs reçus de la requête
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation: ' . $validator->errors()->first()], 422);
        }

        // Récupération des données validées et des tokens
        $validatedData = $validator->validated();
        $pressingToken = Auth::user()->pressing_token; 

        // 1. Vérification de l'existence d'un autre client avec le nouveau numéro de téléphone
        //    dans le même 'pressing_token', en excluant le client actuel par son 'token' (sa clé primaire).
        $existingClient = Client::where('pressing_token', $pressingToken)
                                ->where('phone', $validatedData['phone'])
                                ->where('token', '!=', $client->token) // <-- Exclusion par le champ 'token'
                                ->first();

        if ($existingClient) {
            // Un autre client avec ce numéro de téléphone existe déjà pour ce pressing.
            return response()->json([
                'message' => 'Un autre client avec ce numéro de téléphone (' . $validatedData['phone'] . ') existe déjà pour votre pressing.'
            ], 409); // 409 Conflict pour les doublons
        }
    
        // 2. Si aucune duplication n'est trouvée, procéder à la mise à jour
        $client->update($validatedData);
    
        return response()->json(['message' => 'Client mis à jour avec succès.']);
    }

    /**
     * Supprime un client.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json(['message' => 'Client supprimé avec succès.']);
    }

    /**
     * Récupère l'historique des commandes d'un client.
     */
    public function getOrdersHistory(Client $client)
    {
        $pressingToken =Auth::User()->pressing_token;

        $orders = $client->orders()->select('reference', 'deposit_date', 'payment_status', 'delivery_status', 'total_amount')->where('pressing_token', $pressingToken)->get();

        return response()->json($orders);
    }
}


