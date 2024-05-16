<?php
use Larshansen\Selphi\Flash;
use Larshansen\Selphi\SelPhi;

session_start();

require '../vendor/autoload.php';

$flash = new Flash();

$selphi = new SelPhi();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
  // Set cookie for the name input field and the images.
  setcookie("name", $_POST["name"], time()+86400, "/", "rshansen.dk", 1);
  $selphi->uploadImage($_FILES['image'], $_POST["name"]);
}
$name = $_COOKIE['name'] ?? "";
$loader = new \Twig\Loader\FilesystemLoader('../src/template');
$twig = new \Twig\Environment($loader, ['debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

echo $twig->render(
  'index.html', 
  [
    'name' => $name, 
    'messages' => $flash->flash()
  ]
);
