import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';

const Login = () => {
  const [email, setEmail] = useState("admin@schoolperformance.ma");
  const [password, setPassword] = useState("admin123");
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setSuccess("");
    try {
      const response = await fetch('http://localhost:8000/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });
      const data = await response.json();
      if (response.ok) {
        setSuccess('Connexion réussie !');
        localStorage.setItem('token', data.token);
        navigate('/dashboard');
        
        // window.location.href = '/dashboard';
      } else {
        setError(data.message || 'Erreur de connexion');
      }
    } catch (err) {
      setError('Erreur réseau ou serveur.');
    }
  };

  return (
    <div className="fixed inset-0 min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 via-white to-purple-100">
      <div className="bg-transparent/70 rounded-2xl shadow-lg px-8 py-10 w-full max-w-sm flex flex-col items-center backdrop-blur-md">
        <div className="w-20 h-20 rounded-full bg-[#e3f2fd] flex items-center justify-center mb-4">
          {/* Graduation cap icon for education theme */}
          <svg className="w-12 h-12 text-[#2d2c5a]" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 3L1 9l11 6 9-4.91V17a1 1 0 01-2 0v-2.18l-7 3.82-9-5 1.18-.66L12 15l8.82-4.84A1 1 0 0021 9l-9-6z" />
          </svg>
        </div>
        <h2 className="text-2xl font-bold text-[#2d2c5a] mb-1">WELCOME BACK</h2>
        <p className="text-gray-500 mb-6 text-center text-sm">Please login to continue</p>
        {error && <div className="text-red-500 mb-2">{error}</div>}
        {success && <div className="text-green-600 mb-2">{success}</div>}
        <form className="w-full flex flex-col gap-4" onSubmit={handleSubmit}>
          <input
            type="text" name='email' id="email" autoComplete="email"
            placeholder="hajar@gmail.com"
            className="w-full px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2d2c5a] text-gray-700"
            required
            value={email}
            onChange={e => setEmail(e.target.value)}
          />
          <input
            type="password" name='password' id="password" autoComplete="current-password"
            placeholder="••••••••"
            className="w-full px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2d2c5a] text-gray-700"
            required
            value={password}
            onChange={e => setPassword(e.target.value)}
          />
          <div className="flex justify-end mb-2">
            <a href="#" className="text-sm text-[#2d2c5a] hover:underline">Forgot Password?</a>
          </div>
          <button
            type="submit"
            className="w-full bg-[#2d2c5a] text-white py-3 rounded-lg font-semibold text-lg hover:bg-[#23224a] transition"
          >
            Login
          </button>
        </form>
        <div className="mt-4 text-sm text-gray-600">
          Don&apos;t have an account?{' '}
          <a href="#" className="text-[#2d2c5a] font-semibold hover:underline">Signup</a>
        </div>
      </div>
    </div>
  );
};

export default Login;