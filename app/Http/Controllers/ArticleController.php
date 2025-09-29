<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    /**
     * Affiche la liste des articles avec les filtres.
     */
    public function index(Request $request)
    {
        $pressingToken = Auth::user()->pressing_token;
        $query = Article::query()->where('pressing_token', $pressingToken);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $articles = $query->get();

        if ($request->ajax()) {
            return response()->json($articles);
        }

        return view('article.article', ['articles' => $articles ]);
    }

    /**
     * Stocke un nouvel article dans la base de données.
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

        Article::create($validatedData);

        return response()->json(['message' => 'Article ajouté avec succès.']);
    }

    /**
     * Met à jour un article existant.
     */
    public function update(Request $request, Article $article)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation: ' . $validator->errors()->first()], 422);
        }

        $article->update($validator->validated());

        return response()->json(['message' => 'Article mis à jour avec succès.']);
    }

    /**
     * Supprime un article.
     */
    public function destroy(Article $article)
    {
        $article->delete();
        return response()->json(['message' => 'Article supprimé avec succès.']);
    }
}

