<?php
use Larshansen\Selphi\SelPhi;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

$auth = getenv('AUTH', true) ?: getenv('AUTH');
if (isset($_GET['auth']) && $_GET['auth'] == $auth) {
  setcookie("auth", $_GET['auth'], time()+(86400*5), "/", $_SERVER['SERVER_NAME'], false);
  header("Location: /", true, 303);
  exit;
}
if (!isset($_COOKIE['auth'])) {
  echo 'Mangler en godkendelse';
  die();
}

session_start();

require '../vendor/autoload.php';

$selphi = new SelPhi();

$folders = $selphi->getFolders();
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    
  $name = isset($_POST['name']) ? $_POST["name"] : (isset($_COOKIE["name"]) ? $_COOKIE["name"] : "");
  
  $folder = getcwd() . $selphi->getUploadDir() . "/" .  str_replace(" ", "", strtolower($name));
  if (is_dir($folder) && !isset($_COOKIE["name"])) {
    $_SESSION['uploadStatus'][] = "Dette navn er taget, brug et andet";
  } else {
    if ($name !== "") {
      // Set cookie for the name input field and the images.
      setcookie("name", $name, time()+86400*5, "/", $_SERVER['SERVER_NAME'], false);
      try {
        $selphi->uploadImage($_FILES['image'], $name);
      } catch (Exception $e) {
        echo $e->getMessage();
      }
    }
  }
} else if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["image"]) && isset($_GET["action"]) && $_GET["action"] === "delete" && isset($_COOKIE["name"])) {
  $getArray = explode("/", $_GET["image"]);
  if (isset($getArray[1]) && str_replace(" ", "", strtolower($_COOKIE["name"])) === $getArray[1]) {
    unlink($_GET["image"]);
    header("Location:/");
  }
}
$loader = new FilesystemLoader('../src/template');
$twig = new Environment($loader, ['debug' => true]);
$twig->addExtension(new DebugExtension());
$statusText = $_SESSION['uploadStatus'] ?? "maks ti filer ad gangen.";
$status = ($statusText !== "maks ti filer ad gangen.") ? "text-danger" : "text-success";
echo $twig->render(
  'index.html', 
  [
    'enablednamefield' => !isset($_COOKIE["name"]),
    'uploadStatus' => $statusText,
    'status' => $status,
    'folders' => $folders,
  ]
);
$_SESSION['uploadStatus'] = null;
