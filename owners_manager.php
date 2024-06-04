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

// formularz dodania właściciela
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_owner'])) {
    // dane z formularza
    $owner_name = $_POST['owner_name'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];

    // zapytanie SQL
    $sql = "INSERT INTO owners (name, phone_number, email) VALUES ('$owner_name', '$phone_number', '$email')";

    // dodanie wlasciciela do bazy  
        if ($conn->query($sql) === TRUE) {
            $success_message = "Nowy właściciel dodany.";
        } else {
            $errors[] = "Błąd: " . $sql . "<br>" . $conn->error;
        }
}

// formularz usunięcia właściciela
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_owner'])) {
    // ID właściciela z formularza
    $owner_id = $_POST['owner_id'];

    // zapytanie SQL
    $sql = "DELETE FROM owners WHERE owner_id='$owner_id'";

    if ($conn->query($sql) === TRUE) {
        $success_message = "Właściciel został usunięty.";
    } else {
        $errors[] = "Błąd: " . $sql . "<br>" . $conn->error;
    }
}

// Pobranie właścicieli z bazy danych
$sql = "SELECT * FROM owners";
$all_owners_result = $conn->query($sql);

// Pobieranie zwierząt przypisane do wybranego właściciela
$owner_pets_result = null;
$owner_name = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['show_pets'])) {
    $owner_id = $_POST['owner_id'];

    // Pobranie imienia i nazwiska właściciela na podstawie jego ID
    $sql_owner_name = "SELECT name FROM owners WHERE owner_id='$owner_id'";
    $result_owner_name = $conn->query($sql_owner_name);
    if ($result_owner_name->num_rows > 0) {
        $row_owner_name = $result_owner_name->fetch_assoc();
        $owner_name = $row_owner_name["name"];
    }

    // Pobranie zwierząt przypisane do wybranego właściciela
    $sql_pets = "SELECT * FROM pets WHERE owner_id='$owner_id'";
    $owner_pets_result = $conn->query($sql_pets);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Zarządzanie Właścicielami</title>
        <link rel="stylesheet" href="styles.css"> <!-- Odniesienie do pliku CSS -->
</head>
<body>
<header>
        <h1>System Zarządzania Kliniką Weterynaryjną</h1>
</header>
    <h2>Zarządzanie Właścicielami</h2>
    <a href="index.php"><button>Powrót do Strony Głównej</button></a>
    
    <h2>Dodaj nowego właściciela</h2>
    <form action="owners_manager.php" method="post">
        <input type="hidden" name="add_owner" value="1">
        Name: <input type="text" name="owner_name" required><br>
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

    <h2>Lista Właścicieli</h2>
    <div class="container">
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Phone Number</th>
            <th>Email</th>
            <th>Akcja</th>
        </tr>
        <?php
        if ($all_owners_result->num_rows > 0) {
            while($row = $all_owners_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["owner_id"] . "</td>";
                echo "<td>" . $row["name"] . "</td>";
                echo "<td>" . $row["phone_number"] . "</td>";
                echo "<td>" . $row["email"] . "</td>";
                echo '<td>
                          <form style="display:inline;" action="owners_manager.php" method="post">
                              <input type="hidden" name="delete_owner" value="1">
                              <input type="hidden" name="owner_id" value="' . $row["owner_id"] . '">
                              <input type="submit" value="Usuń">
                          </form>
                          <form style="display:inline;" action="pets_list.php" method="post">
                              <input type="hidden" name="show_pets" value="1">
                              <input type="hidden" name="owner_id" value="' . $row["owner_id"] . '">
                              <input type="submit" value="Pokaż Zwierzęta">
                          </form>
                      </td>';
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>Brak danych</td></tr>";
        }
        ?>
    </table>
    </div>

    <?php
    if ($owner_pets_result) {
        echo "<h2>Zwierzęta należące do $owner_name</h2>";
        if ($owner_pets_result->num_rows > 0) {
            echo '<table border="1">
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
            echo '</table>';
        } else {
            echo "Brak zwierząt należących do $owner_name.";
        }
    }
    ?>

</body>
</html>

<?php
$conn->close();
?>
