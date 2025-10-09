<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $comment = trim($_POST['comment']);

    if (empty($name) || empty($email) || empty($comment)) {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Todos los campos son obligatorios.'];
        header('Location: ../index.php');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Correo electrónico inválido.'];
        header('Location: ../index.php');
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO comments (name, email, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $comment);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Comentario enviado exitosamente. Espera la aprobación del administrador.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al enviar el comentario.'];
    }

    $stmt->close();
    $conn->close();
    header('Location: ../index.php');
    exit();
}

// Handle approve
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE comments SET approved = 1 WHERE id = $id");
    $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Comentario aprobado.'];
    $conn->close();
    header('Location: ../admin.php');
    exit();
}

// Handle delete
if (isset($_GET['delete_comment'])) {
    $id = intval($_GET['delete_comment']);
    $conn->query("DELETE FROM comments WHERE id = $id");
    $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Comentario eliminado.'];
    $conn->close();
    header('Location: ../admin.php');
    exit();
}
?>