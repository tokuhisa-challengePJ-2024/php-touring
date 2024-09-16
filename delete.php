<?php
session_start();
include("funcs.php");

// ログイン状態を確認
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// GETデータの取得
$tours_id = $_GET["tours_id"];

// DB接続
$pdo = db_conn();

//３．データ登録SQL作成
$stmt = $pdo->prepare("DELETE FROM tours WHERE tours_id=:tours_id");
$stmt->bindValue(':tours_id', $tours_id, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute(); //実行

//４．データ登録処理後
if($status==false){
    sql_error($stmt);
}else {
    redirect("index.php");
}

?>