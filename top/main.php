<?php
// ini_set('display_errors', 1); 
session_start();
include("../funcs.php");
sschk();
$content='';
// $id='';
// $name='';
$kanri = $_SESSION["kanri_flg"];

$view='';
if(isset($_SESSION['id']) && isset($_SESSION['name'])){//idと名前が正しく呼ばれているか
    $id = $_SESSION['id'];
    $name = $_SESSION['name'];
    $kanri = $_SESSION["kanri_flg"];
    $image = $_SESSION["image"];
    $email = $_SESSION["email"];
    // if($kanri==0){//管理者だけに見せるやつ
    //     $view  =  '管理者用ページへ';
    //     }
} else {
   redirect("../login/login_top.php");//正しくないと落とす
}

$pdo = db_conn();

//自分が公開している内容を一覧表示させる
$sql = "SELECT * FROM terry_ask_table WHERE uid = :uid AND life_flg = :lflg ORDER BY indate DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':uid', $id, PDO::PARAM_INT); 
$stmt->bindValue(':lflg', 1, PDO::PARAM_INT); 
$status = $stmt->execute();

$view="";
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{
  //Selectデータの数だけ自動でループしてくれる
  //FETCH_ASSOC=http://php.net/manual/ja/pdostatement.fetch.php
  while( $res= $stmt->fetch(PDO::FETCH_ASSOC)){
    //   var_dump($res);
        $view .= '<div class="asks">';
        $view .= '<p class="ask_title">';
        $view .= h($res["title"]);
        $view .= '</p>';
        $view .= '<div class="ask_detail">';
        $view .= '<p class="ask_id">';
        $view .= '投稿ID.'.h($res["id"]);
        $view .= '</p>';
        $view .= '<p class="ask_date">';
        $view .= h($res["indate"]);
        $view .= '</p>';
        $view .= '<a href="../ask/ask_select.php?id='.h($res["id"]).'">';
        $view .= "詳細";
        $view .= '</a>';
        $view .= '</div>';
        $view .= '</div>';

       
        
  }
}

//自分が非公開している内容を一覧表示させる
$sql = "SELECT * FROM terry_ask_table WHERE uid = :uid AND life_flg = :lflg ORDER BY indate DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':uid', $id, PDO::PARAM_INT); 
$stmt->bindValue(':lflg', 0, PDO::PARAM_INT); 
$status = $stmt->execute();

$unview="";
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{
  //FETCH_ASSOC=http://php.net/manual/ja/pdostatement.fetch.php
  while( $res= $stmt->fetch(PDO::FETCH_ASSOC)){
    //   var_dump($res);

        $unview .= '<div class="asks">';
        $unview .= '<p class="ask_title">';
        $unview .= h($res["title"]);
        $unview .= '</p>';
        $unview .= '<div class="ask_detail">';
        $unview .= '<p class="ask_id">';
        $unview .= '投稿ID.'.h($res["id"]);
        $unview .= '</p>';
        $unview .= '<p class="ask_date">';
        $unview .= h($res["indate"]);
        $unview .= '</p>';
        $unview .= '<a href="../ask/ask_select.php?id='.h($res["id"]).'">';
        $unview .= "詳細";
        $unview .= '</a>';
        $unview .= '</div>';
        $unview .= '</div>';

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
<title>マイページ</title>
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
    <!-- Head[End] -->
    <!-- <div><?php echo h($name) ?>さんのページです。今日はどうする？</div> -->
    <!-- Main[Start] -->
    <div class="container menu">
            <div class="menu_title">
                <p><?php echo h($name) ?>さん、今日はどうする？</p>
                    <div class="menu_content">
                        <label>公募を出す：<input id='public' type="button" value="詳細を書く"></label><br>  
                    </div>
            </div>
    </div>
    <div class="ask">
        <div class="ask_top">
            <p class="open">提案募集中の公募一覧</p>
            <div id="search_view" class="container open_ask">
            <?php echo $view?>
            </div>
        </div>       
        <div class="ask_top">
            <p class="open">非公開/下書きの公募一覧</p>
            <div id="search_view" class="container open_ask">
            <?php echo $unview?>
            </div>
        </div>
    </div>



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$('#public').on('click',function(){
    window.location.href = '../ask/ask_top.php';
});
</script>
</body>
</html>