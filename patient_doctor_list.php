<?php
include './db_config.php';
include './config.php';

// Simplifier la requête SQL pour obtenir uniquement les spécialités uniques
$sql_specialties = "SELECT DISTINCT specialty 
                   FROM doctors 
                   WHERE specialty IS NOT NULL 
                   ORDER BY specialty";
$result_specialties = $conn->query($sql_specialties);

$specialties = [];
if ($result_specialties->num_rows > 0) {
    while ($row = $result_specialties->fetch_assoc()) {
        $specialties[] = $row['specialty'];
    }
}

// Améliorer la requête SQL pour inclure plus d'informations
$sql = "SELECT d.doctor_id, d.name, d.specialty, d.contact_info FROM doctors d ORDER BY d.specialty";
$result = $conn->query($sql);

$doctors = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Get unique specialties for the dropdown
$specialties = array_unique(array_column($doctors, 'specialty'));
sort($specialties);

// Assuming generate_url function exists in config.php



$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Médecins à Fès</title>
    <style>
        body {
            background: url('img/patient.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            min-height: 100vh;
        }

        header.header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 40px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            height: 80px; /* Réduire la hauteur */
        }

        .logo {
            display: flex;
            align-items: center;
            height: 100%;
        }

        .logo img {
            height: 200px;  /* Réduire la taille du logo */
            width: auto;
            padding: 5px 0;  /* Réduit de 10px à 5px */
        }

        .btn-pink {
            background-color: #ff4081;
            color: white;
            padding: 15px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-pink:hover {
            background-color: #e91e63;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        .specialty-menu {
            min-width: 250px;
            padding: 12px 20px;
            border: 2px solid #ff4081;
            border-radius: 25px;
            font-size: 16px;
            color: #333;
            background: white;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .specialty-menu option {
            padding: 10px;
            font-size: 14px;
        }

        .specialty-count {
            color: #666;
            font-size: 0.9em;
            margin-left: 5px;
        }

        .doctor-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            padding: 40px;
            margin-top: 100px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .doctor-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .doctor-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .doctor-image {
            width: 100%;
            height: 200px;
            background: white;
            border-bottom: 3px solid #0077b6;
        }

        .doctor-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doctor-info {
            padding: 20px;
        }

        .doctor-info h3 {
            color: #333;
            font-size: 1.4em;
            margin: 0 0 10px 0;
        }

        .doctor-info .specialty {
            color: #0077b6;
            font-weight: 500;
            margin-bottom: 15px;
            display: block;
        }

        .book-appointment-button {
            display: inline-block;
            background: #0077b6;
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            text-align: center;
            width: calc(100% - 50px);
            margin: 10px auto;
        }

        .book-appointment-button:hover {
            background: #005f8d;
            transform: scale(1.05);
        }

        .main-content {
            padding-top: 140px;
        }

        .nav-container {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-left: auto;
        }

        .user-profile {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background: #007acc;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            margin-left: 20px;
        }

        .user-profile i {
            font-size: 20px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="img/la centrale1.png" alt="LaCentrale.ma">
        </div>
        <div class="nav-container">
            <select class="specialty-menu" id="specialtyFilter">
                <option value="all">Toutes les spécialités</option>
                <?php foreach ($specialties as $specialty): ?>
                    <?php if (!empty($specialty)): ?>
                        <option value="<?php echo htmlspecialchars($specialty); ?>">
                            <?php echo htmlspecialchars($specialty); ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <?php if(isset($_SESSION['user_name'])): ?>
                <div class="user-profile">
                    <i>👤</i>
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </div>
            <?php else: ?>
                <a href="<?php echo generate_url('patient_signup.php'); ?>" class="btn-pink">Nous rejoindre</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <div id="doctor-list" class="doctor-list">
            <?php if (!empty($doctors)): ?>
                <?php foreach ($doctors as $doctor): ?>
                    <div class="doctor-card" data-specialty="<?php echo htmlspecialchars($doctor['specialty']); ?>">
                        <div class="doctor-image">
                            <img src="img/doc.jpg" alt="<?php echo htmlspecialchars($doctor['name']); ?>">
                        </div>
                        <div class="doctor-info">
                            <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                            <span class="specialty"><?php echo htmlspecialchars($doctor['specialty']); ?></span>
                            <a href="<?php echo generate_url('agenda.php?doctor_id=' . $doctor['doctor_id']); ?>" 
                               class="book-appointment-button">
                                Prendre rendez-vous
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        document.getElementById('specialtyFilter').addEventListener('change', function() {
            const selectedSpecialty = this.value;
            const doctorCards = document.querySelectorAll('.doctor-card');
            
            doctorCards.forEach(card => {
                const cardSpecialty = card.dataset.specialty;
                if (selectedSpecialty === 'all' || 
                    (selectedSpecialty === 'none' && !cardSpecialty) || 
                    cardSpecialty === selectedSpecialty) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
