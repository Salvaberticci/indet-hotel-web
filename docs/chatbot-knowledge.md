# Base de Conocimiento - Asistente Virtual INDET Hotel

## Información General del Instituto

### INDET - Instituto de Deportes del Estado Trujillo
- **Nombre Completo**: Instituto de Deportes del Estado Trujillo
- **Ubicación**: Trujillo, Venezuela
- **Misión**: Proveemos el deporte a nivel nacional con nuestros jóvenes, construyendo un futuro mejor hasta conseguir los objetivos
- **Hashtag**: #EnTrujilloContinúaElProgresoDeportivoYRecreativo
- **Contacto**:
  - Instagram: @indetrujillo
  - Teléfono: 0412-897643
  - Dirección: Valera Edo Trujillo

### Hotel Deportivo INDET
- **Tipo**: Hotel especializado en atletas y visitantes deportivos
- **Ubicación**: Centro de la ciudad, instalaciones modernas
- **Especialización**: Alojamiento para deportistas, con énfasis en recuperación y rendimiento
- **Servicios**: Habitaciones confortables, gastronomía de clase mundial, instalaciones deportivas

## Servicios del Hotel

### Habitaciones
- **Tipos Disponibles**: Individuales, dobles, suites familiares
- **Características**:
  - Confort máximo para recuperación atlética
  - Diseño moderno y funcional
  - Adaptadas para atletas profesionales y amateurs
- **Capacidad**: Varía según tipo de habitación
- **Precios**: Según temporada y tipo de habitación

### Servicios Disponibles
- **Wi-Fi**: Alta velocidad en todas las instalaciones
- **Restaurante**: Gourmet con menú especializado para deportistas
- **Piscina y Spa**: Recuperación y relajación
- **Gimnasio**: Equipado con máquinas modernas
- **Servicio a la habitación**: 24/7
- **Centro de Negocios**: Para reuniones y trabajo

### Horarios
- **Check-in**: A partir de las 15:00
- **Check-out**: Hasta las 12:00
- **Servicio de habitaciones**: 24 horas
- **Restaurante**: Desayuno 6:00-10:00, Almuerzo 12:00-15:00, Cena 18:00-22:00

## Sistema de Reservaciones

### Proceso de Reserva
1. **Registro/Login**: Usuario debe estar registrado en el sistema
2. **Selección de Fechas**: Fecha de llegada y salida
3. **Selección de Piso**: Según necesidades (accesibilidad para discapacitados)
4. **Número de Personas**: Adultos, niños, discapacitados
5. **Selección de Habitaciones**: Basado en disponibilidad
6. **Confirmación**: Revisión de detalles y confirmación final
7. **Pago**: Procesamiento de pago (si aplica)

### Pisos del Hotel
- **Planta Baja**: Accesible para personas con discapacidades
- **Pisos Superiores**: Habitaciones estándar
- **Consideraciones**: Personas con discapacidades deben seleccionar Planta Baja

### Estados de Reserva
- **Pendiente**: Esperando confirmación del administrador
- **Confirmada**: Reserva activa
- **Cancelada**: Reserva anulada
- **Completada**: Estancia finalizada

## Roles de Usuario

### Cliente (Usuario Regular)
- **Funcionalidades**:
  - Ver información del hotel
  - Realizar reservaciones
  - Ver perfil personal
  - Historial de reservas
  - Cancelar reservas
  - Dejar reseñas
  - Contactar administración

### Administrador
- **Funcionalidades**:
  - Gestión completa de reservas (ver, confirmar, cancelar, editar)
  - Gestión de usuarios (CRUD)
  - Gestión de habitaciones (agregar, editar, eliminar)
  - Gestión de inventario por piso
  - Gestión de pisos
  - Gestión de eventos deportivos
  - Asignación de tareas de mantenimiento
  - Reportes avanzados con gráficos

### Mantenimiento
- **Funcionalidades**:
  - Ver tareas pendientes de limpieza
  - Marcar habitaciones como completadas
  - Lista de habitaciones que requieren mantenimiento

## Módulos del Sistema

### 1. Módulo de Usuario Público
- **Página Principal**: Información general, habitaciones, reservas, FAQ, reseñas
- **Información del Hotel**: Detalles de instalaciones y servicios
- **Sistema de Reservas**: Formulario interactivo con verificación de disponibilidad
- **Eventos**: Lista de actividades deportivas programadas
- **FAQ**: Preguntas frecuentes sobre políticas
- **Contacto**: Formulario para enviar mensajes

### 2. Módulo de Autenticación
- **Registro**: Validación de email único, hash de contraseñas
- **Login**: Verificación segura con sesiones
- **Protección de Rutas**: Redireccionamiento automático
- **Roles**: Cliente, Administrador, Mantenimiento

### 3. Módulo de Administración
- **Dashboard**: Vista general con estadísticas
- **CRUD de Reservas**: Gestión completa
- **Gestión de Usuarios**: Asignación de roles
- **Gestión de Habitaciones**: Inventario con fotos y precios
- **Sistema de Inventario por Piso**: Camas, sillas, muebles
- **Gestión de Pisos**: Crear/editar/eliminar pisos
- **Gestión de Eventos**: Calendario deportivo
- **Sistema de Mantenimiento**: Asignación automática de tareas

### 4. Módulo de Reportes
- **Gráficos Interactivos**: Chart.js para visualizaciones
- **Análisis de Reservas**: Por tipo de habitación y tendencias
- **Perfil de Clientes**: Comportamiento de usuarios
- **Tendencias**: Gráficos de reservas por día

### 5. Módulo de Mantenimiento
- **Lista de Tareas**: Habitaciones pendientes
- **Actualización de Estado**: Cambio automático a "disponible"
- **Asignación**: Sistema de turnos

### 6. Módulo de Comunicación
- **Formulario de Contacto**: Envío de emails
- **Confirmaciones**: Emails automáticos
- **PDFs**: Generación de confirmaciones descargables

## Preguntas Frecuentes (FAQ)

### Sobre Reservas
- **¿Cómo hago una reserva?**: Regístrate, selecciona fechas, piso, personas y habitaciones disponibles
- **¿Puedo cancelar una reserva?**: Sí, desde tu perfil de usuario
- **¿Qué pasa si tengo discapacidades?**: Selecciona Planta Baja para accesibilidad
- **¿Hay límite de tiempo para reservas?**: Check-in 15:00, check-out 12:00

### Sobre el Hotel
- **¿Qué servicios ofrecen?**: Wi-Fi, restaurante, piscina, gimnasio, servicio 24/7
- **¿Es solo para atletas?**: Especializado en deportistas pero abierto a todos
- **¿Tienen estacionamiento?**: Información disponible en recepción
- **¿Aceptan mascotas?**: Consulta políticas específicas

### Sobre Pagos
- **¿Cómo pago?**: Sistema de pago integrado (detalles en confirmación)
- **¿Hay depósitos?**: Según política del hotel
- **¿Facturación?**: Disponible en recepción

## Información Técnica

### Tecnologías Utilizadas
- **Backend**: PHP 8.x con PDO/MySQLi
- **Base de Datos**: MySQL
- **Frontend**: HTML5, CSS3, Tailwind CSS, JavaScript
- **Servidor**: XAMPP (Apache, MySQL, PHP)
- **Librerías**: FPDF, Chart.js, AOS, Font Awesome, Three.js

### Instalación
- **Prerrequisitos**: XAMPP instalado
- **Base de Datos**: Importar indet_hotel_db.sql
- **Configuración**: Verificar credenciales en php/db.php
- **Ejecución**: Acceder via localhost/indet-hotel-web/

### Seguridad
- **Autenticación**: Hash de contraseñas, sesiones seguras
- **Validación**: Prepared statements, sanitización de inputs
- **Protección**: Headers de seguridad, validación de roles

## Información de Contacto de Emergencia

### Administración
- **Email**: info@indet-trujillo.gob.ve (ejemplo)
- **Teléfono**: 0412-897643
- **Horario**: Lunes a Viernes 8:00-17:00

### Soporte Técnico
- **Email**: soporte@indet-hotel.gob.ve (ejemplo)
- **Teléfono**: 0412-897644 (ejemplo)

## Políticas del Hotel

### Política de Cancelación
- Cancelación gratuita hasta 24 horas antes del check-in
- Penalización del 50% dentro de 24 horas
- No reembolsable el día del check-in

### Política de Niños
- Niños menores de 12 años: 50% descuento
- Cunas disponibles bajo petición
- Supervisión parental requerida

### Política de Mascotas
- No se permiten mascotas en habitaciones
- Servicio de guardería disponible (consultar)

### Política de Fumadores
- Hotel 100% libre de humo
- Áreas designadas para fumadores exteriores