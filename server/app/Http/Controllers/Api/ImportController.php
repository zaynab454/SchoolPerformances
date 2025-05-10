<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Etablissement;
use App\Models\Eleve;
use App\Models\ResultatEleve;
use App\Models\Commune;
use App\Models\Matiere;
use App\Models\AnneeScolaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    /**
     * Get all academic years
     */
    public function getAnneesScolaires()
    {
        $annees = AnneeScolaire::orderBy('annee_scolaire', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $annees
        ]);
    }

    public function importResultats(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun fichier n\'a été uploadé'
                ], 400);
            }

            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());

            // Vérifier le type de fichier
            if (!in_array($extension, ['csv', 'xml', 'xlsx', 'xls'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format de fichier non supporté. Formats acceptés : CSV, XML, Excel'
                ], 400);
            }

            // Vérifier l'année scolaire active
            $anneeScolaire = AnneeScolaire::where('est_courante', true)->first();
            if (!$anneeScolaire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune année scolaire active n\'est définie. Veuillez sélectionner une année scolaire avant d\'importer les données.'
                ], 400);
            }

            // Vérifier si l'année scolaire est spécifiée dans la requête
            if ($request->has('annee_scolaire')) {
                $anneeSpecifiee = AnneeScolaire::where('annee_scolaire', $request->annee_scolaire)->first();
                if (!$anneeSpecifiee) {
                    return response()->json([
                        'success' => false,
                        'message' => 'L\'année scolaire spécifiée n\'existe pas'
                    ], 400);
                }
                $anneeScolaire = $anneeSpecifiee;
            }

            DB::beginTransaction();
            $imported = 0;
            $errors = [];

            // Traiter le fichier selon son extension
            switch ($extension) {
                case 'csv':
                    $this->processCSV($file, $imported, $errors, $anneeScolaire);
                    break;
                case 'xml':
                    $this->processXML($file, $imported, $errors, $anneeScolaire);
                    break;
                case 'xlsx':
                case 'xls':
                    $this->processExcel($file, $imported, $errors, $anneeScolaire);
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Importation terminée avec succès. $imported lignes importées pour l'année scolaire {$anneeScolaire->annee_scolaire}.",
                'data' => [
                    'annee_scolaire' => $anneeScolaire->annee_scolaire,
                    'lignes_importees' => $imported
                ],
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur d'importation: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => "Erreur lors de l'importation: " . $e->getMessage()
            ], 500);
        }
    }

    private function processCSV($file, &$imported, &$errors, $anneeScolaire)
    {
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle, 1000, ',');

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            try {
                $row = array_combine($header, $data);
                $this->processRow($row, $imported, $anneeScolaire);
            } catch (\Exception $e) {
                $errors[] = "Erreur à la ligne " . ($imported + 1) . ": " . $e->getMessage();
                Log::error("Erreur d'importation CSV: " . $e->getMessage());
            }
        }

        fclose($handle);
    }

    private function processXML($file, &$imported, &$errors, $anneeScolaire)
    {
        $xml = new SimpleXMLElement($file->getPathname(), 0, true);

        foreach ($xml->children() as $row) {
            try {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$key] = (string)$value;
                }
                $this->processRow($data, $imported, $anneeScolaire);
            } catch (\Exception $e) {
                $errors[] = "Erreur à la ligne " . ($imported + 1) . ": " . $e->getMessage();
                Log::error("Erreur d'importation XML: " . $e->getMessage());
            }
        }
    }

    private function processExcel($file, &$imported, &$errors, $anneeScolaire)
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getPathname());
        $spreadsheet = $reader->load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $header = [];

        // Lire l'en-tête
        foreach ($worksheet->getRowIterator(1, 1) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ($cellIterator as $cell) {
                $header[] = $cell->getValue();
            }
        }

        // Lire les données
        foreach ($worksheet->getRowIterator(2) as $row) {
            try {
                $data = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $i = 0;
                foreach ($cellIterator as $cell) {
                    $data[$header[$i]] = $cell->getValue();
                    $i++;
                }
                $this->processRow($data, $imported, $anneeScolaire);
            } catch (\Exception $e) {
                $errors[] = "Erreur à la ligne " . ($imported + 1) . ": " . $e->getMessage();
                Log::error("Erreur d'importation Excel: " . $e->getMessage());
            }
        }
    }

    private function processRow($row, &$imported, $anneeScolaire)
    {
        // Validation des données requises
        $requiredFields = ['code_etab', 'nom_etab_fr', 'nom_etab_ar', 'code_commune', 'code_niveau', 'nom_eleve_ar', 'prenom_eleve_ar', 'MoyenSession'];
        foreach ($requiredFields as $field) {
            if (!isset($row[$field]) || empty($row[$field])) {
                throw new \Exception("Le champ $field est requis");
            }
        }

        // Créer ou récupérer la commune avec un code unique
        $codeCommune = uniqid('COM_');
        $commune = Commune::firstOrCreate([
            'cd_com' => $codeCommune
        ], [
            'la_com' => $row['nom_commune_ar'],
            'll_com' => $row['الاسم_الفرنسي'],
            'id_province' => $row['id_province']
        ]);

        // Créer ou récupérer l'établissement
        $etablissement = Etablissement::firstOrCreate([
            'code_etab' => $row['code_etab']
        ], [
            'nom_etab_fr' => $row['nom_etab_fr'],
            'nom_etab_ar' => $row['nom_etab_ar'],
            'code_commune' => $commune->cd_com,
            'cycle' => $row['cycle']
        ]);

        // Générer un code élève unique
        $codeEleve = uniqid('ELE_');

        // Créer ou récupérer l'élève
        $eleve = Eleve::firstOrCreate([
            'code_eleve' => $codeEleve
        ], [
            'nom_eleve_ar' => $row['nom_eleve_ar'],
            'prenom_eleve_ar' => $row['prenom_eleve_ar'],
            'code_etab' => $etablissement->code_etab,
            'code_niveau' => $row['code_niveau']
        ]);

        // Créer ou mettre à jour le résultat pour l'année scolaire spécifiée
        ResultatEleve::updateOrCreate(
            [
                'code_eleve' => $eleve->code_eleve,
                'annee_scolaire' => $anneeScolaire->annee_scolaire
            ],
            [
                'MoyenSession' => $row['MoyenSession']
            ]
        );

        $imported++;
    }
}
