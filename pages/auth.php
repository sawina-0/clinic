<?php
    session_start();
    require_once '../config.php';
    if(isLogged()){
        header('Location: ../index.php');
        exit;
    }
    $error='';
    if($_SERVER['REQUEST_METHOD']=== 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
        $phone = trim($_POST['phone']);
        $password = trim($_POST['passEnt']);

        $response = ['success' => false, 'message' => ''];

        if(empty($phone) || empty($password)){
            $response['message'] = 'Заполните все поля';
        }
        else{
            $query = $pdo->prepare("SELECT * FROM users WHERE phone_num = ?");
            $query -> execute([$phone]);
            $user = $query->fetch(PDO::FETCH_ASSOC);

            // $response['debug_phone'] = $phone;
            // $response['debug_user_found'] = $user ? 'да' : 'нет';

            if($user && password_verify($password, $user['password'])){
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];


                $response['success'] = true;
                $response['message'] = 'Вход выполнен';
                $response['redirect'] = '../index.php';
                // header('Location: ../index.php'); //SENT TO A PAGE AFTER ENTER
                // exit;
            }
            else{
                $response['message'] = "неверный номер телефона или пароль";
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <script src="../js/maskAndError.js" defer></script>
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <title>Клиника Кедр - Вход</title>
</head>
<body>
    <main class="action">
        <a class="logoAbs" href="../index.php#hero">
            <img src="../img/svg/logoGreen.svg" alt="логотип кедр">
            Клиника “Кедр”
        </a>
        <form class="block" method="post">
            <h1>Вход в аккаунт</h1>
            <div class="inputs">
                <div class="field">
                    <label for="phoneEnt">номер телефона:</label>
                    <input type="tel" name="phone" id="phoneEnt" placeholder="8(800)555-35-35" required>
                </div>
                <div class="field">
                    <label for="passEnt">пароль:</label>
                    <input type="password" name="passEnt" id="passEnt" required>
                </div>
            </div>
            <div class="links">
                <input type="submit" value="войти" class="commonBtn">
                <a href="./reg.php">регистрация</a>
            </div>
            <p>если забыли пароль обратитесь в регестратуру или по телефону 8(904)322-12-12</p>
            
        </form>
    </main>
</body>
</html>