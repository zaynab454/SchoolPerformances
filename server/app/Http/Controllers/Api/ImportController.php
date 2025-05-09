<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Etablissement;
use App\Models\Eleve;
use App\Models\ResultatEleve;
use App\Models\Commune;
use App\Models\Matiere;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class ImportController extends Controller
{
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

            DB::beginTransaction();
            $imported = 0;
            $errors = [];

            // Traiter le fichier selon son extension
            switch ($extension) {
                case 'csv':
                    $this->processCSV($file, $imported, $errors);
                    break;
                case 'xml':
                    $this->processXML($file, $imported, $errors);
                    break;
                case 'xlsx':
                case 'xls':
                    $this->processExcel($file, $imported, $errors);
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Importation terminée avec succès. $imported lignes importées.",
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

    private function processCSV($file, &$imported, &$errors)
    {
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle, 1000, ',');

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            try {
                $row = array_combine($header, $data);
                $this->processRow($row, $imported);
            } catch (\Exception $e) {
                $errors[] = "Erreur à la ligne " . ($imported + 1) . ": " . $e->getMessage();
                Log::error("Erreur d'importation CSV: " . $e->getMessage());
            }
        }

        fclose($handle);
    }

    private function processXML($file, &$imported, &$errors)
    {
        $xml = new SimpleXMLElement($file->getPathname(), 0, true);

        foreach ($xml->children() as $row) {
            try {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$key] = (string)$value;
                }
                $this->processRow($data, $imported);
            } catch (\Exception $e) {
                $errors[] = "Erreur à la ligne " . ($imported + 1) . ": " . $e->getMessage();
                Log::error("Erreur d'importation XML: " . $e->getMessage());
            }
        }
    }

    private function processExcel($file, &$imported, &$errors)
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
                $this->processRow($data, $imported);
            } catch (\Exception $e) {
                $errors[] = "Erreur à la ligne " . ($imported + 1) . ": " . $e->getMessage();
                Log::error("Erreur d'importation Excel: " . $e->getMessage());
            }
        }
    }

    private function processRow($row, &$imported)
    {
        // Créer ou récupérer la commune
        $commune = Commune::firstOrCreate(
            ['la_com' => $row['la_com']],
            [
                'la_com' => $row['la_com'],
                'll_com' => $row['la_com'],
                'cd_com' => str_pad($imported + 1, 5, '0', STR_PAD_LEFT)
            ]
        );

        // Créer ou récupérer l’établissement
        $etablissement = Etablissement::firstOrCreate(
            ['code_etab' => $row['CD_ETAB']],
            [
                'nom_etab' => $row['NOM_ETABA'],
                'code_commune' => $commune->cd_com
            ]
        );

        // Créer ou récupérer l’élève
        $eleve = Eleve::firstOrCreate(
            ['code_eleve' => $row['codeEleve']],
            [
                'code_eleve' => $row['codeEleve'],
                'nom_eleve_ar' => $row['nomEleveAr'],
                'prenom_eleve_ar' => $row['prenomEleveAr'],
                'code_etab' => $row['CD_ETAB'],
                'code_niveau' => $row['Suffix']
            ]
        );

        // Créer ou récupérer la matière
        $matiere = Matiere::firstOrCreate(
            ['nom_matiere' => $row['MatiereAr']],
            [
                'nom_matiere' => $row['MatiereAr'],
                'nom_colonne' => strtolower(str_replace(' ', '_', $row['MatiereAr']))
            ]
        );

        // Créer ou mettre à jour le résultat
        ResultatEleve::updateOrCreate(
            [
                'code_eleve' => $eleve->code_eleve,
                'id_matiere' => $matiere->id_matiere,
                'annee_scolaire' => date('Y') . '-' . (date('Y') + 1)
            ],
            [
                'MoyenNoteCC' => $row['MoyenneNoteCC_Note'],
                'MoyenExamenNote' => $row['NoteExamen_Note'],
                'MoyenCC' => $row['MoynneCC'],
                'MoyenExam' => $row['MoyenneExam'],
                'MoyenSession' => $row['MoyenneSession']
            ]
        );

        $imported++;
    }
}
