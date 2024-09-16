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

// 1つのツーリング記録とその関連画像を取得する
$sql = "SELECT t.*, p.photo_id, p.photo_path 
        FROM tours t
        LEFT JOIN photos p ON t.tours_id = p.tour_id
        WHERE t.user_id = :user_id AND t.tours_id = :tours_id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(':tours_id', $tours_id, PDO::PARAM_INT);
$stmt->execute();

// クエリの実行エラーチェック
if ($stmt->errorCode() != '00000') {
    echo "SQLエラー: " . implode(', ', $stmt->errorInfo());
}

// fetchAll()で全てのデータを取得
$trip_with_photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 1つのツーリングデータを取得（同じ tours_id のため一つ目のレコードを使用）
$trip = $trip_with_photos[0];

// 画像データのみを抽出
$photos = array_filter($trip_with_photos, function ($row) {
    return isset($row['photo_id']);
});
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ツーリング記録の編集</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* プレビュー画像のスタイル */
        #preview {
            margin-top: 10px;
            max-width: 300px;
            max-height: 300px;
        }
        .photo-container {
            margin-bottom: 10px;
        }
        .photo-container img {
            max-width: 200px;
            max-height: 200px;
        }
        .photo-container button {
            display: block;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <header>
        <h1>ツーリング記録の編集</h1>
        <nav>
            <a href="index.php" class="button">ホーム</a>
            <a href="logout.php" class="button">LOGOUT</a>
            <span>ログイン中: <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        </nav>
    </header>

    <div class="container">
        <main>
            <form action="update.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="tours_id" value="<?php echo htmlspecialchars($trip['tours_id']); ?>">

                <label for="photo">新しい画像:</label>
                <input type="file" id="photo" name="photo[]" accept="image/*" multiple onchange="previewImages(event)"><br>

                <!-- 新しい画像のプレビュー -->
                <div id="preview-container"></div>

                <label for="start_location">出発地:</label>
                <input type="text" id="start_location" name="start_location" value="<?php echo htmlspecialchars($trip['start_location']); ?>" required><br>

                <label for="end_location">到着地:</label>
                <input type="text" id="end_location" name="end_location" value="<?php echo htmlspecialchars($trip['end_location']); ?>" required><br>

                <label for="distance">距離:</label>
                <input type="text" id="distance" name="distance" value="<?php echo htmlspecialchars($trip['distance']); ?>" required><br>

                <label for="date">日付:</label>
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($trip['date']); ?>" required><br>

                <label for="notes">メモ:</label>
                <textarea id="notes" name="notes"><?php echo htmlspecialchars($trip['notes']); ?></textarea><br>

                <!-- 既存の画像と削除オプション -->
                <?php if ($photos): ?>
                    <h2>既存の画像:</h2>
                    <?php foreach ($photos as $photo): ?>
                        <div class="photo-container">
                            <img src="<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="既存の画像">
                            <button type="button" onclick="deletePhoto(<?php echo $photo['photo_id']; ?>)">削除</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- 画像削除用のhiddenフィールド -->
                <input type="hidden" name="delete_photos[]" value="" id="delete-photos">

                <input type="submit" value="更新" class="button">
            </form>
        </main>
    </div>

    <script>
        function previewImages(event) {
            const input = event.target;
            const previewContainer = document.getElementById('preview-container');
            previewContainer.innerHTML = ''; // 既存のプレビューをクリア

            if (input.files) {
                for (let i = 0; i < input.files.length; i++) {
                    const file = input.files[i];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '200px';
                        img.style.maxHeight = '200px';
                        previewContainer.appendChild(img);
                    };

                    reader.readAsDataURL(file);
                }
            }
        }

        function deletePhoto(photoId) {
            if (confirm('この画像を削除しますか？')) {
                const deletePhotosInput = document.getElementById('delete-photos');
                let currentValues = deletePhotosInput.value ? deletePhotosInput.value.split(',') : [];
                if (!currentValues.includes(photoId.toString())) {
                    currentValues.push(photoId);
                    deletePhotosInput.value = currentValues.join(',');
                }
            }
        }
    </script>
</body>
</html>
