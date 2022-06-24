<?php
session_start();
include("../funcs.php");
$email='';//初期化
$password='';//初期化

$error = [];//初期化
// サーバー側のリクエストがpostなのかgetなのかを判断する
if($_SERVER['REQUEST_METHOD']==='POST'){
   $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);//emailに使用できない文字を弾く
   $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
   if($email ==='' || $password === ''){
       $error['login']='blank';//emailとパスワードが空の時にエラー表示するやつ
   } else{
        //データ接続
        $pdo = db_conn();
        //３．データ登録SQL作成
        $sql = "SELECT id, name, password, kanri_flg,email,image FROM terry_user_table WHERE email=:email";//諸々後で使うやつを取ってくる
        $stmt = $pdo->prepare($sql);//prepare関数に一度データを預ける
        if(!$stmt) {
            exit('error');//接続エラーの場合
        }
        $stmt->bindValue(':email', $email,PDO::PARAM_STR);//入力されたemailと検索を参照。引数は:emailではなく、?。?が二つなら二つ設定
        $status = $stmt->execute();
        if($status==false){
            //*** function化を使う！*****************
            sql_error($stmt);
        }
        $val = $stmt->fetch();//データ取得
        $uid = $val["id"];
        $uname = $val["name"];
        $upwd = $val["password"];
        $kanri = $val["kanri_flg"];
        $image = $val["image"];
        $umail = $val["email"];


        // var_dump($urpwd);ok
        // var_dump($password);ok
        // var_dump($kanri);ok
        // var_dump($image);ok
        // var_dump($urmail);ok

        $spw = password_verify($password, $upwd);//pwが一致してるかどうか
        if($spw){
            //login成功時
            $_SESSION["chk_ssid"]  = session_id();
            $_SESSION['id'] = $uid;
            $_SESSION['name'] = $uname;
            $_SESSION["kanri_flg"] = $kanri;
            $_SESSION["image"] = $image;
            $_SESSION["email"] = $umail;
            redirect("../tl/tl.php");
        } else {
             //Login失敗時にエラー表示でリダイレクト
            $error['login']='failed';
        }

   }
}

?>


<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width">
    <link href="../main.css" rel="stylesheet"/> 
    <title>ログイン</title>
</head>
<body>
    <div class="body">
        <header>
            <div class="logo_wrapper">
                <p class="logo">社内no壁</p>
            </div>
        </header>

<!-- lLOGINogin_act.php は認証処理用のPHPです。 -->
        <main>
            <div class="main_wrapper">  
                <form name="form1" action="" method="POST">
                    <div class="signup_title">
                        ユーザーログイン
                    </div>
                    <div class="sgu_detail">
                    <!-- email.パスワードの空欄入力回避 -->
                    <?php if(isset($error['login']) && $error['login'] === 'blank'):?>
                        <p class="error">*emailとパスワードを入力してください</p>
                    <?php endif; ?>
                    <?php if(isset($error['login']) && $error['login'] === 'failed'):?>
                        <p class="error">*ログインに失敗しました。メールアドレスとパスワードを再確認してください</p>
                    <?php endif; ?>
                メールアドレス:<input type="text" name="email" value="<?php echo h($email);?>" /><br>
                <!-- passwordで黒くブランク -->
                パスワード:   <input type="password" name="password" /><br>
                            <input type="submit" value="LOGIN" />
                    </div> 
                </form>
            </div>          
        </main>
    </div>
</body>
</html>