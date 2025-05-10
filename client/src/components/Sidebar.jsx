import React, { useState, useEffect, useContext } from 'react';
import { Link } from 'react-router-dom';
import { FaHome, FaSchool, FaChartLine, FaFileImport, FaCog, FaUser, FaSignOutAlt } from 'react-icons/fa';
import { LanguageContext } from '../contexts/LanguageContext';
import './Sidebar.css';

const Sidebar = () => {
    const [isOpen, setIsOpen] = useState(false);
    const { language, direction } = useContext(LanguageContext);

    useEffect(() => {
        const handleResize = () => {
            if (window.innerWidth > 768) {
                setIsOpen(false);
            }
        };

        handleResize();
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    const menuItems = [
        {
            path: '/dashboard',
            icon: <FaHome />,
            label: {
                fr: 'Tableau de bord',
                en: 'Dashboard',
                ar: 'لوحة التحكم'
            }
        },
        {
            path: '/analyse-commune',
            icon: <FaSchool />,
            label: {
                fr: 'Analyse commune',
                en: 'Common Analysis',
                ar: 'تحليل عام'
            }
        },
        {
            path: '/analyse-etablissement',
            icon: <FaChartLine />,
            label: {
                fr: 'Analyse établissement',
                en: 'School Analysis',
                ar: 'تحليل المؤسسة'
            }
        },
        {
            path: '/import-donnees',
            icon: <FaFileImport />,
            label: {
                fr: 'Import données',
                en: 'Import Data',
                ar: 'استيراد البيانات'
            }
        },
        {
            path: '/rapports',
            icon: <FaFileImport />,
            label: {
                fr: 'Rapports',
                en: 'Reports',
                ar: 'التقارير'
            }
        },
        {
            path: '/parametres',
            icon: <FaCog />,
            label: {
                fr: 'Paramètres',
                en: 'Settings',
                ar: 'الإعدادات'
            }
        }
    ];

    return (
        <div className={`sidebar ${isOpen ? 'open' : ''}`} dir={direction}>
            <div className="logo">
                <img src="/logo.png" alt="SchoolPerf" className="logo-image" />
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
                        <span className="menu-label">{item.label[language]}</span>
                    </Link>
                ))}

                <Link to="/logout" className="menu-item logout">
                    <div className="icon-container">
                        <FaSignOutAlt />
                    </div>
                    <span className="menu-label">Déconnexion</span>
                </Link>
            </nav>
        </div>
    );
};

export default Sidebar;