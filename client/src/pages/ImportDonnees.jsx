import React from 'react';

const ImportDonnees = () => {
  return (
<>
<main className="flex-1 p-8">
        <h1 className="text-2xl font-bold mb-6">Importation des résultats</h1>
        <div className="flex gap-6">
          {/* Import Box */}
          <div className="bg-white rounded-xl shadow-md p-6 flex flex-col gap-4 w-[420px]">
            <div className="flex gap-2 items-center">
              <button className="bg-gray-200 px-4 py-1 rounded-full text-sm font-semibold">2024-2025</button>
              <button className="bg-[#2d2c5a] text-white px-4 py-1 rounded-full text-sm font-semibold">ajouter une annes</button>
            </div>
            <div className="border-2 border-blue-300 rounded-lg flex flex-col items-center justify-center py-8 px-4 text-center">
              <div className="text-5xl mb-2">⬇️</div>
              <div className="text-gray-700">
                Glissez-déposez un fichier (Excel, CSV ou XML) ou cliquez pour sélectionner<br />
                Formats acceptés: .xlsx, .xls, .csv, .xml
              </div>
            </div>
          </div>
          {/* Instructions */}
          <div className="flex flex-col gap-4 w-[350px]">
            <div className="bg-white rounded-xl shadow-md p-4">
              <div className="font-semibold">Format du fichier</div>
              <div className="text-sm">Le fichier doit être au format CSV avec les colonnes suivantes : Matricule, Nom, Prénom, Matière, Note</div>
            </div>
            <div className="bg-white rounded-xl shadow-md p-4">
              <div className="font-semibold">Sélection de l'année</div>
              <div className="text-sm">Choisissez l'année scolaire pour laquelle vous souhaitez importer les résultats</div>
            </div>
            <div className="bg-white rounded-xl shadow-md p-4">
              <div className="font-semibold">Validation</div>
              <div className="text-sm">Les données seront validées avant l'import. Vous recevrez un rapport détaillé des erreurs éventuelles</div>
            </div>
          </div>
        </div>
        {/* Historique des imports */}
        <div className="bg-white rounded-xl shadow-md p-6 mt-8">
          <div className="font-semibold mb-2">Historique des imports</div>
        </div>
      </main>
      </>      
  );
};

export default ImportDonnees;