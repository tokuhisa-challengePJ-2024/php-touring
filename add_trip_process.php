<?php
session_start();

// POSTデータとファイルの取得
$start_location = $_POST['start_location'];
$end_location = $_POST['end_location'];
$distance = $_POST['distance'];
$date = $_POST['date'];
$notes = $_POST['notes'];

// ファイルの処理
$photo_path = "";
if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['photo']['tmp_name'];
    $fileName = $_FILES['photo']['name'];
    $fileSize = $_FILES['photo']['size'];
    $fileType = $_FILES['photo']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // アップロードファイルの保存先パスを設定
    $uploadFileDir = './uploads/';
    $dest_path = $uploadFileDir . uniqid() . '.' . $fileExtension;

    // アップロードディレクトリが存在しない場合は作成
    if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0755, true);
    }

    // ファイルを保存
    if (move_uploaded_file($fileTmpPath, $dest_path)) {
        $photo_path = $dest_path;
    } else {
        echo "ファイルのアップロード中にエラーが発生しました。";
        exit();
    }
} else {
    if ($_FILES['photo']['error'] != UPLOAD_ERR_NO_FILE) {
        echo "ファイルアップロードエラー: " . $_FILES['photo']['error'];
        exit();
    }
}

// DB接続
try {
    $pdo = new PDO('mysql:dbname=DB名;charset=utf8;host=ホスト名', 'ユーザー名', 'パスワード');
} catch (PDOException $e) {
    exit('DBError:'.$e->getMessage());
}


// データ登録SQL作成
$sql = "INSERT INTO tours (user_id, start_location, end_location, distance, date, notes, created_date) 
        VALUES (:user_id, :start_location, :end_location, :distance, :date, :notes, sysdate())";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(':start_location', $start_location, PDO::PARAM_STR);
$stmt->bindValue(':end_location', $end_location, PDO::PARAM_STR);
$stmt->bindValue(':distance', $distance, PDO::PARAM_STR);
$stmt->bindValue(':date', $date, PDO::PARAM_STR);
$stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
$status = $stmt->execute();

if ($status === false) {
    // SQLエラー
    $error = $stmt->errorInfo();
    echo "SQLエラー: " . $error[2];
    exit();
}

// 新しいツーリングIDの取得
$tour_id = $pdo->lastInsertId();

// 画像データの登録
if ($photo_path) {
    $sql = "INSERT INTO photos (tour_id, photo_path, upload_date) VALUES (:tour_id, :photo_path, sysdate())";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':tour_id', $tour_id, PDO::PARAM_INT);
    $stmt->bindValue(':photo_path', $photo_path, PDO::PARAM_STR);
    $status = $stmt->execute();

    if ($status === false) {
        // SQLエラー
        $error = $stmt->errorInfo();
        echo "SQLエラー: " . $error[2];
        exit();
    }
}

// リダイレクト
header("Location: index.php");
exit();
?>
