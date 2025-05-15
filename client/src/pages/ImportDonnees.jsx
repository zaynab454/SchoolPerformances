import React from 'react';
import { FaDownload } from 'react-icons/fa';

const ImportDonnees = () => {
  return (
<>
<main className="flex-1 p-8 bg-gray-50 min-h-screen">
        <h1 className="text-3xl font-bold mb-6 text-black">Importation des résultats</h1>
        <div className="flex gap-6">
          {/* Import Box */}
          <div className="bg-white rounded-xl border p-6 flex flex-col gap-4 w-[430px]">
            <div className="flex gap-2 items-center">
              <button className="bg-gray-100 px-4 py-1 rounded-full text-base font-semibold border">2024-2025</button>
              <button className="bg-[#2d2c5a] text-white px-4 py-1 rounded-full text-base font-semibold">ajouter une annes</button>
            </div>
            <div className="border border-blue-300 rounded-lg flex flex-col items-center justify-center py-8 px-4 text-center">
              <div className="text-6xl mb-2">
                ⬇️         
              </div>
              <div className="text-gray-700 text-base">
                Glissez-déposez un fichier (Excel, CSV ou XML) ou cliquez pour sélectionner<br />
                <span className="text-sm text-gray-500">Formats acceptés: .xlsx, .xls, .csv, .xml</span>
              </div>
            </div>
          </div>
          {/* Instructions */}
          <div className="flex flex-col gap-4 w-[370px]">
            <div className="bg-white rounded-xl border p-4">
              <div className="font-semibold mb-1 text-start text-black">Format du fichier</div>
              <div className="text-sm text-start text-gray-500">Le fichier doit être au format CSV avec les colonnes suivantes : Matricule, Nom, Prénom, Matière, Note</div>
            </div>
            <div className="bg-white rounded-xl border p-4">
              <div className="font-semibold mb-1 text-start text-black">Sélection de l'année</div>
              <div className="text-sm text-start text-gray-500">Choisissez l'année scolaire pour laquelle vous souhaitez importer les résultats</div>
            </div>
            <div className="bg-white rounded-xl border p-4">
              <div className="font-semibold mb-1 text-start text-black">Validation</div>
              <div className="text-sm text-start text-gray-500">Les données seront validées avant l'import. Vous recevrez un rapport détaillé des erreurs éventuelles</div>
            </div>
          </div>
        </div>
        {/* Historique des imports */}
        <div className="bg-white rounded-xl border p-6 mt-8">
          <div className="font-semibold mb-2 text-start text-black">Historique des imports</div>
          {/* Ici vous pouvez ajouter la liste des imports précédents */}
          <br />
          <br />
          <br />
          <br />

        </div>
      </main>
      </>      
  );
};

export default ImportDonnees;