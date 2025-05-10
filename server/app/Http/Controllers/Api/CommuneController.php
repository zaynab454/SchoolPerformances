<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commune;
use App\Models\Etablissement;
use App\Models\Eleve;
use App\Models\ResultatEleve;
use App\Models\NiveauScolaire;
use App\Models\AnneeScolaire;


class CommuneController extends Controller
{


  

    /**
     * Get all communes for selection
     */
    public function getCommunes()
    {
        $communes = Commune::with('province')->get();
        return response()->json([
            'success' => true,
            'communes' => $communes
        ]);
    }

    public function statCommune($id_commune, $annee_scolaire = null)
    {
        // Get the active academic year if none is provided
        if (!$annee_scolaire) {
            $anneeScolaire = AnneeScolaire::where('est_courante', true)->first();
            if ($anneeScolaire) {
                $annee_scolaire = $anneeScolaire->annee_scolaire;
            }
        }

        // Récupérer la commune
        $commune = Commune::with('province')->findOrFail($id_commune);

        // Calculer les statistiques
        $nombreEleves = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
            ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
            ->where('etablissement.code_commune', $id_commune)
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
            })
            ->distinct()
            ->count('eleve.code_eleve');

        $moyenneGenerale = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
            ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
            ->where('etablissement.code_commune', $id_commune)
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
            })
            ->avg('resultat_eleve.MoyenSession') ?? 0;

        $totalResultats = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
            ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
            ->where('etablissement.code_commune', $id_commune)
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
            })
            ->count();

        $reussis = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
            ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
            ->where('etablissement.code_commune', $id_commune)
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
            })
            ->where('resultat_eleve.MoyenSession', '>=', 10)
            ->count();

        $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;
        $tauxEchec = 100 - $tauxReussite;

        // Calculer le rang de la commune dans sa province
        $rangCommune = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
            ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
            ->join('commune', 'etablissement.code_commune', '=', 'commune.cd_com')
            ->where('commune.id_province', $commune->id_province)
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
            })
            ->select('commune.cd_com as id_commune')
            ->selectRaw('AVG(resultat_eleve.MoyenSession) as moyenne')
            ->groupBy('commune.cd_com')
            ->orderByDesc('moyenne')
            ->get()
            ->search(function($item) use ($id_commune) {
                return $item->id_commune == $id_commune;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'statistiques' => [
                    'nombre_eleves' => $nombreEleves,
                    'moyenne_generale' => round($moyenneGenerale, 2),
                    'taux_reussite' => round($tauxReussite, 2),
                    'taux_echec' => round($tauxEchec, 2),
                    'rang_province' => $rangCommune !== false ? $rangCommune + 1 : null
                ]
            ]
        ]);
    }

    public function getClassementEtablissements($id_commune, $annee_scolaire)
    {
        return ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
            ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
            ->where('etablissement.code_commune', $id_commune)
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
            })
            ->select('etablissement.code_etab as id_etablissement')
            ->select('etablissement.nom_etab_fr as nom_etablissement')
            ->selectRaw('COUNT(DISTINCT eleve.code_eleve) as nombre_eleves')
            ->selectRaw('AVG(resultat_eleve.MoyenSession) as moyenne_generale')
            ->selectRaw('COUNT(CASE WHEN resultat_eleve.MoyenSession >= 10 THEN 1 END) * 100.0 / COUNT(*) as taux_reussite')
            ->groupBy('etablissement.code_etab', 'etablissement.nom_etab_fr')
            ->orderByDesc('moyenne_generale')
            ->get()
            ->map(function($etablissement, $index) {
                return [
                    'rang' => $index + 1,
                    'id' => $etablissement->id_etablissement,
                    'nom' => $etablissement->nom_etablissement,
                    'nombre_eleves' => $etablissement->nombre_eleves,
                    'moyenne_generale' => round($etablissement->moyenne_generale, 2),
                    'taux_reussite' => round($etablissement->taux_reussite, 2)
                ];
            });
    }

    public function evolutionCommune($id_commune)
    {
        // Récupérer la commune
        $commune = Commune::with(['province'])->findOrFail($id_commune);

        // Récupérer toutes les années scolaires disponibles
        $annees = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
            $query->where('code_commune', $id_commune);
        })
        ->select('annee_scolaire')
        ->distinct()
        ->orderBy('annee_scolaire')
        ->pluck('annee_scolaire');

        // Statistiques par année
        $evolution = $annees->map(function($annee) use ($id_commune) {
            // Nombre d'élèves
            $nombreEleves = Eleve::whereHas('etablissement', function($query) use ($id_commune) {
                $query->where('code_commune', $id_commune);
            })
            ->whereHas('resultats', function($query) use ($annee) {
                $query->where('annee_scolaire', $annee);
            })
            ->count();

            // Moyenne générale
            $moyenneGenerale = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
                $query->where('code_commune', $id_commune);
            })
            ->where('annee_scolaire', $annee)
            ->avg('MoyenSession');

            // Taux de réussite et d'échec
            $totalResultats = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
                $query->where('code_commune', $id_commune);
            })
            ->where('annee_scolaire', $annee)
            ->count();

            $reussis = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
                $query->where('code_commune', $id_commune);
            })
            ->where('annee_scolaire', $annee)
            ->where('MoyenSession', '>=', 10)
            ->count();

            $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;
            $tauxEchec = 100 - $tauxReussite;

            return [
                'annee_scolaire' => $annee,
                'nombre_eleves' => $nombreEleves,
                'moyenne_generale' => round($moyenneGenerale, 2),
                'taux_reussite' => round($tauxReussite, 2),
                'taux_echec' => round($tauxEchec, 2)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'commune' => [
                    'id' => $commune->cd_com,
                    'nom' => $commune->la_com,
                    'province' => $commune->province->nom_province
                ],
                'evolution' => $evolution
            ]
        ]);
    }

 

      /**
     * Get statistics by cycle for a specific commune
     */
    public function statsParCycle($id_commune, $annee_scolaire = null)
    {
        try {
            // Get the active academic year if none is provided
            if (!$annee_scolaire) {
                $anneeScolaire = AnneeScolaire::where('est_courante', true)->first();
                if ($anneeScolaire) {
                    $annee_scolaire = $anneeScolaire->annee_scolaire;
                }
            }

            // Get all cycles in the commune
            $cycles = Etablissement::where('code_commune', $id_commune)
                ->distinct('cycle')
                ->pluck('cycle');

            // Get statistics by cycle
            $resultats = $cycles->map(function($cycle) use ($id_commune, $annee_scolaire) {
                $nombreEleves = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
                    ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
                    ->where('etablissement.code_commune', $id_commune)
                    ->where('etablissement.cycle', $cycle)
                    ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                        $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
                    })
                    ->distinct()
                    ->count('eleve.code_eleve');

                $moyenneGenerale = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
                    ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
                    ->where('etablissement.code_commune', $id_commune)
                    ->where('etablissement.cycle', $cycle)
                    ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                        $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
                    })
                    ->avg('resultat_eleve.MoyenSession') ?? 0;

                $totalResultats = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
                    ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
                    ->where('etablissement.code_commune', $id_commune)
                    ->where('etablissement.cycle', $cycle)
                    ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                        $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
                    })
                    ->count();

                $reussis = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
                    ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
                    ->where('etablissement.code_commune', $id_commune)
                    ->where('etablissement.cycle', $cycle)
                    ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                        $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
                    })
                    ->where('resultat_eleve.MoyenSession', '>=', 10)
                    ->count();

                $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;

                return [
                    'cycle' => $cycle,
                    'nombre_eleves' => $nombreEleves,
                    'moyenne_generale' => round($moyenneGenerale, 2),
                    'taux_reussite' => round($tauxReussite, 2)
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $resultats->values()->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques par cycle',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

