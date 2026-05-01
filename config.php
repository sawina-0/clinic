<?php
    session_start();
    try{
        $pdo = new PDO("mysql:host=localhost; dbname=clinic","root","");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e){
        die("programm rip" . $e->getMessage());
    }
    function isLogged(){
        return isset($_SESSION['user_id']);
    }
    function isAdmin(){
        return isset($_SESSION['role']) && $_SESSION['role'] == 'Администратор';
    }
    function isDoctor(){
        return isset($_SESSION['role']) && $_SESSION['role'] == 'Доктор';
    }
    function isStuff(){
        return isset($_SESSION['role']) && $_SESSION['role'] == 'Персонал';
    }
    function updateSessionData(){
        global $pdo;
        if(isLogged()){
            $query = $pdo->prepare("SELECT name, role FROM users WHERE user_id = ?");
            $query->execute([$_SESSION['user_id']]);
            $user = $query->fetch(PDO::FETCH_ASSOC);
            if($user){
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
            }
        }
    }
    if(isLogged()){
        updateSessionData();
    }
?>