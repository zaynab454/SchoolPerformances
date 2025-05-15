import React, { useState } from 'react';
import { FaCamera } from 'react-icons/fa';

const checklist = [
  "Au moins 8 caractères",
  "Une lettre majuscule",
  "Une lettre minuscule",
  "Un chiffre",
  "Un caractère spécial"
];

const Parametres = () => {
  const [tab, setTab] = useState('password');
  const [checked, setChecked] = useState([true, true, true, true, true]); // Pour l'exemple, tout validé

  return (
    <div className="min-h-screen bg-gray-100 flex flex-col items-center py-8">
      <div className="bg-white rounded-2xl border mx-auto w-full max-w-4xl p-10">
        <h1 className="text-4xl font-bold mb-8 text-black">Paramètres</h1>
        {/* Onglets */}
        <div className="flex mb-8 w-full">
          <div
            className={`flex-1 py-2 text-center cursor-pointer border border-gray-400 rounded-l-xl text-lg font-medium transition
              ${tab === 'password' ? 'bg-white text-black' : 'bg-gray-200 text-gray-600'}`}
            style={{
              borderRight: 'none',
              borderTopRightRadius: tab === 'password' ? 0 : '0.75rem',
              borderBottomRightRadius: tab === 'password' ? 0 : '0.75rem'
            }}
            onClick={() => setTab('password')}
          >
            Changer le mot de passe
          </div>
          <div
            className={`flex-1 py-2 text-center cursor-pointer border border-gray-400 rounded-r-xl text-lg font-medium transition
              ${tab === 'profile' ? 'bg-white text-black' : 'bg-gray-200 text-gray-600'}`}
            style={{
              borderLeft: 'none',
              borderTopLeftRadius: tab === 'profile' ? 0 : '0.75rem',
              borderBottomLeftRadius: tab === 'profile' ? 0 : '0.75rem'
            }}
            onClick={() => setTab('profile')}
          >
            Modifier le profil
          </div>
        </div>

        {/* Changer le mot de passe */}
        {tab === 'password' && (
          <div className="flex flex-col items-center">
            <form className="w-full max-w-md mx-auto flex flex-col gap-6">
              <div>
                <label className="block text-gray-700 text-start font-semibold mb-2">Mot de passe actuel</label>
                <input type="password" className="w-full border-b border-gray-300 py-2 focus:outline-none" />
              </div>
              <div>
                <label className="block text-gray-700 text-start font-semibold mb-2">Nouveau mot de passe</label>
                <input type="password" className="w-full border-b border-gray-300 py-2 focus:outline-none" />
              </div>
              <div>
                <label className="block text-gray-700 text-start font-semibold mb-2">Confirmer le mot de passe</label>
                <input type="password" className="w-full border-b border-gray-300 py-2 focus:outline-none" />
              </div>
              <div className="mt-2 mb-4">
                <div className="text-gray-700 mb-2">Le mot de passe doit contenir :</div>
                <ul className="space-y-1 text-sm">
                  {checklist.map((item, idx) => (
                    <li key={item} className="flex items-center gap-2">
                      <span className={`w-4 h-4 rounded-full border-2 flex items-center justify-center ${checked[idx] ? 'border-blue-500 text-blue-500' : 'border-gray-300 text-gray-300'}`}>
                        {checked[idx] && <span className="text-blue-500">&#10003;</span>}
                      </span>
                      <span className={checked[idx] ? "text-blue-600" : "text-gray-500"}>{item}</span>
                    </li>
                  ))}
                </ul>
              </div>
              <button type="submit" className="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-blue-700 transition">
                Mettre à jour le mot de passe
              </button>
            </form>
          </div>
        )}

        {/* Modifier le profil */}
        {tab === 'profile' && (
          <div className="flex flex-col items-center">
            <form className="w-full max-w-2xl mx-auto flex flex-row gap-8 items-start">
              <div className="flex-1 flex flex-col gap-5">
                <div>
                  <label className="block text-gray-700 text-start font-semibold mb-2">Prénom</label>
                  <input type="text" className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none" placeholder="Entrez votre prénom" />
                </div>
                <div>
                  <label className="block text-gray-700 text-start font-semibold mb-2">Nom</label>
                  <input type="text" className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none" placeholder="Entrez votre nom" />
                </div>
                <div>
                  <label className="block text-gray-700 text-start font-semibold mb-2">Adresse email</label>
                  <input type="email" className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none" placeholder="exemple@email.com" />
                </div>
                <div>
                  <label className="block text-gray-700 text-start font-semibold mb-2">Numéro de téléphone</label>
                  <input type="tel" className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none" placeholder="+212 XX XX XX XX XX" />
                </div>
                <div className="flex gap-4 mt-4">
                  <button type="button" className="px-6 py-2 rounded-lg border border-gray-300 text-white bg-white">Annuler</button>
                  <button type="submit" className="px-6 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700">Enregistrer les modifications</button>
                </div>
              </div>
              <div className="flex flex-col items-center gap-3">
                <div className="w-28 h-28 rounded-full bg-gray-200 flex items-center justify-center relative overflow-hidden">
                  <FaCamera className="text-gray-400 text-3xl" />
                </div>
                <button type="button" className="mt-2 px-4 py-1 rounded-full border border-gray-300  bg-white text-white text-sm">Changer la photo</button>
                <div className="text-xs text-gray-400 text-center">Format JPG ou PNG, max 1 MB</div>
              </div>
            </form>
          </div>
        )}
      </div>
    </div>
  );
};

export default Parametres;