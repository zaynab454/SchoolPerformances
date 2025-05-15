import React, { useState } from 'react';
import { BarChart, Bar, PieChart, Pie, Cell, LineChart, Line, XAxis, YAxis, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const barDataNiveau = [
  { niveau: 'Primaire', moyenne: 12 },
  { niveau: 'Collégial', moyenne: 14 },
  { niveau: 'Qualifiant', moyenne: 16 },
];
const barDataMatiere = [
  { matiere: 'Math', moyenne: 13 },
  { matiere: 'Physique', moyenne: 15 },
  { matiere: 'SVT', moyenne: 14 },
];
const pieDataNiveau = [
  { name: 'note entre 8 et 10', value: 25 },
  { name: 'note entre 10 et 12', value: 25 },
  { name: 'note entre 12 et 14', value: 25 },
  { name: 'note entre 14 et 20', value: 25 },
];
const pieDataMatiere = [
  { name: 'note entre 8 et 10', value: 20 },
  { name: 'note entre 10 et 12', value: 25 },
  { name: 'note entre 12 et 14', value: 25 },
  { name: 'note entre 14 et 20', value: 30 },
];
const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042'];
const lineData = [
  { year: '2023-2024', value: 13.5 },
  { year: '2024-2025', value: 14.2 },
  { year: '2025-2026', value: 14.5 },
];
const tauxData = [
  { year: '2023-2024', taux: 80 },
  { year: '2022-2023', taux: 75 },
  { year: '2021-2022', taux: 70 },
  { year: '2020-2021', taux: 65 },
];
const tableData = [
  { id: 1, etablissement: 'Lycée Victor Hugo', eleves: 1200, moyenne: 15 },
  { id: 2, etablissement: 'Lycée Victor Hugo', eleves: 1100, moyenne: 14.5 },
  { id: 3, etablissement: 'Lycée Victor Hugo', eleves: 1050, moyenne: 14 },
  { id: 4, etablissement: 'Lycée Victor Hugo', eleves: 1000, moyenne: 13.5 },
  { id: 5, etablissement: 'Lycée Victor Hugo', eleves: 950, moyenne: 13 },
];

const AnalyseEtablissement = () => {
  const [tab, setTab] = useState('niveau');
  return (
    <main className="flex-1 p-8 overflow-auto bg-gray-100 min-h-screen">
      <div className="flex flex-col gap-6">
        {/* Filtres */}
        <div className="flex gap-2 mb-2">
          <button className="border rounded-full px-4 py-1 bg-white">choisir annees</button>
          <button className="border rounded-full px-4 py-1 bg-white">choisir une commune</button>
          <button className="border rounded-full px-4 py-1 bg-white">choisir un etablissement</button>
          <button className="border rounded-full px-4 py-1 bg-white">choisir une annee</button>
          <button className="border rounded-full px-4 py-1 bg-white">filtre</button>
        </div>
        {/* Statistiques */}
        <div className="grid grid-cols-5 gap-4">
          <div className="bg-white rounded-xl border p-4 flex flex-col items-center justify-center">
            <div className="text-sm text-gray-600">rang par province</div>
            <div className="text-2xl font-bold">20</div>
          </div>
          <div className="bg-white rounded-xl border p-4 flex flex-col items-center justify-center">
            <div className="text-sm text-gray-600">Moyenne générale</div>
            <div className="text-2xl font-bold">14.2 / 20</div>
            <div className="text-xs text-gray-500">Tous établissements confondus</div>
          </div>
          <div className="bg-white rounded-xl border p-4 flex flex-col items-center justify-center">
            <div className="text-sm text-gray-600">taux de reussie</div>
            <div className="text-2xl font-bold">80%</div>
            <div className="text-xs text-gray-500">Tous établissements confondus</div>
          </div>
          <div className="bg-white rounded-xl border p-4 flex flex-col items-center justify-center">
            <div className="text-sm text-gray-600">teau d'check</div>
            <div className="text-2xl font-bold">20%</div>
            <div className="text-xs text-gray-500">Tous établissements confondus</div>
          </div>
          <div className="bg-white rounded-xl border p-4 flex flex-col items-center justify-center">
            <div className="text-sm text-gray-600">cycle</div>
            <div className="text-2xl font-bold">collégial</div>
          </div>
        </div>
        {/* Onglets */}
        <div className="flex gap-2 mt-4">
          <button
            className={`px-4 py-1 rounded-t-lg font-semibold border ${tab === 'niveau' ? 'bg-gray-200' : 'bg-white'}`}
            onClick={() => setTab('niveau')}
          >
            par niveau
          </button>
          <button
            className={`px-4 py-1 rounded-t-lg border ${tab === 'matiere' ? 'bg-gray-200' : 'bg-white'}`}
            onClick={() => setTab('matiere')}
          >
            par matiere
          </button>
          <button
            className={`px-4 py-1 rounded-t-lg border ${tab === 'annuelle' ? 'bg-gray-200' : 'bg-white'}`}
            onClick={() => setTab('annuelle')}
          >
            evaluation annuelle
          </button>
        </div>
        {/* Onglets dynamiques */}
        {tab === 'niveau' && (
          <>
            <div className="grid grid-cols-2 gap-6 mt-2">
              <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
                <div className="font-semibold mb-2">moyenne par niveau</div>
                <ResponsiveContainer width="100%" height={180}>
                  <BarChart data={barDataNiveau}>
                    <XAxis dataKey="niveau" />
                    <YAxis />
                    <Tooltip />
                    <Bar dataKey="moyenne" fill="#8884d8" />
                  </BarChart>
                </ResponsiveContainer>
              </div>
              <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
                <div className="font-semibold mb-2">taux de reussi par niveau</div>
                <ResponsiveContainer width="100%" height={180}>
                  <BarChart data={barDataNiveau} layout="vertical">
                    <XAxis type="number" />
                    <YAxis dataKey="niveau" type="category" />
                    <Tooltip />
                    <Bar dataKey="moyenne" fill="#00C49F" />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </div>
            <div className="grid grid-cols-2 gap-6 mt-2">
              <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
                <div className="font-semibold mb-2">repartition des note par niveau</div>
                <ResponsiveContainer width="100%" height={180}>
                  <PieChart>
                    <Pie data={pieDataNiveau} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={60} label>
                      {pieDataNiveau.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                      ))}
                    </Pie>
                    <Tooltip />
                    <Legend />
                  </PieChart>
                </ResponsiveContainer>
              </div>
              <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
                <div className="font-semibold mb-2">détail des notes par niveau</div>
                <ul className="list-disc ml-6 text-sm text-gray-700">
                  <li>note entre 8 et 10 : 25%</li>
                  <li>note entre 10 et 12 : 25%</li>
                  <li>note entre 12 et 14 : 25%</li>
                  <li>note entre 14 et 20 : 25%</li>
                </ul>
              </div>
            </div>
          </>
        )}
        {tab === 'matiere' && (
          <>
            <div className="grid grid-cols-2 gap-6 mt-2">
              <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
                <div className="font-semibold mb-2">moyenne par matiere</div>
                <ResponsiveContainer width="100%" height={180}>
                  <BarChart data={barDataMatiere}>
                    <XAxis dataKey="matiere" />
                    <YAxis />
                    <Tooltip />
                    <Bar dataKey="moyenne" fill="#8884d8" />
                  </BarChart>
                </ResponsiveContainer>
              </div>
              <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
                <div className="font-semibold mb-2">taux de reussi par matiere</div>
                <ResponsiveContainer width="100%" height={180}>
                  <BarChart data={barDataMatiere} layout="vertical">
                    <XAxis type="number" />
                    <YAxis dataKey="matiere" type="category" />
                    <Tooltip />
                    <Bar dataKey="moyenne" fill="#00C49F" />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </div>
            <div className="grid grid-cols-2 gap-6 mt-2">
              <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
                <div className="font-semibold mb-2">repartition des note par matiere</div>
                <ResponsiveContainer width="100%" height={180}>
                  <PieChart>
                    <Pie data={pieDataMatiere} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={60} label>
                      {pieDataMatiere.map((entry, index) => (
                        <Cell key={`cell2-${index}`} fill={COLORS[index % COLORS.length]} />
                      ))}
                    </Pie>
                    <Tooltip />
                    <Legend />
                  </PieChart>
                </ResponsiveContainer>
              </div>
              <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
                <div className="font-semibold mb-2">détail des notes par matiere</div>
                <ul className="list-disc ml-6 text-sm text-gray-700">
                  <li>note entre 8 et 10 : 20%</li>
                  <li>note entre 10 et 12 : 25%</li>
                  <li>note entre 12 et 14 : 25%</li>
                  <li>note entre 14 et 20 : 30%</li>
                </ul>
              </div>
            </div>
          </>
        )}
        {tab === 'annuelle' && (
          <>
            <div className="grid grid-cols-2 gap-6 mt-2">
              <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
                <div className="font-semibold mb-2">evaluation de moyenne par anne</div>
                <ResponsiveContainer width="100%" height={120}>
                  <LineChart data={lineData}>
                    <XAxis dataKey="year" />
                    <YAxis />
                    <Tooltip />
                    <Line type="monotone" dataKey="value" stroke="#8884d8" />
                  </LineChart>
                </ResponsiveContainer>
              </div>
              <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
                <div className="font-semibold mb-2">evaluation de taux de reussi par anne</div>
                <ResponsiveContainer width="100%" height={120}>
                  <BarChart data={tauxData} layout="vertical">
                    <XAxis type="number" />
                    <YAxis dataKey="year" type="category" />
                    <Tooltip />
                    <Bar dataKey="taux" fill="#00C49F" />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </div>
            <div className="mt-6">
              <div className="font-semibold mb-2">Observations clés</div>
              <ul className="list-disc ml-6 text-sm text-gray-700">
                <li>Augmentation constante du taux de réussite depuis 2016 (de 58% à 78%)</li>
                <li>Légère baisse en 2020 due à la pandémie (de 68% à 64%)</li>
                <li>Reprise rapide post-pandémie avec une croissance accélérée (de 64% à 70% en un an)</li>
              </ul>
            </div>
          </>
        )}
      </div>
    </main>
  );
};

export default AnalyseEtablissement;