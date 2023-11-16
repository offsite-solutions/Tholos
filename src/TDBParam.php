<?php
  
  namespace Tholos;
  
  use DateTime;
  use Eisodos\Eisodos;
  use Exception;
  use RuntimeException;
  use Throwable;

  /**
   * Class TDBParam
   * @package Tholos
   */
  class TDBParam extends TComponent {
    
    protected function parameterTransform($text_, $transformation_) {
      if (!$transformation_ || $transformation_ === 'NONE') {
        return $text_;
      }
      if ($transformation_ === 'UPPERCASE') {
        return mb_strtoupper($text_);
      }
      if ($transformation_ === 'LOWERCASE') {
        return mb_strtolower($text_);
      }
      if ($transformation_ === 'CAMELCASE') {
        $i = array('-', '_');
        $text_ = preg_replace('/([a-z])([A-Z])/', "\\1 \\2", $text_);
        $text_ = preg_replace('@[^a-zA-Z0-9\-_ ]+@', '', $text_);
        $text_ = str_replace($i, ' ', $text_);
        $text_ = str_replace(' ', '', ucwords(strtolower($text_)));
        $text_ = strtolower($text_[0]) . substr($text_, 1);
        
        return $text_;
      }
      if ($transformation_ === 'UNCAMELCASE') {
        $text_ = preg_replace('/([a-z])([A-Z])/', "\\1_\\2", $text_);
        $text_ = strtolower($text_);
        
        return $text_;
      }
      
      return $text_;
    }
    
    /**
     *
     * @throws Throwable
     */
    public function init(): void {
      
      Tholos::$app->trace('BEGIN', $this);
      Tholos::$app->trace('(' . $this->_componentType . ') (ID ' . $this->_id . ')', $this);
      
      if ($this->getProperty('MDB2DataType', '') === '') {
        switch ($this->getProperty('DataType', '')) {
          case 'integer':
            $this->setProperty('MDB2DataType', 'integer');
            break;
          case 'date':
            $this->setProperty('MDB2DataType', 'date');
            break;
          case 'datetimehm':
          case 'timestamp':
          case 'datetime':
            $this->setProperty('MDB2DataType', 'timestamp');
            break;
          case 'time':
            $this->setProperty('MDB2DataType', 'time');
            break;
          case 'float':
            $this->setProperty('MDB2DataType', 'float');
            break;
          default :
            $this->setProperty('MDB2DataType', 'text');
            break;
        }
        Tholos::$app->trace('Setting MDB2DataType to ' . $this->getProperty('MDB2DataType') . ' from DataType ' . $this->getProperty('DataType'));
      }
      parent::init();
      Tholos::$app->trace('END', $this);
    }
    
    /**
     * @param TComponent $sender
     * @throws Throwable
     */
    public function initValue(TComponent $sender): void {
      if (!$this->initialized) {
        $this->init();
      }
      if ($sender->getProperty('ParameterPrefix', '') !== '') {
        $pName = Eisodos::$utils->replace_all($this->getProperty('ParameterName', ''), $sender->getProperty('ParameterPrefix', ''), '', false, true);
      } else {
        $pName = $this->getProperty('ParameterName', '');
      }
      $pName = $this->parameterTransform($pName, $sender->getProperty('ParameterNameTransformation'));
      $value = Eisodos::$parameterHandler->getParam($pName, $this->getProperty('DefaultValue', ''));
      // type check
      if ($value !== '') {
        try {
          $dt = $this->getProperty('DataType', 'string');
          if ($dt === 'integer') {
            if (!Eisodos::$utils->isInteger($value, true)) {
              throw new RuntimeException('');
            }
          } elseif ($dt === 'string') {
            if (mb_strlen($value) > 1000) {
              throw new RuntimeException('string length is greater than 1000 - use text instead');
            }
          } elseif ($dt === 'text') {
            NULL;
          } elseif ($dt === 'bool') {
            if (!in_array($value, ['1', '0', 'Y', 'N', 'I', 'true', 'false'], false)) {
              throw new RuntimeException('');
            }
          } elseif (in_array($dt, ['date', 'datetime', 'time', 'datetimehm', 'timestamp'])) {
            $d = DateTime::createFromFormat('!' . Eisodos::$parameterHandler->getParam('PHP' . $this->getProperty('DateFormatParameter', 'datetime') . 'Format'),
              $value
            );
            if ($d) {
              $d->format(Eisodos::$parameterHandler->getParam($this->getProperty('NativeDataType') . '.SPFormat', 'Y-m-d H:i:s'));
            } else {
              throw new RuntimeException('');
            }
            $r = DateTime::getLastErrors();
            if ($r['warning_count'] > 0 || $r['error_count'] > 0) {
              throw new RuntimeException('expected input format: ' . Eisodos::$parameterHandler->getParam('PHP' . $this->getProperty('DateFormatParameter', 'datetime') . 'Format'));
            }
          } elseif ($dt === 'float') {
            if (!Eisodos::$utils->isFloat(Eisodos::$utils->replace_all($value, ',', '.'), true)) {
              throw new RuntimeException('');
            }
            $decimal_separator = Eisodos::$parameterHandler->getParam($this->getProperty('MDB2DataType') . '.DECIMAL_SEPARATOR', '.');
            $value = Eisodos::$utils->replace_all(Eisodos::$utils->replace_all($value, '.', $decimal_separator), ',', $decimal_separator);
          } elseif ($dt === 'JSON') {
            if (!json_decode($value, true, 512, JSON_THROW_ON_ERROR)) {
              throw new RuntimeException('');
            }
          }
        } catch (Exception $e) {
          $em = $e->getMessage();
          if ($this->getProperty('SuppressDataTypeError', 'false') == 'true') {
            $value='';
            Tholos::$app->trace($this->getProperty('Name') . ' raised parameter type error [' . $em . ']!');
          } else {
            throw new RuntimeException($this->getProperty('Name') . ' raised parameter type error [' . $em . ']!');
          }
        }
      }
      $this->setProperty('Value', $value, 'STRING', '', $this->getProperty('ParseValue', 'true') === 'false');
    }
    
    /**
     * @param string $name_
     * @param mixed $value_
     * @param string $type_
     * @param string $value_component_id_
     * @param bool $raw_
     */
    public function setProperty(string $name_, $value_, $type_ = 'STRING', $value_component_id_ = '', $raw_ = false): void {
      parent::setProperty($name_, $value_, $type_, $value_component_id_, $raw_);
      if (mb_strtolower($name_) === 'value') { // setting value with formatting
        if ($value_ === '') {
          $this->setProperty('DBValue', $value_, $type_, $value_component_id_, $raw_);
        } elseif (in_array($this->getProperty('DataType', 'string'), ['date', 'datetime', 'time', 'datetimehm', 'timestamp'])) { // date formatting
          Tholos::$app->trace('Formatting ' . $this->getProperty('MDB2DataType') . ' (' . Eisodos::$parameterHandler->getParam('PHP' . $this->getProperty('DateFormatParameter', 'datetime') . 'Format') .
            ' -> ' . Eisodos::$parameterHandler->getParam($this->getProperty('NativeDataType') . '.SPFormat', 'Y-m-d H:i:s') . ') ' . $value_);
          $this->setProperty('DBValue',
            DateTime::createFromFormat('!' . Eisodos::$parameterHandler->getParam('PHP' . $this->getProperty('DateFormatParameter', 'datetime') . 'Format'),
              $value_
            )->format(Eisodos::$parameterHandler->getParam($this->getProperty('NativeDataType') . '.SPFormat', 'Y-m-d H:i:s')),
            $type_, $value_component_id_, $raw_);
          // itt van egy kis MDB2 hekk: alapvetoen nincs szukseg az OCI8.DATE.SPFORMAT-ra, DBAL-al elvileg nem kell
        } else {
          $this->setProperty('DBValue', $value_, $type_, $value_component_id_, $raw_);
        }
      }
    }
  }
