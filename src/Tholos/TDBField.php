<?php /** @noinspection DuplicatedCode SpellCheckingInspection PhpUnusedFunctionInspection NotOptimalIfConditionsInspection */
  
  namespace Tholos;
  
  use DateTime;
  use Eisodos\Eisodos;
  use Exception;
  use RuntimeException;
  
  /**
   * TGridColumn Component class
   *
   * TGridColumn is a column in a TGrid component
   * Descendant of TGridControls.
   *
   * @package Tholos
   * @see TGrid
   */
  class TDBField extends TComponent {
    
    protected bool $valueEverSet = false;
    
    /**
     * @inheritdoc
     */
    public function __construct($componentType_, $id_, $parent_id_, $properties_ = array(), $events_ = array()) {
      
      parent::__construct($componentType_, $id_, $parent_id_, $properties_, $events_);
      // provider letrehozasa, ha a DBField mezo property volt
      Tholos::$app->instantiateComponent($parent_id_);
    }
    
    /**
     * Updates all form control values associated with this DBField component. Value will also be added to the dbvalue property.
     */
    public function propagateValue(): void {
      Tholos::$app->trace('BEGIN', $this);
      $parseValue = $this->getProperty('ParseValue', 'true');
      $refIds = Tholos::$app->findReferencingComponents($this->_id);
      foreach ($refIds as $compId) {
        $comp = Tholos::$app->findComponentByID($compId);
        if (!$comp) {
          continue;
        }
        if (($this->_id !== $comp->getPropertyComponentId('DBField')) !== false) {
          continue;
        }
        $comp->setProperty('ParseValue', $parseValue);
        $comp->setProperty('Value', $this->getProperty('Value', ''), 'STRING', '', ($parseValue === 'false'));
        $comp->setProperty('DBValue', $this->getProperty('Value', ''), 'STRING', '', ($parseValue === 'false'));
      }
      Tholos::$app->trace('END', $this);
    }
    
    public function setProperty(string $name_, $value_, string $type_ = 'STRING', string $value_component_id_ = '', bool $raw_ = false): void {
      if (strtolower($name_) === 'value') {
        $lastValue = $this->getProperty('value', '');
        $value_ = Tholos::$app->eventHandler($this, 'onSetValue', $value_);
        parent::setProperty($name_, $value_, $type_, $value_component_id_, $raw_);
        if (!$this->valueEverSet) {
          $this->valueEverSet = true;
          parent::setProperty('ValueChanged', 'false', 'BOOLEAN');
        } else {
          parent::setProperty('ValueChanged', ((string)$lastValue === (string)$value_ ? 'false' : 'true'), 'BOOLEAN');
        }
        if (($prefix = Tholos::$app->findComponentByID($this->_parent_id)->getProperty('GlobalParameterPrefix')) !== false) {
          Eisodos::$parameterHandler->setParam($prefix . $this->getProperty('Name'), $value_);
        }
      } else {
        parent::setProperty($name_, $value_, $type_, $value_component_id_, $raw_);
      }
      if (strtolower($name_) === 'dbvalue') { // setting value with formatting
        if ($value_ === '') {
          $this->setProperty('Value', $value_, 'STRING', '', $raw_);
        } elseif (in_array($this->getProperty('DataType', 'string'), ['date', 'datetime', 'time', 'datetimehm', 'timestamp'])) { // date formatting
          try {
            $dateValue_obj = DateTime::createFromFormat(Eisodos::$parameterHandler->getParam($this->getProperty('NativeDataType') . '.Format', 'Y-m-d H:i:s'),
              $value_
            );
            if ($dateValue_obj) {
              $dateValue = $dateValue_obj->format(Eisodos::$parameterHandler->getParam('PHP' . $this->getProperty('DateFormatParameter', 'datetime') . 'Format'));
            } else {
              throw new RuntimeException('Date object is false');
            }
          } catch (Exception) {
            Tholos::$app->error('Error converting date: ' . $value_ .
              ' from (' . $this->getProperty('NativeDataType') . '.Format' . ') ' . Eisodos::$parameterHandler->getParam($this->getProperty('NativeDataType') . '.Format', 'Y-m-d H:i:s') .
              ' to ' . Eisodos::$parameterHandler->getParam('PHP' . $this->getProperty('DateFormatParameter', 'datetime') . 'Format'), $this);
            $dateValue = '';
          }
          $this->setProperty('Value',
            $dateValue, 'STRING', '', $raw_
          );
        } elseif ($this->getProperty('DataType', 'string') === 'bool' && Eisodos::$parameterHandler->eq('Tholos.UseLogicalBool', 'true')
        ) {
          if (in_array($value_, [1, '1', 'Y', 'I', 'true', 't', true], true)) {
            $this->setProperty('Value', 'true', 'STRING', '', $raw_);
          } else {
            $this->setProperty('Value', 'false', 'STRING', '', $raw_);
          }
        } elseif ($this->getProperty('DataType', 'string') === 'JSON') {
          
          try {
            $json_data = json_decode($value_, true, 512, JSON_THROW_ON_ERROR);
          } catch (Exception) {
            $json_data = NULL;
          }
          if (is_array($json_data) && count($json_data) > 0) {
            foreach (Tholos::$app->findChildIDsByType($this, 'TJSONField') as $component) {
              $JSONField = Tholos::$app->findComponentByID($component);
              if (!$JSONField) {
                continue;
              }
              $fieldName_clean = mb_strtolower($JSONField->getProperty('FieldName', ''));
              // check is fieldname has prefix
              if (strpos($fieldName_clean, '.')) {
                $fieldName_clean = explode('.', $fieldName_clean, 2)[1];
              }
              
              /* @var TDBField $JSONField */
              $JSONField->setProperty('DBValue', Eisodos::$utils->safe_array_value($json_data, $fieldName_clean, $JSONField->getProperty('NullResultParameter', '')));
              $JSONField->propagateValue();
            }
          }
        } elseif (str_starts_with($value_, '.') && in_array($this->getProperty('DataType', 'string'), ['integer', 'float'])
        ) {
          $this->setProperty('Value', '0' . $value_, 'STRING', '', $raw_);
        } elseif (str_starts_with($value_, '-.') && in_array($this->getProperty('DataType', 'string'), ['integer', 'float'], false)
        ) {
          $this->setProperty('Value', '-0' . substr($value_, 1), 'STRING', '', $raw_);
        } else {
          $this->setProperty('Value', $value_, 'STRING', '', $raw_);
        }
      }
    }
  }
  