<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * Affiche la liste des services avec les filtres.
     */
    public function index(Request $request)
    {
        $pressingToken = Auth::user()->pressing_token;
        $query = Service::query()->where('pressing_token', $pressingToken);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $services = $query->get();

        if ($request->ajax()) {
            return response()->json($services);
        }

         return view('service.service', ['articles' => $services ]);
    }

    /**
     * Stocke un nouveau service dans la base de données.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation: ' . $validator->errors()->first()], 422);
        }

        $validatedData = $validator->validated();
        $validatedData['user_token'] = Auth::user()->token;
        $validatedData['pressing_token'] = Auth::user()->pressing_token;

        Service::create($validatedData);

        return response()->json(['message' => 'Service ajouté avec succès.']);
    }

    /**
     * Met à jour un service existant.
     */
    public function update(Request $request, Service $service)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation: ' . $validator->errors()->first()], 422);
        }

        $service->update($validator->validated());

        return response()->json(['message' => 'Service mis à jour avec succès.']);
    }

    /**
     * Supprime un service.
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(['message' => 'Service supprimé avec succès.']);
    }
}