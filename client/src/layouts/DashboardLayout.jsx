import React from 'react';
import { Outlet } from 'react-router-dom';

const DashboardLayout = () => {
  return (
    <div className="flex fixed inset-0 min-h-screen min-w-full bg-gray-100">
      {/* Sidebar */}
      <aside className="w-64 bg-[#2d2c5a] text-white flex flex-col justify-between">
        <div>
          <div className="h-24"></div>
          <nav className="flex flex-col gap-2">
            <a href="/dashboard" className="px-6 py-3 text-[#2d2c5a] text-start rounded-l-full font-semibold">Tableau de bord</a>
            <a href="/analyse-commune" className="px-6 py-3 text-start hover:bg-[#23224a] ">Analyse par commune</a>
            <a href="/analyse-etablissement" className="px-6 py-3 text-start hover:bg-[#23224a] ">Analyse par établissement</a>
            <a href="/import-donnees" className="px-6 py-3 text-start hover:bg-[#23224a] ">Importation des données</a>
            <a href="/rapports" className="px-6 py-3 text-start hover:bg-[#23224a] ">Rapport</a>
            <a href="/parametres" className="px-6 py-3 text-start hover:bg-[#23224a] ">Paramètres</a>
          </nav>
        </div>
        <div className="mb-8 px-6">
          <a href="/login" className="px-6 py-3 hover:bg-[#23224a] ">Se déconnecter</a>
        </div>
      </aside>
      {/* Main content */}
      <main className="flex-1 p-8 overflow-auto">
        <Outlet />
      </main>
    </div>
  );
};

export default DashboardLayout;