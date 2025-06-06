<?php
session_start();

// Database connection details
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "lacentrale";

// Check if the doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    header("Location: /connexion.php");

    // Add a connection to the database within this block if needed for redirection logic
    // (though typically session check is enough before any DB interaction related to the user)

    exit();
}

// Fetch doctor information
$doctor_id = $_SESSION['doctor_id'];
?>

<?php
$update_message = "";

// Handle form submission for updating doctor info
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_doctor_info'])) {
    $new_specialty = $_POST['specialty'];
    $new_contact_info = $_POST['contact_info'];

    $sql_update = "UPDATE doctors SET specialty = ?, contact_info = ? WHERE doctor_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssi", $new_specialty, $new_contact_info, $doctor_id);

    if ($stmt_update->execute()) {
        $update_message = "Vos informations ont été mises à jour avec succès.";
    } else {
        $update_message = "Erreur lors de la mise à jour: " . $conn->error;
    }
    $stmt_update->close();
}

// Handle appointment cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_appointment'])) {
    $appointment_id_to_cancel = $_POST['appointment_id'];

    $sql_cancel = "UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ? AND doctor_id = ?";
    $stmt_cancel = $conn->prepare($sql_cancel);
    $stmt_cancel->bind_param("ii", $appointment_id_to_cancel, $doctor_id);
    if ($stmt_cancel->execute()) {
        // Redirect or refresh the page after successful cancellation
        header("Location: /gestion_med.php");
        exit();
    } else {
        // Handle error if cancellation fails
        echo "Error cancelling appointment: " . $conn->error;
    }
    $stmt_cancel->close();
}

// Handle form submission for updating working hours
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_working_hours'])) {
    // Clear existing availability for the doctor
    $sql_delete_availability = "DELETE FROM doctor_availability WHERE doctor_id = ?";
    $stmt_delete_availability = $conn->prepare($sql_delete_availability);
    $stmt_delete_availability->bind_param("i", $doctor_id);
    $stmt_delete_availability->execute();
    $stmt_delete_availability->close();

    // Insert new working hours
    if (isset($_POST['working_days']) && is_array($_POST['working_days'])) {
        $sql_insert_availability = "INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)";
        $stmt_insert_availability = $conn->prepare($sql_insert_availability);

        foreach ($_POST['working_days'] as $day) {
            $start_time_key = strtolower($day) . '_start_time';
            $end_time_key = strtolower($day) . '_end_time';

            if (isset($_POST[$start_time_key]) && isset($_POST[$end_time_key]) && !empty($_POST[$start_time_key]) && !empty($_POST[$end_time_key])) {
                $start_time = $_POST[$start_time_key];
                $end_time = $_POST[$end_time_key];

                $stmt_insert_availability->bind_param("isss", $doctor_id, $day, $start_time, $end_time);
                $stmt_insert_availability->execute();
            }
        }
        $stmt_insert_availability->close();
    }
}

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MediRDV - Gestion des Rendez-vous</title>
  <link rel="stylesheet" href="style.css">
  
</head>
<body>
  <header>
    <div class="logo">LaCentrale<span class="dot">.</span><span class="ma">ma</span></div>
    <nav>
     <?php
      $conn = new mysqli($servername, $username, $password, $dbname);
      if ($conn->connect_error) {git
         die("Connection failed: " . $conn->connect_error);
      }

      $sql = "SELECT name, specialty FROM doctors WHERE doctor_id = $doctor_id";
      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
          $doctor = $result->fetch_assoc();
      ?>
      <ul>
        <li><a href="#">Accueil</a></li>
        <li><a href="#">Rendez-vous</a></li>
        <li><a href="#">Patients</a></li>
        <li><a href="#">Déconnexion</a></li>
      </ul>
    </nav>
   <div class="doctor-info" style="color: white;">
        Bonjour, Dr. <?php echo $doctor['name']; ?> (<?php echo $doctor['specialty']; ?>)
   </div>
    <?php
      }
      $conn->close();
    ?>
  </header>

  <main>
   <?php
    $conn = new mysqli($servername, $username, $password, $dbname);
   if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql_doctor_info = "SELECT specialty, contact_info FROM doctors WHERE doctor_id = $doctor_id";
    $result_doctor_info = $conn->query($sql_doctor_info);
    $doctor_info = $result_doctor_info->fetch_assoc();
    ?>
    <section class="doctor-info-section" style="margin-top: 20px; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3>Mes Informations</h3>
        <?php if (!empty($update_message)) { echo "<p style='color: green;'>$update_message</p>"; } ?>
        <form method="post" action="">
            <input type="hidden" name="update_doctor_info" value="1">
            <div style="margin-bottom: 15px;">
                <label for="specialty" style="display: block; margin-bottom: 5px; font-weight: bold;">Spécialité:</label>
                <input type="text" id="specialty" name="specialty" value="<?php echo htmlspecialchars($doctor_info['specialty']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="contact_info" style="display: block; margin-bottom: 5px; font-weight: bold;">Contact Info:</label>
                <input type="text" id="contact_info" name="contact_info" value="<?php echo htmlspecialchars($doctor_info['contact_info']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <button type="submit" style="background-color: #02c39a; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">Mettre à jour</button>
        </form>
    </section>

    <section class="dispo-section" style="margin-top: 20px; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3>Mes Horaires Réguliers</h3>
        <form method="post" action="">
            <input type="hidden" name="update_working_hours" value="1">
            <div style="margin-bottom: 10px;">
                <input type="checkbox" id="monday" name="working_days[]" value="Monday">
                <label for="monday">Lundi</label>
                <input type="time" name="monday_start_time"> - <input type="time" name="monday_end_time">
            </div>
            <div style="margin-bottom: 10px;">
                <input type="checkbox" id="tuesday" name="working_days[]" value="Tuesday">
                <label for="tuesday">Mardi</label>
                <input type="time" name="tuesday_start_time"> - <input type="time" name="tuesday_end_time">
            </div>
            <div style="margin-bottom: 10px;">
                <input type="checkbox" id="wednesday" name="working_days[]" value="Wednesday">
                <label for="wednesday">Mercredi</label>
                <input type="time" name="wednesday_start_time"> - <input type="time" name="wednesday_end_time">
            </div>
            <div style="margin-bottom: 10px;">
                <input type="checkbox" id="thursday" name="working_days[]" value="Thursday">
                <label for="thursday">Jeudi</label>
                <input type="time" name="thursday_start_time"> - <input type="time" name="thursday_end_time">
            </div>
            <div style="margin-bottom: 10px;">
                <input type="checkbox" id="friday" name="working_days[]" value="Friday">
                <label for="friday">Vendredi</label>
                <input type="time" name="friday_start_time"> - <input type="time" name="friday_end_time">
            </div>
            <div style="margin-bottom: 10px;">
                <input type="checkbox" id="saturday" name="working_days[]" value="Saturday">
                <label for="saturday">Samedi</label>
                <input type="time" name="saturday_start_time"> - <input type="time" name="saturday_end_time">
            </div>
            <div style="margin-bottom: 10px;">
                <input type="checkbox" id="sunday" name="working_days[]" value="Sunday">
                <label for="sunday">Dimanche</label>
                <input type="time" name="sunday_start_time"> - <input type="time" name="sunday_end_time">
            </div>
            <button type="submit" style="background-color: #02c39a; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin-top: 15px;">Enregistrer les horaires</button>
        </form>
    </section>

  <!-- Section Disponibilité -->
  <section class="dispo-section">

    <h3>Ajouter une disponibilité exceptionnelle</h3>
<div class="exceptional-dispo">
  <div class="exception-box">
    <label for="exceptionDate">📅 Date</label>
    <input type="date" id="exceptionDate" />
  </div>
  <div class="exception-box">
    <label for="exceptionStart">🕘 Heure de début</label>
    <input type="time" id="exceptionStart" />
  </div>
  <div class="exception-box">
    <label for="exceptionEnd">🕔 Heure de fin</label>
    <input type="time" id="exceptionEnd" />
  </div>
</div>
    <button type="button" id="saveDispoBtn">Enregistrer</button>
  </form>
</section>


  <main>
    <h2>Liste des Rendez-vous</h2>
    <button id="addBtn">+ Nouveau Rendez-vous</button>
    <table>
      <thead>
        <tr>git
          <th>Nom du patient</th>
          <th>Date</th>
          <th>Heure</th>
          <th>Motif</th>
        </tr>
      </thead>
      <tbody id="rdvTable">
        <?php
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql_appointments = "SELECT a.appointment_id, p.name AS patient_name, a.appointment_datetime, a.status
                             FROM appointments a
                             JOIN patients p ON a.patient_id = p.patient_id
                             WHERE a.doctor_id = ? AND a.status = 'scheduled'
                             ORDER BY a.appointment_datetime";
        $stmt_appointments = $conn->prepare($sql_appointments);
        $stmt_appointments->bind_param("i", $doctor_id);
        $stmt_appointments->execute();
        $result_appointments = $stmt_appointments->get_result();

        if ($result_appointments->num_rows > 0) {
            while ($row = $result_appointments->fetch_assoc()) {
                $appointment_time = new DateTime($row['appointment_datetime']);
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['patient_name']) . "</td>";
                echo "<td>" . $appointment_time->format('Y-m-d') . "</td>";
                echo "<td>" . $appointment_time->format('H:i') . "</td>";
                echo "<td>"; // Placeholder for Motif - you might need to add a motif column to the appointments table or fetch it if it exists
                echo "<form method='post' action=''><input type='hidden' name='cancel_appointment' value='1'><input type='hidden' name='appointment_id' value='" . $row['appointment_id'] . "'><button type='submit'>Annuler</button></form></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Aucun rendez-vous à venir.</td></tr>";
        }
        $stmt_appointments->close();
        $conn->close();
        ?>
      </tbody>
    </table>
  </main>

  <!-- Modale d'ajout -->
  <div class="modal" id="modal">
    <div class="modal-content">
      <span class="close" id="closeModal">&times;</span>
      <h3>Nouveau Rendez-vous</h3>
      <form id="rdvForm">
        <label>Nom du patient: <input type="text" id="nom" required></label>
        <label>Date: <input type="date" id="date" required></label>
        <label>Heure: <input type="time" id="heure" required></label>
        <label>Motif: <input type="text" id="motif" required></label>
        <button type="submit">Ajouter</button>
      </form>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>
<style>
    body {
  font-family: Arial, sans-serif;
  margin: 0;
  background: #f7f9fc;
}

.dispo-section {
  background-color: #eef7ff;
  padding: 20px;
  margin: 20px;
  border-radius: 10px;
}

.dispo-section h2 {
  margin-bottom: 15px;
}

.dispo-day {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.dispo-day input[type="time"] {
  padding: 5px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

#saveDispoBtn {
  margin-top: 10px;
  background-color: #0077b6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
}

#saveDispoBtn:hover {
  background-color: #005b8e;
}

header {
  background-color: #0077b6;
  color: white;
  padding: 15px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

nav ul {
  list-style: none;
  display: flex;
  gap: 20px;
}

nav a {
  color: white;
  text-decoration: none;
}

main {
  padding: 20px;
}

button {
  background-color: #0077b6;
  color: white;
  border: none;
  padding: 10px 15px;
  cursor: pointer;
  border-radius: 5px;
}

button:hover {
  background-color: #005f87;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  background: white;
  border-radius: 5px;
  overflow: hidden;
}

th, td {
  padding: 10px;
  border-bottom: 1px solid #ddd;
  text-align: left;
}

/* Section disponibilité */
.dispo {
  background-color: #e7f2ff;
  padding: 15px 20px;
  margin: 20px;
  border-radius: 8px;
}

.dispo label {
  margin-right: 15px;
}

/* Modal styles */
.modal {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-color: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: white;
  padding: 20px;
  width: 300px;
  border-radius: 8px;
  position: relative;
}

.close {
  position: absolute;
  right: 10px;
  top: 5px;
  font-size: 20px;
  cursor: pointer;
}

form label {
  display: block;
  margin-bottom: 10px;
}

</style>

<script>

    const saveDispoBtn = document.getElementById("saveDispoBtn");
const dispoDayRows = document.querySelectorAll(".dispo-day");

dispoDayRows.forEach(row => {
  const checkbox = row.querySelector("input[type='checkbox']");
  const start = row.querySelector(".start");
  const end = row.querySelector(".end");

  checkbox.addEventListener("change", () => {
    const isChecked = checkbox.checked;
    start.disabled = !isChecked;
    end.disabled = !isChecked;
  });
});

saveDispoBtn.addEventListener("click", () => {
  const disponibilites = [];

  dispoDayRows.forEach(row => {
    const checkbox = row.querySelector("input[type='checkbox']");
    const start = row.querySelector(".start").value;
    const end = row.querySelector(".end").value;
    if (checkbox.checked && start && end) {
      disponibilites.push({
        jour: row.dataset.day,
        jourIndex: checkbox.value,
        debut: start,
        fin: end
      });
    }
  });

  console.log("Disponibilités enregistrées :", disponibilites);

  alert("Disponibilités sauvegardées (simulation)");
});

    const addBtn = document.getElementById("addBtn");
const modal = document.getElementById("modal");
const closeModal = document.getElementById("closeModal");
const form = document.getElementById("rdvForm");
const rdvTable = document.getElementById("rdvTable");
const dispoForm = document.getElementById("dispoForm");
const dateInput = document.getElementById("date");

// Ouvrir/fermer la modale
addBtn.onclick = () => {
  modal.style.display = "flex";
  updateDatePickerAvailability();
};

closeModal.onclick = () => {
  modal.style.display = "none";
};

// Ajouter un RDV
form.onsubmit = (e) => {
  e.preventDefault();

  const nom = document.getElementById("nom").value;
  const date = document.getElementById("date").value;
  const heure = document.getElementById("heure").value;
  const motif = document.getElementById("motif").value;

  const row = document.createElement("tr");
  row.innerHTML = `<td>${nom}</td><td>${date}</td><td>${heure}</td><td>${motif}</td>`;
  rdvTable.appendChild(row);

  form.reset();
  modal.style.display = "none";
};

// Obtenir les jours cochés
function getJoursDisponibles() {
  const checkboxes = dispoForm.querySelectorAll("input[type='checkbox']");
  return Array.from(checkboxes)
    .filter(chk => chk.checked)
    .map(chk => parseInt(chk.value));
}

// Valider que la date sélectionnée est disponible
function updateDatePickerAvailability() {
  const joursDisponibles = getJoursDisponibles();

  dateInput.oninput = () => {
    const selectedDate = new Date(dateInput.value);
    const day = selectedDate.getDay(); // 0 (dim) à 6 (sam)

    if (!joursDisponibles.includes(day)) {
      alert("Ce jour n’est pas disponible pour les rendez-vous !");
      dateInput.value = '';
    }
  };
}

// Recalcul si les jours changent
dispoForm.onchange = updateDatePickerAvailability;

</script>