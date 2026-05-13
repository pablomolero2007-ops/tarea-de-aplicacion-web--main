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
    $stmt_coche->execute();
    $stmt_coche->close();
}

// Obtener listado de coches
$sql = "SELECT c.id, c.modelo, c.anio, c.color, c.precio, m.nombre as marca_nombre, m.pais_origen 
        FROM coches c 
        JOIN marcas m ON c.marca_id = m.id 
        ORDER BY c.id DESC";
$coches = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Catálogo de coches modernos con listado y formulario para añadir nuevos vehículos">
    <title>Catálogo de Coches</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <h1 class="logo">AutoTracker</h1>
            <ul class="nav-links">
                <li><a href="#listado" class="active">Listado</a></li>
                <li><a href="#nuevo">Añadir Coche</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <!-- Dashboard Header -->
        <header class="dashboard-header">
            <h2>Gestión de Vehículos</h2>
            <p>Visualiza y añade nuevos coches al catálogo.</p>
        </header>

        <section id="listado" class="list-section">
            <div class="section-header">
                <h3>Coches Registrados</h3>
            </div>
            <div class="car-grid">
                <?php
                if ($coches && $coches->num_rows > 0) {
                    while($row = $coches->fetch_assoc()) {
                        // Tratar de mapear colores de la bd a colores válidos en CSS de forma sencilla si es un hex o usarlo directamente. 
                        // Para este ejemplo pasamos a minúsculas, funciona bien si es un color básico en inlgés pero visualmente quedará una cajita con color default o el especificado si usaste css codes.
                        $color_badge = htmlspecialchars(strtolower($row['color']));
                        ?>
                        <article class="car-card">
                            <div class="car-card-img">
                                <!-- Imagen estática de ejemplo ya que no hay campo de imagen en la db -->
                                <img src="img/ford_mustang.png" alt="<?php echo htmlspecialchars($row['modelo']); ?>">
                            </div>
                            <div class="car-card-content">
                                <div class="car-card-header">
                                    <h4><?php echo htmlspecialchars($row['modelo']); ?></h4>
                                    <span class="car-tag"><?php echo htmlspecialchars($row['anio']); ?></span>
                                </div>
                                <div class="car-details">
                                    <div class="car-info">
                                        <p><strong>Marca:</strong> <?php echo htmlspecialchars($row['marca_nombre']); ?></p>
                                        <p><strong>Modelo:</strong> <?php echo htmlspecialchars($row['modelo']); ?></p>
                                        <p><strong>Precio:</strong> <span class="price">$<?php echo number_format($row['precio'], 2); ?></span></p>
                                        <div class="color-info">
                                            <strong>Color:</strong>
                                            <div class="color-value">
                                                <span class="color-badge" style="background-color: <?php echo $color_badge; ?>; border: 1px solid #cbd5e1;"></span> <?php echo htmlspecialchars($row['color']); ?>
                                            </div>
                                        </div>
                                        <p><strong>Origen:</strong> <?php echo htmlspecialchars($row['pais_origen']); ?></p>
                                    </div>
                                    <div class="car-card-actions">
                                        <button class="btn btn-secondary btn-sm" title="Editar">✏️ Editar</button>
                                        <button class="btn btn-danger btn-sm" title="Eliminar">🗑️ Eliminar</button>
                                    </div>
                                </div>
                            </div>
                        </article>
                        <?php
                    }
                } else {
                    echo "<p>No hay coches registrados en la base de datos.</p>";
                }
                ?>
            </div>
        </section>

        <!-- Section: Formulario -->
        <section id="nuevo" class="card mt-8">
            <div class="section-header">
                <h3>Añadir Nuevo Coche</h3>
                <p>Completa el registro para añadir un vehículo de alta gama al sistema.</p>
            </div>
            <!-- Añadido method="POST" para que funcione con PHP -->
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const carCards = document.querySelectorAll('.car-card');
            carCards.forEach(card => {
                card.addEventListener('click', (e) => {
                    // Evitar que el click en los botones de acción expanda/contraiga la tarjeta
                    if (!e.target.closest('button')) {
                        card.classList.toggle('expanded');
                    }
                });
            });
        });
    </script>
</body>
</html>
