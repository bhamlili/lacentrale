const params = new URLSearchParams(window.location.search);
const doctorId = params.get("doctor_id");
const jour = params.get("jour") || "Jour inconnu";
const heure = params.get("heure") || "Heure inconnue";

// The PHP file /reservation.php will display the doctor name and appointment details


const form = document.getElementById("form-reservation");
form.addEventListener("submit", function(e) {
  e.preventDefault();

  const nom = form.nom.value;
  const prenom = form.prenom.value;
  const email = form.email.value;
  const tel = form.telephone.value;
  const appointmentDatetime = `${jour} ${heure}`; // Assuming jour and heure are in compatible formats

  const formData = new FormData();
  formData.append('nom', nom);
  formData.append('prenom', prenom);
  formData.append('email', email);
  formData.append('telephone', tel);
  formData.append('doctor_id', doctorId);
  formData.append('appointment_datetime', appointmentDatetime);

  fetch('/reservation.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text()) // Get the response as text to debug if needed
  .then(text => {
    console.log('Server response:', text); // Log the response
    // Assuming reservation.php handles the redirect on success
    // window.location.href = "/confirmation.php"; // reservation.php will redirect
  });
  .catch(error => {
    console.error('Error during reservation:', error);
    // Handle errors, e.g., display an error message to the user
  });
});
