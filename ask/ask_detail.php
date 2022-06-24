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

$tcid = $_GET["id"];//?以降に入れられた公募ごとのid取得
$pdo = db_conn();

//３．商品IDからデータ取得
$sql = "SELECT * FROM terry_ask_table WHERE uid=:uid AND id=:id";//uidと同じidを探してくる
$stmt = $pdo->prepare($sql);
if(!$stmt) {
    exit('error');//接続エラーの場合
}
$stmt->bindValue(':uid', $uid,PDO::PARAM_STR);//入力されたemailと検索を参照。引数は:emailではなく、?。?が二つなら二つ設定
$stmt->bindValue(':id', $tcid,PDO::PARAM_STR);
$status = $stmt->execute();

$val="";
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{
    $val= $stmt->fetch(PDO::FETCH_ASSOC);
}



$update = [
  // 入力内容の受け取りの箱
  'title' =>'',
  'point' =>'',
  'inout' =>'',
  'type' =>'',
  'detail' =>'',
  'oc'=>'',
];

if(isset($_GET['regist']) && $_GET['regist']=== 'again' && isset($_SESSION['form'])){
  $update = $_SESSION['update'];
  $title = $update['title'];
  $point = $update['point'];
  $io    = $update['inout'];
  $type  = $update['type'];
  $detail =$update['detail'];

}else {
  // var_dump($val);ok
  $title =$val['title'];
  $point =$val['point'];
  $io    =$val['io'];
  $type  =$val['type'];
  $detail =$val['detail'];
}

$error = [];//errorの配列、後で使う。errorが全くの空なら次に進めるように配列で設定

// サーバー側のリクエストがpostなのかgetなのかを判断する。xss対策にもなるらしい
if($_SERVER['REQUEST_METHOD']==='POST'){
    $update['title'] = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    // titleのバリデーションチェック
    if($update['title'] ===''){
        $error['title']='blank';//から文字の時エラーを呼ぶ
    }

    //pointのエラーチェック
    $update['point'] = filter_input(INPUT_POST, 'point', FILTER_SANITIZE_STRING);
    // pointの空と文字数制限
    if($update['point'] ===''){
        $error['point']='blank';
    }else if(strlen($update['point']) > 100){ // 100文字以上を制御strlenで文字数を示す
        $error['point']='length';
    }

    // inoutのバリデーションチェック
    $update['inout'] = filter_input(INPUT_POST, 'inout',FILTER_SANITIZE_STRING);
    if(is_null($update['inout'])){
        $error['inout']='blank';
    }

    // typeの値を受け取る
    $update['type'] = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);

    // detailの値を受け取る
    $update['detail'] = filter_input(INPUT_POST, 'detail',FILTER_SANITIZE_STRING);
    if($update['detail']===''){
        $error['detail']='blank';
    } else if(strlen($update['detail']) > 400){ // 100文字以上を制御strlenで文字数を示す
        $error['detail']='length';
    }

    $update['oc'] = filter_input(INPUT_POST, 'oc',FILTER_SANITIZE_STRING);
    if(is_null($update['oc'])){
        $error['oc']='blank';
    }

    // var_dump($update);ok


    if(empty($error)){//全て正常に進んだ場合の処理
        $_SESSION['update'] = $update;//セッションにformの各項目を渡す
        $_SESSION['tcid'] = $tcid;
        redirect("ask_update.php");
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
    <title>公募内容修正</title>
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
                  <label><p class="subtitle">タイトル:</p><p class="subdetail"><input type="text" name="title" value="<?php echo h($title);?>"></label><br>
                      <?php if(isset($error['title']) && $error['title'] === 'blank'):?>
                          <p class="error">*タイトルを入力してください</p>
                      <?php endif; ?>
                  <label><p class="subtitle">相談したい内容の要点（100文字以内）:</p><p class="subdetail"><input type="text" name="point" value="<?php echo h($point);?>"></label><br>
                      <?php if(isset($error['point']) && $error['point'] === 'blank'):?>
                              <p class="error">*相談の要点を入力してください</p>
                      <?php endif; ?>
                      <?php if(isset($error['point']) && $error['point']==='length'):?>
                          <p class="error">*100文字以下で記載してください</p>
                      <?php endif; ?>
                      <p class="subtitle">【相談詳細】</p>
                  <label><p class="subtitle">相談事項の対象</p><p class="subdetail"><input type="radio" name="inout" value="対社内">対社内</label>
                  <label><input type="radio" name="inout" value="対社外">対社外</p></label><br>  
                      <?php if(isset($error['inout']) && $error['inout'] === 'blank'):?>
                          <p class="error">*いずれかを選択してください</p>
                      <?php endif; ?>  
                  <label><p class="subtitle">相談の種類</p>
                  <p class="subdetail">
                      <select class="type" name="type">
                          <option value="業務効率化">業務効率化</option>
                          <option value="新規事業">新規事業</option>
                          <option value="投資/M&A">投資/M&A</option>
                          <option value="人事/労務">人事/労務</option>
                          <option value="企画">企画</option>
                          <option value="CS">CS</option>
                          <option value="営業">営業</option>
                          <option value="その他">その他</option>
                      </select>
                    </p></label><br>
                  <label><p class="subtitle">相談内容(最大400文字）：</p><p class="subdetail"><textArea name="detail" rows="4" cols="40" ><?php echo h($detail);?></textArea></p></label><br>
                      <?php if(isset($error['detail']) && $error['detail'] === 'blank'):?>
                              <p class="error">*相談内容を入力してください</p>
                      <?php endif; ?>
                      <?php if(isset($error['detail']) && $error['detail']==='length'):?>
                          <p class="error">*400文字以下で記載してください</p>
                      <?php endif; ?>
                  <label><p class="subtitle">公開/下書き（非公開）</p><p class="subdetail"><input type="radio" name="oc" value="1">公開</label>
                  <label><input type="radio" name="oc" value="0">下書き（非公開）</p></label><br>  
                      <?php if(isset($error['oc']) && $error['oc'] === 'blank'):?>
                          <p class="error">*いずれかを選択してください</p>
                      <?php endif; ?> 
              <input type="submit" value="確認">
              <a href="../top/main.php">マイページへ</a><br>
              </fieldset>
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