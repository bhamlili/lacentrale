const details = JSON.parse(localStorage.getItem("reservation")) || {};

document.getElementById("confirmation-details").innerHTML = `
  <p><strong>Nom :</strong> ${details.nom || "-"}</p>
  <p><strong>Prénom :</strong> ${details.prenom || "-"}</p>
  <p><strong>Email :</strong> ${details.email || "-"}</p>
  <p><strong>Téléphone :</strong> ${details.telephone || "-"}</p>
  <p><strong>Médecin :</strong> ${details.medecin || "-"}</p>
  <p><strong>Date :</strong> ${details.jour || "-"}</p>
  <p><strong>Heure :</strong> ${details.heure || "-"}</p>
`;
