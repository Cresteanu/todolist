<?php
session_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=tasks_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Conexiunea a eșuat: " . $e->getMessage());
}

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}

$stmt_users = $pdo->prepare("SELECT id, username, is_admin FROM users");
$stmt_users->execute();
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

$stmt_tasks = $pdo->prepare("SELECT tasks.*, users.username FROM tasks JOIN users ON tasks.user_id = users.id ORDER BY tasks.due_date ASC");
$stmt_tasks->execute();
$tasks = $stmt_tasks->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="container">

        <h1>Panou de Administrator</h1>

        <div class="section">
            <h2>Lista Utilizatori</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nume Utilizator</th>
                        <th>Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo $user['is_admin'] ? 'Da' : 'Nu'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>    
        </div>

        <div class="section">
            <h2>Lista Sarcini</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilizator</th>
                        <th>Titlu</th>
                        <th>Descriere</th>
                        <th>Data Limită</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task['id']); ?></td>
                            <td><?php echo htmlspecialchars($task['username']); ?></td>
                            <td><?php echo htmlspecialchars($task['title']); ?></td>
                            <td><?php echo htmlspecialchars($task['description']); ?></td>
                            <td><?php echo htmlspecialchars($task['due_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p><a href="logout.php" class="logout-btn">Deconectare</a></p>  

    </div>
</body>
</html>
