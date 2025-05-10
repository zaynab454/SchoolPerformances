<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Etablissement;
use App\Models\ResultatEleve;
use App\Models\Eleve;
use App\Models\NiveauScolaire;
use App\Models\Matiere;
use App\Models\AnneeScolaire;
use App\Models\Commune;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EtablissementController extends Controller
{



    /**
     * Get establishments by commune
     */
    public function getEtablissementsByCommune(Request $request)
    {
        try {
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
                'message' => 'Établissements récupérés avec succès',
                'data' => $etablissements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des établissements',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get establishment statistics
     */
    public function statEtablissement($code_etab, $annee_scolaire = null)
    {
        try {
            $etablissement = Etablissement::with(['commune.province'])->findOrFail($code_etab);

            // Calculer la moyenne générale
            $moyenneGenerale = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
                ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
                ->where('etablissement.code_etab', $code_etab)
                ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                    $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
                })
                ->avg('resultat_eleve.MoyenSession');

            // Calculer le taux de réussite
            $totalResultats = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
                ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
                ->where('etablissement.code_etab', $code_etab)
                ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                    $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
                })
                ->count();

            $reussis = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
                ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
                ->where('etablissement.code_etab', $code_etab)
                ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                    $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
                })
                ->where('resultat_eleve.MoyenSession', '>=', 10)
                ->count();

            $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;

            // Calculer le rang dans la province
            $etablissementsProvince = Etablissement::whereHas('commune', function($query) use ($etablissement) {
                $query->where('id_province', $etablissement->commune->id_province);
            })->get();

            $moyennesProvince = $etablissementsProvince->map(function($etab) use ($annee_scolaire) {
                $moyenne = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
                    ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
                    ->where('etablissement.code_etab', $etab->code_etab)
                    ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                        $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
                    })
                    ->avg('resultat_eleve.MoyenSession');

                return [
                    'code_etab' => $etab->code_etab,
                    'moyenne' => $moyenne
                ];
            })->sortByDesc('moyenne')->values();

            $rangProvince = $moyennesProvince->search(function($item) use ($code_etab) {
                return $item['code_etab'] == $code_etab;
            }) + 1;

            // Calculer le rang dans la commune
            $etablissementsCommune = Etablissement::where('code_commune', $etablissement->code_commune)->get();

            $moyennesCommune = $etablissementsCommune->map(function($etab) use ($annee_scolaire) {
                $moyenne = ResultatEleve::join('eleve', 'resultat_eleve.code_eleve', '=', 'eleve.code_eleve')
                    ->join('etablissement', 'eleve.code_etab', '=', 'etablissement.code_etab')
                    ->where('etablissement.code_etab', $etab->code_etab)
                    ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                        $query->where('resultat_eleve.annee_scolaire', $annee_scolaire);
                    })
                    ->avg('resultat_eleve.MoyenSession');

                return [
                    'code_etab' => $etab->code_etab,
                    'moyenne' => $moyenne
                ];
            })->sortByDesc('moyenne')->values();

            $rangCommune = $moyennesCommune->search(function($item) use ($code_etab) {
                return $item['code_etab'] == $code_etab;
            }) + 1;

            return response()->json([
                'success' => true,
                'message' => 'Statistiques de l\'établissement récupérées avec succès',
                'data' => [
                    'etablissement' => $etablissement,
                    'statistiques' => [
                        'moyenne_generale' => round($moyenneGenerale, 2),
                        'taux_reussite' => round($tauxReussite, 2),
                        'nombre_eleves' => $totalResultats,
                        'nombre_reussis' => $reussis,
                        'rang_province' => $rangProvince,
                        'rang_commune' => $rangCommune
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get level statistics
     */
    public function statNiveau($code_etab, $annee_scolaire = null, $code_niveau = null)
    {
        try {
            $etablissement = Etablissement::with(['commune.province'])->findOrFail($code_etab);
            
            $niveaux = $code_niveau ? 
                NiveauScolaire::where('code_niveau', $code_niveau)->get() : 
                NiveauScolaire::all();

            $statistiquesNiveaux = $niveaux->map(function($niveau) use ($code_etab, $annee_scolaire) {
                // Récupérer le nombre total d'élèves dans ce niveau
                $nombreElevesTotal = Eleve::where('code_etab', $code_etab)
                    ->where('code_niveau', $niveau->code_niveau)
                    ->count();

                // Récupérer le nombre d'élèves avec des résultats pour cette année
                $nombreEleves = Eleve::where('code_etab', $code_etab)
                    ->where('code_niveau', $niveau->code_niveau)
                    ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                        $query->whereHas('resultats', function($q) use ($annee_scolaire) {
                            $q->where('annee_scolaire', $annee_scolaire);
                        });
                    })
                    ->count();

                $moyenneNiveau = ResultatEleve::whereHas('eleve', function($query) use ($code_etab, $niveau) {
                    $query->where('code_etab', $code_etab)
                        ->where('code_niveau', $niveau->code_niveau);
                })
                ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                    $query->where('annee_scolaire', $annee_scolaire);
                })
                ->avg('MoyenSession');

                $totalResultats = ResultatEleve::whereHas('eleve', function($query) use ($code_etab, $niveau) {
                    $query->where('code_etab', $code_etab)
                        ->where('code_niveau', $niveau->code_niveau);
                })
                ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                    $query->where('annee_scolaire', $annee_scolaire);
                })
                ->count();

                $reussis = ResultatEleve::whereHas('eleve', function($query) use ($code_etab, $niveau) {
                    $query->where('code_etab', $code_etab)
                        ->where('code_niveau', $niveau->code_niveau);
                })
                ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                    $query->where('annee_scolaire', $annee_scolaire);
                })
                ->where('MoyenSession', '>=', 10)
                ->count();

                $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;

                // Distribution des mentions
                $mentions = [
                    'excellent' => 0,
                    'tres_bien' => 0,
                    'bien' => 0,
                    'assez_bien' => 0,
                    'passable' => 0,
                    'insuffisant' => 0
                ];

                $resultats = ResultatEleve::whereHas('eleve', function($query) use ($code_etab, $niveau) {
                    $query->where('code_etab', $code_etab)
                        ->where('code_niveau', $niveau->code_niveau);
                })
                ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                    $query->where('annee_scolaire', $annee_scolaire);
                })
                ->get();

                foreach ($resultats as $resultat) {
                    $moyenne = $resultat->MoyenSession;
                    if ($moyenne >= 16) {
                        $mentions['excellent']++;
                    } elseif ($moyenne >= 14) {
                        $mentions['tres_bien']++;
                    } elseif ($moyenne >= 12) {
                        $mentions['bien']++;
                    } elseif ($moyenne >= 10) {
                        $mentions['assez_bien']++;
                    } elseif ($moyenne >= 8) {
                        $mentions['passable']++;
                    } else {
                        $mentions['insuffisant']++;
                    }
                }

                return [
                    'niveau' => [
                        'code_niveau' => $niveau->code_niveau,
                        'description' => $niveau->description
                    ],
                    'nombre_eleves' => $nombreEleves,
                    'moyenne' => round($moyenneNiveau, 2),
                    'taux_reussite' => round($tauxReussite, 2),
                    'distribution_mentions' => $mentions
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Statistiques par niveau récupérées avec succès',
                'data' => [
                    'etablissement' => $etablissement,
                    'niveaux' => $niveaux,
                    'annee_scolaire' => $annee_scolaire,
                    'statistiques_niveaux' => $statistiquesNiveaux
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques par niveau',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subject statistics
     */
    public function statMatiere($code_etab, $code_niveau, $annee_scolaire = null)
    {
        try {
            $etablissement = Etablissement::with(['commune.province'])->findOrFail($code_etab);
            $niveau = NiveauScolaire::findOrFail($code_niveau);
            
            // Vérifier et nettoyer l'année scolaire
            if ($annee_scolaire) {
                $annee_scolaire = preg_replace('/[^0-9-]/', '', $annee_scolaire);
            }
            
            // Récupérer uniquement les matières liées à ce niveau
            $matieres = Matiere::where('code_niveau', $code_niveau)
                ->with(['niveau' => function($query) use ($code_niveau) {
                    $query->where('code_niveau', $code_niveau);
                }])
                ->get();
            
            $statistiquesMatieres = $matieres->map(function($matiere) use ($code_etab, $code_niveau, $annee_scolaire) {
                $nombreEleves = Eleve::where('code_etab', $code_etab)
                    ->where('code_niveau', $code_niveau)
                    ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                        $query->whereHas('resultats', function($q) use ($annee_scolaire) {
                            $q->where('annee_scolaire', $annee_scolaire);
                        });
                    })
                    ->count();

                // Récupérer les résultats pour cette matière
                $resultats = ResultatEleve::whereHas('eleve', function($query) use ($code_etab, $code_niveau) {
                    $query->where('code_etab', $code_etab)
                        ->where('code_niveau', $code_niveau);
                })
                ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                    $query->where('annee_scolaire', $annee_scolaire);
                })
                ->get();

                // Calculer la moyenne pour cette matière
                $moyenneMatiere = $resultats->avg(function($resultat) use ($matiere) {
                    return $resultat->{$matiere->nom_colonne};
                });

                $totalResultats = ResultatEleve::whereHas('eleve', function($query) use ($code_etab, $code_niveau) {
                    $query->where('code_etab', $code_etab)
                        ->where('code_niveau', $code_niveau);
                })
                ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                    $query->where('annee_scolaire', $annee_scolaire);
                })
                ->count();

                // Compter les réussites pour cette matière
                $reussis = $resultats->filter(function($resultat) use ($matiere) {
                    return $resultat->{$matiere->nom_colonne} >= 10;
                })->count();

                $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;

                // Distribution des mentions
                $mentions = [
                    'excellent' => $resultats->filter(function($resultat) use ($matiere) {
                        return $resultat->{$matiere->nom_colonne} >= 16;
                    })->count(),
                    'tres_bien' => $resultats->filter(function($resultat) use ($matiere) {
                        return $resultat->{$matiere->nom_colonne} >= 14 && $resultat->{$matiere->nom_colonne} < 16;
                    })->count(),
                    'bien' => $resultats->filter(function($resultat) use ($matiere) {
                        return $resultat->{$matiere->nom_colonne} >= 12 && $resultat->{$matiere->nom_colonne} < 14;
                    })->count(),
                    'assez_bien' => $resultats->filter(function($resultat) use ($matiere) {
                        return $resultat->{$matiere->nom_colonne} >= 10 && $resultat->{$matiere->nom_colonne} < 12;
                    })->count(),
                    'passable' => $resultats->filter(function($resultat) use ($matiere) {
                        return $resultat->{$matiere->nom_colonne} >= 8 && $resultat->{$matiere->nom_colonne} < 10;
                    })->count(),
                    'insuffisant' => $resultats->filter(function($resultat) use ($matiere) {
                        return $resultat->{$matiere->nom_colonne} < 8;
                    })->count()
                ];

                return [
                    'matiere' => $matiere,
                    'nombre_eleves' => $nombreEleves,
                    'moyenne' => $moyenneMatiere,
                    'taux_reussite' => $tauxReussite,
                    'distribution_mentions' => $mentions
                ];
                $nombreEleves = Eleve::where('code_etab', $code_etab)
                    ->where('code_niveau', $code_niveau)
                    ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                        $query->whereHas('resultats', function($q) use ($annee_scolaire) {
                            $q->where('annee_scolaire', $annee_scolaire);
                        });
                    })
                    ->count();

                // Récupérer les résultats pour cette matière
                $resultats = ResultatEleve::whereHas('eleve', function($query) use ($code_etab, $code_niveau) {
                    $query->where('code_etab', $code_etab)
                        ->where('code_niveau', $code_niveau);
                })
                ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                    $query->where('annee_scolaire', $annee_scolaire);
                })
                ->get();

                // Calculer la moyenne pour cette matière
                $moyenneMatiere = $resultats->avg(function($resultat) use ($matiere) {
                    return $resultat->{$matiere->nom_colonne};
                });

                $totalResultats = ResultatEleve::whereHas('eleve', function($query) use ($code_etab, $code_niveau) {
                    $query->where('code_etab', $code_etab)
                        ->where('code_niveau', $code_niveau);
                })
                ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                    $query->where('annee_scolaire', $annee_scolaire);
                })
                ->count();

                // Compter les réussites pour cette matière
                $reussis = $resultats->filter(function($resultat) use ($matiere) {
                    return $resultat->{$matiere->nom_colonne} >= 10;
                })->count();

                $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;

                // Distribution des mentions
                $mentions = [
                    'excellent' => 0,
                    'tres_bien' => 0,
                    'bien' => 0,
                    'assez_bien' => 0,
                    'passable' => 0,
                    'insuffisant' => 0
                ];

                // Calculer la distribution des mentions pour cette matière
                foreach ($resultats as $resultat) {
                    $note = $resultat->{$matiere->nom_colonne};
                    if ($note >= 16) {
                        $mentions['excellent']++;
                    } elseif ($note >= 14) {
                        $mentions['tres_bien']++;
                    } elseif ($note >= 12) {
                        $mentions['bien']++;
                    } elseif ($note >= 10) {
                        $mentions['assez_bien']++;
                    } elseif ($note >= 8) {
                        $mentions['passable']++;
                    } else {
                        $mentions['insuffisant']++;
                    }
                }

                return [
                    'matiere' => $matiere->nom_matiere,
                    'nombre_eleves' => $nombreEleves,
                    'moyenne' => round($moyenneMatiere, 2),
                    'taux_reussite' => round($tauxReussite, 2),
                    'distribution_mentions' => $mentions
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Statistiques par matière récupérées avec succès',
                'data' => [
                    'etablissement' => $etablissement,
                    'niveau' => $niveau,
                    'annee_scolaire' => $annee_scolaire,
                    'statistiques_matieres' => $statistiquesMatieres
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques par matière',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get annual evaluation
     */
    public function evaluationAnnuelle($code_etab)
    {
        try {
            $etablissement = Etablissement::with(['commune.province'])->findOrFail($code_etab);
            
            $anneesScolaires = ResultatEleve::whereHas('eleve', function($query) use ($code_etab) {
                $query->where('code_etab', $code_etab);
            })
            ->distinct()
            ->pluck('annee_scolaire');

            $evolution = $anneesScolaires->map(function($annee) use ($code_etab) {
                $moyenneAnnee = ResultatEleve::whereHas('eleve', function($query) use ($code_etab) {
                    $query->where('code_etab', $code_etab);
                })
                ->where('annee_scolaire', $annee)
                ->avg('MoyenSession');

                $totalResultats = ResultatEleve::whereHas('eleve', function($query) use ($code_etab) {
                    $query->where('code_etab', $code_etab);
                })
                ->where('annee_scolaire', $annee)
                ->count();

                $reussis = ResultatEleve::whereHas('eleve', function($query) use ($code_etab) {
                    $query->where('code_etab', $code_etab);
                })
                ->where('annee_scolaire', $annee)
                ->where('MoyenSession', '>=', 10)
                ->count();

                $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;

                return [
                    'annee_scolaire' => $annee,
                    'moyenne' => round($moyenneAnnee, 2),
                    'taux_reussite' => round($tauxReussite, 2)
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Évaluation annuelle récupérée avec succès',
                'data' => [
                    'etablissement' => $etablissement,
                    'evolution' => $evolution
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'évaluation annuelle',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
