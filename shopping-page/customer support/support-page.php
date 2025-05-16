<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'shopping';
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = "";
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['reply'], $_POST['csrf_token'])) {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $id = (int) $_POST['id'];
        $reply = htmlspecialchars(trim($_POST['reply']));
        $stmt = $conn->prepare("UPDATE support_messages SET reply = ?, replied_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $reply, $id);
        $stmt->execute();
        $message = $stmt->affected_rows > 0 ? "<div class='alert success'>Reply sent successfully.</div>" :
                                              "<div class='alert error'>Failed to send reply.</div>";
        $stmt->close();
    } else {
        $message = "<div class='alert error'>Invalid CSRF token.</div>";
    }
}

// Build query with optional search
$sql = "SELECT * FROM support_messages";
$params = [];
if ($search !== "") {
    $sql .= " WHERE user_email LIKE ? OR message LIKE ?";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($search !== "") {
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Support Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --bg-color: #f4f4f4;
            --text-color: #333;
            --primary-color: #3498db;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --font: "Segoe UI", sans-serif;
        }

        body.dark {
            --bg-color: #1e1e1e;
            --text-color: #e0e0e0;
            --primary-color: #2980b9;
        }

        body {
            margin: 0; font-family: var(--font); background-color: var(--bg-color); color: var(--text-color);
        }

        header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dark-toggle {
            cursor: pointer;
            padding: 8px 12px;
            background: rgba(255,255,255,0.1);
            border: 1px solid white;
            border-radius: 4px;
            color: white;
        }

        main {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
        }

        .search-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button, .btn {
            padding: 10px 16px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            vertical-align: top;
        }

        th {
            background-color: #f9f9f9;
        }

        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            position: relative;
        }

        .alert.success { background-color: var(--success-color); color: white; }
        .alert.error { background-color: var(--error-color); color: white; }

        .alert .close {
            position: absolute;
            top: 8px;
            right: 12px;
            cursor: pointer;
            color: white;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            min-height: 100px;
        }

        .char-count {
            font-size: 0.85em;
            text-align: right;
            color: gray;
        }

        @media (max-width: 768px) {
            .search-bar { flex-direction: column; width: 100%; }
            input[type="text"] { width: 100%; }
        }
    </style>
</head>
<body>

<header>
    <h2>Support Messages Admin</h2>
    <div class="dark-toggle" onclick="toggleDarkMode()">🌙 Toggle Dark</div>
</header>

<main>
    <?php if ($message) echo $message; ?>

    <form method="GET" class="search-bar">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by email or message...">
        <button type="submit">Search</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Message</th>
                <th>Reply</th>
                <th>Respond</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['user_email']) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                        <td>
                            <?php if ($row['reply']): ?>
                                <?= nl2br(htmlspecialchars($row['reply'])) ?><br>
                                <small><em>Replied at: <?= $row['replied_at'] ?></em></small>
                            <?php else: ?>
                                <em>Pending</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$row['reply']): ?>
                                <form method="POST" onsubmit="return disableSubmit(this)">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <textarea name="reply" oninput="updateCharCount(this)" maxlength="1000" required></textarea>
                                    <div class="char-count">0 / 1000</div>
                                    <button type="submit">Send Reply</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No messages found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<script>
    function updateCharCount(textarea) {
        const counter = textarea.nextElementSibling;
        counter.textContent = `${textarea.value.length} / ${textarea.maxLength}`;
    }

    function disableSubmit(form) {
        const button = form.querySelector("button");
        button.disabled = true;
        button.textContent = "Sending...";
        return true;
    }

    function toggleDarkMode() {
        document.body.classList.toggle("dark");
    }

    document.querySelectorAll('.alert .close').forEach(btn => {
        btn.addEventListener('click', () => btn.parentElement.remove());
    });
</script>

</body>
</html>
<?php
$conn->close();
?>
