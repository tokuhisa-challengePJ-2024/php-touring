<?php
session_start();

// ログイン状態を確認
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ツーリング記録の追加</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* プレビュー画像のスタイル */
        .preview {
            margin-top: 10px;
            max-width: 300px;
            max-height: 300px;
            object-fit: contain; /* 画像の比率を保持して表示 */
        }
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>ツーリング記録の追加</h1>
        <nav>
            <a href="index.php" class="button">ホーム</a>
            <a href="logout.php" class="button">LOGOUT</a>
            <span>ログイン中: <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        </nav>
    </header>

    <div class="container">
        <main>
            <form action="add_trip_process.php" method="post" enctype="multipart/form-data">
                <label for="photos">画像:</label>
                <input type="file" id="photos" name="photos[]" accept="image/*" multiple onchange="previewImages(event)"><br>

                <!-- プレビュー画像を表示するためのコンテナ -->
                <div class="preview-container" id="preview-container"></div>

                <label for="start_location">出発地:</label>
                <input type="text" id="start_location" name="start_location" required><br>

                <label for="end_location">到着地:</label>
                <input type="text" id="end_location" name="end_location" required><br>

                <label for="distance">距離:</label>
                <input type="text" id="distance" name="distance" required><br>

                <label for="date">日付:</label>
                <input type="date" id="date" name="date" required><br>

                <label for="notes">メモ:</label>
                <textarea id="notes" name="notes"></textarea><br>

                <input type="submit" value="登録" class="button">
            </form>
        </main>
    </div>

    <script>
        function previewImages(event) {
            const files = event.target.files;
            const previewContainer = document.getElementById('preview-container');

            // 既存のプレビュー画像を保持するため、画像が選択されるたびに追加する
            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('preview');
                    previewContainer.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        }
    </script>
</body>
</html>


