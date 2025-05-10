<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Etablissement;
use App\Models\Commune;
use App\Models\Province;
use App\Models\ResultatEleve;
use App\Models\Eleve;
use App\Models\NiveauScolaire;
use App\Models\Matiere;

class RapportController extends Controller
{
    public function rapportEtablissement($id_etablissement, $annee_scolaire = null)
    {
        // Récupérer l'établissement avec ses relations
        $etablissement = Etablissement::with(['commune.province'])->findOrFail($id_etablissement);

        // Statistiques générales
        $nombreEleves = Eleve::where('code_etab', $id_etablissement)
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->whereHas('resultats', function($q) use ($annee_scolaire) {
                    $q->where('annee_scolaire', $annee_scolaire);
                });
            })
            ->count();

        $moyenneGenerale = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement) {
            $query->where('code_etab', $id_etablissement);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })
        ->avg('MoyenSession');

        $totalResultats = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement) {
            $query->where('code_etab', $id_etablissement);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })
        ->count();

        $reussis = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement) {
            $query->where('code_etab', $id_etablissement);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })
        ->where('MoyenSession', '>=', 10)
        ->count();

        $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;

        // Statistiques par niveau
        $niveaux = NiveauScolaire::with(['eleves' => function($query) use ($id_etablissement) {
                $query->where('code_etab', $id_etablissement);
            }, 'matieres'])->get();
        $niveaux = $niveaux->filter(function($niveau) {
            return $niveau->eleves->isNotEmpty();
        });
        $statistiquesNiveaux = $niveaux->map(function($niveau) use ($id_etablissement, $annee_scolaire) {
            $moyenneNiveau = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement, $niveau) {
                $query->where('code_etab', $id_etablissement)
                    ->where('code_niveau', $niveau->code_niveau);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->avg('MoyenSession');

            $totalResultatsNiveau = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement, $niveau) {
                $query->where('code_etab', $id_etablissement)
                    ->where('code_niveau', $niveau->code_niveau);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->count();

            $reussisNiveau = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement, $niveau) {
                $query->where('code_etab', $id_etablissement)
                    ->where('code_niveau', $niveau->code_niveau);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->where('MoyenSession', '>=', 10)
            ->count();

            $tauxReussiteNiveau = $totalResultatsNiveau > 0 ? ($reussisNiveau / $totalResultatsNiveau) * 100 : 0;

            return [
                'niveau' => $niveau,
                'moyenne' => round($moyenneNiveau, 2),
                'taux_reussite' => round($tauxReussiteNiveau, 2)
            ];
        })->values();

        // Statistiques par matière
        $matieres = Matiere::with(['eleves' => function($query) use ($id_etablissement) {
                $query->where('code_etab', $id_etablissement);
            }, 'niveau'])->get();
        $matieres = $matieres->filter(function($matiere) {
            return $matiere->eleves->isNotEmpty();
        });
        $statistiquesMatieres = $matieres->map(function($matiere) use ($id_etablissement, $annee_scolaire) {
            $moyenneMatiere = ResultatEleve::whereHas('eleve', function($query) use ($id_etablissement) {
                $query->where('code_etab', $id_etablissement);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->avg($matiere->nom_colonne);

            return [
                'matiere' => $matiere->nom_matiere,
                'moyenne' => round($moyenneMatiere, 2)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'etablissement' => $etablissement,
                'annee_scolaire' => $annee_scolaire,
                'statistiques_generales' => [
                    'nombre_eleves' => $nombreEleves,
                    'moyenne_generale' => round($moyenneGenerale, 2),
                    'taux_reussite' => round($tauxReussite, 2)
                ],
                'statistiques_niveaux' => $statistiquesNiveaux,
                'statistiques_matieres' => $statistiquesMatieres
            ]
        ]);
    }

    public function rapportCommune($id_commune, $annee_scolaire = null)
    {
        // Récupérer la commune avec ses relations
        $commune = Commune::with(['province'])->findOrFail($id_commune);

        // Statistiques générales
        $nombreEleves = Eleve::whereHas('etablissement', function($query) use ($id_commune) {
            $query->where('code_commune', $id_commune);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->whereHas('resultats', function($q) use ($annee_scolaire) {
                $q->where('annee_scolaire', $annee_scolaire);
            });
        })
        ->count();

        $moyenneGenerale = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
            $query->where('code_commune', $id_commune);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })
        ->avg('MoyenSession');

        $totalResultats = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
            $query->where('code_commune', $id_commune);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })
        ->count();

        $reussis = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($id_commune) {
            $query->where('code_commune', $id_commune);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })
        ->where('MoyenSession', '>=', 10)
        ->count();

        $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;

        // Statistiques par établissement
        $etablissements = Etablissement::with(['eleves' => function($query) use ($id_commune) {
                $query->whereHas('etablissement', function($q) use ($id_commune) {
                    $q->where('code_commune', $id_commune);
                });
            }])->get();
        $etablissements = $etablissements->filter(function($etablissement) {
            return $etablissement->eleves->isNotEmpty();
        });
        $statistiquesEtablissements = $etablissements->map(function($etablissement) use ($annee_scolaire) {
            $moyenneEtablissement = ResultatEleve::whereHas('eleve', function($query) use ($etablissement) {
                $query->where('code_etab', $etablissement->code_etab);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->avg('MoyenSession');

            $totalResultatsEtablissement = ResultatEleve::whereHas('eleve', function($query) use ($etablissement) {
                $query->where('code_etab', $etablissement->code_etab);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->count();

            $reussisEtablissement = ResultatEleve::whereHas('eleve', function($query) use ($etablissement) {
                $query->where('code_etab', $etablissement->code_etab);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->where('MoyenSession', '>=', 10)
            ->count();

            $tauxReussiteEtablissement = $totalResultatsEtablissement > 0 ? ($reussisEtablissement / $totalResultatsEtablissement) * 100 : 0;

            return [
                'etablissement' => $etablissement->nom_etablissement,
                'moyenne' => round($moyenneEtablissement, 2),
                'taux_reussite' => round($tauxReussiteEtablissement, 2)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'commune' => $commune,
                'annee_scolaire' => $annee_scolaire,
                'statistiques_generales' => [
                    'nombre_eleves' => $nombreEleves,
                    'moyenne_generale' => round($moyenneGenerale, 2),
                    'taux_reussite' => round($tauxReussite, 2)
                ],
                'statistiques_etablissements' => $statistiquesEtablissements
            ]
        ]);
    }

    public function rapportProvince($id_province, $annee_scolaire = null)
    {
        // Récupérer la province
        $province = Province::findOrFail($id_province);

        // Statistiques générales
        $nombreEleves = Eleve::whereHas('etablissement.commune', function($query) use ($id_province) {
            $query->where('id_province', $id_province);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->whereHas('resultats', function($q) use ($annee_scolaire) {
                $q->where('annee_scolaire', $annee_scolaire);
            });
        })
        ->count();

        $moyenneGenerale = ResultatEleve::whereHas('eleve.etablissement.commune', function($query) use ($id_province) {
            $query->where('id_province', $id_province);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })
        ->avg('MoyenSession');

        $totalResultats = ResultatEleve::whereHas('eleve.etablissement.commune', function($query) use ($id_province) {
            $query->where('id_province', $id_province);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })
        ->count();

        $reussis = ResultatEleve::whereHas('eleve.etablissement.commune', function($query) use ($id_province) {
            $query->where('id_province', $id_province);
        })
        ->when($annee_scolaire, function($query) use ($annee_scolaire) {
            $query->where('annee_scolaire', $annee_scolaire);
        })
        ->where('MoyenSession', '>=', 10)
        ->count();

        $tauxReussite = $totalResultats > 0 ? ($reussis / $totalResultats) * 100 : 0;

        // Statistiques par commune
        $communes = Commune::where('id_province', $id_province)->get();
        $statistiquesCommunes = $communes->map(function($commune) use ($annee_scolaire) {
            $moyenneCommune = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($commune) {
                $query->where('code_commune', $commune->cd_com);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->avg('MoyenSession');

            $totalResultatsCommune = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($commune) {
                $query->where('code_commune', $commune->cd_com);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->count();

            $reussisCommune = ResultatEleve::whereHas('eleve.etablissement', function($query) use ($commune) {
                $query->where('code_commune', $commune->cd_com);
            })
            ->when($annee_scolaire, function($query) use ($annee_scolaire) {
                $query->where('annee_scolaire', $annee_scolaire);
            })
            ->where('MoyenSession', '>=', 10)
            ->count();

            $tauxReussiteCommune = $totalResultatsCommune > 0 ? ($reussisCommune / $totalResultatsCommune) * 100 : 0;

            return [
                'commune' => [
                    'nom' => $commune->ll_com,
                    'code' => $commune->cd_com
                ],
                'moyenne' => round($moyenneCommune, 2),
                'taux_reussite' => round($tauxReussiteCommune, 2)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'province' => $province,
                'annee_scolaire' => $annee_scolaire,
                'statistiques_generales' => [
                    'nombre_eleves' => $nombreEleves,
                    'moyenne_generale' => round($moyenneGenerale, 2),
                    'taux_reussite' => round($tauxReussite, 2)
                ],
                'statistiques_communes' => $statistiquesCommunes
            ]
        ]);
    }
}
