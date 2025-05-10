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
            $anneeScolaire = $request->query('annee_scolaire');

            // Si aucune année n'est spécifiée, utiliser l'année courante
            if (!$anneeScolaire) {
                $anneeCourante = AnneeScolaire::where('est_courante', true)->first();
                if ($anneeCourante) {
                    $anneeScolaire = $anneeCourante->annee_scolaire;
                }
            }

            // Charger la province avec tous les enfants nécessaires
            $province = Province::with([
                'communes.etablissements' => function($query) {
                    $query->withCount('eleves');
                }
            ])->first();

            if (!$province) {
                return response()->json([
                    'success' => false,
                    'message' => 'Province non trouvée'
                ], 404);
            }

            $totalEleves = 0;
            $totalResultats = 0;
            $totalReussis = 0;
            $sommeMoyennes = 0;

            // Compter les communes et établissements directement
            $nombreCommunes = $province->communes->count();
            $nombreEtablissements = $province->communes->sum(function($commune) {
                return $commune->etablissements->count();
            });

            // Parcourir les élèves et résultats
            foreach ($province->communes as $commune) {
                foreach ($commune->etablissements as $etablissement) {
                    foreach ($etablissement->eleves as $eleve) {
                        // Filtrer les résultats par année scolaire
                        $resultats = $eleve->resultats->where('annee_scolaire', $anneeScolaire);
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
                    'annee_scolaire' => $anneeScolaire,
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


     /**
     * Get province statistics evolution over years
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function evolutionProvince()
    {
        try {
            // Récupérer toutes les années scolaires
            $anneesScolaires = AnneeScolaire::orderBy('annee_scolaire', 'asc')->get();

            $statistiques = [];
            
            foreach ($anneesScolaires as $annee) {
                $nombreEleves = 0;
                $sommeMoyennes = 0;
                $nombreMoyennes = 0;
                $nombreReussis = 0;

                // Charger les élèves avec leurs résultats pour cette année
                $eleves = Eleve::with(['resultats' => function($query) use ($annee) {
                    $query->where('annee_scolaire', $annee->annee_scolaire);
                }])
                ->get();

                // Parcourir les élèves et leurs résultats
                foreach ($eleves as $eleve) {
                    foreach ($eleve->resultats as $resultat) {
                        $nombreEleves++;
                        $sommeMoyennes += $resultat->MoyenSession;
                        $nombreMoyennes++;
                        if ($resultat->MoyenSession >= 10) {
                            $nombreReussis++;
                        }
                    }
                }

                $moyenneGenerale = $nombreMoyennes > 0 ? $sommeMoyennes / $nombreMoyennes : 0;
            $tauxReussite = $nombreEleves > 0 ? ($nombreReussis / $nombreEleves) * 100 : 0;

                $statistiques[] = [
                    'annee_scolaire' => $annee->annee_scolaire,
                'nombre_eleves' => $nombreEleves,
                'moyenne_generale' => round($moyenneGenerale, 2),
                'taux_reussite' => round($tauxReussite, 2)
            ];
            }

            // Charger le nom de la province
            $province = Province::first();
            if (!$province) {
                return response()->json([
                    'success' => false,
                    'message' => 'Province non trouvée'
                ], 404);
            }

        return response()->json([
            'success' => true,
                'data' => [
                    'province' => $province->nom_province,
                    'statistiques' => $statistiques
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function topEtablissementsParProvince($anneeScolaire = null)
    {
        
        // Charger les élèves avec leurs résultats et leur établissement
        $eleves = Eleve::with(['etablissement', 'resultats' => function($q) use ($anneeScolaire) {
            if ($anneeScolaire) {
                $q->where('annee_scolaire', $anneeScolaire);
            }
        }])
        ->get();
        
        
        
        
        
        // Filtrer les élèves qui ont des résultats pour l'année spécifiée
        $elevesAvecResultats = $eleves->filter(function($eleve) use ($anneeScolaire) {
            foreach ($eleve->resultats as $resultat) {
                if ($resultat->annee_scolaire === $anneeScolaire) {
                    return true;
                }
            }
            return false;
        });
        
        
        
        // Grouper les élèves par établissement
        $etablissements = $elevesAvecResultats->groupBy('code_etab')->map(function($eleves, $codeEtab) {
            
            // Rechercher l'établissement avec et sans suffixe R
            $etablissement = Etablissement::where('code_etab', $codeEtab)
                ->orWhere('code_etab', str_replace('R', '', $codeEtab))
                ->first();
            
            if (!$etablissement) {
                return null;
            }

            $moyenneGenerale = 0;
            $nombreEleves = 0;
            $nombreResultats = 0;

            foreach ($eleves as $eleve) {
                // Vérifier si l'élève a des résultats
                if (count($eleve->resultats) > 0) {
                    $nombreEleves++;
                }

                foreach ($eleve->resultats as $resultat) {

                    $moyenneGenerale += $resultat->MoyenSession;
                    $nombreResultats++;
                }
            }

            // Calculer la moyenne générale
            $moyenneGenerale = $nombreResultats > 0 ? $moyenneGenerale / $nombreResultats : 0;

            return [
                'nom_etablissement' => $etablissement->nom_etab_fr,
                'moyenne_generale' => $moyenneGenerale,
                'nombre_eleves' => $nombreEleves
            ];
        })->filter(); // Filtrer les établissements null

        // Trier par moyenne générale décroissante et prendre les 5 premiers
        $resultats = $etablissements->sortByDesc('moyenne_generale')->take(5);
        
        // Convertir les résultats en tableau pour une meilleure lisibilité
        $resultatsArray = $resultats->values()->toArray();

        return response()->json([
            'success' => true,
            'data' => $resultatsArray
        ]);
    }

    public function statsParCycle($anneeScolaire)
    {
        try {
            // Charger les établissements avec leurs élèves et résultats
            $etablissements = Etablissement::with([
                'eleves' => function($query) use ($anneeScolaire) {
                    $query->with(['resultats' => function($q) use ($anneeScolaire) {
                        $q->select('code_eleve', 'MoyenSession', 'annee_scolaire')
                         ->where('annee_scolaire', $anneeScolaire);
                    }])
                    ->select('code_eleve', 'nom_eleve_ar', 'prenom_eleve_ar', 'code_etab')
                    ->whereHas('resultats', function($r) use ($anneeScolaire) {
                        $r->where('annee_scolaire', $anneeScolaire);
                    });
                }
            ])
            ->get();

            // Grouper par cycle et calculer les statistiques
            $resultats = $etablissements->groupBy('cycle')->map(function($group) use ($anneeScolaire) {

                $nombreEleves = 0;
                $sommeMoyennes = 0;
                $nombreMoyennes = 0;
                $nombreReussis = 0;

                foreach ($group as $etablissement) {
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
                    'cycle' => $group[0]->cycle,
                'nombre_eleves' => $nombreEleves,
                    'moyenne_generale' => round($moyenneGenerale, 2),
                    'taux_reussite' => round($tauxReussite, 2)
            ];
        });

            // Convertir en tableau pour une meilleure lisibilité
            $resultatsArray = $resultats->values()->toArray();

        return response()->json([
            'success' => true,
                'data' => $resultatsArray
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques par cycle',
                'error' => $e->getMessage()
            ], 500);
        }
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

  


   
}
