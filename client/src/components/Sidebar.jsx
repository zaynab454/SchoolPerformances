import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { FaHome, FaSchool, FaChartLine, FaFileImport, FaCog, FaUser, FaSignOutAlt, FaChartBar, FaFileAlt } from 'react-icons/fa';
import { useTranslation } from '../hooks/useTranslation';
import './Sidebar.css';

const Sidebar = () => {
    const [isOpen, setIsOpen] = useState(false);
    const { t, translations } = useTranslation();
    const direction = translations.common?.direction || 'ltr';

    useEffect(() => {
        const handleResize = () => {
            if (window.innerWidth > 768) {
                setIsOpen(false);
                document.documentElement.style.setProperty('--sidebar-width', '80px');
            }
        };

        handleResize();
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    useEffect(() => {
        if (isOpen) {
            document.documentElement.style.setProperty('--sidebar-width', '250px');
        } else {
            document.documentElement.style.setProperty('--sidebar-width', '80px');
        }
    }, [isOpen]);

    const menuItems = [
        {
            path: '/dashboard',
            icon: translations.icons?.dashboard === 'FaChartBar' ? <FaChartBar /> : <FaHome />,
            label: 'common.dashboard'
        },
        {
            path: '/analyse-commune',
            icon: translations.icons?.analyseCommune === 'FaChartBar' ? <FaChartBar /> : <FaChartLine />,
            label: 'analyse.common'
        },
        {
            path: '/analyse-etablissement',
            icon: translations.icons?.analyseEtablissement === 'FaSchool' ? <FaSchool /> : <FaChartLine />,
            label: 'analyse.etablissement'
        },
        {
            path: '/import-donnees',
            icon: translations.icons?.importDonnees === 'FaFileImport' ? <FaFileImport /> : <FaFileImport />,
            label: 'import.title'
        },
        {
            path: '/rapports',
            icon: translations.icons?.rapports === 'FaFileAlt' ? <FaFileAlt /> : <FaFileImport />,
            label: 'common.rapports'
        },
        {
            path: '/parametres',
            icon: translations.icons?.parametres === 'FaCog' ? <FaCog /> : <FaCog />,
            label: 'common.parametres'
        }
    ];

    return (
        <div className={`sidebar ${isOpen ? 'open' : ''}`} dir={direction}>
            <div className="logo" onClick={() => setIsOpen(!isOpen)}>
                <img src="/logo.png" alt="Logo" />
                <span>{t('common.logo')}</span>
            </div>
            
            <div className="menu-toggle" onClick={() => setIsOpen(!isOpen)}>
                <span></span>
                <span></span>
                <span></span>
            </div>

            <nav className="nav-menu">
                {menuItems.map((item, index) => (
                    <Link key={index} to={item.path} className="menu-item">
                        <div className="icon-container">
                            {item.icon}
                        </div>
                        <span className="menu-label">{t(item.label)}</span>
                    </Link>
                ))}

                <div className="logout-section">
                    <button 
                        onClick={() => {
                            localStorage.removeItem('token');
                            localStorage.removeItem('user');
                            window.location.href = '/login';
                        }} 
                        className="menu-item logout-button"
                    >
                        <div className="icon-container">
                            <FaSignOutAlt />
                        </div>
                        <span className="menu-label">{t('common.deconnexion')}</span>
                    </button>
                </div>
            </nav>
        </div>
    );
};

export default Sidebar;