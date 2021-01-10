<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use Exception;
  use http\Exception\RuntimeException;
  use LSS\Array2XML;
  use Throwable;

  /**
   * Class TDataProvider
   * @package Tholos
   */
  class TDataProvider extends TComponent {
    
    /**
     * @inheritDoc
     */
    public function __construct($componentType_, $id_, $parent_id_, $properties_ = array(), $events_ = array()) {
      parent::__construct($componentType_, $id_, $parent_id_, $properties_, $events_);
      $this->close();
    }
    
    /**
     * Propagates the raw result to the TDBField's
     *
     * @param mixed $resultRow_
     * @throws Throwable
     */
    public function propagateResult($resultRow_): void {
      Tholos::$app->trace('BEGIN', $this);
      
      if (is_array($resultRow_)) {
        $queryResult = $resultRow_;
      } elseif ($this->getProperty('RowCount', '0') > 0) {
        if ($this->getProperty('ResultType', '') === 'ARRAY') {
          $queryResult = $this->getProperty('Result', array())[0];
        } else {
          $queryResult = json_decode($this->getProperty('Result', ''), true, 512, JSON_THROW_ON_ERROR)[0];
        }
      } else {
        $queryResult = array();
      }
      
      // if (!empty($queryResult)) {
      //  array_change_key_case($queryResult);
      // }
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TDBField') as $component) {
        $dbField = Tholos::$app->findComponentByID($component);
        if (!$dbField) {
          throw new RuntimeException('Invalid reference');
        }
        $fieldName_clean = mb_strtolower($dbField->getProperty('FieldName', ''));
        // check is fieldname has prefix
        if (strpos($fieldName_clean, '.')) {
          $fieldName_clean = explode('.', $fieldName_clean, 2)[1];
        }
        
        /* @var $dbField TDBField */
        $dbField->setProperty('DBValue', Eisodos::$utils->safe_array_value($queryResult, $fieldName_clean, $dbField->getProperty('NullResultParameter', '')), 'STRING', '', $dbField->getProperty('ParseValue', 'true') === 'false');
        $dbField->propagateValue();
      }
      Tholos::$app->trace('END', $this);
    }
    
    /**
     * Initializes itself and all of its child components and in case of a direct call runs itself
     * (it is needed here, because in a normal initialization process, childs initialized later)
     *
     * @inheritDoc
     */
    public function init(): void {
      Tholos::$app->trace('BEGIN', $this);
      Tholos::$app->trace('(' . $this->_componentType . ') (ID ' . $this->_id . ')', $this);
      $this->initialized = true;
      // set runtime properties
      
      // in case of this component called itself via TDataProxy
      if (Eisodos::$parameterHandler->getParam('TholosProxy:TargetComponentID') === (string)$this->_id) {
        Tholos::$app->trace('Finding proxy properties', $this);
        foreach ($this->getPropertyNames() as $key) {
          $proxyValue = Eisodos::$parameterHandler->getParam($this->getProperty('Name') . '>' . $key);
          if ($proxyValue !== '') {
            Tholos::$app->debug('Setting property <' . $key . '> to ' . $proxyValue . ' by proxy request', $this);
            if ($this->getPropertyType($key) === 'ARRAY') {
              $this->setProperty($key, json_decode($proxyValue, true, 512, JSON_THROW_ON_ERROR));
            } else {
              $this->setProperty($key, $proxyValue);
            }
          }
        }
      }
      
      if (Tholos::$app->action_id === $this->_id) { // a dataprovider direktben meg lett hivva
        
        // initializing all child component -- a run miatt ezek kesobb inicializalodnak
        foreach (Tholos::$app->findChildIDsByType($this, 'TComponent') as $componentId) {
          Tholos::$app->findComponentByID($componentId)->init();
        }
        
        Tholos::$app->trace('Auto opening query', $this);
        $this->run(NULL);
      }
      Tholos::$app->trace('END', $this);
    }
    
    /**
     * Interface routine, inherited objects must implement
     *
     * @param ?TComponent $sender
     * @param string $nativeSQL
     */
    protected function open(?TComponent $sender, $nativeSQL = ''): void { }
    
    /**
     * Closes the connection and clears result
     */
    public function close(): void {
      if ($this->getProperty('Opened', 'false') === 'false') {
        return;
      }
      Tholos::$app->trace('BEGIN', $this);
      
      $this->setProperty('Opened', 'false');
      if ($this->getProperty('Result') !== false) {
        $this->setProperty('Result', array(array()));
      }
      if ($this->getProperty('ResultType') !== false) {
        $this->setProperty('ResultType', 'ARRAY');
      }
      if ($this->getProperty('RowCount') !== false) {
        $this->setProperty('Rowcount', 0);
      }
      
      Tholos::$app->trace('END', $this);
    }
    
    /**
     * Opens database, DataProxy or cache connection
     * @param bool $force_ Force open database connection if it's not proxied
     * @throws Throwable
     */
    public function openDatabase($force_ = false): void {
      
      if ($this->getPropertyComponentId('DataProxy')) {
        $dataProxy = Tholos::$app->findComponentByID($this->getPropertyComponentId('DataProxy'));
        if (!$dataProxy) {
          throw new RuntimeException('Invalid reference');
        }
        if ($dataProxy->getProperty('Enabled', 'false') !== 'true') {
          $dataProxy = NULL;
        }
      } else {
        $dataProxy = NULL;
      }
      
      if (is_null($dataProxy)) {
        if ($force_
          || $this->getProperty('Caching', 'Disabled') === 'Disabled'
          || Eisodos::$parameterHandler->eq('TholosCacheAction', 'refresh')) {
          //if ($this->getPropertyType('DatabaseIndex',null)!==null) {
          //  Tholos::$app->debug('Database connection is not needed');
          //  return;
          //}
          if (Tholos::$c->openDBA($this->getProperty('DatabaseIndex', '1'))) {
            Tholos::$app->debug('Opening database connection by ' . $this->getProperty('Name'));
          } else {
            Tholos::$app->debug('Database connection already on for ' . $this->getProperty('Name'));
          }
          if (Tholos::$app->roleManager !== NULL) {
            Tholos::$app->roleManager->initDBSession();
          }
          Tholos::$app->debug('Database connection is ready to use');
        }
      } else {
        Tholos::$app->debug('Opening database connection is disabled by active DataProxy');
      }
    }
    
    /**
     * Opens database, opens itself and propogate its result
     *
     * @param ?TComponent $sender
     * @throws Throwable
     */
    public function run(?TComponent $sender): void {
      try {
        Tholos::$app->trace('BEGIN', $this);
        $this->openDatabase();
        Tholos::$app->checkRole($this, true);
        Tholos::$app->eventHandler($this, 'onBeforeOpen');
        $this->open($sender);
        $this->propagateResult(NULL);
      } catch (Exception $e) {
        Tholos::$app->error($e->getMessage());
        Tholos::$app->responseErrorMessage = $e->getMessage();
      }
      Tholos::$app->trace('END', $this);
    }
    
    /**
     * Autoopen is called by Tholos Application in case of direct call
     *
     * @throws Throwable
     */
    public function autoOpen(): void {
      
      if ($this->getProperty('AutoOpenAllowed', 'true') === 'true') {
        Tholos::$app->trace('BEGIN', $this);
        $this->run(NULL);
        Tholos::$app->trace('END', $this);
      }
      
    }
    
    /**
     * Dataprovider's render function always return an empty string,
     * but generates its result to the application's response array.
     * Inherited components usually don't need to override this method.
     *
     * @param TComponent $sender
     * @param string $content
     * @return string
     * @throws Exception
     */
    public function render(TComponent $sender, string $content): string {
      
      if (!Tholos::$app->checkRole($this)) {
        return '';
      }
      
      $this->renderedContent = '';
      
      if ($sender === NULL) { // alkalmazas hivta
        Tholos::$app->trace('BEGIN', $this);
        
        Tholos::$app->eventHandler($this, 'onBeforeRender');
        
        Tholos::$app->responseType = (Eisodos::$parameterHandler->eq('responseType', '') ? $this->getProperty('ResponseType') : Eisodos::$parameterHandler->getParam('responseType'));
        
        if (Tholos::$app->responseType === 'JSON' || Tholos::$app->responseType === 'PROXY') {
          if ($this->getProperty('ResultType') === 'JSON') {
            Tholos::$app->responseARRAY['data'] = $this->getProperty('Result');
          } elseif ($this->getProperty('ResultType') === 'ARRAY') {
            Tholos::$app->responseARRAY['data'] = json_encode($this->getProperty('Result'), JSON_THROW_ON_ERROR);
          }
          
          Tholos::$app->responseARRAY['callback'] = $this->getProperty('CallbackResult', '');
          Tholos::$app->responseErrorCode = $this->getProperty('ResultErrorCode', '');
          Tholos::$app->responseErrorMessage = $this->getProperty('ResultErrorMessage', '');
          if ((Tholos::$app->responseType === 'PROXY') && Eisodos::$parameterHandler->getParam('TholosProxy:TargetComponentID') === (string)$this->_id) {
            Tholos::$app->trace('Finding requested proxy properties', $this);
            foreach ($this->getPropertyNames() as $key) {
              $proxyValue = Eisodos::$parameterHandler->getParam($this->getProperty('Name') . '<' . $key);
              if ($proxyValue === 'response') {
                Tholos::$app->debug('Pushing back ' . $key . ' property with value ' . $this->getProperty($key, ''));
                Tholos::$app->responseARRAY[$this->getProperty('Name') . '>' . $key] = $this->getProperty($key, '');
              }
            }
          }
        } elseif (Tholos::$app->responseType === 'JSONDATA') {
          if ($this->getProperty('ResultType') === 'JSON') {
            Tholos::$app->responseARRAY['data'] = $this->getProperty('Result');
          } elseif ($this->getProperty('ResultType') === 'ARRAY') {
            Tholos::$app->responseARRAY['data'] = json_encode($this->getProperty('Result'), JSON_THROW_ON_ERROR);
          }
        } elseif (Tholos::$app->responseType === 'XML') {
          
          Array2XML::init($version = '1.0', $encoding = 'UTF-8');
          $xml = NULL;
          
          $a = array('@attributes' => array('xmlns' => $this->getProperty('XMLNamespace', '')));
          
          if ($this->getProperty('ResultType') === 'JSON') {
            $xml = Array2XML::createXML($this->getProperty('XMLRootElement', 'items'),
              ($this->getProperty('XMLRowElement', '') === '' ? json_decode($this->getProperty('Result'), true, 512, JSON_THROW_ON_ERROR) : array($this->getProperty('XMLRowElement', '') => json_decode($this->getProperty('Result'), true, 512, JSON_THROW_ON_ERROR))));
          } elseif ($this->getProperty('ResultType') === 'ARRAY') {
            $xml = Array2XML::createXML($this->getProperty('XMLRootElement', 'items'),
              ($this->getProperty('XMLRowElement', '') === '' ?
                $a + $this->getProperty('Result') :
                array('@attributes' => array('xmlns' => $this->getProperty('XMLNamespace', '')),
                  $this->getProperty('XMLRowElement', '') => $this->getProperty('Result'))));
          }
          
          Tholos::$app->responseARRAY['data'] = $xml->saveXML();
        } elseif (Tholos::$app->responseType === 'PLAINTEXT') {
          
          $format = $this->getProperty('PlainTextFormat', '');
          
          function row2line($a, $format, $separator): string {
            $r = '';
            $separator = Eisodos::$utils->replace_all(Eisodos::$utils->replace_all($separator, 'NEWLINE', "\n"), 'TAB', "\t");
            foreach ($a as $key => $value) {
              $r .= ($r === '' ? '' : $separator) . Eisodos::$utils->replace_all(Eisodos::$utils->replace_all($format, 'KEY', $key, false), 'VALUE', $value, false);
            }
            
            return $r;
          }
          
          if ($this->getProperty('ResultType') === 'JSON') {
            Tholos::$app->responseARRAY['data'] = $this->getProperty('Result');
          } elseif ($this->getProperty('ResultType') === 'ARRAY' && is_array($this->getProperty('Result'))) {
            Tholos::$app->responseARRAY['data'] = '';
            if ($this->getProperty('PlainTextHeader', 'false') === 'true') {
              $headers = array();
              foreach (Tholos::$app->findChildIDsByType($this, 'TDBField') as $comp_id) {
                $headers[] = Tholos::$app->findComponentByID($comp_id)->getProperty('Label');
              }
              Tholos::$app->responseARRAY['data'] .= Eisodos::$translator->translateText(row2line($headers, $format, $this->getProperty('PlainTextRecordSeparator', ','))) . "\n";
            }
            foreach ($this->getProperty('Result') as $key => $value) {
              Tholos::$app->responseARRAY['data'] .= (is_array($value) ?
                  row2line($value, $format, $this->getProperty('PlainTextRecordSeparator', ',')) :
                  Eisodos::$utils->replace_all(Eisodos::$utils->replace_all($format, 'KEY', $key, false), 'VALUE', $value, false)) . "\n";
            }
          }
        } elseif (Tholos::$app->responseType === 'NONE') {
          NULL;
        }
        
        $this->renderedContent = '';
        Tholos::$app->eventHandler($this, 'onAfterRender');
        
        Tholos::$app->trace('END', $this);
      }
      
      return $this->renderedContent;
    }
    
  }