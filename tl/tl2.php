<?php
// ini_set('display_errors', 1); //エラーチェック用
session_start();
include("../funcs.php");

// url直叩きを排除する
if(isset($_SESSION['id'])){//idが正しく呼ばれているか。===0にするとさくらサーバーでエラーになる意味不
    

} else {//直叩きしたやつを強制的にログアウトする

   redirect("../login/login_top.php");
}

$search = $_POST["search"];

$pdo = db_conn();

//３．データ表示(デフォルトの表示用）
$sql = "SELECT A.id, name, dept, A.uid , title, point, io, type, detail, indate,life_flg FROM terry_ask_table A INNER JOIN terry_userprof_table P ON A.uid = P.uid WHERE A.title like :search ORDER BY indate DESC";
$stmt = $pdo->prepare($sql);
$stmt -> bindValue(":search",'%'.$search.'%', PDO::PARAM_STR);
$status = $stmt->execute();


// var_dump($val);

$view="";
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{
  //Selectデータの数だけ自動でループしてくれる
  //FETCH_ASSOC=http://php.net/manual/ja/pdostatement.fetch.php
  while($res= $stmt->fetch(PDO::FETCH_ASSOC)){
      // viewの変数に表示させる式を突っ込んでいる
      $view .= '<div class="asks">';
      $view .= '<p class="ask_title">';
      $view .= h($res["title"]);
      $view .= '</p>';
      $view .= '<div class="ask_detail">';
      $view .= '<p class="ask_id">';
      $view .= '投稿ID.'.h($res["id"]);
      $view .= '</p>';
      $view .= '<p class="ask_name">';
      $view .= '投稿者.'.h($res["name"]);
      $view .= '(';
      $view .= h($res["dept"]);
      $view .= ')';
      $view .= '</p>';
      $view .= '<p class="ask_date">';
      $view .= h($res["indate"]);
      $view .= '</p>';
      $view .= '<a href="../tl/tl_select.php?id='.h($res["id"]).'">';
      $view .= "詳細";
      $view .= '</a>';
      $view .= '</div>';
      $view .= '</div>';

  }
}
echo $view;
exit;

  ?>