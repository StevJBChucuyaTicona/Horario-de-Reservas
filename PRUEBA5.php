<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$dbname = 'profesoresfaz';
$user = 'root';
$pass = '';

// **CORRECCIÓN: Guardamos la fecha en que se hizo la reserva (fecha actual)**
$fecha_hoy = date('Y-m-d'); // Fecha de la reserva

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si el usuario ha iniciado sesión
    if (isset($_SESSION['dni'])) {
        $dni = $_SESSION['dni'];

        // Verificar si existen reservas en la sesión
        if (isset($_SESSION['reservas']) && !empty($_SESSION['reservas'])) {
            $selecciones = $_SESSION['reservas']; // Recuperar las reservas de la sesión
        } else {
            echo "No hay reservas en la sesión."; // Mensaje de error si no hay reservas en la sesión
            exit();
        }

        // Obtén la información del profesor
        $sql = "SELECT profesores.id_p, profesores.nombre, profesores.apellido 
                FROM profesores
                INNER JOIN dni_profesor ON profesores.id_p = dni_profesor.id_p
                WHERE dni_profesor.dni = :dni";
        $stm = $pdo->prepare($sql);
        $stm->bindValue(':dni', $dni);
        $stm->execute();
        $usuario = $stm->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            echo "No se encontró información del profesor.";
            exit();
        }

        $mensaje = '';
        $botons = 0;

        // Si el formulario es enviado
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Obtener datos adicionales del formulario
            $cantAlums = $_POST['alumnos'] ?? '';  // Cantidad de alumnos
            $mates = $_POST['materia'] ?? '';      // Materia
            $area = $_POST['grasec'] ?? '';        // Grado y sección

            // Verificar que `$reservas` esté definida y sea un array válido
            $reservas = explode(',', $selecciones);  // Convertir las reservas en un array   
            if (is_array($reservas) || is_object($reservas)) {
                foreach ($reservas as $reserva) {
                    $partes = explode('|', $reserva);
                    if (count($partes) === 2) {
                        // Aquí se asume que las reservas ya incluyen el día y la hora
                        list($fecha_reservada, $hora) = $partes; // Usar directamente el día y la hora de la reserva
                         // Aquí el **día** ya viene de **PRUEBA4.php** en `data-dia`, no hace falta calcularlo
                         $dia = date('l', strtotime($fecha_reservada)); // Obtener el día en formato largo
                        
                         // Convertir el día al español
                         $daysOfWeekInSpanish = [
                             'Monday' => 'Lunes',
                             'Tuesday' => 'Martes',
                             'Wednesday' => 'Miércoles',
                             'Thursday' => 'Jueves',
                             'Friday' => 'Viernes',
                             'Saturday' => 'Sábado',
                             'Sunday' => 'Domingo',
                         ];
                         $dia = $daysOfWeekInSpanish[$dia]; // Convertir a español
                         echo "Fecha reservada antes de la inserción: " . $fecha_reservada;
                         var_dump($fecha_reservada);
                        // Insertar las reservas con fecha y hora correctas en la base de datos
                        $sql = "INSERT INTO reservas (id_p, semana, fecha, reservado, dia, hora, CantAlumnos, GradoSeccion, area) 
                                VALUES (:id_p, WEEK(:reservado), :fecha, :reservado, :dia, :hora, :alumnos, :grasec, :materia)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':id_p', $usuario['id_p']);
                        $stmt->bindValue(':fecha', $fecha_hoy); // Fecha actual (reserva hecha hoy)
                        $stmt->bindValue(':reservado', $fecha_reservada); // Fecha seleccionada
                        $stmt->bindValue(':dia', $dia); // Usar el día directamente de la reserva
                        $stmt->bindValue(':hora', $hora);
                        $stmt->bindValue(':alumnos', $cantAlums);
                        $stmt->bindValue(':grasec', $area);
                        $stmt->bindValue(':materia', $mates);
                        $stmt->execute();
                        
                    } else {
                        echo "Formato de reserva incorrecto: $reserva";
                    }
                }

                // Reservas insertadas correctamente
                $botons = 1;
            } else {
                echo "No hay reservas disponibles.";
            }
        }
    } else {
        echo "No has iniciado sesión o no hay datos de reserva.";
        exit();
    }
} catch (PDOException $e) {
    die('Error al conectarse a la base de datos: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Reserva</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <header>
        <h1>Confirmar Reserva</h1>
    </header>
    <h3>Instructor: <?php echo htmlspecialchars($usuario['nombre'] . " " . $usuario['apellido']); ?></h3>
    <form action="" method="POST">
        <label for="mate">Materia:</label>
        <input type="text" id="mate" name="materia" placeholder="Ejem: Ciencias"/>  <br><br>
        <label for="alukm">Cantidad de Alumnos:</label>
        <input type="number" id="alukm" name="alumnos" placeholder="Ejem: 47"/> <br><br>
        <label for="gs">Grado y Sección:</label>
        <input type="text" id="gs" name="grasec" placeholder="Ejem: 5to° Z"/>
        <button type="submit">RESERVAR</button>
        <button type="button" onclick="window.location.href='PRUEBA4.php';">Retornar</button>
    </form>
    <?php 
    if ($botons == 1) {
        echo "<script> alert('Su Reserva fue un éxito');</script>";
        echo "<button onclick=\"window.location.href='PRUEBA4.php';\">Cancelar Reserva</button>";
    } 
    ?>
    <footer>
    </footer>
</body>
</html>
