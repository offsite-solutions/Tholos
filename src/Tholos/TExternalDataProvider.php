<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use Exception;
  use RuntimeException;
  use Throwable;
  
  /**
   * Class TExternalDataProvider
   * @package Tholos
   */
  class TExternalDataProvider extends TDataProvider {
    
    /**
     * @param $sender
     * @return array
     * @throws Throwable
     */
    public function buildFilters($sender): array {
      Tholos::$logger->trace('BEGIN', $this);
      
      $filter_array = [];
      foreach (Tholos::$app->findChildIDsByType($this, 'TQueryFilter') as $filterid) {
        // RecordSelector filter csak AutoOpen hivaskor ertelmezett
        /* @var TQueryFilter $filter */
        $filter = Tholos::$app->findComponentByID($filterid);
        try {
          $filter->init();
          $filter->initValue($sender);
          if ($filter->getProperty('JSONFilter', false) !== false) {
            $filter_array[] = $filter->getProperty('JSONFilter');
          }
        } catch (Exception $e) {
          Tholos::$logger->error('Build filter error: ' . $e->getMessage(), $this);
          $this->setProperty('FilterError', 'true');
        }
      }
      
      $this->setProperty('JSONFilters', array_merge($this->getProperty('JSONFilters', []), $filter_array), 'ARRAY)');
      
      Tholos::$logger->trace('END', $this);
      
      return $filter_array;
    }
    
    /**
     * @inheritDoc
     * @throws \JsonException
     * @throws Throwable
     */
    protected function open(?TComponent $sender, string $nativeSQL = ''): void {
      
      Tholos::$app->checkRole($this, true);
      
      if ($this->getProperty('Opened') == 'true') {
        return;
      } // ha mar meg volt nyitva, akkor ne fusson le meg egyszer
      
      if ($this->getProperty('URL')) {
        
        Tholos::$logger->trace('BEGIN', $this);
        
        $JSONParameters = $this->getProperty('JSONParameters', []);
        
        $this->setProperty('FilterError', 'false');
        
        if ($this->getProperty('DisableQueryFilters', 'false') == 'false') {
          $this->buildFilters($sender);
        }
        
        $JSONParameters['filters'] = $this->getProperty('JSONFilters', NULL);
        
        if ($this->getProperty('FilterError', 'false') == 'true') {
          Tholos::$logger->error('Missing required or malformed filter', $this);
          Tholos::$logger->trace('END', $this);
          
          return;
        }
        
        $orderBy_ = [];
        
        $orderBys = explode(',', trim($this->getProperty('OrderBy', '')));
        foreach ($orderBys as $orderByX) {
          if ($orderByX != '') {
            $o_ = explode(' ', $orderByX);
            if (D_isint($o_[0])) {
              $orderBy['fieldIndex'] = $o_[0];
              foreach (Tholos::$app->findChildIDsByType($this, 'TDBField') as $dbFieldId) {
                /* @var TDBField $dbField */
                $dbField = Tholos::$app->findComponentByID($dbFieldId);
                if ($dbField !== NULL && $dbField->getProperty('Index') == $o_[0]) {
                  $orderBy['fieldName'] = $dbField->getProperty('FieldName');
                  break;
                }
              }
            } else {
              $orderBy['fieldName'] = $o_[0];
            }
            if (count($o_) > 1) {
              $orderBy['direction'] = $o_[1];
            } else {
              $orderBy['direction'] = 'ASC';
            }
            if (count($o_) > 2) {
              if (stripos($o_[2], 'first')) {
                $orderBy['nulls'] = 'first';
              } else {
                $orderBy['nulls'] = 'last';
              }
            } else {
              $orderBy['nulls'] = NULL;
            }
            $orderBy_[] = $orderBy;
          }
        }
        $JSONParameters['orderBy'] = $orderBy_;
        
        if ((integer)$this->getProperty('QueryOffset', '0') != 0) {
          $JSONParameters['offset'] = $this->getProperty('QueryOffset', '0');
        } else {
          $JSONParameters['offset'] = NULL;
        }
        
        if ((integer)$this->getProperty('QueryLimit', '0') != 0) {
          $JSONParameters['limit'] = $this->getProperty('QueryLimit', '0');
        } else {
          $JSONParameters['limit'] = NULL;
        }
        
        Tholos::$logger->trace(print_r($JSONParameters, true), $this);
        
        $client_parameters = array();
        foreach (Tholos::$app->findChildIDsByType($this, 'TAPIParameter') as $id) {
          $component = Tholos::$app->findComponentByID($id);
          $client_parameters[$component->getProperty('ParameterName', '')] = $component->getProperty('Value', '');
        }
        
        // AuthProcedure
        
        if ($authproc_id = $this->getPropertyComponentId('AuthProcedure')) {
          /* @var TDataProvider $authproc */
          $authproc = Tholos::$app->findComponentByID($authproc_id);
          $authproc->setProperty('Opened', 'false'); // must run every time
          $authproc->run($this);
          if (Tholos::$app->findComponentByID($authproc_id)->getProperty('Success') == 'false') {
            header('X-Tholos-Error-Code: 403');
            header('X-Tholos-Error-Message: Authentication error');
            header('X-Tholos-Error-Message-B64: ' . base64_encode('Authentication error'));
            Tholos::$app->eventHandler($this, 'onAuthError');
            
            return;
          }
        }
        
        // InitProcedure
        
        if ($initproc_id = $this->getPropertyComponentId('InitProcedure')) {
          /* @var TDataProvider $initproc */
          $initproc = Tholos::$app->findComponentByID($initproc_id);
          $initproc->setProperty('Opened', 'false');
          $initproc->run($this);
          if (Tholos::$app->findComponentByID($initproc_id)->getProperty('Success') == 'false') {
            Tholos::$app->eventHandler($this, 'onInitError');
            
            return;
          }
        }
        
        Tholos::$logger->debug('External data provider opening', $this);
        try {
          
          $x = [];
          foreach (array_merge(
                     $client_parameters,
                     $_POST,
                     $_GET) as $key => $value) {
            $x[] = ['key' => $key, 'value' => $value];
          }
          $JSONParameters['parameters'] = $x;
          
          // null empty keys
          if (is_array($JSONParameters['filters']) && count($JSONParameters['filters']) == 0) {
            $JSONParameters['filters'] = NULL;
          }
          if (is_array($JSONParameters['orderBy']) && count($JSONParameters['orderBy']) == 0) {
            $JSONParameters['orderBy'] = NULL;
          }
          if (is_array($JSONParameters['parameters']) && count($JSONParameters['parameters']) == 0) {
            $JSONParameters['parameters'] = NULL;
          }
          
          $boundParametersJSON = json_encode($JSONParameters, JSON_THROW_ON_ERROR);
          
          if ($this->getProperty('curlDebug') == 'true') {
            Tholos::$logger->debug('Bound parameters: ' . $boundParametersJSON, $this);
          }
          
          $resultParameters = array();
          
          $curl = curl_init();
          
          if ($this->getProperty('curlVerbose') == 'true') {
            $streamVerboseHandle = fopen('php://temp', 'wb+');
          } else {
            $streamVerboseHandle = NULL;
          }
          
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
              'Expect:'], // JAVA Rest API nem jól kezeli a "Expect: 100-continue" headert
              explode("\n", Eisodos::$templateEngine->replaceParamInString(implode("\n", explode('\n', $this->getProperty('HTTPRequestHeader'))))),
              Tholos::$app->roleManager->getHTTPRequestAuthHeaders()
            ),
            CURLOPT_HEADER => true,
            CURLOPT_FAILONERROR => false,
            CURLOPT_VERBOSE => ($this->getProperty('curlVerbose') == 'true'),
            CURLOPT_STDERR => $streamVerboseHandle,
            //CURLOPT_HTTP200ALIASES => (array)400
          );
          
          curl_setopt_array($curl, $options);
          
          if ($this->getProperty('curlDebug') == 'true') {
            Tholos::$logger->debug(print_r($options, true));
          }
          
          $httpResponse = curl_exec($curl);
          $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
          $httpError = curl_error($curl);
          curl_close($curl);
          
          if ($this->getProperty('curlVerbose') == 'true') {
            rewind($streamVerboseHandle);
            $verboseLog = stream_get_contents($streamVerboseHandle);
            Tholos::$logger->debug("Verbose: \n" . $verboseLog);
          }
          
          if ($this->getProperty('curlDebug') == 'true') {
            Tholos::$logger->debug('HTTP Code: ' . $httpCode);
            Tholos::$logger->debug('HTTP Response: ' . $httpResponse);
            Tholos::$logger->debug('HTTP Error: ' . $httpError);
          }
          
          // hard error
          if ($httpError || !$httpCode || $httpResponse === false) {
            throw new RuntimeException($httpResponse === false ? 'Service is not available' : ($httpError ? $httpCode : 'Unknown error'));
          }
          
          // soft error
          if (1 * $httpCode == 401) {
            throw new \RuntimeException('401 Not Authorized');
          }
          
          if (1 * $httpCode >= 200 && 1 * $httpCode < 300) {
            if (str_starts_with($httpResponse, 'HTTP/')) {
              $httpNormalResponse = explode("\r\n\r\n", $httpResponse, 2);
              if (count($httpNormalResponse) > 1) {
                $resultParameters = json_decode((trim($httpNormalResponse[1]) === '' ? '{}' : $httpNormalResponse[1]), true, 512, JSON_THROW_ON_ERROR);
              }
            } else {
              $resultParameters = json_decode((trim($httpResponse) === '' ? '{}' : $httpResponse), true, 512, JSON_THROW_ON_ERROR);
            }
            $this->setProperty('Success', 'true');
          } else {
            throw new RuntimeException($httpCode . ' - ' . $httpError);
          }
          
          if ($this->getProperty("curlDebug") == 'true') {
            Tholos::$logger->debug(print_r($resultParameters, true), $this);
          } else {
            Tholos::$logger->trace(print_r($resultParameters, true), $this);
          }
          
          $this->setProperty('Opened', 'true');
          $this->setProperty('Result', $resultParameters[$this->getProperty('ResultParameter', 'result')], 'ARRAY');
          $this->setProperty('ResultType', 'ARRAY');
          $this->setProperty('RowCount', count($resultParameters[$this->getProperty('ResultParameter', 'result')]));
          $this->setProperty('TotalRowCount', Eisodos::$utils->safe_array_value($resultParameters, $this->getProperty('TotalRowCountField'), $this->getProperty('RowCount')));
          
          Tholos::$app->eventHandler($this, 'onSuccess');
          
        } catch (Exception $e) {
          Tholos::$logger->writeErrorLog($e);
          Tholos::$app->eventHandler($this, 'onError');
          throw $e;
        }
        
        Tholos::$logger->trace('END', $this);
        
      }
      
      return;
    }
    
    /**
     * @inheritDoc
     */
    public function autoOpen(): void {
      
      if ($this->getProperty('AutoOpenAllowed', 'true') == 'true' &&
        count(Tholos::$app->findChildIDsByType($this, 'TDBfield')) > 0) {  // csak akkor nyiljon meg a query, ha vannak benne dbfield-ek (kulonben valoszinuleg lov)
        Tholos::$logger->trace('BEGIN', $this);
        $this->run(NULL);
        Tholos::$logger->trace('END', $this);
      }
      
    }
    
  }
  