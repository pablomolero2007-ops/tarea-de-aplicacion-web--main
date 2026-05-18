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
('Corolla', 2022, 'Blanco', 25000.00, 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&q=80&w=800', 1),
('Mustang', 2021, 'Rojo', 45000.00, 'https://images.unsplash.com/photo-1584345604481-03cd189813f7?auto=format&fit=crop&q=80&w=800', 2),
('Ibiza', 2023, 'Azul', 18000.00, 'https://images.unsplash.com/photo-1619682817481-e994891cd1f5?auto=format&fit=crop&q=80&w=800', 3);
