<?php
session_start();

// エラー表示
ini_set("display_errors", 1);

// POSTデータ取得
$user_name = $_POST["user_name"];
$email = $_POST["email"];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);  // パスワードをハッシュ化

// DB接続
try {
    $pdo = new PDO('mysql:dbname=DB名;charset=utf8;host=ホスト名', 'ユーザー名', 'パスワード');
} catch (PDOException $e) {
    exit('DBError:'.$e->getMessage());
}


// データ登録SQL作成
$sql = "INSERT INTO users(user_name, email, password, create_date)VALUES (:user_name, :email, :password, sysdate())";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->bindValue(':password', $password, PDO::PARAM_STR);
$status = $stmt->execute(); // 実行

// データ登録処理後
if ($status == false) {
    // SQL実行時にエラーがある場合
    $error = $stmt->errorInfo();
    exit("SQLError:".$error[2]);
} else {
    // 登録したユーザーIDを取得
    $user_id = $pdo->lastInsertId();
    
    // セッションにユーザー情報を保存
    $_SESSION['user_id'] = $user_id;
    
    // index.phpへリダイレクト
    header("Location: index.php");
    exit();
}
?>
