<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\Parametre;

class ParametreController extends Controller
{
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe modifié avec succès'
        ]);
    }

    public function setAnneeActive(Request $request)
    {
        $request->validate([
            'annee_scolaire' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/']
        ]);

        // Stocker l'année active dans la session
        session(['annee_scolaire_active' => $request->annee_scolaire]);

        return response()->json([
            'success' => true,
            'message' => 'Année scolaire active mise à jour',
            'data' => [
                'annee_scolaire' => $request->annee_scolaire
            ]
        ]);
    }

    public function getAnneeActive()
    {
        $anneeActive = session('annee_scolaire_active', date('Y') . '-' . (date('Y') + 1));
        
        return response()->json([
            'success' => true,
            'data' => [
                'annee_scolaire' => $anneeActive
            ]
        ]);
    }

    public function getParametres()
    {
        $parametres = Parametre::all()->pluck('valeur', 'cle');
        
        return response()->json([
            'success' => true,
            'data' => $parametres
        ]);
    }
} 