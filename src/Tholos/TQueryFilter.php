<?php /** @noinspection NullPointerExceptionInspection DuplicatedCode SpellCheckingInspection PhpUnusedFunctionInspection NotOptimalIfConditionsInspection */
  
  namespace Tholos;
  
  use DateTime;
  use Eisodos\Eisodos;
  use Exception;
  use RuntimeException;
  use Throwable;
  
  /**
   * TPage Component class
   *
   * TPage component defines the layout of the HTML page.
   * Descendant of TComponent.
   *
   * @package Tholos
   * @see TComponent
   */
  class TQueryFilter extends TComponent {
    
    /**
     * @param TComponent|null $sender
     * @throws Throwable
     */
    public function initValue(TComponent|null $sender): void {
      Tholos::$logger->trace('BEGIN', $this);
      Tholos::$logger->trace('(' . $this->_componentType . ') (ID ' . $this->_id . ')', $this);
      if (($sender === NULL && $this->getProperty('RecordSelector', '') === 'false')
        || ($sender !== NULL && $this->getProperty('RecordSelector', '') === 'true')) {
        Tholos::$logger->debug('QueryFilter skipped', $this);
      } else {
        Tholos::$logger->debug('QueryFilter applied', $this);
        //parent::init();
        $this->setProperty('SQL', '');
        if ($this->getProperty('ParameterName', '') !== '') {
          $value = Eisodos::$parameterHandler->getParam($this->getProperty('ParameterName', ''), $this->getProperty('Value', $this->getProperty('DefaultValue', '')));
        } else {
          $value = $this->getProperty('Value', $this->getProperty('DefaultValue', ''));
        }
        
        if ($value === '' && $this->getProperty('Required', 'false') === 'true') {
          Tholos::$logger->debug('Mandatory QueryFilter missing', $this);
          Tholos::$app->findComponentByID($this->_parent_id)->setProperty('FilterError', 'true');
        } // ha required es nincs kitoltve, akkor megallitani a query futtatast
        
        $JSONFilter = [
          'fieldName' => $this->getProperty('Fieldname', ''),
          'value' => NULL,
          'valueArray' => NULL,
          'operator' => NULL,
          'relation' => $this->getProperty('Relation', '='),
          'nativeDataType' => NULL,
          'dataType' => $this->getProperty('DataType', NULL),
          'isNull' => false,
          'isNotNull' => false
        ];
        if ($value === 'NULL') {
          $this->setProperty('SQL', $this->getProperty('FieldName') . ' IS NULL');
          $JSONFilter['isNull'] = true;
        } elseif ($value === 'NOTNULL') {
          $this->setProperty('SQL', $this->getProperty('FieldName') . ' IS NOT NULL');
          $JSONFilter['isNotNull'] = true;
        } elseif ($value !== '') {
          try {
            $dt = $this->getProperty('DataType', 'string');
            if ($dt === 'integer') {
              if ($this->getProperty('ValueList', 'false') === 'true') {
                foreach (explode(',', $value) as $value_) {
                  if (!Eisodos::$utils->isInteger($value_, true)) {
                    throw new RuntimeException('');
                  }
                }
                $JSONFilter['valueArray'] = explode(',', $value);
                $value = Eisodos::$dbConnectors->db()->toList($value, false);
              } elseif (!Eisodos::$utils->isInteger($value, true)) {
                throw new RuntimeException('');
              }
            } elseif ($dt === 'string') {
              if ($this->getProperty('ValueList', 'false') === 'true') {
                foreach (explode(',', $value) as $value_) {
                  if (mb_strlen($value_) > 1000) {
                    throw new RuntimeException('');
                  }
                }
                $JSONFilter['valueArray'] = explode(',', $value);
                $value = Eisodos::$dbConnectors->db()->toList($value, true);
              } elseif (mb_strlen($value) > 1000) {
                throw new RuntimeException('');
              }
            } elseif ($dt === 'text') {
              assert(true);
            } elseif ($dt === 'bool') {
              if (!in_array($value, ['1', '0', 'Y', 'N', 'I', 'true', 'false'], false)) {
                throw new RuntimeException('');
              }
              if (Eisodos::$parameterHandler->eq('Tholos.UseLogicalBool', 'true')) {
                if (in_array($value, explode(',', strtoupper(Eisodos::$parameterHandler->getParam('Tholos.BoolFalse', ''))), false)) {
                  $value = 'false';
                } else {
                  $value = 'true';
                }
              }
            } elseif (in_array($dt, ['date', 'datetime', 'datetimehm', 'timestamp'])) {
              if ($this->getProperty('DateFormatParameter', '') === '') {
                $dateFormat = Eisodos::$parameterHandler->getParam('UNIVERSAL.PHP' . $dt . 'Format');
                $DBDateFormat = Eisodos::$parameterHandler->getParam('UNIVERSAL.' . $dt . 'Format');
              } else {
                $dateFormat = Eisodos::$parameterHandler->getParam('PHP' . $this->getProperty('DateFormatParameter', 'datetime') . 'Format');
                $DBDateFormat = Eisodos::$parameterHandler->getParam($this->getProperty('DateFormatParameter', 'datetime') . 'Format');
              }
              try {
                $universalDt = DateTime::createFromFormat('!' . $dateFormat, $value);
                $r = DateTime::getLastErrors();
                if ($r && ($r['warning_count'] > 0 || $r['error_count'] > 0)) {
                  throw new RuntimeException('');
                }
                $universalDt->format('Y-m-d');
              } catch (Exception $e) {
                Tholos::$logger->error('Date conversion error ' . $dateFormat . ' on value - ' . $value, $this);
                throw new RuntimeException($e->getMessage());
              }
              $JSONFilter['value'] = $universalDt->format(Eisodos::$parameterHandler->getParam($this->getProperty('NativeDataType', $dt) . '.SPFormat'));
              $value = 'to_date(' . Eisodos::$dbConnectors->connector(Tholos::$app->findComponentByID($this->_parent_id)->getProperty('DBIndex'))->nullStr($value) . ",'" . $DBDateFormat . "')";
            } elseif ($dt === 'time') {
              if ($this->getProperty('DateFormatParameter', '') === '') {
                $dateFormat = Eisodos::$parameterHandler->getParam('UNIVERSAL.PHP' . $dt . 'Format');
                $DBDateFormat = Eisodos::$parameterHandler->getParam('UNIVERSAL.' . $dt . 'Format');
              } else {
                $dateFormat = Eisodos::$parameterHandler->getParam('PHP' . $this->getProperty('DateFormatParameter', 'datetime') . 'Format');
                $DBDateFormat = Eisodos::$parameterHandler->getParam($this->getProperty('DateFormatParameter', 'datetime') . 'Format');
              }
              /* testing datetime and converts to microservice (universal) format */
              try {
                $universalDt = DateTime::createFromFormat('!' . $dateFormat, $value);
                $r = DateTime::getLastErrors();
                if ($r && ($r['warning_count'] > 0 || $r['error_count'] > 0)) {
                  throw new RuntimeException('');
                }
              } catch (Exception $e) {
                Tholos::$logger->error('Date conversion error ' . $dateFormat . ' on value - ' . $value, $this);
                throw new RuntimeException($e->getMessage());
              }
              $JSONFilter['value'] = $universalDt->format(Eisodos::$parameterHandler->getParam($this->getProperty('NativeDataType', $dt) . '.SPFormat'));
              $value = 'to_date(' . Eisodos::$dbConnectors->connector(Tholos::$app->findComponentByID($this->_parent_id)->getProperty('DBIndex'))->nullStr($value) . ",'" . $DBDateFormat . "')";
            } elseif ($dt === 'float') {
              $value = Eisodos::$utils->replace_all($value, ',', '.');
              if (!Eisodos::$utils->isInteger($value, true)) {
                throw new RuntimeException('');
              }
            } elseif ($dt === 'JSON') {
              if (!json_decode($value, true, 512, JSON_THROW_ON_ERROR)) {
                throw new RuntimeException('');
              }
            }
            $this->setProperty('Value', $value);
            if (Eisodos::$utils->safe_array_value($JSONFilter, 'value', '') == '') {
              $JSONFilter['value'] = $value;
            }
            if ($dt == 'bool') {
              if ($JSONFilter['value'] == 'true') {
                $JSONFilter['value'] = true;
              } elseif ($JSONFilter['value'] == 'false') {
                $JSONFilter['value'] = false;
              }
            }
            if ($this->getProperty('Fieldname', '') == '') {
              $this->setProperty('SQL', sprintf($this->getProperty('Relation'), $value));
            } elseif ($this->getProperty('ValueList', 'false') == 'true') {
              $this->setProperty('SQL', $this->getProperty('FieldName') . ' ' . $this->getProperty('Relation') . ' ' . $value);
            } else {
              $this->setProperty('SQL', $this->getProperty('FieldName') . ' ' . $this->getProperty('Relation') . ' ' .
                Eisodos::$dbConnectors->connector(Tholos::$app->findComponentByID($this->_parent_id)->getProperty('DBIndex'))->nullStr($value, !in_array($this->getProperty('DataType'), ['integer', 'float', 'date', 'datetime', 'time', 'datetimehm', 'timestamp'])));
            }
          } catch (Exception $e) {
            Tholos::$logger->error('QueryFilter filter error: ' . $e->getMessage(), $this);
            Tholos::$logger->trace('END', $this);
            throw $e;
          }
        }
        $this->setProperty('JSONFilter', $JSONFilter, 'ARRAY');
      }
      
      Tholos::$logger->trace('END', $this);
    }
    
    /**
     * @inheritDoc
     */
    public function render(?TComponent $sender, string $content): string {
      return '';
    }
  }
