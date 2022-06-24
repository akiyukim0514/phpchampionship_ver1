<?php
// ini_set('display_errors', 1); //エラー表示
session_start();
include("../funcs.php");
// 登録画面の確認フォームの表示制御
if(isset($_SESSION['id']) && isset($_SESSION['name']) && isset($_SESSION['form'])){//セッションの値があれば表示
    $form = $_SESSION['form'];
    $name = $_SESSION['name'];//vardumpで確認する
    $email = $_SESSION['email'];
    $uid = $_SESSION['id'];
    $image = $_SESSION['image'];
    $dept =$form['dept'];
    $divs =$form['divs'];
    $unit =$form['unit'];
    $intro =$form['intro'];
    // var_dump($image);ok
    // var_dump($form['image']);ok
    // var_dump($uid);

}else{
    redirect("../login/login_top.php");//ダイレクトに打ち込まれたら登録画面に戻す
}

$error = [];//後々のエラー対応に使う

//データ接続
$pdo = db_conn();

if($_SERVER['REQUEST_METHOD']==='POST'){
    if($form['image']!==''){
        $sql = "UPDATE terry_user_table SET image=:image WHERE id=:id";//イメージのアップデート
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id',$uid, PDO::PARAM_STR); 
        $stmt->bindValue(':image',$form['image'], PDO::PARAM_STR); 
        $status = $stmt->execute();
        

        if($status==false){

            sql_error($stmt);
        }
    }
}


// -----ここからアップデートとインサートの処理（長い）
// これでできるらしい https://qiita.com/Yuki_Oshima/items/2a73cf70ccbf67bd5215
// サーバー側のリクエストがpostなのかgetなのかを判断する
if($_SERVER['REQUEST_METHOD']==='POST'){
    //３．データ登録SQL作成
    $sql = "SELECT * FROM terry_userprof_table WHERE uid=:uid";//idがuserprofの中にあるのかどうかを探す。あればupdate,なければinsertしたい
    $stmt = $pdo->prepare($sql);//prepare関数に一度データを預ける
    $stmt->bindValue(':uid', $uid,PDO::PARAM_STR);//入力されたuidを参照させる
    $status = $stmt->execute();
    if($status==false){
        //*** function化を使う！*****************
        sql_error($stmt);
    }
    $count = count($stmt->fetchAll(PDO::FETCH_ASSOC)); //同じuidをカウント
    // var_dump($count);確認用
    if($count > 0){//存在した場合のエラー措置
        $error['uid'] = 'exist';
    }

    if(isset($error['uid']) && $error['uid'] === 'exist'){//error['uid']がsetされていて、存在するならば
        $sql = "UPDATE terry_userprof_table SET uid=:uid,name=:name,dept=:dept,divs=:divs,unit=:unit,intro=:intro WHERE uid=:uid";//update処理
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);  
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);  
        $stmt->bindValue(':dept', $dept, PDO::PARAM_STR);  
        $stmt->bindValue(':divs', $divs, PDO::PARAM_STR);  
        $stmt->bindValue(':unit', $unit, PDO::PARAM_STR);  
        $stmt->bindValue(':intro', $intro, PDO::PARAM_STR);  
        $status = $stmt->execute();
        //データ登録処理後


        if($status==false){

            sql_error($stmt);
        }else{
            redirect("profile.php");
        }        
    }else{//error['uid']がセットされていない、つまり同じidに紐づくデータがDB内でない時
    
        $sql = "INSERT INTO terry_userprof_table(uid,name,dept,divs,unit,intro)VALUES(:uid,:name,:dept,:divs,:unit,:intro);";//insert処理
        $stmt = $pdo->prepare($sql);//prepare関数に一度データを預ける
        $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);  
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);  
        $stmt->bindValue(':dept', $dept, PDO::PARAM_STR);  
        $stmt->bindValue(':divs', $divs, PDO::PARAM_STR);  
        $stmt->bindValue(':unit', $unit, PDO::PARAM_STR);  
        $stmt->bindValue(':intro', $intro, PDO::PARAM_STR); 
        $status = $stmt->execute();

            //４．データ登録処理後
        if($status==false){
            sql_error($stmt);
        }else{
            redirect("profile.php");
        }
    }
}
// -------------------------------------

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
    <title>登録内容の確認</title>
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
        <p>プロフィール登録内容確認</p>
        </div>
        <div class="menu_detail">
            <form method="POST" action="" enctype="multipart/form-data" >
            <input type="hidden" name="action" value="submit"/>
                <label><p class="subtitle">名前：</p><p class="subdetail"><?php echo h($name); ?></p></label><br>
                <label><p class="subtitle">email：</p><p class="subdetail"><?php echo h($email); ?></p></label><br>

                <label><p class="subtitle">イメージ：</p><p class="subdetail">
                    <!-- 新しくイメージが追加されたら表示、されなかったら元のデータを表示させる -->
                <?php if($form['image']!==''): ?><!-- 前のページのform imageの値が空じゃないなら -->
                    <p><?php echo '<img src="../img/'.h($form['image']).'">'; ?></p>
                <?php else: ?> <!-- 前のページのform imageが空なら -->
                <p><?php echo '<img src="../img/'.h($image).'">'; ?></p></label><br>
                <?php endif;?>

                <label><p class="subtitle">現在の所属事業部：
                </p><p class="subdetail"><?php echo h($form['dept']); ?></p></label><br>
                <label><p class="subtitle">部門：</p><p class="subdetail"><?php echo h($form['divs']); ?></p></label><br>
                <label><p class="subtitle">unit：</p><p class="subdetail"><?php echo h($form['unit']); ?></p></label><br>
                <label><p class="subtitle">自己紹介：</p><p class="subdetail"><?php echo h($form['intro']); ?></p></label><br>
                <label><p class="subtitle">この内容で登録しますか？</p><input type="submit" value="送信"></label><br>
                <a href="profile_detail.php?regist=again">修正する</a>
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