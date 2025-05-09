<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commune;
use App\Models\Etablissement;
use App\Models\Eleve;
use App\Models\ResultatEleve;
use App\Models\NiveauScolaire;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function statsProvince($anneeScolaire = null)
    {
        // Validation de l'année scolaire
        if ($anneeScolaire && !preg_match('/^[0-9]{4}-[0-9]{4}$/', $anneeScolaire)) {
            return response()->json([
                'success' => false,
                'message' => 'Format d\'année scolaire invalide. Utilisez le format YYYY-YYYY (ex: 2023-2024)'
            ], 400);
        }

        // Récupérer les statistiques avec une requête plus optimisée
        $statistiques = Province::with([
            'communes.etablissements' => function($query) use ($anneeScolaire) {
                if ($anneeScolaire) {
                    $query->withCount([
                        'resultats as nombre_eleves' => function($q) use ($anneeScolaire) {
                            $q->where('annee_scolaire', $anneeScolaire);
                        },
                        'resultats as nombre_reussis' => function($q) use ($anneeScolaire) {
                            $q->where('annee_scolaire', $anneeScolaire)
                              ->where('MoyenSession', '>=', 10);
                        }
                    ])
                    ->withAvg('resultats', 'MoyenSession', 'moyenne_generale')
                    ->whereHas('resultats', function($q) use ($anneeScolaire) {
                        $q->where('annee_scolaire', $anneeScolaire);
                    });
                }
            }
        ])
        ->get()
        ->map(function($province) {
            $nombreEleves = $province->communes
                ->flatMap(function($commune) {
                    return $commune->etablissements;
                })
                ->sum('nombre_eleves');

            $nombreReussis = $province->communes
                ->flatMap(function($commune) {
                    return $commune->etablissements;
                })
                ->sum('nombre_reussis');

            $moyenneGenerale = $province->communes
                ->flatMap(function($commune) {
                    return $commune->etablissements;
                })
                ->avg('moyenne_generale');

            $tauxReussite = $nombreEleves > 0 ? ($nombreReussis / $nombreEleves) * 100 : 0;

            return [
                'province' => $province->nom_province,
                'nombre_eleves' => $nombreEleves,
                'moyenne_generale' => round($moyenneGenerale, 2),
                'taux_reussite' => round($tauxReussite, 2)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $statistiques
        ]);
    }

    public function moyennesParProvince($anneeScolaire = null)
    {
        $resultats = Province::with(['communes.etablissements.resultats' => function($query) use ($anneeScolaire) {
            if ($anneeScolaire) {
                $query->where('annee_scolaire', $anneeScolaire);
            }
        }])
        ->get()
        ->map(function($province) {
            // Calculer le nombre d'élèves
            $nombreEleves = $province->communes
                ->flatMap(function($commune) {
                    return $commune->etablissements;
                })
                ->flatMap(function($etablissement) {
                    return $etablissement->resultats;
                })
                ->count();

            // Calculer la moyenne générale
            $moyenneGenerale = $province->communes
                ->flatMap(function($commune) {
                    return $commune->etablissements;
                })
                ->flatMap(function($etablissement) {
                    return $etablissement->resultats;
                })
                ->avg('MoyenSession');

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
        $resultats = Etablissement::with(['resultats' => function($query) use ($anneeScolaire) {
            if ($anneeScolaire) {
                $query->where('annee_scolaire', $anneeScolaire);
            }
        }])
        ->whereHas('resultats')
        ->get()
        ->map(function($etablissement) {
            // Calculer la moyenne générale
            $moyenneGenerale = $etablissement->resultats->avg('MoyenSession');
            
            // Calculer le nombre d'élèves
            $nombreEleves = $etablissement->resultats->count();

            return [
                'nom_etablissement' => $etablissement->nom_etablissement,
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
        $resultats = NiveauScolaire::with(['resultats' => function($query) use ($anneeScolaire) {
            if ($anneeScolaire) {
                $query->where('annee_scolaire', $anneeScolaire);
            }
        }])
        ->get()
        ->map(function($niveau) {
            // Calculer le nombre d'élèves
            $nombreEleves = $niveau->resultats->count();
            
            // Calculer la moyenne générale
            $moyenneGenerale = $niveau->resultats->avg('MoyenSession');

            return [
                'cycle' => $niveau->nom_niveau,
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
        // Récupérer la province
        $province = Province::findOrFail($id_province);

        // Récupérer les communes avec leurs statistiques
        $communes = $province->communes
            ->map(function($commune) use ($anneeScolaire) {
                // Calculer le nombre d'élèves
                $nombreEleves = $commune->etablissements
                    ->flatMap(function($etablissement) {
                        return $etablissement->resultats;
                    })
                    ->where('annee_scolaire', $anneeScolaire)
                    ->count();

                // Calculer la moyenne générale
                $moyenneGenerale = $commune->etablissements
                    ->flatMap(function($etablissement) {
                        return $etablissement->resultats;
                    })
                    ->where('annee_scolaire', $anneeScolaire)
                    ->avg('MoyenSession');

                // Calculer le taux de réussite
                $reussis = $commune->etablissements
                    ->flatMap(function($etablissement) {
                        return $etablissement->resultats;
                    })
                    ->where('annee_scolaire', $anneeScolaire)
                    ->where('MoyenSession', '>=', 10)
                    ->count();

                $tauxReussite = $nombreEleves > 0 ? ($reussis / $nombreEleves) * 100 : 0;

                return [
                    'commune' => $commune->nom_commune,
                    'nombre_eleves' => $nombreEleves,
                    'moyenne_generale' => round($moyenneGenerale, 2),
                    'taux_reussite' => round($tauxReussite, 2),
                    'rang' => null // Calculé après
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
                            ->flatMap(function($etablissement) {
                                return $etablissement->resultats;
                            })
                            ->where('annee_scolaire', $anneeScolaire)
                            ->count(),
                    'moyenne' => $province->communes
                        ->flatMap(function($commune) {
                            return $commune->etablissements;
                        })
                        ->flatMap(function($etablissement) {
                            return $etablissement->resultats;
                        })
                        ->where('annee_scolaire', $anneeScolaire)
                        ->avg('MoyenSession'),
                    'taux_reussite' => (
                        $province->communes
                            ->flatMap(function($commune) {
                                return $commune->etablissements;
                            })
                            ->flatMap(function($etablissement) {
                                return $etablissement->resultats;
                            })
                            ->where('annee_scolaire', $anneeScolaire)
                            ->where('MoyenSession', '>=', 10)
                            ->count() 
                    ) / (
                        $province->communes
                            ->flatMap(function($commune) {
                                return $commune->etablissements;
                            })
                            ->flatMap(function($etablissement) {
                                return $etablissement->resultats;
                            })
                            ->where('annee_scolaire', $anneeScolaire)
                            ->count()
                    ) 
                        $province->communes
                            ->flatMap(function($commune) {
                                return $commune->etablissements;
                            })
                            ->flatMap(function($etablissement) {
                                return $etablissement->resultats;
                            })
                            ->where('annee_scolaire', $anneeScolaire)
                            ->count() * 100
                ];
            } else {
                // Récupérer toutes les années scolaires uniques
                $annees = ResultatEleve::select('annee_scolaire')
                    ->distinct()
                    ->orderBy('annee_scolaire')
                    ->pluck('annee_scolaire');

                foreach ($annees as $annee) {
                    $resultats[$province->id_province]['annees'][$annee] = [
                        'annee_scolaire' => $annee,
                        'nombre_eleves' => $province->communes
                            ->flatMap(function($commune) {
                                return $commune->etablissements;
                            })
                            ->flatMap(function($etablissement) {
                                return $etablissement->resultats;
                            })
                            ->where('annee_scolaire', $annee)
                            ->count(),
                        'moyenne' => $province->communes
                            ->flatMap(function($commune) {
                                return $commune->etablissements;
                            })
                            ->flatMap(function($etablissement) {
                                return $etablissement->resultats;
                            })
                            ->where('annee_scolaire', $annee)
                            ->avg('MoyenSession'),
                        'taux_reussite' => $province->communes
                            ->flatMap(function($commune) {
                                return $commune->etablissements;
                            })
                            ->flatMap(function($etablissement) {
                                return $etablissement->resultats;
                            })
                            ->where('annee_scolaire', $annee)
                            ->where('MoyenSession', '>=', 10)
                            ->count() / 
                            $province->communes
                                ->flatMap(function($commune) {
                                    return $commune->etablissements;
                                })
                                ->flatMap(function($etablissement) {
                                    return $etablissement->resultats;
                                })
                                ->where('annee_scolaire', $annee)
                                ->count() * 100
                    ];
                }
            }
        }

        return array_values($resultats);
    }

    private function topEtablissementsParProvince($anneeScolaire)
    {
        // Récupérer toutes les provinces
        $provinces = Province::with('communes.etablissements.resultats')
            ->get();

        $resultats = [];
        
        foreach ($provinces as $province) {
            // Calculer la moyenne générale pour chaque établissement
            $etablissements = Etablissement::whereHas('commune', function ($query) use ($province) {
                $query->where('id_province', $province->id_province);
            })
            ->with(['resultats' => function ($query) use ($anneeScolaire) {
                $query->where('annee_scolaire', $anneeScolaire);
            }])
            ->get()
            ->map(function ($etab) {
                return [
                    'code_etab' => $etab->code_etab,
                    'nom_etab_fr' => $etab->nom_etab_fr,
                    'nom_etab_ar' => $etab->nom_etab_ar,
                    'moyenne_generale' => $etab->resultats->avg('MoyenSession')
                ];
            })
            ->sortByDesc('moyenne_generale')
            ->take(5)
            ->toArray();

            $resultats[$province->id_province] = [
                'nom_province' => $province->nom_province,
                'top_etablissements' => $etablissements
            ];
        }

        return array_values($resultats);
    }

    private function statsParCycle($anneeScolaire)
    {
        // Récupérer tous les cycles uniques
        $cycles = Etablissement::select('cycle')
            ->distinct()
            ->pluck('cycle');

        $resultats = [];
        
        foreach ($cycles as $cycle) {
            // Calculer le taux de réussite
            $nombreReussis = ResultatEleve::whereHas('eleve.etablissement', function ($query) use ($cycle) {
                $query->where('cycle', $cycle);
            })
            ->where('MoyenSession', '>=', 10)
            ->count();

            $nombreTotal = ResultatEleve::whereHas('eleve.etablissement', function ($query) use ($cycle) {
                $query->where('cycle', $cycle);
            })
            ->count();

            // Calculer la moyenne générale
            $moyenne = ResultatEleve::whereHas('eleve.etablissement', function ($query) use ($cycle) {
                $query->where('cycle', $cycle);
            })
            ->avg('MoyenSession');

            // Compter le nombre d'élèves
            $nombreEleves = Eleve::whereHas('etablissement', function ($query) use ($cycle) {
                $query->where('cycle', $cycle);
            })
            ->count();

            $resultats[] = [
                'cycle' => $cycle,
                'nombre_eleves' => $nombreEleves,
                'taux_reussite' => $nombreReussis / $nombreTotal * 100,
                'moyenne_generale' => $moyenne
            ];
        }

        return $resultats;
    }
}
