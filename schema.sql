 -- Script SQL para la creación de la base de datos de gestión de coches
 
 -- Crear la base de datos si no existe
 -- CREATE DATABASE IF NOT EXISTS concesionario;
 -- USE concesionario;

-- Tabla de Marcas
CREATE TABLE IF NOT EXISTS marcas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    pais_origen VARCHAR(50)
) ENGINE=InnoDB;

-- Tabla de Coches
CREATE TABLE IF NOT EXISTS coches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modelo VARCHAR(100) NOT NULL,
    anio INT NOT NULL,
    color VARCHAR(30),
    precio DECIMAL(10, 2),
    imagen VARCHAR(255),
    marca_id INT NOT NULL,
    FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insertar datos de ejemplo
INSERT INTO marcas (nombre, pais_origen) VALUES ('Honda', 'Japón'), ('Ford', 'EE.UU.'), ('Porsche', 'Alemania');

INSERT INTO coches (modelo, anio, color, precio, imagen, marca_id) VALUES 
('Civic', 2022, 'Blanco', 28000.00, 'img/honda_civic.png', 1),
('Mustang', 2021, 'Rojo', 45000.00, 'img/ford_mustang.png', 2),
('911', 2023, 'Negro', 130000.00, 'img/porsche_911.png', 3);
