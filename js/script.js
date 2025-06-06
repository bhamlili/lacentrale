let allDoctors = []; // Store all fetched doctors for search functionality

const container = document.getElementById("doctor-list");

function afficherMedecins(doctors) {
  container.innerHTML = "";
  doctors.forEach(doc => {
    const card = document.createElement("div");
    card.className = "card";
    card.innerHTML = `
      <img src="${doc.image}" alt="${doc.nom}" style="width:80px;height:80px;margin-bottom:10px;" />
      <h3>${doc.nom}</h3>
      <p><strong>Spécialité:</strong> ${doc.specialite}</p>
      <p><strong>Ville:</strong> ${doc.ville}</p>
      <a href="/agenda.php?doctor_id=${doc.doctor_id}" class="btn-rdv">Prendre rendez-vous</a>

    `;
    container.appendChild(card);
  });
}

async function fetchDoctors() {
  try {
    // Fetch data from the PHP endpoint
    const response = await fetch("/patient_doctor_list.php");
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json(); // Assuming the PHP returns JSON

    // Map the fetched data to the expected format (assuming PHP returns doctor_id, name, specialty)
    allDoctors = data.map(doctor => ({
      doctor_id: doctor.doctor_id,
      nom: doctor.name,
      specialite: doctor.specialty,
      ville: "Fès", // Assuming all doctors are in Fès based on previous data
      image: "https://cdn-icons-png.flaticon.com/512/1995/1995574.png" // Placeholder image
    }));

    afficherMedecins(allDoctors);
  } catch (error) {
    console.error("Error fetching doctors:", error);
    container.innerHTML = "<p>Une erreur s'est produite lors du chargement des médecins.</p>";
  }
}

function rechercher() {
  const query = document.getElementById("searchInput").value.toLowerCase();
  const resultats = allDoctors.filter(m =>
    m.nom.toLowerCase().includes(query) || m.specialite.toLowerCase().includes(query)
  );
  afficherMedecins(resultats);
}

// Charger tous les médecins à Fès au démarrage
fetchDoctors();
