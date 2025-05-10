import { useState, useEffect, useContext } from 'react';
import { LanguageContext } from '../contexts/LanguageContext';

export const useTranslation = () => {
    const { language } = useContext(LanguageContext);
    const [translations, setTranslations] = useState({});

    useEffect(() => {
        // Charger les traductions selon la langue
        const loadTranslations = async () => {
            try {
                const response = await import(`../locales/${language}.json`);
                setTranslations(response.default);
            } catch (error) {
                console.error('Error loading translations:', error);
                // Charger les traductions par défaut (français)
                const response = await import('../locales/fr.json');
                setTranslations(response.default);
            }
        };

        loadTranslations();
    }, [language]);

    const t = (path) => {
        const keys = path.split('.');
        let current = translations;
        for (const key of keys) {
            if (current && typeof current === 'object' && key in current) {
                current = current[key];
            } else {
                return path; // Retourner la clé si la traduction n'est pas trouvée
            }
        }
        return current;
    };

    return { t, translations };
};
