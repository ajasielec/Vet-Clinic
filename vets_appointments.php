<?php
// Połączenie z bazą danych
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veterinary_clinic";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Wizyty Weterynarza</title>
        <link rel="stylesheet" href="styles.css"> <!-- Odniesienie do pliku CSS -->
</head>
<body>
<header>
        <h1>System Zarządzania Kliniką Weterynaryjną</h1>
</header>
    <a href="vets_manager.php"><button>Powrót do Zarządzania Weterynarzami</button></a>
    
    <?php
    // akcja "Wyświetl wizyty"
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['show_appointments'])) {
        $vet_id = $_POST['show_appointments'];

        // Pobranie imienia i nazwiska weterynarza na podstawie jego ID
        $sql_vet_name = "SELECT name FROM veterinarians WHERE vet_id='$vet_id'";
        $result_vet_name = $conn->query($sql_vet_name);
        if ($result_vet_name->num_rows > 0) {
            $row_vet_name = $result_vet_name->fetch_assoc();
            $vet_name = $row_vet_name["name"];
        }

        // przyszłe wizyty dla wybranego weterynarza
        $sql_future = "SELECT appointments.date, appointments.time, pets.name AS pet_name, pets.species, pets.breed, owners.name AS owner_name, appointments.reason
            FROM appointments
            JOIN pets ON appointments.pet_id = pets.pet_id
            JOIN owners ON pets.owner_id = owners.owner_id
            WHERE appointments.vet_id = '$vet_id' AND appointments.date >= CURDATE()
            ORDER BY appointments.date ASC, appointments.time ASC";
        $result_future = $conn->query($sql_future);

        // przeszłe wizyty dla wybranego weterynarza
        $sql_past = "SELECT appointments.date, appointments.time, pets.name AS pet_name, pets.species, pets.breed, owners.name AS owner_name, appointments.reason
            FROM appointments
            JOIN pets ON appointments.pet_id = pets.pet_id
            JOIN owners ON pets.owner_id = owners.owner_id
            WHERE appointments.vet_id = '$vet_id' AND appointments.date < CURDATE()
            ORDER BY appointments.date DESC, appointments.time DESC";
        $result_past = $conn->query($sql_past);

        echo "<h2>Wizyty dla $vet_name</h2>";

        echo "<h3>Nadchodzące wizyty:</h3>";
        if ($result_future->num_rows > 0) {
            echo '  <div class="container">
                    <table border="1">
                    <tr>
                        <th>Data</th>
                        <th>Godzina</th>
                        <th>Zwierzę</th>
                        <th>Gatunek</th>
                        <th>Rasa</th>
                        <th>Właściciel</th>
                        <th>Powód</th>
                    </tr>';
            while ($row = $result_future->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["date"] . "</td>";
                echo "<td>" . $row["time"] . "</td>";
                echo "<td>" . $row["pet_name"] . "</td>";
                echo "<td>". $row["species"] . "</td>";
                echo "<td>". $row["breed"] . "</td>";
                echo "<td>" . $row["owner_name"] . "</td>";
                echo "<td>" . $row["reason"] . "</td>";
                echo "</tr>";
            }
            echo '</table>
                </div>';
        } else {
            echo "  Brak przyszłych wizyt dla $vet_name.";
        }

        echo "<h3>Przeszłe wizyty:</h3>";
        if ($result_past->num_rows > 0) {
            echo '<div class="container">
                    <table border="1">
                    <tr>
                        <th>Data</th>
                        <th>Godzina</th>
                        <th>Zwierzę</th>
                        <th>Gatunek</th>
                        <th>Rasa</th>
                        <th>Właściciel</th>
                        <th>Powód</th>
                    </tr>';
            while ($row = $result_past->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["date"] . "</td>";
                echo "<td>" . $row["time"] . "</td>";
                echo "<td>" . $row["pet_name"] . "</td>";
                echo "<td>". $row["species"] . "</td>";
                echo "<td>". $row["breed"] . "</td>";
                echo "<td>" . $row["owner_name"] . "</td>";
                echo "<td>" . $row["reason"] . "</td>";
                echo "</tr>";
            }
            echo '</table>
                </div>';
        } else {
            echo "  Brak przeszłych wizyt dla $vet_name.";
        }
    }
    ?>
</body>
</html>