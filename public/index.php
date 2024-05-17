<?php
use Larshansen\Selphi\Flash;
use Larshansen\Selphi\SelPhi;

session_start();

require '../vendor/autoload.php';

$flash = new Flash();

$selphi = new SelPhi();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $cookie_name = isset($_POST['name']) ? $_POST["name"] : (isset($_COOKIE["name"]) ? $_COOKIE["name"] : "");
  if ($cookie_name !== "") {
    // Set cookie for the name input field and the images.
    setcookie("name", $cookie_name, time()+86400, "/", "selphi.lndo.site", 1);
    try {
      $selphi->uploadImage($_FILES['image'], $cookie_name);
    } catch (Exception $e) {
      echo $e->getMessage();
    }
  }
}
$name = $_COOKIE['name'] ?? "";
$loader = new \Twig\Loader\FilesystemLoader('../src/template');
$twig = new \Twig\Environment($loader, ['debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

echo $twig->render(
  'index.html', 
  [
    'name' => $name,
    'enablednamefield' => !($name !== ""),
  ]
);
