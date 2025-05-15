import React, { useState, useEffect, useContext } from 'react';
import { FaExpandArrowsAlt, FaCompressArrowsAlt, FaSun, FaMoon, FaLanguage, FaBell, FaUserCircle, FaCalendar, FaBars, FaExpand, FaCompress, FaChevronDown } from 'react-icons/fa';
import { LanguageContext } from '../contexts/LanguageContext';
import './Navbar.css';

const Navbar = () => {
    const [isFullscreen, setIsFullscreen] = useState(false);
    const [isDarkMode, setIsDarkMode] = useState(false);
    const [isLanguageOpen, setIsLanguageOpen] = useState(false);
    const { language, setLanguage } = useContext(LanguageContext);

    const languages = [
        { code: 'fr', name: 'Français' },
        { code: 'en', name: 'English' },
        { code: 'ar', name: 'العربية' }
    ];
    const [notifications, setNotifications] = useState(3);
    const [isNotificationsOpen, setIsNotificationsOpen] = useState(false);
    const [selectedYear, setSelectedYear] = useState('2023-2024');

    const years = [
        '2018-2019',
        '2019-2020',
        '2020-2021',
        '2021-2022',
        '2022-2023',        
        '2023-2024',
        '2024-2025',
        '2025-2026',
    ];

    // Gestion du mode plein écran
    const handleFullscreen = () => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
        setIsFullscreen(!isFullscreen);
    };

    // Gestion du mode sombre
    const handleDarkMode = () => {
        setIsDarkMode(!isDarkMode);
        document.body.classList.toggle('dark-mode');
    };

    // Gestion du changement de langue
    const handleLanguageChange = (lang) => {
        setLanguage(lang);
        console.log('Language changed to:', lang);
    };

    // Gestion du changement de langue via le bouton
    const toggleLanguage = () => {
        const newLang = language === 'fr' ? 'en' : 'fr';
        handleLanguageChange(newLang);
    };

    // Gestion des notifications
    const handleNotifications = () => {
        setIsNotificationsOpen(!isNotificationsOpen);
    };

    // Gestion du marquage comme lu
    const markAllRead = () => {
        setNotifications(0);
    };

    // Gestion du profil
    const handleProfile = () => {
        // À implémenter
    };

    // Gestion du mode plein écran automatique
    useEffect(() => {
        const handleFullscreenChange = () => {
            setIsFullscreen(!!document.fullscreenElement);
        };

        handleFullscreenChange();
        window.addEventListener('fullscreenchange', handleFullscreenChange);
        return () => window.removeEventListener('fullscreenchange', handleFullscreenChange);
    }, []);

    return (
        <nav className="navbar">
            <div className="nav-left">
                <div className="year-selector">
                    <select value={selectedYear} onChange={(e) => setSelectedYear(e.target.value)}>
                        {years.map((year) => (
                            <option key={year} value={year}>
                                {year}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            <div className="nav-right">
                <button className="action-btn" onClick={handleFullscreen}>
                    {isFullscreen ? <FaCompressArrowsAlt /> : <FaExpandArrowsAlt />}
                </button>
                <button className="action-btn" onClick={handleDarkMode}>
                    {isDarkMode ? <FaSun /> : <FaMoon />}
                </button>
                <div className="language-selector">
                    <button className="language-btn" onClick={() => setIsLanguageOpen(!isLanguageOpen)}>
                        <FaLanguage />
                        <span>{language === 'fr' ? 'FR' : language === 'en' ? 'EN' : 'AR'}</span>
                        <FaChevronDown className="language-arrow" />
                    </button>
                    {isLanguageOpen && (
                        <div className="language-menu">
                            {languages.map((lang) => (
                                <button
                                    key={lang.code}
                                    className={`language-option ${language === lang.code ? 'active' : ''}`}
                                    onClick={() => handleLanguageChange(lang.code)}
                                >
                                    {lang.name}
                                </button>
                            ))}
                        </div>
                    )}
                </div>
                <button className="action-btn" onClick={handleNotifications}>
                    <FaBell />
                    <span className="notification-badge">{notifications}</span>
                </button>
                <div className="notifications-menu" style={{ display: isNotificationsOpen ? 'block' : 'none' }}>
                    <div className="notifications-header">
                        <h3>Notifications</h3>
                        <button onClick={markAllRead}>Marquer tout comme lu</button>
                    </div>
                    <div className="notifications-list">
                        {/* Exemple de notifications */}
                        <div className="notification-item">
                            <FaBell />
                            <div className="notification-content">
                                <p>Nouvelle mise à jour disponible</p>
                                <span className="notification-time">il y a 1 heure</span>
                            </div>
                        </div>
                    </div>
                    <div className="notifications-footer">
                        <button onClick={() => window.location.href = '/notifications'}>Voir toutes les notifications</button>
                    </div>
                </div>
                <button className="action-btn" onClick={handleProfile}>
                    <FaUserCircle />
                </button>
            </div>
        </nav>
    );
};

export default Navbar;
