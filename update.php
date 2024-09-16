<?php
session_start();
include("funcs.php");

// ログイン状態を確認
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// 1. POSTデータの取得
$start_location = $_POST['start_location'];
$end_location = $_POST['end_location'];
$distance = $_POST['distance'];
$date = $_POST['date'];
$notes = $_POST['notes'];
$tours_id = $_POST['tours_id'];

// 削除対象の画像IDを取得
$delete_photos = isset($_POST['delete_photos']) ? array_filter(array_map('intval', $_POST['delete_photos'])) : [];

// 画像のアップロード処理
$uploaded_photos = [];
if (!empty($_FILES['photo']['name'][0])) {
    $upload_dir = 'uploads/';
    foreach ($_FILES['photo']['name'] as $key => $filename) {
        $file_tmp = $_FILES['photo']['tmp_name'][$key];
        $file_path = $upload_dir . basename($filename);
        if (move_uploaded_file($file_tmp, $file_path)) {
            $uploaded_photos[] = $file_path;
        }
    }
}

// 2. DB接続
$pdo = db_conn();

// 3. データ更新SQLの作成
// 1. ツーリング記録の更新
$sql = "UPDATE tours 
        SET start_location = :start_location, 
            end_location = :end_location, 
            distance = :distance, 
            date = :date, 
            notes = :notes 
        WHERE user_id = :user_id AND tours_id = :tours_id";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(':start_location', $start_location, PDO::PARAM_STR);
$stmt->bindValue(':end_location', $end_location, PDO::PARAM_STR);
$stmt->bindValue(':distance', $distance, PDO::PARAM_STR);
$stmt->bindValue(':date', $date, PDO::PARAM_STR);
$stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
$stmt->bindValue(':tours_id', $tours_id, PDO::PARAM_INT);
$status = $stmt->execute();

// 2. 画像の削除処理
if ($status) {
    if (!empty($delete_photos)) {
        $delete_sql = "DELETE FROM photos WHERE photo_id IN (" . implode(',', $delete_photos) . ")";
        $pdo->query($delete_sql);
    }

    // 3. アップロードした画像をデータベースに追加
    foreach ($uploaded_photos as $file_path) {
        $insert_sql = "INSERT INTO photos (tour_id, photo_path, upload_date) VALUES (:tour_id, :photo_path, NOW())";
        $stmt = $pdo->prepare($insert_sql);
        $stmt->bindValue(':tour_id', $tours_id, PDO::PARAM_INT);
        $stmt->bindValue(':photo_path', $file_path, PDO::PARAM_STR);
        $stmt->execute();
    }

    // 4. データ更新後のリダイレクト
    redirect("index.php");
} else {
    sql_error($stmt);
}
?>
