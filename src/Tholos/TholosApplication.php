<?php
  /** @noinspection PhpSameParameterValueInspection */
  /** @noinspection DuplicatedCode */
  /** @noinspection SpellCheckingInspection */
  /** @noinspection PhpUnusedFunctionInspection */
  /** @noinspection NotOptimalIfConditionsInspection */
  
  namespace Tholos;
  
  use DateTime;
  use Eisodos\Abstracts\Singleton;
  use Eisodos\Eisodos;
  use Eisodos\Parsers\CallbackFunctionParser;
  use Eisodos\Parsers\CallbackFunctionShortParser;
  use Exception;
  use JsonException;
  use Mpdf\Mpdf;
  use Redis;
  use RedisException;
  use RuntimeException;
  use Throwable;
  
  /**
   * TholosApplication class
   *
   * TholosApplication class is the first class to instantiate in Tholos. Based on the provided route and action
   * application configuration will be fetched from the database and the required components (TComponent) will be
   * instantiated and initialized. TholosApplication also provides a render method for starting the page render tree.
   *
   * @author Laszlo Banfalvi <laszlo.banfalvi@offsite-solutions.com>
   * @author Attila Balog <attila.balog@offsite-solutions.com>
   * @see TComponent
   * @package Tholos
   */
  final class TholosApplication extends Singleton {
    
    /**
     * Component definitions loaded from .tcd files created by Tholos Builder Compiler.
     * ComponentDefinitions is indexed by the components unique ids. Each item is holding an associative array of the following structure:
     *   pid: parent_id of current component
     *   h:   component type path structure, ex: 'TStoredProcedure.TDataProvider.TComponent'
     *   o:   name
     *   p:   properties, JSON associative array: {"ROWSET":[{"N":"defaultaction","T":"COMPONENT","V":null,"C":null,"D":"N"},{"N":"namesuffix","T":"STRING","V":null,"C":null,"D":"N"},{"N":"functioncode","T":"STRING","V":null,"C":null,"D":"N"},{"N":"name","T":"STRING","V":"COR_USERS","C":null,"D":"N"}]}
     *        n: property name
     *        t: type
     *        v: value
     *        c: in case of component type, the component id of value
     *        d: nodata flag (Y|N), if its value is true, the current property will not be generated into hidden data structure of the component
     *   e:   events, JSON associatve array: {"ROWSET":[{"N":"onsubmitsuccess","T":"GUI","V":null,"M":"navigate","P":"DASHBOARD.index:TAction","C":2383,"I":1001,"A":null},{"N":"onsubmiterror","T":"GUI","V":"greengo_formSubmitError","M":null,"P":null,"C":null,"I":null,"A":null}]}
     *        n: event name
     *        t: type (GUI|PHP)
     *        v: value
     *        m: method name if event points to another component's method
     *        p: method path in format route.path:ComponentType
     *        c: component id of method owner
     *        i: method ID
     *        a: user definied parameters in JSON format - this parameter will be passed into userData argument on GUI side
     *
     * @var array of loaded component definitions.
     */
    private array $componentDefinitions = array();
    
    /**
     * @var array of loaded component definitions. Component definitions loaded from .tcd files created by Tholos Builder Compiler.
     */
    private array $componentCreationOrder = array();
    
    /**
     * @var int ID of the TApplication component (mandatory)
     * @see TApplication
     */
    private int $application_id;
    
    /**
     * @var TRoleManager of the TRoleManager component (optional)
     */
    public TRoleManager $roleManager;
    
    /**
     * @var int|null ID of the current TRoute component being processed. Component's name is received in tholos_route parameter. (mandatory)
     */
    public int|null $route_id = NULL;
    
    /**
     * @var int ID of the current TAction or TDataprovider component being processed. Component's name is received in tholos_action parameter. (mandatory, default is 'index')
     */
    public int|null $action_id = NULL;
    
    /**
     * @var int ID of the partially addressed component
     */
    public int $partial_id;
    
    /**
     * Render ID all rendered components name will be prefixed with this ID
     *      (ex: ID of TButton component named 'btnOK' will be asdfghjk_btnOK ($prop_ID), while its name will be kept as btnOK ($prop_name)).
     *      renderID is automatically generated in case of no tholos_renderID parameter received.
     *      renderID is always accessible from templates through the $tholos_renderID variable.
     *
     * @var string random ID of the current render process
     */
    public string $renderID = '';
    /** @var TComponent */
    public TComponent $renderer;
    
    /**
     * @var array
     */
    public array $BoolFalse = array();
    /**
     * @var array
     */
    public array $BoolTrue = array();
    
    /**
     * @var string
     */
    public string $response = '';
    /**
     * @var string
     */
    public string $responseType = '';
    /**
     * @var array = {
     *   'success' string OK|ERROR
     *   'html' string HTML response
     *   'errorcode' integer In case of error, the error codes, default 0
     *   'errormsg' string In case of error, the error message
     *   'data' string JSON data
     *   'callback' string Parseable structure by Tholos Application JS
     *   'component_name>property_name' string Used by proxied call on the server side
     * }
     */
    public array $responseARRAY = array();
    /**
     * @var string
     */
    public string $responseErrorCode = '0';
    /**
     * @var string
     */
    public string $responseErrorMessage = '';
    /** @var Mpdf */
    public Mpdf $responsePDF;
    
    /**
     * Component Type structure, in associative array:
     *      class_name: Class name - index
     *      ancestor_name: Ancestor name
     *
     * @var array
     */
    private array $componentTypes = array();
    
    /**
     * Component Type Index structure, in associative array:
     *      n: Component ID - index
     *      p: Class path (ex: TApplication.TComponent)
     *
     * @var array
     */
    private array $componentTypeIndex = array();
    
    /**
     * Component index structure, associative array:
     *      id: component ID - index
     *      pid: Parent Component ID
     *      p: Component Type ID
     *      n: Name of the component
     *      c: Tholos Component Definition (.tcd) filename (w/o extension) - used for loading compiled files (ex: COR_USERS.tcd)
     *      r: Route ID - if exists
     *      a: Action ID - if exists
     *
     * @var array
     */
    private array $componentIndex = array();
    
    /**
     * Array of all instantiated components
     *
     * @var array[] $components = {
     *  'name'        => (string) Name of the component
     *  'parent_id'   => (integer) Parent component's ID
     *  'class_name'  => (string) Class name
     *  'object'      => (TComponent) Object itself
     * }
     */
    private array $components = array();
    
    /**
     * Array of referencing components (cache), associative array:
     *      id: component ID - index
     *      array of ids: referencing component to the indexed component
     *
     * @var array
     */
    private array $refComponents = array();
    
    /** Array of components (cache), associative array:
     *      id: component ID - index
     *
     * @var array
     */
    private array $cacheFindChildIDsByType = array();
    
    /**
     * @var array Items to be included in the header of the HTML page. These are typically CSS style sheets and Javascript files.
     */
    protected array $headItems = [];
    
    /**
     * @var array Items to be included in the footer of the HTML page. These are typically CSS style sheets and Javascript files.
     */
    protected array $footItems = [];
    
    /**
     * @var array Foot items in the generated page
     */
    private array $FootItems;
    
    /** @var bool EnableComponentPropertyCache */
    public bool $EnableComponentPropertyCache = true;
    
    /** @var ?object CacheServer connection */
    private ?object $cacheServer;
    
    public TholosCallback $callback;
    
    // BEGIN Construct
    
    private function writeAccessLog(): void {
      try {
        if (Eisodos::$parameterHandler->neq('Tholos.AccessLog', '')) {
          $line = Eisodos::$parameterHandler->getParam('Tholos.AccessLog.Format');
          $line = Eisodos::$templateEngine->replaceParamInString(str_replace('%', '$', $line));
          $file = fopen(Eisodos::$parameterHandler->getParam('Tholos.AccessLog'), 'ab+');
          if ($file) {
            fwrite($file, $line . "\n");
            fclose($file);
          }
        }
      } catch (Exception $e) {
      
      }
    }
    
    /**
     *
     */
    public function generateSessionID(): void {
      Eisodos::$parameterHandler->setParam('Tholos_sessionID', uniqid(Eisodos::$parameterHandler->getParam('random'), true), true);
      Tholos::$logger->debug('Tholos_sessionID is ' . Eisodos::$parameterHandler->getParam('Tholos_sessionID'));
    }
    
    /**
     * Tholos application init
     *
     * Constructor is responsible for determining which route/action we are dealing with and instantiating
     * all components that are directly or indirectly involved in constructing the application. Directly
     * involved components are the ones that are located under the currently selected action. Indirectly
     * involved components are the ones that are referenced by other components, like data fields, data providers, etc.
     *
     * @throws Throwable Throws exception when getting application configuration fails
     */
    public function init(array $options_): void {
      
      try {
        
        $this->callback = new TholosCallback();
        
        Eisodos::$templateEngine->registerParser(new CallbackFunctionParser());
        Eisodos::$templateEngine->registerParser(new CallbackFunctionShortParser());
        
        try {
          $time = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
          if ($time) {
            Eisodos::$parameterHandler->setParam('Tholos_App_StartDate', $time->format('Y-m-d H:i:s.u'));
            Eisodos::$parameterHandler->setParam('Tholos_App_StartTime', $time->format('H:i:s.u'));
          }
        } catch (Exception $e) {
        
        }
        
        // generating Tholos Application Unique Session ID
        if (Eisodos::$parameterHandler->eq('Tholos_sessionID', '')) {
          Tholos::$app->generateSessionID();
        }
        
        // generating unique render ID for this request only
        if (Eisodos::$parameterHandler->eq('Tholos_renderID', '')) {
          $this->renderID = 'TRI' . Eisodos::$parameterHandler->getParam('random');
          Eisodos::$parameterHandler->setParam('Tholos_renderID', $this->renderID);
        } else {
          $this->renderID = Eisodos::$parameterHandler->getParam('Tholos_renderID');
        }
        
        if (Eisodos::$parameterHandler->eq('Tholos.nonce', '')) {
          Eisodos::$parameterHandler->setParam('Tholos.nonce', Eisodos::$parameterHandler->getParam('random') . Eisodos::$parameterHandler->getParam('random') . Eisodos::$parameterHandler->getParam('random'), true);
        }
        Eisodos::$parameterHandler->setParam('Tholos_nonce', Eisodos::$parameterHandler->getParam('Tholos.nonce'));
        
        $this->BoolFalse = explode(',', strtolower(Eisodos::$parameterHandler->getParam('Tholos.BoolFalse')));
        $this->BoolTrue = explode(',', strtolower(Eisodos::$parameterHandler->getParam('Tholos.BoolTrue')));
        
        Tholos::$logger->setDebugLevels(Eisodos::$parameterHandler->getParam('session_TholosDebugLevel', Eisodos::$parameterHandler->getParam('Tholos.debugLevel')));
        $debugFileName = Eisodos::$parameterHandler->getParam('session_TholosDebugToFile', Eisodos::$parameterHandler->getParam('Tholos.debugToFile'));
        $debugFileName = str_replace(array('SESSIONID', 'TIME'), array(Eisodos::$parameterHandler->getParam('_sessionid'), date('YmdHis')), $debugFileName);
        Tholos::$logger->setDebugOutputs(
          ['debugToFile' => $debugFileName,
           'debugToUrl' => Eisodos::$parameterHandler->getParam('Tholos.debugToUrl'),
          ]);
        Tholos::$logger->debug('----- Start -----', $this);
        Eisodos::$parameterHandler->setParam('TholosJSDebugLevel', Eisodos::$parameterHandler->getParam('Tholos.JSDebugLevel'));
        
        Tholos::$logger->trace('BEGIN', $this);
        
        $this->componentDefinitions = array();
        include(Eisodos::$parameterHandler->getParam('Tholos.ApplicationCacheDir') . '_tholos.init');           // loading componentTypes, componentTypeIndex
        include(Eisodos::$parameterHandler->getParam('Tholos.ApplicationCacheDir') . 'application.tcd');        // TApplication component
        if (count($this->componentDefinitions) === 0) {
          throw new RuntimeException('Application is missing!');
        }
        Tholos::$logger->trace('Component definition loaded', $this);
        $i = 0;                                                                                 // create component order
        foreach ($this->componentDefinitions as $id => $comp) {
          $this->componentCreationOrder[$id] = $i++;
        }
        Tholos::$logger->trace('Component creation order generated', $this);
        
        // Tholos::$logger->trace(print_r($this->componentDefinitions,true),$this);
        
        Eisodos::$parameterHandler->setParam('TholosComponentTypes', json_encode($this->componentTypes, JSON_THROW_ON_ERROR), true);                                     // $TholosComponentTypes is used in GUI (tholos_application.js)
        Eisodos::$parameterHandler->setParam('TholosApplicationInit', Eisodos::$templateEngine->getTemplate('tholos/application.jsinit', array(), false), true);  // application initiaclization javascript
        
        $this->application_id = $this->findComponentIDByTypeFromIndex('TApplication');                                                    // instantiating TApplication
        $this->instantiateComponent($this->application_id, false);
        Tholos::$logger->debug('TApplication (' . $this->application_id . ') instantiated', $this);
        
        if ($this->findComponentIDByTypeFromIndex('TRoleManager', $this->application_id)) {                                                // Instantiating TRoleManager if exists
          $component = $this->instantiateComponent($this->findComponentIDByTypeFromIndex('TRoleManager', $this->application_id));
          if ($component) {
            $this->roleManager = $component;
          }
          Tholos::$logger->debug('TRoleManager instantiated', $this);
        }
        
        $this->route_id = $this->findComponentIDByNameClassFromIndex(Eisodos::$parameterHandler->getParam('tholos_route', 'index'), 'TRoute');            // Finding corresponding TRoute component
        if (!$this->route_id) {
          http_response_code(404);
          Eisodos::$render->Response = '';
          Eisodos::$templateEngine->addToResponse('Page not found');
          Eisodos::$render->finishRaw();
          exit;
        }
        $this->instantiateComponent($this->route_id, false);
        Tholos::$logger->debug('TRoute (' . $this->route_id . ') instantiated', $this);
        
        $this->action_id = $this->findComponentIDByNameClassFromIndex(Eisodos::$parameterHandler->getParam('tholos_action'), '*.TDataProvider', $this->route_id); // Finding corresponding TAction or TDataprovider component within TRoute
        if (!$this->action_id) {
          $this->action_id = $this->getActionId('TAction');
        }
        $this->instantiateComponent($this->action_id);                                                                              // Instantiating TAction component
        Tholos::$logger->debug('TAction (' . $this->action_id . ') instantiated', $this);
        
        // reorder by creation order
        Tholos::$logger->trace('Reordering components', $this);
        uksort($this->components, function ($cid1, $cid2) {
          return ($this->componentCreationOrder[$cid1] - $this->componentCreationOrder[$cid2]);
        });
        
        if (Eisodos::$parameterHandler->neq('tholos_partial', '')) {
          $partial = $this->findChildByName($this->findComponentByID($this->action_id), Eisodos::$parameterHandler->getParam('tholos_partial'));
          if ($partial !== NULL) {
            $this->partial_id = $this->findComponentId($partial);
          }
        }
        
        try {
          $time2 = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
          if ($time2) {
            Eisodos::$parameterHandler->setParam('Tholos_App_InitDate', $time2->format('Y-m-d H:i:s.u'));
            Eisodos::$parameterHandler->setParam('Tholos_App_InitTime', $time2->format('H:i:s.u'));
          }
        } catch (Exception $e) {
        
        }
        
        Tholos::$logger->trace('END', $this);
        
      } catch (Exception $e) {
        Tholos::$logger->trace('ERROR', $this);
        Tholos::$logger->writeErrorLog($e);
        Tholos::$logger->trace('END', $this);
        throw $e;
      }
    }
    
    /**
     * @param ?TComponent $sender
     * @return void
     */
    public function regenerateJSInit(?TComponent $sender): void {
      Eisodos::$parameterHandler->setParam("TholosApplicationInit", Eisodos::$templateEngine->getTemplate("tholos/application.jsinit", array(), false), true);  // application initiaclization javascript
    }
    
    /**
     * @param int $componentID_ Loading definition file for the given component ID.
     */
    private function loadDefinitionFile(int $componentID_): void {
      
      if (!array_key_exists($componentID_, $this->componentDefinitions)) {
        
        Tholos::$logger->trace('BEGIN');
        Tholos::$logger->trace('ID: ' . $componentID_ . ' - ' . $this->componentIndex[$componentID_]['c'], $this);
        require Eisodos::$parameterHandler->getParam('Tholos.ApplicationCacheDir') . $this->componentIndex[$componentID_]['c'] . '.tcd';
        $i = 0;
        foreach ($this->componentDefinitions as $id => $comp) {
          $this->componentCreationOrder[$id] = $i++;
        }
        Tholos::$logger->trace('END', $this);
        
      }
    }
    
    /**
     * Instantiates components starting from a component ID
     *
     * @param integer $componentID_ Component ID to start instantiating from. Preferrably a TAction component.
     * @param bool $createChildren_ Create component children by default
     * @return TComponent|TRoleManager|null The instantiated object
     * @throws JsonException
     * @throws Throwable
     * @see TAction
     */
    public function instantiateComponent(int $componentID_, bool $createChildren_ = true): TComponent|TRoleManager|null {
      
      if (!isset($this->componentDefinitions[$componentID_])) {
        $this->loadDefinitionFile($componentID_);
      }
      
      if (isset($this->components[$componentID_]) && !isset($this->components[$componentID_]['object'])) {
        return NULL;
      } // korkoros gyerek-szulo kereszthivatkozas megelozese
      
      if (isset($this->components[$componentID_]['object'])) {
        if ($createChildren_
          && !($this->components[$componentID_]['class_name'] === 'TAction'
            && (string)$componentID_ !== (string)$this->action_id)
        ) {
          foreach ($this->componentDefinitions as $child) {
            if ((string)$child['pid'] === (string)$componentID_) {
              $this->instantiateComponent($child['id']);
            }
          }
        }
        
        return $this->components[$componentID_]['object'];
      }
      
      Tholos::$logger->trace($this->componentDefinitions[$componentID_]['o'], $this);
      
      $component = $this->componentDefinitions[$componentID_];
      
      $this->components[$component['id']] = array();
      $this->components[$component['id']]['name'] = mb_strtolower($component['o']);
      $this->components[$component['id']]['parent_id'] = $component['pid'];
      $this->components[$component['id']]['class_name'] = explode('.', $this->componentTypeIndex[$component['t']]['h'])[0];
      
      $mainClass = '';
      foreach (explode('.', $this->componentTypeIndex[$component['t']]['h']) as $class) {
        if ($mainClass === '') {
          $mainClass = $class;
        }
        $class = Tholos::THOLOS_CLASS_PREFIX . $class;
        if (class_exists($class)) {
          $this->components[$component['id']]['object'] =
            new $class($mainClass,
              $component['id'],
              $component['pid'],
              json_decode($component['p'], true, 512, JSON_THROW_ON_ERROR),
              json_decode($component['e'], true, 512, JSON_THROW_ON_ERROR));
          break;
        }
      }
      
      // create children
      if ($createChildren_
        && !($this->components[$component['id']]['class_name'] === 'TAction'
          && (string)$component['id'] !== (string)$this->action_id)
      ) {
        foreach ($this->componentDefinitions as $child) {
          if ($child['pid'] === $component['id']) {
            $this->instantiateComponent($child['id']);
          }
        }
      }
      
      return $this->components[$componentID_]['object'];
    }
    
    /**
     * Gets the action ID of the TAction component associated with the current URL
     *
     * @param string $rootType
     * @return bool|string TAction component's ID
     * @throws Throwable when no action is found
     */
    private function getActionId(string $rootType): bool|string {
      
      try {
        Tholos::$logger->trace('BEGIN', $this);
        
        $action_id = $this->findComponentIDByNameClassFromIndex(Eisodos::$parameterHandler->getParam('tholos_action'), 'TAction', $this->route_id);
        if (!$action_id && $rootType === 'TAction') {
          $action_id = array_search('defaultaction', array_column(json_decode($this->componentDefinitions[$this->route_id]['p'], true, 512, JSON_THROW_ON_ERROR), 'n'), NULL)['c']; // TODO-BAXi
        }
        
        if (!$action_id) {
          throw new RuntimeException('No Tholos Action object found!');
        }
        Tholos::$logger->trace('Action ID: ' . $action_id, $this);
        Tholos::$logger->trace('END', $this);
        
        return $action_id;
      } catch (Exception $e) {
        Tholos::$logger->trace('ERROR', $this);
        Tholos::$logger->writeErrorLog($e);
        Tholos::$logger->trace('END', $this);
        throw $e;
      }
    }
    
    // END Construct
    
    // BEGIN Object locators
    
    /**
     * Finds and returns a TComponent or descendant object by name
     *
     * @param string $name_ Component name to find
     * @param string $class_name_ Class name of the component
     * @return TComponent|null TComponent object
     * @see TComponent
     */
    public function findComponentByName(string $name_, string $class_name_ = ''): ?TComponent {
      
      $name_ = strtolower($name_);
      foreach ($this->components as $component) {
        if ($component['name'] === $name_ && ($class_name_ === '' || is_a($component['object'], Tholos::THOLOS_CLASS_PREFIX . $class_name_))) {
          return $component['object'];
        }
      }
      
      return NULL;
    }
    
    /**
     * Finds and returns array of TComponents by its class. Class name can not be wild carded.
     *
     * @param string $class_name_ Class name of the component
     * @return TComponent[]
     * @see TComponent
     */
    public function findComponentsByClass(string $class_name_ = ''): array {
      $a = array();
      foreach ($this->components as $component) {
        if (is_a($component['object'], Tholos::THOLOS_CLASS_PREFIX . $class_name_)) {
          $a[] = $component['object'];
        }
      }
      
      return $a;
    }
    
    /**
     * Set a component ($value_component_id_) referenced by aonther component ($component_id_)
     *
     * @param integer $value_component_id_
     * @param integer $component_id_
     */
    public function setReferencingComponent(int $value_component_id_, int $component_id_): void {
      $this->refComponents[$value_component_id_][] = $component_id_;
    }
    
    /**
     * Finds all components that are referencing a given components through an object property
     *
     * @param string $component_id_ Component ID being referred
     * @return array Array of referencing component ID's
     */
    public function findReferencingComponents(string $component_id_): array {
      
      return $this->refComponents[$component_id_] ?? array();
    }
    
    /**
     * Get a component object by ID
     *
     * @param int|string $id_ Component ID of the object to find
     * @return TComponent|null Component object
     */
    public function findComponentByID(int|string $id_): ?TComponent {
      if ((string)$id_ !== '') {
        return @$this->components[$id_]['object'];
      }
      
      return NULL;
    }
    
    /**
     * Get a component ID by providing the TComponent object
     *
     * @param TComponent $component_ Component to find
     * @return bool|string Component ID or `false` when not found
     */
    protected function findComponentId(TComponent $component_): bool|string {
      
      foreach ($this->components as $id => $component) {
        if ($component['object'] === $component_) {
          return $id;
        }
      }
      
      return false;
    }
    
    /**
     * Gets a component's first parent component of a certain type
     *
     * `findParentByType` will find and return the first object relative to an object of a certain type. It can be used
     * for locating the first container of a leaf object or a form it is embedded into. Will return `false` when no
     * match found ie. the search hits the `TAction` component in the tree.
     *
     * @param TComponent $component_ Component to start the search from
     * @param string $type_ Component type to search for
     * @param string $root_type Root object type where the search terminates
     * @return bool|TComponent Component object that matches the search or `false` when not found
     */
    public function findParentByType(TComponent $component_, string $type_, string $root_type = 'TApplication'): TComponent|bool {
      
      $component_id_ = $this->findComponentId($component_);
      if ($component_id_
        && (string)$this->components[$component_id_]['parent_id'] !== ''
        && array_key_exists($this->components[$component_id_]['parent_id'], $this->components)
      ) {
        
        if ($type_ === '' || $this->components[$this->components[$component_id_]['parent_id']]['object']->getComponentType() === $type_) {
          return $this->components[$this->components[$component_id_]['parent_id']]['object'];
        }
        
        if ($this->components[$this->components[$component_id_]['parent_id']]['object']->getComponentType() === $root_type) {
          return false;
        }
        
        return $this->findParentByType($this->components[$this->components[$component_id_]['parent_id']]['object'], $type_, $root_type);
      }
      
      return false;
    }
    
    /**
     * Finds all child component ID's of a certain type
     *
     * @param TComponent $component_ Parent Component object
     * @param string $type_ Type of the child objects to look for
     * @return array List of child ID's that matches the query
     */
    public function findChildIDsByType(TComponent $component_, string $type_): array {
      
      $result = array();
      $component_id_ = $this->findComponentId($component_);
      
      if (array_key_exists($component_id_ . ':' . $type_, $this->cacheFindChildIDsByType)) {
        return $this->cacheFindChildIDsByType[$component_id_ . ':' . $type_];
      }
      
      foreach ($this->components as $id => $component) {
        if ((string)$component['parent_id'] === (string)$component_id_
          && ($component['class_name'] === $type_ || is_a($component['object'], Tholos::THOLOS_CLASS_PREFIX . $type_))
        ) {
          $result[] = $id;
        }
      }
      
      $this->cacheFindChildIDsByType[$component_id_ . ':' . $type_] = $result;
      
      return $result;
    }
    
    /**
     * Finds a child object by its name
     *
     * @param TComponent $component_ Parent component
     * @param string $name_ Child component's name
     * @return TComponent|null
     */
    public function findChildByName(TComponent $component_, string $name_): ?TComponent {
      $found = NULL;
      $parent_id = $this->findComponentId($component_);
      foreach ($this->components as $component) {
        if ($component['object']->getProperty('Name', '') === $name_) {
          return $component['object'];
        }
        
        if ((string)$component['parent_id'] === (string)$parent_id) {
          $found = $this->findChildByName($component['object'], $name_);
        }
        if ($found !== NULL) {
          return $found;
        }
      }
      
      return $found;
    }
    
    // END Object locators
    
    // Component Index related methods
    
    /**
     * Finds the first component of a certain name, component class and parent ID in the cache
     *
     * @param string $objectName_ Name of the component
     * @param string $componentType_ Component type to search for, eg. TAction, can be wildcarded: *.TDataprovider
     * @param null $parentId_ Parent ID of the component. When NULL, no relationship will be checked
     * @return bool|string Matching component's ID or `false` when no match found
     */
    public function findComponentIDByNameClassFromIndex(string $objectName_, string $componentType_, $parentId_ = NULL): bool|string {
      
      foreach ($this->componentIndex as $_component) {
        if ((($parentId_ === NULL) || ((string)$_component['pid'] === (string)$parentId_))
          && ($_component['n'] === $objectName_)
          && (explode('.', $this->componentTypeIndex[$_component['p']]['h'])[0] === $componentType_
            || (str_contains($componentType_, '*')
              && str_contains($this->componentTypeIndex[$_component['p']]['h'], str_replace('*', '', $componentType_)))
          )
        ) {
          return $_component['id'];
        }
      }
      
      return false;
    }
    
    /**
     * Finds the first component of a certain type and parent ID in the cache
     *
     * @param string $componentType_ Component type to search for, eg. TApplication
     * @param null $parentId_ Parent ID of the component. When NULL, no relationship will be checked
     * @return bool|string Matching component's ID or `false` when no match found
     */
    private function findComponentIDByTypeFromIndex(string $componentType_, $parentId_ = NULL): bool|string {
      foreach ($this->componentIndex as $_component) {
        if (explode('.', $this->componentTypeIndex[$_component['p']]['h'])[0] === $componentType_) {
          if (($parentId_ === NULL) || ((string)$_component['pid'] === (string)$parentId_)) {
            return $_component['id'];
          }
        }
      }
      
      return false;
    }
    
    /**
     * Gives back the Route/Action path of a component from Component Index. ex: /COR_USERS/index/
     *
     * @param integer $component_id_ Component ID
     * @return string /Route/Action/ path of the component
     */
    public function getComponentRouteActionFromIndex(int $component_id_): string {
      $route_id = $this->componentIndex[$component_id_]['r'];
      if ($route_id) {
        $route = $this->componentIndex[$route_id]['n'];
      } else {
        $route = '';
      }
      $action_id = $this->componentIndex[$component_id_]['a'];
      if ($action_id) {
        $action = $this->componentIndex[$action_id]['n'];
      } else {
        $action = '';
      }
      
      return '/' . $route . '/' . $action . '/';
    }
    
    /**
     * Gives back the Route component of a component from Component Index.
     *
     * @param integer $component_id_ Component ID
     * @return integer Index of the component
     */
    public function getComponentRoute(int $component_id_): int {
      return $this->componentIndex[$component_id_]['r'];
    }
    
    // END Application cache methods
    
    // BEGIN Init methods
    
    /**
     * Initialization runner
     *
     * Init phase occurs between the construct and run phases. It walks through all existing components and calls
     * their `init()` method.
     *
     */
    public function initComponents(): void {
      
      foreach ($this->components as $component_id => $component) {
        $this->components[$component_id]['object']->init();
      }
    }
    
    // END Init methods
    
    // BEGIN AutoOpen methods
    
    /**
     * Auto opens TDataProvider components before the rendering phase
     * @throws Throwable
     */
    private function autoOpen(): void {
      
      foreach ($this->findComponentsByClass('TDataProvider') as $object) {
        /* @var TDataProvider $object */
        $object->autoOpen();
      }
    }
    
    // END AutoOpen methods
    
    // BEGIN Role Management functions
    
    /**
     * Checks if a component's FunctionCode parameter is exists in the loaded Functions list.
     *
     * @param TComponent $sender_ Sender object
     * @param bool $throwException_ In case of no access right exception is thrown
     * @param bool $rootLevel_
     * @return bool
     * @throws Exception
     */
    public function checkRole(TComponent $sender_, bool $throwException_ = false, bool $rootLevel_ = false): bool {
      if (!isset($this->roleManager)) {
        return true;
      }
      $functionCode = $sender_->getProperty('FunctionCode', '');
      if ($functionCode === '') {
        return true;
      }
      $ret = $this->roleManager->checkRole($functionCode, $throwException_, $rootLevel_);
      if (!$ret) {
        Tholos::$logger->trace('checkRole() returned with false. FunctionCode: ' . $functionCode);
      }
      
      return $ret;
    }
    
    // END Role Management functions
    
    // BEGIN Rendering methods
    
    /**
     * Return header items to be included in the HTML header
     *
     * @return array List of all header items
     */
    public function getHeadItems(): array {
      
      return $this->headItems;
    }
    
    /**
     * Sets header items to be included in the HTML header
     *
     * @param array $headItems List of header items
     */
    public function setHeadItems(array $headItems): void {
      
      $this->headItems = $headItems;
    }
    
    /**
     * Add header items to the already existing list of items to be included in the HTML header
     *
     * @param array $headItems List of header items to add
     */
    public function addHeadItems(array $headItems): void {
      
      $this->headItems = array_unique(array_merge($this->headItems, $headItems), SORT_REGULAR);
    }
    
    /**
     * Return footer items to be included in the HTML footer
     *
     * @return array List of all header footers
     */
    public function getFootItems(): array {
      
      return $this->footItems;
    }
    
    /**
     * Sets footer items to be included in the HTML footer
     *
     * @param array $footItems List of footer items
     */
    public function setFootItems(array $footItems): void {
      
      $this->footItems = $footItems;
    }
    
    /**
     * Add footer items to the already existing list of items to be included in the HTML footer
     *
     * @param array $footItems List of footer items to add
     */
    public function addFootItems(array $footItems): void {
      
      $this->FootItems = array_unique(array_merge($this->footItems, $footItems), SORT_REGULAR);
    }
    
    /**
     * Renders the component tree, returns rendered HTML content
     *
     * @param ?object $sender_ Sender object
     * @param integer $component_id_ Database ID of the parent component under which all child components will be rendered
     * @param bool $childOnly_ Render only the child components
     * @return string Rendered content
     * @throws Exception|Throwable
     */
    public function render(?object $sender_, int $component_id_, bool $childOnly_ = false): string {
      
      try {
        Tholos::$logger->trace('BEGIN', $this);
        Tholos::$logger->trace('Component ID = ' . $component_id_, $this);
        
        $renderThis = $this->findComponentByID($component_id_);
        if ($renderThis->selfRenderer) {
          Tholos::$logger->trace('END', $this);
          
          return $renderThis->render($sender_, '');
        }
        
        $content = '';
        foreach ($this->components as $component_id => $component) {
          if ((string)$component['parent_id'] === (string)$component_id_) {
            $content .= $this->render($sender_, $component_id);
          }
        }
        
        $this->addHeadItems(explode("\n", trim(
          Eisodos::$templateEngine->getTemplate('tholos/' . $this->components[$component_id_]['object']->getComponentType() . '.init.head', array(), false))));
        $this->addFootItems(explode("\n", trim(
          Eisodos::$templateEngine->getTemplate('tholos/' . $this->components[$component_id_]['object']->getComponentType() . '.foot.head', array(), false))));
        
        if ($childOnly_) {
          Tholos::$logger->trace('END', $this);
          
          return $content;
        }
        
        $return = $this->components[$component_id_]['object']->render($sender_, $content);
        Tholos::$logger->trace('Component ID = ' . $component_id_, $this);
        Tholos::$logger->trace('END', $this);
        
        return $return;
        
      } catch (Exception $e) {
        Tholos::$logger->trace('ERROR', $this);
        Tholos::$logger->writeErrorLog($e);
        Tholos::$logger->trace('END', $this);
        throw $e;
      }
    }
    
    /**
     * @param string $response_
     * @return string
     */
    public function cleanupRenderedHTML(string $response_): string {
      Tholos::$logger->debug('HTML Code cleanup');
      $result = '';
      foreach (explode("\n", $response_) as $line) {
        if (trim($line) !== '</>') {
          $result .= $line . "\n";
        }
      }
      
      return trim(Eisodos::$utils->replace_all($result, '</>', ''));
    }
    
    // END Render methods
    
    // BEGIN PHP event handlers
    
    /**
     * PHP type event's handler function
     *
     * @param TComponent $sender_ Sender Object
     * @param string $eventName_ Name of the event
     * @param mixed $notFound_
     * @return mixed Returns the return of the handled event or false
     */
    public function eventHandler(TComponent $sender_, string $eventName_, mixed $notFound_ = false): mixed {
      try {
        $event = $sender_->getEvent($eventName_);
        if (!$event) {
          return $notFound_;
        }
        $eventParams = explode('.', $event);
        $class = Tholos::$app->findComponentByID($this->application_id)->getProperty('Name', '') . "\\" . $eventParams[0];
        if (class_exists($class)) {
          $evt = call_user_func(array($class, 'getInstance'));
          if (method_exists($evt, $eventParams[1])) {
            return $evt->{$eventParams[1]}($sender_);
          }
        }
      } catch (Exception $e) {
        Tholos::$logger->writeErrorLog($e);
      }
      return $notFound_;
    }
    
    //
    
    // BEGIN Run
    
    /**
     * Application runner
     *
     * Tholos application runner is executed after the constructor has finished instantiating all Tholos objects.
     * It is responsible for walking the application through the initialization and render phases.
     *
     * @@throws Throwable
     */
    public function run(): void {
      
      try {
        Tholos::$logger->debug('BEGIN');
        
        Tholos::$logger->debug('Component initialization phase');
        
        $this->initComponents();
        
        if (Eisodos::$parameterHandler->getParam('Tholos.CSPEnabled', 'false') == "true") {
          header("Content-Security-Policy: script-src 'self' 'nonce-" . Eisodos::$parameterHandler->getParam('Tholos.Nonce') . "' " . Eisodos::$parameterHandler->getParam('Tholos.CSPJavascriptHosts', '') . ';' .
            " font-src 'self' " . Eisodos::$parameterHandler->getParam('Tholos.CSPFontHosts', '') . ';');
        }
        
        if (Eisodos::$parameterHandler->neq('REDIRECT', '')) {
          
          header('X-Tholos-Redirect: ' . Eisodos::$parameterHandler->getParam('REDIRECT'));
          if (Eisodos::$parameterHandler->eq('IsAjaxRequest', 'T')) {
            Eisodos::$parameterHandler->setParam('REDIRECT');
          }
          Eisodos::$render->finish(); // TRoute and TAction can abort execution in init phase by missing role
          
        } else {
          
          Tholos::$logger->debug('Auto opening dataproviders');
          
          $this->autoOpen();
          
          if (isset($this->renderer)) {
            Tholos::$logger->debug('Render phase started', $this->renderer);
            $response_ = $this->render(NULL, $this->renderer->getId());
            Tholos::$logger->debug('Render phase finished');
          } else if (Eisodos::$parameterHandler->eq('tholos_partial', '')) {
            Tholos::$logger->debug('Render phase started');
            $response_ = $this->render(NULL, $this->action_id);
            Tholos::$logger->debug('Render phase finished');
          } else {
            Tholos::$logger->debug('Partial render phase started');
            if (Eisodos::$parameterHandler->eq('responseType', '')) {
              Eisodos::$parameterHandler->setParam('responseType', 'JSON');
            }
            if (Eisodos::$parameterHandler->eq('tholos_partial', '*')) {
              $response_ = $this->render(NULL, $this->action_id);
            } else {
              $partial = $this->findChildByName($this->findComponentByID($this->action_id), Eisodos::$parameterHandler->getParam('tholos_partial'));
              if ($partial !== NULL) {
                $response_ = $this->render(NULL, $this->findComponentId($partial));
              } else {
                throw new RuntimeException('No partial (' . Eisodos::$parameterHandler->getParam('tholos_partial') . ') found in action!');
              }
            }
            $response_ = implode("\n", Tholos::$app->getHeadItems()) . $response_ . implode("\n", Tholos::$app->getFootItems());
            Tholos::$logger->debug('Partial render phase finished');
          }
          
          if (Eisodos::$parameterHandler->neq('REDIRECT', '')) {
            header('X-Tholos-Redirect: ' . Eisodos::$parameterHandler->getParam('REDIRECT'));
            if (Eisodos::$parameterHandler->eq('IsAjaxRequest', 'T')) {
              Eisodos::$parameterHandler->setParam('REDIRECT');
            }
          }
          
          if ($this->responseType !== 'BINARY' && $this->responseType !== 'CUSTOM') {
            $this->response = Tholos::$app->cleanupRenderedHTML($response_);
            
            if (($this->responseType === 'JSON' && Eisodos::$parameterHandler->eq('responseType', ''))
              || Eisodos::$parameterHandler->eq('responseType', 'JSON')) {
              header('Content-type: application/json');
              Eisodos::$render->Response = '';
              $this->responseARRAY['success'] = (($this->responseErrorCode === '' || $this->responseErrorCode === '0') ? 'OK' : 'ERROR');
              $this->responseARRAY['errormsg'] = $this->responseErrorMessage;
              $this->responseARRAY['errorcode'] = $this->responseErrorCode;
              $this->responseARRAY['html'] = $this->response;
              if (!array_key_exists('callback', $this->responseARRAY)) {
                Tholos::$app->responseARRAY['callback'] = '{}';
              }
              Eisodos::$templateEngine->addToResponse(json_encode($this->responseARRAY, JSON_THROW_ON_ERROR));
              Tholos::$logger->trace('END', $this);
              Eisodos::$render->finishRaw(true); // save session variables
            } elseif (Eisodos::$parameterHandler->eq('responseType', 'PROXY')) {
              header('Content-type: application/json');
              Eisodos::$render->Response = '';
              $this->responseARRAY['success'] = (($this->responseErrorCode === '' || $this->responseErrorCode === '0') ? 'OK' : 'ERROR');
              $this->responseARRAY['errormsg'] = $this->responseErrorMessage;
              $this->responseARRAY['errorcode'] = $this->responseErrorCode;
              $this->responseARRAY['html'] = $this->response;
              if (!array_key_exists('callback', $this->responseARRAY)) {
                Tholos::$app->responseARRAY['callback'] = '{}';
              }
              Eisodos::$templateEngine->addToResponse(json_encode($this->responseARRAY, JSON_THROW_ON_ERROR));
              Tholos::$logger->trace('END', $this);
              Eisodos::$render->finishRaw(true); // save session variables
            } elseif (($this->responseType === 'JSONDATA' && Eisodos::$parameterHandler->eq('responseType', ''))
              || Eisodos::$parameterHandler->eq('responseType', 'JSONDATA')) {
              header('Content-type: application/json');
              Eisodos::$render->Response = '';
              Eisodos::$templateEngine->addToResponse($this->responseARRAY['data']);
              Tholos::$logger->trace('END', $this);
              Eisodos::$render->finishRaw(true); // save session variables
            } elseif (($this->responseType === 'XML' && Eisodos::$parameterHandler->eq('responseType', ''))
              || Eisodos::$parameterHandler->eq('responseType', 'XML')) {
              header('Content-type: application/xml');
              Eisodos::$render->Response = '';
              Eisodos::$templateEngine->addToResponse($this->responseARRAY['data']);
              Tholos::$logger->trace('END', $this);
              Eisodos::$render->finishRaw(true); // save session variables
            } elseif (($this->responseType === 'PLAINTEXT' && Eisodos::$parameterHandler->eq('responseType', ''))
              || Eisodos::$parameterHandler->eq('responseType', 'PLAINTEXT')) {
              header('Content-type: text/plain');
              Eisodos::$render->Response = '';
              Eisodos::$templateEngine->addToResponse($this->responseARRAY['data']);
              Tholos::$logger->trace('END', $this);
              Eisodos::$render->finishRaw(true, true); // save session variables and create language tags
            } elseif (($this->responseType === 'PDF' && Eisodos::$parameterHandler->eq('responseType', ''))
              || Eisodos::$parameterHandler->eq('responseType', 'PDF')) {
              header('Content-type: application/pdf');
              Eisodos::$render->Response = '';
              $this->responsePDF->Output();
              Tholos::$logger->trace('END', $this);
              Eisodos::$render->finishRaw(true, true); // save session variables and create language tags
            } else {
              Eisodos::$templateEngine->addToResponse($this->response);
              Tholos::$logger->trace('END');
              Eisodos::$render->finish();
            }
          } elseif ($this->responseType !== 'BINARY') {
            Tholos::$logger->trace('END');
            Eisodos::$render->finishRaw(true, Eisodos::$parameterHandler->eq('Tholos.WriteLanguageFile', 'T'));
          }
        }
        
        try {
          $time3 = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
          if ($time3) {
            Eisodos::$parameterHandler->setParam('Tholos_App_FinishDate', $time3->format('Y-m-d H:i:s.u'));
            Eisodos::$parameterHandler->setParam('Tholos_App_FinishTime', $time3->format('H:i:s.u'));
          }
        } catch (Exception $e) {
        
        }
        $this->writeAccessLog();
      } catch (Exception $e) {
        Tholos::$logger->writeErrorLog($e);
        throw $e;
      }
    }
    
    /**
     * @param array $config
     * @throws Throwable
     */
    public function initResponsePDF(array $config = []): void {
      $this->responsePDF = new Mpdf(array_merge(json_decode(Eisodos::$parameterHandler->getParam('Tholos.mPDF'), true, 512, JSON_THROW_ON_ERROR), $config));
    }
    
    /** Open cache server connection
     * @throws RedisException
     */
    private function openCacheServer(): void {
      if (!isset($this->cacheServer)) {
        $this->cacheServer = new Redis();
        $this->cacheServer->connect(Eisodos::$parameterHandler->getParam('Tholos.CacheServer', 'localhost'),
          1 * Eisodos::$parameterHandler->getParam('Tholos.CachePort', '6379'),
          1.0 * Eisodos::$parameterHandler->getParam('Tholos.CacheTimeout', '0.0'));
        Tholos::$logger->trace('Redis cache connected');
      }
    }
    
    /** Close cache server connection */
    private function closeCacheServer(): void {
      if (isset($this->cacheServer) && Eisodos::$parameterHandler->eq('Tholos.CacheMethod', 'redis')) {
        $this->cacheServer->close();
        unset($this->cacheServer);
        Tholos::$logger->trace('Redis cache disconnected');
      }
    }
    
    /**
     * @param TComponent $sender
     * @param string $cacheScope_
     * @param string $cacheID_
     * @param string $partition_
     * @param string $sql_
     * @param string $sqlConflictMode_
     * @return bool|mixed|string
     * @throws Throwable
     */
    public function readCache(TComponent $sender, string $cacheScope_, string $cacheID_, string $partition_ = '', string $sql_ = '', string $sqlConflictMode_ = ''): mixed {
      
      $sender->setProperty('CacheInfo', array());
      
      if (Eisodos::$parameterHandler->eq('Tholos.CacheMethod', 'file')) {
        
        $cacheIndexFilename = Eisodos::$parameterHandler->getParam('Tholos.CacheDir') .
          '/' .
          ($cacheScope_ === 'Private' ? Eisodos::$parameterHandler->getParam('Tholos_sessionID') . '_' : '') .
          $cacheID_ .
          '.index';
        
        if (file_exists($cacheIndexFilename) && ($filesize = filesize($cacheIndexFilename))) {
          $file = fopen($cacheIndexFilename, 'rb');
          $currentCacheIndex = fread($file, $filesize);
          fclose($file);
          
          $currentCacheIndex = json_decode($currentCacheIndex, true, 512, JSON_THROW_ON_ERROR);
          
          $now = time();
          $currentTime = date('YmdHis', $now);
          
          foreach ($currentCacheIndex as $indexRow) {
            if ($partition_ === $indexRow['partition']) {
              if ($indexRow['validity'] !== ''
                && $indexRow['validity'] < $currentTime) {
                Tholos::$logger->debug($cacheID_ . ($partition_ !== '' ? '@' . $partition_ : '') . ' cache is expired');
                $sender->setProperty('CacheUsed', 'false');
                $sender->setProperty('CacheRefresh', 'true');
                
                return false;
              }
              
              if ($indexRow['sql'] !== ''
                && $sql_ !== ''
                && $indexRow['sql'] !== $sql_
                && $sqlConflictMode_ !== 'ReadCache') {
                Tholos::$logger->debug($cacheID_ . ($partition_ !== '' ? '@' . $partition_ : '') . ' SQL changed, cache not used');
                $sender->setProperty('CacheUsed', 'false');
                $sender->setProperty('CacheRefresh', ($sender->getProperty('CacheSQLConflict', 'DisableCaching') === 'RewriteCache') ? 'true' : 'false');
                
                return false;
              }
              
              $sender->setProperty('CacheInfo', $indexRow);
              break;
            }
          }
        }
        
        
        $filename = Eisodos::$parameterHandler->getParam('Tholos.CacheDir') .
          '/' .
          ($cacheScope_ === 'Private' ? Eisodos::$parameterHandler->getParam('Tholos_sessionID') . '_' : '') .
          $cacheID_ .
          ($partition_ !== '' ? '@' . $partition_ : '') .
          '.cache';
        if (file_exists($filename) && ($filesize = filesize($filename))) {
          $file = fopen($filename, 'rb');
          $content_ = fread($file, $filesize);
          fclose($file);
        } else {
          Tholos::$logger->debug($cacheID_ . ($partition_ !== '' ? '@' . $partition_ : '') . ' cache not exists');
          $content_ = 'NULL';
          $sender->setProperty('CacheUsed', 'false');
          $sender->setProperty('CacheRefresh', 'true');
        }
        
      } else if (Eisodos::$parameterHandler->getParam('Tholos.CacheMethod', 'file') === 'redis') {
        
        $cacheIndexFilename = Eisodos::$parameterHandler->getParam('Tholos.CacheDir') .
          '/' .
          ($cacheScope_ === 'Private' ? Eisodos::$parameterHandler->getParam('Tholos_sessionID') . '_' : '') .
          $cacheID_ .
          '.index';
        
        $this->openCacheServer();
        $currentCacheIndex = $this->cacheServer->get($cacheIndexFilename);
        
        if ($currentCacheIndex) {
          
          $currentCacheIndex = json_decode($currentCacheIndex, true, 512, JSON_THROW_ON_ERROR);
          
          $now = time();
          $currentTime = date('YmdHis', $now);
          
          foreach ($currentCacheIndex as $indexRow) {
            if ($partition_ === $indexRow['partition']) {
              if ($indexRow['validity'] !== ''
                && $indexRow['validity'] < $currentTime) {
                Tholos::$logger->debug($cacheID_ . ($partition_ !== '' ? '@' . $partition_ : '') . ' cache is expired');
                $sender->setProperty('CacheUsed', 'false');
                $sender->setProperty('CacheRefresh', 'true');
                
                return false;
              }
              
              if ($indexRow['sql'] !== ''
                && $sql_ !== ''
                && $indexRow['sql'] !== $sql_
                && $sqlConflictMode_ !== 'ReadCache') {
                Tholos::$logger->debug($cacheID_ . ($partition_ !== '' ? '@' . $partition_ : '') . ' SQL changed, cache not used');
                $sender->setProperty('CacheUsed', 'false');
                $sender->setProperty('CacheRefresh', ($sender->getProperty('CacheSQLConflict', 'DisableCaching') === 'RewriteCache') ? 'true' : 'false');
                
                return false;
              }
              
              $sender->setProperty('CacheInfo', $indexRow);
              break;
            }
          }
        }
        
        
        $filename = Eisodos::$parameterHandler->getParam('Tholos.CacheDir') .
          '/' .
          ($cacheScope_ === 'Private' ? Eisodos::$parameterHandler->getParam('Tholos_sessionID') . '_' : '') .
          $cacheID_ .
          ($partition_ !== '' ? '@' . $partition_ : '') .
          '.cache';
        
        $content_ = $this->cacheServer->get($filename);
        
        if (!$content_) {
          Tholos::$logger->debug($cacheID_ . ($partition_ !== '' ? '@' . $partition_ : '') . ' cache not exists');
          $content_ = 'NULL';
          $sender->setProperty('CacheUsed', 'false');
          $sender->setProperty('CacheRefresh', 'true');
        }
        
        $this->closeCacheServer();
        
      } else if (Eisodos::$parameterHandler->getParam('Tholos.CacheMethod', 'file') === 'memory') {
        $content_ = Eisodos::$parameterHandler->getParam('Tholos.Cache.' . $cacheID_, 'NULL');
      } else {
        Tholos::$logger->error('No caching configured. Caching is disabled.');
        $sender->setProperty('CacheUsed', 'false');
        
        return false;
      }
      
      try {
        $content_ = json_decode($content_, true, 512, JSON_THROW_ON_ERROR);
        if ($content_ === NULL
          || $content_ === false) {
          throw new RuntimeException('');
        }
        Tholos::$logger->debug($cacheID_ . ' read from ' . Eisodos::$parameterHandler->getParam('Tholos.CacheMethod', 'file') . ' cache');
        Tholos::$logger->debug(print_r($sender->getProperty('CacheInfo', []), true));
        $sender->setProperty('CacheUsed', 'true');
        
        return $content_;
      } catch (Exception) {
        Tholos::$logger->debug($cacheID_ . ' cache is invalid');
        $sender->setProperty('CacheUsed', 'false');
        
        return false;
      }
    }
    
    /**
     * Writes cache file in exclusive mode
     * @param string $filename_
     * @param mixed $content_
     * @param string $mode_
     * @return bool
     */
    private function writeCacheFileLock(string $filename_, mixed $content_, string $mode_): bool {
      
      $file = fopen($filename_, $mode_);
      if (flock($file, LOCK_EX)) {
        fwrite($file, $content_);
        flock($file, LOCK_UN);
        fclose($file);
        
        return true;
      }
      
      fclose($file);
      
      return false;
      
    }
    
    /**
     * Write content to cache, according to Tholos.CacheMethod configuration variable
     * @param string $filename_ Cache file name, in case of redis key
     * @param mixed $content_ Cache content
     * @param string $validity_ Validity in seconds
     * @throws Exception
     */
    private function writeCacheContent(string $filename_, mixed $content_, string $validity_ = ''): void {
      
      if (Eisodos::$parameterHandler->eq('Tholos.CacheMethod', 'file')) {
        $lockWait = 1 * Eisodos::$parameterHandler->getParam('Tholos.CacheLockWait', '100');
        $lockLoop = 0;
        $maxLockLoop = 1 * Eisodos::$parameterHandler->getParam('Tholos.CacheLockLoop', '20');
        while (!($this->writeCacheFileLock($filename_, $content_, 'wb') or $lockLoop > $maxLockLoop)) {
          Tholos::$logger->trace('Cache waits for lock');
          usleep($lockWait);
          $lockLoop++;
        }
        
        if ($lockLoop > $maxLockLoop) {
          throw new RuntimeException('Cache write error (' . $filename_ . ')');
        }
      } else if (Eisodos::$parameterHandler->eq('Tholos.CacheMethod', 'redis')) {
        $this->openCacheServer();
        if ($validity_) {
          $this->cacheServer->setex($filename_, 1 * $validity_ * 60, $content_);
        } else {
          $this->cacheServer->setex($filename_, 24 * 60 * 60 * 7, $content_);
        }
      }
      
    }
    
    /**
     * Reads back cached content
     * @param $cacheScope_
     * @param $cacheID_
     * @param $content_
     * @param string $partitionedBy_
     * @param string $validity_
     * @param string $partition_
     * @param string $sql_
     * @return bool
     * @throws Exception
     */
    public function writeCache($cacheScope_, $cacheID_, $content_, string $partitionedBy_ = '', string $validity_ = '', string $partition_ = '', string $sql_ = ''): bool {
      
      $contents_ = array();
      
      if (!is_array($content_) && ($partitionedBy_ !== '')) {
        Tholos::$logger->error($cacheID_ . ' can not be partitioned by ' . $partitionedBy_);
        
        return true;
      }
      
      $cacheIndexFilename = Eisodos::$parameterHandler->getParam('Tholos.CacheDir') .
        '/' .
        ($cacheScope_ === 'Private' ? Eisodos::$parameterHandler->getParam('Tholos_sessionID') . '_' : '') .
        $cacheID_ .
        '.index';
      
      if ($partitionedBy_ !== '') {
        if (!empty($content_)) {
          foreach ($content_ as $row) {
            if (!array_key_exists($partitionedBy_, $row)) {
              Tholos::$logger->error($partitionedBy_ . ' partition key not exists in the row');
              
              return false;
            }
            $contents_[$row[$partitionedBy_]][] = $row;
          }
        } else if ($partition_ !== '') { // write empty content
          $contents_[$partition_][] = $content_;
        }
      }
      
      $now = time();
      $currentTime = date('YmdHis', $now);
      
      if ($validity_ !== '') {
        $validityTime = date('YmdHis', $now + (1 * $validity_ * 60));
      } else {
        $validityTime = '';
      }
      
      $cacheIndex = array();
      
      if (in_array(Eisodos::$parameterHandler->getParam('Tholos.CacheMethod', 'file'), ['file', 'redis'])) {
        if ($partitionedBy_ === '') {
          
          $cacheFilename = ($cacheScope_ === 'Private' ? Eisodos::$parameterHandler->getParam('Tholos_sessionID') . '_' : '') . $cacheID_ . '.cache';
          
          $this->writeCacheContent(Eisodos::$parameterHandler->getParam('Tholos.CacheDir') . '/' . $cacheFilename, json_encode($content_, JSON_THROW_ON_ERROR), $validity_);
          
          $cacheIndex[] = array(
            'validity' => $validityTime,
            'filename' => $cacheFilename,
            'updated' => $currentTime,
            'sql' => $sql_,
            'partition' => '',
            'items' => (is_array($content_) ? count($content_) : ''));
          
        } else {
          
          // removing old cache files
          
          if (Eisodos::$parameterHandler->eq('Tholos.CacheMethod', 'file')
            && file_exists($cacheIndexFilename)
            && ($filesize = filesize($cacheIndexFilename))) {
            $file = fopen($cacheIndexFilename, 'rb');
            $currentCacheIndex = fread($file, $filesize);
            fclose($file);
            
            $currentCacheIndex = json_decode($currentCacheIndex, true, 512, JSON_THROW_ON_ERROR);
            
            foreach ($currentCacheIndex as $indexRow) {
              if ($partition_ === ''
                || ($indexRow['validity'] !== '' && $indexRow['validity'] < $currentTime)) {
                @unlink(Eisodos::$parameterHandler->getParam('Tholos.CacheDir') . '/' . $indexRow['filename']);
              } elseif ($indexRow['partition'] === $partition_) {
                assert(true);
              } else {
                $cacheIndex[] = $indexRow;
              }
            }
          }
          
          // writing cache files
          
          foreach ($contents_ as $key => $cacheContent) {
            
            if (empty($cacheContent[0])) {
              $cacheContent = [];
            }
            
            $cacheFilename = ($cacheScope_ === 'Private' ? Eisodos::$parameterHandler->getParam('Tholos_sessionID') . '_' : '') .
              $cacheID_ .
              '@' .
              $key .
              '.cache';
            
            $this->writeCacheContent(Eisodos::$parameterHandler->getParam('Tholos.CacheDir') . '/' . $cacheFilename, json_encode($cacheContent, JSON_THROW_ON_ERROR), $validity_);
            
            $cacheIndex[] = array(
              'validity' => $validityTime,
              'filename' => $cacheFilename,
              'updated' => $currentTime,
              'sql' => '',
              'partition' => $key,
              'items' => count($cacheContent));
          }
        }
        
        // writing cache index with validity
        
        $this->writeCacheContent($cacheIndexFilename, json_encode($cacheIndex, JSON_THROW_ON_ERROR), $validity_);
        $this->closeCacheServer();
        
      } else if (Eisodos::$parameterHandler->getParam('Tholos.CacheMethod', 'file') === 'memory') {
        if ($cacheScope_ !== 'Private') {
          Tholos::$logger->error('In memory caching can not be set to global. Use memcached or file instead!');
        }
        Eisodos::$parameterHandler->setParam('Tholos.Cache.' . $cacheID_, json_encode($content_, JSON_THROW_ON_ERROR), true);
      } else {
        Tholos::$logger->error('No caching configured. Caching is disabled.');
        
        return false;
      }
      
      Tholos::$logger->debug($cacheID_ . ' cache written to ' . Eisodos::$parameterHandler->getParam('Tholos.CacheMethod', 'file'));
      
      return true;
    }
  }
  