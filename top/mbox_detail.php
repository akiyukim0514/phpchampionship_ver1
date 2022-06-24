<?php
// ini_set('display_errors', 1); //エラー表示
session_start();
include("../funcs.php");
// 公募のオーナーで、その人が閲覧者の時だけOK
if(isset($_SESSION['id'])){//セッションの値があれば表示
    $uid = $_SESSION['id'];//閲覧者のid

 
}else{
    redirect("../login/login_top.php");//ダイレクトに打ち込まれたら登録画面に戻す
}
$sid = $_GET["id"];//提案のID
$_SESSION["sid"] = $sid;


$pdo = db_conn();

//未読と既読の管理
$sql = "SELECT sid, chat, userread,cname FROM terry_chat_table WHERE sid = :sid ORDER BY indate DESC LIMIT 1";
$stmt = $pdo->prepare($sql);//prepare関数に一度データを預ける
$stmt->bindValue(':sid', $sid, PDO::PARAM_INT); 
$status = $stmt->execute();

$val="";
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{
    $val= $stmt->fetch(PDO::FETCH_ASSOC);
}

$read = $val['userread'];
$npname = $val['cname'];
$npsid = $val['sid'];
$_SESSION['name'];
// var_dump($npname);
if($npname !== $_SESSION['name'] && $read == 0 ){
    $newread = 1;
    $sql = "UPDATE terry_chat_table SET userread=:userread WHERE sid=:sid ";
    $stmt = $pdo->prepare($sql);//prepare関数に一度データを預ける
    $stmt->bindValue(':sid', $npsid, PDO::PARAM_INT); 
    $stmt->bindValue(':userread', $newread, PDO::PARAM_INT); 
    $status = $stmt->execute();

    if($status==false) {
        //execute（SQL実行時にエラーがある場合）
        sql_error($stmt);
    }else{
    
    }
}




$sql = "SELECT A.id,auid,chat,sname,cname,C.indate FROM terry_ask_table A LEFT JOIN terry_chat_table C ON C.aid = A.id LEFT JOIN terry_sgt_table S ON C.sid = S.sid WHERE C.sid = :sid";
$stmt = $pdo->prepare($sql);//prepare関数に一度データを預ける
$stmt->bindValue(':sid', $sid, PDO::PARAM_INT); 
$status = $stmt->execute();

//---確認用
// $val="";
// if($status==false) {
//     //execute（SQL実行時にエラーがある場合）
//     sql_error($stmt);
// }else{
//     $val= $stmt->fetch(PDO::FETCH_ASSOC);
// }
//---


$view="";
if($status==false) {
    sql_error($stmt);
}else{
  while($res= $stmt->fetch(PDO::FETCH_ASSOC)){
      // viewの変数に表示させる式を突っ込んでいる
      $view .= '<div class="chat_area">';
      if($res['cname']===$_SESSION['name']){
        $view .= '<div class="block">';
        $view .= '<div class="thischat">';
        $view .= $res['chat'];
        $view .= '</div>';
        $view .= '</div>';
        $view .= '<div class="chat_detail">';
        $view .= $res['cname'];
        $view .= '　';
        $view .= $res['indate'];
        $view .= '</div>';
        $view .= '</div>';
      }else {
        $view .= '<div class="thischat_l">';
        $view .= $res['chat'];
        $view .= '</div>';
        $view .= '<div class="chat_detail_l">';
        $view .= $res['cname'];
        $view .= '　';
        $view .= $res['indate'];
        $view .= '</div>';
        $view .= '</div>';

      }

      $_SESSION['tlid'] = $res['id'];
      $_SESSION['auid'] = $res['auid'];
      
  }
}




$backans  = '<a href="../tl/tl_answer.php?id='.h($sid).'">';
$backans .= "提案に戻る";
$backans .= '</a>';

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
    <title>メッセージ</title>
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
    <div class="chatall">
            <div class="chat_container">
                    <div id="chat_content">
                        <?= $view ?>
                    </div>
                    <div class="chat_send">
                        <textArea id="urchat" rows="4" cols="100" ></textArea>
                        <button id="chat_send">送信</button>
                        
                    </div>
                    <?= $backans ?>
                    <a class="btn profile" href="../top/top_mbox.php">メッセージボックスへ</a>
            </div>
    </div>
<!-- Main[End] -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
  
  let test = $("#urchat").val();
  console.log(test);

$("#chat_send").on("click", function() {
    //axiosでAjax送信
    //Ajax（非同期通信）
    const params = new URLSearchParams();
    params.append('urchat',   $("#urchat").val());
    //axiosでAjax送信
    axios.post('../tl/chat2.php',params).then(function (response) {
        console.log(typeof response.data);//通信OK
        if(response.data){
          //>>>>通信でデータを受信したら処理をする場所<<<<
          document.querySelector("#chat_content").innerHTML=response.data;
          
        }
    }).catch(function (error) {
        console.log(error);//通信Error
    }).then(function () {
        console.log("test成功");//通信OK/Error後に処理を必ずさせたい場合
    });
    $("#urchat").val("");


});
</script>

</body>
</html>