<?php
session_start();
include 'db.php';

// User must be logged in to submit a review
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);
    $comment = filter_var(trim($_POST['comment']), FILTER_SANITIZE_STRING);

    // For simplicity, we are not linking reviews to a specific room booking here.
    // In a real app, you'd verify the user has stayed in a room before allowing a review.
    // We'll use a placeholder room_id, e.g., 1, or the first room available.
    $room_id = 1; // Placeholder

    if (empty($comment) || $rating < 1 || $rating > 5) {
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'Por favor, proporciona una calificación y comentario válidos.'
        ];
        header("Location: ../index.php#reviews");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO reviews (user_id, room_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $user_id, $room_id, $rating, $comment);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = [
            'status' => 'success',
            'text' => '¡Gracias por tu opinión!'
        ];
    } else {
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'Hubo un error al enviar tu opinión.'
        ];
    }

    $stmt->close();
    $conn->close();

    header("Location: ../index.php#reviews");
    exit();

} else {
    header("Location: ../index.php");
    exit();
}
?>
