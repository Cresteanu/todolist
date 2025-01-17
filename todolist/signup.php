<?php
session_start();

include('db.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_message = 'Toate câmpurile sunt obligatorii.';
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_user) {
            $error_message = 'Utilizatorul există deja.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $hashed_password]);
            header('Location: login.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Înregistrează-te</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>

<div class="container">
    <h2>Înregistrează-te</h2>

    <?php if (isset($error_message)) : ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="signup.php">
        <input type="text" name="username" placeholder="Nume utilizator" required>
        <input type="password" name="password" placeholder="Parolă" required>
        <button type="submit">Creează cont</button>
    </form>

    <div class="login-link">
        <p>Ai deja un cont? <a href="login.php">Autentifică-te aici</a></p>
    </div>
</div>

</body>
</html>
