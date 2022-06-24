<?php
// ini_set('display_errors', 1); //エラー表示
session_start();
include("../funcs.php");
// 登録画面の確認フォームの表示制御
if(isset($_SESSION['sgt'])){//セッションの値があれば表示
    $sgt = $_SESSION['sgt'];
    $aid = $_SESSION['tlid'];
    $uid = $_SESSION['id'];
    $sname =$_SESSION['name'];
 
}else{
    redirect("../login/login_top.php");//ダイレクトに打ち込まれたら登録画面に戻す
}


// var_dump($uid);
// var_dump($sgt);
// var_dump($aid);
// var_dump($sname);
//データ接続
$pdo = db_conn();

//３．公募への提案を入れ込む
$sql = "INSERT INTO terry_sgt_table(aid,uid,sname,content,indate)VALUES(:aid,:uid,:sname,:content,sysdate())";
$stmt = $pdo->prepare($sql);//prepare関数に一度データを預ける
$stmt->bindValue(':aid', $aid, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':uid', $uid, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':sname', $sname, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':content', $sgt, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute();
$newsid = $pdo->lastInsertId();

//４．データ登録処理後
if($status==false){
    //*** function化を使う！*****************
    sql_error($stmt);
    
}else{
    
    //正常に入力されたら、表示させる
    $sql = "SELECT title,P.name,content,image,sname,dept,S.indate,S.uid FROM terry_sgt_table S INNER JOIN terry_ask_table A  ON A.id = S.aid LEFT JOIN terry_userprof_table P ON S.uid = P.uid INNER JOIN terry_user_table U ON S.uid = U.id WHERE A.id = :id AND sid =:sid";
    //$sql = "SELECT * FROM terry_sgt_table S INNER JOIN terry_ask_table A  ON A.id = S.aid INNER JOIN terry_userprof_table P ON A.uid = P.uid INNER JOIN terry_user_table U ON S.uid = U.id WHERE A.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':sid', $newsid, PDO::PARAM_INT); 
    $stmt->bindValue(':id', $aid, PDO::PARAM_INT); 
    $status = $stmt->execute();
    $val= $stmt->fetch(PDO::FETCH_ASSOC);

    //今回の案件の詳細取得。
    $sql = "SELECT name FROM terry_ask_table A LEFT JOIN terry_userprof_table P ON A.uid = P.uid WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $aid, PDO::PARAM_INT); 
    $status = $stmt->execute();
    $vala= $stmt->fetch(PDO::FETCH_ASSOC);

    ///slack通知用の変数
    $slack  = $vala['name'];
    $slack .= 'さん  ';
    $slack .= "\n";
    $slack .= '募集中の';
    $slack .= '『';
    $slack .= $val['title'];
    $slack .= '』';
    $slack .= 'に対する【新着の提案です】';
    $slack .= "\n";
    $slack .= '提案者:';
    $slack .= $val['sname'];
    $slack .= "\n";
    $slack .= '所属事業部:';
    $slack .= $val['dept'];
    $slack .= "\n";
    $slack .= '提案内容:';
    $slack .= "\n";
    $slack .= $val['content'];
    

    //slackのurl
    $url = "https://hooks.slack.com/services/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";
    //表示させたいチャンネルと、文章
    $message = [
    "channel" => "95_slack連携テスト",
    "text" => $slack
    ];

    //URLセッションの初期化
    $ch = curl_init();


    $options = [
    CURLOPT_URL => $url,//urlの指定
    // 返り値を文字列で返す
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    // POST
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([//エンコードされた文字列の生成
    'payload' => json_encode($message)
    ])
    ];

    curl_setopt_array($ch, $options);//urlに指定したurlを配列として預ける
    curl_exec($ch);//指定したurlセッションの実行
    curl_close($ch);//urlセッションのclose
    //-----slack関連の処理ここまで

    // //*** function化を使う！*****************
    redirect('../tl/tl_select.php?id='.h($aid).'');
}
    

?>