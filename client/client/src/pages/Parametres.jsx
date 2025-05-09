import React from 'react';
import { updateSettings } from '../services/api';

const Parametres = () => {
  const [settings, setSettings] = React.useState({
    theme: 'light',
    notifications: true,
    emailNotifications: true,
    language: 'fr'
  });
  const [saving, setSaving] = React.useState(false);
  const [success, setSuccess] = React.useState(false);

  const handleSettingChange = (e) => {
    const { name, value, type, checked } = e.target;
    setSettings(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  const handleSave = async () => {
    setSaving(true);
    setSuccess(false);

    try {
      await updateSettings(settings);
      setSuccess(true);
      setTimeout(() => setSuccess(false), 3000);
    } catch (error) {
      console.error('Error saving settings:', error);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-800">Paramètres</h1>

      {/* Thème */}
      <div className="bg-white p-6 rounded-lg shadow-lg">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Thème</h2>
        <div className="space-y-4">
          <div>
            <label className="block text-gray-700 text-sm font-bold mb-2" htmlFor="theme">
              Mode sombre
            </label>
            <select
              id="theme"
              name="theme"
              value={settings.theme}
              onChange={handleSettingChange}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="light">Clair</option>
              <option value="dark">Sombre</option>
            </select>
          </div>
        </div>
      </div>

      {/* Notifications */}
      <div className="bg-white p-6 rounded-lg shadow-lg">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Notifications</h2>
        <div className="space-y-4">
          <div className="flex items-center">
            <input
              type="checkbox"
              id="notifications"
              name="notifications"
              checked={settings.notifications}
              onChange={handleSettingChange}
              className="mr-2"
            />
            <label className="text-gray-700" htmlFor="notifications">
              Activer les notifications
            </label>
          </div>
          <div className="ml-6">
            <div className="flex items-center">
              <input
                type="checkbox"
                id="emailNotifications"
                name="emailNotifications"
                checked={settings.emailNotifications}
                onChange={handleSettingChange}
                className="mr-2"
              />
              <label className="text-gray-700" htmlFor="emailNotifications">
                Recevoir des notifications par email
              </label>
            </div>
          </div>
        </div>
      </div>

      {/* Langue */}
      <div className="bg-white p-6 rounded-lg shadow-lg">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Langue</h2>
        <div className="space-y-4">
          <div>
            <label className="block text-gray-700 text-sm font-bold mb-2" htmlFor="language">
              Langue de l'interface
            </label>
            <select
              id="language"
              name="language"
              value={settings.language}
              onChange={handleSettingChange}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="fr">Français</option>
              <option value="en">Anglais</option>
            </select>
          </div>
        </div>
      </div>

      {/* Bouton de sauvegarde */}
      <div className="flex justify-end space-x-4">
        <button
          onClick={handleSave}
          disabled={saving}
          className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50"
        >
          {saving ? 'Sauvegarde...' : 'Sauvegarder'}
        </button>
      </div>

      {/* Message de succès */}
      {success && (
        <div className="fixed bottom-4 right-4 p-4 rounded-lg bg-green-100 text-green-800">
          Paramètres sauvegardés avec succès !
        </div>
      )}
    </div>
  );
};

export default Parametres;
