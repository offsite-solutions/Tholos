<?php /** @noinspection DuplicatedCode SpellCheckingInspection PhpUnusedFunctionInspection NotOptimalIfConditionsInspection */
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use Exception;
  use RuntimeException;
  
  
  class TAPIPost extends TStoredProcedure {
    
    private function StringToJSON($text_, $dataType_) {
      if ($text_ === NULL or $text_ === '') {
        return NULL;
      }
      if ($dataType_ === 'integer') {
        return 1 * $text_;
      }
      if ($dataType_ === 'bool') {
        if (in_array($text_, ['1', 'Y', 'I', 'true'], false)) {
          return true;
        }
        
        return false;
      }
      if ($dataType_ === 'float') {
        return 1.0 * $text_;
      }
      
      return $text_;
    }
    
    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function open(?TComponent $sender, $nativeSQL = ''): void {
      if ($this->getProperty('Opened', 'false') === 'true') {
        return;
      } // ha mar meg volt nyitva, akkor ne fusson le meg egyszer
      
      /* @var TDataProxy $dataProxy */
      if ($this->getPropertyComponentId('DataProxy')) {
        $dataProxy = Tholos::$app->findComponentByID($this->getPropertyComponentId('DataProxy'));
        if ($dataProxy->getProperty('Enabled', 'false') !== 'true') {
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
            if (!$param or $param->getProperty('ParameterMode') === 'OUT') {
              continue;
            }
            /* @var TDBParam $param */
            $param->initValue($this);
            $boundParameters[$param->getProperty('ParameterName')] = $this->StringToJSON($param->getProperty('DBValue', ''), $param->getProperty('DataType'));
          }
          
          $boundParametersJSON = json_encode($boundParameters, JSON_THROW_ON_ERROR);
          
          Tholos::$app->trace('Bound parameters: ' . $boundParametersJSON, $this);
          
          $resultParameters = array();
          
          $curl = curl_init();
          
          $options = array(
            CURLOPT_URL => $this->getProperty('URL') . $this->getProperty('URLPath'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 1 * $this->getProperty('TimeOut', '0'),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $this->getProperty('HTTPRequestMethod'),
            CURLOPT_POSTFIELDS => $boundParametersJSON,
            CURLOPT_USERAGENT => 'Tholos (' . Eisodos::$parameterHandler->getParam('last_tholos_release', 'dev') . ')',
            CURLOPT_HTTPHEADER => array_merge(['X-Tholos-SessionID: ' . Eisodos::$parameterHandler->getParam('Tholos_sessionID'),
              'Expect:'], // JAVA Rest API nem jól kezeli a 'Expect: 100-continue' headert
              explode("\n", Eisodos::$templateEngine->replaceParamInString(implode("\n", explode('\n', $this->getProperty('HTTPRequestHeader'))))),
              Tholos::$app->roleManager->getHTTPRequestAuthHeaders()
            ),
            CURLOPT_HEADER => true,
            CURLOPT_FAILONERROR => false
            //CURLOPT_HTTP200ALIASES => (array)400
          );
          
          curl_setopt_array($curl, $options);
          
          Tholos::$app->trace(print_r($options, true));
          
          $httpResponse = curl_exec($curl);
          $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
          $httpError = curl_error($curl);
          curl_close($curl);
          
          Tholos::$app->trace('HTTP Code: ' . $httpCode);
          Tholos::$app->trace('HTTP Response: ' . $httpResponse);
          Tholos::$app->trace('HTTP Error: ' . $httpError);
          
          // TODO 401-es hibat kezelni RoleManagerre
          
          // hard error
          if ($httpError || !$httpCode || $httpResponse === false) {
            throw new RuntimeException($httpResponse === false ? 'Service is not available' : ($httpError ? $httpCode : 'Unknown error'));
          }
          
          // soft error
          if (1 * $httpCode === 401) {
            Tholos::$app->roleManager->logout();
          } else if (1 * $httpCode >= 200 and 1 * $httpCode < 300) {
            if (strpos($httpResponse, 'HTTP/') === 0) {
              $httpNormalResponse = explode("\r\n\r\n", $httpResponse, 2);
              if (count($httpNormalResponse) > 1) {
                $resultParameters = json_decode($httpNormalResponse[1], true, 512, JSON_THROW_ON_ERROR);
              }
            } else {
              $resultParameters = json_decode($httpResponse, true, 512, JSON_THROW_ON_ERROR);
            }
            $this->setProperty('Success', 'true');
          } else {
            /*
             * HTTP/1.1 400
             * Content-Type: application/json
             * Transfer-Encoding: chunked
             * Date: Wed, 24 Jun 2020 19:33:56 GMT
             * Connection: close
             *
             * {"timestamp":"2020-06-24T19:33:56.816+00:00","status":400,"error":"Bad Request","message":"","path":"/nop/institution"}
             */
            $httpErrorResponse = explode("\r\n\r\n", $httpResponse, 2);
            if (count($httpErrorResponse) > 1) {
              $resultParameters = json_decode($httpErrorResponse[1], true, 512, JSON_THROW_ON_ERROR);
            }
            $this->setProperty('ResultErrorCode', Eisodos::$utils->safe_array_value($resultParameters, $this->getProperty('ErrorCodeJSONField'), $httpCode)); // todo
            $this->setProperty('ResultErrorMessage', Eisodos::$utils->safe_array_value($resultParameters, $this->getProperty('ErrorMessageJSONField'), $httpCode)); // todo
            $this->setProperty('Success', 'false');
          }
          
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
        if ($this->getProperty('CallbackParameter') !== '') { // ha van controlparameter, akkor azt beletolteni a ControlResult JSON property-be
          $callbackResult = $resultParameters[Tholos::$app->findComponentByID($this->getPropertyComponentId('CallbackParameter'))->getProperty('ParameterName')];
          try {
            if ($callbackResult !== '') {
              if (json_decode($callbackResult, true, 512, JSON_THROW_ON_ERROR) === false) {
                throw new RuntimeException('Invalid JSON result');
              }
              $this->setProperty('CallbackResult', $callbackResult);
            }
          } catch (Exception $e) {
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
        }
        
        if ($this->getProperty('ErrorMessageParameter', '') !== '') {
          $this->setProperty('ResultErrorMessage',
            $resultParameters[Tholos::$app->findComponentByID($this->getPropertyComponentId('ErrorMessageParameter'))->getProperty('ParameterName')]);
        }
        
        header('X-Tholos-Error-Code: ' . $this->getProperty('ResultErrorCode'));
        header('X-Tholos-Error-Message: ' . $this->getProperty('ResultErrorMessage'));
        header('X-Tholos-Error-Message-B64: ' . base64_encode($this->getProperty('ResultErrorMessage')));
        
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
          
        } else {
          
          Tholos::$app->error('Execution failed', $this);
          Tholos::$app->eventHandler($this, 'onError');
          
          if ($this->getProperty('WriteErrorLogOnError', 'false') === 'true') {
            Eisodos::$logger->writeErrorLog(NULL, 'Tholos TAPIPost handled error debug information');
          }
          
        }
        
        if ($this->getProperty('LogParameter', '') !== '' && $this->getPropertyComponentId('LoggerStoredProcedure') !== false) { // ha van controlparameter, akkor azt beletolteni a ControlResult JSON property-be
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
        
        $this->setProperty('ResultErrorMessage', $e->getMessage());
        $this->setProperty('ResultErrorCode', -1);
        $this->setProperty('Opened', 'false');
        $this->setProperty('Success', 'false');
        
        Eisodos::$logger->writeErrorLog($e);
        
        if ($this->getProperty('ThrowException', 'false') === 'true') {
          throw $e;
        }
        Tholos::$app->eventHandler($this, 'onError');
        
        Tholos::$app->trace('END', $this);
      }
    }
    
  }
  