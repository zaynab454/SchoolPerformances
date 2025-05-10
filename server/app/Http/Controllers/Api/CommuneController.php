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
use Illuminate\Support\Facades\DB;

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
            $anneeScolaire = AnneeScolaire::where('active', true)->first();
            if ($anneeScolaire) {
                $annee_scolaire = $anneeScolaire->annee;
            }
        }

        // Récupérer la commune
        $commune = Commune::with(['province', 'etablissements'])->findOrFail($id_commune);

        // Nombre d'élèves dans la commune
        $nombreEleves = Eleve::whereHas('etablissement', function($query) use ($id_commune) {
            $query->where('id_commune', $id_commune);
        })->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->whereHas('resultats', function($q) use ($annee_scolaire) {
                $q->where('annee_scolaire', $annee_scolaire);
            });
        })->count();

        // Moyenne générale de la commune
        $moyenneGenerale = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
            $query->where('id_commune', $id_commune);
        })->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })->avg('MoyenSession');

        // Taux de réussite
        $totalResultats = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
            $query->where('id_commune', $id_commune);
        })->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })->count();

        $reussis = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
            $query->where('id_commune', $id_commune);
        })->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })->where('MoyenSession', '>=', 10)->count();

        $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;
        $tauxEchec = 100 - $tauxReussite;

        // Rang de la commune dans la province
        $rangCommune = DB::table('resultat_eleves')
            ->join('eleves', 'resultat_eleves.id_eleve', '=', 'eleves.id_eleve')
            ->join('etablissements', 'eleves.id_etablissement', '=', 'etablissements.id_etablissement')
            ->join('communes', 'etablissements.id_commune', '=', 'communes.id_commune')
            ->where('communes.id_province', $commune->id_province)
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('resultat_eleves.annee_scolaire', $annee_scolaire);
            })
            ->select('communes.id_commune', DB::raw('AVG(resultat_eleves.MoyenSession) as moyenne'))
            ->groupBy('communes.id_commune')
            ->orderByDesc('moyenne')
            ->get()
            ->search(function($item) use ($id_commune) {
                return $item->id_commune == $id_commune;
            });

        // Classement des établissements dans la commune
        $classementEtablissements = DB::table('resultat_eleves')
            ->join('eleves', 'resultat_eleves.id_eleve', '=', 'eleves.id_eleve')
            ->join('etablissements', 'eleves.id_etablissement', '=', 'etablissements.id_etablissement')
            ->where('etablissements.id_commune', $id_commune)
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('resultat_eleves.annee_scolaire', $annee_scolaire);
            })
            ->select(
                'etablissements.id_etablissement',
                'etablissements.nom_etablissement',
                DB::raw('COUNT(DISTINCT eleves.id_eleve) as nombre_eleves'),
                DB::raw('AVG(resultat_eleves.MoyenSession) as moyenne_generale'),
                DB::raw('COUNT(CASE WHEN resultat_eleves.MoyenSession >= 10 THEN 1 END) * 100.0 / COUNT(*) as taux_reussite')
            )
            ->groupBy('etablissements.id_etablissement', 'etablissements.nom_etablissement')
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

        return response()->json([
            'success' => true,
            'data' => [
                'commune' => $commune,
                'statistiques' => [
                    'nombre_eleves' => $nombreEleves,
                    'moyenne_generale' => round($moyenneGenerale, 2),
                    'taux_reussite' => round($tauxReussite, 2),
                    'taux_echec' => round($tauxEchec, 2),
                    'rang_province' => $rangCommune !== false ? $rangCommune + 1 : null,
                    'classement_etablissements' => $classementEtablissements
                ]
            ]
        ]);
    }

    public function evolutionCommune($id_commune)
    {
        // Récupérer la commune
        $commune = Commune::with(['province'])->findOrFail($id_commune);

        // Récupérer tous les cycles scolaires
        $cycles = NiveauScolaire::select('cycle')
            ->distinct()
            ->orderBy('cycle')
            ->pluck('cycle');

        // Récupérer toutes les années scolaires disponibles
        $annees = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
            $query->where('id_commune', $id_commune);
        })
        ->select('annee_scolaire')
        ->distinct()
        ->orderBy('annee_scolaire')
        ->pluck('annee_scolaire');

        // Statistiques par année
        $evolution = $annees->map(function($annee) use ($id_commune, $cycles) {
            // Statistiques globales
            $statsGlobales = $this->calculerStatistiquesGlobales($id_commune, $annee);

            // Statistiques par cycle
            $statsParCycle = $cycles->map(function($cycle) use ($id_commune, $annee) {
                // Nombre d'élèves par cycle
                $nombreEleves = Eleve::whereHas('etablissement', function($query) use ($id_commune) {
                    $query->where('id_commune', $id_commune);
                })
                ->whereHas('niveau', function($query) use ($cycle) {
                    $query->where('cycle', $cycle);
                })
                ->whereHas('resultats', function($query) use ($annee) {
                    $query->where('annee_scolaire', $annee);
                })
                ->count();

                // Moyenne générale par cycle
                $moyenneGenerale = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
                    $query->where('id_commune', $id_commune);
                })
                ->whereHas('eleve.niveau', function($query) use ($cycle) {
                    $query->where('cycle', $cycle);
                })
                ->where('annee_scolaire', $annee)
                ->avg('MoyenSession');

                // Taux de réussite par cycle
                $totalResultats = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
                    $query->where('id_commune', $id_commune);
                })
                ->whereHas('eleve.niveau', function($query) use ($cycle) {
                    $query->where('cycle', $cycle);
                })
                ->where('annee_scolaire', $annee)
                ->count();

                $reussis = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
                    $query->where('id_commune', $id_commune);
                })
                ->whereHas('eleve.niveau', function($query) use ($cycle) {
                    $query->where('cycle', $cycle);
                })
                ->where('annee_scolaire', $annee)
                ->where('MoyenSession', '>=', 10)
                ->count();

                $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;
                $tauxEchec = 100 - $tauxReussite;

                return [
                    'cycle' => $cycle,
                    'nombre_eleves' => $nombreEleves,
                    'moyenne_generale' => round($moyenneGenerale, 2),
                    'taux_reussite' => round($tauxReussite, 2),
                    'taux_echec' => round($tauxEchec, 2)
                ];
            });

            return [
                'annee_scolaire' => $annee,
                'statistiques_globales' => $statsGlobales,
                'statistiques_par_cycle' => $statsParCycle
            ];
        });

        return response()->json([
            'commune' => [
                'id' => $commune->id_commune,
                'nom' => $commune->nom_commune,
                'province' => $commune->province->nom_province
            ],
            'evolution' => $evolution
        ]);
    }

    private function calculerStatistiquesGlobales($id_commune, $annee)
    {
        // Nombre d'élèves
        $nombreEleves = Eleve::whereHas('etablissement', function($query) use ($id_commune) {
            $query->where('id_commune', $id_commune);
        })
        ->whereHas('resultats', function($query) use ($annee) {
            $query->where('annee_scolaire', $annee);
        })
        ->count();

        // Moyenne générale
        $moyenneGenerale = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
            $query->where('id_commune', $id_commune);
        })
        ->where('annee_scolaire', $annee)
        ->avg('MoyenSession');

        // Taux de réussite
        $totalResultats = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
            $query->where('id_commune', $id_commune);
        })
        ->where('annee_scolaire', $annee)
        ->count();

        $reussis = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
            $query->where('id_commune', $id_commune);
        })
        ->where('annee_scolaire', $annee)
        ->where('MoyenSession', '>=', 10)
        ->count();

        $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;
        $tauxEchec = 100 - $tauxReussite;

        // Nombre d'établissements
        $nombreEtablissements = Etablissement::where('id_commune', $id_commune)
            ->whereHas('eleves.resultats', function($query) use ($annee) {
                $query->where('annee_scolaire', $annee);
            })
            ->count();

        // Rang dans la province
        $rangProvince = DB::table('resultat_eleves')
            ->join('eleves', 'resultat_eleves.id_eleve', '=', 'eleves.id_eleve')
            ->join('etablissements', 'eleves.id_etablissement', '=', 'etablissements.id_etablissement')
            ->join('communes', 'etablissements.id_commune', '=', 'communes.id_commune')
            ->where('communes.id_province', Commune::find($id_commune)->id_province)
            ->where('resultat_eleves.annee_scolaire', $annee)
            ->select('communes.id_commune', DB::raw('AVG(resultat_eleves.MoyenSession) as moyenne'))
            ->groupBy('communes.id_commune')
            ->orderByDesc('moyenne')
            ->get()
            ->search(function($item) use ($id_commune) {
                return $item->id_commune == $id_commune;
            });

        return [
            'nombre_eleves' => $nombreEleves,
            'nombre_etablissements' => $nombreEtablissements,
            'moyenne_generale' => round($moyenneGenerale, 2),
            'taux_reussite' => round($tauxReussite, 2),
            'taux_echec' => round($tauxEchec, 2),
            'rang_province' => $rangProvince !== false ? $rangProvince + 1 : null
        ];
    }

      /**
     * Get statistics by cycle for a specific commune
     */
    public function statsParCycle($id_commune, $annee_scolaire = null)
    {
        try {
            // Get the active academic year if none is provided
            if (!$annee_scolaire) {
                $anneeScolaire = AnneeScolaire::where('active', true)->first();
                if ($anneeScolaire) {
                    $annee_scolaire = $anneeScolaire->annee;
                }
            }

            \Log::info('Recherche des statistiques pour la commune: ' . $id_commune);
            \Log::info('Année scolaire: ' . $annee_scolaire);

            // Get statistics by cycle using Eloquent
            $resultats = Etablissement::with(['eleves.resultats'])
            ->whereHas('commune', function($q) use ($id_commune) {
                $q->where('cd_com', $id_commune);
            })
            ->whereHas('eleves', function($q) use ($annee_scolaire) {
                $q->whereHas('resultats', function($r) use ($annee_scolaire) {
                    $r->where('annee_scolaire', $annee_scolaire);
                });
            })
            ->get()
            ->groupBy('cycle')
            ->map(function($group) use ($annee_scolaire) {
                $nombreEleves = 0;
                $sommeMoyennes = 0;
                $nombreMoyennes = 0;
                $nombreReussis = 0;

                foreach ($group as $etablissement) {
                    foreach ($etablissement->eleves as $eleve) {
                        foreach ($eleve->resultats as $resultat) {
                            if ($resultat->annee_scolaire == $annee_scolaire) {
                                $nombreEleves++;
                                $sommeMoyennes += $resultat->MoyenSession;
                                $nombreMoyennes++;
                                if ($resultat->MoyenSession >= 10) {
                                    $nombreReussis++;
                                }
                            }
                        }
                    }
                }

                $moyenneGenerale = $nombreMoyennes > 0 ? $sommeMoyennes / $nombreMoyennes : 0;
                $tauxReussite = $nombreEleves > 0 ? ($nombreReussis / $nombreEleves) * 100 : 0;

                return [
                    'cycle' => $group[0]->cycle,
                    'nombre_eleves' => $nombreEleves,
                    'moyenne_generale' => round($moyenneGenerale, 2),
                    'taux_reussite' => round($tauxReussite, 2)
                ];
            });

            \Log::info('Résultats trouvés: ' . json_encode($resultats));

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

