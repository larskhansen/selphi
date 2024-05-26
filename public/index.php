<?php
use Larshansen\Selphi\SelPhi;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

if (isset($_GET['auth']) && $_GET['auth'] == $_ENV['AUTH']) {
  setcookie("auth", $_GET['auth'], time()+86400, "/", $_SERVER['SERVER_NAME'], false);
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
$name = $_COOKIE["name"] ?? "";
if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $name = isset($_POST['name']) ? $_POST["name"] : $name;
  if ($name !== "") {
    // Set cookie for the name input field and the images.
    setcookie("name", $name, time()+86400, "/", $_SERVER['SERVER_NAME'], false);
    try {
      $selphi->uploadImage($_FILES['image'], $name);
    } catch (Exception $e) {
      echo $e->getMessage();
    }
  }
}
$loader = new FilesystemLoader('../src/template');
$twig = new Environment($loader, ['debug' => true]);
$twig->addExtension(new DebugExtension());
$status = $_SESSION['uploadStatus'] ?? "maks ti filer ad gangen.";

echo $twig->render(
  'index.html', 
  [
    'name' => $name,
    'enablednamefield' => !($name !== ""),
    'uploadStatus' => $status,
  ]
);
$_SESSION['uploadStatus'] = null;
