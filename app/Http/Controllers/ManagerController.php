<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManagerController extends Controller
{
    /**
     * Affiche la vue de gestion des utilisateurs.
     */
    public function index()
    {
        return view('manager.gestionnaire');
    }

    /**
     * Récupère les utilisateurs pour le tableau AJAX (avec pagination).
     */
    public function getUsers(Request $request)
    {
        // Récupère le token_pressing de l'administrateur connecté
        $adminTokenPressing = Auth::User()->pressing_token;
        $perPage = 5; // Défini dans le JS, doit correspondre

        $users = User::where('pressing_token', $adminTokenPressing)
                     // N'affiche pas l'administrateur lui-même dans la liste à éditer
                     ->where('token', '!=', Auth::User()->token) 
                     ->paginate($perPage);

        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * Crée un nouvel utilisateur (Employé).
     */
    public function storeUser(Request $request)
    {
        // Récupère le token_pressing de l'administrateur connecté
        $adminTokenPressing = Auth::User()->pressing_token;
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
        ], [
            'email.unique' => 'Cet email est déjà utilisé par un autre utilisateur.',
            'password.required' => 'Le mot de passe est obligatoire pour la création.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'employe', // Par défaut lors de l'ajout
            'status' => 'active',
            'pressing_token' => $adminTokenPressing,
        ]);

        return response()->json(['message' => 'Utilisateur créé avec succès.'], 201);
    }

    /**
     * Met à jour les informations d'un utilisateur.
     */
    public function updateUser(Request $request, User $user)
    {
        // 1. Vérification de sécurité (s'assurer que l'admin peut modifier cet utilisateur)
        if ($user->pressing_token !== Auth::User()->pressing_token) {
            return response()->json(['message' => "Accès non autorisé à cet utilisateur."], 403);
        }

        // 2. Validation
        $request->validate([
            'name' => 'required|string|max:255',
            // Unique sauf pour l'utilisateur en cours de modification
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->token, 'token')],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->token, 'token')],
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,employe',
            'status' => 'required|in:active,inactive',
        ]);

        // 3. Mise à jour des données
        // $employe = User::find($user->token);
        // dd($employe);
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['message' => 'Utilisateur mis à jour avec succès.'], 200);
    }


    public function getPressingsApi(Request $request)
    {
        $query = \App\Models\Pressing::query();

        // Filtre par plan si présent
        if ($request->plan) {
            $query->where('subscription_plan', $request->plan);
        }

        // Pagination de 5 éléments par page
        $pressings = $query->latest()->paginate(5);

        return response()->json([
            'data' => $pressings->items(),
            'current_page' => $pressings->currentPage(),
            'last_page' => $pressings->lastPage(),
            'total' => \App\Models\Pressing::count(),
            'active_count' => \App\Models\Pressing::where('subscription_plan', 'basic')->count(),
            'inactive_count' => \App\Models\Pressing::where('subscription_plan', 'inactive')->count(),
        ]);
    }

    public function updatePressingApi(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'plan' => 'required|in:basic,inactive',
        ]);

        $pressing = \App\Models\Pressing::where('token', $request->token)->firstOrFail();

        if ($request->plan === 'inactive') {
            $pressing->update(['subscription_plan' => 'inactive']);
            // Désactivation des utilisateurs liés
            \App\Models\User::where('pressing_token', $request->token)->update(['status' => 'inactive']);
        } else {
            $startDate = \Carbon\Carbon::parse($request->last_subscription_at);
            $expiryDate = $startDate->copy()->addMonths((int)$request->duration);

            $pressing->update([
                'subscription_plan' => 'basic',
                'last_subscription_at' => $startDate,
                'subscription_expires_at' => $expiryDate
            ]);
            // Réactivation des utilisateurs liés
            \App\Models\User::where('pressing_token', $request->token)->update(['status' => 'active']);
        }

        return response()->json(['success' => true]);
    }


    /**
     * Récupère la liste des utilisateurs avec pagination et filtrage
     */
    public function getUsersApi(Request $request)
    {
        $users = User::with('pressing')
            ->where('role', '!=', 'manager') 
            ->when($request->filled('name'), function ($query) use ($request) {
                $searchTerm = '%' . $request->name . '%';
                // On cherche par nom OU par email
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm);
                });
            })
            ->latest()
            ->paginate(5);

        return response()->json($users);
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur spécifique
     */
    public function resetUserPasswordApi(Request $request)
    {
        $request->validate([
            'user_token'  => 'required|exists:users,token',
            'password' => 'required|string|min:6',
        ]);

        try {
            $user = User::findOrFail($request->user_token);
            
            $user->update([
                'password' => Hash::make($request->password),
                'update_password' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour.'
            ], 500);
        }
    }
}