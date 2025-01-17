<?php
session_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=tasks_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Conexiunea a eșuat: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title']) && !isset($_POST['edit_id'])) {
    if (!isset($_SESSION['user_id'])) {
        $error_message = "Trebuie să fii logat pentru a adăuga o sarcină.";
    } else {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $due_date = $_POST['due_date'];
        $user_id = $_SESSION['user_id'];

        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $description, $due_date]);
        header('Location: index.php');
        exit();
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$delete_id, $_SESSION['user_id']]);
    header('Location: index.php');
    exit();
}

if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$edit_id, $_SESSION['user_id']]);
    $task_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task_to_edit) {
        header('Location: index.php');
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$title, $description, $due_date, $edit_id, $_SESSION['user_id']]);
    header('Location: index.php');
    exit();
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if ($user_id) {
    $sql = "SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $current_date = date('Y-m-d');

    $past_tasks = [];
    $present_tasks = [];
    $future_tasks = [];

    foreach ($tasks as $task) {
        $task_due_date = strtotime($task['due_date']);
        $current_date_timestamp = strtotime($current_date);

        if ($task_due_date < $current_date_timestamp) {
            $past_tasks[] = $task;
        } elseif ($task_due_date === $current_date_timestamp) {
            $present_tasks[] = $task;
        } else {
            $future_tasks[] = $task;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Sarcini</title>
    <link rel="stylesheet" href="css/index_css.css">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        
        <h1>Lista de Sarcini - Bun venit, <?php echo htmlspecialchars($_SESSION['username']); ?></h1> 
        <p><a href="logout.php" class="logout-btn">Sign Out</a></p>       
    <?php else: ?>
        <h1>Lista de Sarcini</h1>
        <p><a href="login.php" class="logout-btn">Login</a></p>
     
        
    <?php endif; ?>

    <div class="form-container">
        <h2>Adaugă o Sarcină</h2>
        <form method="POST" action="index.php">
            <input type="text" name="title" placeholder="Titlul sarcinii" required>
            <textarea name="description" placeholder="Descriere" rows="3"></textarea>
            <input type="date" name="due_date" required min="<?php echo date('Y-m-d'); ?>">
            <button type="submit">Adaugă</button>
        </form>
    </div>

    <?php if (isset($error_message)): ?>
    <div class="popup-error">
        <p><?php echo $error_message; ?></p>
        <a href="login.php"><button>Login</button></a>
    </div>
    <script>
        document.querySelector('.popup-error').style.display = 'block';
    </script>
    <?php endif; ?>

    <?php if (isset($task_to_edit)): ?>
        <div class="form-container">
            <h2>Editare Sarcină</h2>
            <form method="POST" action="index.php">
                <input type="text" name="title" value="<?php echo htmlspecialchars($task_to_edit['title']); ?>" required>
                <textarea name="description" rows="3"><?php echo htmlspecialchars($task_to_edit['description']); ?></textarea>
                <input type="date" name="due_date" value="<?php echo htmlspecialchars($task_to_edit['due_date']); ?>" required>
                <input type="hidden" name="edit_id" value="<?php echo $task_to_edit['id']; ?>">
                <button type="submit">Salvează Modificările</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <h2>Sarcinile tale:</h2>
        <div class="task-container">
            <div id="past-task" class="task-window">
                <h3>Sarcini din trecut: </h3>
                <?php if (count($past_tasks) > 0): ?>
                    <?php foreach ($past_tasks as $task): ?>
                        <div class="task">
                            <h3 class="title"><?php echo htmlspecialchars($task['title']); ?></h3>
                            <p class="desc"><?php echo htmlspecialchars($task['description']); ?></p>
                            <p class="date">Data limită: <?php echo htmlspecialchars($task['due_date']); ?></p>
                            <a href="index.php?edit_id=<?php echo $task['id']; ?>" class="btn edit">Editează</a>
                            <a href="index.php?delete_id=<?php echo $task['id']; ?>" class="btn delete">Șterge</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nu există sarcini din trecut.</p>
                <?php endif; ?>
            </div>

            <div id="present-task" class="task-window">
                <h3>Sarcini din prezent: </h3>
                <?php if (count($present_tasks) > 0): ?>
                    <?php foreach ($present_tasks as $task): ?>
                        <div class="task">
                            <h3 class="title"><?php echo htmlspecialchars($task['title']); ?></h3>
                            <p class="desc"><?php echo htmlspecialchars($task['description']); ?></p>
                            <p class="date">Data limită: <?php echo htmlspecialchars($task['due_date']); ?></p>
                            <a href="index.php?edit_id=<?php echo $task['id']; ?>" class="btn edit">Editează</a>
                            <a href="index.php?delete_id=<?php echo $task['id']; ?>" class="btn delete">Șterge</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nu există sarcini din prezent.</p>
                <?php endif; ?>
             </div>

            <div id="future-task" class="task-window">
                <h3>Sarcini din viitor: </h3>
                <?php if (count($future_tasks) > 0): ?>
                    <?php foreach ($future_tasks as $task): ?>
                        <div class="task">
                            <h3 class="title"><?php echo htmlspecialchars($task['title']); ?></h3>
                            <p class="desc"><?php echo htmlspecialchars($task['description']); ?></p>
                            <p class="date">Data limită: <?php echo htmlspecialchars($task['due_date']); ?></p>
                            <a href="index.php?edit_id=<?php echo $task['id']; ?>" class="btn edit">Editează</a>
                            <a href="index.php?delete_id=<?php echo $task['id']; ?>" class="btn delete">Șterge</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nu există sarcini din viitor.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>

<?php
$pdo = null;
?>
