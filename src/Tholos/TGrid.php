<?php /** @noinspection NullPointerExceptionInspection DuplicatedCode SpellCheckingInspection PhpUnusedFunctionInspection NotOptimalIfConditionsInspection */
  
  namespace Tholos;
  
  use DateTime;
  use Eisodos\Eisodos;
  use Exception;
  use PhpOffice\PhpSpreadsheet\Cell\Coordinate as CellCoordinate;
  use PhpOffice\PhpSpreadsheet\Cell\DataType as CellDataType;
  use PhpOffice\PhpSpreadsheet\Spreadsheet;
  use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
  use RuntimeException;
  use Throwable;
  
  /**
   * TGrid Component class
   *
   * TGrid component implements grid view of data provided by a TDataProvider.
   * Descendant of TComponent.
   *
   * @package Tholos
   * @see TComponent
   */
  class TGrid extends TComponent {
    
    /**
     * @var array
     */
    private array $last_filters = array();
    
    /**
     * @var array
     */
    private array $userSettings = array();
    
    /**
     * @var string
     */
    private string $filterDropdown = '';
    
    /**
     * @var string
     */
    private string $filterSlots = '';
    /**
     * @var string
     */
    private string $filterSQL = '';
    
    /**
     * @var string
     */
    private string $columnHeadItems = '';
    /**
     * @var array
     */
    private array $transposedRows = array();
    
    /**
     * @var array
     */
    private array $chartComponents = array();
    /**
     * @var array
     */
    private array $chartDatasets = array();
    /**
     * @var array
     */
    private array $chartDatasetsOptions = array();
    
    /**
     * @var bool
     */
    private bool $reloadStateNeeded = false;
    
    /**
     * @var string
     */
    private $origOrderBy = "";
    
    /**
     * @throws Exception Throws exception
     * @throws Throwable
     */
    public function init(): void {
      
      Tholos::$logger->trace('BEGIN', $this);
      Tholos::$logger->trace('(' . $this->_componentType . ') (ID ' . $this->_id . ')', $this);
      parent::init();
      $this->selfRenderer = true;
      Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'))->setProperty('AutoOpenAllowed', 'false');
      
      $this->setProperty('cellHeadType', $this->getProperty('GridHTMLType') === 'table' ? 'th' : 'div');
      $this->setProperty('cellType', $this->getProperty('GridHTMLType') === 'table' ? 'td' : 'div');
      $this->setProperty('cellRowType', $this->getProperty('GridHTMLType') === 'table' ? 'tr' : 'div');
      
      if ($this->getProperty('GridHTMLType') !== 'table') {
        $this->setProperty('ShowTransposeCheckbox', 'false');
        $this->setProperty('ShowScrollCheckbox', 'false');
        $this->setProperty('Selectable', 'false');
        $this->setProperty('Scrollable', 'false');
        $this->setProperty('ScrollableY', 'false');
        $this->setProperty('Transposed', 'false');
      }
      
      //if (!Tholos::$app->findComponentByID($this->getPropertyComponentId('SortedBy'))) {
      //  Tholos::$logger->error('TGrid configuration error: mandatory SortedBy property was not specified', $this);
      //  throw new Exception('TGrid configuration error: mandatory SortedBy property was not specified');
      // }
      // setting runtime parameters
      
      if (Eisodos::$parameterHandler->neq('TGrid_uuid_', '')) {
        $this->setProperty('UUID', Eisodos::$parameterHandler->getParam('TGrid_uuid_'));
      }
      
      if ($this->getProperty('UUID') === '' && $this->getProperty('Persistent', '') !== '') {
        $this->reloadStateNeeded = true;
      }
      
      // saving original values for caching
      // $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId("ListSource"));
      if ($this->getPropertyComponentId("SortedBy", NULL) != NULL
        && $this->getProperty("SortedByAlways", "") == "") {
        $this->origOrderBy = Tholos::$app->findComponentByID(
            Tholos::$app->findComponentByID($this->getPropertyComponentId("SortedBy")
            )->getPropertyComponentId("DBField"))->getProperty("Index") . " " .
          $this->getProperty("SortingDirection", "ASC");
      } else {
        $this->origOrderBy = $this->getProperty("SortedByAlways", "1 ASC");
      }
      
      $this->loadState();
      
      if ($this->getProperty('SortedByAlways', '') === '') {
        if (Eisodos::$parameterHandler->neq('TGrid_SortedBy_', '')) {
          $found = false;
          foreach (Tholos::$app->findChildIDsByType($this, 'TGridColumn') as $id) {
            if (Tholos::$app->findComponentByID($id)->getProperty('Name') === Eisodos::$parameterHandler->getParam('TGrid_SortedBy_')) {
              $this->setProperty('SortedBy', Tholos::$app->findComponentByID($id)->getProperty('Name'), 'COMPONENT', $id);
              $found = true;
              break;
            }
          }
          if (!$found) {
            foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowID) {
              $gridRow = Tholos::$app->findComponentByID($rowID);
              foreach (Tholos::$app->findChildIDsByType($gridRow, 'TGridColumn') as $id) {
                if (Tholos::$app->findComponentByID($id)->getProperty('Name') === Eisodos::$parameterHandler->getParam('TGrid_SortedBy_')) {
                  $this->setProperty('SortedBy', Tholos::$app->findComponentByID($id)->getProperty('Name'), 'COMPONENT', $id);
                  break;
                }
              }
            }
          }
        }
        if (Eisodos::$parameterHandler->neq('TGrid_SortingDirection_', '')) {
          $this->setProperty('SortingDirection', Eisodos::$parameterHandler->getParam('TGrid_SortingDirection_', 'ASC'));
        } else {
          $this->setProperty('SortingDirection', Tholos::$app->findComponentByID($this->getPropertyComponentId('SortedBy'))->getProperty('SortingDirection', 'ASC'));
        }
        
        if ($this->getPropertyComponentId('SortedBy', false) !== false) {
          Tholos::$app->findComponentByID($this->getPropertyComponentId('SortedBy', NULL))->setProperty('Sorted', $this->getProperty('SortingDirection', 'ASC'));
        }
      }
      
      if (Eisodos::$parameterHandler->neq('TGrid_ActivePage_', '')) {
        $this->setProperty('ActivePage', Eisodos::$parameterHandler->getParam('TGrid_ActivePage_'));
      }
      
      if (Eisodos::$parameterHandler->neq('TGrid_RowsPerPage_', '')) {
        $this->setProperty('RowsPerPage', Eisodos::$parameterHandler->getParam('TGrid_RowsPerPage_'));
      }
      
      if (Eisodos::$parameterHandler->neq('TGrid_Scrollable_', '')) {
        $this->setProperty('Scrollable', Eisodos::$parameterHandler->getParam('TGrid_Scrollable_'));
      }
      
      if (Eisodos::$parameterHandler->neq('TGrid_ScrollableY_', '')) {
        $this->setProperty('ScrollableY', Eisodos::$parameterHandler->getParam('TGrid_ScrollableY_'));
      }
      
      if (Eisodos::$parameterHandler->neq('TGrid_Transposed_', '')) {
        $this->setProperty('Transposed', Eisodos::$parameterHandler->getParam('TGrid_Transposed_'));
      }
      
      if (Eisodos::$parameterHandler->neq('TGrid_MasterValue_', '')) {
        $this->setProperty('MasterValue', Eisodos::$parameterHandler->getParam('TGrid_MasterValue_'));
      }
      
      if (Eisodos::$parameterHandler->neq('TGrid_ViewMode_', '')) {
        $this->setProperty('ViewMode', Eisodos::$parameterHandler->getParam('TGrid_ViewMode_'));
      }
      
      if ($this->getProperty("ViewMode") == '') {
        $this->setProperty("ViewMode", "GRID");
      }
      
      $this->saveUserSettings();
      
      Tholos::$logger->trace('END', $this);
    }
    
    /**
     * @param $value_
     * @param bool $isString_
     * @return string
     */
    private function nullStr($value_, bool $isString_ = true): string {
      if (($component = $this->getPropertyComponentId('ListSource'))) {
        return Eisodos::$dbConnectors->connector(Tholos::$app->findComponentByID($component)->getProperty('DatabaseIndex'))->nullStr($value_, $isString_);
      }
      
      return '';
    }
    
    /**
     * @throws Exception Throws exception
     */
    private function loadState(): void {
      
      // loading previous state
      $n = $this->getProperty('name', '') . '_f_';
      if (Eisodos::$parameterHandler->eq('TGrid_todo_', 'reloadState') && $this->getProperty('UUID', '') !== '') { // grid visszatoltese az utolso allapotra
        if ($this->getProperty('Persistent', '') === 'DATABASE') {  // utolso filterek
          $this->last_filters = @unserialize(Eisodos::$dbConnectors->connector($this->getProperty('DatabaseIndex'))->query(RT_FIRST_ROW_FIRST_COLUMN, 'select value from cor_session_parameters where session_id=' . $this->nullStr(session_id()) . ' and parameter_name=' . $this->nullStr($this->getProperty('Name', '') . '.filters.' . $this->getProperty('UUID', ''))), ['allowed_classes' => false]);
          $this->userSettings = @unserialize(Eisodos::$dbConnectors->connector($this->getProperty('DatabaseIndex'))->query(RT_FIRST_ROW_FIRST_COLUMN, 'select value from cor_session_parameters where session_id=' . $this->nullStr(session_id()) . ' and parameter_name=' . $this->nullStr($this->getProperty('Name', '') . '.grid.' . $this->getProperty('UUID', ''))), ['allowed_classes' => false]);
        } elseif ($this->getProperty('Persistent', '') === 'SESSION') {
          if ($prefix = $this->getProperty('PersistencyPrefix')) {
            $prefix = '';
          }
          $unserialized = @unserialize($prefix . Eisodos::$parameterHandler->getParam($this->getProperty('Name', '') . '.filters.' . $this->getProperty('UUID', '')), ['allowed_classes' => false]);
          $this->last_filters = ($unserialized !== false) ? $unserialized : [];
          $unserialized = @unserialize($prefix . Eisodos::$parameterHandler->getParam($this->getProperty('Name', '') . '.grid.' . $this->getProperty('UUID', '')), ['allowed_classes' => false]);
          $this->userSettings = ($unserialized !== false) ? $unserialized : [];
        }
        
        for ($i = 1; $i < 100; $i++) {
          Eisodos::$parameterHandler->setParam($n . $i);
        }
        
        foreach ($this->last_filters as $filter => $value) {
          Eisodos::$parameterHandler->setParam($filter, $value);
        }
        foreach ($this->userSettings as $key => $value) {
          Eisodos::$parameterHandler->setParam($key, $value);
        }
        
      } else {
        for ($i = 1; $i < 100; $i++) {
          if (Eisodos::$parameterHandler->neq($n . $i, '')) {
            $this->last_filters[$n . $i] = Eisodos::$parameterHandler->getParam($n . $i);
          }
        }
      }
      
    }
    
    /**
     * @throws Exception Throws exception
     */
    private function saveFilterState(): void {
      
      if ($this->getProperty('Persistent', '') === 'DATABASE') {
        if (Eisodos::$dbConnectors->connector($this->getProperty('DatabaseIndex'))->query(RT_FIRST_ROW_FIRST_COLUMN, "select session_id \n" .
            "  from cor_session_parameters \n" .
            " where session_id=" . $this->nullStr(session_id()) . "\n" .
            "       and parameter_name=" . $this->nullStr($this->getProperty("Name", "") . ".filters." . $this->getProperty("UUID", ""))) !== "") {
          $sql = "update cor_session_parameters \n" .
            "   set value=? \n" .
            " where session_id=" . $this->nullStr(session_id()) . "\n" .
            "       and parameter_name=" . $this->nullStr($this->getProperty("Name", "") . ".filters." . $this->getProperty("UUID", ""));
        } else {
          $sql = "INSERT INTO cor_session_parameters \n" .
            "  (session_id,parameter_name,value) VALUES \n" .
            "  (" . $this->nullStr(session_id()) . "," . $this->nullStr($this->getProperty("Name", "") . ".filters." . $this->getProperty("UUID", "")) . ",:VALUE)";
        }
        
        Eisodos::$dbConnectors->connector($this->getProperty('DatabaseIndex'))->startTransaction();
        $boundVariables = [];
        Eisodos::$dbConnectors->connector($this->getProperty('DatabaseIndex'))->bind($boundVariables, 'VALUE', 'string', serialize($this->last_filters));
        Eisodos::$dbConnectors->connector($this->getProperty('DatabaseIndex'))->executePreparedDML2(
          $sql,
          $boundVariables
        );
        
        Eisodos::$dbConnectors->connector($this->getProperty('DatabaseIndex'))->commit();
      } elseif ($this->getProperty('Persistent', '') === 'SESSION') {
        if ($prefix = $this->getProperty('PersistencyPrefix')) {
          $prefix = '';
        }
        Eisodos::$parameterHandler->setParam($prefix . $this->getProperty('Name', '') . '.filters.' . $this->getProperty('UUID', ''), serialize($this->last_filters), true);
      }
    }
    
    /**
     * @throws Exception Throws exception
     */
    private function saveUserSettings(): void {
      
      $this->userSettings['TGrid_SortedBy_'] = Eisodos::$parameterHandler->getParam('TGrid_SortedBy_');
      $this->userSettings['TGrid_SortingDirection_'] = Eisodos::$parameterHandler->getParam('TGrid_SortingDirection_');
      $this->userSettings['TGrid_ActivePage_'] = Eisodos::$parameterHandler->getParam('TGrid_ActivePage_');
      $this->userSettings['TGrid_RowsPerPage_'] = Eisodos::$parameterHandler->getParam('TGrid_RowsPerPage_');
      $this->userSettings['TGrid_Scrollable_'] = Eisodos::$parameterHandler->getParam('TGrid_Scrollable_');
      $this->usersettings['TGrid_ScrollableY_'] = Eisodos::$parameterHandler->getParam('TGrid_ScrollableY_');
      
      if ($this->getProperty('Persistent', '') === 'DATABASE') {
        if (Eisodos::$dbConnectors->connector($this->getProperty('DatabaseIndex'))->query(RT_FIRST_ROW_FIRST_COLUMN, 'select session_id from cor_session_parameters where session_id=' . $this->nullStr(session_id()) . ' and parameter_name=' . $this->nullStr($this->getProperty('Name', '') . '.grid.' . $this->getProperty('UUID', ''))) !== '') {
          $sql = 'update cor_session_parameters set value=? where session_id=' . $this->nullStr(session_id()) . ' and parameter_name=' . $this->nullStr($this->getProperty('Name', '') . '.grid.' . $this->getProperty('UUID', ''));
        } else {
          $sql = 'insert into cor_session_parameters (session_id,parameter_name,value) values (' . $this->nullStr(session_id()) . ',' . $this->nullStr($this->getProperty('Name', '') . '.grid.' . $this->getProperty('UUID', '')) . ',:VALUE)';
        }
        
        Eisodos::$dbConnectors->connector($this->getProperty('DatabaseIndex'))->startTransaction();
        $boundVariables = [];
        Eisodos::$dbConnectors->connector($this->getProperty('DatabaseIndex'))->bind($boundVariables, 'VALUE', 'string', serialize($this->userSettings));
        Eisodos::$dbConnectors->connector($this->getProperty('DatabaseIndex'))->executePreparedDML2(
          $sql,
          $boundVariables
        );
        Eisodos::$dbConnectors->connector($this->getProperty('DatabaseIndex'))->commit();
      } elseif ($this->getProperty('Persistent', '') === 'SESSION') {
        if ($prefix = $this->getProperty('PersistencyPrefix')) {
          $prefix = '';
        }
        Eisodos::$parameterHandler->setParam($prefix . $this->getProperty('Name', '') . '.grid.' . $this->getProperty('UUID', ''), serialize($this->userSettings), true);
      }
    }
    
    /**
     * @param $filters
     * @throws Throwable
     */
    private function renderFilters($filters): void {
      
      $this->filterDropdown = '';
      
      foreach ($filters as $filterID) {
        /* @var TGridFilter $filter */
        $filter = Tholos::$app->findComponentByID($filterID);
        $this->filterDropdown .= $filter->render($this, '');
        $filter->generateDefault($this->getProperty('name', ''));
      }
      
      $this->filterSlots = '';
      $this->filterSQL = '';
      
      // generating filter slots
      $n = $this->getProperty('name', '') . '_f_';
      for ($i = 1; $i < 100; $i++) {
        if (Eisodos::$parameterHandler->neq($n . $i, '')) {
          $this->filterSlots .= Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.filter.slot',
              array('slot' => $n . $i,
                'value' => Eisodos::$parameterHandler->getParam($n . $i)
              ), false) . "\n";
        }
      }
      
    }
    
    /**
     * @param mixed $value
     * @param bool $encapsulate
     * @return string
     * @throws Exception
     */
    private function boolConvert(mixed $value, bool $encapsulate = true): string {
      if (Eisodos::$parameterHandler->eq('Tholos.UseLogicalBool', 'true')) {
        if (in_array($value, explode(',', strtoupper(Eisodos::$parameterHandler->getParam('Tholos.BoolFalse', ''))), false)) {
          $value = 'false';
        } else {
          $value = 'true';
        }
      } else if ($encapsulate) {
        $value = $this->nullStr($value, true);
      }
      
      return $value;
    }
    
    /**
     * @param $filters
     * @throws Exception
     */
    private function generateFilterSQL($filters): void {
      
      $this->filterSQL = '';
      $filter_array = [];
      
      // generating filter SQL
      $n = $this->getProperty('name', '') . '_f_';
      for ($i = 1; $i < 100; $i++) {
        if (Eisodos::$parameterHandler->neq($n . $i, '')) { // ha van az adott parameterben valami
          $filterParam = explode(':', Eisodos::$parameterHandler->getParam($n . $i), 3);
          $filter = false;
          $dbField = NULL;
          foreach ($filters as $filterID) {
            $filter = Tholos::$app->findComponentByID($filterID);
            if ($filter->getProperty('Name') === $filterParam[0]) {
              $dbField = Tholos::$app->findComponentByID($filter->getPropertyComponentId('DBField'));
              break;
            }
          }
          if (!$filter || $dbField === NULL) {
            Tholos::$logger->error('No filter defined: ' . $filterParam[0], $this);
          } else {
            $dateError = false;
            $JSONDateValue = '';
            if (@$filterParam[2] != ''
              && in_array($dbField->getProperty('datatype'), ['date', 'datetime', 'datetimehm', 'time', 'timestamp'])
            ) {
              $dateformat = Eisodos::$parameterHandler->getParam('PHP' . $dbField->getProperty('DateFormatParameter') . 'Format');
              $universalDt = DateTime::createFromFormat('!' . $dateformat, @$filterParam[2]);
              $r = DateTime::getLastErrors();
              if ($r && ($r["warning_count"] > 0 || $r["error_count"] > 0)) {
                $dateError = true;
                Tholos::$logger->error(print_r(array_merge($filterParam, $r), true), $this);
              } else {
                try {
                  $JSONDateValue = $universalDt->format(Eisodos::$parameterHandler->getParam($dbField->getProperty('NativeDataType') . '.SPFormat'));
                } catch (Exception $e) {
                  Tholos::$logger->error($e->getMessage(), $this);
                }
              }
            }
            if (!$dateError) {
              $SQLValue = '';
              $skipFilter = false;
              switch ($dbField->getProperty('datatype')) {
                case 'string':
                  $SQLValue = (in_array($filterParam[1], ['in', 'notin']) ? Eisodos::$dbConnectors->db()->toList(@$filterParam[2], true) : $this->nullStr(@$filterParam[2], true));
                  break;
                case 'list':
                case 'text':
                  $SQLValue = $this->nullStr(@$filterParam[2], true);
                  break;
                case 'bool': // TODO ha bool=*, akkor FIELD=FIELD generalodik es ha null a field, akkor nem jelenik meg
                  if (@$filterParam[2] === '*') {
                    $skipFilter = true;
                  } else {
                    $SQLValue = $this->boolConvert(@$filterParam[2]);
                  }
                  break;
                case 'boolIN':
                case 'boolYN':
                  if (@$filterParam[2] === '*') {
                    $skipFilter = true;
                  } else {
                    $SQLValue = $this->nullStr(@$filterParam[2], true);
                  }
                  break;
                case 'bool10':
                  if (@$filterParam[2] === '-1') {
                    $skipFilter = true;
                  } else {
                    $SQLValue = $this->nullStr(@$filterParam[2], false);
                  }
                  break;
                case 'datebetween':
                case 'date':
                  $SQLValue = "to_date('" . @$filterParam[2] . "','" . Eisodos::$parameterHandler->getParam("dateformat") . "')";
                  break;
                case 'datetime':
                  $SQLValue = "to_date('" . @$filterParam[2] . "','" . Eisodos::$parameterHandler->getParam("datetimeformat") . "')";
                  break;
                case 'datetimehm':
                  $SQLValue = "to_date('" . @$filterParam[2] . "','" . Eisodos::$parameterHandler->getParam("datetimehmformat") . "')";
                  break;
                case 'time':
                  $SQLValue = "to_date('" . @$filterParam[2] . "','" . Eisodos::$parameterHandler->getParam("timeformat") . "')";
                  break;
                case 'timestamp':
                  $SQLValue = "to_date('" . @$filterParam[2] . "','" . Eisodos::$parameterHandler->getParam("timestampformat") . "')";
                  break;
                case 'float':
                case 'integer':
                  $SQLValue = (in_array($filterParam[1], ['in', 'notin']) ? Eisodos::$dbConnectors->db()->toList(@$filterParam[2], false) : $this->nullStr(@$filterParam[2], false));
                  break;
              }
              
              $SQLSentence = '';
              
              switch ($filterParam[1]) {
                case 'NULL':
                  $SQLSentence = ' IS NULL';
                  break;
                case 'NOT NULL':
                  $SQLSentence = ' IS NOT NULL';
                  break;
                case 'eq':
                  $SQLSentence = '=%s';
                  break;
                case 'neq':
                  $SQLSentence = '!=%s';
                  break;
                case 'like':
                  $SQLSentence = ' like lower(%s) ';
                  break;
                case 'nlike':
                  $SQLSentence = ' not like lower(%s) ';
                  break;
                case 'gt':
                  $SQLSentence = '>%s';
                  break;
                case 'gteq':
                  $SQLSentence = '>=%s';
                  break;
                case 'lt':
                  $SQLSentence = '<%s';
                  break;
                case 'lteq':
                  $SQLSentence = '<=%s';
                  break;
                case 'bw':
                  $SQLSentence = $filter->getProperty('SQL', '');
                  break;
                case 'in':
                  $SQLSentence = ' in %s ';
                  break;
                case 'notin':
                  $SQLSentence = ' not in %s ';
                  break;
              }
              
              if (!$skipFilter) {
              
                $this->filterSQL .= ' and ' .
                (($filterParam[1] === 'like' || $filterParam[1] === 'nlike') ? 'lower(' . $dbField->getProperty('FieldName') . ')' : $dbField->getProperty('FieldName')) .
                sprintf($SQLSentence, $SQLValue) . " \n";
                
                $JSONValue = NULL;
                
                switch ($dbField->getProperty('datatype')) {
                  case 'text':
                  case 'string':
                    $JSONValue = (in_array($filterParam[1], ["in", "notin"]) ? NULL : @$filterParam[2]);
                    break;
                  case 'bool':
                    $JSONValue = $this->boolConvert(@$filterParam[2], false);
                    break;
                  case 'boolIN':
                  case 'bool10':
                  case 'boolYN':
                    $JSONValue = @$filterParam[2];
                    break;
                  case 'datetime':
                  case 'datetimehm':
                  case 'time':
                  case 'timestamp':
                  case 'date':
                    $JSONValue = $JSONDateValue;
                    break;
                  case 'datebetween':
                    $JSONValue = @$filterParam[2];
                    break;
                  case 'integer':
                    $JSONValue = (in_array($filterParam[1], ['in', 'notin']) ? NULL : @$filterParam[2]);
                    break;
                  case 'float':
                    $JSONValue = (in_array($filterParam[1], ['in', 'notin']) ? NULL : str_replace(',', '.', @$filterParam[2]));
                    break;
                }
                
                $JSONValueArray = NULL;
                
                switch ($dbField->getProperty('datatype')) {
                  case 'text':
                  case 'integer':
                  case 'float':
                  case 'string':
                    $JSONValueArray = (in_array($filterParam[1], ['in', 'notin']) ? explode(',', @$filterParam[2]) : NULL);
                    break;
                  case 'list':
                    $JSONValueArray = explode(',', @$filterParam[2]);
                    break;
                }
                
                $JSONFilter = [
                  'fieldName' => $dbField->getProperty('FieldName'),
                  'value' => $JSONValue,
                  'valueArray' => $JSONValueArray,
                  'operator' => $filterParam[1],
                  'relation' => NULL,
                  'nativeDataType' => $dbField->getProperty('NativeDataType'),
                  'dataType' => $dbField->getProperty('datatype'),
                  'isNull' => ($filterParam[1] == 'NULL'),
                  'isNotNull' => ($filterParam[1] == 'NOT NULL')
                ];
                
                $filter_array[] = $JSONFilter;
              }
            }
          }
        }
      }
      
      if ($this->getPropertyComponentId('MasterDBField', NULL) !== NULL) {
        $masterValue = $this->getProperty('MasterValue', '');
        if ($masterValue === '') {
          $this->filterSQL .= "\n and 0=1 ";
          $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'));
          $listSource->setProperty('StructureInfoOnly', 'true');
          $listSource->setProperty('StructureRequester', $this->_id);
          $JSONFilter = [
            'fieldName' => '0',
            'value' => '1',
            'valueArray' => NULL,
            'operator' => 'eq',
            'relation' => NULL,
            'nativeDataType' => NULL,
            'dataType' => NULL,
            'isNull' => false,
            'isNotNull' => false
          ];
        } else {
          $dbField = Tholos::$app->findComponentByID($this->getPropertyComponentId('MasterDBField', NULL));
          
          $SQLValue = '';
          
          switch ($dbField->getProperty('datatype')) {
            case 'string':
              $SQLValue = $this->nullStr($masterValue, true);
              break;
            case 'date':
              $SQLValue = "to_date('" . $masterValue . "','" . Eisodos::$parameterHandler->getParam("dateformat") . "')";
              break;
            case 'datetime':
              $SQLValue = "to_date('" . $masterValue . "','" . Eisodos::$parameterHandler->getParam("datetimeformat") . "')";
              break;
            case 'datetimehm':
              $SQLValue = "to_date('" . $masterValue . "','" . Eisodos::$parameterHandler->getParam("datetimehmformat") . "')";
              break;
            case 'time':
              $SQLValue = "to_date('" . $masterValue . "','" . Eisodos::$parameterHandler->getParam("timeformat") . "')";
              break;
            case 'timestamp':
              $SQLValue = "to_date('" . $masterValue . "','" . Eisodos::$parameterHandler->getParam("timestampformat") . "')";
              break;
            case 'float':
            case 'integer':
              $SQLValue = $this->nullStr($masterValue, false);
              break;
          }
          
          $this->filterSQL .= ' and ' .
            $dbField->getProperty('FieldName') .
            sprintf('=%s', $SQLValue) . " \n";
          $JSONFilter = [
            'fieldName' => $dbField->getProperty('FieldName'),
            'value' => Eisodos::$utils->ODecode(array($dbField->getProperty('datatype'),
                'string', $masterValue,
                'date', $masterValue,
                'datetime', $masterValue,
                'datetimehm', $masterValue,
                'time', $masterValue,
                'timestamp', $masterValue,
                'integer', $masterValue,
                'float', $masterValue,
                NULL
              )
            ),
            'valueArray' => NULL,
            'operator' => 'eq',
            'relation' => NULL,
            'nativeDataType' => $dbField->getProperty('NativeDataType'),
            'dataType' => $dbField->getProperty('datatype'),
            'isNull' => false,
            'isNotNull' => false
          ];
        }
        $filter_array[] = $JSONFilter;
      }
      
      $this->setProperty('FilterSQL', str_replace("\n", ' ', $this->filterSQL));
      $this->setProperty('JSONFilters', $filter_array, 'ARRAY');
      
    }
    
    /**
     * @throws Exception Throws exception
     * @throws Throwable
     */
    private function renderColumnHeadItems(): void {
      
      $this->columnHeadItems = '';
      $this->transposedRows = array();
      $transposed = ($this->getProperty('Transposed', 'false') === 'true');
      
      $items = array();
      
      $exportable = false;
      
      if ($this->getPropertyComponentId('DBField') !== false) {
        $items['__TransposedHeader'] = '<' . $this->getProperty('cellHeadType', '') . ' class="TGrid-resp-row TGrid-resp-header" style="' . ($transposed ? '' : 'width: 25px; max-width: 25px;') . '" data-resizable-column-id="">&nbsp;</' . $this->getProperty('cellHeadType', '') . '>';
      }
      
      $hasAnyStandaloneGridColumn = false;
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TComponent') as $id) {
        $column = Tholos::$app->findComponentByID($id);
        if (!$column) {
          throw new RuntimeException('Invalid reference');
        }
        if ($column->getComponentType() === 'TGridColumn'
          || $column->getComponentType() === 'TGridRowActions') {
          if ($transposed) {
            $column->setProperty('ColumnOffset', '0');
            $column->setProperty('ColumnSpan', '');
          }
          if ($column->getProperty('Visible', 'true') === 'true') {
            $items[$column->getProperty('Name')] = $column->renderPartial($this, 'head');
            $hasAnyStandaloneGridColumn = true;
          }
          if ($column->getProperty('Exportable') === 'true'
            && (Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn')) {
            $exportable = true;
          }
        }
      }
      
      if (!$transposed && $hasAnyStandaloneGridColumn) {
        $this->columnHeadItems .= $this->renderPartial($this, 'headitems', implode($items));
      }
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowID) {
        if (!$transposed && $hasAnyStandaloneGridColumn) {
          $items = array();
        }
        if ($transposed || Tholos::$app->findComponentByID($rowID)->getProperty('ShowColumnHead', '') === 'true') {
          if (!$transposed && $this->getPropertyComponentId('DBField') !== false) {
            $items['__TransposedHeader'] = '<' . $this->getProperty('cellHeadType', '') . ' class="TGrid-resp-header" style="width: 25px; max-width: 25px;" data-resizable-column-id="">&nbsp;</' . $this->getProperty('cellHeadType', '') . '>';
          }
        }
        foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowID), 'TComponent') as $id) {
          $column = Tholos::$app->findComponentByID($id);
          if ($column->getComponentType() === 'TGridColumn'
            || $column->getComponentType() === 'TGridRowActions') {
            if ($transposed) {
              $column->setProperty('ColumnOffset', '0');
              $column->setProperty('ColumnSpan', '');
            }
            if ($column->getProperty('Visible', 'true') === 'true') {
              $items[$column->getProperty('Name')] = $column->renderPartial($this, 'head');
            }
            if ($column->getProperty('Exportable') === 'true' && Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn') {
              $exportable = true;
            }
          }
        }
        if (!$transposed && count($items) > 0 && Tholos::$app->findComponentByID($rowID)->getProperty('ShowColumnHead', '') == 'true') {
          $this->columnHeadItems .= $this->renderPartial($this, 'headitems', implode($items));
        }
      }
      
      if ($transposed) {
        $this->transposedRows = $items;
      }
      
      $this->setProperty('Exportable', ($this->getProperty('Exportable') === 'true' and ($exportable)) ? 'true' : 'false');
      
    }
    
    /**
     * @throws Exception Throws exception
     * @throws Throwable
     */
    private function renderExcel(): void {
      
      ini_set('memory_limit', '-1');
      Eisodos::$parameterHandler->setParam('INCLUDESTATISTIC', 'F');
      Eisodos::$render->Response = '';
      
      $objPHPExcel = new Spreadsheet();
      $objPHPExcel->setActiveSheetIndex(0);
      $objPHPExcel->getActiveSheet()->setTitle('Data');
      
      $columns = array();
      
      $j = 1;
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TComponent') as $id) {
        $column = Tholos::$app->findComponentByID($id);
        if (!$column) {
          throw new RuntimeException('Invalid reference');
        }
        if ($column->getProperty('Exportable') === 'true'
          && Tholos::$app->checkRole($column)
          && Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn') {
          $objPHPExcel->getActiveSheet()->getCell([$j, 1])->
          setValueExplicit(Eisodos::$translator->translateText($column->getProperty('Label')),
            CellDataType::TYPE_STRING);
          $objPHPExcel->getActiveSheet()->getStyle(CellCoordinate::stringFromColumnIndex($j) . '1')->applyFromArray(array('font' => array('bold' => 'false')));
          /* @var array[] $columns
           * @option TComponent 'object'
           * @option string 'dtype'
           */
          $columns[$j]['object'] = $column;
          $columns[$j]['etype'] = CellDataType::TYPE_STRING;
          if (in_array(Tholos::$app->findComponentByID($column->getPropertyComponentId('DBField'))->getProperty('Datatype'), array('integer', 'float'))) {
            $columns[$j]['etype'] = CellDataType::TYPE_NUMERIC;
          }
          $j++;
        }
      }
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowID) {
        foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowID), 'TComponent') as $id) {
          $column = Tholos::$app->findComponentByID($id);
          if ($column->getProperty('Exportable') === 'true'
            && Tholos::$app->checkRole($column)
            && Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn') {
            $objPHPExcel->getActiveSheet()->getCell([$j, 1])->
            setValueExplicit(Eisodos::$translator->translateText($column->getProperty('Label')),
              CellDataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle(CellCoordinate::stringFromColumnIndex($j) . '1')->applyFromArray(array('font' => array('bold' => 'false')));
            /* @var array[] $columns
             * @option TComponent 'object'
             * @option string 'dtype'
             */
            $columns[$j]['object'] = $column;
            $columns[$j]['etype'] = CellDataType::TYPE_STRING;
            if (in_array(Tholos::$app->findComponentByID($column->getPropertyComponentId('DBField'))->getProperty('Datatype'), array('integer', 'float'))) {
              $columns[$j]['etype'] = CellDataType::TYPE_NUMERIC;
            }
            $j++;
          }
        }
      }
      
      // running data query
      
      /* @var TQuery $listSource */
      $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'));
      $listSource->setProperty('DisableQueryFilters', 'true');
      $listSource->setProperty('FilterArray', $listSource->buildFilters($this));
      $listSource->setProperty('JSONFilters', $this->getProperty('JSONFilters'));
      $listSource->setProperty('Filter', $this->filterSQL);
      if ($this->getPropertyComponentId('SortedBy', NULL) !== NULL
        && $this->getProperty('SortedByAlways', '') === '') {
        $listSource->setProperty('OrderBy', Tholos::$app->findComponentByID(
            Tholos::$app->findComponentByID($this->getPropertyComponentId('SortedBy'))->
            getPropertyComponentId('DBField'))->
          getProperty('Index') . ' ' .
          $this->getProperty('SortingDirection'));
      } else {
        $listSource->setProperty('OrderBy', $this->getProperty('SortedByAlways', '1 ASC'));
      }
      $listSource->setProperty('QueryLimit', '0');
      $listSource->setProperty('QueryOffset', '0');
      $listSource->setProperty('CountTotalRows', 'false');
      $listSource->setProperty('Caching', 'Disabled');
      $listSource->run($this);
      
      $i = 1;
      if (1 * $listSource->getProperty('RowCount') > 0) {
        foreach ($listSource->getProperty('Result') as $row) {
          $listSource->propagateResult($row);
          $i++;
          $j = 1;
          
          foreach ($columns as $column) {
            $objPHPExcel->getActiveSheet()->getCell([$j, $i])->setValueExplicit($column['object']->getProperty('Value'), $column['etype']);
            $j++;
          }
          
        }
      }
      
      // exporting filters
      
      $sheetId = 1;
      $objPHPExcel->createSheet($sheetId);
      $objPHPExcel->setActiveSheetIndex($sheetId);
      $objPHPExcel->getActiveSheet()->setTitle('Filter');
      
      $j = 1;
      $n = $this->getProperty('name', '') . '_f_';
      for ($i = 1; $i < 100; $i++) {
        if (Eisodos::$parameterHandler->neq($n . $i, '')) { // ha van az adott parameterben valami
          $filterParam = explode(':', Eisodos::$parameterHandler->getParam($n . $i), 3);
          $filter = false;
          $DBField = NULL;
          foreach (Tholos::$app->findChildIDsByType($this, 'TGridFilter') as $filterID) {
            $filter = Tholos::$app->findComponentByID($filterID);
            if ($filter->getProperty('Name') === $filterParam[0]) {
              $DBField = Tholos::$app->findComponentByID($filter->getPropertyComponentId('DBField'));
              break;
            }
          }
          if (!$filter || $DBField === NULL) {
            Tholos::$logger->error('No filter definied: ' . $filterParam[0], $this);
          } else {
            $objPHPExcel->getActiveSheet()->getCell([1, $j])->
            setValueExplicit(Eisodos::$translator->translateText($filter->getProperty('Label')), CellDataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getCell([2, $j])->
            setValueExplicit(Eisodos::$utils->ODecode(array($filterParam[1],
                'NULL', ' IS NULL',
                'NOT NULL', 'IS NOT NULL',
                'eq', '=',
                'neq', '!=%s',
                'like', ' like ',
                'nlike', ' not like ',
                'gt', '>',
                'gteq', '>=',
                'lt', '<',
                'lteq', '<=',
                'bw', '@',
                'in', ' in ',
                'notin', ' not in '
              )
            ),
              CellDataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getCell([3, $j])->setValueExplicit($filterParam[2], CellDataType::TYPE_STRING);
            $j++;
          }
        }
      }
      
      $objPHPExcel->setActiveSheetIndex(0);
      
      $objWriter = new Xlsx($objPHPExcel);
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment;filename="' . date('YmdHis') . '.xlsx"');
      header('Cache-Control: max-age=0');
      $objWriter->save('php://output');
      
      Tholos::$app->responseType = 'BINARY'; // force application not to modify output
      
    }
    
    /**
     * @param string $type_ tsv|csv
     * @throws Exception
     * @throws Throwable
     */
    private function renderPlainTextExport(string $type_): void {
      
      ini_set('memory_limit', '-1');
      Eisodos::$parameterHandler->setParam('INCLUDESTATISTIC', 'F');
      Eisodos::$render->Response = '';
      
      $exp_row = '';
      
      if (strtolower($type_) === 'tsv') {
        $separator = "\t";
        header('Content-Type: text/tab-separated-values');
        header('Content-Disposition: attachment;filename="' . date('YmdHis') . '.tsv"');
      } else {
        $separator = ';';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . date('YmdHis') . '.csv"');
      }
      header('Cache-Control: max-age=0');
      
      $columns = array();
      
      $i = -1;
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TComponent') as $id) {
        $column = Tholos::$app->findComponentByID($id);
        if ($column->getProperty('Exportable') === 'true'
          && Tholos::$app->checkRole($column)
          (Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn')) {
          $exp_row .= ($exp_row === '' ? '' : $separator) . '"' . Eisodos::$translator->translateText($column->getProperty('Label')) . '"';
          $i++;
          /* @var array[] $columns
           * @option TComponent 'object'
           * @option string 'dtype'
           */
          $columns[$i]['object'] = $column;
          $columns[$i]['dtype'] = Tholos::$app->findComponentByID($column->getPropertyComponentId('DBField'))->getProperty('Datatype');
        }
      }
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowID) {
        foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowID), 'TComponent') as $id) {
          $column = Tholos::$app->findComponentByID($id);
          if ($column->getProperty('Exportable') === 'true'
            && Tholos::$app->checkRole($column)
            && (Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn')) {
            $exp_row .= ($exp_row === '' ? '' : $separator) . '"' . Eisodos::$translator->translateText($column->getProperty('Label')) . '"';
            $i++;
            /* @var array[] $columns
             * @option TComponent 'object'
             * @option string 'dtype'
             */
            $columns[$i]['object'] = $column;
            $columns[$i]['dtype'] = Tholos::$app->findComponentByID($column->getPropertyComponentId('DBField'))->getProperty('Datatype');
          }
        }
      }
      
      $exp_rows = "\xEF\xBB\xBF" . $exp_row . "\n";
      
      // running data query
      
      /* @var TQuery $listSource */
      $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'));
      $listSource->setProperty('DisableQueryFilters', 'true');
      $listSource->setProperty('FilterArray', $listSource->buildFilters($this));
      $listSource->setProperty('JSONFilters', $this->getProperty('JSONFilters'));
      $listSource->setProperty('Filter', $this->filterSQL);
      // $listSource->setProperty('Filter',$listSource->buildFilters($this).'\n'.$this->filterSQL);
      if ($this->getPropertyComponentId('SortedBy', NULL) !== NULL
        && $this->getProperty('SortedByAlways', '') === '') {
        $listSource->setProperty('OrderBy', Tholos::$app->findComponentByID(
            Tholos::$app->findComponentByID($this->getPropertyComponentId('SortedBy'))->
            getPropertyComponentId('DBField'))->
          getProperty('Index') . ' ' .
          $this->getProperty('SortingDirection'));
      } else {
        $listSource->setProperty('OrderBy', $this->getProperty('SortedByAlways', '1 ASC'));
      }
      $listSource->setProperty('QueryLimit', '0');
      $listSource->setProperty('QueryOffset', '0');
      $listSource->setProperty('CountTotalRows', 'false');
      $listSource->setProperty('Caching', 'Disabled');
      $listSource->run($this);
      
      // build component cache
      
      Tholos::$logger->debug('Export started', $this);
      
      if (1 * $listSource->getProperty('RowCount') > 0) {
        foreach ($listSource->getProperty('Result') as $row) {
          $exp_row = '';
          $listSource->propagateResult($row);
          $rowStarted = false;
          
          foreach ($columns as $column) {
            $v = $column['object']->getProperty('Value');
            if ($column['dtype'] === 'integer' || $column['dtype'] === 'float') {
              $str = Eisodos::$utils->replace_all($v, '.', ',');
            } else {
              $str = Eisodos::$utils->replace_all($v, '"', '""');
              if ($str !== '') {
                $str = '"' . $str . '"';
              }
            }
            $exp_row .= (($exp_row === '' and !$rowStarted) ? '' : $separator) . $str;
            $rowStarted = true;
          }
          
          $exp_rows .= $exp_row . "\n";
        }
      }
      
      Tholos::$logger->debug('Export finished', $this);
      
      // exporting filters
      
      Tholos::$app->responseType = 'CUSTOM'; // force application not to modify output
      Eisodos::$templateEngine->addToResponse($exp_rows);
    }
    
    /**
     * @param $array_
     * @return string
     */
    private function array2csv($array_): string {
      $result = '';
      foreach ($array_ as $row) {
        $row = array_map(static function ($cell) {
          if (preg_match('/["\n,]/', $cell)) {
            return '"' . preg_replace('/"/', '""', $cell) . '"';
          }
          
          return '"' . $cell . '"';
        }, $row);
        $result .= implode(';', $row) . "\n";
      }
      
      return $result;
    }
    
    /**
     * @param $array_
     * @return string
     */
    private function array2tsv($array_): string {
      $result = '';
      foreach ($array_ as $row) {
        $result .= '"' . implode('"' . "\t" . '"', $row) . '"' . "\n";
      }
      
      return $result;
    }
    
    /**
     * @param $type_
     * @throws Exception
     * @throws Throwable
     */
    private function renderRawTSV($type_): void {
      
      ini_set('memory_limit', '-1');
      Eisodos::$parameterHandler->setParam('INCLUDESTATISTIC', 'F');
      Eisodos::$render->Response = '';
      
      $exp_row = '';
      $fieldNames = '';
      
      if (strtolower($type_) === 'rawtsv') {
        $separator = "\t";
        header('Content-Type: text/tab-separated-values');
        header('Content-Disposition: attachment;filename="' . date('YmdHis') . '.tsv"');
      } elseif (strtolower($type_) === 'rawjson') {
        $separator = "\t";
        header('Content-Type: application/json');
        header('Content-Disposition: attachment;filename="' . date('YmdHis') . '.json"');
      } else {
        $separator = ';';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . date('YmdHis') . '.csv"');
      }
      header('Cache-Control: max-age=0');
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TComponent') as $id) {
        $column = Tholos::$app->findComponentByID($id);
        if ($column->getProperty('Exportable') === 'true'
          && Tholos::$app->checkRole($column)
          && (Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn')) {
          if (strtolower($type_) !== 'rawjson') {
            $exp_row .= ($exp_row === '' ? '' : $separator) . '"' . Eisodos::$translator->translateText($column->getProperty('Label')) . '"';
          }
          if ($column->getPropertyComponentId('DBField') !== false) {
            $DBFieldID = $column->getPropertyComponentId('DBField');
            $fieldName_clean = mb_strtolower(Tholos::$app->findComponentByID($DBFieldID)->getProperty('FieldName', ''));
            if (strpos($fieldName_clean, '.')) {
              $fieldName_clean = explode('.', $fieldName_clean, 2)[1];
            }
            if ($fieldName_clean !== '') {
              $fieldNames .= ($fieldNames === '' ? '' : ', ') . $fieldName_clean;
            }
          }
        }
      }
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowID) {
        foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowID), 'TComponent') as $id) {
          $column = Tholos::$app->findComponentByID($id);
          if ($column->getProperty('Exportable') === 'true'
            && Tholos::$app->checkRole($column)
            && (Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn')) {
            if (strtolower($type_) !== 'rawjson') {
              $exp_row .= ($exp_row === '' ? '' : $separator) . '"' . Eisodos::$translator->translateText($column->getProperty('Label')) . '"';
            }
            if ($column->getPropertyComponentId('DBField') !== false) {
              $DBFieldID = $column->getPropertyComponentId('DBField');
              $fieldName_clean = mb_strtolower(Tholos::$app->findComponentByID($DBFieldID)->getProperty('FieldName', ''));
              if (strpos($fieldName_clean, '.')) {
                $fieldName_clean = explode('.', $fieldName_clean, 2)[1];
              }
              if ($fieldName_clean !== '') {
                $fieldNames .= ($fieldNames === '' ? '' : ', ') . $fieldName_clean;
              }
            }
          }
        }
      }
      
      $exp_rows = '';
      
      if (strtolower($type_) !== 'rawjson') {
        $exp_rows = "\xEF\xBB\xBF" . $exp_row . "\n";
      }
      
      // running data query
      
      /* @var TQuery $listSource */
      $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'));
      $listSource->setProperty('DisableQueryFilters', 'true');
      $listSource->setProperty('FilterArray', $listSource->buildFilters($this));
      $listSource->setProperty('JSONFilters', $this->getProperty('JSONFilters'));
      $listSource->setProperty('Filter', $this->filterSQL);
      // $listSource->setProperty('Filter',$listSource->buildFilters($this).'\n'.$this->filterSQL);
      if ($this->getPropertyComponentId('SortedBy', NULL) !== NULL
        && $this->getProperty('SortedByAlways', '') === '') {
        $listSource->setProperty('OrderBy', Tholos::$app->findComponentByID(
            Tholos::$app->findComponentByID($this->getPropertyComponentId('SortedBy'))->
            getPropertyComponentId('DBField'))->
          getProperty('Index') . ' ' .
          $this->getProperty('SortingDirection'));
      } else {
        $listSource->setProperty('OrderBy', $this->getProperty('SortedByAlways', '1 ASC'));
      }
      $listSource->setProperty('QueryLimit', '0');
      $listSource->setProperty('QueryOffset', '0');
      $listSource->setProperty('CountTotalRows', 'false');
      $listSource->setProperty('Caching', 'Disabled');
      $listSource->setProperty('SQL', 'select ' . $fieldNames . ' from (' . $listSource->getProperty('SQL') . ') q');
      $listSource->run($this);
      
      // build component cache
      
      if (strtolower($type_) === 'rawjson') {
        $exp_rows = json_encode($listSource->getProperty('Result', []), JSON_THROW_ON_ERROR);
      } elseif (strtolower($type_) === 'rawcsv') {
        $exp_rows .= $this->array2csv($listSource->getProperty('Result', []));
      } elseif (strtolower($type_) === 'rawtsv') {
        $exp_rows .= $this->array2tsv($listSource->getProperty('Result', []));
      }
      
      Tholos::$logger->debug('Export finished', $this);
      
      // exporting filters
      
      Tholos::$app->responseType = 'CUSTOM'; // force application not to modify output
      Eisodos::$templateEngine->addToResponse($exp_rows);
    }
    
    
    /**
     * @return string
     * @throws Throwable
     */
    private function renderDetails(): string {
      Tholos::$logger->trace('BEGIN', $this);
      
      /* @var TQuery $listSource */
      $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'));
      Tholos::$logger->trace('AutoOpening Grid Query', $this);
      $listSource->setProperty('AutoOpenAllowed', 'true');
      $listSource->autoOpen();
      Tholos::$logger->trace('AutoOpening Grid Query done', $this);
      $result = '';
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TComponent') as $id) {
        $column = Tholos::$app->findComponentByID($id);
        if (!$column) {
          throw new RuntimeException('Invalid reference');
        }
        if ($column->getProperty('Exportable') === 'true'
          && $column->getProperty('DBField', '') !== ''
          && (Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn')) {
          Tholos::$logger->trace('Rendering', $column);
          $result .= Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.details.row',
            array('label' => $column->getProperty('Label'),
              'value' => Eisodos::$utils->ODecode(array(Tholos::$app->findComponentByID($column->getPropertyComponentId('DBField'))->getProperty('DataType', 'string'),
                'bool', Eisodos::$translator->translateText((!in_array($column->getProperty('Value'), explode(',', strtoupper(Eisodos::$parameterHandler->getParam('Tholos.BoolFalse', ''))), false)) ? '[:GRID.FILTER.BOOL_YES,Igen:]' : '[:GRID.FILTER.BOOL_NO,Nem:]'),
                'text', $column->getProperty('Value') ? ("<textarea class=\"form-control\" style=\"height: 150px;\" readonly>" . $column->getProperty("Value") . "</textarea>") : '&nbsp;',
                ($column->getProperty('Value', '') !== '' ? $column->getProperty('Value') : '&nbsp;')
              ))
            ),
            false);
        }
      }
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowID) {
        foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowID), 'TComponent') as $id) {
          $column = Tholos::$app->findComponentByID($id);
          if (!$column) {
            throw new RuntimeException('Invalid reference');
          }
          if ($column->getProperty('Exportable') === 'true'
            && $column->getProperty('DBField', '') !== ''
            && (Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn')) {
            $result .= Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.details.row',
              array('label' => $column->getProperty('Label'),
                'value' => Eisodos::$utils->ODecode(array(Tholos::$app->findComponentByID($column->getPropertyComponentId('DBField'))->getProperty('DataType'),
                  'bool', Eisodos::$translator->translateText((!in_array($column->getProperty('Value'), explode(',', strtoupper(Eisodos::$parameterHandler->getParam('Tholos.BoolFalse', ''))), false)) ? '[:GRID.FILTER.BOOL_YES,Igen:]' : '[:GRID.FILTER.BOOL_NO,Nem:]'),
                  'text', $column->getProperty('Value') ? ("<textarea class=\"form-control\" style=\"height: 150px;\">" . $column->getProperty('Value') . "</textarea>") : '&nbsp;',
                  ($column->getProperty('Value', '') !== '' ? $column->getProperty('Value') : '&nbsp;')
                ))
              ),
              false);
          }
        }
      }
      
      Tholos::$logger->trace('END', $this);
      
      return Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.details.container', array('data' => $result), false);
    }
    
    /**
     * @inheritdoc
     */
    
    public function render(?TComponent $sender, string $content): string {
      
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      $result = '';
      
      Tholos::$logger->debug('render start', $this);
      if (!Tholos::$app->checkRole($this)) {
        return '';
      }
      
      // checking export role
      if (Tholos::$app->roleManager !== NULL && !Tholos::$app->roleManager->checkRole($this->getProperty('ExportFunctionCode', ''))) {
        $this->setProperty('ShowExportButton', 'false');
      }
      
      if (Eisodos::$parameterHandler->eq('TGrid_todo_', 'details')) {
        return $this->renderDetails();
      }
      
      if ($this->getProperty('AutoLoad', 'true') === 'false') {
        $this->setProperty('AJAXMode', 'true');
      }
      
      $this->generateProps();
      
      $this->renderFilters(Tholos::$app->findChildIDsByType($this, 'TGridFilter'));
      $this->generateFilterSQL(Tholos::$app->findChildIDsByType($this, 'TGridFilter'));
      $this->saveFilterState();
      
      $this->renderColumnHeadItems();
      
      if (Eisodos::$parameterHandler->eq('TGrid_todo_', 'excel')) {
        if ($this->getProperty('ShowExportButton', 'false') === 'true') {
          $this->renderExcel();
        }
        
        return '';
      }
      
      if (Eisodos::$parameterHandler->eq('TGrid_todo_', 'tsv')
        || Eisodos::$parameterHandler->eq('TGrid_todo_', 'csv')) {
        if ($this->getProperty('ShowExportButton', 'false') === 'true') {
          $this->renderPlainTextExport(Eisodos::$parameterHandler->getParam('TGrid_todo_'));
        }
        
        return '';
      }
      
      if (Eisodos::$parameterHandler->eq('TGrid_todo_', 'rawtsv')
        || Eisodos::$parameterHandler->eq('TGrid_todo_', 'rawcsv')
        || Eisodos::$parameterHandler->eq('TGrid_todo_', 'rawjson')) {
        if ($this->getProperty('ShowExportButton', 'false') === 'true') {
          $this->renderRawTSV(Eisodos::$parameterHandler->getParam('TGrid_todo_'));
        }
        
        return '';
      }
      
      // setting value
      if ($this->getProperty('LookupValue', '') === '') {
        $this->setProperty('LookupValue', Eisodos::$parameterHandler->getParam('TGrid_Value_'));
      }
      $this->setProperty('Value', $this->getProperty('LookupValue', ''));
      
      $this->generateProps();
      
      $this->setProperty('DataGenerated',
        ($this->getProperty('AJAXMode', 'false') == 'false' or (Eisodos::$parameterHandler->eq('IsAJAXRequest', 'T') and (Tholos::$app->partial_id == $this->_id))) ? "true" : "false");
      Tholos::$logger->debug('DataGenerated: ' . $this->getProperty('DataGenerated', ''), $this);
      
      
      if ($this->getPropertyComponentId('ChartXAxis')) {
        $this->chartComponents[] = Tholos::$app->findComponentByID($this->getPropertyComponentId('ChartXAxis'));
        $this->chartDatasets[] = array();
        foreach (Tholos::$app->findChildIDsByType($this, 'TComponent') as $id) {
          $component = Tholos::$app->findComponentByID($id);
          if ($component->getComponentType() === 'TGridColumn'
            && Tholos::$app->checkRole($component)
            && $component->getProperty('ChartType')) {
            $this->chartComponents[] = $component;
            $this->chartDatasets[] = array();
            $co = $component->getProperty('ChartOptions');
            $co = Eisodos::$utils->replace_all($co, '{', '{' . ' label: "' . $component->getProperty('Label') . '", ' .
              ' type: "' . $component->getProperty('ChartType') . '", ', false, true);
            $this->chartDatasetsOptions[] = $co;
          }
        }
      }
      
      if ($this->getProperty('ViewMode', 'GRID') === 'GRID') {
        $result = $this->renderPartial($this, 'head', '',
            array('filterslots' => $this->filterSlots,
              'filters' => Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.filter.head',
                array('items' => $this->filterDropdown,
                  'filtervisible' => ($this->filterDropdown !== '' ? '' : 'hidden')
                ), false),
              'ShowGridControls' => (($this->filterDropdown !== ''
                || $this->getProperty('ShowRefreshButton') === 'true'
                || $this->getProperty('ShowScrollCheckbox') === 'true'
                || $this->getProperty('ShowExportButton') === 'true'
                || $this->getProperty('ShowTransposeCheckbox') === 'true') ? '' : 'hidden'),
              'columnHeadItems' => $this->columnHeadItems,
              'chartInit' => $this->getPropertyComponentId('ChartXAxis') ? Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.chart.init',
                array('chartDatasetsOptions' => '[' . implode(', ', $this->chartDatasetsOptions) . ']')
                , false) : ''
            )
          ) . "\n";
      }
      
      /* @var TQuery $listSource */
      $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'));
      $listSource->setProperty('DisableQueryFilters', 'true');
      $listSource->setProperty('FilterArray', $listSource->buildFilters($this));
      $this->setProperty('JSONFilters', array_merge($this->getProperty('JSONFilters', []), $listSource->getProperty('JSONFilters', [])));
      // request only headers
      $emptyWhere = '';
      if (!($this->getProperty('AJAXMode', 'false') == 'false' || (Eisodos::$parameterHandler->eq('IsAJAXRequest', 'T') && (Tholos::$app->partial_id == $this->_id)))) {
        $emptyWhere = "\n and 0=1";
        $listSource->setProperty('StructureInfoOnly', 'true');
        $listSource->setProperty('StructureRequester', $this->_id);
        $JSONFilter = [
          'fieldName' => '0',
          'value' => '1',
          'valueArray' => NULL,
          'operator' => 'eq',
          'relation' => NULL,
          'nativeDataType' => NULL,
          'dataType' => NULL,
          'isNull' => false,
          'isNotNull' => false
        ];
        /* @var $filter_array array */
        $filter_array = $this->getProperty('JSONFilters', []);
        $filter_array[] = $JSONFilter;
        $this->setProperty('JSONFilters', $filter_array);
      }
      $listSource->setProperty('JSONFilters', $this->getProperty('JSONFilters'));
      $listSource->setProperty('Filter', $this->filterSQL . $emptyWhere); // TODO a zero based-et lecacheltetni a listSource-val
      if ($this->getPropertyComponentId('SortedBy', NULL) !== NULL
        && $this->getProperty('SortedByAlways', '') === '') {
        $listSource->setProperty('OrderBy', Tholos::$app->findComponentByID(
            Tholos::$app->findComponentByID($this->getPropertyComponentId('SortedBy')
            )->getPropertyComponentId('DBField'))->getProperty('Index') . ' ' .
          $this->getProperty('SortingDirection'));
      } else {
        $listSource->setProperty('OrderBy', $this->getProperty('SortedByAlways', '1 ASC'));
      }
      if ($this->getProperty('ViewMode', 'GRID') === 'GRID') {
        $listSource->setProperty('QueryLimit', $this->getProperty('RowsPerPage', '0'));
        $listSource->setProperty('QueryOffset', (((integer)$this->getProperty('RowsPerPage', '0')) * (((integer)$this->getProperty('ActivePage', '1')) - 1)));
      }
      $listSource->setProperty('CountTotalRows', 'true');
      
      if ($this->getProperty('AJAXMode', 'false') == 'false' || (Eisodos::$parameterHandler->eq("IsAJAXRequest", "T") && (Tholos::$app->partial_id == $this->_id))) {
        $this->setProperty('Caching', 'false');
      }
      
      if ($this->getProperty('Caching', 'false') === 'true'
        && $listSource->getProperty('Caching', 'Disabled') === 'Disabled') { // ha a query cache-d, akkor o intezkedik
        $listSource->setProperty('CacheRefresh', Eisodos::$parameterHandler->getParam('TGrid_RefreshCache', 'false'));
        $listSource->setProperty('CacheValidity', $this->getProperty('CacheValidity', ''));
        $listSource->setProperty('CacheMode', 'ReadWrite');
        $listSource->setProperty('CacheSQLConflict', 'RewriteCache');
        $listSource->setProperty('CacheID', Tholos::$app->findComponentByID(Tholos::$app->getComponentRoute($this->_id))->getProperty('Name') . '_' . $this->getProperty('Name'));
        $listSource->setProperty('Caching', 'Private');
        Tholos::$logger->debug('Query caching turned on by grid', $this);
      }
      
      if (!$this->reloadStateNeeded
        && ($this->getProperty('AJAXMode', 'false') == 'false' ||
          (Eisodos::$parameterHandler->eq('IsAJAXRequest', 'T') && Tholos::$app->partial_id == $this->_id))) {
        if ($this->getProperty('ListsourceAlwaysReopen', 'false') == 'true') {
          $listSource->setProperty('Opened', 'false');
        }
        $listSource->run($this);
      }
      $this->setProperty('RowCount', $listSource->getProperty('RowCount', '0'));
      $this->setProperty('TotalRowCount', $listSource->getProperty('TotalRowCount', '0'));
      
      $subComponents = Tholos::$app->findChildIDsByType($this, 'TComponent');
      
      $selection_found = $this->getProperty('Selectable', 'false') === 'false';
      
      // collecting chart base data
      
      if ($this->getProperty('ViewMode', 'GRID') === 'GRID') {
        
        $rowNum = 0;
        if ($this->getPropertyComponentId('MarkerDBField') !== false) {
          $marker_dbfield = Tholos::$app->findComponentByID($this->getPropertyComponentId('MarkerDBField'));
        } else {
          $marker_dbfield = false;
        }
        
        if (1 * $listSource->getProperty('RowCount', '0') > 0) {
          foreach ($listSource->getProperty('Result') as $row) {
            $columns = '';
            $rowNum++;
            $listSource->propagateResult($row);
            
            if ($marker_dbfield) {
              $this->setProperty('MarkerValue', $marker_dbfield->getProperty('Value', ''));
            }
            
            if (!$selection_found
              && $this->getProperty('DBField', '') !== ''
              && Tholos::$app->findComponentByID($this->getPropertyComponentId('DBField'))->getProperty('Value', '') === $this->getProperty('LookupValue', '')) {
              $selection_found = true;
            }
            
            if ($this->getProperty('Transposed', 'false') === 'false') {
              
              $hasAnyStandaloneGridColumn = false;
              
              foreach ($subComponents as $id) {
                $component = Tholos::$app->findComponentByID($id);
                if ($component->getComponentType() === 'TGridColumn') {
                  if ($component->getProperty('Visible', 'false') === 'true'
                    && Tholos::$app->checkRole($component)) {
                    if ($component->getProperty('Template', '') !== '') {
                      $component->generateProps();
                      $columns .= Eisodos::$templateEngine->getTemplate($component->getProperty('Template', ''), array(), false);
                    } elseif ($component->getProperty('ValueTemplate', '') !== '') {
                      $component->generateProps();
                      $component->setProperty('Value', 'HTML::' . Eisodos::$templateEngine->getTemplate($component->getProperty('ValueTemplate', ''), array(), false));
                      $columns .= $component->render($this, '');
                    } else {
                      $columns .= $component->render($this, '');
                    }
                  }
                } elseif ($component->getComponentType() === 'TGridRowActions'
                  && $component->getProperty('Visible', 'false') === 'true'
                  && Tholos::$app->checkRole($component)) {
                  $columns .= '<' . $this->getProperty('cellType', '') .
                    ' class="TGrid-resp-cell text-nowrap ' . $component->getProperty('Class', '') . ' ' . ($component->getProperty('Align', '') ? ' text-' . $component->getProperty('Align', '') : '') . '" ' .
                    ($component->getProperty('ColumnSpan', '') ? ' colspan="' . $component->getProperty('ColumnSpan', '') . '" ' : '') .
                    ($component->getProperty('Style', '') ? ' style="' . $component->getProperty('Style', '') . '"' : '') .
                    ' data-resptitle="' . $component->getProperty('Label', '') . '" ' .
                    ' >' . Tholos::$app->render($this, $id, true) . '</' . $this->getProperty('cellType', '') . '>';
                }
              }
              if ($columns !== '') {
                if ($this->getPropertyComponentId('DBField') !== false) {
                  $columns = $this->renderPartial($this, 'selectable') . $columns;
                }
                $result .= $this->renderPartial($this, 'row', $columns) . "\n";
                $hasAnyStandaloneGridColumn = true;
              }
              
              // a row-ba rendezett komponensek jonnek
              foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowID) {
                $columns = '';
                $isEmptyRow = true;
                foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowID), 'TComponent') as $id) {
                  $component = Tholos::$app->findComponentByID($id);
                  if ($columns === ''
                    && $hasAnyStandaloneGridColumn
                    && $rowNum === 1
                    && $this->getPropertyComponentId('DBField') !== false) {
                    $component->setProperty('ColumnOffset', 1 * $component->getProperty('ColumnOffset', '0') + 1);
                  }
                  if ($component->getComponentType() === 'TGridColumn') {
                    if ($component->getProperty('Visible', 'false') === 'true'
                      && Tholos::$app->checkRole($component)) {
                      $isEmptyRow = ($isEmptyRow and ($component->getProperty('Value', '') === ''));
                      if ($component->getProperty('Template', '') !== '') {
                        $component->generateProps();
                        $columns .= Eisodos::$templateEngine->getTemplate($component->getProperty('Template', ''), array(), false);
                      } elseif ($component->getProperty('ValueTemplate', '') !== '') {
                        $component->generateProps();
                        $component->setProperty('Value', 'HTML::' . Eisodos::$templateEngine->getTemplate($component->getProperty('ValueTemplate', ''), array(), false));
                        $columns .= $component->render($this, '');
                      } else {
                        $columns .= $component->render($this, '');
                      }
                    }
                  } elseif ($component->getComponentType() === 'TGridRowActions'
                    && $component->getProperty('Visible', 'false') === 'true'
                    && Tholos::$app->checkRole($component)) {
                    $isEmptyRow = false;
                    $columns .= '<' . $this->getProperty('cellType', '') . ' ' .
                      ' class="TGrid-resp-cell text-nowrap ' . $component->getProperty('Class', '') . ' ' . ($component->getProperty('Align', '') ? ' text-' . $component->getProperty('Align', '') : '') . '" ' .
                      ($component->getProperty('ColumnSpan', '') ? ' colspan="' . $component->getProperty('ColumnSpan', '') . '" ' : '') .
                      ($component->getProperty('Style', '') ? ' style="' . $component->getProperty('Style', '') . '"' : '') .
                      ' >' . Tholos::$app->render($this, $id, true) . '</' . $this->getProperty('cellType', '') . '>';
                  }
                }
                if (!$isEmptyRow || Tholos::$app->findComponentByID($rowID)->getProperty('HideWhenEmpty', 'false') === 'false') {
                  if ($this->getPropertyComponentId('DBField') !== false) {
                    if ($hasAnyStandaloneGridColumn) {
                      $columns = $this->renderPartial($this, 'noselectable') . $columns;
                    } else {
                      $columns = $this->renderPartial($this, 'selectable') . $columns;
                    }
                  }
                  $result .= Tholos::$app->findComponentByID($rowID)->render($this, $columns) . "\n";
                }
              }
            } else {
              
              if ($this->getPropertyComponentId('DBField') !== false) {
                $this->transposedRows['__TransposedHeader'] .= $this->renderPartial($this, 'headtransposed');
              }
              
              foreach ($subComponents as $id) {
                $component = Tholos::$app->findComponentByID($id);
                if ($component->getComponentType() === 'TGridColumn') {
                  if ($component->getProperty('Visible', 'false') === 'true'
                    && Tholos::$app->checkRole($component)) {
                    if ($component->getProperty('Template', '') !== '') {
                      $component->generateProps();
                      $this->transposedRows[$component->getProperty('Name')] .= Eisodos::$templateEngine->getTemplate($component->getProperty('Template', ''), array(), false);
                    } elseif ($component->getProperty('ValueTemplate', '') !== '') {
                      $component->generateProps();
                      $component->setProperty('Value', 'HTML::' . Eisodos::$templateEngine->getTemplate($component->getProperty('ValueTemplate', ''), array(), false));
                      $this->transposedRows[$component->getProperty('Name')] .= $component->render($this, '');
                    } else {
                      $this->transposedRows[$component->getProperty('Name')] .= $component->render($this, '');
                    }
                  }
                } elseif ($component->getComponentType() === 'TGridRowActions'
                  && $component->getProperty('Visible', 'false') === 'true'
                  && Tholos::$app->checkRole($component)) {
                  $this->transposedRows[$component->getProperty('Name')] .= '<' . $this->getProperty('cellType', '') . ' ' .
                    ' class="TGrid-resp-cell ' . $component->getProperty('Class', '') . ' ' . ($component->getProperty('Align', '') ? ' text-' . $component->getProperty('Align', '') : '') . '" ' .
                    ($component->getProperty('Style', '') ? ' style="' . $component->getProperty('Style', '') . '"' : '') .
                    ' >' . Tholos::$app->render($this, $id, true) . '</' . $this->getProperty('cellType', '') . '>';
                }
              }
              
              foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowID) {
                foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowID), 'TComponent') as $id) {
                  $component = Tholos::$app->findComponentByID($id);
                  if (!$component) {
                    throw new RuntimeException('Invalid reference');
                  }
                  $component->setProperty('ColumnOffset', '0');
                  $component->setProperty('ColumnSpan', '');
                  if ($component->getComponentType() === 'TGridColumn') {
                    if ($component->getProperty('Visible', 'false') === 'true'
                      && Tholos::$app->checkRole($component)) {
                      if ($component->getProperty('Template', '') !== '') {
                        $component->generateProps();
                        $this->transposedRows[$component->getProperty('Name')] .= Eisodos::$templateEngine->getTemplate($component->getProperty('Template', ''), array(), false);
                      } elseif ($component->getProperty('ValueTemplate', '') !== '') {
                        $component->generateProps();
                        $component->setProperty('Value', 'HTML::' . Eisodos::$templateEngine->getTemplate($component->getProperty('ValueTemplate', ''), array(), false));
                        $this->transposedRows[$component->getProperty('Name')] .= $component->render($this, '');
                      } else {
                        $this->transposedRows[$component->getProperty('Name')] .= $component->render($this, '');
                      }
                    }
                  } elseif ($component->getComponentType() === 'TGridRowActions'
                    && $component->getProperty('Visible', 'false') === 'true'
                    && Tholos::$app->checkRole($component)) {
                    $this->transposedRows[$component->getProperty('Name')] .= '<' . $this->getProperty('cellType', '') . ' ' .
                      ' class="TGrid-resp-cell ' . $component->getProperty('Class', '') . ' ' . ($component->getProperty('Align', '') ? ' text-' . $component->getProperty('Align', '') : '') . '" ' .
                      ($component->getProperty('Style', '') ? ' style="' . $component->getProperty('Style', '') . '"' : '') .
                      ' >' . Tholos::$app->render($this, $id, true) . '</' . $this->getProperty('cellType', '') . '>';
                  }
                }
              }
              
            }
          }
          
        }
        
        if ($this->getProperty('Transposed', 'false') === 'true') {
          foreach ($this->transposedRows as $transposedRow) {
            $result .= $this->renderPartial($this, 'rowtransposed', $transposedRow) . "\n";
          }
        }
        
        if ($this->getProperty('RowsPerPage', '0') === '0') {
          $pageCount = 1;
        } else {
          $pageCount = ceil((1 * $this->getProperty('TotalRowCount', '0')) / $this->getProperty('RowsPerPage', '0'));
        }
        $activePage = 1 * ($this->getProperty('ActivePage', '1'));
        $this->setProperty('PageCount', $pageCount);
        
        $this->generateProps();
        
        $pageItems = '';
        
        if ((integer)$this->getProperty('RowsPerPage', '0') > 0 /* and $pageCount>1 */) {
          if (max(min($activePage - 5, $pageCount - 10), 1) > 1) {
            $pageItems .= Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.pagination.item',
              array('pagenum' => max(min($activePage - 5, $pageCount - 10), 1) - 1,
                'pagenumtext' => '..',
                'pageactive' => ''
              ), false);
          }
          for ($i = max(min($activePage - 5, $pageCount - 10), 1); $i <= min(max($activePage - 5, 1) + 10, $pageCount); $i++) {
            $pageItems .= Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.pagination.item',
              array('pagenum' => $i,
                'pagenumtext' => $i,
                'pageactive' => ($i === $activePage ? 'active' : '')
              ), false);
          }
          if (min(max($activePage - 5, 1) + 10, $pageCount) < $pageCount) {
            $pageItems .= Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.pagination.item',
              array('pagenum' => min(max($activePage - 5, 1) + 10, $pageCount) + 1,
                'pagenumtext' => '..',
                'pageactive' => ''
              ), false);
          }
          
          $pagination = Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.pagination.main',
              array('items' => $pageItems,
                'lastpage' => $pageCount,
                'isfirstpage' => ($activePage === 0 || $activePage === 1) ? 'T' : 'F'),
              false) . "\n";
          
        } else {
          $pagination = '';
        }

//      \PC::debug(array($this->getProperty('Selectable','false')=='true',$selection_found,$this->getProperty('DBField','')!='',$this->getProperty('LookupValue','')));
        
        if ($listSource->getProperty('CacheUsed', 'false') === 'true'
          && $this->getProperty('ShowCacheInfo', 'true') === 'true'
          && Eisodos::$utils->safe_array_value($listSource->getProperty('CacheInfo'), 'updated') !== '') {
          $dateValueObj = DateTime::createFromFormat('YmdHis', Eisodos::$utils->safe_array_value($listSource->getProperty('CacheInfo'), 'updated'));
          $dateValue = $dateValueObj->format(Eisodos::$parameterHandler->getParam('PHP' . $this->getProperty('DateFormatParameter', 'datetime') . 'Format'));
          $since_start = $dateValueObj->diff(new DateTime());
          $minutes = $since_start->days * 24 * 60;
          $minutes += $since_start->h * 60;
          $minutes += $since_start->i;
          $cacheInfo = Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.cache.info', ['cacheupdated' => $dateValue . ' (' . $minutes . ') '], false);
        } else {
          $cacheInfo = '';
        }
        
        $result .= $this->renderPartial($this, 'foot', '',
          array('pagination' => $pagination,
            'selectionoutoflist' => (($this->getProperty('Selectable', 'false') === 'true'
              && !$selection_found
              && $this->getProperty('DBField', '') !== ''
              && $this->getProperty('LookupValue', '') !== '') ? $this->getProperty('LabelSelectionOutOfList', '') : ''),
            'cacheinfo' => $cacheInfo
          )
        );
      } else if ($this->getProperty('ViewMode') === 'CHART') {
        
        /*
          desired format for chart data:
         
            [
             0=>[x1,x2,x3,x4]  -- labels
             1=>[y1,y2,y3,y4]  -- first chart data
             ...
             n=>[y1,y2,y3,y4]  -- n. chart data
            ]
        
          this will be converted to JSONDATA
        
          ---
       */
        
        foreach ($listSource->getProperty('Result') as $row) {
          
          $listSource->propagateResult($row);
          $i = 0;
          foreach ($this->chartComponents as $chartComponent) {
            $this->chartDatasets[$i][] = $chartComponent->getProperty('Value');
            $i++;
          }
          
        }
        Tholos::$app->responseType = 'JSONDATA';
        Eisodos::$parameterHandler->setParam('responseType', 'JSONDATA');
        Tholos::$app->responseARRAY['data'] = json_encode($this->chartDatasets, JSON_THROW_ON_ERROR);
        
      }
      
      Tholos::$logger->debug('Render ended', $this);
      
      $this->renderedContent = $result;
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return $this->renderedContent;
      
    }
  }
  