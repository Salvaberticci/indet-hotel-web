# MANUAL DE USUARIO - SISTEMA DE GESTIÓN HOTELERA INDET

## 1. Introducción
Bienvenido al Manual de Usuario del Sistema de Gestión Hotelera del Instituto de Deportes del Estado Trujillo (INDET). Este sistema ha sido diseñado para facilitar la gestión de reservas, inventario y mantenimiento de las instalaciones hoteleras deportivas, ofreciendo una experiencia fluida tanto para los atletas y visitantes como para el personal administrativo y de mantenimiento.

### 1.1 Objetivos del Sistema
- Automatizar el proceso de reserva de habitaciones.
- Gestionar de manera eficiente el inventario de cada piso y habitación.
- Facilitar la comunicación entre administración y personal de mantenimiento.
- Proporcionar reportes detallados sobre el desempeño del hotel.

---

## 2. Módulo de Usuario Público (Atletas y Visitantes)

Este módulo es accesible para cualquier persona que desee alojarse en el hotel deportivo del INDET.

### 2.1 Registro e Inicio de Sesión
Para realizar una reserva, el usuario debe estar registrado en el sistema.
- **Registro:** Acceda a la página de "Login" y seleccione "Registrarse". Complete el formulario con su nombre, correo electrónico y contraseña.
- **Login:** Ingrese sus credenciales (correo y contraseña) para acceder a las funcionalidades de reserva.

### 2.2 Página de Inicio (Index)
En la página principal podrá encontrar:
- **Sobre Nosotros:** Información sobre la misión del INDET.
- **Habitaciones:** Galería con los diferentes tipos de habitaciones disponibles.
- **Reseñas:** Comentarios de otros usuarios sobre su estancia.
- **Formulario de Contacto:** Ubicado en el pie de página para enviar consultas directas a la administración.

### 2.3 Realizar una Reserva
1. Una vez iniciada la sesión, haga clic en el botón **"Reservacion"** en el menú de navegación o **"Ver Reservacion"** en la página de inicio.
2. **Formulario de Reserva:**
   - Ingrese su Cédula, Nombre y Apellido.
   - Seleccione la **Fecha de Llegada** y **Fecha de Salida**.
   - Indique la cantidad de personas (Adultos, Niños, Discapacitados).
   - Seleccione el **Piso** deseado.
3. **Selección de Habitación:** El sistema mostrará las habitaciones disponibles según sus criterios. Haga clic en "Seleccionar" en la habitación deseada.
4. **Datos de Huéspedes:** Se abrirá un modal para ingresar los nombres, apellidos y teléfonos de cada persona que se alojará en la habitación.
5. **Confirmación:** Revise los detalles y confirme su reserva. El sistema le redirigirá a su perfil.

### 2.4 Mi Perfil
En esta sección, el usuario puede:
- Ver sus datos personales.
- Consultar el historial de sus reservas.
- **Descargar Comprobante:** Si tiene una reserva activa, puede descargar un PDF con los detalles de la misma.
- **Cancelar Reserva:** Si la política lo permite, puede cancelar su reserva directamente desde su perfil.

### 2.5 Reseñas y FAQ
- **FAQ:** Sección de preguntas frecuentes para resolver dudas rápidas sobre las políticas del hotel.
- **Comentarios:** Al final de la página de inicio, los usuarios pueden dejar su experiencia y calificación sobre el servicio.

---

## 3. Módulo de Administración

Este módulo es de uso exclusivo para el personal autorizado con rol de "Administrador".

### 3.1 Panel de Control (Dashboard)
Al ingresar como administrador, tendrá acceso a una vista general con acceso rápido a todos los submódulos:

#### A. Gestión de Reservas
- Visualice todas las reservas realizadas en el sistema.
- **Confirmar/Cancelar:** Cambie el estado de las reservas según sea necesario.
- **Generar Reportes:** Opción para ver detalles específicos de cada reserva.

#### B. Gestión de Usuarios
- CRUD completo (Crear, Leer, Actualizar, Eliminar) de usuarios.
- Asignación de **Roles**: Cliente, Administrador o Mantenimiento.

#### C. Gestión de Pisos y Habitaciones
- **Pisos:** Cree y asigne nombres personalizados a los pisos (ej. Planta Baja, Piso 1).
- **Habitaciones:** Agregue nuevas habitaciones, defina su tipo (ej. Individual, Colectiva con literas), descripción y suba fotografías.

#### D. Inventario por Habitación
- Controle los activos presentes en cada habitación (camas, televisores, muebles).
- Agregue, edite o elimine items de inventario.
- Actualice cantidades rápidamente mediante los botones (+) y (-).

#### E. Check-in / Check-out Diario
- Una herramienta optimizada para gestionar las entradas y salidas del día actual.
- Visualice rápidamente qué habitaciones deben ser entregadas y cuáles están por ser ocupadas.

#### F. Reportes y Estadísticas
- Gráficos interactivos que muestran tendencias de reserva por tipo de habitación y por día.
- Análisis de la capacidad hotelera.

#### G. Gestión de Eventos
- Publique eventos deportivos o actividades especiales que se mostrarán en la página pública de eventos.

---

## 4. Módulo de Mantenimiento

Diseñado para el personal encargado de la limpieza y puesta a punto de las habitaciones.

### 4.1 Tareas de Mantenimiento
- El personal de mantenimiento verá una lista de tareas asignadas o habitaciones que requieren atención (generalmente después de un Check-out).
- **Marcar como Completado:** Una vez terminada la limpieza o reparación, el usuario marca la tarea como lista, lo cual actualiza automáticamente el estado de la habitación en el sistema de administración a "Disponible".

---

## 5. Guía de Instalación Técnica (Solo para Administradores de Sistemas)

1. **Requisitos:** XAMPP (Apache + MySQL), PHP 8.x.
2. **Base de Datos:**
   - Importar el archivo `indet_hotel_db.sql` mediante phpMyAdmin.
   - Configurar las credenciales en `php/db.php`.
3. **Archivos:** Colocar la carpeta del proyecto en `C:/xampp/htdocs/`.
4. **Acceso:** Abrir en el navegador `http://localhost/indet-hotel-web/`.

---

## 6. Soporte y Contacto
Para incidencias técnicas o dudas adicionales, contacte al administrador del sistema mediante el formulario de contacto en la web o directamente en las oficinas del INDET en Valera, Edo. Trujillo.

*© 2026 Instituto de Deportes del Estado Trujillo - #EnTrujilloContinúaElProgresoDeportivoYRecreativo*
