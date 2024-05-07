<?php
use Larshansen\Selphi\SelPhi;

require 'vendor/autoload.php';

$selphi = new SelPhi();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
  // Set cookie for the name input field and the images.
  setcookie("name", $_POST["name"], time()+86400, "/", "rshansen.dk", 1);
  var_dump($_FILES);
  $selphi->uploadImage($_FILES);
  var_dump($_POST);
}
$name = $_COOKIE['name'] ?? "";
$loader = new \Twig\Loader\FilesystemLoader('./src/template');
$twig = new \Twig\Environment($loader);

echo $twig->render('index.html', ['name' => $name]);
