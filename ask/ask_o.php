<?php
//1. POSTデータ取得（プライマリキーのところで必ずやる。被りがないため）
$tcid = $_GET["id"];

//2. DB接続します
include("../funcs.php");  //funcs.phpを読み込む（関数群）
$pdo = db_conn();      //DB接続関数

$lflg= 1;//公開する場合は1、非公開にする場合は０。デフォルトは公開で設定する

//データ接続
$pdo = db_conn();

$sql = "UPDATE terry_ask_table SET life_flg=:lflg,indate=sysdate() WHERE id=:id";
$stmt = $pdo->prepare($sql);//prepare関数に一度データを預ける
$stmt->bindValue(':id', $tcid, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':lflg', $lflg, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute();

    //４．データ登録処理後
if($status==false){
    sql_error($stmt);
}else{
    redirect("../top/main.php");
}

?>
