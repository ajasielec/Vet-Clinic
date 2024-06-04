<?php
// Połączenie z bazą danych
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veterinary_clinic";

$conn = new mysqli($servername, $username, $password, $dbname);

// Sprawdzenie połączenia
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pobieranie wszystkich zwierząt i weterynarzy z bazy danych
$sql_pets = "SELECT * FROM pets";
$pets_result = $conn->query($sql_pets);

$sql_vets = "SELECT * FROM veterinarians";
$vets_result = $conn->query($sql_vets);

// Funkcja do sprawdzenia czy data jest przyszła
function isFutureDate($date) {
    $currentDate = date("Y-m-d");
    return $date >= $currentDate;
}

$errors = [];
$success_message = '';

// formularz dodania wizyty
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_appointment'])) {
    // Pobierz dane z formularza
    $pet_id = $_POST['pet_id'];
    $vet_id = $_POST['vet_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $reason = $_POST["reason"];

    if (!isFutureDate($date)) {
       // $errors[] = "Data wizyty nie może być przeszła.";
    }

    // Dodanie wizyty do bazy
    if (empty($errors)) {
        $sql = "INSERT INTO appointments (pet_id, vet_id, date, time, reason) VALUES ('$pet_id', '$vet_id', '$date', '$time', '$reason')";
        if ($conn->query($sql) === TRUE) {
            $success_message = "Nowa wizyta została dodana.";
        } else {
            $errors[] = "Błąd: " . $sql . "<br>" . $conn->error;
            echo "Wizyta nie została dodana.";
        }
    }
}

// formularz anulowania wizyty
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_appointment'])) {
    // ID wizyty z formularza
    $appointment_id = $_POST['appointment_id'];

    // Zapytanie SQL
    $sql = "DELETE FROM appointments WHERE appointment_id='$appointment_id'";

    if ($conn->query($sql) === TRUE) {
        $success_message = "Wizyta została anulowana.";
    } else {
        $errors[] = "Błąd: " . $sql . "<br>" . $conn->error;
    }
}

// formularz dodania leczenia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_treatment'])) {
    // dane z formularza
    $appointment_id = $_POST['appointment_id'];
    $description = $_POST['description'];
    $cost = $_POST['cost'];    

    // Dodanie leczenia do bazy
    if (empty($errors)) {
        $sql = "INSERT INTO treatments (appointment_id, description, cost) VALUES ('$appointment_id', '$description', '$cost')";
        if ($conn->query($sql) === TRUE) {
            $success_message = "Nowe leczenie zostało dodane.";
        } else {
            $errors[] = "Błąd: " . $sql . "<br>" . $conn->error;
        }
    }
}

// pobranie przyszłych wizyt z bazy danych
$sql_future = "SELECT appointments.appointment_id, appointments.date, appointments.time, pets.name AS pet_name, owners.name AS owner_name, veterinarians.name AS vet_name, appointments.reason
        FROM appointments
        JOIN pets ON appointments.pet_id = pets.pet_id
        JOIN owners ON pets.owner_id = owners.owner_id
        JOIN veterinarians ON appointments.vet_id = veterinarians.vet_id
        WHERE appointments.date >= CURDATE()
        ORDER BY appointments.date ASC, appointments.time ASC";
$result_future = $conn->query($sql_future);

// pobranie przeszłych wizyty z bazy danych
$sql_past = "SELECT appointments.appointment_id, appointments.date, appointments.time, pets.name AS pet_name, owners.name AS owner_name, veterinarians.name AS vet_name, appointments.reason, treatments.description, treatments.cost
        FROM appointments
        JOIN pets ON appointments.pet_id = pets.pet_id
        JOIN owners ON pets.owner_id = owners.owner_id
        JOIN veterinarians ON appointments.vet_id = veterinarians.vet_id
        LEFT JOIN treatments ON appointments.appointment_id = treatments.appointment_id
        WHERE appointments.date < CURDATE()
        ORDER BY appointments.date DESC, appointments.time DESC";
$result_past = $conn->query($sql_past);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Zarządzanie Wizytami</title>
    <link rel="stylesheet" href="styles.css"> <!-- Odniesienie do pliku CSS -->
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
<header>
        <h1>System Zarządzania Kliniką Weterynaryjną</h1>
</header>
    <h2>Zarządzanie Wizytami</h2>
    <a href="index.php"><button>Powrót do Strony Głównej</button></a>

    <?php
    // komunikaty o błędach
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }

    // komunikat o sukcesie
    if ($success_message) {
        echo "<p style='color:green;'>$success_message</p>";
    }
    ?>

    <h2>Zaplanuj wizytę</h2>
    <form action="appointments_manager.php" method="post">
        <input type="hidden" name="add_appointment" value="1">
        Zwięrzę:
        <select name="pet_id" required>
            <option value="" disabled selected>Wybierz zwierzę</option>
        <?php
        if ($pets_result->num_rows > 0) {
            while ($row = $pets_result->fetch_assoc()) {
                echo "<option value='" . $row["pet_id"] . "'>" . $row["name"] .  " (" . $row["species"] . ")</option>";
            }
        }
        ?>
        </select>
        Weterynarz:
        <select name="vet_id" required>
            <option value="" disabled selected>Wybierz weterynarza</option>
        <?php
        if ($vets_result->num_rows > 0) {
            while ($row = $vets_result->fetch_assoc()) {
                echo "<option value='" . $row["vet_id"] . "'>" . $row["name"] . "</option>";
            }
        }
        ?>
        </select>
        Data: <input type="date" name="date" required><br>
        Godzina: <input type="time" name="time" required><br>
        Powód: <input type="text" name="reason" required><br>
        <br>
        <input type="submit" value="Dodaj">
    </form>


    <h2>Nadchodzące Wizyty</h2>
    <div class="container">
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Data</th>
            <th>Godzina</th>
            <th>Zwierzę</th>
            <th>Właściciel</th>
            <th>Weterynarz</th>
            <th>Powód wizyty</th>
            <th>Akcja</th>
        </tr>
        <?php
        if ($result_future->num_rows > 0) {
            while($row = $result_future->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["appointment_id"] . "</td>";
                echo "<td>" . $row["date"] . "</td>";
                echo "<td>" . $row["time"] . "</td>";
                echo "<td>" . $row["pet_name"] . "</td>";
                echo "<td>" . $row["owner_name"] . "</td>";
                echo "<td>" . $row["vet_name"] . "</td>";
                echo "<td>" . $row["reason"] . "</td>";
                echo '<td>
                      <form style="display:inline;" action="appointments_manager.php" method="post">
                          <input type="hidden" name="delete_appointment" value="1">
                          <input type="hidden" name="appointment_id" value="' . $row["appointment_id"] . '">
                          <input type="submit" value="Anuluj">
                      </form>
                      </td>';
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8'>Brak danych</td></tr>";
        }
        ?>
    </table>
    </div>

    <h2>Przeszłe Wizyty</h2>
    <div class="container">
<table border="1">
    <tr>
        <th>ID</th>
        <th>Data</th>
        <th>Godzina</th>
        <th>Zwierzę</th>
        <th>Właściciel</th>
        <th>Weterynarz</th>
        <th>Powód wizyty</th>
        <th>Leczenie</th>
        <th>Koszt</th>
        <th>Akcja</th>
    </tr>
    <?php
    if ($result_past->num_rows > 0) {
        while($row = $result_past->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["appointment_id"] . "</td>";
            echo "<td>" . $row["date"] . "</td>";
            echo "<td>" . $row["time"] . "</td>";
            echo "<td>" . $row["pet_name"] . "</td>";
            echo "<td>" . $row["owner_name"] . "</td>";
            echo "<td>" . $row["vet_name"] . "</td>";
            echo "<td>" . $row["reason"] . "</td>";
            echo "<td>". $row["description"] . "</td>";
            echo "<td>". $row["cost"] . "</td>";
            // Dodanie przycisku tylko dla wizyt bez leczenia
            if (empty($row["description"])) {
                echo '<td>
                <button onclick="openTreatmentModal(' . $row["appointment_id"] . ', \'' . $row["date"] . '\')">Dodaj Leczenie</button>
                </td>';
            } else {
                echo "<td></td>";
            }
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7'>Brak danych</td></tr>";
    }
    ?>
</table>
</div>

<!-- Modal do dodawania leczenia -->
<div id="treatmentModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Dodaj Leczenie</h2>
        <form action="appointments_manager.php" method="post">
            <input type="hidden" name="add_treatment" value="1">
            <input type="hidden" id="modal_appointment_id" name="appointment_id">
            Data leczenia: <input type="text" id="modal_treatment_date" name="treatment_date" readonly><br>
            Opis leczenia: <input type="text" name="description" required><br>
            Koszt: <input type="number" step="0.01" name="cost" required><br>
            <input type="submit" value="Dodaj Leczenie">
        </form>
    </div>
</div>

<script>
    function openTreatmentModal(appointmentId, appointmentDate) {
        document.getElementById('modal_appointment_id').value = appointmentId;
        document.getElementById('modal_treatment_date').value = appointmentDate;
        document.getElementById('treatmentModal').style.display = "block";
    }

    function closeModal() {
        document.getElementById('treatmentModal').style.display = "none";
    }
</script>

</body>
</html>
<?php

// Zamknięcie połączenia z bazą danych
$conn->close();
?>