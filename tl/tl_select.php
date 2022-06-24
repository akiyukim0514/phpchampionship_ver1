<?php
ini_set('display_errors', 1); //エラー表示
session_start();
include("../funcs.php");
// 登録画面の確認フォームの表示制御
if(isset($_SESSION['id'])&& $_SESSION['life_flg']=='1'){//セッションの値があれば表示
    $uid = $_SESSION['id'];
 
}else{
    redirect("../login/login_top.php");//ダイレクトに打ち込まれたら登録画面に戻す
}

$tlid = $_GET["id"];//?以降に入れられた公募ごとのid取得
$pdo = db_conn();

//３．３．相談したい内容の詳細をask tableからとってくる。そしてそれと紐づけられた投稿者の情報をとってくる
$sql = "SELECT A.id, name, dept, A.uid , title, point, io, type, detail, indate, life_flg FROM terry_ask_table A INNER JOIN terry_userprof_table P ON A.uid = P.uid WHERE A.id = :id";
$stmt = $pdo->prepare($sql);
if(!$stmt) {
    exit('error');//接続エラーの場合
}

$stmt->bindValue(':id', $tlid,PDO::PARAM_STR);
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
$name = $val['name'];
$dept = $val['dept'];

$who  = $name;
$who .= '(';
$who .= $dept;
$who .= ')';


$view  ='<a href="../tl/tl_detail.php?id='.h($tlid).'">';
$view .='提案する';
$view .='</a>';
$view .='<br>';

// $newsid = $_SESSION["sid"];
// var_dump($newsid);

//商品に紐づいている投稿を全部とってくる
$sql = "SELECT * FROM terry_sgt_table S INNER JOIN terry_ask_table A  ON A.id = S.aid LEFT JOIN terry_userprof_table P ON S.uid = P.uid INNER JOIN terry_user_table U ON S.uid = U.id WHERE A.id = :id ORDER BY S.indate DESC";
//$sql = "SELECT * FROM terry_sgt_table S INNER JOIN terry_ask_table A  ON A.id = S.aid INNER JOIN terry_userprof_table P ON A.uid = P.uid INNER JOIN terry_user_table U ON S.uid = U.id WHERE A.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $tlid, PDO::PARAM_INT); 
$status = $stmt->execute();

$sres="";
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{
  //FETCH_ASSOC=http://php.net/manual/ja/pdostatement.fetch.php
  while( $res= $stmt->fetch(PDO::FETCH_ASSOC)){
        $sres .= '<div class="user_sgt">';
        $sres .= '<div class="user_content">';
        $sres .= mb_strimwidth(h($res["content"]),0,50,'...','UTF-8');
        $sres .= '</div>';
        $sres .= '<div class="user_detail">';
        $sres .= '<div class="user_image">';
        $sres .= '<img src="../img/'.h($res["image"]).'">';
        $sres .= '</div>';
        $sres .= '<div class="user_name">';
        $sres .= h($res["sname"]);
        $sres .= '(';
        $sres .= h($res["dept"]);
        $sres .= ')';
        $sres .= '</div>';
        $sres .= '<div class="sgt_detail">';
        $sres .= '<a href="../tl/tl_answer.php?id='.h($res["sid"]).'">';
        $sres .= '提案の確認';
        $sres .= '</a>';
        $sres .= '</div>';
        $sres .= '</div>';
        $sres .= '</div>';
        //公募のIDを持っていく
        $_SESSION['tlid'] = $tlid;



  }
}




$form = [
  // 入力内容の受け取りの箱
  'sgt' =>'',
];

$error = [];//errorの配列、後で使う。errorが全くの空なら次に進めるように配列で設定

// サーバー側のリクエストがpostなのかgetなのかを判断する。xss対策にもなるらしい
if($_SERVER['REQUEST_METHOD']==='POST'){
    $form['sgt'] = filter_input(INPUT_POST, 'sgt', FILTER_SANITIZE_STRING);
    // titleのバリデーションチェック
    if($form['sgt'] ===''){
        $error['sgt']='blank';//から文字の時エラーを呼ぶ
    }else if(strlen($form['sgt']) > 400){ // 100文字以上を制御strlenで文字数を示す
      $error['sgt']='length';
    
    }


    if(empty($error)){//全て正常に進んだ場合の処理
        $_SESSION['sgt'] = $form['sgt'];//セッションにformの各項目を渡す
        $_SESSION['tlid'] = $tlid;
        // var_dump($_SESSION['tlid']);
        // var_dump($uid);
        // var_dump($_SESSION['name']);
        
        redirect("tl_detail.php");
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
    <title>公募詳細</title>
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
          <p>公募内容</p>
        </div>
        <div class="menu_detail">
          <form method="POST" action="" enctype="multipart/form-data" >
          <input type="hidden" name="action" value="submit"/>         
            <label><p class="subtitle">登録日時：</p><p class="subdetail"><?php echo h($date); ?></p></label><br>  
            <label><p class="subtitle">投稿者：</p><p class="subdetail"><?php echo h($who); ?></p></label><br>   
            <label><p class="subtitle">タイトル：</p><p class="subdetail"><?php echo h($title); ?></p></label><br>
            <label><p class="subtitle">相談したい内容の要点：</p><p class="subdetail"><?php echo h($point); ?></p></label><br>
            <p class="subtitle">【相談詳細】</p><br>
            <label><p class="subtitle">相談事項の対象：</p><p class="subdetail"><?php echo h($io); ?></p></label><br>
            <label><p class="subtitle">相談の種類：</p><p class="subdetail"><?php echo h($type); ?></p></label><br>
            <label><p class="subtitle">相談内容：</p><p class="subdetail"><?php echo h($detail); ?></p></label><br>
            <input class="btn_sgt" type="button" value="提案する">
            <div class="suggestion subtitle">
              <label>提案内容(最大400文字）：<textArea name="sgt" rows="4" cols="40" ><?php echo h($form['sgt']);?></textArea></label><br>
              <?php if(isset($error['sgt']) && $error['sgt'] === 'blank'):?>
                        <p class="error">*提案の要点を入力してください</p>
                <?php endif; ?>
                <?php if(isset($error['sgt']) && $error['sgt']==='length'):?>
                    <p class="error">*100文字以下で記載してください</p>
                <?php endif; ?>
            
            
              <input type="submit" value="提案する">
            </div>
              <a href="../tl/tl.php">戻る</a><br>
          </form> 
        </div>
      </div>         
    <div class="sgts">
      <div class="sgts_title">
        <p>提案内容一覧</p>
      </div>
      <div class="open_ask">
        <?php echo $sres?>
      </div>
    </div>
  </div>
<!-- Main[End] -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$('.suggestion').hide();
$('.btn_sgt').on('click',function(){
  $('.suggestion').show();
  $('.btn_sgt').hide();
})
</script>

</body>
</html>