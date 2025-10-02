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

        // Ajout des tokens de l'utilisateur authentifié aux données
        $validatedData['user_token'] = Auth::user()->token;
        $validatedData['pressing_token'] = Auth::user()->pressing_token;

        // Création du client avec les données complétées
        Client::create($validatedData);

        return response()->json(['message' => 'Client ajouté avec succès.']);
    }


    /**
     * Met à jour un client.
     */
    public function update(Request $request, Client $client)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation: ' . $validator->errors()->first()], 422);
        }
    
        $client->update($validator->validated());
    
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


