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

// Pobranie ID zwierzaka z parametru URL
$pet_id = $_GET['pet_id'];

// Pobranie imienia zwierzaka
$sql_pet_name = "SELECT name FROM pets WHERE pet_id='$pet_id'";
$pet_name_result = $conn->query($sql_pet_name);
$pet_name_row = $pet_name_result->fetch_assoc();
$pet_name = $pet_name_row["name"];

// Pobranie leczenia przypisanego do przeszłych wizyt zwierzaka
$sql = "SELECT treatments.treatment_id, treatments.description, treatments.cost, appointments.date AS appointment_date
        FROM treatments
        JOIN appointments ON treatments.appointment_id = appointments.appointment_id
        WHERE appointments.pet_id='$pet_id' AND appointments.date <= CURDATE()
        ORDER BY appointments.date ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista Leczeń</title>
    <link rel="stylesheet" href="styles.css"> <!-- Odniesienie do pliku CSS -->
</head>
<body>
<header>
        <h1>System Zarządzania Kliniką Weterynaryjną</h1>
</header>
    <h2>Lista Leczeń</h2>
    <a href="pets_manager.php"><button>Powrót do Zarządzania Zwierzętami</button></a>
    
    <h2>Leczenia Zwierzaka: <?php echo $pet_name; ?></h2>
    <div class="container">
    <table border="1">
        <tr>
            <th>ID Leczenia</th>
            <th>Data Wizyty</th>
            <th>Opis</th>
            <th>Koszt</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["treatment_id"] . "</td>";
                echo "<td>" . $row["appointment_date"] . "</td>";
                echo "<td>" . $row["description"] . "</td>";
                echo "<td>" . $row["cost"] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>Brak leczeń</td></tr>";
        }
        ?>
    </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>
