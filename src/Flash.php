<?php

namespace Larshansen\Selphi;

class Flash {
  
  const FLASH = 'FLASH_MESSAGES';
  const FLASH_ERROR = 'error';
  const FLASH_WARNING = 'warning';
  const FLASH_INFO = 'info';
  const FLASH_SUCCESS = 'success';

  /**
  * Create a flash message 
  * 
  * @param string $name
  * @param string $message
  * @param string $type
  * @return void
  */
  private function create_flash_message(string $name, string $message, string $type): void {
    // remove existing message with the name
    if (isset($_SESSION[Flash::FLASH][$name])) {
        unset($_SESSION[Flash::FLASH][$name]);
    }
    // add the message to the session
    $_SESSION[Flash::FLASH][$name] = ['message' => $message, 'type' => $type];
  }

  /**
  * Format a flash message
  *
  * @param array $flash_message
  * @return string
  */
  private function format_flash_message(array $flash_message): string {
    return sprintf('<div class="alert alert-%s">%s</div>',
      $flash_message['type'],
      $flash_message['message']
    );
  }

  /**
  * Display a flash message
  *
  * @param string $name
  * @return void
  */
  private function display_flash_message(string $name): string {
    if (!isset($_SESSION[Flash::FLASH][$name])) {
      return "";
    }

    // get message from the session
    $flash_message = $_SESSION[Flash::FLASH][$name];

    // delete the flash message
    unset($_SESSION[Flash::FLASH][$name]);

    // display the flash message
    return $this->format_flash_message($flash_message);
  }
  
  /**
  * Display all flash messages
  *
  * @return void
  */
  private function display_all_flash_messages(): array {
    if (!isset($_SESSION[Flash::FLASH])) {
      return [];
    }

    // get flash messages
    $flash_messages = $_SESSION[Flash::FLASH];

    // remove all the flash messages
    unset($_SESSION[Flash::FLASH]);
    $return = [];
    // show all flash messages
    foreach ($flash_messages as $flash_message) {
      $return[] = $this->format_flash_message($flash_message);
    }
    return $return;
  }

  /**
  * Flash a message
  *
  * @param string $name
  * @param string $message
  * @param string $type (error, warning, info, success)
  * @return void
  */
  public function flash(string $name = '', string $message = '', string $type = ''): void {
    if ($name !== '' && $message !== '' && $type !== '') {
      $this->create_flash_message($name, $message, $type);
    } elseif ($name !== '' && $message === '' && $type === '') {
        $this->display_flash_message($name);
    } elseif ($name === '' && $message === '' && $type === '') {
        $this->display_all_flash_messages();
    }
  }

}
