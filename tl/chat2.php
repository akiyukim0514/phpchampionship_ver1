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

$aid = $_SESSION["tlid"]; //公募のID
$auid = $_SESSION["auid"]; //公募オーナーのID
$sid = $_SESSION["sid"];//提案のID
$chat =$_POST["urchat"];//チャットの内容
$cname = $_SESSION["name"];
$read = 0;//デフォルトで0（＝未読）をDBに入れる

$pdo = db_conn();

$sql = "INSERT INTO terry_chat_table(aid,auid,sid,chat,cname,userread,indate)VALUES(:aid,:auid,:sid,:chat,:cname,:userread,sysdate())";
$stmt = $pdo->prepare($sql);//prepare関数に一度データを預ける
$stmt->bindValue(':aid', $aid, PDO::PARAM_INT); 
$stmt->bindValue(':auid', $auid, PDO::PARAM_INT); 
$stmt->bindValue(':sid', $sid, PDO::PARAM_INT); 
$stmt->bindValue(':cname', $cname, PDO::PARAM_STR); 
$stmt->bindValue(':chat', $chat, PDO::PARAM_STR); 
$stmt->bindValue(':userread', $read, PDO::PARAM_INT); 
$status = $stmt->execute();


$view="";
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{
    $sql = "SELECT chat,P.name,C.indate, cname FROM terry_chat_table C LEFT JOIN terry_ask_table A ON C.aid = A.id LEFT JOIN terry_userprof_table P ON A.uid = P.uid WHERE sid = :sid";
    $stmt = $pdo->prepare($sql);//prepare関数に一度データを預ける
    $stmt->bindValue(':sid', $sid, PDO::PARAM_INT); 
    $status = $stmt->execute();

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
    }
    }



  
}
echo $view;
exit;
