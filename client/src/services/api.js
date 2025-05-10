import axios from 'axios';
import { authApi } from './auth';

const API_BASE_URL = 'http://localhost:8000/api';

const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Ajouter un intercepteur de réponse
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response) {
            // Le serveur a répondu avec un statut autre que 2xx
            console.error('Erreur API:', error.response.data);
        } else if (error.request) {
            // La requête a été faite mais pas de réponse
            console.error('Pas de réponse du serveur:', error.request);
        } else {
            // Erreur de configuration de la requête
            console.error('Erreur de configuration:', error.message);
        }
        return Promise.reject(error);
    }
);

// Exporter les endpoints de l'API
export const apiEndpoints = {
    // Statistiques Province
    statsProvince: (annee) => `/province/stats${annee ? '/' + annee : ''}`,
    evolutionProvince: '/province/evolution',
    topEtablissements: (annee) => `/province/top-etablissements/${annee}`,
    statsParCycle: (annee) => `/province/cycles/${annee}`,
    comparaisonCommunes: (idProvince, annee) => `/province/comparaison-communes/${idProvince}/${annee}`,
    
    // Communes
    getCommunes: '/commune/communes',
    statCommune: (id, annee) => `/commune/${id}/stats/${annee}`,
    evolutionCommune: (id) => `/commune/${id}/evolution`,
    statsParCycleCommune: (id, annee) => `/commune/${id}/cycles/${annee}`,
    
    // Établissements
    statEtablissement: (id, annee) => `/etablissement/${id}/stats/${annee}`,
    statNiveau: (id, annee, codeNiveau) => `/etablissement/${id}/niveaux/${annee}/${codeNiveau}`,
    statMatiere: (id, codeNiveau, annee) => `/etablissement/${id}/matieres/${codeNiveau}/${annee}`,
    evaluationAnnuelle: (id) => `/etablissement/${id}/evolution`,
    
    // Rapports
    rapportEtablissement: (id, annee) => `/rapports/etablissement/${id}/${annee}`,
    rapportCommune: (id, annee) => `/rapports/commune/${id}/${annee}`,
    rapportProvince: (id, annee) => `/rapports/province/${id}/${annee}`,
    
    // Import
    getAnneesScolaires: '/import/annees',
    addAnneeScolaire: '/import/annee',
    selectAnneeScolaire: '/import/annee/select',
    importResultats: '/import/resultats',
    
    // Paramètres
    changePassword: '/parametres/password',
    setAnneeActive: '/parametres/annee-active',
    getAnneeActive: '/parametres/annee-active',
    
    // Années Scolaires
    anneesScolaires: '/annees-scolaires',
    addAnneeScolaire: '/annees-scolaires',
    updateAnneeScolaire: (id) => `/annees-scolaires/${id}`,
    deleteAnneeScolaire: (id) => `/annees-scolaires/${id}`,
    setCourante: (id) => `/annees-scolaires/${id}/set-courante`
};

// Utiliser authApi pour les requêtes protégées
export const protectedApi = authApi;

export default api;