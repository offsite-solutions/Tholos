<?php /** @noinspection NullPointerExceptionInspection DuplicatedCode SpellCheckingInspection PhpUnusedFunctionInspection NotOptimalIfConditionsInspection */
  
  namespace Tholos;
  
  use DateTime;
  use Eisodos\Eisodos;
  use Exception;
  use PhpOffice\PhpSpreadsheet\Cell\Coordinate as CellCoordinate;
  use PhpOffice\PhpSpreadsheet\Cell\DataType as CellDataType;
  use PhpOffice\PhpSpreadsheet\Spreadsheet;
  use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
  
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
    private array $usersettings = array();
    
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
     * @throws Exception Throws exception
     * @throws Throwable
     */
    public function init(): void {
      
      Tholos::$app->trace('BEGIN', $this);
      Tholos::$app->trace('(' . $this->_componentType . ') (ID ' . $this->_id . ')', $this);
      parent::init();
      Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'))->setProperty('AutoOpenAllowed', 'false');
      
      $this->setProperty('cellHeadType', $this->getProperty('GridHTMLType') === 'table' ? 'th' : 'div');
      $this->setProperty('cellType', $this->getProperty('GridHTMLType') === 'table' ? 'td' : 'div');
      $this->setProperty('cellRowType', $this->getProperty('GridHTMLType') === 'table' ? 'tr' : 'div');
      
      if ($this->getProperty('GridHTMLType') !== 'table') {
        $this->setProperty('ShowTransposeCheckbox', 'false');
        $this->setProperty('ShowScrollCheckbox', 'false');
        $this->setProperty('Selectable', 'false');
        $this->setProperty('Scrollable', 'false');
        $this->setProperty('Transposed', 'false');
      }
      
      //if (!Tholos::$app->findComponentByID($this->getPropertyComponentId('SortedBy'))) {
      //  Tholos::$app->error('TGrid configuration error: mandatory SortedBy property was not specified', $this);
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
      // $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'));
      if ($this->getPropertyComponentId('SortedBy', NULL) !== NULL
        && $this->getProperty('SortedByAlways', '') === '') {
        $origOrderBy = Tholos::$app->findComponentByID(
            Tholos::$app->findComponentByID($this->getPropertyComponentId('SortedBy')
            )->getPropertyComponentId('DBField'))->getProperty('Index') . ' ' .
          $this->getProperty('SortingDirection', 'ASC');
      } else {
        $origOrderBy = $this->getProperty('SortedByAlways', '1 ASC');
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
            foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowid) {
              $gridRow = Tholos::$app->findComponentByID($rowid);
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
        
        if ($this->getPropertyComponentId('SortedBy', NULL) !== NULL) {
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
      
      if (Eisodos::$parameterHandler->neq('TGrid_Transposed_', '')) {
        $this->setProperty('Transposed', Eisodos::$parameterHandler->getParam('TGrid_Transposed_'));
      }
      
      if (Eisodos::$parameterHandler->neq('TGrid_MasterValue_', '')) {
        $this->setProperty('MasterValue', Eisodos::$parameterHandler->getParam('TGrid_MasterValue_'));
      }
      
      if (Eisodos::$parameterHandler->neq('TGrid_ViewMode_', '')) {
        $this->setProperty('ViewMode', Eisodos::$parameterHandler->getParam('TGrid_ViewMode_'));
      }
      
      $this->saveUserSettings();
      
      Tholos::$app->trace('END', $this);
    }
    
    /**
     * @throws Exception Throws exception
     */
    private function loadState(): void {
      
      // loading previous state
      $n = $this->getProperty('name', '') . '_f_';
      if (Eisodos::$parameterHandler->eq('TGrid_todo_', 'reloadState') and $this->getProperty('UUID', '') !== '') { // grid visszatoltese az utolso allapotra
        if ($this->getProperty('Persistent', '') === 'DATABASE') {  // utolso filterek
          $this->last_filters = @unserialize(getSQLback(Tholos::$c->db, 'select value from cor_session_parameters where session_id=' . n(session_id()) . ' and parameter_name=' . n($this->getProperty('Name', '') . '.filters.' . $this->getProperty('UUID', ''))), false);
          $this->usersettings = @unserialize(getSQLback(Tholos::$c->db, 'select value from cor_session_parameters where session_id=' . n(session_id()) . ' and parameter_name=' . n($this->getProperty('Name', '') . '.grid.' . $this->getProperty('UUID', ''))), false);
        } elseif ($this->getProperty('Persistent', '') === 'SESSION') {
          if ($prefix = $this->getProperty('PersistencyPrefix')) {
            $prefix = '';
          }
          $this->last_filters = @unserialize($prefix . Eisodos::$parameterHandler->getParam($this->getProperty('Name', '') . '.filters.' . $this->getProperty('UUID', '')), false);
          $this->usersettings = @unserialize($prefix . Eisodos::$parameterHandler->getParam($this->getProperty('Name', '') . '.grid.' . $this->getProperty('UUID', '')), false);
        }
        
        for ($i = 1; $i < 100; $i++) {
          Eisodos::$parameterHandler->setParam($n . $i);
        }
        if (is_array($this->last_filters)) {
          foreach ($this->last_filters as $filter => $value) {
            Eisodos::$parameterHandler->setParam($filter, $value);
          }
        }
        
        if (is_array($this->usersettings)) {
          foreach ($this->usersettings as $key => $value) {
            Eisodos::$parameterHandler->setParam($key, $value);
          }
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
        if (getSQLback(Tholos::$c->db, "select session_id \n" .
            "  from cor_session_parameters \n" .
            " where session_id=" . n(session_id()) . "\n" .
            "       and parameter_name=" . n($this->getProperty("Name", "") . ".filters." . $this->getProperty("UUID", ""))) !== "") {
          $sql = "update cor_session_parameters \n" .
            "   set value=? \n" .
            " where session_id=" . n(session_id()) . "\n" .
            "       and parameter_name=" . n($this->getProperty("Name", "") . ".filters." . $this->getProperty("UUID", ""));
        } else {
          $sql = "INSERT INTO cor_session_parameters \n" .
            "  (session_id,parameter_name,value) VALUES \n" .
            "  (" . n(session_id()) . "," . n($this->getProperty("Name", "") . ".filters." . $this->getProperty("UUID", "")) . ",?)";
        }
        
        Tholos::$c->getDBByIndex(1)->beginTransaction();
        runSQLPrep(Tholos::$c->db,
          $sql,
          array('text'),
          array(serialize($this->last_filters))
        );
        Tholos::$c->getDBByIndex(1)->commit();
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
      
      $this->usersettings['TGrid_SortedBy_'] = Eisodos::$parameterHandler->getParam('TGrid_SortedBy_');
      $this->usersettings['TGrid_SortingDirection_'] = Eisodos::$parameterHandler->getParam('TGrid_SortingDirection_');
      $this->usersettings['TGrid_ActivePage_'] = Eisodos::$parameterHandler->getParam('TGrid_ActivePage_');
      $this->usersettings['TGrid_RowsPerPage_'] = Eisodos::$parameterHandler->getParam('TGrid_RowsPerPage_');
      $this->usersettings['TGrid_Scrollable_'] = Eisodos::$parameterHandler->getParam('TGrid_Scrollable_');
      
      if ($this->getProperty('Persistent', '') === 'DATABASE') {
        if (getSQLback(Tholos::$c->db, 'select session_id from cor_session_parameters where session_id=' . n(session_id()) . ' and parameter_name=' . n($this->getProperty('Name', '') . '.grid.' . $this->getProperty('UUID', ''))) !== '') {
          $sql = 'update cor_session_parameters set value=? where session_id=' . n(session_id()) . ' and parameter_name=' . n($this->getProperty('Name', '') . '.grid.' . $this->getProperty('UUID', ''));
        } else {
          $sql = 'insert into cor_session_parameters (session_id,parameter_name,value) values (' . n(session_id()) . ',' . n($this->getProperty('Name', '') . '.grid.' . $this->getProperty('UUID', '')) . ',?)';
        }
        
        Tholos::$c->getDBByIndex(1)->beginTransaction();
        runSQLPrep(Tholos::$c->db,
          $sql,
          array('text'),
          array(serialize($this->usersettings))
        );
        Tholos::$c->getDBByIndex(1)->commit();
      } elseif ($this->getProperty('Persistent', '') === 'SESSION') {
        if ($prefix = $this->getProperty('PersistencyPrefix')) {
          $prefix = '';
        }
        Eisodos::$parameterHandler->setParam($prefix . $this->getProperty('Name', '') . '.grid.' . $this->getProperty('UUID', ''), serialize($this->usersettings), true);
      }
    }
    
    /**
     * @param $filters
     * @throws Throwable
     */
    private function renderFilters($filters): void {
      
      $this->filterDropdown = '';
      
      foreach ($filters as $filterid) {
        /* @var TGridFilter $filter */
        $filter = Tholos::$app->findComponentByID($filterid);
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
     * @param $value
     * @return string
     */
    private function boolConvert($value) {
      if (Eisodos::$parameterHandler->eq('Tholos.UseLogicalBool', 'true')) {
        if (in_array($value, explode(',', strtoupper(Eisodos::$parameterHandler->getParam('Tholos.BoolFalse', ''))), false)) {
          $value = 'false';
        } else {
          $value = 'true';
        }
      } else {
        $value = n($value, true);
      }
      
      return $value;
    }
    
    /**
     * @param $filters
     */
    private function generateFilterSQL($filters): void {
      
      $this->filterSQL = '';
      
      // generating filter SQL
      $n = $this->getProperty('name', '') . '_f_';
      for ($i = 1; $i < 100; $i++) {
        if (Eisodos::$parameterHandler->neq($n . $i, '')) { // ha van az adott parameterben valami
          $filterParam = explode(':', Eisodos::$parameterHandler->getParam($n . $i), 3);
          $filter = false;
          $dbField = NULL;
          foreach ($filters as $filterid) {
            $filter = Tholos::$app->findComponentByID($filterid);
            if ($filter->getProperty('Name') === $filterParam[0]) {
              $dbField = Tholos::$app->findComponentByID($filter->getPropertyComponentId('DBField'));
              break;
            }
          }
          if (!$filter or $dbField === NULL) {
            Tholos::$app->error('No filter defined: ' . $filterParam[0], $this);
          } else {
            $this->filterSQL .= ' and ' .
              (($filterParam[1] === 'like' or $filterParam[1] === 'nlike') ? 'lower(' . $dbField->getProperty('FieldName') . ')' : $dbField->getProperty('FieldName')) .
              sprintf(Eisodos::$utils->ODecode(array($filterParam[1],
                'NULL', ' IS NULL',
                'NOT NULL', ' IS NOT NULL',
                'eq', '=%s',
                'neq', '!=%s',
                'like', ' like lower(%s) ',
                'nlike', ' not like lower(%s) ',
                'gt', '>%s',
                'gteq', '>=%s',
                'lt', '<%s',
                'lteq', '<=%s',
                'bw', $filter->getProperty('SQL', ''),
                'in', ' in %s ',
                'notin', ' not in %s '
              )),
                Eisodos::$utils->ODecode(array($dbField->getProperty('datatype'),
                    'string', (in_array($filterParam[1], ['in', 'notin']) ? nlist(@$filterParam[2], true) : n(@$filterParam[2], true)),
                    'text', n(@$filterParam[2], true),
                    'bool', @$filterParam[2] === '*' ? $dbField->getProperty('FieldName') : $this->boolConvert(@$filterParam[2]),
                    'boolYN', @$filterParam[2] === '*' ? $dbField->getProperty('FieldName') : n(@$filterParam[2], true),
                    'boolIN', @$filterParam[2] === '*' ? $dbField->getProperty('FieldName') : n(@$filterParam[2], true),
                    'bool10', @$filterParam[2] === '-1' ? $dbField->getProperty('FieldName') : n(@$filterParam[2], false),
                    'list', n(@$filterParam[2], true),
                    'date', "to_date('" . @$filterParam[2] . "','" . Eisodos::$parameterHandler->getParam("dateformat") . "')",
                    'datetime', "to_date('" . @$filterParam[2] . "','" . Eisodos::$parameterHandler->getParam("datetimeformat") . "')",
                    'datetimehm', "to_date('" . @$filterParam[2] . "','" . Eisodos::$parameterHandler->getParam("datetimehmformat") . "')",
                    'time', "to_date('" . @$filterParam[2] . "','" . Eisodos::$parameterHandler->getParam("timeformat") . "')",
                    'timestamp', "to_date('" . @$filterParam[2] . "','" . Eisodos::$parameterHandler->getParam("timestampformat") . "')",
                    'datebetween', "to_date('" . @$filterParam[2] . "','" . Eisodos::$parameterHandler->getParam("dateformat") . "')",
                    'integer', (in_array($filterParam[1], ['in', 'notin']) ? nlist(@$filterParam[2], false) : n(@$filterParam[2], false)),
                    'float', (in_array($filterParam[1], ["in", "notin"]) ? nlist(@$filterparam[2], false) : n(@$filterparam[2], false))
                  )
                )
              ) . " \n";
          }
        }
      }
      
      if ($this->getPropertyComponentId('MasterDBField', NULL) !== NULL) {
        if ($this->getProperty('MasterValue', '') === '') {
          $this->filterSQL .= "\n and 0=1 ";
          $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'));
          $listSource->setProperty('StructureInfoOnly', 'true');
          $listSource->setProperty('StructureRequester', $this->_id);
        } else {
          $dbField = Tholos::$app->findComponentByID($this->getPropertyComponentId('MasterDBField', NULL));
          $masterValue = $this->getProperty('MasterValue', '');
          $this->filterSQL .= ' and ' .
            $dbField->getProperty('FieldName') .
            sprintf('=%s',
              Eisodos::$utils->ODecode(array($dbField->getProperty('datatype'),
                  'string', n($masterValue, true),
                  'date', "to_date('" . $masterValue . "','" . Eisodos::$parameterHandler->getParam("dateformat") . "')",
                  'datetime', "to_date('" . $masterValue . "','" . Eisodos::$parameterHandler->getParam("datetimeformat") . "')",
                  'datetimehm', "to_date('" . $masterValue . "','" . Eisodos::$parameterHandler->getParam("datetimehmformat") . "')",
                  'time', "to_date('" . $masterValue . "','" . Eisodos::$parameterHandler->getParam("timeformat") . "')",
                  'timestamp', "to_date('" . $masterValue . "','" . Eisodos::$parameterHandler->getParam("timestampformat") . "')",
                  'integer', n($masterValue, false),
                  'float', n($masterValue, false)
                )
              )
            ) . " \n";
        }
      }
      
      $this->setProperty('FilterSQL', str_replace("\n", ' ', $this->filterSQL));
      
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
        if ($column) {
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
          if ((Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn') && $column->getProperty('Exportable') === 'true') {
            $exportable = true;
          }
        }
      }
      
      if (!$transposed && $hasAnyStandaloneGridColumn) {
        $this->columnHeadItems .= $this->renderPartial($this, 'headitems', implode($items));
      }
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowid) {
        if (!$transposed && $hasAnyStandaloneGridColumn) {
          $items = array();
        }
        if ($transposed || Tholos::$app->findComponentByID($rowid)->getProperty('ShowColumnHead', '') === 'true') {
          if (!$transposed && $this->getPropertyComponentId('DBField') !== false) {
            $items['__TransposedHeader'] = '<' . $this->getProperty('cellHeadType', '') . ' class="TGrid-resp-header" style="width: 25px; max-width: 25px;" data-resizable-column-id="">&nbsp;</' . $this->getProperty('cellHeadType', '') . '>';
          }
        }
        foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowid), 'TComponent') as $id) {
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
        if (count($items) > 0 and !$transposed and Tholos::$app->findComponentByID($rowid)->getProperty('ShowColumnHead', '') == 'true') {
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
        if (Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn'
          && $column->getProperty('Exportable') === 'true'
          && Tholos::$app->checkRole($column)) {
          $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($j, 1)->
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
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowid) {
        foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowid), 'TComponent') as $id) {
          $column = Tholos::$app->findComponentByID($id);
          if (Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn'
            && $column->getProperty('Exportable') === 'true'
            && Tholos::$app->checkRole($column)) {
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($j, 1)->
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
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($j, $i)->setValueExplicit($column['object']->getProperty('Value'), $column['etype']);
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
          $filterparam = explode(':', Eisodos::$parameterHandler->getParam($n . $i), 3);
          $filter = false;
          $dbfield = NULL;
          foreach (Tholos::$app->findChildIDsByType($this, 'TGridFilter') as $filterid) {
            $filter = Tholos::$app->findComponentByID($filterid);
            if ($filter->getProperty('Name') === $filterparam[0]) {
              $dbfield = Tholos::$app->findComponentByID($filter->getPropertyComponentId('DBField'));
              break;
            }
          }
          if (!$filter or $dbfield === NULL) {
            Tholos::$app->error('No filter definied: ' . $filterparam[0], $this);
          } else {
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, $j)->
            setValueExplicit(Eisodos::$translator->translateText($filter->getProperty('Label')), CellDataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2, $j)->
            setValueExplicit(Eisodos::$utils->ODecode(array($filterparam[1],
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
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3, $j)->setValueExplicit($filterparam[2], CellDataType::TYPE_STRING);
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
        header('Cache-Control: max-age=0');
      } else {
        $separator = ';';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
      }
      
      $columns = array();
      
      $i = -1;
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TComponent') as $id) {
        $column = Tholos::$app->findComponentByID($id);
        if ((Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn')
          && $column->getProperty('Exportable') === 'true'
          && Tholos::$app->checkRole($column)) {
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
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowid) {
        foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowid), 'TComponent') as $id) {
          $column = Tholos::$app->findComponentByID($id);
          if ((Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn')
            && $column->getProperty('Exportable') === 'true'
            && Tholos::$app->checkRole($column)) {
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
      
      Tholos::$app->debug('Export started', $this);
      
      $i = 1;
      if (1 * $listSource->getProperty('RowCount') > 0) {
        foreach ($listSource->getProperty('Result') as $row) {
          $exp_row = '';
          $listSource->propagateResult($row);
          $i++;
          $rowstarted = false;
          
          foreach ($columns as $column) {
            $v = $column['object']->getProperty('Value');
            if ($column['dtype'] === 'integer' or $column['dtype'] === 'float') {
              $str = Eisodos::$utils->replace_all($v, '.', ',');
            } else {
              $str = Eisodos::$utils->replace_all($v, '"', '""');
              if ($str !== '') {
                $str = '"' . $str . '"';
              }
            }
            $exp_row .= (($exp_row === '' and !$rowstarted) ? '' : $separator) . $str;
            $rowstarted = true;
          }
          
          $exp_rows .= $exp_row . "\n";
        }
      }
      
      Tholos::$app->debug('Export finished', $this);
      
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
        header('Cache-Control: max-age=0');
      } elseif (strtolower($type_) === 'rawjson') {
        $separator = "\t";
        header('Content-Type: application/json');
        header('Content-Disposition: attachment;filename="' . date('YmdHis') . '.json"');
        header('Cache-Control: max-age=0');
      } else {
        $separator = ';';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
      }
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TComponent') as $id) {
        $column = Tholos::$app->findComponentByID($id);
        if ((Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn') && $column->getProperty('Exportable') === 'true'
          && Tholos::$app->checkRole($column)) {
          if (strtolower($type_) !== 'rawjson') {
            $exp_row .= ($exp_row === '' ? '' : $separator) . '"' . Eisodos::$translator->translateText($column->getProperty('Label')) . '"';
          }
          if ($column->getPropertyComponentId('DBField') !== false) {
            $dbfieldId = $column->getPropertyComponentId('DBField');
            $fieldName_clean = mb_strtolower(Tholos::$app->findComponentByID($dbfieldId)->getProperty('FieldName', ''));
            if (strpos($fieldName_clean, '.')) {
              $fieldName_clean = explode('.', $fieldName_clean, 2)[1];
            }
            if ($fieldName_clean !== '') {
              $fieldNames .= ($fieldNames === '' ? '' : ', ') . $fieldName_clean;
            }
          }
        }
      }
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowid) {
        foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowid), 'TComponent') as $id) {
          $column = Tholos::$app->findComponentByID($id);
          if ((Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn')
            && $column->getProperty('Exportable') === 'true'
            && Tholos::$app->checkRole($column)) {
            if (strtolower($type_) !== 'rawjson') {
              $exp_row .= ($exp_row === '' ? '' : $separator) . '"' . Eisodos::$translator->translateText($column->getProperty('Label')) . '"';
            }
            if ($column->getPropertyComponentId('DBField') !== false) {
              $dbfieldId = $column->getPropertyComponentId('DBField');
              $fieldName_clean = mb_strtolower(Tholos::$app->findComponentByID($dbfieldId)->getProperty('FieldName', ''));
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
      
      Tholos::$app->debug('Export finished', $this);
      
      // exporting filters
      
      Tholos::$app->responseType = 'CUSTOM'; // force application not to modify output
      Eisodos::$templateEngine->addToResponse($exp_rows);
    }
    
    
    /**
     * @return string
     * @throws Throwable
     */
    private function renderDetails(): string {
      Tholos::$app->trace('BEGIN', $this);
      
      /* @var TQuery $listSource */
      $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'));
      Tholos::$app->trace('AutoOpening Grid Query', $this);
      $listSource->setProperty('AutoOpenAllowed', 'true');
      $listSource->autoOpen();
      Tholos::$app->trace('AutoOpening Grid Query done', $this);
      $result = '';
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TComponent') as $id) {
        $column = Tholos::$app->findComponentByID($id);
        if (!$column) {
          throw new RuntimeException('Invalid reference');
        }
        if ((Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn') && $column->getProperty('Exportable') === 'true' and $column->getProperty('DBField', '') !== '') {
          Tholos::$app->trace('Rendering', $column);
          $result .= Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.details.row',
            array('label' => $column->getProperty('Label'),
              'value' => Eisodos::$utils->ODecode(array(Tholos::$app->findComponentByID($column->getPropertyComponentId('DBField'))->getProperty('DataType', 'string'),
                'bool', Eisodos::$utils->ODecode($column->getProperty('Value') === 'Y' ? '[:GRID.FILTER.BOOL_YES,Igen:]' : '[:GRID.FILTER.BOOL_NO,Nem:]'),
                'text', $column->getProperty('Value') ? ("<textarea class=\"form-control\" style=\"height: 150px;\" readonly>" . $column->getProperty("Value") . "</textarea>") : '&nbsp;',
                ($column->getProperty('Value', '') !== '' ? $column->getProperty('Value') : '&nbsp;')
              ))
            ),
            false);
        }
      }
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowid) {
        foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowid), 'TComponent') as $id) {
          $column = Tholos::$app->findComponentByID($id);
          if (!$column) {
            throw new RuntimeException('Invalid reference');
          }
          if ((Tholos::$app->findComponentByID($id)->getComponentType() === 'TGridColumn') && $column->getProperty('Exportable') === 'true' && $column->getProperty('DBField', '') !== '') {
            $result .= Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.details.row',
              array('label' => $column->getProperty('Label'),
                'value' => Eisodos::$utils->ODecode(array(Tholos::$app->findComponentByID($column->getPropertyComponentId('DBField'))->getProperty('DataType'),
                  'bool', Eisodos::$utils->ODecode($column->getProperty('Value') === 'Y' ? '[:GRID.FILTER.BOOL_YES,Igen:]' : '[:GRID.FILTER.BOOL_NO,Nem:]'),
                  'text', $column->getProperty('Value') ? ("<textarea class=\"form-control\" style=\"height: 150px;\">" . $column->getProperty('Value') . "</textarea>") : '&nbsp;',
                  ($column->getProperty('Value', '') !== '' ? $column->getProperty('Value') : '&nbsp;')
                ))
              ),
              false);
          }
        }
      }
      
      Tholos::$app->trace('END', $this);
      
      return Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.details.container', array('data' => $result), false);
    }
    
    /**
     * @inheritdoc
     */
    
    public function render(TComponent $sender, string $content): string {
      
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      $result = '';
      
      Tholos::$app->debug('render start', $this);
      if (!Tholos::$app->checkRole($this)) {
        return '';
      }
      
      // checking export role
      if (Tholos::$app->roleManager !== NULL and !Tholos::$app->roleManager->checkRole($this->getProperty('ExportFunctionCode', ''))) {
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
      
      if (Eisodos::$parameterHandler->eq('TGrid_todo_', 'tsv') or
        Eisodos::$parameterHandler->eq('TGrid_todo_', 'csv')) {
        if ($this->getProperty('ShowExportButton', 'false') === 'true') {
          $this->renderPlainTextExport(Eisodos::$parameterHandler->getParam('TGrid_todo_'));
        }
        
        return '';
      }
      
      if (Eisodos::$parameterHandler->eq('TGrid_todo_', 'rawtsv') or
        Eisodos::$parameterHandler->eq('TGrid_todo_', 'rawcsv') or
        Eisodos::$parameterHandler->eq('TGrid_todo_', 'rawjson')) {
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
      Tholos::$app->debug('DataGenerated: ' . $this->getProperty('DataGenerated', ''), $this);
      
      
      if ($this->getPropertyComponentId('ChartXAxis')) {
        $this->chartComponents[] = Tholos::$app->findComponentByID($this->getPropertyComponentId('ChartXAxis'));
        $this->chartDatasets[] = array();
        foreach (Tholos::$app->findChildIDsByType($this, 'TComponent') as $id) {
          $component = Tholos::$app->findComponentByID($id);
          if ($component->getComponentType() === 'TGridColumn'
            and Tholos::$app->checkRole($component)
            and $component->getProperty('ChartType')) {
            $this->chartComponents[] = $component;
            $this->chartDatasets[] = array();
            $co = $component->getProperty('ChartOptions');
            $co = Eisodos::$utils->replace_all($co, '{', '{' . ' label: "' . $component->getProperty('Label') . '", ' .
              ' type: "' . $component->getProperty('ChartType') . '", ', false, true);
            $this->chartDatasetsOptions[] = $co;
          }
        }
      }
      
      if ($this->getProperty('ViewMode') === 'GRID') {
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
                array('chartDatasetsOptions' => "[' . implode(", ", $this->chartDatasetsOptions) . ']")
                , false) : ''
            )
          ) . "\n";
      }
      
      /* @var TQuery $listSource */
      $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'));
      $listSource->setProperty('DisableQueryFilters', 'true');
      $listSource->setProperty('FilterArray', $listSource->buildFilters($this));
      // request only headers
      $emptyWhere = '';
      if (!($this->getProperty('AJAXMode', 'false') == 'false' or (Eisodos::$parameterHandler->eq('IsAJAXRequest', 'T') and (Tholos::$app->partial_id == $this->_id)))) {
        $emptyWhere = "\n and 0=1";
        $listSource->setProperty('StructureInfoOnly', 'true');
        $listSource->setProperty('StructureRequester', $this->_id);
      }
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
      if ($this->getProperty('ViewMode') === 'GRID') {
        $listSource->setProperty('QueryLimit', $this->getProperty('RowsPerPage', '0'));
        $listSource->setProperty('QueryOffset', (((integer)$this->getProperty('RowsPerPage', '0')) * (((integer)$this->getProperty('ActivePage', '1')) - 1)));
      }
      $listSource->setProperty('CountTotalRows', 'true');
      
      if ($this->getProperty('AJAXMode', 'false') == 'false' or (Eisodos::$parameterHandler->eq("IsAJAXRequest", "T") and (Tholos::$app->partial_id == $this->_id))) {
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
        Tholos::$app->debug('Query caching turned on by grid', $this);
      }
      
      if (!$this->reloadStateNeeded and
        ($this->getProperty('AJAXMode', 'false') == 'false' or
          (Eisodos::$parameterHandler->eq('IsAJAXRequest', 'T') and Tholos::$app->partial_id == $this->_id))) {
        if ($this->getProperty('ListsourceAlwaysReopen','false')=='true') {
          $listSource->setProperty('Opened','false');
        }
        $listSource->run($this);
      }
      $this->setProperty('RowCount', $listSource->getProperty('RowCount', '0'));
      $this->setProperty('TotalRowCount', $listSource->getProperty('TotalRowCount', '0'));
      
      $subComponents = Tholos::$app->findChildIDsByType($this, 'TComponent');
      
      $selection_found = $this->getProperty('Selectable', 'false') === 'false';
      
      // collecting chart base data
      
      if ($this->getProperty('ViewMode', 'GRID') === 'GRID') {
        
        $rownum = 0;
        
        if (1 * $listSource->getProperty('RowCount', '0') > 0) {
          foreach ($listSource->getProperty('Result') as $row) {
            $columns = '';
            $rownum++;
            $listSource->propagateResult($row);
            
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
              foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowid) {
                $columns = '';
                $isEmptyRow = true;
                foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowid), 'TComponent') as $id) {
                  $component = Tholos::$app->findComponentByID($id);
                  if ($this->getPropertyComponentId('DBField') !== false
                    and $columns === ''
                    and $hasAnyStandaloneGridColumn
                    and $rownum === 1) {
                    $component->setProperty('ColumnOffset', 1 * $component->getProperty('ColumnOffset', '0') + 1);
                  }
                  if ($component->getComponentType() === 'TGridColumn') {
                    if ($component->getProperty('Visible', 'false') === 'true'
                      and Tholos::$app->checkRole($component)) {
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
                    and $component->getProperty('Visible', 'false') === 'true'
                    and Tholos::$app->checkRole($component)) {
                    $isEmptyRow = false;
                    $columns .= '<' . $this->getProperty('cellType', '') . ' ' .
                      ' class="TGrid-resp-cell text-nowrap ' . $component->getProperty('Class', '') . ' ' . ($component->getProperty('Align', '') ? ' text-' . $component->getProperty('Align', '') : '') . '" ' .
                      ($component->getProperty('ColumnSpan', '') ? ' colspan="' . $component->getProperty('ColumnSpan', '') . '" ' : '') .
                      ($component->getProperty('Style', '') ? ' style="' . $component->getProperty('Style', '') . '"' : '') .
                      ' >' . Tholos::$app->render($this, $id, true) . '</' . $this->getProperty('cellType', '') . '>';
                  }
                }
                if (!$isEmptyRow || Tholos::$app->findComponentByID($rowid)->getProperty('HideWhenEmpty', 'false') === 'false') {
                  if ($this->getPropertyComponentId('DBField') !== false) {
                    if ($hasAnyStandaloneGridColumn) {
                      $columns = $this->renderPartial($this, 'noselectable') . $columns;
                    } else {
                      $columns = $this->renderPartial($this, 'selectable') . $columns;
                    }
                  }
                  $result .= Tholos::$app->findComponentByID($rowid)->render($this, $columns) . "\n";
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
              
              foreach (Tholos::$app->findChildIDsByType($this, 'TGridRow') as $rowid) {
                foreach (Tholos::$app->findChildIDsByType(Tholos::$app->findComponentByID($rowid), 'TComponent') as $id) {
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
          foreach ($this->transposedRows as $key => $transposedRow) {
            $result .= $this->renderPartial($this, 'rowtransposed', $transposedRow) . "\n";
          }
        }
        
        if ($this->getProperty('RowsPerPage', '0') === '0') {
          $pagecount = 1;
        } else {
          $pagecount = ceil((1 * $this->getProperty('TotalRowCount', '0')) / $this->getProperty('RowsPerPage', '0'));
        }
        $activepage = 1 * ($this->getProperty('ActivePage', '1'));
        $this->setProperty('PageCount', $pagecount);
        
        $this->generateProps();
        
        $pageitems = '';
        
        if ((integer)$this->getProperty('RowsPerPage', '0') > 0 /* and $pagecount>1 */) {
          if (max(min($activepage - 5, $pagecount - 10), 1) > 1) {
            $pageitems .= Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.pagination.item',
              array('pagenum' => max(min($activepage - 5, $pagecount - 10), 1) - 1,
                'pagenumtext' => '..',
                'pageactive' => ''
              ), false);
          }
          for ($i = max(min($activepage - 5, $pagecount - 10), 1); $i <= min(max($activepage - 5, 1) + 10, $pagecount); $i++) {
            $pageitems .= Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.pagination.item',
              array('pagenum' => $i,
                'pagenumtext' => $i,
                'pageactive' => ($i === $activepage ? 'active' : '')
              ), false);
          }
          if (min(max($activepage - 5, 1) + 10, $pagecount) < $pagecount) {
            $pageitems .= Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.pagination.item',
              array('pagenum' => min(max($activepage - 5, 1) + 10, $pagecount) + 1,
                'pagenumtext' => '..',
                'pageactive' => ''
              ), false);
          }
          
          $pagination = Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.pagination.main',
              array('items' => $pageitems,
                'lastpage' => $pagecount,
                'isfirstpage' => ($activepage === 0 || $activepage === 1) ? 'T' : 'F'),
              false) . "\n";
          
        } else {
          $pagination = '';
        }

//      \PC::debug(array($this->getProperty('Selectable','false')=='true',$selection_found,$this->getProperty('DBField','')!='',$this->getProperty('LookupValue','')));
        
        if ($listSource->getProperty('CacheUsed', 'false') === 'true'
          and $this->getProperty('ShowCacheInfo', 'true') === 'true'
          and Eisodos::$utils->safe_array_value($listSource->getProperty('CacheInfo'), 'updated') !== '') {
          $datevalue_obj = DateTime::createFromFormat('YmdHis', Eisodos::$utils->safe_array_value($listSource->getProperty('CacheInfo'), 'updated'));
          $datevalue = $datevalue_obj->format(Eisodos::$parameterHandler->getParam('PHP' . $this->getProperty('DateFormatParameter', 'datetime') . 'Format'));
          $since_start = $datevalue_obj->diff(new DateTime());
          $minutes = $since_start->days * 24 * 60;
          $minutes += $since_start->h * 60;
          $minutes += $since_start->i;
          $cacheinfo = Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.cache.info', ['cacheupdated' => $datevalue . ' (' . $minutes . ') '], false);
        } else {
          $cacheinfo = '';
        }
        
        $result .= $this->renderPartial($this, 'foot', '',
          array('pagination' => $pagination,
            'selectionoutoflist' => (($this->getProperty('Selectable', 'false') === 'true'
              && !$selection_found
              && $this->getProperty('DBField', '') !== ''
              && $this->getProperty('LookupValue', '') !== '') ? $this->getProperty('LabelSelectionOutOfList', '') : ''),
            'cacheinfo' => $cacheinfo
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
      
      Tholos::$app->debug('Render ended', $this);
      
      $this->renderedContent = $result;
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return $this->renderedContent;
      
    }
  }
  