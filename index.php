<?php
session_start();
// funcs.php読み込み
include("funcs.php");

// セッションにユーザーIDが設定されているか確認
if (!isset($_SESSION['user_id'])) {
    $loggedIn = false;
} else {
    $loggedIn = true;
}

// 1. DB接続 
$pdo = db_conn();

// 2. ツーリング記録と画像を取得のSQL作成
$sql = "SELECT t.*, p.photo_path 
        FROM tours t
        LEFT JOIN photos p ON t.tours_id = p.tour_id 
        WHERE t.user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();

// 3. データ表示
if ($stmt->errorCode() != '00000') {
    echo "SQLエラー: " . implode(', ', $stmt->errorInfo());
}

// 4. 全データ取得
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC); // PDO::FETCH_ASSOC[カラム名のみで取得できるモード]

// ツーリングIDごとに画像をグループ化
$tripsGrouped = [];
foreach ($trips as $trip) {
    $toursId = $trip['tours_id'];
    if (!isset($tripsGrouped[$toursId])) {
        $tripsGrouped[$toursId] = [
            'start_location' => $trip['start_location'],
            'end_location' => $trip['end_location'],
            'distance' => $trip['distance'],
            'date' => $trip['date'],
            'notes' => $trip['notes'],
            'photos' => []
        ];
    }
    if ($trip['photo_path']) {
        $tripsGrouped[$toursId]['photos'][] = $trip['photo_path'];
    }
}
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
            <?php if ($tripsGrouped): ?>
            <ul class="trip-list">
                <?php foreach ($tripsGrouped as $toursId => $trip): ?>
                    <li>
                        <h2>
                            ツーリングID: <?php echo htmlspecialchars($toursId); ?>
                            <a href="detail.php?tours_id=<?= htmlspecialchars($toursId) ?>">📝</a>
                            <a href="delete.php?tours_id=<?= htmlspecialchars($toursId) ?>">🚮</a>
                        </h2>
                        
                        <?php if (!empty($trip['photos'])): ?>
                            <?php foreach ($trip['photos'] as $photoPath): ?>
                                <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="ツーリング画像">
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-image">画像はありません。</p>
                        <?php endif; ?>

                        <p>出発地: <?php echo htmlspecialchars($trip['start_location']); ?></p>
                        <p>到着地: <?php echo htmlspecialchars($trip['end_location']); ?></p>
                        <p>距離: <?php echo htmlspecialchars($trip['distance']); ?></p>
                        <p>日付: <?php echo htmlspecialchars($trip['date']); ?></p>
                        <p>メモ: <?php echo htmlspecialchars($trip['notes']); ?></p>
                        
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
