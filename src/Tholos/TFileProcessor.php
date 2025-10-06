<?php /** @noinspection DuplicatedCode SpellCheckingInspection PhpUnusedFunctionInspection NotOptimalIfConditionsInspection */
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use Exception;
  use RuntimeException;
  use ZipArchive;
  
  /**
   * Class TFileProcessor
   * @package Tholos
   */
  class TFileProcessor extends TDataProvider {
    
    /**
     * @param TComponent|null $sender
     * @param string $nativeSQL
     * @return string
     * @throws RuntimeException
     * @throws Exception
     */
    protected function open(?TComponent $sender, string $nativeSQL = ''): string {
      
      if ($this->getProperty('Opened', 'false') == 'true') {
        Tholos::$logger->trace('Already opened, exiting');
        
        return '';
      }
      
      if (!Tholos::$app->checkRole($this)) {
        return '';
      }
      
      Tholos::$logger->trace('BEGIN', $this);
      
      try {
        $storeFolder = $this->getProperty('LocalFilePath', '');
        if ($storeFolder === '') {
          throw new RuntimeException('FileProcessor configuration error, path is missing');
        }
        if (!empty($_FILES)) {
          $fileSet = array();
          $i = 0;
          foreach ($_FILES['file']['name'] as $tempFile) { // Bug!!!
            //$tempFile = $_FILES['file']['tmp_name'][$i];
            $extension = mb_strtolower(pathinfo($_FILES['file']['name'][$i])['extension']);
            Tholos::$logger->trace('Extension is ' . $extension . ', ExtractArchive is ' . $this->getProperty('ExtractArchive', 'false') . ', TempFile is ' . $tempFile);
            if ($extension == 'zip' && $this->getProperty('ExtractArchive', 'false') == 'true' && file_exists($tempFile)) {
              Tholos::$logger->trace('ZIP Archive initialization');
              $zip = new ZipArchive;
              Tholos::$logger->trace('ZIP Archive initialized');
              if ($zip->open($tempFile) === true) {
                $numFiles = 0;
                for ($ii = 0; $ii < $zip->numFiles; $ii++) { // skipping fucking __MACOSX folder
                  if (strpos($zip->getNameIndex($ii), '/') > 0 || strpos($zip->getNameIndex($ii), '\\') > 0) {
                    continue;
                  }
                  $numFiles++;
                }
                Tholos::$logger->debug('ZIP open succeed, number of files in archive is ' . $numFiles);
                $maximumFilesInArchive = 1 * $this->getProperty('MaximumFilesInArchive', '0');
                if ($maximumFilesInArchive > 0 && $numFiles > $maximumFilesInArchive) {
                  $zip->close();
                  throw new RuntimeException('Too many files in archive, maximum ' . $maximumFilesInArchive);
                }
                $extractFiles = [];
                for ($ii = 0; $ii < $zip->numFiles; $ii++) {
                  if (strpos($zip->getNameIndex($ii), '/') > 0 || strpos($zip->getNameIndex($ii), '\\') > 0) {
                    continue;
                  }
                  Tholos::$logger->trace('ZIP[' . $ii . ']: ' . $zip->getNameIndex($ii));
                  $extractFiles[] = $zip->getNameIndex($ii);
                }
                if (!$zip->extractTo($storeFolder . DIRECTORY_SEPARATOR, $extractFiles)) {
                  Tholos::$logger->error('Error on extracting files to (' . $storeFolder . DIRECTORY_SEPARATOR . '): ' . implode(',', $extractFiles));
                } else {
                  Tholos::$logger->debug('Extracting files succeed to (' . $storeFolder . DIRECTORY_SEPARATOR . '): ' . implode(',', $extractFiles));
                }
                foreach ($extractFiles as $extracted) {
                  $targetFile = $storeFolder . DIRECTORY_SEPARATOR .
                    Eisodos::$utils->generateUUID() . '.' . mb_strtolower(pathinfo($extracted)['extension']);
                  Tholos::$logger->trace('Renaming file '.$extracted.' to '.$targetFile);
                  rename($storeFolder . DIRECTORY_SEPARATOR . $extracted, $targetFile);
                  $fileSet[] = pathinfo($targetFile)['basename'];
                }
                $zip->close();
              } else {
                throw new RuntimeException('Could not extract zip archive');
              }
            } else {
              $targetFile = $storeFolder . DIRECTORY_SEPARATOR .
                Eisodos::$utils->generateUUID() . '.' . mb_strtolower(pathinfo($_FILES['file']['name'][$i])['extension']);
              Tholos::$logger->debug('Moving received file (' . $i . ') - ' . $tempFile . ' to ' . $targetFile);
              $fileSet[] = pathinfo($targetFile)['basename'];
              if (!file_exists($tempFile)) {
                Tholos::$logger->debug('No temp file found!');
                continue;
              }
              move_uploaded_file($tempFile, $targetFile);
              if (!file_exists($targetFile)) {
                Tholos::$logger->debug('Moving temp file failed!');
              }
            }
          }
          $this->setProperty('Result', ['fileSet' => implode(',', $fileSet)]);
          $this->setProperty('ResultType', 'ARRAY');
          
          Tholos::$logger->trace('Returning fileset: ' . implode(',', $fileSet));
          Tholos::$app->eventHandler($this, 'onSuccess');
        } else {
          Tholos::$logger->debug('open() called but no file to process', $this);
        }
        $this->setProperty('Opened', 'true');
        Tholos::$logger->trace('Opened: ' . $this->getProperty('Opened', 'false'));
      } catch (Exception $e) {
        Tholos::$logger->trace('ERROR', $this);
        $this->setProperty('ResultErrorMessage', $e->getMessage());
        $this->setProperty('ResultErrorCode', -1);
        if ($this->getProperty('ThrowException') === 'true') {
          throw $e;
        }
        Tholos::$app->eventHandler($this, 'onError');
      }
      Tholos::$logger->trace('END', $this);
      return '';
    }
  }
