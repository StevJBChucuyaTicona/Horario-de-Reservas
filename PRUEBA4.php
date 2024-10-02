<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$dbname = 'profesoresfaz';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_SESSION['dni'])) {
        $dni = $_SESSION['dni'];
        // Obtén la información del profesor
        $sql = "SELECT profesores.id_p, profesores.nombre, profesores.apellido, profesores.telefono, profesores.nivel, profesores.direccion, profesores.edad, profesores.Turno
                FROM profesores
                INNER JOIN dni_profesor
                ON dni_profesor.id_p = profesores.id_p
                WHERE dni_profesor.dni = :dni";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':dni', $dni);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && isset($result['id_p'])) {
            $id_p = $result['id_p'];
   
             // Obtener las reservas actuales con la fecha para la semana seleccionada o la actual
             $semana_seleccionada = isset($_SESSION['reservas_seleccionadas']) 
             ? date('W', strtotime($_SESSION['reservas_seleccionadas'])) 
             : date('W'); // Si no hay semana seleccionada, usar la actual  
         
             $año_actual = date('Y');
        
         $sql = "SELECT dia , hora, id_p
         FROM reservas
         WHERE WEEK(reservado) = :semana
         AND id_p = :id_p";
         $stmt = $pdo->prepare($sql);
         $stmt->bindValue(':semana', $semana_seleccionada); // Usa la semana seleccionada o actual
         $stmt->bindValue(':id_p', $id_p);
         $stmt->execute();
         $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // Verificación de la carga de reservas
            if (!$reservas) {
                echo "<script>console.log('No se encontraron reservas para la semana actual.');</script>";
            }
        } else {
            echo "No se encontró información del profesor para el DNI: " . htmlspecialchars($dni);
            $reservas = [];
        }
    } else {
        echo "No has iniciado sesión.";
        exit();
    }
} catch (PDOException $e) {
    die('Error al conectarse a la base de datos: ' . $e->getMessage());
}

// Guardar las selecciones cuando se haga una reserva
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selecciones']) && !empty($_POST['selecciones'])) {
    // Verifica que las selecciones no estén vacías
    $_SESSION['reservas'] = $_POST['selecciones']; // Guarda la selección en la sesión
    header('Location: PRUEBA5.php');
    exit();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "No se seleccionó ningún horario."; // Añade un mensaje en caso de que no haya selecciones
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <title>Inicio</title>
    <style>
        .reservado {
            background-color: red;
            color: white;
        }
        .seleccionado {
            background-color: green;
        }
        .reservado-naranja {
            background-color: orange;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <h1 class="titulo">HORARIO DE RESERVA</h1>
        <h2 class="subtitulo">TURNO MAÑANA</h2>
    </header>
    <table class="border">
        <thead>
            <tr>
                <th class="mov">Docente:</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="move2">
            <tr>
                <td>Nombres:</td>
                <td><?php echo htmlspecialchars($result['nombre']) ?></td>
            </tr>
            <tr>
                <td>Apellidos:</td>
                <td><?php echo htmlspecialchars($result['apellido']) ?></td>
            </tr>
            <tr>
                <td>Telefono:</td>
                <td><?php echo htmlspecialchars($result['telefono']) ?></td>
            </tr>
            <tr>
                <td>Nivel:</td>
                <td><?php echo htmlspecialchars($result['nivel']) ?></td>
            </tr>
            <tr>
                <td>Direccion:</td>
                <td><?php echo htmlspecialchars($result['direccion']) ?></td>
            </tr>
            <tr>
                <td>Edad:</td>
                <td><?php echo htmlspecialchars($result['edad']) ?></td>
            </tr>
            <tr>
                <td><h4 class="h4y">Turno:</h4></td>
                <td><?php echo htmlspecialchars($result['Turno']) ?></td>
            </tr>
            <tr>
                <td>Mes:</td>
                <td><h4 id="mes"></h4></td>
            </tr>
        </tbody>
    </table>

    <form id="formReserva" action="" method="POST" style="display:block;">
        <input type="hidden" name="selecciones" id="selecciones">
    </form>

    <table id="horario" class="table2">
        <thead>
            <tr>
                <th></th>
                <th>HORAS</th>
                <th>LUNES</th>
                <th>MARTES</th>
                <th>MIERCOLES</th>
                <th>JUEVES</th>
                <th>VIERNES</th>
            </tr>
        </thead>
        <tbody> 
        <tr>
            <td class="cajas">1ra Hora</td>
            <td class="cajas">7:45 - 8:30</td>
            <td class="cajas cuadropro" data-dia="Lunes" data-hora="7:45 - 8:30"></td>
            <td class="cajas cuadropro" data-dia="Martes" data-hora="7:45 - 8:30"></td>
            <td class="cajas cuadropro" data-dia="Miércoles" data-hora="7:45 - 8:30"></td>
            <td class="cajas cuadropro" data-dia="Jueves" data-hora="7:45 - 8:30"></td>
            <td class="cajas cuadropro" data-dia="Viernes" data-hora="7:45 - 8:30"></td>
        </tr>
        <tr>
            <td class="cajas">2da Hora</td>
            <td class="cajas">8:30 - 9:15</td>
            <td class="cajas cuadropro" data-dia="Lunes" data-hora="8:30 - 9:15"></td>
            <td class="cajas cuadropro" data-dia="Martes" data-hora="8:30 - 9:15"></td>
            <td class="cajas cuadropro" data-dia="Miércoles" data-hora="8:30 - 9:15"></td>
            <td class="cajas cuadropro" data-dia="Jueves" data-hora="8:30 - 9:15"></td>
            <td class="cajas cuadropro" data-dia="Viernes" data-hora="8:30 - 9:15"></td>
        </tr>
        <!-- Agrega las otras horas aquí -->
        </tbody>
    </table>
    
    <h4 id="contador" class="mois">Total Seleccionado: 0</h4>
    <button  id="reservar" class="botomd">Reservar</button>
    <a href="reserva.php" class="linkind">Cambiar a Turno Tarde</a>
    <h3 class="moled" id="fecha"></h3>
    <button class="pipa" id="siguienteSemana">Siguiente Semana</button>
    <button class="pipa" id="anteriorSemana">Semana Anterior</button>
    
    <script>
document.addEventListener('DOMContentLoaded', () => {
    const celdas = document.querySelectorAll('.cuadropro');
    let contador = 0;

    let semanaActual = <?php echo date('W'); ?>; // Obtiene la semana actual
    console.log("Semana actual: " + semanaActual);

  let reservas = <?php echo json_encode($reservas); ?>;

  console.log('reservas', reservas);


// Asegúrate de que reservas no esté vacío y usa json_encode
let reservasPorSemana = {};
    reservasPorSemana[semanaActual]= <?php echo !empty($reservas) ? json_encode(array_map(function($reserva) {
        return [
            'dia' => $reserva['dia'],
            'hora' => $reserva['hora'],
            'id_p' => $reserva['id_p']
        ];
    }, $reservas)) : '[]'; ?>


console.log("Reservas por semana:", reservasPorSemana);

 

    // Usar un único bucle JavaScript para marcar las celdas reservadas en la semana actual
    function marcarReservas(semana) {
        // Limpiar las reservas visuales antes de marcar las nuevas
        celdas.forEach(celda => {
            celda.classList.remove('reservado', 'reservado-naranja', 'seleccionado');
        });

        // Marcar solo las reservas de la semana actual -----
        if (reservasPorSemana[semana]) {
            reservasPorSemana[semana].forEach(reserva => {
                const celda = document.querySelector(`td[data-dia="${reserva.dia}"][data-hora="${reserva.hora}"]`);
                if (celda) {
                      if (reserva.id_p == "<?php echo $id_p; ?>") {
                        celda.classList.add('reservado-naranja');
                    } else {
                        celda.classList.add('reservado');
                    }
                } else {
                    console.log(`No se encontró la celda para el día: ${reserva.dia} y la hora: ${reserva.hora}`);
                }
            });
        } else {
            console.log('No hay reservas para marcar en esta semana.');
        }
    }

    // Calcular la fecha exacta de cada día de la semana actual
    function calcularFechaDia(diaSemana) {
        const fechaActual = new Date();
        const lunes = new Date(fechaActual);

        const diaDelaSemana = fechaActual.getDay() || 7;
        const diferenciaSemanas = semanaActual - <?php echo date('W'); ?>;
       lunes.setDate(fechaActual.getDate() + (diferenciaSemanas * 7) - (fechaActual.getDay() || 7) + 1); // Lunes de la semana actual
       
       const diasSemana = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"];
        const indiceDia = diasSemana.indexOf(diaSemana);

        if (indiceDia !== -1) {
            const fechaReserva = new Date(lunes);
            fechaReserva.setDate(lunes.getDate() + indiceDia);
            return fechaReserva.toISOString().split('T')[0]; // Retornar la fecha en formato YYYY-MM-DD
        }
        return null;
    }

    // Selección de celdas
    celdas.forEach(celda => {
        celda.addEventListener('click', () => {
            if (!celda.classList.contains('reservado') && !celda.classList.contains('reservado-naranja')) {
                celda.classList.toggle('seleccionado');
                contador += celda.classList.contains('seleccionado') ? 1 : -1;
                document.getElementById('contador').textContent = `Total Seleccionado: ${contador}`;
            }
        });
    });

    // Enviar selección al formulario
    document.getElementById('reservar').addEventListener('click', () => {
        const seleccionadas = Array.from(document.querySelectorAll('.seleccionado')).map(celda => {
            const diaSemana = celda.dataset.dia;
            const hora = celda.dataset.hora;
            const fechaCompleta = calcularFechaDia(diaSemana); // Calcular la fecha exacta del día seleccionado
            return `${fechaCompleta}|${hora}`;
        }).join(',');

        if (!seleccionadas.length) {
            alert("No has seleccionado ninguna celda.");
            return;
        }

        document.getElementById('selecciones').value = seleccionadas;
        document.getElementById('formReserva').submit();

    });

    // *** Actualizar fechas y semana seleccionada ***
    
function actualizarFechas(semana) {
    const fechaActual = new Date();
    const lunes = new Date(fechaActual);

    // Ajusta la fecha tomando en cuenta el cambio de semanas
    lunes.setDate(fechaActual.getDate() + (7 * (semana - <?php echo date('W'); ?>)) - (fechaActual.getDay() || 7) + 1); // Lunes de la semana actual

    const viernes = new Date(lunes);
    viernes.setDate(lunes.getDate() + 4);

    const diasSemana = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
    const meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

    // Verifica si el año cambia correctamente al avanzar semanas
    const diaLunes = lunes.getDate();
    const mesLunes = meses[lunes.getMonth()];
    const diaViernes = viernes.getDate();
    const mesViernes = meses[viernes.getMonth()];

    // Mostrar las fechas actualizadas en los elementos correspondientes
    document.getElementById('fecha').textContent = `${diasSemana[lunes.getDay()]} ${diaLunes} ${mesLunes} - ${diasSemana[viernes.getDay()]} ${diaViernes} ${mesViernes}`;
    document.getElementById('mes').textContent = mesLunes;

    // Marcar las reservas para la semana actual
    marcarReservas(semana);
}
    actualizarFechas(semanaActual);

    // Navegar entre semanas
    document.getElementById('siguienteSemana').addEventListener('click', () => {
        semanaActual++;
        actualizarFechas(semanaActual);
    });

    document.getElementById('anteriorSemana').addEventListener('click', () => {
        semanaActual--;
        actualizarFechas(semanaActual);
    });
});
</script>

</body>
</html>
