import React from 'react';
import DashboardLayout from '../layouts/DashboardLayout.jsx';
import { LineChart, Line, BarChart, Bar, PieChart, Pie, Cell, XAxis, YAxis, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const summaryData = [
  { label: 'Nombre commune', value: 12 },
  { label: 'Nombre d\'établissements', value: 157 },
  { label: 'Nombre d\'étudiants', value: 60000 },
  { label: 'Moyenne générale', value: '14.2 / 20' },
  { label: 'Taux de reussite', value: '18 / 20' },
  { label: 'Taux d\'echec', value: '12 / 20' },
];

const lineData = [
  { year: '2023-2024', value: 10.5 },
  { year: '2024-2025', value: 12.2 },
  { year: '2025-2026', value: 16.5 },
];

const barData = [
  { cycle: 'Primaire', moyenne: 14 },
  { cycle: 'Collège', moyenne: 11 },
  { cycle: 'Lycée', moyenne: 16 },
];

const pieData = [
  { name: 'note entre 8 et 10', value: 15 },
  { name: 'note entre 10 et 12', value: 35 },
  { name: 'note entre 12 et 14', value: 25 },
  { name: 'note entre 14 et 20', value: 25 },
];

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042'];

const tableData = [
  { id: 1, etablissement: 'Lycée', commune: 'Commune 1', eleves: 1000, moyenne: 15, taux: '90%' },
  { id: 2, etablissement: 'Lycée', commune: 'Commune 2', eleves: 950, moyenne: 14.5, taux: '88%' },
  { id: 3, etablissement: 'Lycée', commune: 'Commune 3', eleves: 900, moyenne: 14, taux: '85%' },
  { id: 4, etablissement: 'Lycée', commune: 'Commune 4', eleves: 850, moyenne: 13.5, taux: '80%' },
  { id: 5, etablissement: 'Lycée', commune: 'Commune 5', eleves: 800, moyenne: 13, taux: '75%' },
];

const Dashboard = () => {
  return (
    <>
      <h2 className="text-5xl text-black font-bold mt-6 mb-4">Tableau de bord</h2>
      {/* Summary Cards */}
      <div className="grid grid-cols-3 gap-4 mb-8">
        {summaryData.map((item, idx) => (
          <div key={idx} className="bg-white rounded shadow p-4 flex flex-col items-center">
            <span className="text-lg text-black  font-bold">{item.label}</span>
            <span className="text-2xl text-gray-500  font-semibold">{item.value}</span>
          </div>
        ))}
      </div>
      {/* Charts and Map */}
      <div className="grid grid-cols-2 gap-4 mb-8">
        <div className="bg-white rounded shadow p-4">
          <h2 className="font-semibold text-black mb-2">moyenne de province</h2>
          <ResponsiveContainer width="100%" height={150}>
            <LineChart data={lineData}>
              <XAxis dataKey="year" />
              <YAxis />
              <Tooltip />
              <Line type="monotone" dataKey="value" stroke="#8884d8" />
            </LineChart>
          </ResponsiveContainer>
        </div>
        <div className="bg-white rounded shadow p-4 flex items-center justify-center">
          {/* Replace with your Morocco map image */}
          <img src="./images/morocco-map.png" alt="Carte Maroc" className="h-60 w-5xl" />
        </div>
      </div>
      <div>
        <div className="bg-white rounded-xl border shadow p-4">
          <h2 className="font-bold text-black mb-2">Top 5 établissement dans le province</h2>
          <table className="w-full text-sm">
            <thead>
              <tr>
                <th className='text-black font-medium '>#</th>
                <th className='text-black font-medium '>Etablissement</th>
                <th className='text-black font-medium '>Commune</th>
                <th className='text-black font-medium '>Nombre élèves</th>
                <th className='text-black font-medium '>Moyenne</th>
                <th className='text-black font-medium '>Taux de réussite</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {tableData.map(row => (
                <tr key={row.id}>
                  <td className='text-gray-500'>{row.id}</td>
                  <td className='text-gray-500'>{row.etablissement}</td>
                  <td className='text-gray-500'>{row.commune}</td>
                  <td className='text-gray-500'>{row.eleves}</td>
                  <td className='text-gray-500'>{row.moyenne}</td>
                  <td className='text-gray-500'>{row.taux}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
      <br />
      {/* Second Section: Bar and Pie Charts */}
      <div className="grid grid-cols-2 gap-4">
        <div className="bg-white rounded shadow p-4">
          <h2 className="font-semibold text-black mb-2">Moyenne par cycle</h2>
          <ResponsiveContainer width="100%" height={150}>
            <BarChart data={barData}>
              <XAxis dataKey="cycle" />
              <YAxis />
              <Tooltip />
              <Bar dataKey="moyenne" fill="#8884d8" />
            </BarChart>
          </ResponsiveContainer>
        </div>
        <div className="bg-white rounded shadow p-4">
          <h2 className="font-semibold text-black mb-2">Taux de réussi des cycle</h2>
          <ResponsiveContainer width="100%" height={150}>
            <PieChart>
              <Pie data={pieData} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={50} label>
                {pieData.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                ))}
              </Pie>
              <Tooltip />
              <Legend />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>
    </>
  );
};

export default Dashboard;