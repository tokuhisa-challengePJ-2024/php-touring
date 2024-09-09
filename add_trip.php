<?php
session_start();

// ログイン状態を確認
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ツーリング記録の追加</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>ツーリング記録の追加</h1>
        <nav>
            <a href="index.php" class="button">ホーム</a>
            <a href="logout.php" class="button">LOGOUT</a>
            <span>ログイン中: <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        </nav>
    </header>

    <div class="container">
        <main>
            <form action="add_trip_process.php" method="post" enctype="multipart/form-data">
                <label for="start_location">出発地:</label>
                <input type="text" id="start_location" name="start_location" required><br>

                <label for="end_location">到着地:</label>
                <input type="text" id="end_location" name="end_location" required><br>

                <label for="distance">距離:</label>
                <input type="text" id="distance" name="distance" required><br>

                <label for="date">日付:</label>
                <input type="date" id="date" name="date" required><br>

                <label for="notes">メモ:</label>
                <textarea id="notes" name="notes"></textarea><br>

                <label for="photo">画像:</label>
                <input type="file" id="photo" name="photo"><br>

                <input type="submit" value="登録" class="button">
            </form>
        </main>
    </div>
</body>
</html>
