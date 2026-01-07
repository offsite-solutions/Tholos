<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use Exception;
  
  /**
   * Class TJSONDataProvider
   * @package Tholos
   */
  class TJSONDataProvider extends TDataProvider {
    
    /**
     * @param $sender
     * @param string $nativeSQL
     * @throws Exception
     */
    protected function open(?TComponent $sender, string $nativeSQL = ''): void {
      
      Tholos::$app->checkRole($this, true);
      
      if ($nativeSQL == '' && $this->getProperty('Opened') == 'true') {
        return;
      } // ha mar meg volt nyitva, akkor ne fusson le meg egyszer
      
      if ($nativeSQL) {
        
        Tholos::$logger->trace('BEGIN', $this);
        
        try {
          
          Tholos::$logger->debug('Parse JSON ' . $nativeSQL);
          
          $result_ = json_decode($nativeSQL, true, 512, JSON_THROW_ON_ERROR);
          if (!$result_) {
            $result_ = [];
          }
          if ($this->getProperty('DataResultMustBeIndexed', 'false') == 'true') {
            $result[0] = $result_;
          } else {
            $result = $result_;
          }
          
          $this->setProperty('Opened', 'true');
          $this->setProperty('Result', $result);
          $this->setProperty('ResultType', 'ARRAY');
          $this->setProperty('RowCount', count($result));
          $this->setProperty('TotalRowCount', count($result));
          
          Tholos::$app->eventHandler($this, 'onSuccess');
          
        } catch (Exception $e) {
          Tholos::$logger->writeErrorLog($e);
          Tholos::$app->eventHandler($this, 'onError');
          throw $e;
        }
        
        Tholos::$logger->trace('END', $this);
        
      }
    }
    
    /**
     * @inheritDoc
     */
    public function autoOpen():void {
      //
    }
    
    /**
     * @inheritDoc
     */
    public function openDatabase($force_ = false):void {
      //
    }
    
    /**
     * @inheritDoc
     */
    public function run(?TComponent $sender):void {
      //
    }
    
    /**
     * @inheritDoc
     */
    public function close():void {
      //
    }
    
  }
  