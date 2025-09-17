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
│   ├── contact.php
│   └── db.php
├── index.php
├── database.sql
└── README.md
```

## Modules and Functionalities

The project is divided into the following modules:

-   **Information Module:** Displays general information about INDET.
-   **Rooms and Reservations Module:** Allows users to view and book rooms.
-   **Room Management Module:** Allows staff to manage room status.
-   **User Management Module:** Handles user authentication and roles.

This is a simplified version of the project. The full implementation of all modules will be completed in future updates.
