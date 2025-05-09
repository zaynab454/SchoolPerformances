<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Etablissement;
use App\Models\ResultatEleve;
use App\Models\Eleve;
use App\Models\NiveauScolaire;
use App\Models\Matiere;
use Illuminate\Support\Facades\DB;

class EtablissementController extends Controller
{
    public function index()
    {
        $etablissements = Etablissement::with(['commune.province'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $etablissements
        ]);
    }

    public function show($id)
    {
        $etablissement = Etablissement::with(['commune.province'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $etablissement
        ]);
    }

    public function statEtablissement($id_etablissement, $annee_scolaire = null)
    {
        $etablissement = Etablissement::with(['commune.province'])->findOrFail($id_etablissement);

        // Calculer la moyenne générale
        $moyenneGenerale = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement) {
            $query->where('id_etablissement', $id_etablissement);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })
        ->avg('MoyenSession');

        // Calculer le taux de réussite
        $totalResultats = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement) {
            $query->where('id_etablissement', $id_etablissement);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })
        ->count();

        $reussis = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement) {
            $query->where('id_etablissement', $id_etablissement);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })
        ->where('MoyenSession', '>=', 10)
        ->count();

        $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;

        // Calculer le rang dans la province
        $etablissementsProvince = Etablissement::whereHas('commune', function($query) use ($etablissement) {
            $query->where('id_province', $etablissement->commune->id_province);
        })->get();

        $moyennesProvince = $etablissementsProvince->map(function($etab) use ($annee_scolaire) {
            $moyenne = ResultatEleve::whereHas('eleve', function($query) use ($etab) {
                $query->where('id_etablissement', $etab->id_etablissement);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->avg('MoyenSession');

            return [
                'id_etablissement' => $etab->id_etablissement,
                'moyenne' => $moyenne
            ];
        })->sortByDesc('moyenne')->values();

        $rangProvince = $moyennesProvince->search(function($item) use ($id_etablissement) {
            return $item['id_etablissement'] == $id_etablissement;
        }) + 1;

        // Calculer le rang dans la commune
        $etablissementsCommune = Etablissement::where('id_commune', $etablissement->id_commune)->get();

        $moyennesCommune = $etablissementsCommune->map(function($etab) use ($annee_scolaire) {
            $moyenne = ResultatEleve::whereHas('eleve', function($query) use ($etab) {
                $query->where('id_etablissement', $etab->id_etablissement);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->avg('MoyenSession');

            return [
                'id_etablissement' => $etab->id_etablissement,
                'moyenne' => $moyenne
            ];
        })->sortByDesc('moyenne')->values();

        $rangCommune = $moyennesCommune->search(function($item) use ($id_etablissement) {
            return $item['id_etablissement'] == $id_etablissement;
        }) + 1;

        return response()->json([
            'success' => true,
            'data' => [
                'etablissement' => $etablissement,
                'statistiques' => [
                    'moyenne_generale' => round($moyenneGenerale, 2),
                    'taux_reussite' => round($tauxReussite, 2),
                    'rang_province' => $rangProvince,
                    'rang_commune' => $rangCommune
                ]
            ]
        ]);
    }

    public function statNiveau($id_etablissement, $annee_scolaire = null, $id_niveau = null)
    {
        $etablissement = Etablissement::with(['commune.province'])->findOrFail($id_etablissement);
        
        $niveaux = $id_niveau ? 
            NiveauScolaire::where('id_niveau', $id_niveau)->get() : 
            NiveauScolaire::all();

        $statistiquesNiveaux = $niveaux->map(function($niveau) use ($id_etablissement, $annee_scolaire) {
            $nombreEleves = Eleve::where('id_etablissement', $id_etablissement)
                ->where('id_niveau', $niveau->id_niveau)
                ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                    $query->whereHas('resultats', function($q) use ($annee_scolaire) {
                        $q->where('annee_scolaire', $annee_scolaire);
                    });
                })
                ->count();

            $moyenneNiveau = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement, $niveau) {
                $query->where('id_etablissement', $id_etablissement)
                    ->where('id_niveau', $niveau->id_niveau);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->avg('MoyenSession');

            $totalResultats = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement, $niveau) {
                $query->where('id_etablissement', $id_etablissement)
                    ->where('id_niveau', $niveau->id_niveau);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->count();

            $reussis = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement, $niveau) {
                $query->where('id_etablissement', $id_etablissement)
                    ->where('id_niveau', $niveau->id_niveau);
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

            $resultats = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement, $niveau) {
                $query->where('id_etablissement', $id_etablissement)
                    ->where('id_niveau', $niveau->id_niveau);
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
                'niveau' => $niveau->nom_niveau,
                'nombre_eleves' => $nombreEleves,
                'moyenne' => round($moyenneNiveau, 2),
                'taux_reussite' => round($tauxReussite, 2),
                'distribution_mentions' => $mentions
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'etablissement' => $etablissement,
                'annee_scolaire' => $annee_scolaire,
                'statistiques_niveaux' => $statistiquesNiveaux
            ]
        ]);
    }

    public function statMatiere($id_etablissement, $id_niveau, $annee_scolaire = null)
    {
        $etablissement = Etablissement::with(['commune.province'])->findOrFail($id_etablissement);
        $niveau = NiveauScolaire::findOrFail($id_niveau);
        
        $matieres = Matiere::all();
        $statistiquesMatieres = $matieres->map(function($matiere) use ($id_etablissement, $id_niveau, $annee_scolaire) {
            $nombreEleves = Eleve::where('id_etablissement', $id_etablissement)
                ->where('id_niveau', $id_niveau)
                ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                    $query->whereHas('resultats', function($q) use ($annee_scolaire) {
                        $q->where('annee_scolaire', $annee_scolaire);
                    });
                })
                ->count();

            $moyenneMatiere = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement, $id_niveau) {
                $query->where('id_etablissement', $id_etablissement)
                    ->where('id_niveau', $id_niveau);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->avg($matiere->nom_colonne);

            $totalResultats = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement, $id_niveau) {
                $query->where('id_etablissement', $id_etablissement)
                    ->where('id_niveau', $id_niveau);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->count();

            $reussis = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement, $id_niveau) {
                $query->where('id_etablissement', $id_etablissement)
                    ->where('id_niveau', $id_niveau);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->where($matiere->nom_colonne, '>=', 10)
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

            $resultats = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement, $id_niveau) {
                $query->where('id_etablissement', $id_etablissement)
                    ->where('id_niveau', $id_niveau);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->get();

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
            'data' => [
                'etablissement' => $etablissement,
                'niveau' => $niveau,
                'annee_scolaire' => $annee_scolaire,
                'statistiques_matieres' => $statistiquesMatieres
            ]
        ]);
    }

    public function evaluationAnnuelle($id_etablissement)
    {
        $etablissement = Etablissement::with(['commune.province'])->findOrFail($id_etablissement);
        
        // Récupérer toutes les années scolaires distinctes
        $anneesScolaires = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement) {
            $query->where('id_etablissement', $id_etablissement);
        })
        ->distinct()
        ->pluck('annee_scolaire');

        $evolution = $anneesScolaires->map(function($annee) use ($id_etablissement) {
            $moyenneAnnee = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement) {
                $query->where('id_etablissement', $id_etablissement);
            })
            ->where('annee_scolaire', $annee)
            ->avg('MoyenSession');

            $totalResultats = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement) {
                $query->where('id_etablissement', $id_etablissement);
            })
            ->where('annee_scolaire', $annee)
            ->count();

            $reussis = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement) {
                $query->where('id_etablissement', $id_etablissement);
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
            'data' => [
                'etablissement' => $etablissement,
                'evolution' => $evolution
            ]
        ]);
    }
}
