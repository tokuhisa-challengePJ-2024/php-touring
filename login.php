<?php
session_start();  // セッションを開始

// エラーメッセージの初期化
$errorMessage = "";

// POSTデータが送信されたか確認
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // メールアドレスとパスワードを取得
    $email = $_POST["email"];
    $password = $_POST["password"];

    // DB接続
    try {
        $pdo = new PDO('mysql:dbname=DB名;charset=utf8;host=ホスト名', 'ユーザー名', 'パスワード');
    } catch (PDOException $e) {
        exit('DBError:'.$e->getMessage());
    }


    // メールアドレスがDBに存在するか確認
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ユーザーが見つかり、パスワードが一致するか確認
    if ($user && password_verify($password, $user['password'])) {
        // セッションにユーザーIDを保存
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];  // ユーザー名も保存しておくと便利
        header("Location: index.php");  // ログイン後にindex.phpにリダイレクト
        exit();
    } else {
        // 認証エラー
        $errorMessage = "メールアドレスまたはパスワードが間違っています。";
    }
}
?>
