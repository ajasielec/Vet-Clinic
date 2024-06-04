<?php
// Połączenie z bazą danych
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veterinary_clinic";

$conn = new mysqli($servername, $username, $password, $dbname);

// Sprawdzanie połączenie
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pobieranie ID zwierzaka
$pet_id = $_GET['pet_id'];

// Pobieranie imienia zwierzaka
$sql_pet_name = "SELECT name FROM pets WHERE pet_id='$pet_id'";
$pet_name_result = $conn->query($sql_pet_name);
$pet_name_row = $pet_name_result->fetch_assoc();
$pet_name = $pet_name_row["name"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Zaplanowane Wizyty - <?php echo $pet_name; ?></title>
    <link rel="stylesheet" href="styles.css"> <!-- Odniesienie do pliku CSS -->
</head>
<body>
<header>
        <h1>System Zarządzania Kliniką Weterynaryjną</h1>
</header>
    <h2>Zaplanowane Wizyty - <?php echo $pet_name; ?></h2>
    <a href="pets_manager.php"><button>Powrót do Zarządzania Zwierzętami</button></a>
    
    <h2>Wizyty Zwierzaka: <?php echo $pet_name; ?></h2>
    <div class="container">
    <table border="1">
        <tr>
            <th>ID Wizyty</th>
            <th>Data</th>
            <th>Godzina</th>
            <th>Weterynarz</th>
            <th>Powód Wizyty</th>
        </tr>
        <?php
        // Pobieranie zaplanowanych wizyt zwierzaka wraz z informacjami o weterynarzu
        $sql = "SELECT appointments.appointment_id, appointments.date, appointments.time, appointments.reason, veterinarians.name AS vet_name
                FROM appointments
                JOIN veterinarians ON appointments.vet_id = veterinarians.vet_id
                WHERE appointments.pet_id='$pet_id' AND appointments.date >= CURDATE()
                ORDER BY appointments.date ASC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["appointment_id"] . "</td>";
                echo "<td>" . $row["date"] . "</td>";
                echo "<td>" . $row["time"] . "</td>";
                echo "<td>" . $row["vet_name"] . "</td>";
                echo "<td>" . $row["reason"] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>Brak zaplanowanych wizyt</td></tr>";
        }
        ?>
    </table>
    </div>
</body>
</html>

<?php
// Zamknięcie połączenia z bazą danych
$conn->close();
?>
