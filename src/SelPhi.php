<?php

namespace Larshansen\Selphi;

class SelPhi {

  private $target_dir = "uploads/";
  private $target_file;
  private $uploadOk = 1;
  private $files = [];

  public function __construct() {

  }

  public function uploadImage($files) {
    var_dump($files);
    $this->files = $files;
    $this->target_file = $this->target_dir . basename($this->files["fileToUpload"]["name"]);
    $imageFileType = strtolower(pathinfo($this->target_file,PATHINFO_EXTENSION));
    $check = getimagesize($this->files["fileToUpload"]["tmp_name"]);
    if($check !== false) {
      echo "File is an image - " . $check["mime"] . ".";
      $this->uploadOk = 1;
    } else {
      echo "File is not an image.";
      $this->uploadOk = 0;
    }
    // Check if file already exists
    $this->file_exits();
    // Check if file is to big
    $this->file_to_big();
    if (move_uploaded_file($this->files["fileToUpload"]["tmp_name"], $this->target_file)) {
      echo "The file ". htmlspecialchars( basename( $this->files["fileToUpload"]["name"])). " has been uploaded.";
    }
  
  }

  private function file_exits() {
    if (file_exists($this->target_file)) {
      $this->uploadOk = 0;
    }
  }

  private function file_to_big() {
    if ($this->files["fileToUpload"]["size"] > 500000) {
      $this->uploadOk = 0;
    }
  }

}
