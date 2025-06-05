// Récupérer le nom du médecin depuis l’URL
const params = new URLSearchParams(window.location.search);
const medecin = params.get("medecin") || "Inconnu";

// Exemple d'agendas par médecin
const agendas = {
  "Dr.Salma": {
    "Lundi": ["09:00", "10:00", "11:00"],
    "Mardi": ["14:00", "15:00"],
    "Mercredi": ["09:00", "10:00", "14:00"]
  },
  "Dr.Hamza": {
    "Lundi": ["13:00", "14:00"],
    "Jeudi": ["10:00", "11:00"],
    "Vendredi": ["15:00", "16:00"]
  },
  "Dr.Rania": {
    "Mardi": ["09:00", "10:00"],
    "Mercredi": ["13:00", "14:00"],
    "Samedi": ["11:00", "12:00"]
  }
};

// Affiche le nom du médecin
document.querySelector("h2").textContent = "Agenda de " + medecin;

// Récupère l’agenda du médecin
const agenda = agen
