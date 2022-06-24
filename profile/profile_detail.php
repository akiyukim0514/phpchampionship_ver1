<?php
// ini_set('display_errors', 1); //エラー表示
session_start();
include("../funcs.php");



// プロフィールの編集画面の表示制御
if(isset($_SESSION['id']) && isset($_SESSION['name'])){//セッションの値があれば表示
    $name = $_SESSION['name'];//vardumpで確認する
    $email = $_SESSION['email'];
    $image = $_SESSION['image'];
    $uid = $_SESSION['id'];
 
}else{
    redirect("../login/login_top.php");//ダイレクトに打ち込まれたら登録画面に戻す
}

//profile check画面で修正したいというaタグを踏んで、戻ってきた時の処理（regist経由であり、それはagainで、かつセッションが設定されている場合）
if(isset($_GET['regist']) && $_GET['regist']=== 'again' && isset($_SESSION['form'])){
    $form = $_SESSION['form'];
}else {
$form = [
    // このページで追加する項目たちを入れる
    'dept' =>'',
    'divs' =>'',
    'unit' =>'',
    // 'pdep' =>'',
    // 'pdivs' =>'',
    // 'punit' =>'',
    'intro' =>'',

];


}

$pdo = db_conn();
//３．データ表示(個人データ）
$sql = "SELECT email, image FROM terry_user_table WHERE id=:id";//uidと同じidを探してくる
$stmt = $pdo->prepare($sql);
if(!$stmt) {
    exit('error');//接続エラーの場合
}
$stmt->bindValue(':id', $uid,PDO::PARAM_STR);//入力されたemailと検索を参照。引数は:emailではなく、?。?が二つなら二つ設定
$status = $stmt->execute();

$res="";//個人データの取得
if($status==false) {
    //execute（SQL実行時にエラーがある場合）
    sql_error($stmt);
}else{
    $res= $stmt->fetch(PDO::FETCH_ASSOC);
}
// var_dump($res);ok

$remail =$res['email'];
$rimage =$res['image'];


$error = [];//errorの配列、後で使う。errorが全くの空なら次に進めるように配列で設定

//postかどうかの配列
if($_SERVER['REQUEST_METHOD']==='POST'){

    $form['dept'] = filter_input(INPUT_POST, 'dept', FILTER_SANITIZE_STRING);
    // 事業部のバリデーションチェック
    if($form['dept'] ===''){
        $error['dept']='blank';//から文字の時エラーを呼ぶ
    }

    $form['divs'] = filter_input(INPUT_POST, 'divs', FILTER_SANITIZE_STRING);
    // 部門のバリデーションチェック
    if($form['divs'] ===''){
        $error['divs']='blank';//から文字の時エラーを呼ぶ
    }


    $form['unit'] = filter_input(INPUT_POST, 'unit');//ユニットの制御。これは空欄でもいいかなとか思う
    $form['intro'] = filter_input(INPUT_POST, 'intro');//紹介文。これも空欄でいいかなと思うが200文字以上書くと250で多分varcharの限界255に近くなる気がするので制限加える
    if(strlen($form['intro']) > 200){//そもそも200文字以上書いても読まないだろと思う
        $error['intro']='length';
    }

    //画像のアップロード前確認（エラーがないか、、名前が入っているか）
    if($_FILES['image']['name']!=="" && $_FILES['image']['error']=== 0) {
        $type = mime_content_type($_FILES['image']['tmp_name']);//ファイルタイプの指定。html側でも指定してるのでhtml側を書き換えられない限り問題ないかな？？
        if ($type !== 'image/png' && $type !== 'image/jpeg'){
            $error['image']='type';
        }

    }

    $image = $_FILES['image']['name'];//めんどいので変数に
    $upload ="../img/";//画像入れるサーバー

    if(empty($error)){//全て正常に進んだ場合の処理
        $_SESSION['form'] = $form;//セッションにformの各項目を渡す
        if($image !==''){
            $filename = date('YmdHis').'_'.$image;//重複チェック。同じ名前のファイルで被らないように
            if(!move_uploaded_file($_FILES['image']['tmp_name'],$upload.$filename)){//うまくいかなかった時の処理
                exit('ファイルを保存できませんでした');
            }
            $_SESSION['form']['image'] = $filename;//imageのkeyにファイルの名前を入れる
        } else{
            $_SESSION['form']['image'] = '';//名前がからなら空文字を入れる
        }    
        redirect("profile_check.php");
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
        <p>プロフィール情報更新</p>
        </div>
        <div class="menu_detail">
            <form method="POST" action="" enctype="multipart/form-data" >
            <input type="hidden" name="action" value="submit"/>
                <label><p class="subtitle">名前：</p><p class="subdetail"><?php echo h($name); ?></p></label><br>

                <label><p class="subtitle">email：</p><p class="subdetail"><?php echo h($remail); ?></p></label><br>

                <label><p class="subtitle">イメージ：</p><p class="subdetail"><?php echo '<img id="preview" src="../img/'.h($rimage).'">'; ?></p></label><br>
                <!-- <label><img id="preview"> -->
                    <input id="image" type="file" name="image" accept="image/*" onchange="previewImage(this);" ></label><br>

                <label><p class="subtitle">現在の所属事業部：</p><p class="subdetail"><input type="text" name="dept" value="<?php echo h($form['dept']);?>"></label><br>
                <?php if(isset($error['dept']) && $error['dept'] === 'blank'):?>
                            <p class="error">*未入力です</p>
                        <?php endif; ?>

                <label><p class="subtitle">部門：</p><p class="subdetail"><input type="text" name="divs" value="<?php echo h($form['divs']);?>"></label><br>
                <?php if(isset($error['divs']) && $error['divs'] === 'blank'):?>
                            <p class="error">*未入力です</p>
                        <?php endif; ?>

                <label><p class="subtitle">ユニット：</p><p class="subdetail"><input type="text" name="unit" value="<?php echo h($form['unit']);?>"></label><br>

                <!-- <label>以前の事業部：<input type="text" name="pdep" value="<?php echo h($form['pdep']);?>"></label><br>
                <label>部門：<input type="text" name="pdivs" value="<?php echo h($form['pdivs']);?>"></label><br>
                <label>ユニット：<input type="text" name="pdivs" value="<?php echo h($form['punit']);?>"></label><br> -->

                <label><p class="subtitle">自己紹介(最大200文字）：</p><textArea name="intro" rows="4" cols="40" ><?php echo h($form['intro']);?></textArea></label><br>
                <?php if(isset($error['intro']) && $error['intro']==='length'):?>
                            <p class="error">*200文字以下で記述してください</p>
                        <?php endif; ?>
                <input type="submit" value="確認">
                <a href="../top/main.php">マイページへ</a><br>
            </form>
        </div>
    </div>
</div>
<!-- Main[End] -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
function previewImage(obj)
{
	var fileReader = new FileReader();
	fileReader.onload = (function() {
		document.getElementById('preview').src = fileReader.result;
	});
	fileReader.readAsDataURL(obj.files[0]);
}
</script>

</body>
</html>
