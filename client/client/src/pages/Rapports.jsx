import React from 'react';
import { getAnalysisData } from '../services/api';

const Rapports = () => {
  const [rapports, setRapports] = React.useState([]);
  const [loading, setLoading] = React.useState(true);
  const [selectedRapport, setSelectedRapport] = React.useState(null);

  React.useEffect(() => {
    const fetchRapports = async () => {
      try {
        const data = await getAnalysisData('/rapports');
        setRapports(data);
        setLoading(false);
      } catch (error) {
        console.error('Error fetching rapports:', error);
        setLoading(false);
      }
    };

    fetchRapports();
  }, []);

  if (loading) {
    return (
      <div className="flex items-center justify-center h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-800">Rapports</h1>

      {/* Liste des rapports */}
      <div className="bg-white p-6 rounded-lg shadow-lg">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Liste des rapports</h2>
        <div className="space-y-4">
          {rapports.map((rapport) => (
            <div
              key={rapport.id}
              className="p-4 border rounded-lg hover:bg-gray-50 cursor-pointer"
              onClick={() => setSelectedRapport(rapport)}
            >
              <h3 className="font-semibold text-gray-800">{rapport.titre}</h3>
              <p className="text-gray-600 text-sm">{rapport.date}</p>
            </div>
          ))}
        </div>
      </div>

      {/* Détails du rapport sélectionné */}
      {selectedRapport && (
        <div className="bg-white p-6 rounded-lg shadow-lg">
          <h2 className="text-lg font-semibold text-gray-800 mb-4">Détails du rapport</h2>
          <div className="space-y-4">
            <div>
              <h3 className="font-semibold text-gray-800">Titre</h3>
              <p className="text-gray-600">{selectedRapport.titre}</p>
            </div>
            <div>
              <h3 className="font-semibold text-gray-800">Date</h3>
              <p className="text-gray-600">{selectedRapport.date}</p>
            </div>
            <div>
              <h3 className="font-semibold text-gray-800">Statistiques</h3>
              <div className="grid grid-cols-2 gap-6">
                <div>
                  <h4 className="text-gray-600">Nombre d'Étudiants</h4>
                  <p className="text-2xl font-bold text-blue-600">{selectedRapport.stats.totalStudents}</p>
                </div>
                <div>
                  <h4 className="text-gray-600">Moyenne Générale</h4>
                  <p className="text-2xl font-bold text-green-600">{selectedRapport.stats.moyenneGenerale}/20</p>
                </div>
                <div>
                  <h4 className="text-gray-600">Taux de Réussite</h4>
                  <p className="text-2xl font-bold text-purple-600">{selectedRapport.stats.tauxReussite}%</p>
                </div>
              </div>
            </div>
            <div>
              <h3 className="font-semibold text-gray-800">Répartition par Filière</h3>
              <div className="space-y-4">
                {Object.entries(selectedRapport.stats.filiere).map(([filiere, count]) => (
                  <div key={filiere} className="flex justify-between items-center">
                    <span className="text-gray-700">{filiere}</span>
                    <span className="font-semibold text-blue-600">{count}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Rapports;
