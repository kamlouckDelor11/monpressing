<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class PasswordUpdateController extends Controller
{
    public function showUpdateForm() {
        return view('auth.force-password-update');
    }

    public function updatePassword(Request $request) {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:4|confirmed',
        ]);

        $user =  User::find(Auth::user()->token);

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['errors' => ['old_password' => ['L\'ancien code est incorrect.']]], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'update_password' => null
        ]);

        return response()->json(['message' => 'Code de connexion mis à jour avec succès ! Redirection...']);
    }
}


