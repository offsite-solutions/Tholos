<?php /** @noinspection DuplicatedCode SpellCheckingInspection PhpUnusedFunctionInspection NotOptimalIfConditionsInspection */
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use RuntimeException;
  use Throwable;
  
  /**
   * Tholos Component class
   *
   * TComponent is the base class of all Tholos visual components. Some descendant components like `TEdit` will directly
   * use this class and few others in need in special processing like `TTemplate` will implement their own class based
   * on TComponent.
   *
   * TComponent stores all configuration information including properties, methods and events. It also comes with a `render()`
   * method that will render the component using the underlying template engine and returns the generated component in
   * form of a HTML string.
   *
   * @package Tholos
   */
  class TComponent {
    
    /**
     * @var string Component type name
     */
    protected string $_componentType = '';
    
    /**
     * Associative array of properties of the component. Structure:
     *      name: property name - index
     *      value: Value of the component
     *      type: Type STRING|NUMBER|BOOLEAN|JSON|ARRAY|TEXT|TEMPLATE|PARAMETER|COMPONENT
     *      nodata: bool Y|N - if true the property will not be generated into hidden data structure
     *      component_id: in case of COMPONENT type, the referring component's ID
     * @var array property list of the component containing type and value
     */
    protected array $_properties = array();
    
    /**
     * Associative array of events of the component. Structure:
     *      name: event name - index
     *      value: event value - a method name
     *      type: GUI|PHP
     *      method_name: method name if event points to another component's method
     *      method_path: method path in format route.path:ComponentType
     *      value_component_id: component id of method owner
     *      value_method_id: method ID
     *      parameters: user definied parameters in JSON format - this parameter will be passed into userData argument on GUI side
     *
     * @var array event list of the component
     */
    protected array $_events = array();
    
    /**
     * @var int component's unique identifier
     */
    protected int $_id;
    
    /**
     * @var int|null parent component's ID
     */
    protected int|null $_parent_id;
    
    /**
     * @var bool whether the component has been initialized in the past, as we only want to initialize components once
     */
    protected bool $initialized = false;
    
    /**
     * @var string contains rendered content
     */
    public string $renderedContent = '';
    
    /**
     * @var bool this component handles calls its children render method
     */
    public bool $selfRenderer = false;
    
    /**
     * Constructor
     *
     * @param string $componentType_ component type name
     * @param int $id_ Component ID
     * @param int|null $parent_id_ Parent ID - in case off TApplication component it can be null
     * @param array $properties_ list of properties defined by the component
     * @param array $events_ list of events associated with the component
     * @throws Throwable
     */
    public function __construct(string $componentType_, int $id_, int|null $parent_id_, array $properties_ = array(), array $events_ = array()) {
      
      $this->_componentType = $componentType_;
      $this->_id = $id_;
      $this->_parent_id = $parent_id_;
      $this->setProperties($properties_);
      $this->setEvents($events_);
      
    }
    
    /**
     * Retrieves the ID of the component
     *
     * @return mixed
     */
    public function getId(): int {
      
      return $this->_id;
    }
    
    /**
     * Get all properties of the component
     *
     * @return array list of available properties
     */
    public function getProperties(): array {
      
      return $this->_properties;
    }
    
    /**
     * Sets a list of properties of the component. Previously defined property list will be overridden.
     *
     * @param ?array $properties list of properties
     * @return void
     * @throws Throwable
     */
    private function setProperties(?array $properties): void {
      
      if (is_array($properties)) {
        foreach ($properties as $prop) {
          
          $n = strtolower($prop['n']);
          
          $this->_properties[$n]['value'] = $prop['v'];
          $this->_properties[$n]['type'] = $prop['t'];
          $this->_properties[$n]['nodata'] = $prop['d'];
          
          if (($prop['t'] === 'COMPONENT') && $prop['c']) {
            $this->_properties[$n]['component_id'] = $prop['c'];
            // register referencing component
            Tholos::$app->setReferencingComponent($prop['c'], $this->_id);
            Tholos::$app->instantiateComponent($prop['c']);
          } else {
            $this->_properties[$n]['component_id'] = '';
          }
        }
      }
    }
    
    /**
     * Get all events of the component
     *
     * @return array list of defined events
     */
    public function getEvents(): array {
      
      return $this->_events;
    }
    
    /**
     * Sets a list of events of the component. Previously defined event list will be overridden.
     *
     * @param mixed $events list of events
     */
    private function setEvents(mixed $events): void {
      
      if (is_array($events)) {
        foreach ($events as $event) {
          $n = strtolower($event['n']);
          $this->_events[$n]['value'] = $event['v'];
          $this->_events[$n]['type'] = $event['t'];
          $this->_events[$n]['method_name'] = $event['m'];
          $this->_events[$n]['method_path'] = $event['p'];
          $this->_events[$n]['value_component_id'] = $event['c'];
          $this->_events[$n]['value_method_id'] = $event['i'];
          $this->_events[$n]['parameters'] = $event['a'];
        }
      }
    }
    
    /**
     * Method for parsing a property value. Iteration through default value list.
     *
     * @param mixed $property_value_
     * @param mixed $notFound_
     * @return mixed
     * @see getProperty
     */
    private function parsePropertyValue2(mixed $property_value_, mixed $notFound_ = false): mixed {
      
      if ((string)$property_value_ === '') {
        return $notFound_;
      }
      
      if (strlen($property_value_) > 2 && $property_value_[0] === '@') {
        
        $prop_value = substr($property_value_, strpos($property_value_, '.') + 1);
        if (str_contains($prop_value, '|')) {
          $prop_default_value = substr($prop_value, strpos($prop_value, '|') + 1);
          if (strlen($prop_default_value) > 2 && ($prop_default_value[0] == '@' || $prop_default_value[0] == '%')) {
            $prop_default_value = $this->parsePropertyValue2($prop_default_value);
            if ($prop_default_value === false) {
              $prop_default_value = $notFound_;
            }
          }
          $prop_value = substr($prop_value, 0, strpos($prop_value, '|'));
        } else {
          $prop_default_value = $notFound_;
        }
        
        if ($property_value_[1] === '(') {
          $component_type = substr($property_value_, 2, strpos($property_value_, ')') - 2);
          if ($component_type === 'parameter') {
            return Eisodos::$parameterHandler->getParam($prop_value, $prop_default_value);
          }
          
          if ($component_type[0] === '>') {
            $components = Tholos::$app->findChildIDsByType($this, substr($component_type, 1));
            if ($components) {
              $component = Tholos::$app->findComponentByID($components[0]);
              if ($component) {
                $component_ = explode('.', $prop_value);
                if ($this->getPropertyType($component_[0]) === 'COMPONENT'
                  && Tholos::$app->findComponentByID($component->getPropertyComponentId($component_[0])) !== NULL) {
                  return Tholos::$app->findComponentByID($component->getPropertyComponentId($component_[0]))->getProperty($component_[1], $prop_default_value);
                }
                
                return $component->getProperty($prop_value, $prop_default_value);
              }
              
              return $prop_default_value;
            }
            
            return $prop_default_value;
          }
          
          $component = Tholos::$app->findParentByType($this, $component_type, 'TAction');
          if ($component) {
            $component_ = explode('.', $prop_value);
            if ($this->getPropertyType($component_[0]) === 'COMPONENT'
              && Tholos::$app->findComponentByID($component->getPropertyComponentId($component_[0])) !== NULL) {
              return Tholos::$app->findComponentByID($component->getPropertyComponentId($component_[0]))->getProperty($component_[1], $prop_default_value);
            }
            
            return $component->getProperty($prop_value, $prop_default_value);
          }
          
          return $prop_default_value;
        }
        
        $component_name = substr($property_value_, 1, strpos($property_value_, '.') - 1);
        if ($component_name === '@') {
          return '';
        }
        
        if ($component_name === 'this') {
          $component = $this;
        } elseif ($component_name === 'parent') {
          $component = Tholos::$app->findComponentByID($this->_parent_id);
        } elseif ($component_name === 'route') {
          $component = Tholos::$app->findComponentByID(Tholos::$app->getComponentRoute($this->_id));
        } else {
          $component = Tholos::$app->findComponentByName($component_name);
        }
        
        if ($component !== NULL) {
          $component_ = explode('.', $prop_value);
          if ($component->getPropertyType($component_[0]) === 'COMPONENT'
            && Tholos::$app->findComponentByID($component->getPropertyComponentId($component_[0])) !== NULL) {
            return Tholos::$app->findComponentByID($component->getPropertyComponentId($component_[0]))->getProperty($component_[1], $prop_default_value);
          }
          
          return $component->getProperty($prop_value, $prop_default_value);
        }
        
        return $prop_default_value;
      }
      
      if (strlen($property_value_) > 2 && $property_value_[0] === '%') {
        $functionName = substr($property_value_, 1);
        
        return Eisodos::$templateEngine->parseTemplateText('[%funcjob=' . $functionName . '%]', array(), false);
      }
      
      return $property_value_;
    }
    
    /**
     * Parsing concatenated property value
     * @param mixed $property_value__
     * @param mixed $notFound_
     * @return mixed
     */
    private function parsePropertyValue(mixed $property_value__, mixed $notFound_ = false): mixed {
      // ha ossze van fuzve + jellel, akkor
      if ((string)$property_value__ === '') {
        return $notFound_;
      }
      
      $return = '';
      foreach (explode('++', $property_value__) as $property_value_) {
        $return .= $this->parsePropertyValue2($property_value_, '');
      }
      
      return $return;
    }
    
    /**
     * Check whether value is a kind of boolean according to configuration Tholos.BoolFalse or Tholos.BoolTrue
     * @param string $value_
     * @return string
     */
    private function castAsBoolean(string $value_): string {
      $value_ = strtolower($value_);
      $not = false;
      if ($value_ !== '' && $value_[0] === '!') {
        $value_ = mb_substr($value_, 1);
        $not = true;
      }
      if (in_array($value_, Tholos::$app->BoolFalse, false)) {
        return ($not ? 'true' : 'false');
      }
      if (in_array($value_, Tholos::$app->BoolTrue, false) || ($value_ !== '' && in_array('*', Tholos::$app->BoolTrue, false))) {
        return ($not ? 'false' : 'true');
      }
      
      return $value_;
    }
    
    /**
     * Gets a single property of the property list
     *
     * Attempts to get a property from the component's property list. When successful, matching property will be provided.
     * When property is not found value `false` or the value specified in parameter `$notFound_` will be returned.
     *
     * Special value formats:
     * `'@component_name.property_name[.property_name2]|default_value|default_value2|...' : Property value of Component under the same Action`
     * `'@(component_type).property_name[.property_name2]|default_value|default_value2|...' : Property value of the first found parent component of the specified type`
     * `'@(>component_type).property_name[.property_name2]|default_value|default_value2|...' : Property value of the first found chile component of the specified type`
     * `'@(parameter).parameter_name[.property_name2]|default_value|default_value2|...' : Parameter with parameter_name (config, input, etc) value`
     * `'@this.property_name[.property_name2]|default_value|default_value2|...' : this object's property value`
     * `'@parent[.property_name2]|default_value|default_value2|...' : this object's property value`
     * `'@route.property_name|default_value|default_value2|...' : component's route object's property value
     * `'%callback_function_name : result of a callback function`
     *
     *    where default_value can be in special format also
     *
     *  `concatenating strings: @xxx++string++@...`
     *
     * @param string $name_ Name of the property to get
     * @param mixed $notFound_ Value to return when name is not found. Defaults to `false`.
     * @param bool $parse_ Parse property value
     * @return mixed Property data or $notFound_ value when property is not found
     */
    public function getProperty(string $name_, mixed $notFound_ = false, bool $parse_ = true): mixed {
      
      $propName = strtolower($name_);
      
      if (isset($this->_properties[$propName])) {
        $prop_value = $this->_properties[$propName]['value'];
        if ($propName === 'name' && $this->getProperty('NameSuffix', '') !== '') {
          $prop_value .= '++' . $this->getProperty('NameSuffix', '');
        }
        $prop_type = $this->_properties[$propName]['type'];
        
        if (Tholos::$app->EnableComponentPropertyCache
            && isset($this->_properties[$propName]['cached_value'])
            && $notFound_ === false) {
          return $this->_properties[$propName]['cached_value'];
        }
        
        if ($prop_type === 'PARAMETER') {
          if ($prop_value === NULL) {
            $this->_properties[$propName]['cached_value'] = '';
          } else {
            $this->_properties[$propName]['cached_value'] = Eisodos::$parameterHandler->getParam($prop_value);
          }
          
          return $this->_properties[$propName]['cached_value'];
        }
        
        if ($prop_type === 'COMPONENT') {
          $component = Tholos::$app->findComponentByID($this->_properties[$propName]['component_id']);
          if ($component === NULL) {
            $this->_properties[$propName]['cached_value'] = '';
            
            return '';
          }
          
          $this->_properties[$propName]['cached_value'] = $component->getProperty('Name');
          
          return $this->_properties[$propName]['cached_value'];
        }
        
        if (!is_array($prop_value)) {
          if ($prop_value === '@') {
            $prop_value = '';
          }
          if ((isset($this->_properties[$propName]['raw']) && $this->_properties[$propName]['raw']) && $parse_ === false) {
            $returnValue = ((@strlen($prop_value) == 0) ? $notFound_ : $prop_value);
          } else {
            $returnValue = $this->parsePropertyValue($prop_value, $notFound_);
          }
          if ($returnValue === $prop_value && !str_contains($prop_value, '@')) {
            $this->_properties[$propName]['cached_value'] = $returnValue;
          }
          if ($prop_type === 'BOOLEAN') {
            return $this->castAsBoolean($returnValue);
          }
          
          return $returnValue;
        }
        
        $this->_properties[$propName]['cached_value'] = $prop_value;
        
        return $prop_value;
        
      }
      
      return $notFound_;
    }
    
    /**
     * Gets the type of a property
     *
     * @param $name_ string property name
     * @param mixed $notFound_ Value to return when name is not found. Defaults to `false`.
     * @return mixed Property data or $notFound_ value when property type is not found
     */
    public function getPropertyType(string $name_, mixed $notFound_ = false): mixed {
      
      $propName = strtolower($name_);
      if (isset($this->_properties[$propName])) {
        return $this->_properties[$propName]['type'];
      }
      
      return $notFound_;
    }
    
    /**
     * Gets the component ID assigned to a property, if exists
     *
     * @param string $name_
     * @param mixed $notFound_ Value to return when there is no component ID bound to the propery. Defaults to `false`.
     * @return mixed Property data or $notFound_ value when compionent ID is not found
     */
    public function getPropertyComponentId(string $name_, mixed $notFound_ = false): mixed {
      
      $propName = strtolower($name_);
      if (isset($this->_properties[$propName])) {
        return ($this->_properties[$propName]['component_id'] === '' ? $notFound_ : $this->_properties[$propName]['component_id']);
      }
      
      return $notFound_;
    }
    
    /**
     * Gets all property names assigned to the component
     *
     * @return array list of property names
     */
    public function getPropertyNames(): array {
      return array_keys($this->getProperties());
    }
    
    /**
     * Sets a component property
     *
     * Assigns a value to a component property. When property type is `COMPONENT`, the referenced component's ID
     * will also be assigned.
     *
     * @param string $name_ name of the component property
     * @param mixed $value_ value of the component property
     * @param string $type_ component property type, defaults to `STRING`
     * @param string $value_component_id_ when property type is `COMPONENT`, referenced component's ID will be required. Otherwise it's an empty string.
     * @param bool $raw_ value is a raw value, do not parse
     */
    public function setProperty(string $name_, mixed $value_, string $type_ = 'STRING', string $value_component_id_ = '', bool $raw_ = false): void {
      
      $name_ = strtolower($name_);
      
      if (!isset($this->_properties[$name_])) {
        $this->_properties[$name_]['value'] = $value_;
        $this->_properties[$name_]['type'] = $type_;
        $this->_properties[$name_]['component_id'] = $value_component_id_;
      } else {
        $this->_properties[$name_]['value'] = $value_;
        if ($this->_properties[$name_]['type'] === 'COMPONENT') {
          $this->_properties[$name_]['component_id'] = $value_component_id_;
        }
        if (isset($this->_properties[$name_]['cached_value'])) {
          unset ($this->_properties[$name_]['cached_value']);
        }
      }
      $this->_properties[$name_]['raw'] = $raw_;
    }
    
    /**
     * Gets a single event of the component's event list
     *
     * Attempts to get a event from the component's event list. When successful, matching event will be provided.
     * When event is not found value `false` or the value specified in parameter `$notFound_` will be returned.
     * @param string $name_ Name of the event to get
     * @param mixed $notFound_ Value to return when name is not found. Defaults to false.
     * @return bool|string Event data or $notFound_ value when event is not found
     */
    public function getEvent(string $name_, mixed $notFound_ = false): bool|string {
      
      $eventName = strtolower($name_);
      if (array_key_exists($eventName, $this->_events)) {
        return $this->_events[$eventName]['value'];
      }
      
      return $notFound_;
    }
    
    /**
     * Gets component type as name
     *
     * @return string Name of the component type
     */
    public function getComponentType(): string {
      
      return $this->_componentType;
    }
    
    protected function getFullRenderName(): string {
      
      return Tholos::$app->renderID . '_' . $this->getProperty('Name');
    }
    
    /**
     * Generates `data-` type of properties for the hidden field to be displayed
     *
     * Apart from the `data-` values based on component properties, few extra properties like component's route, type and
     * the referenced component's ID will also be generated.
     *
     * @return string List of of data params
     * @throws Throwable
     */
    protected function generateDataValues(): string {
      
      //Tholos::$logger->debug('GD-Start',$this);
      
      $self_route = Tholos::$app->getComponentRouteActionFromIndex($this->_id);
      
      $this->setProperty('ID', $this->getFullRenderName());
      
      $return = '';
      foreach ($this->_properties as $key => $prop) {
        if (!isset($prop['nodata']) || $prop['nodata'] !== 'Y') {
          if (!is_array($this->getProperty($key, ''))) {
            $return .= 'data-' . $key . '="' . str_replace('"', '&quot;', $this->getProperty($key, '')) . '" ';
          }
          if ($prop['type'] === 'COMPONENT' && $prop['component_id']) {
            $component_route = Tholos::$app->getComponentRouteActionFromIndex($prop['component_id']);
            if ($self_route !== $component_route) {
              $return .= 'data-' . $key . 'Route="' . $component_route . '" ';
            }
          }
        }
      }
      // generate TDataParam
      $a = array();
      foreach (Tholos::$app->findChildIDsByType($this, 'TDataParameter') as $id) {
        $component = Tholos::$app->findComponentByID($id);
        if (!$component) {
          throw new RuntimeException('Could not find component by Id: ' . $id);
        }
        $a[$component->getProperty('ParameterName', '')] = $component->getProperty('Value', '');
      }
      
      if (count($a)) {
        $return .= " data-dataparameters='" . json_encode($a, JSON_THROW_ON_ERROR) . "'";
      }
      
      return 'data-componenttype="' . $this->_componentType . '" ' . $return;
    }
    
    /**
     * Adds all properties as params to the scope prepended by `prop_`.
     *
     * Also generates and adds all `data-` type of values to the scope.
     * @throws Throwable
     */
    protected function generateProps(): void {
      
      //foreach (Eisodos::->getParamNames('/^prop_./') as $key) {
      //  Eisodos::->addParam($key, '');
      //}
      
      $this->setProperty('ID', $this->getFullRenderName());
      
      foreach ($this->_properties as $key => $prop) {
        $v = $this->getProperty($key, '');
        if (is_array($v)) {
          continue;
        }
        $prop_value = (str_starts_with($v, 'HTML::') ? str_replace('HTML::', '', $v) : str_replace('"', '&quot;', $v));
        Eisodos::$parameterHandler->setParam('prop_' . $key, $prop_value);
        if ($prop['type'] === 'COMPONENT' && $prop['component_id']) {
          $component_route = Tholos::$app->getComponentRouteActionFromIndex($prop['component_id']);
          Eisodos::$parameterHandler->setParam('prop_' . $key . '_route', $component_route);
        }
      }
      
      Eisodos::$parameterHandler->setParam('prop_route', Tholos::$app->getComponentRouteActionFromIndex($this->_id));
      
      Eisodos::$parameterHandler->setParam('prop_datavalues', $this->generateDataValues());
      
      // adding hardcoded properties
      
      $parent = Tholos::$app->findParentByType($this, '');
      if ($parent) {
        Eisodos::$parameterHandler->setParam('prop_parent_name', $parent->getProperty('Name'));
        Eisodos::$parameterHandler->setParam('prop_parent_id', $parent->getProperty('ID'));
      }
    }
    
    /**
     * Generates all Javascript GUI events
     */
    protected function generateEvents(): void {
      
      foreach (Eisodos::$parameterHandler->getParamNames('/^event_./') as $key) {
        Eisodos::$parameterHandler->setParam($key);
      }
      
      $this->setProperty('ID', $this->getFullRenderName());
      
      foreach ($this->_events as $key => $event) {
        $value = $event['value'];
        $jsEvent = '';
        if ($event['type'] === 'GUI') {
          if ($value) { // az ertek van megadva
            
            // a jsevent formatuma funkcionev(sender,eventData,userdata,[extra parameterek])
            $generatedParameters = "'" . $this->getProperty('ID', '') . "'," .
              "(typeof eventData !== 'undefined'?eventData:null)," .
              (trim($event['parameters']) === '' ? 'null' : trim($event['parameters']));
            
            if (str_contains($value, '(')) {
              if (strpos($value, ')') > strpos($value, '(') + 1) { // a user ilyet adott meg jsfunc(valami)
                $jsEvent = Eisodos::$utils->replace_all($value, '(', '(' . $generatedParameters . ',', false, false);
              } else { // a user ilyet adott meg jsfunc()
                $jsEvent = Eisodos::$utils->replace_all($value, '(', '(' . $generatedParameters, false, false);
              }
            } else { //
              $jsEvent = $value . '(' . $generatedParameters . ');';
            }
          } elseif ($event['value_method_id']) { // ha komponens metodusara van rakotve az esemeny
            [$target_component, $component_type] = explode(':', $event['method_path'], 2);
            $p_ = explode('.', $target_component, 3);
            $target_route_action = '/' . $p_[0] . '/' . $p_[1];
            if (count($p_) > 2) {
              $q = explode('.', $p_[2]);
              $target_component = Tholos::$app->renderID . '_' . end($q);
            }
            // if component is initiated, then get its nem with its name suffix
            // ex: useful in grids, where components rendered with the same name but different suffix
            if ($event['value_component_id']) {
              $vc = Tholos::$app->findComponentByID($event['value_component_id']);
              if ($vc) {
                $target_component = $vc->getProperty('Name');
              }
            }
            
            $jsEvent = "Tholos.eventHandler('" . $this->getProperty('ID', '') . "','" .
              $target_component . "','" .
              $component_type . "','" .
              $event['method_name'] . "','" .
              $target_route_action . "'," .
              "(typeof eventData !== 'undefined'?eventData:null)," . // feluletrol erkezo data - nem biztos, hogy letezik
              (trim($event['parameters']) === '' ? 'null' : trim($event['parameters'])) . ");";
          }
          Eisodos::$parameterHandler->setParam('event_' . $key, $jsEvent);
        }
      }
      
    }
    
    /**
     * Component's init phase
     * @throws Throwable
     */
    public function init(): void {
      
      Tholos::$logger->trace('BEGIN', $this);
      Tholos::$logger->trace('(TComponent) (ID ' . $this->_id . ') ' . $this->_componentType, $this);
      $this->initialized = true;
      Tholos::$app->eventHandler($this, 'onAfterInit');
      Tholos::$logger->trace('END', $this);
    }
    
    
    /**
     * Renders a partial - section of a component
     *
     * @param ?TComponent $sender Caller object
     * @param string $partialID Partial identifier, defines the main template: TComponentType.partial.$partialID
     * @param string $content Caller generated content which will be passed to the template in $content variable
     * @param array $parameters Parameters will be passed to the template
     * @return string Rendered template
     * @throws Throwable
     */
    public function renderPartial(?TComponent $sender, string $partialID, string $content = '', array $parameters = array()): string {
      
      if (!Tholos::$app->checkRole($this)
        || $this->getProperty('Generate', 'true') !== 'true') {
        return '';
      }
      
      if ($partialID !== '') {
        Tholos::$logger->trace('RENDERING PARTIAL (TComponent) - ' . $partialID . ' (ID ' . $this->_id . ') ' . $this->_componentType . ', SENDER: ' . ($sender === NULL ? 'null' : $sender->getProperty('Name')), $this);
        
        $this->generateProps();
        $this->generateEvents();
        
        return Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.partial.' . $partialID,
          array_merge(array('sender' => ($sender === NULL ? '' : $sender->getProperty('Name', '')),
            'content' => $content,
            'component_id' => $this->_id
          ),
            $parameters
          ),
          false);
      }
      
      return '';
    }
    
    /**
     * Renders the component
     *
     * `render()` method is responsible for rendering the HTML content of the content. It heavily relies on the underlying
     * template system to do so. When invoked it attempts to render the template called <b>/tholos/<i><component name></i>.main.template</b>.
     * A special parameter called `$content` is passed to the renderer which contains the previously rendered HTML content
     * received from the underlying component(s). `$content` value will automatically be injected into the template.
     *
     * @param ?TComponent $sender sender object
     * @param string $content Content
     * @return string Rendered template as string
     * @throws Throwable
     */
    
    public function render(?TComponent $sender, string $content): string {
      
      $this->renderedContent = '';
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      if (!Tholos::$app->checkRole($this)
        || $this->getProperty('Generate', 'true') !== 'true') {
        return '';
      }
      
      Tholos::$logger->trace('BEGIN', $this);
      Tholos::$logger->trace('(TComponent) (ID ' . $this->_id . ') ' . $this->_componentType . ', SENDER: ' . ($sender === NULL ? 'null' : $sender->getProperty('Name', '')), $this);
      
      $this->generateProps();
      $this->generateEvents();
      
      $return = Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.main',
        array('content' => $content,
          'sender' => ($sender === NULL ? '' : $sender->getProperty('Name', '')),
          'component_id' => $this->_id,
          'page_headitems' => implode("\n", Tholos::$app->getHeadItems()),
          'page_footitems' => implode("\n", Tholos::$app->getFootItems())),
        false);
      
      Tholos::$logger->trace('(TComponent) (ID ' . $this->_id . ') ' . $this->_componentType . ', SENDER: ' . ($sender === NULL ? 'null' : $sender->getProperty('Name', '')) . ', LENGTH: ' . strlen($return), $this);
      Tholos::$logger->trace('END', $this);
      
      $this->renderedContent = $return;
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return $this->renderedContent;
    }
  }
