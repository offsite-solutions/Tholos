<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use Exception;
  use RuntimeException;

  /**
   * Class TFileProcessor
   * @package Tholos
   */
  class TFileProcessor extends TDataProvider {
    
    /**
     * @param TComponent|null $sender
     * @param string $nativeSQL
     * @return string|void
     * @throws RuntimeException
     * @throws Exception
     */
    protected function open(?TComponent $sender, $nativeSQL = ''): void {
      
      if (!Tholos::$app->checkRole($this)) {
        return;
      }
      
      Tholos::$app->trace('BEGIN', $this);
      
      try {
        $storeFolder = $this->getProperty('LocalFilePath', '');
        if ($storeFolder === '') {
          throw new RuntimeException('FileProcessor configuration error, path is missing');
        }
        if (!empty($_FILES)) {
          $fileSet = array();
          $i = 0;
          foreach ($_FILES['file']['name'] as $item) { // Bug!!!
            $tempFile = $_FILES['file']['tmp_name'][$i];
            $targetFile = $storeFolder . DIRECTORY_SEPARATOR .
              Eisodos::$utils->generateUUID() . '.' . mb_strtolower(pathinfo($_FILES['file']['name'][$i])['extension']);
            Tholos::$app->debug('Moving received file (' . $i . ') - ' . $tempFile . ' to ' . $targetFile);
            $fileSet[] = pathinfo($targetFile)['basename'];
            if (!file_exists($tempFile)) {
              Tholos::$app->debug('No temp file found!');
            }
            move_uploaded_file($tempFile, $targetFile);
            if (!file_exists($targetFile)) {
              Tholos::$app->debug('Moving temp file failed!');
            }
            $i++;
          }
          $this->setProperty('Result', ['fileSet' => implode(',', $fileSet)]);
          $this->setProperty('ResultType', 'ARRAY');
          
          Tholos::$app->trace('Returning fileset: ' . implode(',', $fileSet));
          Tholos::$app->eventHandler($this, 'onSuccess');
        } else {
          Tholos::$app->debug('open() called but no file to process', $this);
        }
      } catch (Exception $e) {
        Tholos::$app->trace('ERROR', $this);
        $this->setProperty('ResultErrorMessage', $e->getMessage());
        $this->setProperty('ResultErrorCode', -1);
        if ($this->getProperty('ThrowException') === 'true') {
          throw $e;
        }
        Tholos::$app->eventHandler($this, 'onError');
      }
      Tholos::$app->trace('END', $this);
    }
  }
