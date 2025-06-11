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
    if (!$stmt_update) {
        die("Erreur de préparation de la requête: " . $conn->error);
    }
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #007acc;
            --secondary: #0abde3;
            --success: #00b894;
            --danger: #d63031;
            --warning: #fdcb6e;
            --light: #e8f4f8;
            --dark: #004a77;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--light);
        }

        /* Navbar Styles */
        .navbar {
            background: white;
            padding: 10px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            height: 120px;
        }

        .navbar .logo {
            padding: 0;
            flex: 0 0 auto;
        }

        .navbar .logo img {
            height: 180px;
            width: auto;
            margin-top: 30px;
        }

        .navbar nav {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex: 1;
            padding-left: 50px;
        }

        .navbar nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 40px;
        }

        .navbar nav a {
            color: var(--dark);
            font-weight: 500;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .navbar nav a:hover,
        .navbar nav a.active {
            color: var(--primary);
            background: var(--light);
            transform: translateY(-2px);
        }

        /* Main Content adjustments */
        .main-content {
            padding: 30px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        /* Dashboard Stats */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary);
        }

        .stat-card h4 {
            color: var(--dark);
            margin: 0 0 10px 0;
        }

        .stat-card .number {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary);
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: 0.3s;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-warning { background: var(--warning); color: white; }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: var(--primary);
            color: white;
            padding: 12px;
            font-weight: 500;
        }

        .table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .rdv-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.1);
        }

        .rdv-table th {
            background: var(--primary);
            color: white;
            font-weight: 500;
            text-align: left;
            padding: 15px;
        }

        .rdv-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .rdv-table tr:hover {
            background: var(--light);
        }

        /* Headings */
        h1, h2, h3 {
            color: var(--primary);
            font-weight: 600;
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">
            <img src="img/La Centrale1.png" alt="LaCentrale.ma">
        </div>
        <nav>
            <ul>
                <li><a href="javascript:void(0)" onclick="showSection('accueil')">Accueil</a></li>
                <li><a href="javascript:void(0)" onclick="showSection('rdv-list')">Rendez-vous</a></li>
                <li><a href="javascript:void(0)" onclick="showSection('calendar')">Calendrier</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <section id="accueil" class="section card">
            <h2>Tableau de bord</h2>
            <?php
            // Compter les rendez-vous d'aujourd'hui
            $today = date('Y-m-d');
            $sql_today = "SELECT COUNT(*) as count FROM appointments 
                         WHERE doctor_id = ? AND DATE(appointment_datetime) = ? 
                         AND status = 'scheduled'";
            $stmt = $conn->prepare($sql_today);
            $stmt->bind_param("is", $doctor_id, $today);
            $stmt->execute();
            $today_count = $stmt->get_result()->fetch_assoc()['count'];

            // Compter tous les rendez-vous
            $sql_total = "SELECT COUNT(*) as count FROM appointments WHERE doctor_id = ?";
            $stmt = $conn->prepare($sql_total);
            $stmt->bind_param("i", $doctor_id);
            $stmt->execute();
            $total_count = $stmt->get_result()->fetch_assoc()['count'];
            ?>
            <div class="stats-container">
                <div class="stat-card">
                    <h4>Rendez-vous aujourd'hui</h4>
                    <div class="number"><?php echo $today_count; ?></div>
                </div>
                <div class="stat-card">
                    <h4>Total des rendez-vous</h4>
                    <div class="number"><?php echo $total_count; ?></div>
                </div>
            </div>

            <!-- Ajout de la liste des rendez-vous dans l'accueil -->
            <h3>Rendez-vous à venir</h3>
            <table class="rdv-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT a.appointment_id, 
                           p.patient_id,
                           p.name AS patient_name,
                           a.appointment_datetime,
                           a.status
                           FROM appointments a
                           JOIN patients p ON a.patient_id = p.patient_id
                           WHERE a.doctor_id = ? 
                           AND a.status = 'scheduled'
                           AND DATE(a.appointment_datetime) >= CURDATE()
                           ORDER BY a.appointment_datetime ASC
                           LIMIT 5";
                    
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
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td>
                                <button onclick='cancelAppointment({$row['appointment_id']})' class='btn btn-danger'>
                                    Annuler
                                </button>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>Aucun rendez-vous prévu</td></tr>";
                    }
                    $stmt->close();
                    ?>
                </tbody>
            </table>
        </section>

        <section id="rdv-list" class="section card" style="display:none">
            <h2>Tous les rendez-vous</h2>
            <div class="rdv-container">
                <!-- Le contenu existant de la liste complète des rendez-vous -->
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
                    <table class="rdv-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Motif</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT a.appointment_id, 
                                   p.patient_id,
                                   p.name AS patient_name,
                                   a.appointment_datetime,
                                   a.status
                                   FROM appointments a
                                   JOIN patients p ON a.patient_id = p.patient_id
                                   WHERE a.doctor_id = ? 
                                   AND a.status = 'scheduled'
                                   ORDER BY a.appointment_datetime ASC";
                            

                            $stmt = $conn->prepare($sql);
                            if (!$stmt) {
                                die("Erreur de préparation de la requête: " . $conn->error);
                            }
                            $stmt->bind_param("i", $_SESSION['doctor_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $date = new DateTime($row['appointment_datetime']);
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['patient_name']) . " (ID: " . $row['patient_id'] . ")</td>";
                                    echo "<td>" . $date->format('d/m/Y') . "</td>";
                                    echo "<td>" . $date->format('H:i') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                    echo "<td>
                                        <button onclick='cancelAppointment({$row['appointment_id']})' class='btn btn-danger'>
                                            Annuler
                                        </button>
                                    </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>Aucun rendez-vous prévu</td></tr>";
                            }
                            $stmt->close();
                            ?>
                        </tbody>
                    </table>
                </div>
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
    </div>

    <div id="calendar-section" style="display: none;">
 <h2>Ajouter Disponibilité</h2>
 <form id="availability-form" method="post" action="">
 <div>
 <label for="available-date">Date:</label>
 <input type="date" id="available-date" name="available-date" required>
 </div>
 <div>
 <label for="available-time">Heure:</label>
 <input type="time" id="available-time" name="available-time" required>
 </div>
 <button type="submit">Ajouter Disponibilité</button>
 </form>

 <div id="calendar">
 <!-- Calendar will be displayed here -->
 </div>
    </div>

    <script>
        function loadStats() {
            // Récupérer le nombre de rendez-vous d'aujourd'hui
            let todayCount = document.querySelector('.stats-container .today-count');
            let totalCount = document.querySelector('.stats-container .total-count');
            
            fetch('get_stats.php')
            .then(response => response.json())
            .then(data => {
                todayCount.textContent = data.today;
                totalCount.textContent = data.total;
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            showSection('accueil');
        });

        // Remplacer la fonction showSection existante
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

            // Mettre à jour la classe active dans la navigation
            document.querySelectorAll('nav a').forEach(link => {
                link.classList.remove('active');
            });
            document.querySelector(`nav a[onclick*="${sectionId}"]`).classList.add('active');
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