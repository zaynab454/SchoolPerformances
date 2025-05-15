import React from 'react';

const Parametres = () => {
  return (
    <div className="min-h-screen bg-gray-100 flex flex-col items-center justify-start py-12">
      <div className="bg-white rounded-2xl shadow-lg p-10 w-full max-w-2xl">
        <h1 className="text-3xl font-bold text-[#2d2c5a] mb-8">Paramètres</h1>
        <form className="space-y-8">
          <div>
            <label className="block text-gray-700 font-semibold mb-2" htmlFor="username">Nom d'utilisateur</label>
            <input
              id="username"
              type="text"
              className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2d2c5a]"
              placeholder="Entrez votre nom d'utilisateur"
            />
          </div>
          <div>
            <label className="block text-gray-700 font-semibold mb-2" htmlFor="email">Email</label>
            <input
              id="email"
              type="email"
              className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2d2c5a]"
              placeholder="Entrez votre email"
            />
          </div>
          <div>
            <label className="block text-gray-700 font-semibold mb-2" htmlFor="password">Nouveau mot de passe</label>
            <input
              id="password"
              type="password"
              className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2d2c5a]"
              placeholder="Entrez un nouveau mot de passe"
            />
          </div>
          <button
            type="submit"
            className="w-full bg-[#2d2c5a] text-white py-3 rounded-lg font-semibold text-lg hover:bg-[#23224a] transition"
          >
            Enregistrer les modifications
          </button>
        </form>
      </div>
    </div>
  );
};

export default Parametres;