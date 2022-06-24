<?php
// ini_set('display_errors', 1); //エラー表示
session_start();
include("../funcs.php");
// 登録画面の確認フォームの表示制御
if(isset($_SESSION['form'])){//セッションの値があれば表示
    $form = $_SESSION['form'];
 
}else{
    redirect("signin_top.php");//ダイレクトに打ち込まれたら登録画面に戻す
}

$name = $form['name'];
$email = $form['email'];
$image = $form['image'];
$password = password_hash($form['password'], PASSWORD_DEFAULT);//パスのハッシュ化
$kanri = 1; //デフォルトは一般
$life = 1; //登録時点では有効の1（後でやる）

// サーバー側のリクエストがpostなのかgetなのかを判断する
if($_SERVER['REQUEST_METHOD']==='POST'){
    //データ登録
    $pdo = db_conn();
    //３．データ登録SQL作成
    $sql = "INSERT INTO terry_user_table(name,email,password,image,kanri_flg,life_flg)VALUES(:name,:email,:password,:image,:kanri_flg,:life_flg);";
    $stmt = $pdo->prepare($sql);//prepare関数に一度データを預ける
    // $stmt->bindValue(':name', $name, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    // $stmt->bindValue(':email', $email, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':password', $password, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':image', $image, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':kanri_flg', $kanri, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':life_flg', $life, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
    $status = $stmt->execute();

        //４．データ登録処理後
    if($status==false){
        //*** function化を使う！*****************
        sql_error($stmt);
    }else{
        // unset($_SESSION['form']);これはあかん書き方
        session_destroy();
        //重複登録を防ぐために、セッションを終わらす。
        //*** function化を使う！*****************
        redirect("signup_fin.php");
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
    <title>登録情報確認フォーム</title>
</head>
<body>
<div class="body">
        <header>
            <div class="logo_wrapper">
                <p class="logo">社内no壁</p>
            </div>
        </header>
        <div class="container menu">
          <div class="menu_title">
          <p>ユーザー登録内容確認</p>
          </div>
          <div class="menu_detail">
            <form method="POST" action="" enctype="multipart/form-data" >
            <input type="hidden" name="action" value="submit"/>
                <label><p class="subtitle">名前：</p><p class="subdetail"><?php echo h($form['name']); ?></p></label><br>
                <label><p class="subtitle">email：</p><p class="subdetail"><?php echo h($form['email']); ?></p></label><br>
                <label><p class="subtitle">パスワード：</p><p class="subdetail">セキュリティのため非表示</p></label><br>
                <label><p class="subtitle">イメージ：</p><p class="subdetail"><?php echo '<img src="../img/'.h($form['image']).'">'; ?></p></label><br>
                <label><p class="subtitle">この内容で登録しますか？<input type="submit" value="送信"></label><br>
                <a href="signup_top.php?regist=again">修正する</a>
            </form>
          </div>
        </div>
</div>
<!-- Main[End] -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
function previewImage(obj)
{
	var fileReader = new FileReader();
	fileReader.onload = (function() {
		document.getElementById('preview').src = fileReader.result;
	});
	fileReader.readAsDataURL(obj.files[0]);
}
</script>

</body>
</html>
