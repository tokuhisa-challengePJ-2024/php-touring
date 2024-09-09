<?php
session_start();

// セッションにユーザーIDが設定されているか確認
if (!isset($_SESSION['user_id'])) {
    $loggedIn = false;
} else {
    $loggedIn = true;
}

// DB接続
try {
    $pdo = new PDO('mysql:dbname=DB名;charset=utf8;host=ホスト名', 'ユーザー名', 'パスワード');
} catch (PDOException $e) {
    exit('DBError:'.$e->getMessage());
}

// ツーリング記録と画像を取得
$sql = "SELECT t.*, p.photo_path 
        FROM tours t
        LEFT JOIN photos p ON t.tours_id = p.tour_id
        WHERE t.user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();

// クエリの実行エラーチェック
if ($stmt->errorCode() != '00000') {
    echo "SQLエラー: " . implode(', ', $stmt->errorInfo());
}

$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ツーリング記録</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>ツーリング記録</h1>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>ログイン中: <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="add_trip.php">ツーリング記録追加</a>
                <a href="logout.php">ログアウト</a>
            <?php else: ?>
                <a href="login.html">ログイン</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <main>
            <?php if ($trips): ?>
            <ul class="trip-list">
                <?php foreach ($trips as $trip): ?>
                    <li>
                        <h2>ツーリングID: <?php echo htmlspecialchars($trip['tours_id']); ?></h2>
                        <p>出発地: <?php echo htmlspecialchars($trip['start_location']); ?></p>
                        <p>到着地: <?php echo htmlspecialchars($trip['end_location']); ?></p>
                        <p>距離: <?php echo htmlspecialchars($trip['distance']); ?></p>
                        <p>日付: <?php echo htmlspecialchars($trip['date']); ?></p>
                        <p>メモ: <?php echo htmlspecialchars($trip['notes']); ?></p>
                        <?php if ($trip['photo_path']): ?>
                            <img src="<?php echo htmlspecialchars($trip['photo_path']); ?>" alt="ツーリング画像">
                        <?php else: ?>
                            <p class="no-image">画像はありません。</p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
                <p>記録がありません。</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
