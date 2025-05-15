import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { isAuthenticated } from './services/auth';
import DashboardLayout from './layouts/DashboardLayout';
import Dashboard from './pages/Dashboard';
import AnalyseCommune from './pages/AnalyseCommune';
import AnalyseEtablissement from './pages/AnalyseEtablissement';
import ImportDonnees from './pages/ImportDonnees';
import Rapports from './pages/Rapports';
import Parametres from './pages/Parametres';
import Login from './pages/Login';
import NotFound from './pages/NotFound';
import Navbar from './components/Navbar';
import { LanguageProvider } from './contexts/LanguageContext';
import './App.css';

// Composant pour protéger les routes
const PrivateRoute = ({ children }) => {
  return isAuthenticated() ? children : <Navigate to="/login" />;
};

function App() {
  return (
    <Router>
      <LanguageProvider>
        <div className="app-container">
          <Routes>
            {/* Routes publiques */}
            <Route path="/login" element={<Login />} />
            <Route path="/" element={<PrivateRoute>
              <>
                <Navbar />
                <DashboardLayout />
              </>
            </PrivateRoute>}>
            <Route index element={<Dashboard />} />
              <Route path="/dashboard" element={<Dashboard />} />
              <Route path="/analyse-commune" element={<AnalyseCommune />} />
              <Route path="analyse-etablissement" element={<AnalyseEtablissement />} />
              <Route path="import-donnees" element={<ImportDonnees />} />
              <Route path="rapports" element={<Rapports />} />
              <Route path="parametres" element={<Parametres />} />
            </Route>
            {/* Route 404 */}
            <Route path="*" element={<NotFound />} />
          </Routes>
        </div>
      </LanguageProvider>
    </Router>
  );
}

export default App;
