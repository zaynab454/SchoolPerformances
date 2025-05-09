import axios from 'axios';
import { authApi } from './auth';

const API_BASE_URL = 'http://localhost:3000/api';   

export const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
    },
});

// Fonctions d'API
export const getStudentResults = async () => {
    try {
        const response = await authApi.get('/results');
        return response.data;
    } catch (error) {
        throw error;
    }
};

export const getAnalysisData = async (endpoint = '/analysis') => {
    try {
        const response = await authApi.get(endpoint);
        return response.data;
    } catch (error) {
        throw error;
    }
};

export const importData = async (data) => {
    try {
        const response = await authApi.post('/import', data);
        return response.data;
    } catch (error) {
        throw error;
    }
};

// Fonctions pour les rapports
export const getRapports = async () => {
    try {
        const response = await authApi.get('/rapports');
        return response.data;
    } catch (error) {
        throw error;
    }
};

export const generateRapport = async (data) => {
    try {
        const response = await authApi.post('/rapports/generate', data);
        return response.data;
    } catch (error) {
        throw error;
    }
};

// Fonctions pour les paramètres
export const getSettings = async () => {
    try {
        const response = await authApi.get('/settings');
        return response.data;
    } catch (error) {
        throw error;
    }
};

export const updateSettings = async (settings) => {
    try {
        const response = await authApi.put('/settings', settings);
        return response.data;
    } catch (error) {
        throw error;
    }
};
