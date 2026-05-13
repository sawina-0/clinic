<?php
    session_start();
    require_once '../config.php';

    if(isLogged()){
        header('Location: ../index.php');
        exit;
    }
    $error = '';
    $succes = '';
    if($_SERVER['REQUEST_METHOD']=== 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
        $surname = trim($_POST['surname']);
        $name = trim($_POST['name']);
        $secName = trim($_POST['secname']);
        $phone = trim($_POST['phone']);
        $pass = trim($_POST['password']);
        $confPass = trim($_POST['confPassword']);

        $response = ['success' => false, 'message' => ''];

        if(empty($surname) || empty($name) || empty($phone) || empty($pass) || empty($confPass)){
            $response['message'] = 'Заполните все поля';
        }
        elseif($pass != $confPass){
            $response['message'] = 'Пароли должны совпадать';
        }
        elseif(strlen($pass) < 6){
            $response['message'] = 'Пароль должен быть минимум 6 символов';
        }
        elseif(!preg_match('/[А-ЯA-Z]/', $pass)){
            $response['message'] = 'Пароль должен содержать хотя бы одну заглавную букву';
        }
        elseif(!preg_match('/[0-9]/', $pass)){
            $response['message'] = 'Пароль должен содержать хотя бы одну цифру';
        }
        else{
            $query = $pdo->prepare("SELECT user_id FROM users WHERE phone_num = ?");
            $query -> execute([$phone]);

            if($query->rowCount() > 0){
                $response['message'] = 'Аккаунт с этим номером телефона уже существует';
            }
            else{
                $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
                $query = $pdo->prepare("INSERT INTO users (phone_num, name, surname, password, sec_name, role) VALUES (?, ?, ?, ?, ?, 'Пользователь')");
                if($query->execute([$phone, $name, $surname, $hashed_password, $secName])){
                    $response['success'] = true;
                    $response['message'] = 'Регистрация успешна!';
                    $response['redirect'] = './auth.php';
                }
                else{
                    $response['message'] = "что-то пошло не так";
                }
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
    <title>Клиника Кедр - Регистрация</title>
</head>
<body>
    <main class="action">
        <a class="logoAbs" href="../index.php#hero">
            <img src="../img/svg/logoGreen.svg" alt="логотип кедр">
            Клиника “Кедр”
        </a>
        <form class="block" method="post">
            <h1>Регистрация</h1>
            <div class="inputs">
                <div class="field">
                    <label for="regSurname">фамилия:</label>
                    <input type="text" name="surname" id="regSurname" placeholder="Иванов" required>
                </div>
                <div class="field">
                    <label for="regName">имя:</label>
                    <input type="text" name="name" id="regName" placeholder="Иван" required>
                </div>
                <div class="field">
                    <label for="regSecname">отчество:</label>
                    <input type="text" name="secname" id="regSecname" placeholder="Иванович">
                </div>
                <div class="field">
                    <label for="regPhone">номер телефона:</label>
                    <input type="tel" name="phone" id="regPhone" placeholder="8(800)555-35-35" required>
                </div>
                <div class="field">
                    <label for="regPass">пароль:</label>
                    <input type="password" name="password" id="regPass" required placeholder=""> 
                    <p>пароль должен включать цифру, заглавную букву и минимум 6 символов</p>
                </div>
                <div class="field">
                    <label for="regPassCon">пароль повторно:</label>
                    <input type="password" name="confPassword" id="regPassCon" required>
                </div>
            </div>
            <div class="links">
                <input type="submit" value="регистрация" class="commonBtn">
                <a href="./auth.php">вход</a>
            </div>
            
        </form>
    </main>
</body>
</html>