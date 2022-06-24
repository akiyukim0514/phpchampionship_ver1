<?php
ini_set('display_errors', 1); //エラーチェック用
session_start();
include("../funcs.php");
if(isset($_SESSION['id']) && isset($_SESSION['name'])){//idと名前が正しく呼ばれているか
    $id = $_SESSION['id'];
    $name = $_SESSION['name'];

} else {
   redirect("login_top.php");//正しくないと落とす
}

//var_dump($id);ok
$pdo = db_conn();
//sinupcheck画面で修正したいというaタグを踏んで、戻ってきた時の処理（regist経由であり、それはagainで、かつセッションが設定されている場合）
if(isset($_GET['regist']) && $_GET['regist']=== 'again' && isset($_SESSION['form'])){
    $form = $_SESSION['form'];
}else {//それ以外は初期化して最初の処理
// 変数の初期化（配列）
$form = [
    // 入力内容の受け取りの箱
    'title' =>'',
    'point' =>'',
    'inout' =>'',
    'type' =>'',
    'detail' =>'',
];
}
// var_dump($form);
$error = [];//errorの配列、後で使う。errorが全くの空なら次に進めるように配列で設定

// サーバー側のリクエストがpostなのかgetなのかを判断する。xss対策にもなるらしい
if($_SERVER['REQUEST_METHOD']==='POST'){
    $form['title'] = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    // titleのバリデーションチェック
    if($form['title'] ===''){
        $error['title']='blank';//から文字の時エラーを呼ぶ
    }

    //pointのエラーチェック
    $form['point'] = filter_input(INPUT_POST, 'point', FILTER_SANITIZE_STRING);
    // pointの空と文字数制限
    if($form['point'] ===''){
        $error['point']='blank';
    }else if(strlen($form['point']) > 100){ // 100文字以上を制御strlenで文字数を示す
        $error['point']='length';
    }

    // inoutのバリデーションチェック
    $form['inout'] = filter_input(INPUT_POST, 'inout',FILTER_SANITIZE_STRING);
    if(is_null($form['inout'])){
        $error['inout']='blank';
    }

    // typeの値を受け取る
    $form['type'] = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $type = $form['type'];

    // detailの値を受け取る
    $form['detail'] = filter_input(INPUT_POST, 'detail',FILTER_SANITIZE_STRING);
    if($form['detail']===''){
        $error['detail']='blank';
    } else if(strlen($form['detail']) > 400){ // 100文字以上を制御strlenで文字数を示す
        $error['detail']='length';
    }
    // var_dump($form);ok


    if(empty($error)){//全て正常に進んだ場合の処理
        $_SESSION['form'] = $form;//セッションにformの各項目を渡す
        redirect("ask_check.php");
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
    <title>登録情報確認フォーム</title>
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
                    <label><p class="subtitle">タイトル:</p><p class="subdetail"><input type="text" name="title" value="<?php echo h($form['title']);?>"></p></label><br>
                        <?php if(isset($error['title']) && $error['title'] === 'blank'):?>
                            <p class="error">*タイトルを入力してください</p>
                        <?php endif; ?>
                    <label><p class="subtitle">相談したい内容の要点（100文字以内）:</p><p class="subdetail"><input type="text" name="point" value="<?php echo h($form['point']);?>"></p></label><br>
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
                    <label><p class="subtitle">相談内容(最大400文字）：</p><p class="subdetail"><textArea name="detail" rows="4" cols="40" ><?php echo h($form['detail']);?></textArea></p></label><br>
                        <?php if(isset($error['detail']) && $error['detail'] === 'blank'):?>
                                <p class="error">*相談内容を入力してください</p>
                        <?php endif; ?>
                        <?php if(isset($error['detail']) && $error['detail']==='length'):?>
                            <p class="error">*400文字以下で記載してください</p>
                        <?php endif; ?> 
                <input type="submit" value="確認">
                <a href="../top/main.php">マイページへ</a><br>
            </form>
        </div>
<!-- Main[End] -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

</script>

</body>
</html>