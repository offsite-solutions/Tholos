<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use RuntimeException;
  use Throwable;
  
  /**
   * Class TRoleManager
   * @package Tholos
   */
  class TRoleManager extends TComponent {
    
    private bool $sessionInitialized = false;
    private bool $sessionInitializationInProgress = false;
    
    /**
     * @throws Throwable
     */
    public function initDBSession(): void {
      // initialize DB session
      Tholos::$app->trace('initDBSession', $this);
      
      if (!$this->sessionInitialized
        && !$this->sessionInitializationInProgress
        && $this->getPropertyComponentId('InitSessionProvider') !== false
        && $this->getProperty('DisableInitSessionProvider', 'false') === 'false') {
        Tholos::$app->trace('initDBSession.run', $this);
        $this->sessionInitializationInProgress = true;
        /* @var TStoredProcedure $component */
        $component = Tholos::$app->findComponentByID($this->getPropertyComponentId('InitSessionProvider'));
        $component->run($this);
        $this->sessionInitializationInProgress = false;
      }
      $this->sessionInitialized = true;
    }
    
    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function init(): void {
      Tholos::$app->trace('BEGIN', $this);
      Tholos::$app->trace('(' . $this->_componentType . ') (ID ' . $this->_id . ')', $this);
      
      parent::init();
      
      if (Eisodos::$parameterHandler->neq('TholosProxy:LoginID', '')) {
        Eisodos::$parameterHandler->setParam('LoginID', Eisodos::$parameterHandler->getParam('TholosProxy:LoginID'));
      }
      $this->setProperty('LoginID', Eisodos::$parameterHandler->getParam('LoginID'));
      
      $functionCodes_ = Eisodos::$parameterHandler->getParam('TRoleManager.FunctionCodes');
      if (!$functionCodes_ == '') {
        $this->setProperty('FunctionCodes', unserialize($functionCodes_, false), 'ARRAY');
      }
      
      if ($this->getPropertyComponentId('ListSource') !== false) {
        Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'))->setProperty('AutoOpenAllowed', 'false');
      } // AutoOpen-es lenne, de folosleges nyitogatni
      
      Tholos::$app->trace('Function Codes: ' . implode(',', $this->getProperty('FunctionCodes', [])), $this);
      Tholos::$app->trace('END', $this);
    }
    
    /**
     * @inheritDoc
     */
    public function setProperty(string $name_, $value_, string $type_ = 'STRING', string $value_component_id_ = '', bool $raw_ = false): void {
      if (is_array($value_) && mb_strtolower($name_) === 'functioncodes') {
        $value_ = array_map('strtoupper', $value_);
      }
      parent::setProperty($name_, $value_, $type_, $value_component_id_, $raw_);
      if (is_array($value_) && mb_strtolower($name_) === 'functioncodes') {
        Eisodos::$parameterHandler->setParam('TRoleManager.FunctionCodes', serialize($value_), true);
      }
    }
    
    /**
     * @param $functionCodeA_
     * @return string
     */
    private function checkRole_($functionCodeA_): string {
      $functionCode_ = $functionCodeA_[0];
      if ($functionCode_ === 'AND' || $functionCode_ === 'and') {
        return 'AND';
      }
      if ($functionCode_ === 'OR' || $functionCode_ === 'or') {
        return 'OR';
      }
      if ($functionCode_ === 'TRUE' || $functionCode_ === 'true') {
        return 'TRUE';
      }
      if ($functionCode_ === 'FALSE' || $functionCode_ === 'false') {
        return 'FALSE';
      }
      if ($functionCode_ === 'NOT' || $functionCode_ === 'not') {
        return '!';
      }
      if ($functionCode_ === '!') {
        return '!';
      }
      $functionCodes = $this->getProperty('FunctionCodes', array());
      if ($functionCode_[0] === '!') {
        $hasRole = !(in_array(substr($functionCode_, 1), $functionCodes, false));
      } else {
        $hasRole = in_array($functionCode_, $functionCodes, false);
      }
      
      return ($hasRole ? 'TRUE' : 'FALSE');
    }
    
    /**
     * @param $functionCode_
     * @param bool $throwException_
     * @param bool $generateRedirect_
     * @return bool
     */
    public function checkRole($functionCode_, bool $throwException_ = false, bool $generateRedirect_ = false): bool {
      if ($this->getProperty('Enabled', 'true') === 'false') {
        return (true);
      }
      if ($functionCode_ === '') {
        return (true);
      }
      if (Eisodos::$parameterHandler->neq('TholosProxy:ProxyComponentID', '')) {
        return (true);
      } // Ha Proxy-s a hívás, akkor nem kell futnia a checkrole-nak
      if ($functionCode_ === '#') {
        $hasRole = Eisodos::$parameterHandler->neq('LoginID', '');
      } // be van loginolva
      else {
        $roleString = preg_replace_callback('|[!a-zA-Z0-9_.]+|', self::class . '::checkRole_', $functionCode_); // todo ez nem biztos, h jó így ehelyett: 'self::checkRole_'
        Tholos::$app->trace($functionCode_ . ' will be evaulated as ' . $roleString, $this);
        $hasRole = @eval('return (' . $roleString . ')');
      }
      
      if ($generateRedirect_
        && !$hasRole
        && Eisodos::$parameterHandler->neq('IsAJAXRequest', 'T')
        && Eisodos::$parameterHandler->neq('LoginID', '')
        && $this->getProperty('AccessDeniedURL', '') !== '') { // ha nincs role-ja de be van jelentkezve
        Eisodos::$parameterHandler->setParam('REDIRECT', $this->getProperty('AccessDeniedURL', ''));
        Tholos::$app->trace('Redirect to ' . $this->getProperty('AccessDeniedURL', ''));
      }
      
      if ($generateRedirect_
        && !$hasRole
        && Eisodos::$parameterHandler->neq('IsAJAXRequest', 'T')
        && Eisodos::$parameterHandler->eq('LoginID', '')
        && $this->getProperty('LoginURL', '') !== '') { // ha nincs bejelentkezve
        if (!str_contains(Eisodos::$render->currentPageURL(), $this->getProperty('LoginURL', ''))) {
          Eisodos::$render->storeCurrentURL('URLBeforeLogin');
        }
        Eisodos::$parameterHandler->setParam('REDIRECT', $this->getProperty('LoginURL', ''));
        Tholos::$app->trace('Redirect to ' . $this->getProperty('LoginURL', ''));
      }
      
      if (!$hasRole && $throwException_) {
        throw new RuntimeException($this->getProperty('ErrorMessage', 'Access denied!'));
      }
      
      return ($hasRole);
    }
    
    /**
     * Rereads function codes from database
     * @throws Throwable
     */
    public function refreshFunctionCodes(): void {
      $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'));
      if (!is_null($listSource)) {
        /* @var TQuery $listSource */
        $listSource->setProperty('DisableQueryFilters', 'true');
        $listSource->setProperty('Caching', 'Disabled');
        $listSource->run($this);
        $a = array();
        foreach ($listSource->getProperty('Result') as $row) {
          $a[] = array_values($row)[0];
        }
        $this->setProperty('FunctionCodes', $a, 'ARRAY');
      } else {
        $this->setProperty('FunctionCodes', [], 'ARRAY');
      }
    }
    
    /**
     * @param $loginID
     * @throws Throwable
     */
    public function login($loginID): void {
      Tholos::$app->trace('BEGIN', $this);
      Tholos::$app->trace('Login ID: ' . $loginID, $this);
      
      Eisodos::$parameterHandler->setParam('LoginID', $loginID, true);
      $this->refreshFunctionCodes();
      
      Tholos::$app->trace('Function Codes: ' . implode(',', $this->getProperty('FunctionCodes', [])), $this);
      Tholos::$app->trace('END', $this);
    }
    
    public function getHTTPRequestAuthHeaders(): array {
      if ($this->getProperty('LoginIDHeader')) {
        return [$this->getProperty('LoginIDHeader') . ': ' . $this->getProperty('LoginID')];
      }
      
      return [];
    }
    
    public function logout(): void {
      Eisodos::$render->logout(false);
      Eisodos::$parameterHandler->setParam('REDIRECT', $this->getProperty('LoginURL', ''));
      Tholos::$app->debug('Redirect to ' . $this->getProperty('LoginURL', ''));
    }
    
  }
