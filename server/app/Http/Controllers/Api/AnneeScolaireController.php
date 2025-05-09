<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AnneeScolaire;
use Illuminate\Support\Facades\Validator;

class AnneeScolaireController extends Controller
{
    /**
     * Afficher toutes les années scolaires
     */
    public function index()
    {
        $annees = AnneeScolaire::orderBy('annee_scolaire', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $annees
        ]);
    }

    /**
     * Ajouter une nouvelle année scolaire
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'annee_scolaire' => 'required|string|regex:/^\d{4}-\d{4}$/|unique:annee_scolaire,annee_scolaire',
            'est_courante' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Si l'année est marquée comme courante, désactiver les autres
        if ($request->est_courante) {
            AnneeScolaire::where('est_courante', true)->update(['est_courante' => false]);
        }

        $annee = AnneeScolaire::create([
            'annee_scolaire' => $request->annee_scolaire,
            'est_courante' => $request->est_courante ?? false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Année scolaire ajoutée avec succès',
            'data' => $annee
        ], 201);
    }

    /**
     * Mettre à jour une année scolaire
     */
    public function update(Request $request, $id)
    {
        $annee = AnneeScolaire::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'annee_scolaire' => 'string|regex:/^\d{4}-\d{4}$/|unique:annee_scolaire,annee_scolaire,' . $id . ',id_annee',
            'est_courante' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Si l'année est marquée comme courante, désactiver les autres
        if ($request->has('est_courante') && $request->est_courante) {
            AnneeScolaire::where('est_courante', true)
                        ->where('id_annee', '!=', $id)
                        ->update(['est_courante' => false]);
        }

        $annee->update($request->only(['annee_scolaire', 'est_courante']));

        return response()->json([
            'success' => true,
            'message' => 'Année scolaire mise à jour avec succès',
            'data' => $annee
        ]);
    }

    /**
     * Supprimer une année scolaire
     */
    public function destroy($id)
    {
        $annee = AnneeScolaire::findOrFail($id);

        // Vérifier si l'année est courante
        if ($annee->est_courante) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer l\'année scolaire courante'
            ], 422);
        }

        $annee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Année scolaire supprimée avec succès'
        ]);
    }

    /**
     * Définir une année scolaire comme courante
     */
    public function setCourante($id)
    {
        $annee = AnneeScolaire::findOrFail($id);

        // Désactiver toutes les autres années
        AnneeScolaire::where('est_courante', true)
                    ->where('id_annee', '!=', $id)
                    ->update(['est_courante' => false]);

        // Activer l'année sélectionnée
        $annee->update(['est_courante' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Année scolaire définie comme courante avec succès',
            'data' => $annee
        ]);
    }
} 