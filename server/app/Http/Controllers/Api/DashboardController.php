<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commune;
use App\Models\Etablissement;
use App\Models\Eleve;
use App\Models\ResultatEleve;
use App\Models\NiveauScolaire;
use App\Models\Province;
use App\Models\AnneeScolaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    /**
     * Get statistics by province
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statsProvince(Request $request)
    {
        try {
            // Validation de l'année scolaire
            $validator = Validator::make($request->all(), [
                'annee_scolaire' => 'nullable|regex:/^\d{4}-\d{4}$/'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Récupérer l'année scolaire active si non spécifiée
            $annee_scolaire = $request->annee_scolaire ?? AnneeScolaire::where('est_courante', true)->value('annee_scolaire');

            if (!$annee_scolaire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune année scolaire active n\'est définie'
                ], 400);
            }

            // Récupérer la province unique avec ses communes
            $province = Province::with(['communes.etablissements.eleves.resultats' => function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            }])->first();

            if (!$province) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune province trouvée'
                ], 404);
            }

            $nombreCommunes = $province->communes->count();
            $nombreEtablissements = $province->communes->sum(function($commune) {
                return $commune->etablissements->count();
            });
            
            $totalEleves = 0;
            $totalResultats = 0;
            $totalReussis = 0;
            $sommeMoyennes = 0;

            foreach ($province->communes as $commune) {
                foreach ($commune->etablissements as $etablissement) {
                    foreach ($etablissement->eleves as $eleve) {
                        $resultats = $eleve->resultats;
                        if ($resultats->isNotEmpty()) {
                            $totalEleves++;
                            $totalResultats++;
                            $moyenne = $resultats->avg('MoyenSession');
                            $sommeMoyennes += $moyenne;
                            if ($moyenne >= 10) {
                                $totalReussis++;
                            }
                        }
                    }
                }
            }

            $moyenneGenerale = $totalResultats > 0 ? $sommeMoyennes / $totalResultats : 0;
            $tauxReussite = $totalResultats > 0 ? ($totalReussis / $totalResultats) * 100 : 0;
            $tauxEchec = $totalResultats > 0 ? (($totalResultats - $totalReussis) / $totalResultats) * 100 : 0;

            return response()->json([
                'success' => true,
                'message' => 'Statistiques de la province récupérées avec succès',
                'data' => [
                    'annee_scolaire' => $annee_scolaire,
                    'province' => [
                        'id_province' => $province->id_province,
                        'nom_province' => $province->nom_province,
                        'statistiques' => [
                            'nombre_communes' => $nombreCommunes,
                            'nombre_etablissements' => $nombreEtablissements,
                            'nombre_eleves' => $totalEleves,
                            'moyenne_generale' => round($moyenneGenerale, 2),
                            'taux_reussite' => round($tauxReussite, 2),
                            'taux_echec' => round($tauxEchec, 2)
                        ]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques de la province',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function evaluationMoyennesParProvince($anneeScolaire = null)
    {
        // Si aucune année n'est spécifiée, utiliser l'année courante
        if (!$anneeScolaire) {
            $anneeCourante = AnneeScolaire::where('est_courante', true)->first();
            if ($anneeCourante) {
                $anneeScolaire = $anneeCourante->annee_scolaire;
            }
        }

        $resultats = Province::with([
            'communes.etablissements.eleves.resultats' => function($query) use ($anneeScolaire) {
                if ($anneeScolaire) {
                    $query->where('annee_scolaire', $anneeScolaire);
                }
            }
        ])
        ->get()
        ->map(function($province) {
            $nombreEleves = 0;
            $sommeMoyennes = 0;
            $nombreMoyennes = 0;

            foreach ($province->communes as $commune) {
                foreach ($commune->etablissements as $etablissement) {
                    foreach ($etablissement->eleves as $eleve) {
                        foreach ($eleve->resultats as $resultat) {
                            $nombreEleves++;
                            $sommeMoyennes += $resultat->MoyenSession;
                            $nombreMoyennes++;
                        }
                    }
                }
            }

            $moyenneGenerale = $nombreMoyennes > 0 ? $sommeMoyennes / $nombreMoyennes : 0;

            return [
                'province' => $province->nom_province,
                'nombre_eleves' => $nombreEleves,
                'moyenne_generale' => round($moyenneGenerale, 2)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $resultats
        ]);
    }

    public function topEtablissementsParProvince($anneeScolaire = null)
    {
        // Si aucune année n'est spécifiée, utiliser l'année courante
        if (!$anneeScolaire) {
            $anneeCourante = AnneeScolaire::where('est_courante', true)->first();
            if ($anneeCourante) {
                $anneeScolaire = $anneeCourante->annee_scolaire;
            }
        }

        $resultats = Etablissement::with([
            'eleves.resultats' => function($query) use ($anneeScolaire) {
                if ($anneeScolaire) {
                    $query->where('annee_scolaire', $anneeScolaire);
                }
            }
        ])
        ->whereHas('eleves.resultats')
        ->get()
        ->map(function($etablissement) {
            $nombreEleves = 0;
            $sommeMoyennes = 0;
            $nombreMoyennes = 0;

            foreach ($etablissement->eleves as $eleve) {
                foreach ($eleve->resultats as $resultat) {
                    $nombreEleves++;
                    $sommeMoyennes += $resultat->MoyenSession;
                    $nombreMoyennes++;
                }
            }

            $moyenneGenerale = $nombreMoyennes > 0 ? $sommeMoyennes / $nombreMoyennes : 0;

            return [
                'nom_etablissement' => $etablissement->nom_etab_fr,
                'moyenne_generale' => round($moyenneGenerale, 2),
                'nombre_eleves' => $nombreEleves
            ];
        })
        ->sortByDesc('moyenne_generale')
        ->take(5);

        return response()->json([
            'success' => true,
            'data' => $resultats
        ]);
    }

    public function statsParCycle($anneeScolaire = null)
    {
        // Si aucune année n'est spécifiée, utiliser l'année courante
        if (!$anneeScolaire) {
            $anneeCourante = AnneeScolaire::where('est_courante', true)->first();
            if ($anneeCourante) {
                $anneeScolaire = $anneeCourante->annee_scolaire;
            }
        }

        $resultats = NiveauScolaire::with([
            'eleves.resultats' => function($query) use ($anneeScolaire) {
                if ($anneeScolaire) {
                    $query->where('annee_scolaire', $anneeScolaire);
                }
            }
        ])
        ->get()
        ->map(function($niveau) {
            $nombreEleves = 0;
            $sommeMoyennes = 0;
            $nombreMoyennes = 0;

            foreach ($niveau->eleves as $eleve) {
                foreach ($eleve->resultats as $resultat) {
                    $nombreEleves++;
                    $sommeMoyennes += $resultat->MoyenSession;
                    $nombreMoyennes++;
                }
            }

            $moyenneGenerale = $nombreMoyennes > 0 ? $sommeMoyennes / $nombreMoyennes : 0;

            return [
                'cycle' => $niveau->description,
                'nombre_eleves' => $nombreEleves,
                'moyenne_generale' => round($moyenneGenerale, 2)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $resultats
        ]);
    }

    public function comparaisonCommunesProvince($id_province, $anneeScolaire = null)
    {
        // Si aucune année n'est spécifiée, utiliser l'année courante
        if (!$anneeScolaire) {
            $anneeCourante = AnneeScolaire::where('est_courante', true)->first();
            if ($anneeCourante) {
                $anneeScolaire = $anneeCourante->annee_scolaire;
            }
        }

        // Récupérer la province
        $province = Province::with([
            'communes.etablissements.eleves.resultats' => function($query) use ($anneeScolaire) {
                if ($anneeScolaire) {
                    $query->where('annee_scolaire', $anneeScolaire);
                }
            }
        ])->findOrFail($id_province);

        // Récupérer les communes avec leurs statistiques
        $communes = $province->communes->map(function($commune) {
            $nombreEleves = 0;
            $sommeMoyennes = 0;
            $nombreMoyennes = 0;
            $nombreReussis = 0;

            foreach ($commune->etablissements as $etablissement) {
                foreach ($etablissement->eleves as $eleve) {
                    foreach ($eleve->resultats as $resultat) {
                        $nombreEleves++;
                        $sommeMoyennes += $resultat->MoyenSession;
                        $nombreMoyennes++;
                        if ($resultat->MoyenSession >= 10) {
                            $nombreReussis++;
                        }
                    }
                }
            }

            $moyenneGenerale = $nombreMoyennes > 0 ? $sommeMoyennes / $nombreMoyennes : 0;
            $tauxReussite = $nombreEleves > 0 ? ($nombreReussis / $nombreEleves) * 100 : 0;

            return [
                'commune' => $commune->nom_commune,
                'nombre_eleves' => $nombreEleves,
                'moyenne_generale' => round($moyenneGenerale, 2),
                'taux_reussite' => round($tauxReussite, 2),
                'rang' => null
            ];
        });

        // Trier les communes par moyenne générale (descendant)
        $communes = $communes->sortByDesc('moyenne_generale');

        // Ajouter le rang à chaque commune
        $communes = $communes->map(function($commune, $index) {
            $commune['rang'] = $index + 1;
            return $commune;
        });

        // Calculer la moyenne générale de la province
        $moyenneProvince = $communes->avg('moyenne_generale');

        return response()->json([
            'success' => true,
            'data' => [
                'province' => $province->nom_province,
                'moyenne_generale_province' => round($moyenneProvince, 2),
                'communes' => $communes
            ]
        ]);
    }

    /**
     * Get all academic years for filtering
     */
    public function getAnneesScolaires()
    {
        $annees = AnneeScolaire::orderBy('annee_scolaire', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $annees
        ]);
    }

    /**
     * Get all communes for filtering
     */
    public function getCommunes()
    {
        $communes = Commune::orderBy('ll_com', 'asc')->get();
        return response()->json([
            'success' => true,
            'data' => $communes
        ]);
    }

    /**
     * Get establishments by commune
     */
    public function getEtablissementsByCommune(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code_commune' => 'required|exists:commune,cd_com'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $etablissements = Etablissement::where('code_commune', $request->code_commune)
            ->orderBy('nom_etab_fr', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $etablissements
        ]);
    }

    /**
     * Get statistics by filters
     */
    public function getStatsByFilters(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'annee_scolaire' => 'required|exists:annee_scolaire,annee_scolaire',
            'code_commune' => 'nullable|exists:commune,cd_com',
            'code_etab' => 'nullable|exists:etablissement,code_etab'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = ResultatEleve::query()
            ->join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
            ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
            ->join('commune', 'etablissement.code_commune', '=', 'commune.cd_com')
            ->where('resultat_eleve.annee_scolaire', $request->annee_scolaire);

        // Filtrer par commune si spécifiée
        if ($request->has('code_commune')) {
            $query->where('commune.cd_com', $request->code_commune);
        }

        // Filtrer par établissement si spécifié
        if ($request->has('code_etab')) {
            $query->where('etablissement.code_etab', $request->code_etab);
        }

        // Calculer les statistiques
        $stats = $query->select(
            DB::raw('COUNT(DISTINCT eleve.code_eleve) as total_eleves'),
            DB::raw('AVG(resultat_eleve.MoyenSession) as moyenne_generale'),
            DB::raw('COUNT(DISTINCT CASE WHEN resultat_eleve.MoyenSession >= 10 THEN eleve.code_eleve END) as eleves_admis'),
            DB::raw('COUNT(DISTINCT etablissement.code_etab) as total_etablissements')
        )->first();

        // Calculer le taux de réussite
        $stats->taux_reussite = $stats->total_eleves > 0 
            ? round(($stats->eleves_admis / $stats->total_eleves) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'statistiques' => $stats,
                'filtres' => [
                    'annee_scolaire' => $request->annee_scolaire,
                    'commune' => $request->code_commune,
                    'etablissement' => $request->code_etab
                ]
            ]
        ]);
    }

    /**
     * Get detailed statistics by establishment
     */
    public function getStatsByEtablissement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'annee_scolaire' => 'required|exists:annee_scolaire,annee_scolaire',
            'code_etab' => 'required|exists:etablissement,code_etab'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $stats = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
            ->where('eleve.code_etab', $request->code_etab)
            ->where('resultat_eleve.annee_scolaire', $request->annee_scolaire)
            ->select(
                DB::raw('COUNT(DISTINCT eleve.code_eleve) as total_eleves'),
                DB::raw('AVG(resultat_eleve.MoyenSession) as moyenne_generale'),
                DB::raw('COUNT(DISTINCT CASE WHEN resultat_eleve.MoyenSession >= 10 THEN eleve.code_eleve END) as eleves_admis'),
                DB::raw('COUNT(DISTINCT CASE WHEN resultat_eleve.MoyenSession < 10 THEN eleve.code_eleve END) as eleves_echoues')
            )->first();

        // Calculer le taux de réussite
        $stats->taux_reussite = $stats->total_eleves > 0 
            ? round(($stats->eleves_admis / $stats->total_eleves) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
      }
}
