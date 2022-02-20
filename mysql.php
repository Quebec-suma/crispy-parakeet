<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>MYSQL掲示板</title>
</head>
<body>
    SQL掲示板<br>
    コメントを入力してください。（名前・パスワードは任意です）
    <form action="" method="post">
        <p>
            名前　　:
            <input type="text" name="name" placeholder="名前"><br>
            コメント:
            <input type="text" name="str" placeholder="コメント"><br>
            PASSWORD:
            <input type="text" name="pw" placeholder="パスワード">
            <input type="submit" name="submit" value="新規投稿">
        </p>
        <p>
            コメントを入力の上、編集する行を指定してください。<br>
            もしくは、削除する行を指定してください。<br>
            <input type="number" name="num" placeholder="編集or削除行を指定">
            <input type="submit" name="resub" value="編集する">
            <input type="submit" name="delsub" value="削除する"><br>
            編集・削除実行用パスワードを入力してください。
            <input type="text" name="pwck" placeholder="操作実行用パスワード">
        </p>
    </form>
    <?php    
        // DB接続設定
        $dsn = 'データベース名';
        $user = 'ユーザネーム名';
        $password = 'パスワード';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

        if(!empty($_POST["str"])) {  /* コメント欄が空の時は実行しない */
            if(isset($_POST["resub"]) && !empty($_POST["num"])){  // 編集ボタンが押下され、数字が入力されたら実行
                $id = $_POST["num"];
                echo $id."行目を編集します<br>";
                
                // パスワード取得SQL
                $sql = $pdo->prepare('SELECT id,password FROM テーブル名');
                $sql->execute();
                foreach ($sql as $row){  // SELECT password FROM テーブル名 where id=:idみたいなことしたかった
                    if($row[0]==$id){
                        $PASS = $row[1];
                    }
                }
                
                if($_POST["pwck"] == $PASS && !empty($PASS)){  /* ここでパスワードチェック */
                    $sql = 'UPDATE テーブル名 SET name=:name, comment=:comment, date=:date, password=:password WHERE id=:id';
                    $sql = $pdo->prepare($sql);
                    
                    $sql->bindParam(':id', $id, PDO::PARAM_INT);
                    $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                    $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                    $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                    $sql -> bindParam(':password', $password, PDO::PARAM_STR);
                    
                    if(empty($_POST["name"])){  /* 名前欄が空の時は名無しにする */
                        $name = "名無し";
                    }else{
                        $name = $_POST["name"];
                    }
                    $comment = $_POST["str"];
                    $date = new DateTime();
                    $date = $date->format('Y-m-d H:i:s');
                    $password = $PASS;  /* パスワードは変更しない */
                    $sql -> execute();
                }elseif(empty($PASS)){
                    echo "この投稿はロックされています。編集は行われませんでした。<br>";
                }else{
                    echo "パスワードが正しくありません。編集は行われませんでした。<br>";
                }
                
            }else{
                // レコードの挿入
                $sql = $pdo -> prepare("INSERT INTO テーブル名 (name, comment, date, password) VALUES (:name, :comment, :date, :password)");
                
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                $sql -> bindParam(':password', $password, PDO::PARAM_STR);

                if(empty($_POST["name"])){  /* 名前欄が空の時は名無しにする */
                    $name = "名無し";
                }else{
                    $name = $_POST["name"];
                }
                $comment = $_POST["str"];  /* この辺編集とかぶってる部分あるから減らしたい */
                $date = new DateTime();
                $date = $date->format('Y-m-d H:i:s');
                $password = $_POST["pw"];
                $sql -> execute();
            }
        }
        
        // 削除ボタンが押下され、削除フォームに数字が入力されたら削除
        if(isset($_POST["delsub"]) && !empty($_POST["num"])){
            $id = $_POST["num"];
            echo $id."行目を削除します<br>";
            
            // パスワード取得SQL
            $sql = $pdo->prepare('SELECT id,password FROM テーブル名');
            $sql->execute();
            foreach ($sql as $row){  // SELECT password FROM テーブル名 where id=:idみたいなことしたかった
                if($row[0]==$id){
                    $PASS = $row[1];
                }
            }
            
            if($_POST["pwck"] == $PASS && !empty($PASS)){  /* ここでパスワードチェック */
                $sql = 'delete from テーブル名 where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            }elseif(empty($PASS)){
                echo "この投稿はロックされています。削除は行われませんでした。<br>";
            }else{
                echo "パスワードが正しくありません。削除は行われませんでした。<br>";
            }
        }

        // レコード表示
        $sql = 'SELECT * FROM テーブル名';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
            echo $row['id'].'|';
            echo htmlspecialchars($row['name']).', ';  /* HTMLインジェクション対策にサニタイジング */
            echo htmlspecialchars($row['comment']).' --- ';
            echo $row['date'].'<br>';
            echo "<hr width='45%' align='left'>";
        }
    ?>
</body>
</html>