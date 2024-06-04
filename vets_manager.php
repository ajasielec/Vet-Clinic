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

$errors = [];
$success_message = '';

// formularz dodania weterynarza
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_vet'])) {
    // dane z formularza
    $vet_name = $_POST['vet_name'];
    $specialization = $_POST['specialization'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];

    // zapytanie SQL
    $sql = "INSERT INTO veterinarians (name, specialization, phone_number, email) VALUES ('$vet_name', '$specialization', '$phone_number', '$email')";

    if ($conn->query($sql) === TRUE) {
        $success_message = "Nowy weterynarz został dodany.<br>";
    } else {
        $errors[] = "Błąd: " . $sql . "<br>" . $conn->error;
    }
}

// formularz usunięcia weterynarza
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_vet'])) {
    // ID weterynarza z formularza
    $vet_id = $_POST['vet_id'];

    // zapytanie SQL
    $sql = "DELETE FROM veterinarians WHERE vet_id='$vet_id'";

    if ($conn->query($sql) === TRUE) {
        $success_message = "Weterynarz został usunięty.<br>";
    } else {
        $errors[] = "Błąd: " . $sql . "<br>" . $conn->error;
    }
}

// Pobranie wszystkich weterynarzy z bazy danych
$sql = "SELECT * FROM veterinarians";
$all_vets_result = $conn->query($sql);

// przyszłe i przeszłe wizyty dla wybranego weterynarza
$vet_appointments_result = null;
$vet_name = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['show_future_appointments']) || isset($_POST['show_past_appointments'])) {
        $vet_id = $_POST['vet_id'];

        // imię i nazwisko weterynarza na podstawie jego ID
        $sql_vet_name = "SELECT name FROM veterinarians WHERE vet_id='$vet_id'";
        $result_vet_name = $conn->query($sql_vet_name);
        if ($result_vet_name->num_rows > 0) {
            $row_vet_name = $result_vet_name->fetch_assoc();
            $vet_name = $row_vet_name["name"];
        }

        // sprawdzenie, czy pokazać przyszłe wizyty czy przeszłe
        $condition = isset($_POST['show_future_appointments']) ? "appointments.date > CURDATE()" : "appointments.date < CURDATE()";

        // wizyty dla wybranego weterynarza
        $sql = "SELECT appointments.date, appointments.time, pets.name AS pet_name, owners.name AS owner_name, appointments.reason
                FROM appointments
                JOIN pets ON appointments.pet_id = pets.pet_id
                JOIN owners ON pets.owner_id = owners.owner_id
                WHERE appointments.vet_id = '$vet_id' AND $condition
                ORDER BY appointments.date ASC, appointments.time ASC";
        $vet_appointments_result = $conn->query($sql);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Zarządzanie Weterynarzami</title>
    <link rel="stylesheet" href="styles.css"> <!-- Odniesienie do pliku CSS -->
</head>
<body>
<header>
        <h1>System Zarządzania Kliniką Weterynaryjną</h1>
</header>
    <h2>Zarządzanie Weterynarzami</h2>
    <a href="index.php"><button>Powrót do Strony Głównej</button></a>
    
    <h2>Dodaj nowego weterynarza</h2>
    <form action="vets_manager.php" method="post">
        <input type="hidden" name="add_vet" value="1">
        Name: <input type="text" name="vet_name" required><br>
        Specialization: <input type="text" name="specialization" required><br>
        Phone Number: <input type="text" name="phone_number" required><br>
        Email: <input type="email" name="email" required><br>
        <input type="submit" value="Dodaj">
    </form>

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


    <h2>Lista Weterynarzy</h2>
    <div class="container">
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Specialization</th>
            <th>Phone Number</th>
            <th>Email</th>
            <th>Akcja</th>
        </tr>
        <?php

        if ($all_vets_result->num_rows > 0) {
            while($row = $all_vets_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["vet_id"] . "</td>";
                echo "<td>" . $row["name"] . "</td>";
                echo "<td>" . $row["specialization"] . "</td>";
                echo "<td>" . $row["phone_number"] . "</td>";
                echo "<td>" . $row["email"] . "</td>";
                echo '<td>
                          <form style="display:inline;" action="vets_manager.php" method="post">
                              <input type="hidden" name="delete_vet" value="1">
                              <input type="hidden" name="vet_id" value="' . $row["vet_id"] . '">
                              <input type="submit" value="Usuń">
                          </form>
                          <form style="display:inline;" action="vets_appointments.php" method="post">
                              <input type="hidden" name="show_appointments" value="' . $row["vet_id"] . '">
                              <input type="submit" value="Wyświetl wizyty">
                          </form>
                      </td>';
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>Brak danych</td></tr>";
        }
        ?>
    </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>