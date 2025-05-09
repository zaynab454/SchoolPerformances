import React from 'react';
import { getStudentResults } from '../services/api';

const Dashboard = () => {
  const [stats, setStats] = React.useState({
    totalStudents: 0,
    averageScore: 0,
    successRate: 0
  });

  React.useEffect(() => {
    const fetchStats = async () => {
      try {
        const results = await getStudentResults();
        const totalStudents = results.length;
        const totalScore = results.reduce((sum, result) => sum + result.score, 0);
        const averageScore = totalScore / totalStudents;
        const successRate = results.filter(result => result.score >= 10).length / totalStudents * 100;

        setStats({
          totalStudents,
          averageScore: averageScore.toFixed(2),
          successRate: successRate.toFixed(1)
        });
      } catch (error) {
        console.error('Error fetching stats:', error);
      }
    };

    fetchStats();
  }, []);

  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
      {/* Card 1 */}
      <div className="bg-white p-6 rounded-lg shadow-lg">
        <h3 className="text-lg font-semibold text-gray-800 mb-2">Nombre d'Étudiants</h3>
        <p className="text-3xl font-bold text-blue-600">{stats.totalStudents}</p>
      </div>

      {/* Card 2 */}
      <div className="bg-white p-6 rounded-lg shadow-lg">
        <h3 className="text-lg font-semibold text-gray-800 mb-2">Moyenne Générale</h3>
        <p className="text-3xl font-bold text-green-600">{stats.averageScore}/20</p>
      </div>

      {/* Card 3 */}
      <div className="bg-white p-6 rounded-lg shadow-lg">
        <h3 className="text-lg font-semibold text-gray-800 mb-2">Taux de Réussite</h3>
        <p className="text-3xl font-bold text-purple-600">{stats.successRate}%</p>
      </div>
    </div>
  );
};

export default Dashboard;
