<?php
include_once __DIR__ . '/core/Database.php';
$method = $_SERVER["REQUEST_METHOD"];
$parsed = parse_url($_SERVER['REQUEST_URI']);
$path = $parsed['path'];

//print_r(getPathWhitId(($_SERVER['HTTP_REFERER'])));
$routes = [
    'GET' => [
        "/" => 'home',
    ],
    'POST' => [
        "/login" => 'loginhandler',
        "/logout" => 'logoutHandler',
        "/changepassword" => 'changepassword',
        "/newuseradd" => 'register'
    ]
];
session_start();

$handlerFunction = $routes[$method][$path] ?? "notFoundHandler";

$handlerFunction();


function home()
{
    if (isLoggedIn()) {
        echo compileTemplate('wrapper.phtml', [
            'content' => compileTemplate('mainMenu.phtml', []),
            'bejelentkezve' => isLoggedIn()
        ]);
    } else {
        echo compileTemplate('wrapper.phtml', [
            'content' => compileTemplate('login.phtml', []),
            'bejelentkezve' => isLoggedIn()
        ]);
        session_unset();
    }
}

function notFoundHandler()
{
    echo compileTemplate("wrapper.phtml", [
        'content' => compileTemplate('notfoundPage.phtml'),
        'bejelentkezve' => isLoggedIn()
    ]);
}

// ha hibás az url akkor erre az oldalra dob át
function isLoggedIn(): bool
{
    if (! isset($_COOKIE[session_name()])) {
        return false;
    }

    if (! isset($_SESSION['userID'])) {
        return false;
    }
    return true;
}

// vizsgálja hogy a böngésző tárol e sütit
function loginhandler()
{
    $err = 'success';
    $pdo = getConnection();
    $statement = $pdo->prepare("SELECT * FROM users WHERE user_name = ? ");
    $statement->execute([
        $_POST["username"]
    ]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if (! $user) {
        $err = 'invalidData';
        $_SESSION['checkPassword'] = $err;
        $_SESSION['username'] = $_POST["username"];
        header('Location: ' . '/');
        return;
    }
    if (! (make($_POST['pw']) === $user["password"]) && $user['active'] === 'Igen') {
        $err = 'invalidData';
        $_SESSION['checkPassword'] = $err;
        $_SESSION['username'] = $_POST["username"];
        header('Location: ' . '/');
        return;
    }

    if ($user['active'] === 'Igen') {
        $_SESSION['checkPassword'] = $err;
        header('Location: ' . '/');
        $_SESSION['userID'] = $user['id'];
        $_SESSION['username'] = $user['user_name'];
        $_SESSION['fullname'] = $user['full_name'];
        $_SESSION['pw'] = $user['password'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['governmentoffice'] = $user['government_office'];
        $_SESSION['accounttype'] = $user['account_type'];
        $_SESSION['firstlogin'] = $user['first_login'];
        $_SESSION['NFA'] = $user['NFA'];
        $_SESSION['GINOP'] = $user['GINOP'];
        $_SESSION['TOP'] = $user['TOP'];
        $_SESSION['active'] = $user['active'];
        return;
    } else {
        $err = 'accountIsNotActive';
        $_SESSION['username'] = $_POST["username"];
        $_SESSION['checkPassword'] = $err;
        header('Location: ' . '/');
        return;
    }
}

function changepassword()
{
    if (! (make($_POST['curr_pass']) === $_SESSION['pw'])) {
        $passwordCheck = make($_POST['curr_pass']);
        echo $passwordCheck;
        return;
    }
    
    $pdo = getConnection();
    $statement = $pdo->prepare("UPDATE users SET password = ? WHERE user_name = ?");
    $statement->execute([
        make($_POST['password']),
        $_SESSION['username']
    ]);
    unset($_SESSION['pw']);
    $_SESSION['pw'] = make($_POST['password']);
    $passwordCheck = 'ok';
    echo $passwordCheck;
    return;
}

function register()
{
    $pdo = getConnection();
    $statement = $pdo->prepare("SELECT * FROM users WHERE user_name = ? ");
    $statement->execute([
        $_POST["userName"]
    ]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $userCheck = 'no';
        echo $userCheck;
        return;
        //header('Location: ' . getPathWhitId($_SERVER['HTTP_REFERER']) . '&info=regisztracioSikertelen');
    } else {
        $statement = $pdo->prepare("INSERT INTO users (user_name, full_name, email, password, government_office, account_type, active, NFA, GINOP, TOP, first_login) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $statement->execute([
            $_POST['userName'],
            $_POST['fullName'],
            $_POST['email'],
            make('abc123'),
            $_POST['list_jaras'],
            $_POST['list_accounttype'],
            'Igen',
            $_POST['NFA'],
            $_POST['GINOP'],
            $_POST['TOP'],
            'Igen'
        ]);
        $userCheck = 'ok';
        echo $userCheck;
        return;
    }
}

function logoutHandler()
{
    $params = session_get_cookie_params();
    setcookie(session_name(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
    // session_unset();
    session_destroy();
    header('Location: ' . '/');
}

// kijelentkezés gombra kattintva eldobja a sütiket és nem lesznek elérhetőek azok az oldalak amik bejelentkezve elérhetőek voltak

// ezt még ki kell tapasztalni!
function getPathWhitId($url)
{
    $parsed = parse_url($url);
    if (! isset($parsed['query'])) {
        return $url;
    }
    $queryParams = [];
    parse_str($parsed['query'], $queryParams);
    return $parsed['path'] . $queryParams['id'];
}

function make($string, $salt = '')
{
    return hash('sha256', $string . $salt);
}

function compileTemplate($filePath, $params = []): string
{
    ob_start();
    require __DIR__ . "/views/" . $filePath;
    return ob_get_clean();
}

?>
