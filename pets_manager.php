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
$sql_owners = "SELECT * FROM owners";
$owners_result = $conn->query($sql_owners);

// Funkcja do sprawdzenia, czy data jest przeszła
function isPastDate($date) {
    $currentDate = date("Y-m-d");
    return $date <= $currentDate;
}

$errors = [];
$success_message = '';

// formularz dodania zwierzaka
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_pet'])) {
    // dane z formularza
    $name = $_POST['name'];
    $species = $_POST['species'];
    $breed = $_POST['breed'];
    $birthdate = $_POST['birthdate'];
    $owner_id = $_POST['owner_id'];

    if (!isPastDate($birthdate)) {
        $errors[] = "Data narodzin zwierzaka nie może być przyszła.";
    }

    // dodanie zwierzaka do bazy
    if (empty($errors)) {
        $sql = "INSERT INTO pets (name, species, breed, birthdate, owner_id) VALUES ('$name', '$species', '$breed', '$birthdate', '$owner_id')";
        if ($conn->query($sql) === TRUE) {
            $success_message = "Dodano nowe zwięrzę.";
        } else {
            $errors[] = "Błąd: " . $sql . "<br>" . $conn->error;
        }
    } 
}

// formularz usunięcia zwierzaka
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_pet'])) {
    // ID zwierzaka z formularza
    $pet_id = $_POST['pet_id'];

    // zapytanie SQL
    $sql = "DELETE FROM pets WHERE pet_id='$pet_id'";

    if ($conn->query($sql) === TRUE) {
        $success_message = "Zwierzę zostało usunięte.<br>";
    } else {
        $errors[] = "Błąd: " . $sql . "<br>" . $conn->error;
    }
}

// Pobranie wszystkich zwierząt z bazy danych
$sql = "SELECT pets.pet_id, pets.name AS pet_name, pets.species, pets.breed, pets.birthdate, owners.name AS owner_name
        FROM pets
        JOIN owners ON pets.owner_id = owners.owner_id";
$all_pets_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Zarządzanie Zwierzętami</title>
    <link rel="stylesheet" href="styles.css"> <!-- Odniesienie do pliku CSS -->
</head>
<body>
<header>
        <h1>System Zarządzania Kliniką Weterynaryjną</h1>
</header>
    <h2>Zarządzanie Zwierzętami</h2>
    <a href="index.php"><button>Powrót do Strony Głównej</button></a>
    
    <h2>Dodaj nowe zwierzę</h2>
    <form action="pets_manager.php" method="post">
        <input type="hidden" name="add_pet" value="1">
        Imię: <input type="text" name="name" required><br>
        Gatunek: <input type="text" name="species" required><br>
        Rasa: <input type="text" name="breed" required><br>
        Data urodzenia: <input type="date" name="birthdate" required><br>
        Właściciel: 
        <select name="owner_id" required>
            <option value="" disabled selected>Wybierz właściciela</option>
        <?php
        if ($owners_result->num_rows > 0) {
            while ($row = $owners_result->fetch_assoc()) {
                echo "<option value='" . $row["owner_id"] . "'>" . $row["name"] . "</option>";
            }
        }
        ?>
        </select>
        <br>
        <input type="submit" value="Dodaj">
    </form>

    <?php
    // komunikaty o błędach
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
        echo "<p style='color:red;'>Zwierzę nie zostało dodane.</p>";
    }

    // komunikat o sukcesie
    if ($success_message) {
        echo "<p style='color:green;'>$success_message</p>";
    }
    ?>

    <h2>Lista Zwierząt</h2>
    <div class="container">
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Imię</th>
            <th>Gatunek</th>
            <th>Rasa</th>
            <th>Data urodzenia</th>
            <th>Właściciel</th>
            <th>Akcja</th>
        </tr>
        <?php
        if ($all_pets_result->num_rows > 0) {
            while($row = $all_pets_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["pet_id"] . "</td>";
                echo "<td>" . $row["pet_name"] . "</td>";
                echo "<td>" . $row["species"] . "</td>";
                echo "<td>" . $row["breed"] . "</td>";
                echo "<td>" . $row["birthdate"] . "</td>";
                echo "<td>" . $row["owner_name"] . "</td>";
                echo '<td>
                <form style="display:inline;" action="appointments_list.php" method="get">
                <input type="hidden" name="pet_id" value="' . $row["pet_id"] . '">
                <input type="submit" value="Zaplanowane Wizyty">
                </form>
                <form style="display:inline;" action="pets_manager.php" method="post">
                  <input type="hidden" name="delete_pet" value="1">
                  <input type="hidden" name="pet_id" value="' . $row["pet_id"] . '">
                  <input type="submit" value="Usuń">
                </form>
                <form style="display:inline;" action="treatments_list.php" method="get">
                          <input type="hidden" name="pet_id" value="' . $row["pet_id"] . '">
                          <input type="submit" value="Odbyte Leczenia">
                      </form>
                      </td>';
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>Brak danych</td></tr>";
        }
        ?>
    </table>
    </div>

</body>
</html>

<?php
$conn->close();
?>
