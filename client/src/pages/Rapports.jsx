import React, { useState } from 'react';

const Rapports = () => {
  const [tab, setTab] = useState('available');

  return (
  <>      {/* Main Content */}
  <main className="flex-1 p-8 overflow-auto">
    <h1 className="text-5xl text-black font-bold mb-8">Rapports</h1>
    {/* Tabs */}
    <div className="flex mb-8 w-full max-w-2xl mx-auto">
      <div
        className={`flex-1 py-2 text-center cursor-pointer border border-gray-400 rounded-l-xl text-lg font-medium transition
          ${tab === 'available' ? 'bg-white text-black' : 'bg-gray-200 text-gray-600'}`}
        style={{
          borderRight: 'none',
          borderTopRightRadius: tab === 'available' ? 0 : '0.75rem',
          borderBottomRightRadius: tab === 'available' ? 0 : '0.75rem'
        }}
        onClick={() => setTab('available')}
      >
        Rapports disponibles
      </div>
      <div
        className={`flex-1 py-2 text-center cursor-pointer border border-gray-400 rounded-r-xl text-lg font-medium transition
          ${tab === 'generate' ? 'bg-white text-black' : 'bg-gray-200 text-gray-600'}`}
        style={{
          borderLeft: 'none',
          borderTopLeftRadius: tab === 'generate' ? 0 : '0.75rem',
          borderBottomLeftRadius: tab === 'generate' ? 0 : '0.75rem'
        }}
        onClick={() => setTab('generate')}
      >
        Générer un rapport
      </div>
    </div>
    {/* Content */}
    {tab === 'generate' ? (
      <div className="bg-white rounded-xl shadow-md p-8 max-w-3xl mx-auto">
        <div className="text-xl font-semibold mb-6 text-black">generateCustomReport</div>
        <div className="grid grid-cols-2 gap-6 mb-4">
          <div>
            <div className="font-semibold mb-2 text-black">reportType</div>
            <div className="flex flex-col gap-2">
              <label className="flex items-center gap-2">
                <input type="radio" name="reportType" defaultChecked />
                <span className='text-black'>comprehensiveReport</span>
              </label>
              <label className="flex items-center gap-2">
                <input type="radio" name="reportType" />
                <span className='text-black'>summaryReport</span>
              </label>
              <label className="flex items-center gap-2">
                <input type="radio" name="reportType" />
                <span className='text-black'>rawDataExport</span>
              </label>
            </div>
          </div>
          <div></div>
        </div>
        <div className="grid grid-cols-2 gap-6 mb-4">
          <div>
            <div className="font-semibold mb-2 text-black">timePeriod</div>
            <select className="w-full border border-black bg-gray-100 text-black rounded px-3 py-2">
              <option className='text-black'>2022</option>
              <option className='text-black'>2023</option>
              <option className='text-black'>2024</option>
              <option className='text-black'>2025</option>
            </select>
          </div>
          <div>
            <div className="font-semibold mb-2 text-black">schoolType</div>
            <select className="w-full border border-black bg-gray-100 text-black rounded px-3 py-2">
              <option className='text-black'>Tous les écoles</option>
              <option className='text-black'>Primaire</option>
              <option className='text-black'>Collège</option>
              <option className='text-black'>Lycée</option>
            </select>
          </div>
        </div>
        <div className="grid grid-cols-2 gap-6 mb-4">
          <div>
            <div className="font-semibold mb-2 text-black">includeInReport</div>
            <div className="flex flex-col gap-2">
              <label className="flex items-center gap-2 text-black">
                <input type="checkbox" defaultChecked /> performanceMetrics
              </label>
              <label className="flex items-center gap-2 text-black">
                <input type="checkbox" defaultChecked /> yearlyComparison
              </label>
              <label className="flex items-center gap-2 text-black">
                <input type="checkbox" defaultChecked /> Visualisations
              </label>
            </div>
          </div>
          <div>
            <div className="font-semibold mb-2">&nbsp;</div>
            <div className="flex flex-col gap-2 mt-2">
              <label className="flex items-center gap-2 text-black">
                <input type="checkbox" defaultChecked/> demographics
              </label>
              <label className="flex items-center gap-2 text-black">
                <input type="checkbox" defaultChecked/> Recommandations
              </label>
              <label className="flex items-center gap-2 text-black">
                <input type="checkbox" defaultChecked/> Tableaux de données
              </label>
            </div>
          </div>
        </div>
        <div className="mb-4">
          <div className="font-semibold mb-2 text-black">fileFormat</div>
          <div className="flex gap-4">
            <label className="flex items-center gap-2 text-black">
              <input type="radio" name="fileFormat" defaultChecked  /> PDF
            </label>
            <label className="flex items-center gap-2 text-black">
              <input type="radio" name="fileFormat"  /> Excel
            </label>
            <label className="flex items-center gap-2 text-black">
              <input type="radio" name="fileFormat" /> both
            </label>
          </div>
        </div>
        <button className="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold mt-4">generateReport</button>
      </div>
    ) : (
      <div className="bg-white rounded-xl shadow-md p-8 max-w-4xl mx-auto">
        <table className="w-full text-left">
          <thead>
            <tr className="border-b">
              <th className="py-2 text-black">reportName</th>
              <th className="py-2 text-black">date</th>
              <th className="py-2 text-black">type</th>
              <th className="py-2 text-black">size</th>
              <th className="py-2 text-black">actions</th>
            </tr>
          </thead>
          <tbody>
            <tr className="border-b hover:bg-gray-50">
              <td className="py-2 text-black">Rapport annuel 2023</td>
              <td className="py-2 text-black">15/12/2023</td>
              <td className="py-2 text-black">PDF</td>
              <td className="py-2 text-black">2.4 MB</td>
              <td className="py-2 text-black flex gap-2">
                <span className="cursor-pointer">👁️</span>
                <span className="cursor-pointer">⬇️</span>
              </td>
            </tr>
            <tr className="border-b hover:bg-gray-50">
              <td className="py-2 text-black">Statistiques trimestrielles Q4 2023</td>
              <td className="py-2 text-black">01/12/2023</td>
              <td className="py-2 text-black">Excel</td>
              <td className="py-2 text-black">1.8 MB</td>
              <td className="py-2 text-black flex gap-2">
                <span className="cursor-pointer">👁️</span>
                <span className="cursor-pointer">⬇️</span>
              </td>
            </tr>
            <tr className="border-b hover:bg-gray-50">
              <td className="py-2 text-black">Analyse comparative 2022-2023</td>
              <td className="py-2 text-black">20/11/2023</td>
              <td className="py-2 text-black">PDF</td>
              <td className="py-2 text-black">3.2 MB</td>
              <td className="py-2 text-black flex gap-2">
                <span className="cursor-pointer">👁️</span>
                <span className="cursor-pointer">⬇️</span>
              </td>
            </tr>
            <tr className="border-b hover:bg-gray-50">
              <td className="py-2 text-black">Performance par établissement</td>
              <td className="py-2 text-black">05/11/2023</td>
              <td className="py-2 text-black">PDF</td>
              <td className="py-2 text-black">5.1 MB</td>
              <td className="py-2 text-black flex gap-2">
                <span className="cursor-pointer">👁️</span>
                <span className="cursor-pointer">⬇️</span>
              </td>
            </tr>
            <tr className="hover:bg-gray-50">
              <td className="py-2 text-black">Données brutes 2023</td>
              <td className="py-2 text-black">01/10/2023</td>
              <td className="py-2 text-black">Excel</td>
              <td className="py-2 text-black">7.6 MB</td>
              <td className="py-2 text-black flex gap-2">
                <span className="cursor-pointer">👁️</span>
                <span className="cursor-pointer">⬇️</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    )}
  </main>
  </>

  );
};

export default Rapports;