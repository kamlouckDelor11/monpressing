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
}