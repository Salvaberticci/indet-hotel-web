# INDET Hotel Web Management System

This is a web management system for the Instituto de Deportes del Estado Trujillo (INDET). It allows users to view information about the institute, check room availability, and make reservations.

## Technologies Used

- **Backend:** PHP 8.x
- **Database:** MySQL (via phpMyAdmin)
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla JS)
- **Local Server:** XAMPP

## Setup and Installation

### 1. Prerequisites

- [XAMPP](https://www.apachefriends.org/index.html) installed on your machine.
- A modern web browser.
- A code editor (e.g., Visual Studio Code).

### 2. Clone the Repository

Open your terminal or Git Bash and run the following command to clone the repository:

```bash
git clone https://github.com/your-username/indet-hotel-web.git
```

Navigate to the project directory:

```bash
cd indet-hotel-web
```

### 3. Configure XAMPP

1.  **Start XAMPP:** Open the XAMPP Control Panel and start the **Apache** and **MySQL** services.
2.  **Move Project Files:** Move the cloned project folder to the `htdocs` directory inside your XAMPP installation folder (e.g., `C:/xampp/htdocs/`).

### 4. Create the Database

1.  **Open phpMyAdmin:** In your web browser, navigate to `http://localhost/phpmyadmin`.
2.  **Create a New Database:** Click on the **New** button on the left sidebar. Enter `indet_hotel_db` as the database name and click **Create**.
3.  **Import the SQL File:** Select the `indet_hotel_db` database from the sidebar. Click on the **Import** tab. Click on **Choose File** and select the `database.sql` file from the project directory. Click **Go** to import the database schema.

### 5. Run the Project

Open your web browser and navigate to `http://localhost/indet-hotel-web/`. You should see the home page of the INDET Hotel Web Management System.

## Project Structure

```
indet-hotel-web/
├── css/
│   └── styles.css
├── images/
│   ├── ...
├── js/
│   └── main.js
├── php/
│   ├── book.php
│   ├── contact_handler.php
│   ├── db.php
│   ├── login_handler.php
│   ├── logout.php
│   ├── register_handler.php
│   ├── submit_review.php
│   ├── update_reservation_status.php
│   ├── update_room_status.php
│   └── user_management.php
├── admin.php
├── confirmation.php
├── index.php
├── login.php
├── register.php
├── database.sql
└── README.md
```

## Modules and Functionalities

The project is divided into the following modules:

-   **Public-Facing Site (`index.php`):**
    -   Modern, animated user interface.
    -   Sections for "About", "Rooms", "Booking", "FAQ", and "Reviews".
    -   Functional contact form.
    -   Users can register, log in, and book rooms.
    -   Authenticated users can submit reviews.

-   **Admin Panel (`admin.php`):**
    -   **Reservation Management:** View all reservations. Admins can confirm or cancel pending reservations.
    -   **User Management:** A full CRUD interface (`php/user_management.php`) to create, view, edit, and delete users and assign roles (client/admin).
    -   **Room Status Management:** View the status of all rooms (available, occupied, cleaning) and update it in real-time.
    -   **Performance Reports:** A bar chart visualizing the number of reservations per room type.

-   **User Authentication:**
    -   Secure login and registration system with password hashing.
    -   Session-based authentication to protect routes and functionalities.
