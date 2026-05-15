require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}



// Procesar petición de eliminación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id'])) {
    $coche_id = intval($_POST['id']);
    $stmt_del = $conn->prepare("DELETE FROM coches WHERE id = ?");
    $stmt_del->bind_param("i", $coche_id);
    $stmt_del->execute();
    $stmt_del->close();
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
                <li><a href="index.php" class="active">Listado</a></li>
                <li><a href="nuevo_coche.php">Añadir Coche</a></li>
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
                                    <div class="car-card-actions" style="display: flex; gap: 1rem; align-items: center;">
                                        <a href="editar_coche.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm" title="Editar" style="text-decoration: none;">✏️ Editar</a>
                                        <form method="POST" action="index.php" style="margin: 0;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este vehículo del catálogo? Esta acción no se puede deshacer.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">🗑️ Eliminar</button>
                                        </form>
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


    </main>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const carCards = document.querySelectorAll('.car-card');
            carCards.forEach(card => {
                card.addEventListener('click', (e) => {
                    // Evitar que el click en los botones de acción expanda/contraiga la tarjeta
                    if (!e.target.closest('button')) {
                        const isExpanded = card.classList.contains('expanded');
                        // Cerrar todas
                        carCards.forEach(c => c.classList.remove('expanded'));
                        // Abrir la actual si no lo estaba
                        if (!isExpanded) {
                            card.classList.add('expanded');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
