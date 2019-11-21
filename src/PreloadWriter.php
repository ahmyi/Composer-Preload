<?php

namespace Ayesh\ComposerPreload;


class PreloadWriter {
  private $list;
  private $count;
  private $status_check = true;

  public function __construct(PreloadList $list) {
    $this->list = $list;
  }

  private function genCacheLine(string $file_path): string {
    $file_path = addslashes($file_path);
    return "opcache_compile_file('{$file_path}');" . PHP_EOL;
  }

  public function getScript(): string {
    $this->count = 0;
    $list = "";

    foreach ($this->list as $file) {
      /**
       * @var $file \SplFileInfo
       */
      $list .= $this->genCacheLine($file->getRealPath());
      ++$this->count;
    }

    return $list;
  }

  public function write(string $file_path, string $file_template): void {
    $layout = file_get_contents($file_template);
    $output = str_replace("[:opcode:]", $this->getScript(),$layout);
    $status = file_put_contents($file_path, $output);
    if (!$status) {
      throw new \RuntimeException('Error writing the preload file.');
    }
  }

  public function getCount(): int {
    if ($this->count === NULL) {
      throw new \BadMethodCallException('File count is not available until iterated.');
    }
    return $this->count;
  }

  public function setStatusCheck(bool $check): void {
    $this->status_check = $check;
  }
}
