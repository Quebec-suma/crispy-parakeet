<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>PHP掲示板</title>
</head>
<body>
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
            コメントを入力の上、編集する行を指定してください。
            <input type="number" name="re" placeholder="数字を入力してください">
            <input type="submit" name="resub" value="編集"><br>
            削除する行を指定してください。
            <input type="number" name="del" placeholder="数字を入力してください">
            <input type="submit" name="delsub" value="削除"><br>
            編集・削除実行用パスワードを入力してください。
            <input type="text" name="pwck" placeholder="操作実行用パスワード">
        </p>
    </form>
    <?php
        $filename="テキストファイル名.txt";
        if(!empty($_POST["str"])) {  /* コメント欄が空の時は実行しない */
            $str = $_POST["str"];
            
            if(empty($_POST["name"])){  /* 名前欄が空の時は名無しにする */
                $name = "名無し";
            }else{
                $name = $_POST["name"];
            }
            
            $pw = $_POST["pw"];
            
            echo $str."を受け付けました<br>";
            
            //  行数+1を投稿番号とし、初めてなら1とする
            if(file_exists($filename)){
                $num = count(file($filename))+1;
            }else{
                $num = 1;
            }
            
            $date = date("Y/m/d/ H:i:s");
            
            if(isset($_POST["resub"]) && !empty($_POST["re"])){  // 編集ボタンが押下され、数字が入力されたら実行
                $re = $_POST["re"];
                echo $re."行目を編集します<br>";
                $lines = file($filename,FILE_IGNORE_NEW_LINES);
                $fp = fopen($filename,"w");  /* 上書き */
                
                foreach($lines as $line){  /* 一行ずつ読み取る */
                    $data = explode("<>",$line);
                    if($re != $data[0]){  /* 指定された行以外は上書きして書き直す */
                        fwrite($fp, $line.PHP_EOL);
                    }else{
                        if($_POST["pwck"] == $data[4] && !empty($data[4])){  /* ここでパスワードチェック */
                            fwrite($fp, $re."<>".$name."<>".$str."<>".$date."(編集済)"."<>".$data[4]."<>".PHP_EOL);
                        }elseif(empty($data[4])){
                            fwrite($fp, $line.PHP_EOL);
                            echo "この投稿はロックされています。編集は行われませんでした。";
                        }else{
                            fwrite($fp, $line.PHP_EOL);
                            echo "パスワードが正しくありません。編集は行われませんでした。";
                        }
                    }
                }
                fclose($fp);
                
            }else{  // コメントの新規書き込み
                $fp = fopen($filename,"a");  /* 追記可 */
                fwrite($fp, $num."<>".$name."<>".$str."<>".$date."<>".$pw."<>".PHP_EOL);
                fclose($fp);
            }
        }
        
        
        // 削除フォームに数字が入力されたら削除
        if(!empty($_POST["del"])){
            $del = $_POST["del"];
            echo $del."行目を削除します<br>";
            
            if(file_exists($filename)){  /* ファイルの存在チェック */
                $lines = file($filename,FILE_IGNORE_NEW_LINES);
                $fp = fopen($filename,"w");  /* 上書き */
                
                foreach($lines as $line){  /* 一行ずつ読み取る */
                    $data = explode("<>",$line);
                    if($del != $data[0]){  /* 指定された行以外は上書きして書き直す */
                        fwrite($fp, $line.PHP_EOL);
                    }else{
                        if(empty($data[4])){
                            fwrite($fp, $line.PHP_EOL);
                            echo "この投稿はロックされています。削除は行われませんでした。";
                        }elseif($_POST["pwck"] == $data[4]  && !empty($data[4])){  /* ここでパスワードチェック */
                            fwrite($fp, $del."<>***<>この投稿は削除されました<>".$data[3]."<><>".PHP_EOL);
                        }else{
                            fwrite($fp, $line.PHP_EOL);
                            echo "パスワードが正しくありません。削除は行われませんでした。";
                        }
                    }
                }
                fclose($fp);
            }
        }
    ?>
    <p>
    <?php
        // コメントの表示
        $filename="テキストファイル名.txt";
        if(file_exists($filename)){  /* ファイルの存在チェック */
            $lines = file($filename,FILE_IGNORE_NEW_LINES);
            foreach($lines as $line){  /* 一行ずつ読み取る */
                $data = explode("<>",$line);
                echo $data[0]."|".$data[1].", ".$data[2]." --- ".$data[3]."<br>";
                //echo "<hr width='45%' align='left'>";  罫線いる？
            }
        }
    ?>
    </p>
</body>
</html>