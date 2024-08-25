<?php

namespace Larshansen\Selphi;

class SelPhi {

  private const ALLOWED_FILES = [
    'image/png' => 'png',
    'image/jpeg' => 'jpg'
  ];
  
  private const MESSAGES = [
    UPLOAD_ERR_OK => 'Filen er uploaded',
    UPLOAD_ERR_INI_SIZE => 'Filen er for stor',
    UPLOAD_ERR_FORM_SIZE => 'Filen er for stor',
    UPLOAD_ERR_PARTIAL => 'Filen er kun delvist uploaded',
    UPLOAD_ERR_NO_FILE => 'Ingen fil blev uploaded',
    UPLOAD_ERR_NO_TMP_DIR => 'Fejl i tmp opsætning af server',
    UPLOAD_ERR_CANT_WRITE => 'Filen blev ikke gemt på disk',
    UPLOAD_ERR_EXTENSION => 'Fil typen må ikke uploades på serveren',
  ];

  private $errors;
  private const MAX_SIZE = 6 * 1024 * 1024; //  5MB
  
  private const UPLOAD_DIR = '/upload';

  public function __construct() {
  }

  public function uploadImage(array $files, string $name): void {
    
    $has_file = isset($files);
    $file_count = count($files['name']);
    if (!$has_file) {
      throw new \RuntimeException('Invalid file upload operation');
    }

    for ($i = 0; $i < $file_count; $i++) {
      // get the uploaded file info
      $status = $files['error'][$i];
      $filename = $files['name'][$i];
      $tmp = $files['tmp_name'][$i];
  
      // an error occurs
      if ($status !== UPLOAD_ERR_OK) {
          throw new \RuntimeException(self::MESSAGES[$status]);
      }
      // validate the file size
      $filesize = filesize($tmp);
  
      if ($filesize > self::MAX_SIZE) {
          // construct an error message
          $message = sprintf("The file %s is %s which is greater than the allowed size %s",
              $filename,
              $this->format_filesize($filesize),
              $this->format_filesize(self::MAX_SIZE));

        throw new \RuntimeException(self::MESSAGES[$status]);
      }
  
      // validate the file type
      if (!array_key_exists($this->get_mime_type($tmp), self::ALLOWED_FILES)) {
        throw new \RuntimeException("The file $filename is not allowed to upload");
      }
    }


    // move the files
    for($i = 0; $i < $file_count; $i++) {
      $filename = $files['name'][$i];
      $tmp = $files['tmp_name'][$i];
      $mime_type = $this->get_mime_type($tmp);
  
      // set the filename as the basename + extension
      $uploadedFile = pathinfo($filename, PATHINFO_FILENAME) . '.' . self::ALLOWED_FILES[$mime_type];
      $folderStructur = getcwd() . self::UPLOAD_DIR . '/' . str_replace(" ", "", strtolower($name));

      if (!is_dir($folderStructur)) {
        if (!mkdir($folderStructur) && !is_dir($folderStructur)) {
          throw new \RuntimeException(sprintf('Directory "%s" was not created', $folderStructur));
        }
      }

      // new filepath
      
      $filepath = $folderStructur . '/' . $uploadedFile;
  
      // move the file to the upload dir
      $success = move_uploaded_file($tmp, $filepath);
      if(!$success) {
        $this->errors[$filename] = "The file $filename was failed to move.";
      }
    }
    if (is_null($this->errors)) {
      $_SESSION['uploadStatus'] = ($file_count > 1) ? "Filerne er uploadet" : "Filen er uploadet";
    } else {
      $_SESSION['uploadStatus'] = $this->errors;
    }
    header("Location: /", true, 303);
    exit;
  }

  private function get_mime_type(string $filename) {
    $info = finfo_open(FILEINFO_MIME_TYPE);
    if (!$info) {
      return false;
    }

    $mime_type = finfo_file($info, $filename);
    finfo_close($info);
    return $mime_type;
  }

  /**
   * Return a human-readable file size
   *
   * @param int $bytes
   * @param int $decimals
   * @return string
   */
  private function format_filesize(int $bytes, int $decimals = 2): string {
    $units = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$decimals}f", $bytes / (1024 ** $factor)) . $units[(int)$factor];
  }

  public function getFolders(): array {
    $returnArray = [];
    $files = scandir(getcwd() . self::UPLOAD_DIR);
    if (isset($_COOKIE['name'])) {
      $ownFolder = getcwd() . self::UPLOAD_DIR . "/" .  str_replace(" ", "", strtolower($_COOKIE['name']));
      $test = self::UPLOAD_DIR . "/" .  str_replace(" ", "", strtolower($_COOKIE['name']));
      if (is_dir($ownFolder)) {
        $files = scandir($ownFolder);
        foreach ($files as $file) {
          $filePath = substr($test . '/' . $file, 1);
          if (is_file($filePath)) {
            $returnArray[] = $filePath;
          }
        }
      } else {
        mkdir($ownFolder);
      }
    }
    return $returnArray;
  }

  public function getUploadDir() {
    return self::UPLOAD_DIR;
  }

}
