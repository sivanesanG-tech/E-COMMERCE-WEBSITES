<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: delivery_login.php'); // Redirect to login if not logged in
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'shopping');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if id and delivery_status are set
if (isset($_POST['id'], $_POST['delivery_status'])) {
    $id = intval($_POST['id']); // Sanitize id
    $delivery_status = $conn->real_escape_string($_POST['delivery_status']); // Sanitize delivery_status
    $delivery_notes = isset($_POST['delivery_notes']) ? $conn->real_escape_string(trim($_POST['delivery_notes'])) : '';

    // Fetch existing delivery notes and current status
    $query = "SELECT delivery_status, delivery_notes FROM orders WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($current_status, $existing_notes);
    if ($stmt->fetch()) {
        $stmt->close();

        // Define allowed status transitions
        $allowed_transitions = [
            'Pending' => ['In Transit', 'Cancelled'],
            'In Transit' => ['Delivered', 'Cannot Reach Customer'],
            'Cannot Reach Customer' => ['In Transit', 'Cancelled'],
            'Cancelled' => [], // Once cancelled, no further updates
            'Delivered' => [] // Once delivered, no further updates
        ];

        // Validate transition
        $is_valid = isset($allowed_transitions[$current_status]) &&
                    in_array($delivery_status, $allowed_transitions[$current_status]);

        if ($is_valid) {
            // Append new notes to existing notes
            $timestamp = date('Y-m-d H:i:s');
            $final_notes = $existing_notes;
            if (!empty($delivery_notes)) {
                $note_entry = "\n\n[$timestamp] - $delivery_notes";
                $final_notes .= $note_entry;
            }

            // Update the delivery status and notes
            $update_query = "UPDATE orders SET delivery_status = ?, delivery_notes = ?";
            if ($delivery_status === 'Delivered') {
                $update_query .= ", delivery_time = NOW()";
            }
            $update_query .= " WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssi", $delivery_status, $final_notes, $id);

            if ($stmt->execute()) {
                $_SESSION['status_message'] = [
                    'type' => 'success',
                    'text' => "Order #$id has been successfully updated to $delivery_status."
                ];
            } else {
                $_SESSION['status_message'] = [
                    'type' => 'error',
                    'text' => "Error updating order #$id: " . $stmt->error
                ];
            }
            $stmt->close();
        } else {
            $_SESSION['status_message'] = [
                'type' => 'error',
                'text' => "Invalid status transition for order #$id from $current_status to $delivery_status."
            ];
        }
    } else {
        $_SESSION['status_message'] = [
            'type' => 'error',
            'text' => "Order #$id not found."
        ];
    }
} else {
    $_SESSION['status_message'] = [
        'type' => 'error',
        'text' => "Invalid request. Missing required data."
    ];
}

header('Location: delivery.php');
$conn->close();
?>
