<?php
// Configuración de la conexión a la base de datos
$host = "localhost";
$user = "root"; // Tu usuario de MySQL
$pass = "";     // Tu contraseña de MySQL
$dbname = "concesionario";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$success = false;

// Procesar formulario al enviar
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $marca_nombre = $_POST['marca'];
    $pais = $_POST['pais'];
    $modelo = $_POST['modelo'];
    $nombre = $_POST['nombre']; 
    $anio = $_POST['anio'];
    $color = $_POST['color'];
    $precio = $_POST['precio'];
    
    // Combinamos nombre y modelo, ya que la BBDD solo tiene un campo 'modelo'
    $modelo_completo = $nombre . " " . $modelo;

    // Verificar si la marca ya existe en su tabla
    $stmt_marca = $conn->prepare("SELECT id FROM marcas WHERE nombre = ?");
    $stmt_marca->bind_param("s", $marca_nombre);
    $stmt_marca->execute();
    $result_marca = $stmt_marca->get_result();

    if ($result_marca->num_rows > 0) {
        $row = $result_marca->fetch_assoc();
        $marca_id = $row['id'];
    } else {
        // Si no existe, crear la marca
        $stmt_insert_marca = $conn->prepare("INSERT INTO marcas (nombre, pais_origen) VALUES (?, ?)");
        $stmt_insert_marca->bind_param("ss", $marca_nombre, $pais);
        $stmt_insert_marca->execute();
        $marca_id = $stmt_insert_marca->insert_id;
        $stmt_insert_marca->close();
    }
    $stmt_marca->close();

    // Insertar el coche
    $stmt_coche = $conn->prepare("INSERT INTO coches (modelo, anio, color, precio, marca_id) VALUES (?, ?, ?, ?, ?)");
    $stmt_coche->bind_param("sisdi", $modelo_completo, $anio, $color, $precio, $marca_id);
    if($stmt_coche->execute()) {
        $success = true;
    }
    $stmt_coche->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Añadir nuevo coche al catálogo">
    <title>Añadir Nuevo Coche</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        .success-msg {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid #10b981;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <h1 class="logo">AutoTracker</h1>
            <ul class="nav-links">
                <li><a href="index.php">Listado</a></li>
                <li><a href="nuevo_coche.php" class="active">Añadir Coche</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <!-- Dashboard Header -->
        <header class="dashboard-header">
            <h2>Añadir Nuevo Vehículo</h2>
            <p>Rellena el formulario para incluir un nuevo coche en el catálogo.</p>
        </header>

        <!-- Section: Formulario -->
        <section id="nuevo" class="card">
            <?php if ($success): ?>
                <div class="success-msg">
                    ¡Coche registrado con éxito! Puedes añadir otro o volver al <a href="index.php" style="color: #fff; text-decoration: underline;">Listado</a>.
                </div>
            <?php endif; ?>
            
            <form class="car-form" method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej. Mustang GT" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="marca">Marca</label>
                        <input type="text" id="marca" name="marca" placeholder="Ej. Ford" required>
                    </div>

                    <div class="form-group">
                        <label for="modelo">Modelo</label>
                        <input type="text" id="modelo" name="modelo" placeholder="Ej. Fastback" required>
                    </div>

                    <div class="form-group">
                        <label for="anio">Año</label>
                        <input type="number" id="anio" name="anio" min="1886" max="2100" placeholder="Ej. 2024" required>
                    </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" placeholder="Ej. Rojo Pasión" required>
                    </div>

                    <div class="form-group">
                        <label for="precio">Precio ($)</label>
                        <input type="number" id="precio" name="precio" placeholder="Ej. 55000" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="pais">País de Origen</label>
                        <select id="pais" name="pais" required>
                            <option value="">Selecciona un país...</option>
                            <option value="Alemania">Alemania</option>
                            <option value="Corea del Sur">Corea del Sur</option>
                            <option value="Estados Unidos">Estados Unidos</option>
                            <option value="Francia">Francia</option>
                            <option value="Italia">Italia</option>
                            <option value="Japón">Japón</option>
                            <option value="Reino Unido">Reino Unido</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="imagen">URL de la Imagen</label>
                        <input type="url" id="imagen" name="imagen" placeholder="https://ejemplo.com/coche.jpg">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">Limpiar</button>
                    <button type="submit" class="btn btn-primary">Registrar Coche</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
