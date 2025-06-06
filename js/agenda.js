// Get doctor ID from URL
const params = new URLSearchParams(window.location.search);
const doctorId = params.get("doctor_id");

const agendaDiv = document.getElementById("agenda");
const doctorNameElement = document.querySelector("h1");
const doctorSpecialtyElement = document.querySelector("p");
const availabilityTitleElement = document.querySelector("h2");

if (!doctorId) {
  // Redirect or show error if doctor_id is missing
  doctorNameElement.textContent = "Erreur: ID du médecin manquant.";
  availabilityTitleElement.style.display = "none";
} else {
  fetch(`/agenda.php?doctor_id=${doctorId}`)
    .then(response => response.json())
    .then(data => {
      if (data.doctor) {
        doctorNameElement.textContent = `Agenda de ${data.doctor.name}`;
        doctorSpecialtyElement.textContent = `Spécialité: ${data.doctor.specialty}`;
      } else {
        doctorNameElement.textContent = "Médecin introuvable.";
        doctorSpecialtyElement.style.display = "none";
        availabilityTitleElement.style.display = "none";
        return;
      }

      if (data.available_slots && Object.keys(data.available_slots).length > 0) {
        availabilityTitleElement.textContent = "Choisissez une date de rendez-vous";
        displayAgenda(data.available_slots);
      } else {
        availabilityTitleElement.textContent = "Aucune disponibilité trouvée pour ce médecin.";
      }
    })
    .catch(error => {
      console.error("Error fetching agenda:", error);
      doctorNameElement.textContent = "Erreur lors du chargement de l'agenda.";
      doctorSpecialtyElement.style.display = "none";
      availabilityTitleElement.style.display = "none";
    });
}

function displayAgenda(availableSlots) {
  agendaDiv.innerHTML = ""; // Clear previous content

  for (const date in availableSlots) {
    if (availableSlots.hasOwnProperty(date)) {
      const times = availableSlots[date];
      const dayCard = document.createElement("div");
      dayCard.className = "day-card";
      dayCard.innerHTML = `
        <div class="date">${date}</div>
        <div class="times">${times.join(", ")}</div>
      `;
      // Add event listener to handle time slot selection and redirection
      dayCard.addEventListener('click', () => {
          // This part needs refinement to select a specific time and pass it
          // For now, it just indicates the date has available slots
          alert(`Available times on ${date}: ${times.join(", ")}`);
          // TODO: Add logic to select a time and redirect to reservation.php
          // window.location.href = `/reservation.php?doctor_id=${doctorId}&datetime=...`;
      });
      agendaDiv.appendChild(dayCard);
    }
  }
}
