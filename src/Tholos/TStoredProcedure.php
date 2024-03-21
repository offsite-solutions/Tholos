<?php /** @noinspection DuplicatedCode SpellCheckingInspection PhpUnusedFunctionInspection NotOptimalIfConditionsInspection */
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use Exception;
  use RuntimeException;
  use Throwable;
  
  
  class TStoredProcedure extends TDataProvider {
    
    /**
     * @inheritDoc
     * @throws Throwable
     */
    protected function open(?TComponent $sender, string $nativeSQL = ''): void {
      if ($this->getProperty('Opened') === 'true') {
        return;
      } // ha mar meg volt nyitva, akkor ne fusson le meg egyszer
      
      /* @var TDataProxy $dataProxy */
      if ($this->getPropertyComponentId('DataProxy')) {
        $dataProxy = Tholos::$app->findComponentByID($this->getPropertyComponentId('DataProxy'));
        if (!$dataProxy || $dataProxy->getProperty('Enabled', 'false') !== 'true') {
          $dataProxy = NULL;
        }
      } else {
        $dataProxy = NULL;
      }
      
      try {
        
        if (!Tholos::$app->checkRole($this)) {
          return;
        }
        
        Tholos::$app->trace('BEGIN', $this);
        
        if (is_null($dataProxy)) {
          
          $boundParameters = array();
          foreach (Tholos::$app->findChildIDsByType($this, 'TDBParam') as $paramId) {
            $param = Tholos::$app->findComponentByID($paramId);
            if (!$param) {
              throw new RuntimeException('Invalid reference');
            }
            /* @var TDBParam $param */
            $param->initValue($this);
            StoredProc_Param(Tholos::$c,
              $boundParameters,
              $param->getProperty('ParameterName'),
              $param->getProperty('MDB2DataType', 'text'),
              $param->getProperty('DBValue', ''),
              $param->getProperty('ParameterMode', 'IN'));
          }
          
          Tholos::$app->trace('Bound parameters: ' . print_r($boundParameters, true), $this);
          
          $resultParameters = array();
          
          $this->setProperty('Success', 'false');
          
          if ($this->getProperty('TransactionMode', 'true') === 'true') {
            Tholos::$c->getDBByIndex((integer)$this->getProperty('db', '1'))->beginTransaction();
            Tholos::$app->trace('Transaction started', $this);
          }
          
          StoredProc_Run(Tholos::$c->getDBByIndex((integer)$this->getProperty('db', '1')),
            StoredProc_SQL(Tholos::$c->getDBByIndex((integer)$this->getProperty('db', '1')),
              $boundParameters,
              $this->getProperty('Procedure')),
            $boundParameters,
            $resultParameters
          );
          
        } else {
          try {
            $jsonArray = json_decode($dataProxy->open($this, array(), array()), true, 512, JSON_THROW_ON_ERROR);
            $resultParameters = json_decode($jsonArray['data'], true, 512, JSON_THROW_ON_ERROR);
          } catch (Exception $e) {
            Tholos::$app->error($e->getMessage(), $this);
            $resultParameters = [];
          }
        }
        
        Tholos::$app->trace('Result parameters: ' . print_r($resultParameters, true), $this);
        
        $this->setProperty('ResultType', 'ARRAY');
        if ($this->getProperty('CallbackParameter', '') !== '') { // ha van controlparameter, akkor azt beletolteni a ControlResult JSON property-be
          $callBackResult = $resultParameters[Tholos::$app->findComponentByID($this->getPropertyComponentId('CallbackParameter'))->getProperty('ParameterName')];
          try {
            if ($callBackResult !== '') {
              if (json_decode($callBackResult, false, 512, JSON_THROW_ON_ERROR) === false) {
                throw new RuntimeException('Invalid JSON result');
              }
              $this->setProperty('CallbackResult', $callBackResult);
            }
          } catch (Exception) {
            $this->setProperty('CallbackResult', '');
            Tholos::$app->error('CallbackParameter contains invalid JSON value', $this);
          }
        }
        
        if ($this->getProperty('ErrorCodeParameter', '') !== '') {
          $this->setProperty('ResultErrorCode',
            $resultParameters[Tholos::$app->findComponentByID($this->getPropertyComponentId('ErrorCodeParameter'))->getProperty('ParameterName')]);
          
          if ($this->getProperty('ErrorCodeOnSuccess', '') !== ''
            && $this->getProperty('ResultErrorCode') !== $this->getProperty('ErrorCodeOnSuccess', '')) {
            $this->setProperty('Success', 'false');
          } else {
            $this->setProperty('Success', 'true');
          }
        } else {
          $this->setProperty('Success', 'true');
        }
        
        if ($this->getProperty('ErrorMessageParameter', '') !== '') {
          $this->setProperty('ResultErrorMessage',
            $resultParameters[Tholos::$app->findComponentByID($this->getPropertyComponentId('ErrorMessageParameter'))->getProperty('ParameterName')]);
        }
        
        header('X-Tholos-Error-Code: ' . $this->getProperty('ResultErrorCode'));
        header('X-Tholos-Error-Message: ' . $this->getProperty('ResultErrorMessage'));
        header('X-Tholos-Error-Message-B64: ' . base64_encode($this->getProperty('ResultErrorMessage')));
        
        //$this->setProperty('Result',$resultParameters);
        // generating result set
        $result = array();
        
        foreach (Tholos::$app->findChildIDsByType($this, 'TDBParam') as $paramId) {
          $param = Tholos::$app->findComponentByID($paramId);
          $param->setProperty('DBValue', $resultParameters[$param->getProperty('ParameterName')]);
          //$param->setProperty('Value',$resultParameters[$param->getProperty('ParameterName')]);
          if ($this->getProperty('GenerateDataResult', 'true') === 'true'
            && $param->getProperty('AddToResult', 'true') === 'true') {
            $result[$param->getProperty('ParameterName')] = $resultParameters[$param->getProperty('ParameterName')];
          }
        }
        
        $this->setProperty('Result', $result);
        //$this->setProperty('Result',$resultParameters);
        
        $this->setProperty('Opened', 'true');
        
        if ($this->getProperty('Success', 'false') === 'true') {
          
          Tholos::$app->debug('Execution success', $this);
          Tholos::$app->eventHandler($this, 'onSuccess');
          
          if (is_null($dataProxy)
            && $this->getProperty('TransactionMode', 'true') === 'true'
            && Tholos::$c->getDBByIndex((integer)$this->getProperty('db', '1'))->inTransaction()) {
            Tholos::$c->getDBByIndex((integer)$this->getProperty('db', '1'))->commit();
            Tholos::$app->trace('Transaction commit', $this);
          }
          
        } else {
          
          Tholos::$app->debug('Execution failed', $this);
          // if (Eisodos::$parameterHandler->eq('TholosProxy:ProxyComponentID', '')) { // proxy oldalon ne fusson
          Tholos::$app->eventHandler($this, 'onError');
          // }
          
          if (is_null($dataProxy)
            && $this->getProperty('TransactionMode', 'true') === 'true'
            && Tholos::$c->getDBByIndex((integer)$this->getProperty('db', '1'))->inTransaction()) {
            if ($this->getProperty('RollbackOnError', 'true') === 'true') {
              Tholos::$c->getDBByIndex((integer)$this->getProperty('db', '1'))->rollback();
              Tholos::$app->trace('Transaction rollback', $this);
            } else {
              Tholos::$c->getDBByIndex((integer)$this->getProperty('db', '1'))->commit();
              Tholos::$app->trace('Transaction commit', $this);
            }
          }
          
          if ($this->getProperty('WriteErrorLogOnError', 'false') === 'true') {
            Eisodos::$logger->writeErrorLog(NULL, 'Tholos TStoredProcedure handled error debug information');
          }
          
        }
        
        if ($this->getProperty('LogParameter') !== '' && $this->getPropertyComponentId('LoggerStoredProcedure') !== false) { // ha van controlparameter, akkor azt beletolteni a ControlResult JSON property-be
          $logs = $resultParameters[Tholos::$app->findComponentByID($this->getPropertyComponentId('LogParameter'))->getProperty('ParameterName')];
          try {
            if ($logs !== '') {
              Eisodos::$parameterHandler->setParam($this->getProperty('LogDBParameterName'), $logs);
              /* @var TDataProvider $loggerSP */
              $loggerSP = Tholos::$app->findComponentByID($this->getPropertyComponentId('LoggerStoredProcedure'));
              $loggerSP->run($this);
            }
          } catch (Exception $e) {
            Tholos::$app->error($e->getMessage());
          }
        }
        
        Tholos::$app->trace('END', $this);
        
      } catch (Exception $e) {
        Tholos::$app->error('ERROR', $this);
        
        if (is_null($dataProxy)
          && $this->getProperty('TransactionMode', 'true') === 'true'
          && Tholos::$c->getDBByIndex((integer)$this->getProperty('db', '1'))->inTransaction()) {
          Tholos::$c->getDBByIndex((integer)$this->getProperty('db', '1'))->rollback();
        }
        
        $this->setProperty('ResultErrorMessage', $e->getMessage());
        $this->setProperty('ResultErrorCode', -1);
        $this->setProperty('Opened', 'false');
        $this->setProperty('Success', 'false');
        
        Eisodos::$logger->writeErrorLog($e, $this->getProperty('Name'));
        
        if ($this->getProperty('ThrowException') === 'true') {
          throw $e;
        }
        if (Eisodos::$parameterHandler->eq('TholosProxy:ProxyComponentID', '')) { // proxy oldalon ne fusson
          Tholos::$app->eventHandler($this, 'onError');
        }
        
        Tholos::$app->trace('END', $this);
      }
    }
    
  }
  