<?php
// ini_set('display_errors', 1); //エラー表示
session_start();
include("../funcs.php");
// 登録画面の確認フォームの表示制御
if(isset($_SESSION['id'])){//セッションの値があれば表示
    $uid = $_SESSION['id'];
 
}else{
    redirect("../login/login_top.php");//ダイレクトに打ち込まれたら登録画面に戻す
}

$tlid = $_SESSION["tlid"];//公募のID
$sid = $_GET["id"];//提案者のID
// var_dump($tlid);

$pdo = db_conn();

//案件情報にnullはあってはいけないが、user情報はnullあっていい
$sql = "SELECT content,image,sname,dept,S.indate,S.uid FROM terry_sgt_table S INNER JOIN terry_ask_table A  ON A.id = S.aid LEFT JOIN terry_userprof_table P ON S.uid = P.uid INNER JOIN terry_user_table U ON S.uid = U.id WHERE A.id = :id AND sid =:sid";
//$sql = "SELECT * FROM terry_sgt_table S INNER JOIN terry_ask_table A  ON A.id = S.aid INNER JOIN terry_userprof_table P ON A.uid = P.uid INNER JOIN terry_user_table U ON S.uid = U.id WHERE A.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':sid', $sid, PDO::PARAM_INT); 
$stmt->bindValue(':id', $tlid, PDO::PARAM_INT); 
$status = $stmt->execute();

$val="";
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{
    $val= $stmt->fetch(PDO::FETCH_ASSOC);
}

$sql = "SELECT life_flg,id,A.uid,name FROM terry_ask_table A LEFT JOIN terry_userprof_table P ON A.uid = P.uid WHERE id = :id";
//$sql = "SELECT * FROM terry_sgt_table S INNER JOIN terry_ask_table A  ON A.id = S.aid INNER JOIN terry_userprof_table P ON A.uid = P.uid INNER JOIN terry_user_table U ON S.uid = U.id WHERE A.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $tlid, PDO::PARAM_INT); 
$status = $stmt->execute();

$ask="";
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{
    $ask= $stmt->fetch(PDO::FETCH_ASSOC);
}


$content = $val["content"];
$image = $val["image"];
$sname = $val["sname"];
$dept = $val["dept"];
$date = $val["indate"];
$suid = $val["uid"];
$_SESSION["sid"] = $sid;
$aid = $ask["id"];
$auid = $ask["uid"];
$_SESSION["auid"] = $auid;
$_SESSION["life_flg"] = $ask["life_flg"];

// var_dump($_SESSION["life_flg"]);

$who  = $sname;
$who .= '(';
$who .= $dept;
$who .= ')';

$message  = '<a href="../tl/chat_start.php?id='.h($tlid).'">';
$message .= "メッセージを送る";
$message .= '</a>';

$view  = '<a href="../tl/tl_select.php?id='.h($aid).'">';
$view .= "提案の一覧に戻る";
$view .= '</a>';

$prof  = '<a href="../tl/tl_open_profile.php?id='.h($suid).'">';
$prof .= '提案者のプロフィールを見る';
$prof .= '</a>';

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
  <title>提案内容</title>
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
          <p>提案内容</p>
          </div>
          <div class="menu_detail">
            <form method="POST" action="" enctype="multipart/form-data" >
            <input type="hidden" name="action" value="submit"/>
                  <label><p class="subtitle">名前：</p><p class="subdetail"><?php echo h($who); ?></p></label><br>
                  <label><p class="subtitle">提案日時：</p><p class="subdetail"><?php echo h($date); ?></p></label><br>

                  <label><p class="subtitle">イメージ：
                  </p><p class="subdetail"><?php echo '<img src="../img/'.h($image).'">'; ?></p></label><br>

                  <label><p class="subtitle">提案内容：
                  </p><p class="subdetail"><?php echo h($content); ?></p></label><br>
                  <?php if($uid === $auid):?>
                      <?php echo $message?><br>
                  <?php endif;?>
                  <?php echo $prof?><br>
                  <?php echo $view?><br>
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