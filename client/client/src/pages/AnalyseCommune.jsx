import React from 'react';
import { getAnalysisData } from '../services/api';

const AnalyseCommune = () => {
  const [analysisData, setAnalysisData] = React.useState(null);

  React.useEffect(() => {
    const fetchAnalysis = async () => {
      try {
        const data = await getAnalysisData();
        setAnalysisData(data);
      } catch (error) {
        console.error('Error fetching analysis data:', error);
      }
    };

    fetchAnalysis();
  }, []);

  if (!analysisData) {
    return <div className="flex items-center justify-center h-screen">Chargement...</div>;
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-800">Analyse Commune</h1>

      {/* Répartition par filière */}
      <div className="bg-white p-6 rounded-lg shadow-lg">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Répartition par Filière</h2>
        <div className="space-y-4">
          {Object.entries(analysisData.filiere).map(([filiere, count]) => (
            <div key={filiere} className="flex justify-between items-center">
              <span className="text-gray-700">{filiere}</span>
              <span className="font-semibold text-blue-600">{count}</span>
            </div>
          ))}
        </div>
      </div>

      {/* Répartition par niveau */}
      <div className="bg-white p-6 rounded-lg shadow-lg">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Répartition par Niveau</h2>
        <div className="space-y-4">
          {Object.entries(analysisData.niveau).map(([niveau, count]) => (
            <div key={niveau} className="flex justify-between items-center">
              <span className="text-gray-700">{niveau}</span>
              <span className="font-semibold text-blue-600">{count}</span>
            </div>
          ))}
        </div>
      </div>

      {/* Statistiques générales */}
      <div className="bg-white p-6 rounded-lg shadow-lg">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Statistiques Générales</h2>
        <div className="grid grid-cols-2 gap-6">
          <div>
            <h3 className="text-gray-600">Moyenne Générale</h3>
            <p className="text-2xl font-bold text-green-600">{analysisData.moyenneGenerale}/20</p>
          </div>
          <div>
            <h3 className="text-gray-600">Taux de Réussite</h3>
            <p className="text-2xl font-bold text-purple-600">{analysisData.tauxReussite}%</p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AnalyseCommune;
