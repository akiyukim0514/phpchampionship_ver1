<?php
// ini_set('display_errors', 1); //エラーチェック用
session_start();
include("../funcs.php");
$pdo = db_conn();

//３．データ表示(デフォルトの表示用）
$sql = "SELECT * FROM terry_user_table";
$stmt = $pdo->prepare($sql);
$status = $stmt->execute();


// url直叩きを排除する
if(isset($_SESSION['id']) && isset($_SESSION['name']) && isset($_SESSION["kanri_flg"]) && $_SESSION["kanri_flg"]==0){//idと名前が正しく呼ばれているか。===0にするとさくらサーバーでエラーになる意味不
    

} else {//直叩きしたやつを強制的にログアウトする

   redirect("../login/login_top.php");
}

$view="";
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{
  //Selectデータの数だけ自動でループしてくれる
  //FETCH_ASSOC=http://php.net/manual/ja/pdostatement.fetch.php
  while( $res= $stmt->fetch(PDO::FETCH_ASSOC)){
      // viewの変数に表示させる式を突っ込んでいる
      $view .= '<tr class="t_content">';
      $view .= '<td>';
      $view .= h($res["id"]);
      $view .= '</td>';
      $view .= '<td>';
      $view .= h($res["name"]);
      $view .= '</td>';
      $view .= '<td>';
      $view .= h($res["email"]);
      $view .= '</td>';
      $view .= '<td>';
      $view .= '<img src="../img/'.h($res["image"]).'">';
      $view .= '</td>';
      $view .= '<td>';
      $view .= '<a href="user_delete.php?id='.h($res["id"]).'">';
      $view .= '[削除]';
      $view .= '</a>';
      $view .= '</td>';
      $view .= '</tr>';

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
    <title>ユーザーリスト</title>
</head>
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
              <p>ユーザーリスト</p>      
          <table class="information" border="1">
            <tr class="t_head">
              <th>ID</th><th>名前</th><th>メール</th><th>イメージ</th><th>管理用</th>
            </tr>
            <?=$view?>
          </table>
          </div>
    </div>
</div>
<!-- Main[End] -->

</body>
</html>