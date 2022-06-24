<?php
ini_set('display_errors', 1); //エラーチェック用
session_start();
include("../funcs.php");

//sinupcheck画面で修正したいというaタグを踏んで、戻ってきた時の処理（regist経由であり、それはagainで、かつセッションが設定されている場合）
if(isset($_GET['regist']) && $_GET['regist']=== 'again' && isset($_SESSION['form'])){
    $form = $_SESSION['form'];
}else {//それ以外は初期化して最初の処理
// 変数の初期化（配列）
$form = [
    // nameという名前で使うという意志。入れないと後々keyと連動させて突っ込めない
    'name' =>'',
    'email' =>'',
    'password' =>'',
];
}
$error = [];//errorの配列、後で使う。errorが全くの空なら次に進めるように配列で設定

// サーバー側のリクエストがpostなのかgetなのかを判断する。xss対策にもなるらしい
if($_SERVER['REQUEST_METHOD']==='POST'){
// filter_inputで多分postで数値受け取るのと同じ処理してるはず。こっちの方がissetかまさなくて便利な書き方という理解
    $form['name'] = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    // 名前のバリデーションチェック
    if($form['name'] ===''){
        $error['name']='blank';//から文字の時エラーを呼ぶ
    }

    //メールのエラーチェック
    $form['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);//emailに使われない文字を弾く
    // メールのバリデーションチェック
    if($form['email'] ===''){
        $error['email']='blank';
    }else {//メールの重複チェック
        $pdo = db_conn();
        $sql = "SELECT * FROM terry_user_table WHERE email=:email";//入力されたemailを後から代入して探してくる。？使ってたけどバインドパラムでできるならそっちの方がいいかな
        $stmt = $pdo->prepare($sql);
        if(!$stmt) {
            exit('error');//接続エラーの場合
        }
        $stmt->bindValue(':email', $form['email'],PDO::PARAM_STR);//入力されたemailと検索を参照。?場合は引数は:emailではなく、?。?が二つなら二つ設定
        $status = $stmt->execute();
        if($status==false){
            //*** function化を使う！*****************
            sql_error($stmt);
        }
        $count = count($stmt->fetchAll(PDO::FETCH_ASSOC)); //同じemailをカウント。なぜかsql内でのカウント（count(*))はできなかったのでこちらで
        // var_dump($count);確認用

        if($count > 0){//メールアドレスが被った時のエラー設置
            $error['email'] = 'same';
        }
     
        
    }


    $form['password'] = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);//無効な文字列を排除するやつ
        // パスワードのバリデーションチェック
    if($form['password'] ===''){
        $error['password']='blank';
    }else if(strlen($form['password']) < 8){ // 8文字以下を制御strlenで文字数を示す
        $error['password']='length';
    }

    //画像のアップロード前確認（エラーがないか、、名前が入っているか）
    if($_FILES['image']['name']!=="" && $_FILES['image']['error']=== 0) {
        $type = mime_content_type($_FILES['image']['tmp_name']);//ファイルタイプの指定。html側でも指定してるのでhtml側を書き換えられない限り問題ないかな？？
        if ($type !== 'image/png' && $type !== 'image/jpeg'){
            $error['image']='type';
        }

    }

    $image = $_FILES['image']['name'];//めんどいので変数に
    $upload ="../img/";//画像入れるサーバー

    if(empty($error)){//全て正常に進んだ場合の処理
        $_SESSION['form'] = $form;//セッションにformの各項目を渡す
        if($image !==''){
            $filename = date('YmdHis').'_'.$image;//重複チェック。同じ名前のファイルで被らないように
            if(!move_uploaded_file($_FILES['image']['tmp_name'],$upload.$filename)){//うまくいかなかった時の処理
                exit('ファイルを保存できませんでした');
            }
            $_SESSION['form']['image'] = $filename;//imageのkeyにファイルの名前を入れる
        } else{
            $_SESSION['form']['image'] = '';//名前がからなら空文字を入れる
        }    
        redirect("signup_check.php");
    }

}






// if(move_uploaded_file($_FILES['image']['tmp_name'],$upload.$image)){

// }else{
//     echo "登録失敗";
// }


?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width">
    <link href="../main.css" rel="stylesheet"/>  
    <title>新規会員登録</title>     
</head>
<body>
    <div class="body">
        <header>
            <div class="logo_wrapper">
                <p class="logo">社内no壁</p>
            </div>
        </header>

    <!-- Main[Start] -->
        <main>
            <div class="main_wrapper">  
                <form method="POST" action="" enctype="multipart/form-data" >      
                    <div class="signup_title">
                        ユーザー新規登録フォーム
                    </div>
                    <div class="sgu_detail">
                    <label>名前：<input type="text" name="name" value="<?php echo h($form['name']);?>"></label><br>
                            <!-- バリデーションチェックを入れる。エラーがセットされかつ中身がブランクの時 -->
                            <?php if(isset($error['name']) && $error['name'] === 'blank'):?>
                                <p class="error">*名前を入力してください</p>
                            <?php endif; ?>
                    <label>email：<input type="text" name="email" value="<?php echo h($form['email']);?>"></label><br>
                            <!-- バリデーションチェックを入れる。エラーがセットされかつ中身がブランクの時 -->
                            <?php if(isset($error['email']) && $error['email'] === 'blank'):?>
                                <p class="error">*emailを入力してください</p>
                            <?php endif; ?>
                            <!-- 重複チェックを入れる。エラーがセットされかつ中身が同じの時 -->
                            <?php if(isset($error['email']) && $error['email'] === 'same'):?>
                                <p class="error">*メールアドレスは既に登録されています</p>
                            <?php endif; ?>
                    <label>パスワード（8文字以上）：<input type="password" name="password"></label><br>
                            <!-- バリデーションチェックを入れる。エラーがセットされかつ中身がブランクの時 -->
                            <?php if(isset($error['password']) && $error['password'] === 'blank'):?>
                                <p class="error">*パスワードを入力してください</p>
                            <?php endif; ?>
                            <!-- 文字数チェックを入れる。エラーがセットされかつ文字が8文字を越える時の時 -->
                            <?php if(isset($error['password']) && $error['password']==='length'):?>
                                <p class="error">*パスワードは8文字以上にしてください</p>
                            <?php endif; ?>
                    <label>イメージ：<img id="preview">
                        <input id="image" type="file" name="image" accept="image/*" onchange="previewImage(this);" ></label><br>
                            <!-- バリデーションチェックを入れる。エラーがセットされかつ中身が指定していないタイプの時 -->
                            <?php if(isset($error['image']) && $error['image'] === 'type'):?>
                                <p class="error">*画像はjpg,pngのみの対応となります</p>
                            <?php endif; ?>
                    <input type="submit" value="確認">
                    </div>  
                </form>
            </div>          
        </main>
    </div>
<!-- Main[End] -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
function previewImage(obj)
{
	var fileReader = new FileReader();//filereaderの読み込み
	fileReader.onload = (function() {//アップロードしたら・・・
		document.getElementById('preview').src = fileReader.result;
	});
    //直近のファイルの最新のものを表示させる
	fileReader.readAsDataURL(obj.files[0]);
}
</script>

</body>
</html>