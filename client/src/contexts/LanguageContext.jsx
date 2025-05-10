import React, { createContext, useState, useEffect } from 'react';

const LanguageContext = createContext();

export const LanguageProvider = ({ children }) => {
    const [language, setLanguage] = useState('fr');
    const [direction, setDirection] = useState('ltr');

    useEffect(() => {
        // Définir la direction du texte selon la langue
        const newDirection = language === 'ar' ? 'rtl' : 'ltr';
        setDirection(newDirection);
        
        // Mettre à jour la direction du document
        document.documentElement.setAttribute('dir', newDirection);
        
        // Mettre à jour le localStorage
        localStorage.setItem('language', language);
        localStorage.setItem('direction', newDirection);
    }, [language]);

    // Charger la langue depuis le localStorage au démarrage
    useEffect(() => {
        const savedLanguage = localStorage.getItem('language') || 'fr';
        const savedDirection = localStorage.getItem('direction') || 'ltr';
        setLanguage(savedLanguage);
        setDirection(savedDirection);
        document.documentElement.setAttribute('dir', savedDirection);
    }, []);

    return (
        <LanguageContext.Provider value={{ language, setLanguage, direction }}>
            {children}
        </LanguageContext.Provider>
    );
};

export { LanguageContext };
