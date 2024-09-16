<?php
session_start();
include("funcs.php");

// ログイン状態を確認
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// POSTデータの取得
$start_location = $_POST['start_location'];
$end_location = $_POST['end_location'];
$distance = $_POST['distance'];
$date = $_POST['date'];
$notes = $_POST['notes'];

// DB接続
$pdo = db_conn();

// ツーリング記録の追加
$sql = "INSERT INTO tours (user_id, start_location, end_location, distance, date, notes, created_date) VALUES (:user_id, :start_location, :end_location, :distance, :date, :notes, sysdate())";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(':start_location', $start_location, PDO::PARAM_STR);
$stmt->bindValue(':end_location', $end_location, PDO::PARAM_STR);
$stmt->bindValue(':distance', $distance, PDO::PARAM_STR);
$stmt->bindValue(':date', $date, PDO::PARAM_STR);
$stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
$stmt->execute();

// 新しいツーリングIDを取得
$tours_id = $pdo->lastInsertId();

// 画像のアップロード処理
if (!empty($_FILES['photos']['name'][0])) {
    $uploadFileDir = './uploads/';
    // アップロードディレクトリが存在しない場合は作成
    if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0755, true);
    }

    for ($i = 0; $i < count($_FILES['photos']['name']); $i++) {
        if ($_FILES['photos']['error'][$i] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['photos']['tmp_name'][$i];
            $fileName = $_FILES['photos']['name'][$i];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // アップロードファイルの保存先パスを設定
            $dest_path = $uploadFileDir . uniqid() . '.' . $fileExtension;

            // ファイルを保存
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // 画像データをデータベースに挿入
                $sql = "INSERT INTO photos (tour_id, photo_path, upload_date) VALUES (:tour_id, :photo_path, sysdate())";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':tour_id', $tours_id, PDO::PARAM_INT);
                $stmt->bindValue(':photo_path', $dest_path, PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }
}

// リダイレクト
header("Location: index.php");
exit();
?>
