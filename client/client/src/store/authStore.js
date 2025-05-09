// authStore.js
import { makeAutoObservable } from "mobx";

class AuthStore {
  isAuthenticated = false;  // L'état de connexion
  user = null;              // Informations sur l'utilisateur connecté
  token = null;             // Token d'authentification

  constructor() {
    makeAutoObservable(this);
  }

  // Connexion de l'utilisateur
  login(user, token) {
    this.user = user;
    this.token = token;
    this.isAuthenticated = true;
    localStorage.setItem("token", token); // On sauvegarde le token dans localStorage
  }

  // Déconnexion de l'utilisateur
  logout() {
    this.user = null;
    this.token = null;
    this.isAuthenticated = false;
    localStorage.removeItem("token");  // On supprime le token du localStorage
  }

  // Vérifier l'authentification au démarrage
  checkAuth() {
    const token = localStorage.getItem("token");
    if (token) {
      this.token = token;
      this.isAuthenticated = true;
    }
  }
}

const authStore = new AuthStore();
export default authStore;
