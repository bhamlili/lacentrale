const medecins = [
  {
    nom: "Dr. Salma Bennis",
    specialite: "Cardiologue",
    ville: "Fès",
    image: "https://cdn-icons-png.flaticon.com/512/387/387561.png"
  },
  {
    nom: "Dr. Youssef El Amrani",
    specialite: "Dentiste",
    ville: "Fès",
    image: "https://cdn-icons-png.flaticon.com/512/607/607414.png"
  },
  {
    nom: "Dr. Rania Lahlou",
    specialite: "Dermatologue",
    ville: "Fès",
    image: "https://cdn-icons-png.flaticon.com/512/4341/4341088.png"
  },
  {
    nom: "Dr. Hicham Berrada",
    specialite: "Médecin généraliste",
    ville: "Fès",
    image: "https://cdn-icons-png.flaticon.com/512/1995/1995574.png"
  },
  {
    nom: "Dr. Amal Idrissi",
    specialite: "Pédiatre",
    ville: "Fès",
    image: "https://cdn-icons-png.flaticon.com/512/3602/3602123.png"
  }
];

const container = document.getElementById("doctor-list");

function afficherMedecins(liste) {
  container.innerHTML = "";
  liste.forEach(doc => {
    const card = document.createElement("div");
    card.className = "card";
    card.innerHTML = `
      <img src="${doc.image}" alt="${doc.nom}" style="width:80px;height:80px;margin-bottom:10px;" />
      <h3>${doc.nom}</h3>
      <p><strong>Spécialité:</strong> ${doc.specialite}</p>
      <p><strong>Ville:</strong> ${doc.ville}</p>
     <a href="agenda.html?medecin=${encodeURIComponent(doc.nom)}" class="btn-rdv">
  Prendre rendez-vous
</a>

    `;
    container.appendChild(card);
  });
}

function rechercher() {
  const query = document.getElementById("searchInput").value.toLowerCase();
  const resultats = medecins.filter(m =>
    m.nom.toLowerCase().includes(query) || m.specialite.toLowerCase().includes(query)
  );
  afficherMedecins(resultats);
}

// Charger tous les médecins à Fès au démarrage
afficherMedecins(medecins);
