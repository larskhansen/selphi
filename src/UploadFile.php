<?php

namespace Larshansen\Selphi;

class UploadFile {
  
  private $name;
  private $fullPath;
  private $size;
  private $tmpName;
  private $type;
  private $error;

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

private const MAX_SIZE  = 5 * 1024 * 1024; //  5MB

  public function __construct(array $files) {
    $this->name = $files['name'];
    $this->fullPath = $files['full_path'];
    $this->tmpName = $files['tmp_name'];
    $this->type = $files['type'];
    $this->error = $files['error'];
    $this->size = $files['size'];
  }

  public function getName() {
    return $this->name;
  }

  public function getFullPath() {
    return $this->fullPath;
  }

  public function getSize() {
    return $this->size;
  }

  public function getTmpName() {
    return $this->tmpName;
  }

  public function getType() {
    return $this->type;
  }

  public function getError() {
    return UploadFile::MESSAGES[$this->error];
  }
}