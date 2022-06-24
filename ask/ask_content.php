<?php
// ini_set('display_errors', 1); //エラー表示
session_start();
include("../funcs.php");
// 登録画面の確認フォームの表示制御
if(isset($_SESSION['id']) && isset($_SESSION['cid'])){//セッションの値があれば表示
    $cid = $_SESSION['cid'];
    $uid = $_SESSION['id'];
}else{
    redirect("../login/login_top.php");//ダイレクトに打ち込まれたら登録画面に戻す
}

// var_dump($cid);
//sessionでformに入力した値を持ってくる（念の為）
// $form =$_SESSION['form'];



//var_dump($cid);ok
//var_dump($form);ok

$pdo = db_conn();

//３．商品ID表示
$sql = "SELECT * FROM terry_ask_table WHERE uid=:uid AND id=:id";//uidと同じidを探してくる
$stmt = $pdo->prepare($sql);
if(!$stmt) {
    exit('error');//接続エラーの場合
}
$stmt->bindValue(':uid', $uid,PDO::PARAM_STR);//入力されたemailと検索を参照。引数は:emailではなく、?。?が二つなら二つ設定
$stmt->bindValue(':id', $cid,PDO::PARAM_STR);
$status = $stmt->execute();

$val="";
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{
    $val= $stmt->fetch(PDO::FETCH_ASSOC);
}

// var_dump($val);ok
$title =$val['title'];
$point =$val['point'];
$io =$val['io'];
$type =$val['type'];
$detail =$val['detail'];
$date = $val['indate'];

//var_dump($val);ok

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
    <title>公募登録内容</title>
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
        <p>公募登録内容</p>
        </div>
        <div class="menu_detail">
          <form method="POST" action="" enctype="multipart/form-data" >
          <input type="hidden" name="action" value="submit"/>
              <label><p class="subtitle">登録日時：</p><p class="subdetail"><?php echo h($date); ?></p></label><br>    
              <label><p class="subtitle">タイトル：</p><p class="subdetail"><?php echo h($title); ?></p></label><br>
              <label><p class="subtitle">相談したい内容の要点：</p><p class="subdetail"><?php echo h($point); ?></p></label><br>
              <p class="subtitle">【相談詳細】</p><p class="subdetail"><br>
              <label><p class="subtitle">相談事項の対象：</p><p class="subdetail"><?php echo h($io); ?></p></label><br>
              <label><p class="subtitle">相談の種類：</p><p class="subdetail"><?php echo h($type); ?></p></label><br>
              <label><p class="subtitle">相談内容：</p><p class="subdetail"><?php echo h($detail); ?></p></label><br>
              <a href="../top/main.php">戻る</a><br>
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