import React from 'react';
import { Link } from 'react-router-dom';

const NotFound = () => {
  return (
    <div className="fixed inset-0 min-h-screen min-w-full flex items-center justify-center bg-gray-100">
      <div className="bg-white rounded-2xl shadow-lg p-10 w-full max-w-md text-center">
        <h1 className="text-7xl font-extrabold text-[#2d2c5a] mb-4">404</h1>
        <h2 className="text-2xl font-semibold text-gray-700 mb-4">Page non trouvée</h2>
        <p className="text-gray-600 mb-8">La page que vous recherchez n'existe pas ou a été déplacée.</p>
        <Link
          to="/"
          className="inline-block bg-[#2d2c5a] text-white px-8 py-3 rounded-lg font-semibold text-lg hover:bg-[#23224a] transition"
        >
          Retourner au Dashboard
        </Link>
      </div>
    </div>
  );
};

export default NotFound;
