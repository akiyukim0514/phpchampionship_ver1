<?php
// ini_set('display_errors', 1); //エラーチェック用
session_start();
include("../funcs.php");

// url直叩きを排除する
if(isset($_SESSION['id'])){//idが正しく呼ばれているか。===0にするとさくらサーバーでエラーになる意味不
    

} else {//直叩きしたやつを強制的にログアウトする

   redirect("../login/login_top.php");
}

$pdo = db_conn();

//３．公募と投稿者のプロフィール情報を一致させる、かつ降順で、かつ公開で
$sql = "SELECT A.id, name, dept, A.uid , title, point, io, type, detail, indate,life_flg FROM terry_ask_table A INNER JOIN terry_userprof_table P ON A.uid = P.uid WHERE life_flg = :lflg ORDER BY indate DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':lflg', 1, PDO::PARAM_INT); 
$status = $stmt->execute();


// var_dump($val);

//ここから公募情報表示
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

      $_SESSION["life_flg"] = $res["life_flg"];

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
//チャットテーブル名前が自分の案件、あるいは自分が提案している案件で、名前が自分ではない、そしてuserread数値が０（つまり未読）を探してくる）
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
    <title>みんなの投稿</title>
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
    <!-- Main[Start] --> 
        
        <div class="container menu">
            <div class="menu_title">
                    <p>誰かに聞きたいことがある</p>
                    <div class="menu_content">
                        <label>公募を出す：<input class="btn_tl" id='public' type="button" value="詳細を書く"></label><br>
                        <label>公募を検索して探す：<input id='search' type="text" value="">
                        <button id="send">検索</button></label><br>  
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
        </div>         
    </div>

    


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
$('#public').on('click',function(){
    window.location.href = '../ask/ask_top.php';
});

//検索ajax処理
//登録ボタンをクリック
$("#send").on("click", function() {
    //axiosでAjax送信
    //Ajax（非同期通信）
    const params = new URLSearchParams();
    params.append('search',   $("#search").val());
    
    //axiosでAjax送信
    axios.post('tl2.php',params).then(function (response) {
        console.log(typeof response.data);//通信OK
        if(response.data){
          //>>>>通信でデータを受信したら処理をする場所<<<<
          document.querySelector("#search_view").innerHTML=response.data;
          
        }
    }).catch(function (error) {
        console.log(error);//通信Error
    }).then(function () {
        console.log("test成功");//通信OK/Error後に処理を必ずさせたい場合
    });


});

</script>
</body>
</html>