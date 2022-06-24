<?php
// ini_set('display_errors', 1); //エラー表示
session_start();
include("../funcs.php");
// 登録画面の確認フォームの表示制御
if(isset($_SESSION['form'])){//セッションの値があれば表示
    $form = $_SESSION['form'];
 
}else{
    redirect("../login/login_top.php");//ダイレクトに打ち込まれたら登録画面に戻す
}

$title = $form['title'];
$point = $form['point'];
$io = $form['inout'];
$type = $form['type'];
$detail = $form['detail'];
$uid = $_SESSION['id'];
$lflg= 1;//公開する場合は1、非公開にする場合は０。デフォルトは公開で設定する

//var_dump($uid);
//データ接続
$pdo = db_conn();

// サーバー側のリクエストがpostなのかgetなのかを判断する
if($_SERVER['REQUEST_METHOD']==='POST'){

    //３．データ登録SQL作成
    $sql = "INSERT INTO terry_ask_table(uid,title,point,io,type,detail,life_flg,indate)VALUES(:uid,:title,:point,:io,:type,:detail,:lflg,sysdate());";
    $stmt = $pdo->prepare($sql);//prepare関数に一度データを預ける
    // $stmt->bindValue(':name', $name, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    // $stmt->bindValue(':email', $email, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':point', $point, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':io', $io, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':detail', $detail, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':lflg', $lflg, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
    $status = $stmt->execute();

    //最後に入力したデータのunique IDをとってくる
    $cid = $pdo->lastInsertId();
        //４．データ登録処理後
    if($status==false){
        //*** function化を使う！*****************
        sql_error($stmt);
    }else{
        //今回登録された公募内容のidをセッションで取得
        $_SESSION["cid"] = $cid;
        //*** function化を使う！*****************
        redirect("ask_content.php");
    }
}

//ヘッダーのアイコン
$iconimg = $_SESSION['image'];
$headericon  = '<div class="hicon">';
$headericon .= '<a href="../profile/profile.php">';
$headericon .= '<img src="../img/'.h($iconimg).'">';
$headericon .= '</a>';
$headericon .= '</div>';

$kanri = $_SESSION["kanri_flg"];

//未読メッセージカウント
$sql = "SELECT userread FROM terry_chat_table C INNER JOIN terry_sgt_table S ON C.sid = S.sid WHERE (auid = :auid OR S.uid = :uid) AND cname != :cname AND userread = :userread";
$stmt = $pdo->prepare($sql);//prepare関数に一度データを預ける
$stmt->bindValue(':cname', $_SESSION['name'], PDO::PARAM_STR); 
$stmt->bindValue(':auid', $_SESSION['id'], PDO::PARAM_INT); 
$stmt->bindValue(':uid', $_SESSION['id'], PDO::PARAM_INT); 
$stmt->bindValue(':userread', 0 , PDO::PARAM_INT);
$status = $stmt->execute();
if($status==false){
    sql_error($stmt);
}
$mcount = count($stmt->fetchAll(PDO::FETCH_ASSOC));

$mcview  = '<span>';
$mcview .= $mcount;
$mcview .= '</span>';

?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width">
    <link href="../main.css" rel="stylesheet"/>  
    <title>公募内容確認フォーム</title>
</head>
<body>
<div class="body">
    <header>
        <div class="header_content">
            <div class="logo_wrapper">
                <p class="logo">社内no壁</p>
            </div>
            <div class="header_menu">
                    <a class="btn_plofile" href="../tl/tl.php">みんなの投稿</a>
                    <a class="btn_plofile" href="../top/top_mbox.php">メール<?php echo $mcview?></a>
                    <a class="btn_plofile" href="../profile/profile.php">プロフィール</a>
                    <?php if($kanri==0):?>
                    <a class="btn_plofile" href="../admin/master.php">管理者ページ</a>
                    <?php endif;?>
                    <a class="btn_logout" href="../login/logout.php">LOGOUT</a>
                    <?=$headericon ?>
            </div>
        </div>
    </header>
    <div class="container menu">
        <div class="menu_title">
        <p>公募内容確認</p>
        </div>
        <div class="menu_detail">
            <form method="POST" action="" enctype="multipart/form-data" >
            <input type="hidden" name="action" value="submit"/>
                <legend><p class="subtitle">公募内容</p><p class="subdetail"></legend>
                <label><p class="subtitle">タイトル：</p><p class="subdetail"><?php echo h($title); ?></p></label><br>
                <label><p class="subtitle">相談したい内容の要点：</p><p class="subdetail"><?php echo h($point); ?></p></label><br>
                <p class="subtitle">【相談詳細】</p><br>
                <label><p class="subtitle">相談事項の対象：</p><p class="subdetail"><?php echo h($io); ?></p></label><br>
                <label><p class="subtitle">相談の種類：</p><p class="subdetail"><?php echo h($type); ?></p></label><br>
                <label><p class="subtitle">相談内容：</p><p class="subdetail"><?php echo h($detail); ?></p></label><br>
                <label><p class="subtitle">この内容で公募しますか？</p><input type="submit" value="募集する"></label><br>
                <a href="ask_top.php?regist=again">修正する</a>
            </form>
        </div>
    </div>
</div>
<!-- Main[End] -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

</script>

</body>
</html>