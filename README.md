# INDET Hotel Web Management System

## Descripción General

Este es un sistema de gestión web completo para el Instituto de Deportes del Estado Trujillo (INDET), diseñado específicamente para gestionar reservas de habitaciones en un hotel deportivo. El sistema permite a los usuarios públicos explorar información sobre el instituto, verificar disponibilidad de habitaciones y realizar reservas, mientras que proporciona a los administradores herramientas avanzadas para gestionar todos los aspectos del hotel.

El proyecto está construido con tecnologías modernas y sigue las mejores prácticas de desarrollo web, incluyendo autenticación segura, gestión de sesiones, y una interfaz de usuario intuitiva.

## Tecnologías Utilizadas

- **Backend:** PHP 8.x con PDO/MySQLi para conexiones de base de datos
- **Base de Datos:** MySQL (via phpMyAdmin)
- **Frontend:** HTML5, CSS3 (Tailwind CSS), JavaScript (Vanilla JS)
- **Servidor Local:** XAMPP
- **Librerías Externas:**
  - FPDF para generación de PDFs
  - Chart.js para gráficos
  - AOS (Animate On Scroll) para animaciones
  - Font Awesome para iconos
  - Three.js para efectos visuales 3D

## Funcionalidades Principales

### Sitio Público
- **Página de Inicio (index.php):** Interfaz moderna con animaciones, secciones de "Sobre Nosotros", habitaciones, reservas, FAQ y reseñas
- **Información del Hotel (hotel_info.php):** Detalles sobre las instalaciones, servicios disponibles y horarios
- **Habitaciones:** Visualización de tipos de habitaciones con precios, capacidad y descripciones
- **Sistema de Reservas:** Formulario interactivo para verificar disponibilidad y realizar reservas
- **Eventos (events.php):** Lista de eventos deportivos y actividades programadas
- **FAQ (faq.php):** Preguntas frecuentes sobre políticas del hotel
- **Formulario de Contacto:** Sistema para enviar mensajes a la administración

### Sistema de Autenticación de Usuarios
- **Registro y Login:** Sistema seguro con hash de contraseñas
- **Sesiones:** Gestión de sesiones para proteger rutas y funcionalidades
- **Roles de Usuario:** Cliente, Administrador, Mantenimiento

### Panel de Administración (admin.php)
- **Gestión de Reservas:** Ver, confirmar, cancelar y editar reservas
- **Gestión de Usuarios:** CRUD completo para usuarios con asignación de roles
- **Gestión de Habitaciones:** Agregar, editar y eliminar habitaciones
- **Gestión de Eventos:** Crear y gestionar eventos deportivos
- **Asignación de Mantenimiento:** Asignar tareas de limpieza a personal de mantenimiento
- **Reportes Avanzados (reports.php):** Gráficos y análisis de rendimiento

### Panel de Mantenimiento (maintenance.php)
- **Tareas Pendientes:** Lista de habitaciones que requieren limpieza
- **Marcado de Completado:** Actualización automática del estado de habitaciones

### Sistema de Reseñas
- **Envío de Reseñas:** Usuarios autenticados pueden dejar calificaciones y comentarios
- **Visualización:** Muestra de reseñas en la página principal

### Generación de PDFs
- **Confirmación de Reservas:** Genera PDFs detallados con información completa de la reserva

## Estructura de la Base de Datos

El sistema utiliza las siguientes tablas principales:

- **users:** Información de usuarios (id, name, email, password, role)
- **rooms:** Detalles de habitaciones (id, type, capacity, description, price, photos)
- **reservations:** Reservas (id, user_id, room_id, checkin_date, checkout_date, status)
- **reviews:** Reseñas de usuarios (id, user_id, rating, comment, created_at)
- **events:** Eventos programados (id, name, description, date, image)
- **maintenance_tasks:** Tareas de mantenimiento (id, room_id, assigned_to_user_id, status, created_at, completed_at)
- **room_status:** Estado actual de habitaciones (room_id, status, date)

## Instalación y Configuración

### Prerrequisitos

- [XAMPP](https://www.apachefriends.org/index.html) instalado
- Navegador web moderno
- Editor de código (ej. Visual Studio Code)

### Pasos de Instalación

1. **Clonar el Repositorio:**
   ```bash
   git clone https://github.com/your-username/indet-hotel-web.git
   cd indet-hotel-web
   ```

2. **Configurar XAMPP:**
   - Iniciar servicios Apache y MySQL
   - Mover la carpeta del proyecto a `C:/xampp/htdocs/`

3. **Crear la Base de Datos:**
   - Acceder a `http://localhost/phpmyadmin`
   - Crear base de datos `indet_hotel_db`
   - Importar el archivo `database.sql` (si existe)

4. **Configurar Conexión:**
   - Verificar configuración en `php/db.php`
   - Asegurar credenciales correctas para MySQL

5. **Ejecutar el Proyecto:**
   - Acceder a `http://localhost/indet-hotel-web/`

## Estructura del Proyecto

```
indet-hotel-web/
├── css/
│   └── styles.css          # Estilos personalizados
├── images/                 # Imágenes del proyecto
│   ├── logo.png
│   ├── hero-bg.jpg
│   └── equipo-*.jpg
├── js/
│   └── main.js             # Scripts JavaScript
├── php/                    # Lógica backend
│   ├── db.php              # Conexión a base de datos
│   ├── book.php            # Procesamiento de reservas
│   ├── login_handler.php   # Autenticación de usuarios
│   ├── register_handler.php # Registro de usuarios
│   ├── logout.php          # Cierre de sesión
│   ├── user_handler.php    # Gestión de usuarios (CRUD)
│   ├── reservation_handler.php # Gestión de reservas
│   ├── room_handler.php    # Gestión de habitaciones
│   ├── event_handler.php   # Gestión de eventos
│   ├── assign_task.php     # Asignación de tareas mantenimiento
│   ├── maintenance_handler.php # Procesamiento mantenimiento
│   ├── contact_handler.php # Procesamiento contacto
│   ├── submit_review.php   # Envío de reseñas
│   ├── update_reservation_status.php # Actualización estados
│   ├── update_room_status.php # Actualización habitaciones
│   ├── availability_handler.php # Verificación disponibilidad
│   └── user_management.php # Gestión usuarios (legacy)
├── admin.php               # Panel de administración
├── reports.php             # Reportes avanzados
├── maintenance.php         # Panel de mantenimiento
├── index.php               # Página principal
├── hotel_info.php          # Información del hotel
├── events.php              # Página de eventos
├── faq.php                 # Preguntas frecuentes
├── reservar.php            # Página de reservas
├── confirmation.php        # Confirmación de reserva
├── generate_pdf.php        # Generador de PDFs
├── login.php               # Página de login
├── register.php            # Página de registro
├── composer.json           # Dependencias PHP
├── composer.lock
├── composer.phar
├── fpdf/                   # Librería FPDF
├── .gitignore
└── README.md
```

## Módulos y Funcionalidades Detalladas

### 1. Módulo de Usuario Público
- **Navegación:** Menú responsive con enlaces a todas las secciones
- **Reservas:** Formulario con validación de fechas y tipos de habitación
- **Verificación de Disponibilidad:** AJAX para mostrar habitaciones disponibles
- **Sistema de Reseñas:** Calificaciones de 1-5 estrellas con comentarios

### 2. Módulo de Autenticación
- **Registro:** Validación de email único y hash de contraseñas
- **Login:** Verificación segura con sesiones
- **Protección de Rutas:** Redireccionamiento automático para usuarios no autenticados
- **Roles:** Diferentes niveles de acceso (user, admin, maintenance)

### 3. Módulo de Administración
- **Dashboard:** Vista general con estadísticas
- **CRUD de Reservas:** Crear, leer, actualizar, eliminar reservas
- **Gestión de Usuarios:** Asignación de roles y permisos
- **Gestión de Habitaciones:** Inventario completo con fotos y precios
- **Gestión de Eventos:** Calendario de actividades deportivas
- **Sistema de Mantenimiento:** Asignación automática de tareas

### 4. Módulo de Reportes
- **Gráficos Interactivos:** Usando Chart.js para visualizaciones
- **Análisis de Reservas:** Por tipo de habitación y tendencias temporales
- **Perfil de Clientes:** Análisis de comportamiento de usuarios
- **Tendencias:** Gráficos de línea para reservas por día

### 5. Módulo de Mantenimiento
- **Lista de Tareas:** Habitaciones pendientes de limpieza
- **Actualización de Estado:** Cambio automático a "disponible" tras completado
- **Asignación:** Sistema de turnos para personal de mantenimiento

### 6. Módulo de Comunicación
- **Formulario de Contacto:** Envío de emails (requiere configuración SMTP)
- **Confirmaciones:** Emails automáticos para reservas
- **PDFs:** Generación de confirmaciones descargables

## Características Técnicas

- **Responsive Design:** Adaptable a móviles y tablets
- **Seguridad:** Hash de contraseñas, prepared statements, validación de inputs
- **Performance:** Optimización de consultas SQL, lazy loading de imágenes
- **UX/UI:** Animaciones suaves, feedback visual, navegación intuitiva
- **Internacionalización:** Soporte para español (fácil extensión a otros idiomas)

## Contribución

Para contribuir al proyecto:

1. Fork el repositorio
2. Crear una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agrega nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver archivo LICENSE para más detalles.

## Contacto

Para preguntas o soporte, contactar al equipo de desarrollo o usar el formulario de contacto del sitio.

---

**Instituto de Deportes del Estado Trujillo (INDET)**
*#EnTrujilloContinúaElProgresoDeportivoYRecreativo*
