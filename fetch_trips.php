<?php
// DB接続
try {
    $pdo = new PDO('mysql:dbname=challenge-pj2024_php_touring;charset=utf8;host=mysql57.challenge-pj2024.sakura.ne.jp', 'challenge-pj2024', 'Tokuhisa5155');
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
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// エラーメッセージの表示（もしあれば）
if ($stmt->errorCode() != '00000') {
    echo "SQLエラー: " . implode(', ', $stmt->errorInfo());
}
