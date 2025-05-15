import React, { useState } from 'react';
import { BarChart, Bar, PieChart, Pie, Cell, LineChart, Line, XAxis, YAxis, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const barData = [
  { cycle: 'Primaire', moyenne: 12 },
  { cycle: 'Collégial', moyenne: 9 },
  { cycle: 'Qualifiant', moyenne: 15 },
];
const pieData = [
  { name: 'Primaire', value: 25 },
  { name: 'Collégial', value: 70 },
  { name: 'Qualifiant', value: 60 },
];
const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042'];
const lineData = [
  { year: '2023-2024', value: 12.5 },
  { year: '2024-2025', value: 10 },
  { year: '2025-2026', value: 15.25 },
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

const AnalyseCommune = () => {
  return (
    <main className="flex-1 p-8 overflow-auto bg-gray-100 min-h-screen">
      <div className="flex flex-col gap-6">
        {/* Filtres */}
        <div className="flex gap-2 mb-2">
          <button className="border rounded-full px-4 py-1 bg-white">choisir annees</button>
          <button className="border rounded-full px-4 py-1 bg-white">choisir une commune</button>
          <button className="border rounded-full px-4 py-1 bg-white">choisir un etablissement</button>
          <button className="border rounded-full px-4 py-1 mr-7 bg-white">choisir une annee</button>
          <button className="border rounded-full px-4 py-1 ml-5 bg-white">filtre</button>
        </div>
        {/* Statistiques */}
        <div className="grid grid-cols-4 gap-4">
          <div className="bg-white rounded-xl border p-4 flex flex-col items-center justify-center">
            <div className="text-sm text-gray-600">rang par province</div>
            <div className="text-2xl text-black font-bold">20</div>
          </div>
          <div className="bg-white rounded-xl border p-4 flex flex-col items-center justify-center">
            <div className="text-sm text-gray-600">Moyenne générale</div>
            <div className="text-2xl font-bold">19/20</div>
            <div className="text-xs text-gray-500">Élèves ayant validé leur année</div>
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
        </div>
        {/* Diagrammes principaux */}
        <div className="grid grid-cols-2 gap-4 mt-2">
          <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
            <div className="font-semibold mb-2">Répartition des établissements par cycle</div>
            <ResponsiveContainer width="100%" height={180}>
              <PieChart>
                <Pie data={pieData} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={60} label>
                  {pieData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip />
                <Legend />
              </PieChart>
            </ResponsiveContainer>
          </div>
          <div className="bg-white rounded-xl border p-4 flex flex-col items-center justify-center">
            <div className="font-semibold mb-2">Carte des communes</div>
            <img src="/images/p-o.png" alt="Carte Maroc" className="h-60 w-3xl" />
          </div>
        </div>
        {/* Moyenne par cycle et taux de reussite */}
        <div className="grid grid-cols-2 gap-4 mt-4">
          <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
            <div className="font-semibold mb-2">moyenne par cycle</div>
            <ResponsiveContainer width="100%" height={150}>
              <BarChart data={barData}>
                <XAxis dataKey="cycle" />
                <YAxis />
                <Tooltip />
                <Bar dataKey="moyenne" fill="#8884d8" />
              </BarChart>
            </ResponsiveContainer>
          </div>
          <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
            <div className="font-semibold mb-2">teu de reussi cycle</div>
            <ResponsiveContainer width="100%" height={180}>
              <PieChart>
                <Pie data={pieData} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={60} label>
                  {pieData.map((entry, index) => (
                    <Cell key={`cell2-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip />
                <Legend />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>
        <div className='grid grid-cols-4 gap-4'>
        <div className="bg-white rounded-xl border p-4 flex flex-col items-center justify-center">
            <div className="text-sm font-bold text-gray-600">cycle</div>          </div>
        </div>
        {/* Tableau classement */}
        <div className="bg-white rounded-xl border p-4 mt-4">
          <div className="font-semibold mb-2">Classement des Meilleures Écoles dans le commune</div>
          <table className="w-full text-sm">
            <thead>
              <tr>
                <th className="text-gray-400 text-left py-2 px-4">#</th>
                <th className="text-gray-400 text-left py-2 px-4">établissement</th>
                <th className="text-gray-400 text-left py-2 px-4">nombre eleves</th>
                <th className="text-gray-400 text-left py-2 px-4">moyenne</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {tableData.map(row => (
                <tr key={row.id}>
                  <td className="text-gray-400 py-2 px-4 font-semibold">{row.id}</td>
                  <td className="text-gray-700 py-2 px-4">{row.etablissement}</td>
                  <td className="text-gray-700 py-2 px-4">{row.eleves}</td>
                  <td className="text-gray-700 py-2 px-4">{row.moyenne}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        {/* Moyenne de province et courbe */}
        <div className="grid grid-cols-2 gap-4 mt-4">
          <div className="bg-white rounded-xl border p-4 flex flex-col items-center">
            <div className="font-semibold mb-2">moyenne de province</div>
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
            <div className="font-semibold mb-2">taux de reussite de province</div>
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
      </div>
    </main>
  );
};

export default AnalyseCommune;