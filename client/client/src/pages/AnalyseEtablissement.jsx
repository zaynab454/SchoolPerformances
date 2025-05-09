import React from 'react';
import { getAnalysisData } from '../services/api';

const AnalyseEtablissement = () => {
  const [etablissements, setEtablissements] = React.useState([]);
  const [selectedEtablissement, setSelectedEtablissement] = React.useState(null);
  const [etablissementData, setEtablissementData] = React.useState(null);

  React.useEffect(() => {
    const fetchEtablissements = async () => {
      try {
        const data = await getAnalysisData();
        setEtablissements(data.etablissements);
      } catch (error) {
        console.error('Error fetching etablissements:', error);
      }
    };

    fetchEtablissements();
  }, []);

  const handleEtablissementSelect = async (etablissement) => {
    setSelectedEtablissement(etablissement);
    try {
      const data = await getAnalysisData(`/${etablissement}`);
      setEtablissementData(data);
    } catch (error) {
      console.error('Error fetching etablissement data:', error);
    }
  };

  if (!etablissements.length) {
    return <div className="flex items-center justify-center h-screen">Chargement...</div>;
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-800">Analyse par Établissement</h1>

      {/* Sélection d'établissement */}
      <div className="bg-white p-6 rounded-lg shadow-lg">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Sélectionner un Établissement</h2>
        <select
          className="w-full p-2 border border-gray-300 rounded-md"
          value={selectedEtablissement || ''}
          onChange={(e) => handleEtablissementSelect(e.target.value)}
        >
          <option value="">Sélectionner un établissement</option>
          {etablissements.map((etab) => (
            <option key={etab.id} value={etab.id}>
              {etab.nom}
            </option>
          ))}
        </select>
      </div>

      {selectedEtablissement && etablissementData && (
        <div className="space-y-6">
          {/* Statistiques de l'établissement */}
          <div className="bg-white p-6 rounded-lg shadow-lg">
            <h2 className="text-lg font-semibold text-gray-800 mb-4">Statistiques</h2>
            <div className="grid grid-cols-2 gap-6">
              <div>
                <h3 className="text-gray-600">Nombre d'Étudiants</h3>
                <p className="text-2xl font-bold text-blue-600">{etablissementData.totalStudents}</p>
              </div>
              <div>
                <h3 className="text-gray-600">Moyenne Générale</h3>
                <p className="text-2xl font-bold text-green-600">{etablissementData.moyenneGenerale}/20</p>
              </div>
              <div>
                <h3 className="text-gray-600">Taux de Réussite</h3>
                <p className="text-2xl font-bold text-purple-600">{etablissementData.tauxReussite}%</p>
              </div>
            </div>
          </div>

          {/* Répartition par filière */}
          <div className="bg-white p-6 rounded-lg shadow-lg">
            <h2 className="text-lg font-semibold text-gray-800 mb-4">Répartition par Filière</h2>
            <div className="space-y-4">
              {Object.entries(etablissementData.filiere).map(([filiere, count]) => (
                <div key={filiere} className="flex justify-between items-center">
                  <span className="text-gray-700">{filiere}</span>
                  <span className="font-semibold text-blue-600">{count}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default AnalyseEtablissement;
