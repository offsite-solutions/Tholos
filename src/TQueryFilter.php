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
     *
     * @param TComponent $sender
     * @throws Throwable
     */
    public function initValue(TComponent $sender): void {
      Tholos::$app->trace('BEGIN', $this);
      Tholos::$app->trace('(' . $this->_componentType . ') (ID ' . $this->_id . ')', $this);
      if (($sender === NULL and $this->getProperty('RecordSelector', '') === 'false')
        or ($sender !== NULL and $this->getProperty('RecordSelector', '') === 'true')) {
        Tholos::$app->debug('QueryFilter skipped', $this);
      } else {
        Tholos::$app->debug('QueryFilter applied', $this);
        //parent::init();
        $this->setProperty('SQL', '');
        if ($this->getProperty('ParameterName', '') !== '') {
          $value = Eisodos::$parameterHandler->getParam($this->getProperty('ParameterName', ''), $this->getProperty('Value', $this->getProperty('DefaultValue', '')));
        } else {
          $value = $this->getProperty('Value', $this->getProperty('DefaultValue', ''));
        }
        
        if ($this->getProperty('Required', 'false') === 'true' && $value === '') {
          Tholos::$app->findComponentByID($this->_parent_id)->setProperty('FilterError', 'true');
        } // ha required es nincs kitoltve, akkor megallitani a query futtatast
        
        if ($value === 'NULL') {
          $this->setProperty('SQL', $this->getProperty('FieldName') . ' IS NULL');
        } elseif ($value === 'NOTNULL') {
          $this->setProperty('SQL', $this->getProperty('FieldName') . ' IS NOT NULL');
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
                $value = nlist($value, false);
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
                $value = nlist($value, true);
              } elseif (mb_strlen($value) > 1000) {
                throw new RuntimeException('');
              }
            } elseif ($dt === 'text') {
              NULL;
            } elseif ($dt === 'bool') {
              if (!in_array($value, ['1', '0', 'Y', 'N', 'I', 'true', 'false'], false)) {
                throw new RuntimeException('');
              }
              if (Eisodos::$parameterHandler->eq("Tholos.UseLogicalBool", "true")) {
                if (in_array($value, explode(',', strtoupper(Eisodos::$parameterHandler->getParam("Tholos.BoolFalse", ""))), false)) {
                  $value = 'false';
                } else {
                  $value = 'true';
                }
              }
            } elseif (in_array($dt, ['date', 'datetime', 'time', 'datetimehm', 'timestamp'])) {
              if ($this->getProperty('DateFormatParameter', '') === '') {
                $dateformat = Eisodos::$parameterHandler->getParam('UNIVERSAL.PHP' . $dt . 'Format');
                $dbdateformat = Eisodos::$parameterHandler->getParam('UNIVERSAL.' . $dt . 'Format');
              } else {
                $dateformat = Eisodos::$parameterHandler->getParam('PHP' . $this->getProperty('DateFormatParameter', 'datetime') . 'Format');
                $dbdateformat = Eisodos::$parameterHandler->getParam($this->getProperty('DateFormatParameter', 'datetime') . 'Format');
              }
              DateTime::createFromFormat('!' . $dateformat,
                $value
              )->format('Y-m-d');
              $r = DateTime::getLastErrors();
              if ($r['warning_count'] > 0 or $r['error_count'] > 0) {
                throw new RuntimeException('');
              }
              $value = 'to_date(' . Eisodos::$dbConnectors->connector(Tholos::$app->findComponentByID($this->_parent_id)->getProperty('DBIndex'))->nullStr($value) . ",'" . $dbdateformat . "')";
            } elseif ($dt === 'time') {
              if ($this->getProperty('DateFormatParameter', '') === '') {
                $dateformat = Eisodos::$parameterHandler->getParam('UNIVERSAL.PHP' . $dt . 'Format');
                $dbdateformat = Eisodos::$parameterHandler->getParam('UNIVERSAL.' . $dt . 'Format');
              } else {
                $dateformat = Eisodos::$parameterHandler->getParam('PHP' . $this->getProperty('DateFormatParameter', 'datetime') . 'Format');
                $dbdateformat = Eisodos::$parameterHandler->getParam($this->getProperty('DateFormatParameter', 'datetime') . 'Format');
              }
              DateTime::createFromFormat('!' . $dateformat,
                $value
              )->format('h:n');
              $r = DateTime::getLastErrors();
              if ($r['warning_count'] > 0 or $r['error_count'] > 0) {
                throw new RuntimeException('');
              }
              $value = 'to_date(' . Eisodos::$dbConnectors->connector(Tholos::$app->findComponentByID($this->_parent_id)->getProperty('DBIndex'))->nullStr($value) . ",'" . $dbdateformat . "')";
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
            if ($this->getProperty('Fieldname', '') === '') {
              $this->setProperty('SQL', sprintf($this->getProperty('Relation'), $value));
            } elseif ($this->getProperty('ValueList', 'false') === 'true') {
              $this->setProperty('SQL', $this->getProperty('FieldName') . ' ' . $this->getProperty('Relation') . ' ' . $value);
            } else {
              $this->setProperty('SQL', $this->getProperty('FieldName') . ' ' . $this->getProperty('Relation') . ' ' .
                Eisodos::$dbConnectors->connector(Tholos::$app->findComponentByID($this->_parent_id)->getProperty('DBIndex'))->nullStr($value, !in_array($this->getProperty('DataType'), ['integer', 'float', 'date', 'datetime', 'time', 'datetimehm', 'timestamp'])));
            }
          } catch (Exception $e) {
            Tholos::$app->trace('END', $this);
            // Tholos::$app->findComponentByID($this->_parent_id)->setProperty('FilterError','true');
            throw $e;
          }
        }
      }
      
      Tholos::$app->trace('END', $this);
    }
    
    /**
     * @inheritDoc
     */
    public function render(TComponent $sender, string $content): string {
      return '';
    }
  }
