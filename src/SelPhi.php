<?php

namespace Larshansen\Selphi;

class SelPhi {

  private const ALLOWED_FILES = [
    'image/png' => 'png',
    'image/jpeg' => 'jpg'
  ];
  
  private $errors;
  private const MAX_SIZE = 5 * 1024 * 1024; //  5MB
  
  private const UPLOAD_DIR = __DIR__ . '/../public/upload';

  public function __construct() {
  }

  public function uploadImage(array $files, string $name): void {
    
    $has_file = isset($files);
    $file_count = count($files['name']);
    if (!$has_file) {
      $this->redirect_with_message('Invalid file upload operation', Flash::FLASH_ERROR);
    }

    for ($i = 0; $i < $file_count; $i++) {
      // get the uploaded file info
      $status = $files['error'][$i];
      $filename = $files['name'][$i];
      $tmp = $files['tmp_name'][$i];
  
      // an error occurs
      if ($status !== UPLOAD_ERR_OK) {
          $this->errors[$filename] = UploadFile::MESSAGES[$status];
          continue;
      }
      // validate the file size
      $filesize = filesize($tmp);
  
      if ($filesize > SELF::MAX_SIZE) {
          // construct an error message
          $message = sprintf("The file %s is %s which is greater than the allowed size %s",
              $filename,
              $this->format_filesize($filesize),
              $this->format_filesize(SELF::MAX_SIZE));
  
          $this->errors[$filesize] = $message;
          continue;
      }
  
      // validate the file type
      if (!in_array($this->get_mime_type($tmp), array_keys(SELF::ALLOWED_FILES))) {
          $this->errors[$filename] = "The file $filename is allowed to upload";
      }
  }
  
  if ($this->errors) {
      $this->redirect_with_message($this->format_messages('The following errors occurred:',$errors), FLASH::FLASH_ERROR);
  }

  // move the files
  for($i = 0; $i < $file_count; $i++) {
      $filename = $files['name'][$i];
      $tmp = $files['tmp_name'][$i];
      $mime_type = $this->get_mime_type($tmp);
  
      // set the filename as the basename + extension
      $uploaded_file = pathinfo($filename, PATHINFO_FILENAME) . '.' . SELF::ALLOWED_FILES[$mime_type];
      // new filepath
      $filepath = SELF::UPLOAD_DIR . '/' . str_replace(" ", "", strtolower($name)) . '/' . $uploaded_file;
  
      // move the file to the upload dir
      $success = move_uploaded_file($tmp, $filepath);
      if(!$success) {
          $this->errors[$filename] = "The file $filename was failed to move.";
      }
  }
  
  $this->errors ?
      $this->redirect_with_message($this->format_messages('The following errors occurred:',$this->errors), FLASH::FLASH_ERROR) :
      $this->redirect_with_message('All the files were uploaded successfully.', FLASH::FLASH_SUCCESS);
  }

  /**
   * Redirect with a human readable message for Flash.
   * 
   * @param string message
   * @param string type
   * @param string name
   * @param string location
   * 
   */
  private function redirect_with_message(string $message, string $type=Flash::FLASH_ERROR, string $name='upload', string $location='index.php'): void {
    $flash = new Flash();
    $flash->flash($name, $message, $type);
    header("Location: $location", true, 303);
    exit;
  }

  private function format_messages(string $title, array $messages): string {
      $message = "<p>$title</p>";
      $message .= '<ul>';
      foreach ($messages as $key => $value) {
          $message .= "<li>$value</li>";
      }
      $message .= '<ul>';
  
      return $message;
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
  function format_filesize(int $bytes, int $decimals = 2): string {
    $units = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $units[(int)$factor];
  }
}
