const params = new URLSearchParams(window.location.search);
const medecin = params.get("medecin") || "Médecin inconnu";
const jour = params.get("jour") || "Jour inconnu";
const heure = params.get("heure") || "Heure inconnue";

document.getElementById("rdv-info").innerHTML = `
  <p><strong>Médecin :</strong> ${medecin}</p>
  <p><strong>Jour :</strong> ${jour}</p>
  <p><strong>Heure :</strong> ${heure}</p>
`;

const form = document.getElementById("form-reservation");
form.addEventListener("submit", function(e) {
  e.preventDefault();

  const nom = form.nom.value;
  const prenom = form.prenom.value;
  const email = form.email.value;
  const tel = form.telephone.value;
  const data = {
  nom, prenom, email, telephone: tel,
  medecin, jour, heure
};

localStorage.setItem("reservation", JSON.stringify(data));

// Redirection vers page de confirmation
window.location.href = "confirmation.html";

  // Ici tu peux envoyer les données vers une base (plus tard)
  console.log("Réservation :", {
    medecin, jour, heure, nom, prenom, email, tel
  });

  // Afficher confirmation
  form.style.display = "none";
  document.getElementById("confirmation-message").style.display = "block";
});

