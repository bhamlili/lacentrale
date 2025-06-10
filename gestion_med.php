<?php
session_start();

include 'config.php';

// Database connection details
include 'db_config.php';


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
        header("Location: " . generate_url('gestion_med.php'));
        exit();
    } else {
        // Handle error if cancellation fails
        echo "Error cancelling appointment: " . $conn->error;
    }
    $stmt_cancel->close();
}

// Handle form submission for adding new appointment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_appointment'])) {
    $patient_name = $_POST['patient_name'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $motif = $_POST['motif'];

    // Combine date and time for the database
    $appointment_datetime = $appointment_date . ' ' . $appointment_time;
    // For simplicity, assuming patient name is unique or you'll handle patient creation/selection

    // In a real app, you would likely have a way to link to an existing patient or create a new one.
    // For this example, let's assume we have a patient with ID 1 for now.
    // You would need to implement logic to find or create a patient based on the provided name.
    $patient_id = 1; // Replace with actual patient ID lookup/creation logic

    $sql_insert_appointment = "INSERT INTO appointments (doctor_id, patient_id, appointment_datetime, motif, status) VALUES (?, ?, ?, ?, 'scheduled')";
    $stmt_insert_appointment = $conn->prepare($sql_insert_appointment);
    // Assuming 'motif' column exists in 'appointments' table. If not, remove it from the query and bind_param
    $stmt_insert_appointment->bind_param("iiss", $doctor_id, $patient_id, $appointment_datetime, $motif);
    if ($stmt_insert_appointment->execute()) {
        // Redirect back to the gestion_med page after successful addition
        $_SESSION['success_message'] = 'Rendez-vous ajouté avec succès.';
        header("Location: " . generate_url('gestion_med.php'));
        exit();
    } else {
        $_SESSION['error_message'] = 'Erreur lors de l\'ajout du rendez-vous: ' . $conn->error;
        // Respond with error
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout du rendez-vous: ' . $conn->error]);
    }
    $stmt_insert_appointment->close();
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

// Remplacer le handler existant par celui-ci
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_availability'])) {
    $date_calendrier = $_POST['date_calendrier'];
    $heure_calendrier = $_POST['heure_calendrier'];

    $sql = "INSERT INTO calendrier (date_calendrier, heure_calendrier) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $date_calendrier, $heure_calendrier);
    
    if ($stmt->execute()) {
        echo "<script>alert('Disponibilité ajoutée avec succès');</script>";
    } else {
        echo "<script>alert('Erreur lors de l'ajout');</script>";
    }
    $stmt->close();
}

// Ajouter ce handler en haut du fichier avec les autres handlers POST
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['section']) && $_GET['section'] == 'rdv') {
    header('Content-Type: application/json');
    $sql = "SELECT a.appointment_id, 
            p.name AS patient_name,
            p.phone AS patient_phone, 
            a.appointment_datetime,
            a.status,
            a.motif,
            d.name AS doctor_name,
            d.specialty
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            JOIN doctors d ON a.doctor_id = d.doctor_id
            WHERE a.doctor_id = ? 
            AND a.status = 'scheduled'
            ORDER BY a.appointment_datetime ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['doctor_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    echo json_encode($appointments);
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion des Rendez-vous - LaCentrale.ma</title>
    <style>
        header {
            background: white;
            padding: 10px 40px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 120px; /* Augmenté pour accommoder le plus grand logo */
        }

        .logo img {
            height: 120px; /* Augmenté à 120px */
            width: auto;
        }

        .logo {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
        }

        nav {
            flex: 1 1 auto;
            display: flex;
            justify-content: flex-end;
        }

        nav ul {
            display: flex;
            gap: 30px;
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
        }

        nav a {
            color: #333;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #0077b6;
        }

        .add-rdv-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .alert {
            padding: 10px; margin-bottom: 15px; border-radius: 4px;
        }

        body {
            font-weight: 600;
            font-family: Arial, sans-serif;
            font-size: 16px;
            margin: 0;
            background: #f7f9fc;
        }

        .dispo-section {
            padding: 20px;
            margin: 20px;
            border-radius: 10px;
        };

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
            margin: 20px;
            margin-top: 10px;
            border-radius: 10px;
            background-color: #0077b6;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        #saveDispoBtn:hover {
            background-color: #005b8e;
        }

        main {
            padding: 20px;
        }

        button {
            background-color: #0077b6;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #005f87;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin-top: 20px;
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

        .calendar-form {
            max-width: 600px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .day-slot {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        .day-header {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .time-slots {
            display: flex;
            gap: 20px;
            margin-left: 25px;
        }

        .time-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .time-input input[type="time"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .save-availability {
            margin-bottom: 10px;
            background: #0077b6;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }

        .save-availability:hover {
            background: #005f8d;
        }

        .availability-form {
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 20px auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .save-btn {
            background: #0077b6;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 20px;
        }

        .rdv-table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 5px;
            overflow: hidden;
            border: 2px solid #0077b6; /* Ajout de la bordure bleue */
        }

        .rdv-table th, .rdv-table td {
            padding: 15px;
            border: 1px solid #0077b6; /* Bordures intérieures bleues */
        }

        .rdv-table thead {
            background-color: #0077b6;
            color: white;
        }

        .section-title {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .rdv-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .btn-cancel {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background: #bb2d3b;
        }

        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="img/la centrale1.png" alt="LaCentrale.ma">
        </div>
        <nav>
            <ul>
                <li><a href="gestion_med.php">Accueil</a></li>
                <li><a href="javascript:void(0)" onclick="showSection('rdv-list')">Rendez-vous</a></li>
                <li><a href="javascript:void(0)" onclick="showSection('calendar')">Calendrier</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section id="appointments" class="section">
            <div class="add-rdv-section">
                <h3>Ajouter un rendez-vous</h3>
                <form id="manual-rdv-form" method="POST">
                    <!-- Formulaire d'ajout de rendez-vous manuel -->
                    <input type="hidden" name="add_appointment" value="1">
                    <label>Nom du patient: <input type="text" id="patient_name" name="patient_name" required></label>
                    <label>Date: <input type="date" id="appointment_date" name="appointment_date" required></label>
                    <label>Heure: <input type="time" id="appointment_time" name="appointment_time" required></label>
                    <label>Motif: <input type="text" id="motif" required></label>
                    <button type="submit">Ajouter</button>
                </form>
            </div>
            <div class="rdv-list">
                <h3>Liste des rendez-vous</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nom du patient</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Motif</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Récupérer les rendez-vous du médecin connecté
                        $sql = "SELECT a.appointment_id, p.name AS patient_name, a.appointment_datetime, a.motif, a.status
                               FROM appointments a
                               JOIN patients p ON a.patient_id = p.patient_id
                               WHERE a.doctor_id = ? 
                               AND a.status = 'scheduled'
                               ORDER BY a.appointment_datetime ASC";
                        
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $_SESSION['doctor_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $date = new DateTime($row['appointment_datetime']);
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['patient_name']) . "</td>";
                                echo "<td>" . $date->format('d/m/Y') . "</td>";
                                echo "<td>" . $date->format('H:i') . "</td>";
                                echo "<td>" . htmlspecialchars($row['motif']) . "</td>";
                                echo "<td>
                                    <form method='post' action=''>
                                        <input type='hidden' name='cancel_appointment' value='1'>
                                        <input type='hidden' name='appointment_id' value='" . $row['appointment_id'] . "'>
                                        <button type='submit' class='btn-cancel'>Annuler</button>
                                    </form>
                                </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align: center;'>Aucun rendez-vous prévu</td></tr>";
                        }
                        $stmt->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
        <section id="rdv-list" class="section" style="display:none">
            <h3 class="section-title">Liste des Rendez-vous</h3>
            <div class="rdv-container">
                <table class="rdv-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Téléphone</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Statut</th>
                            <th>Médecin</th>
                            <th>Spécialité</th>
                            <th>Motif</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="rdv-tbody">
                        <!-- Les données seront chargées ici dynamiquement -->
                    </tbody>
                </table>
            </div>
        </section>
        <section id="calendar" class="section" style="display:none">
            <h3>Ajouter une disponibilité</h3>
            <form method="POST" class="calendar-form">
                <input type="hidden" name="save_availability" value="1">
                <div class="form-group">
                    <label>Date :</label>
                    <input type="date" name="date_calendrier" required>
                </div>
                <div class="form-group">
                    <label>Heure :</label>
                    <input type="time" name="heure_calendrier" required>
                </div>
                <button type="submit" class="save-btn">Enregistrer</button>
            </form>

            <div class="calendar-display">
                <h4>Disponibilités enregistrées</h4>
                <table class="rdv-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM calendrier ORDER BY date_calendrier, heure_calendrier";
                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . date('d/m/Y', strtotime($row['date_calendrier'])) . "</td>";
                                echo "<td>" . date('H:i', strtotime($row['heure_calendrier'])) . "</td>";
                                echo "<td>
                                        <form method='post' style='display:inline;'>
                                            <input type='hidden' name='delete_id' value='" . $row['id'] . "'>
                                            <button type='submit' class='btn-cancel'>Supprimer</button>
                                        </form>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' style='text-align:center;'>Aucune disponibilité</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <script>
        // Afficher la section accueil par défaut au chargement
        document.addEventListener('DOMContentLoaded', function() {
            showSection('appointments');
        });

        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(section => {
                section.style.display = 'none';
            });

            const selectedSection = document.getElementById(sectionId);
            if (selectedSection) {
                selectedSection.style.display = 'block';
                if (sectionId === "rdv-list") {
                    loadAppointments();
                }
            }
        }

        function loadAppointments() {
            fetch("gestion_med.php?section=rdv")
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById("rdv-tbody");
                    tbody.innerHTML = "";
                    
                    if (data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="9" class="no-data">Aucun rendez-vous prévu</td></tr>`;
                        return;
                    }
                    
                    data.forEach(rdv => {
                        const date = new Date(rdv.appointment_datetime);
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
                            <td>${escapeHtml(rdv.patient_name)}</td>
                            <td>${escapeHtml(rdv.patient_phone)}</td>
                            <td>${date.toLocaleDateString()}</td>
                            <td>${date.toLocaleTimeString([], {hour: "2-digit", minute: "2-digit"})}</td>
                            <td>${escapeHtml(rdv.status)}</td>
                            <td>${escapeHtml(rdv.doctor_name)}</td>
                            <td>${escapeHtml(rdv.specialty)}</td>
                            <td>${escapeHtml(rdv.motif)}</td>
                            <td>
                                <button class="btn-cancel" onclick="cancelAppointment(${rdv.appointment_id})">
                                    Annuler
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(error => console.error("Erreur:", error));
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/\'/g, "&#039;");
        }

        function cancelAppointment(appointmentId) {
            if (confirm('Voulez-vous vraiment annuler ce rendez-vous ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="cancel_appointment" value="1">
                    <input type="hidden" name="appointment_id" value="${appointmentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteAvailability(id) {
            if (confirm('Voulez-vous vraiment supprimer cette disponibilité ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="delete_availability" value="1">
                    <input type="hidden" name="availability_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    <style>
        nav a.active {
            color: #0077b6;
            font-weight: bold;
            border-bottom: 2px solid #0077b6;
        }
    </style>
</body>
</html>