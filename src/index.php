<?php
include_once __DIR__ . '/core/Database.php';
header('Content-Type: text/html; charset=iso-8859-2');
$method = $_SERVER["REQUEST_METHOD"];
$parsed = parse_url($_SERVER['REQUEST_URI']);
$path = $parsed['path'];

$routes = [
    'GET' => [
        "/" => 'menu',
        "/mainmenu" => 'menu'
    ],
    'POST' => [
        "/mainmenu" => 'loginhandler',
        "/logout" => 'logoutHandler'
    ]
];
session_start();

$handlerFunction = $routes[$method][$path] ?? "notFoundHandler";

$handlerFunction();

function loginForm()
{
    echo compileTemplate('wrapper.phtml', [
        'content' => compileTemplate('login.phtml', []),
        'bejelentkezve' => false
    ]);
}

function menu()
{
    if (isLoggedIn()) {
        echo compileTemplate('wrapper.phtml', [
            'content' => compileTemplate('mainMenu.phtml', []),
            'bejelentkezve' => true,
            'tipus' => $_SESSION['accounttype']
        ]);
    } else {
        loginForm();
    }
}

function compileTemplate($filePath, $params = []): string
{
    ob_start();
    require __DIR__ . "/views/" . $filePath;
    return ob_get_clean();
}

function notFoundHandler()
{
    echo "Oldal nem található";
}

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
    $pdo = getConnection();
    $statement = $pdo->prepare("SELECT * FROM users WHERE user_name = ? ");
    $statement->execute([
        $_POST["username"]
    ]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if (! $user) {
        header('Location: ' . 'mainmenu' . '&info=invalidDate');
        return;
    }
    if (! (make($_POST['pw']) === $user["password"])) {
        header('Location: ' . 'mainmenu' . '&info=invalidDate');
        return;
    }
    $_SESSION['userID'] = $user['id'];
    $_SESSION['username'] = $user['user_name'];
    $_SESSION['fullname'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['governmentoffice'] = $user['government_office'];
    $_SESSION['accounttype'] = $user['account_type'];
    $_SESSION['firstlogin'] = $user['first_login'];
    $_SESSION['NFA'] = $user['NFA'];
    $_SESSION['GINOP'] = $user['GINOP'];
    $_SESSION['TOP'] = $user['TOP'];
    $_SESSION['active'] = $user['active'];
    if ($_SESSION['active'] == 'Igen') {
        menu();
    } else {
        header('Location: ' . 'mainmenu' . '&info=accountIsNotActive');
    }
    
}

// ezt még ki kell tapasztalni!
function getPathWhitId($url)
{
    $parsed = parse_url($url);
    if (! isset($parsed['query'])) {
        return $url;
    }
    $queryParams = [];
    parse_str($parsed['query'], $queryParams);
    return $parsed['path'] . "?id=" . $queryParams['id'];
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
function make($string, $salt = '')
{
    return hash('sha256', $string . $salt);
}

?>
