import React from 'react';
import { importData } from '../services/api';

const ImportDonnees = () => {
  const [file, setFile] = React.useState(null);
  const [importStatus, setImportStatus] = React.useState(null);

  const handleFileChange = (e) => {
    setFile(e.target.files[0]);
  };

  const handleImport = async () => {
    if (!file) {
      alert('Veuillez sélectionner un fichier');
      return;
    }

    const formData = new FormData();
    formData.append('file', file);

    try {
      setImportStatus('uploading');
      await importData(formData);
      setImportStatus('success');
      setTimeout(() => setImportStatus(null), 3000);
    } catch (error) {
      setImportStatus('error');
      setTimeout(() => setImportStatus(null), 3000);
      console.error('Error importing data:', error);
    }
  };

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-800">Import de Données</h1>

      <div className="bg-white p-6 rounded-lg shadow-lg">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Sélectionner un fichier</h2>
        <div className="space-y-4">
          <div>
            <input
              type="file"
              accept=".csv,.xlsx,.xls"
              onChange={handleFileChange}
              className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            />
          </div>
          <button
            onClick={handleImport}
            className="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700"
            disabled={!file}
          >
            {importStatus === 'uploading' ? 'Import en cours...' : 'Importer'}
          </button>
        </div>
      </div>

      {importStatus && (
        <div className={`fixed bottom-4 right-4 p-4 rounded-lg shadow-lg ${
          importStatus === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
        }`}>
          {importStatus === 'success' ? 'Import réussi !' : 'Erreur lors de l\'import'}
        </div>
      )}
    </div>
  );
};

export default ImportDonnees;
