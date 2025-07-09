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
      
      Tholos::$c->addParam('CSRFEnabled', $this->getProperty('CSRFEnabled', 'false'));
      if ($this->getProperty('CSRFEnabled', 'false') == 'true') {
        if ($this->getProperty('CSRFCookieName', '') == '' && Tholos::$c->getParam('x_xsrf_token', '') == '') {
          Tholos::$app->trace('Genereting CSRF Token', $this);
          Tholos::$c->addParam('csrf_token_value', md5(Tholos::$c->getParam('Tholos_sessionID')), true);
        } else {
          Tholos::$c->addParam('csrf_token_value', Eisodos::$utils->safe_array_value($_COOKIE, $this->getProperty('CSRFCookieName', ''), '', true), true);
        }
        Tholos::$c->addParam('csrf_header_name', $this->getProperty('CSRFHeaderName', 'anti-csrf-token'), true);
      }
      Tholos::$app->regenerateJSInit($this);
      
      if (Eisodos::$parameterHandler->neq('TholosProxy:LoginID', '')) {
        Eisodos::$parameterHandler->setParam('LoginID', Eisodos::$parameterHandler->getParam('TholosProxy:LoginID'));
      }
      
      // check if new login exists
      if ($this->getProperty('NewLoginHeader')) {
        if (!function_exists('apache_request_headers')) {
          $ApacheRequestHeaders = $this->apache_request_headers();
        } else {
          $ApacheRequestHeaders = apache_request_headers();
        }
        // new login
        if (Eisodos::$utils->safe_array_value($ApacheRequestHeaders, $this->getProperty('NewLoginHeader', ''), '', true) != ''
          && Eisodos::$utils->safe_array_value($ApacheRequestHeaders, $this->getProperty('NewLoginHeader', ''), '', true) != Eisodos::$parameterHandler->getParam('LoginID', '')
        ) {
          Tholos::$app->trace('New login detected: ' . Eisodos::$utils->safe_array_value($ApacheRequestHeaders, $this->getProperty('NewLoginHeader', ''), '', true), $this);
          $this->login(Eisodos::$utils->safe_array_value($ApacheRequestHeaders, $this->getProperty('NewLoginHeader', ''), '', true));
          Eisodos::$parameterHandler->setParam('Logged_In_User_name', Eisodos::$utils->safe_array_value($ApacheRequestHeaders, $this->getProperty('UsernameHeader', ''), '', true), true);
        }
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
     * @param bool $rootLevel_
     * @return bool
     */
    public function checkRole($functionCode_, bool $throwException_ = false, bool $rootLevel_ = false): bool {
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
      
      if ($rootLevel_
        && !$hasRole
        && Eisodos::$parameterHandler->neq('IsAJAXRequest', 'T')
        && Eisodos::$parameterHandler->neq('LoginID', '')
        && $this->getProperty('AccessDeniedURL', '') !== '') { // ha nincs role-ja de be van jelentkezve
        Eisodos::$parameterHandler->setParam('REDIRECT', $this->getProperty('AccessDeniedURL', ''));
        Tholos::$app->debug('Redirect to ' . $this->getProperty('AccessDeniedURL', ''));
      }
      
      // User nincs bejelentkezve es nem AJAX hivast kuldd es van login URL megadva (sajat kezelesu a login)
      if ($rootLevel_
        && !$hasRole
        && Eisodos::$parameterHandler->neq('IsAJAXRequest', 'T')
        && Eisodos::$parameterHandler->eq('LoginID', '')
        && $this->getProperty('LoginURL', '') !== '') { // ha nincs bejelentkezve
        if (!str_contains(Eisodos::$render->currentPageURL(), $this->getProperty('LoginURL', ''))) {
          Eisodos::$render->storeCurrentURL('URLBeforeLogin');
        }
        Eisodos::$parameterHandler->setParam('REDIRECT', $this->getProperty('LoginURL', ''));
        Tholos::$app->debug('(No login) Redirect to ' . $this->getProperty('LoginURL', ''));
      }
      
      // User nincs bejelentkezve es nem AJAX hivast kuldd es nincs login URL megadva
      // - OUATH mogott vagyunk, az be van jelentkezve (mert beengedett ide), de itt valoszinuleg lejart a session
      if ($rootLevel_
        && !$hasRole
        && Tholos::$c->neq('IsAJAXRequest', 'T')
        && Tholos::$c->eq('LoginID', '')
        && $this->getProperty('SessionExpiredURL', '') != '') { // ha nincs bejelentkezve
        Tholos::$c->addParam('REDIRECT', $this->getProperty('SessionExpiredURL', ''));
        Tholos::$app->debug('(Session expired) Redirect to ' . $this->getProperty('SessionExpiredURL', ''));
      }
      
      // AJAX hivas - nincs bejelentkezve
      if ($rootLevel_
          && !$hasRole
          && Tholos::$c->eq('IsAJAXRequest', 'T')
          && Tholos::$c->eq('LoginID', '')) {
          header('HTTP/1.1 401 Unathorized');
          header('X-Tholos-Security-Info: '.$functionCode_);
          header('X-Redirect-Location: '.$this->getProperty('LoginURL', $this->getProperty('SessionExpiredURL', Eisodos::$parameterHandler->getParam('MainAddress'))));
      }
      
      // AJAX hivas - be van jelentkezve, nincs joga
      if ($rootLevel_
          && !$hasRole
          && Tholos::$c->eq('IsAJAXRequest', 'T')
          && Tholos::$c->neq('LoginID', '')) {
          Tholos::$app->debug('User no access for function ['.$functionCode_.']');
          header('HTTP/1.1 403 Forbidden');
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
      } elseif ($this->getProperty('FunctionCodesHeader')) {
        if (!function_exists('apache_request_headers')) {
          $ApacheRequestHeaders = $this->apache_request_headers();
        } else {
          $ApacheRequestHeaders = apache_request_headers();
        }
        
        if (Eisodos::$utils->safe_array_value($ApacheRequestHeaders, $this->getProperty("FunctionCodesHeader", ''), '',true) != '') {
          if ($this->getProperty('FunctionCodesHeaderType', '') == 'JSON') {
            $this->setProperty('FunctionCodes', json_decode(Eisodos::$utils->safe_array_value($ApacheRequestHeaders, $this->getProperty('FunctionCodesHeader', '',true)), NULL, 512, JSON_THROW_ON_ERROR), "ARRAY");
          } elseif ($this->getProperty('FunctionCodesHeaderType', '') == 'CSV') {
            $this->setProperty('FunctionCodes', explode(',', Eisodos::$utils->safe_array_value($ApacheRequestHeaders, $this->getProperty('FunctionCodesHeader', '',true))), "ARRAY");
          }
          Tholos::$app->trace('Function Codes loaded: ' . implode(',', $this->getProperty('FunctionCodes', [])), $this);
        } else {
          $this->setProperty('FunctionCodes', [], "ARRAY");
        }
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
    
    /**
     * @return array
     */
    private function apache_request_headers(): array {
      $arh = array();
      $rx_http = '/\AHTTP_/';
      foreach ($_SERVER as $key => $val) {
        if (preg_match($rx_http, $key)) {
          $arh_key = preg_replace($rx_http, '', $key);
          $rx_matches = explode('_', $arh_key);
          if (count($rx_matches) > 0 && strlen($arh_key) > 2) {
            foreach ($rx_matches as $ak_key => $ak_val) {
              $rx_matches[$ak_key] = ucfirst($ak_val);
            }
            $arh_key = implode('-', $rx_matches);
          }
          $arh[$arh_key] = $val;
        }
      }
      
      return ($arh);
    }
    
    /**
     * @return array
     */
    public function getHTTPRequestAuthHeaders(): array {
      if (!function_exists('apache_request_headers')) {
        $ApacheRequestHeaders = $this->apache_request_headers();
      } else {
        $ApacheRequestHeaders = apache_request_headers();
      }
      
      $return = [];
      
      if (Eisodos::$utils->safe_array_value($ApacheRequestHeaders, $this->getProperty('AuthorizationHeader', 'authorization'), '', true) != '') {
        $return[] = $this->getProperty("AuthorizationHeader", 'authorization') . ': ' . Eisodos::$utils->safe_array_value($ApacheRequestHeaders, $this->getProperty('AuthorizationHeader', 'authorization'), '', true);
      }
      
      if ($this->getProperty('ExtraHeaders', '') != '') {
        foreach (explode(',',$this->getProperty('ExtraHeaders', '')) as $headerKey) {
          if ($value = Eisodos::$utils->safe_array_value($ApacheRequestHeaders, $headerKey, '', true)) {
            $return[] = $headerKey . ': '.$value;
          }
        }
      }
      
      if ($this->getProperty('LoginIDHeader')) {
        $return[] = $this->getProperty('LoginIDHeader') . ': ' . $this->getProperty("LoginID");
      }
      
      return $return;
    }
    
    public function logout(): void {
      Eisodos::$render->logout(false);
      Eisodos::$parameterHandler->setParam('REDIRECT', $this->getProperty('LoginURL', ''));
      Tholos::$app->debug('Redirect to ' . $this->getProperty('LogoutURL', ''));
    }
    
  }
