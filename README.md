# AutoTracker - Catálogo de Gestión de Vehículos

AutoTracker es una aplicación web moderna y elegante diseñada para la gestión y visualización de un catálogo de coches de alta gama. Permite a los usuarios explorar vehículos de forma interactiva, añadir nuevos registros, editar información existente y gestionar el inventario de manera eficiente.

## 🚀 Características principales
- **Interfaz Premium**: Diseño visualmente impactante con efectos de glassmorphism y animaciones fluidas.
- **Acordeón Interactivo**: Visualización optimizada mediante un acordeón exclusivo que permite ver los detalles de un coche a la vez.
- **Gestión CRUD Completa**: Funcionalidad total para Crear, Leer, Actualizar y Eliminar coches y marcas.
- **Navegación Intuitiva**: Estructura multi-página para separar el listado de la gestión de nuevos ingresos.

## 🛠️ Instrucciones de Instalación

Sigue estos pasos para configurar la aplicación en tu entorno local (XAMPP/WAMP/MAMP):

### 1. Clonar o descargar el proyecto
Copia todos los archivos del repositorio en tu carpeta de servidor local (ej. `htdocs/auto-tracker`).

### 2. Configurar la Base de Datos
1. Abre tu gestor de bases de datos (ej. phpMyAdmin).
2. Crea una nueva base de datos llamada `concesionario`.
3. Importa el archivo `schema.sql` que se encuentra en la raíz del proyecto para crear automáticamente las tablas y los datos de ejemplo:
   - Selecciona la base de datos `concesionario`.
   - Haz clic en la pestaña **Importar**.
   - Selecciona el archivo `schema.sql`.
   - Haz clic en **Continuar**.

### 3. Configuración de Conexión
Para conectar con la base de datos:
1. Copia el archivo `config.php.example` y cámbiale el nombre a `config.php`.
2. Abre `config.php` y actualiza los valores según tu entorno local:
   - `DB_HOST`: Normalmente `localhost`.
   - `DB_USER`: Tu usuario de MySQL.
   - `DB_PASS`: Tu contraseña de MySQL.
   - `DB_NAME`: El nombre de la base de datos (`concesionario`).

### 4. Ejecutar
Abre tu navegador y accede a: `http://localhost/nombre-de-tu-carpeta/index.php`.

## 🌐 Producción 
Puedes acceder a la versión desplegada de la aplicación en el siguiente enlace:
👉 [Enlace al servidor en producción](https://alumno4.dwes.site/)
