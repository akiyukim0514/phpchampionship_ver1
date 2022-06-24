<?php
// ini_set('display_errors', 1); 
session_start();
include("../funcs.php");

// url直叩きを排除する
if(isset($_SESSION['id'])){//idと名前が正しく呼ばれているか。===0にするとさくらサーバーでエラーになる意味不
    $uid = $_SESSION['id'];
} else {//直叩きしたやつを強制的にログアウトする

   redirect("../login/login_top.php");
}

$pdo = db_conn();

$sql = "SELECT A.id, title, S.sid, chat,C.indate, sname,auid FROM terry_ask_table A INNER JOIN terry_chat_table C ON C.aid = A.id INNER JOIN terry_sgt_table S ON A.id = S.aid  WHERE A.uid = :uid GROUP BY S.sid" ;
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':uid', $uid, PDO::PARAM_INT); 
$status = $stmt->execute();

//ーーーーー確認用
// $val="";
// if($status==false) {
//     //execute（SQL実行時にエラーがある場合）
//     sql_error($stmt);
// }else{
//     $val= $stmt->fetch(PDO::FETCH_ASSOC);
// }

// var_dump($val);
//------------


$view="";
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{

  //Selectデータの数だけ自動でループしてくれる
  //FETCH_ASSOC=http://php.net/manual/ja/pdostatement.fetch.php
  while( $res= $stmt->fetch(PDO::FETCH_ASSOC)){
      // viewの変数に表示させる式を突っ込んでいる
      $view .= '<tr class="m_id">';
      $view .= '<td>';
      $view .= h($res["id"]);
      $view .= '</td>';
      $view .= '<td>';
      $view .= '<a href="../top/mbox_detail.php?id='.h($res["sid"]).'">';
      $view .= h($res["title"]);
      $view .= '</a>';
      $view .= '<td>';
      $view .= h($res["sname"]);
      $view .= '</td>';
      $view .= '<td>';
      $view .= h($res["indate"]);
      $view .= '</td>';
      $view .= '</tr>';


      $_SESSION["tlid"] = $res["id"];
      $_SESSION["sid"] = $res["sid"];
      $_SESSION["auid"] = $res["auid"];
     
  }

}

$sql = "SELECT A.id, title, S.sid, chat,C.indate, sname,auid FROM terry_chat_table C INNER JOIN terry_sgt_table S ON C.sid = S.sid INNER JOIN terry_ask_table A ON A.id = S.aid  WHERE S.uid = :uid GROUP BY S.sid" ;
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':uid', $uid, PDO::PARAM_INT); 
$status = $stmt->execute();

//ーーーーー確認用
// $val="";
// if($status==false) {
//     //execute（SQL実行時にエラーがある場合）
//     sql_error($stmt);
// }else{
//     $val= $stmt->fetch(PDO::FETCH_ASSOC);
// }

// var_dump($val);
//------------


$other="";
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{

  //Selectデータの数だけ自動でループしてくれる
  //FETCH_ASSOC=http://php.net/manual/ja/pdostatement.fetch.php
  while( $res= $stmt->fetch(PDO::FETCH_ASSOC)){
      // viewの変数に表示させる式を突っ込んでいる
      $other .= '<tr class="m_id">';
      $other  .= '<td>';
      $other .= h($res["id"]);
      $other  .= '</td>';
      $other  .= '<td>';
      $other  .= '<a href="../top/mbox_detail.php?id='.h($res["sid"]).'">';
      $other  .= h($res["title"]);
      $other  .= '</a>';
      $other  .= '<td>';
      $other  .= h($res["sname"]);
      $other  .= '</td>';
      $other  .= '<td>';
      $other  .= h($res["indate"]);
      $other  .= '</td>';
      $other  .= '</tr>';


      $_SESSION["tlid"] = $res["id"];
      $_SESSION["sid"] = $res["sid"];
      $_SESSION["auid"] = $res["auid"];
     
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
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width">
    <link href="../main.css" rel="stylesheet"/>  
    <title>メールボックス</title>
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
        <div id="mail_wrapper">
            <div class="urpjt">
                <p>メッセージ受信箱（公募）</p>
                <table class="information" border="1">
                <tr class="t_head">
                    <th>公募ID</th><th>タイトル</th><th>提案者</th><th>メッセージ開始日</th>
                </tr>
                <?=$view?>
                </table>
            </div>
            <div class="othpjt">
                <p>メッセージ受信箱（提案）</p>
                <table class="information" border="1">
                <tr class="t_head">
                    <th>公募ID</th><th>タイトル</th><th>提案者</th><th>メッセージ開始日</th>
                </tr>
                <?=$other?>
                </table>
            </div>
        </div>
</div>