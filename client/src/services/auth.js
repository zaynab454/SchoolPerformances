import axios from 'axios';

const API_BASE_URL = 'http://localhost:8000/api'; 

// Configuration de l'instance axios
export const authApi = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
    },
});

// Fonctions d'authentification
export const login = async (credentials) => {
    try {
        const response = await authApi.post('/auth/login', credentials);
        // Stocker le token dans le localStorage
        localStorage.setItem('token', response.data.token);
        return response.data;
    } catch (error) {
        throw error;
    }
};

export const logout = () => {
    localStorage.removeItem('token');
};

export const isAuthenticated = () => {
    return localStorage.getItem('token') !== null;
};

// Middleware pour ajouter le token aux requêtes
authApi.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Gestion des erreurs d'authentification
authApi.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Token expiré ou invalide
            logout();
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);
