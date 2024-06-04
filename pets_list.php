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

// Pobranie wszystkich właścicieli z bazy danych
$sql = "SELECT * FROM owners";
$all_owners_result = $conn->query($sql);

// Pobranie zwierząt przypisanych do wybranego właściciela
$owner_pets_result = null;
$owner_name = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['show_pets'])) {
    $owner_id = $_POST['owner_id'];

    $sql_owner_name = "SELECT name FROM owners WHERE owner_id='$owner_id'";
    $result_owner_name = $conn->query($sql_owner_name);
    if ($result_owner_name->num_rows > 0) {
        $row_owner_name = $result_owner_name->fetch_assoc();
        $owner_name = $row_owner_name["name"];
    }

    $sql_pets = "SELECT * FROM pets WHERE owner_id='$owner_id'";
    $owner_pets_result = $conn->query($sql_pets);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Zaplanowane Wizyty - <?php echo $owner_name; ?></title>
    <link rel="stylesheet" href="styles.css"> <!-- Odniesienie do pliku CSS -->
</head>
<body>
<header>
        <h1>System Zarządzania Kliniką Weterynaryjną</h1>
</header>
    <h2>Zwierzęta - <?php echo $owner_name; ?></h2>
    <a href="owners_manager.php"><button>Powrót do Zarządzania Właścicielami</button></a>
    
    <?php
    if ($owner_pets_result) {
        echo "<h2>Zwierzęta należące do $owner_name</h2>";
        if ($owner_pets_result->num_rows > 0) {
            echo '  <div class="container">
                    <table border="1">
                    <tr>
                        <th>ID</th>
                        <th>Imię</th>
                        <th>Gatunek</th>
                        <th>Rasa</th>
                    </tr>';
            while ($row = $owner_pets_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["pet_id"] . "</td>";
                echo "<td>" . $row["name"] . "</td>";
                echo "<td>" . $row["species"] . "</td>";
                echo "<td>" . $row["breed"] . "</td>";
                echo "</tr>";
            }
            echo '</table>
                    </div>';
        } else {
            echo "  Brak zwierząt należących do $owner_name.";
        }
    }
    ?>

</body>
</html>

<?php
$conn->close();
?>
