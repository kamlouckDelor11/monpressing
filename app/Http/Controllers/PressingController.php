<?php

namespace App\Http\Controllers;

use App\Models\Pressing;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PressingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Validation manuelle avec Validator (plus flexible que ->validate)
        $validator = Validator::make($request->all(), [
            'pressing_name'    => 'required|string|max:255',
            'admin_name'       => 'required|string|max:255',
            'admin_email'      => 'required|email|unique:users,email',
            'admin_phone'      => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'admin_password'   => 'required|string|min:4|', 
            // ⚠️ `confirmed` attend un champ "admin_password_confirmation"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422); // 422 = Validation Error
        }

        try {
            $result = DB::transaction(function () use ($request) {
                // ✅ Création du pressing
                $pressing = Pressing::create([
                    'name'     => e($request->pressing_name),
                    'subscription_plan' => 'basic',
                ]);

                // ✅ Création de l'admin associé
                User::create([
                    'pressing_token' => $pressing->token,
                    'name'           => e($request->admin_name),
                    'email'          => e($request->admin_email),
                    'phone'          => e($request->admin_phone),
                    'password'       => Hash::make($request->admin_password),
                    'role'           => 'admin',
                    'status'         => 'active',
                ]);

                return $pressing;
            });

            return response()->json([
                'success' => true,
                'message' => "✅ Pressing et administrateur créés avec succès<a href='/login'> Cliquez ici pour vous connecter !</a>",
                'data'    => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Échec de la création',
                'error'   => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    
    }


    /**
     * Display the specified resource.
     */
    public function show(Pressing $pressing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pressing $pressing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pressing $pressing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pressing $pressing)
    {
        //
    }
}
