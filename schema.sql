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
INSERT INTO marcas (nombre, pais_origen) VALUES ('Toyota', 'Japón'), ('Ford', 'EE.UU.'), ('Seat', 'España');

INSERT INTO coches (modelo, anio, color, precio, imagen, marca_id) VALUES 
('Corolla', 2022, 'Blanco', 25000.00, 'https://upload.wikimedia.org/wikipedia/commons/e/eb/2021_Toyota_Corolla_Ascent_Sport_hybrid_sedan_%282021-06-25%29_01.jpg', 1),
('Mustang', 2021, 'Rojo', 45000.00, 'https://upload.wikimedia.org/wikipedia/commons/1/1d/2018_Ford_Mustang_GT_5.0.jpg', 2),
('Ibiza', 2023, 'Azul', 18000.00, 'https://upload.wikimedia.org/wikipedia/commons/3/36/SEAT_Ibiza_Style_1.0_TGI_%28VI%29_%E2%80%93_Frontansicht%2C_28._Oktober_2017%2C_Essen.jpg', 3);
