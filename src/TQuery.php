<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use Exception;
  use RuntimeException;
  use Throwable;
  
  /**
   * Class TQuery
   * @package Tholos
   */
  class TQuery extends TDataProvider {
    
    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function init(): void {
      Tholos::$app->trace('BEGIN', $this);
      Tholos::$app->trace('(' . $this->_componentType . ') (ID ' . $this->_id . ')', $this);
      
      parent::init();
      
      Tholos::$app->trace('END', $this);
    }
    
    /**
     * @param $sender
     * @return array
     */
    public function buildFilters($sender): array {
      Tholos::$app->trace('BEGIN', $this);
      
      $filter_array = [];
      foreach (array_merge(Tholos::$app->findChildIDsByType($this, 'TQueryFilterGroup'), Tholos::$app->findChildIDsByType($this, 'TQueryFilter')) as $filterid) {
        // RecordSelector filter csak AutoOpen hivaskor ertelmezett
        /* @var TQueryFilter $filter */
        $filter = Tholos::$app->findComponentByID($filterid);
        try {
          $filter->init();
          $filter->initValue($sender);
          $filter_SQL = $filter->getProperty('SQL', '');
          if ($filter_SQL !== '') {
            $filter_array[$filter->getProperty('FilterGroupParameter', ':filter')] = Eisodos::$utils->safe_array_value($filter_array, $filter->getProperty('FilterGroupParameter', ':filter')) . 'AND ' . $filter_SQL . " \n";
          }
        } catch (Throwable $e) {
          $this->setProperty('FilterError', 'true');
          $filter_array = [];
        }
      }
      
      Tholos::$app->trace('END', $this);
      
      return $filter_array;
    }
    
    /**
     * @param TComponent|null $sender
     * @param string $nativeSQL
     * @throws Throwable
     */
    protected function open(?TComponent $sender, $nativeSQL = ''): void {
      
      Tholos::$app->checkRole($this, true);
      
      if ($this->getProperty('Opened') === 'true') {
        return;
      } // ha mar meg volt nyitva, akkor ne fusson le meg egyszer
      
      /* @var TDataProxy $dataProxy */
      if ($this->getPropertyComponentId('DataProxy')) {
        $dataProxy = Tholos::$app->findComponentByID($this->getPropertyComponentId('DataProxy'));
        if (!$dataProxy) {
          throw new RuntimeException('Invalid reference');
        }
        if ($dataProxy->getProperty('Enabled', 'false') !== 'true') {
          $dataProxy = NULL;
        }
      } else {
        $dataProxy = NULL;
      }
      
      if ($nativeSQL !== '' || $this->getProperty('sql', '') !== '') {
        
        Tholos::$app->trace('BEGIN', $this);
        
        // preparing sql
        $back = array();
        
        $this->setProperty('FilterError', 'false');
        
        $sql = $nativeSQL !== '' ? $nativeSQL : $this->getProperty('sql');
        
        if ($this->getProperty('DisableQueryFilters', 'false') === 'false') {
          $this->setProperty('FilterArray', $this->buildFilters($sender));
        }
        Tholos::$app->trace(print_r($this->getProperty('FilterArray', array()), true), $this);
        Tholos::$app->trace("Additional filters: \n" . $this->getProperty('Filter', ''), $this);
        
        $filter_array = $this->getProperty('FilterArray', array());
        if ($filter_array && count($filter_array) > 0) {
          foreach ($filter_array as $filter_key => $filter_values) {
            if ($filter_key === ':filter') {
              $filter_values .= "\n" . $this->getProperty('filter', '');
            }
            $sql = str_replace($filter_key, $filter_values, $sql);
          }
        }
        $sql = str_replace(':filter', $this->getProperty('filter', ''), $sql);
        
        if ($this->getProperty('FilterError', 'false') === 'true') {
          Tholos::$app->error('Missing required filter', $this);
          Tholos::$app->trace('END', $this);
          
          return;
        }
        
        if (is_null($dataProxy)) {
          $sqlcolumns = '';
          if ($this->getProperty('TotalRowCountSQL', '') !== ''
            && strpos($sql, ':columns') > 1) {
            $sqlcolumns = ',' . $this->getProperty('TotalRowCountSQL', '');
          }
          
          $sql = str_replace(array(':columns', ':orderby'), array($sqlcolumns, $this->getProperty('orderby', '1 asc')), $sql);
          
          if ($this->getProperty('Caching', 'Disabled') === 'Disabled'
            || Eisodos::$parameterHandler->neq('TholosCacheAction', 'refresh')
            || $this->getProperty('CacheRefresh', 'false') === 'true') {
            if ($this->getProperty('CountTotalRows', 'false') === 'true' &&
              $this->getProperty('TotalRowCountField', '') === '') {
              Tholos::$app->trace('Counting rows started', $this);
              $this->openDatabase(true);
              $this->setProperty('TotalRowCount',
                getSQLback(Tholos::$c->getDBByIndex((integer)$this->getProperty('DatabaseIndex', '1')),
                  'select count(1) from (' . $sql . ') q'));
              Tholos::$app->trace('Counting rows finished', $this);
            }
          }
          
          // offseting and paging is disabled on cached queries
          
          if ($this->getProperty('Caching', 'Disabled') === 'Disabled') {
            $dbsyntax = Tholos::$c->getDBByIndex((integer)$this->getProperty('DatabaseIndex', '1'))->getDSN('array', false)['dbsyntax'];
            if ((integer)$this->getProperty('QueryOffset', '0') !== 0) {
              if ($dbsyntax === 'oci8') {
                $sql .= "\n" .
                  ' OFFSET ' . $this->getProperty('QueryOffset', '0') . ' ROWS ';
              } elseif ($dbsyntax === 'pgsql') {
                $sql .= "\n" .
                  ' offset ' . $this->getProperty('QueryOffset', '0');
              } else {
                throw new RuntimeException('QueryOffset not implemented!');
              }
            }
            
            if ((integer)$this->getProperty('QueryLimit', '0') !== 0) {
              $dbsyntax = Tholos::$c->getDBByIndex((integer)$this->getProperty('DatabaseIndex', '1'))->getDSN('array', false)['dbsyntax'];
              if ($dbsyntax === 'oci8') {
                $sql .= "\n" .
                  ' FETCH FIRST ' . $this->getProperty('QueryLimit', '0') . ' ROWS ONLY ';
              } elseif ($dbsyntax === 'pgsql') {
                $sql .= "\n" .
                  ' limit ' . $this->getProperty('QueryLimit', '0');
              } else {
                throw new RuntimeException('QueryLimit not implemented!');
              }
            }
          }
          
          $this->setProperty('PreparedSQL', $sql);
          Tholos::$app->debug('Prepared SQL:' . PHP_EOL . $sql, $this);
        } else {
          Tholos::$app->debug('DataProxy is active' . PHP_EOL . $sql, $this);
        }
        
        if ($this->getProperty('DynamicMode', 'false') === 'true') {
          Tholos::$app->debug('Entering Dynamic mode', $this);
          $this->setProperty('SQL', getSQLback(Tholos::$c->getDBByIndex((integer)$this->getProperty('DatabaseIndex', '1')), $sql));
          $this->setProperty('DynamicMode', 'false');
          Tholos::$app->debug('Leaving Dynamic mode', $this);
          Tholos::$app->trace('END', $this);
          $this->open($sender);
        } else {
          
          // AuthProcedure
          
          if ($authproc_id = $this->getPropertyComponentId('AuthProcedure')  // TODO: giga bug
            and
            (
              (!Tholos::$app->findComponentByID($authproc_id)->getPropertyComponentId('DataProxy') // nincs proxyzva
                && Eisodos::$parameterHandler->eq('TholosProxy:ProxyComponentID', '') // nem proxy modban fut
              )
              ||
              (Tholos::$app->findComponentByID($authproc_id)->getPropertyComponentId('DataProxy') // van proxy-ja
                && is_null($dataProxy) // es ez a nem proxy oldal
              )
            )
          ) {
            /* @var TDataProvider $authproc */
            $authproc = Tholos::$app->findComponentByID($authproc_id);
            $authproc->setProperty('Opened', 'false'); // must run every time
            $authproc->run($this);
            if (Tholos::$app->findComponentByID($authproc_id)->getProperty('Success') === 'false') {
              header('X-Tholos-Error-Code: 403');
              header('X-Tholos-Error-Message: Authentication error');
              header('X-Tholos-Error-Message-B64: ' . base64_encode('Authentication error'));
              Tholos::$app->eventHandler($this, 'onAuthError');
              
              return;
            }
          }
          
          if ($this->getProperty('Caching', 'Disabled') !== 'Disabled'
            && Eisodos::$parameterHandler->neq('TholosCacheAction', 'refresh')
            && $this->getProperty('CacheRefresh', 'false') === 'false'
            && $this->getProperty('CacheMode') !== 'WriteOnly') {
            $cacheFilterID = $this->getPropertyComponentId('CachePartitionFilter');
            if ($cacheFilterID !== false) {
              $cachePartitionValue = Tholos::$app->findComponentByID($cacheFilterID)->getProperty('Value');
            } else {
              $cachePartitionValue = '';
            }
            $cacheResult = Tholos::$app->readCache(
              $this,
              $this->getProperty('Caching'),
              $this->getProperty('CacheID'),
              $cachePartitionValue,
              $sql,
              $this->getProperty('CacheSQLConflict', 'DisableCaching'));
          } else {
            $cacheResult = false;
          }
          
          if ($cacheResult === false
            && !is_null($dataProxy)) {
            try {
              // disable query filters, because it is build on the client side
              $this->setProperty('DisableQueryFilters', 'true');
              $jsonArray = json_decode($dataProxy->open($this, array('CountTotalRows', 'FilterArray', 'Filter', 'OrderBy', 'QueryLimit', 'QueryOffset', 'DisableQueryFilters'), array('TotalRowCount', 'RowCount')), true, 512, JSON_THROW_ON_ERROR);
              $this->setProperty('DisableQueryFilters', 'false');
              $cacheResult = json_decode($jsonArray['data'], true, 512, JSON_THROW_ON_ERROR);
              $this->setProperty('CacheRefresh', 'true');
              $a_ = array_change_key_case($jsonArray);
              $n_ = strtolower($this->getProperty('Name'));
              foreach ($this->getPropertyNames() as $key) {
                if (array_key_exists($n_ . '>' . $key, $a_)) {
                  Tholos::$app->trace('Setting property <' . $key . '> to ' . Eisodos::$utils->safe_array_value($a_, $n_ . '>' . $key) . ' by proxy response', $this);
                  if ($this->getPropertyType($key) === 'ARRAY') {
                    $this->setProperty($key, json_decode(Eisodos::$utils->safe_array_value($a_, $n_ . '>' . $key), true, 512, JSON_THROW_ON_ERROR));
                  } else {
                    $this->setProperty($key, Eisodos::$utils->safe_array_value($a_, $n_ . '>' . $key));
                  }
                }
              }
            } catch (Exception $e) {
              Tholos::$app->error($e->getMessage(), $this);
              $cacheResult = [];
              $this->setProperty('DisableQueryFilters', 'false');
            }
          }
          
          if ($cacheResult === false
            and is_null($dataProxy)
            and $sql != ''
            and $this->getProperty('CacheMode') != 'ReadOnly') {
            
            // InitProcedure
            
            if ($initproc_id = $this->getPropertyComponentId("InitProcedure")
              and
              (
                (!Tholos::$app->findComponentByID($initproc_id)->getPropertyComponentId("DataProxy") // nincs proxyzva
                  and Eisodos::$parameterHandler->eq("TholosProxy:ProxyComponentID", "") // nem proxy modban fut
                )
                or
                (Tholos::$app->findComponentByID($initproc_id)->getPropertyComponentId("DataProxy") // van proxy-ja
                  and is_null($dataProxy) // es ez a nem proxy oldal
                )
              )
            ) {
              /* @var TDataProvider $initproc */
              $initproc = Tholos::$app->findComponentByID($initproc_id);
              $initproc->setProperty("Opened", "false");
              $initproc->run($this);
              if (Tholos::$app->findComponentByID($initproc_id)->getProperty('Success') == 'false') {
                Tholos::$app->eventHandler($this, "onInitError");
                
                return;
              }
            }
            
            // check if structure info only and if it is already cached
            if ($this->getProperty('StructureInfoOnly', 'false') == 'true') {
              $cacheResult = Tholos::$app->readCache(
                $this,
                'Private',
                $this->getProperty("CacheID") . '.StructureOnly.'.$this->getProperty('StructureRequester', ''),
                '',
                $sql,
                'ReadCache');
              // clear cache info
              $this->setProperty("CacheInfo", array());
            }
            
            if (!is_array($cacheResult)) {
              Tholos::$app->debug("SQL query opening", $this);
              $this->openDatabase(true);
              try {
                getSQLtoArrayFull(Tholos::$c->getDBByIndex((integer)$this->getProperty("DatabaseIndex", "1")),
                  $sql,
                  $back);
                
                if ($this->getProperty('StructureInfoOnly', 'false') == 'true') {
                  Tholos::$app->debug('Structure written to cache for grid', $this);
                  Tholos::$app->writeCache('Private',
                    $this->getProperty("CacheID") . '.StructureOnly.'.$this->getProperty('StructureRequester', ''),
                    $back,
                    '',
                    24*60,
                    '',
                    $sql
                  );
                }
                
              } catch (Exception $e) {
                Eisodos::$logger->writeErrorLog($e);
                Tholos::$app->eventHandler($this, "onError");
                throw $e;
              }
            } else {
              Tholos::$app->debug('Structure read from cache for grid', $this);
            }
            
          } else {
            if (is_array($cacheResult)) {
              $back = $cacheResult;
            } else {
              $back = [];
              $this->setProperty('CacheRefresh', 'false');
            }
          }
          
          // reseting structure info cache property to its default for parallel usage
          Tholos::$app->debug('Resetting structure info cache', $this);
          $this->setProperty('StructureInfoOnly','false');
          $this->setProperty('StructureRequester','');
          
          Tholos::$app->eventHandler($this, "onSuccess");
          
          Tholos::$app->debug('SQL query has finished', $this);
          
          if ($this->getProperty('Caching', 'Disabled') !== 'Disabled'
            && ($this->getProperty('CacheRefresh', 'false') === 'true'
              || Eisodos::$parameterHandler->eq('TholosCacheAction', 'refresh'))
            && $this->getProperty('CacheMode') !== 'ReadOnly') {
            Tholos::$app->debug('Caching Query', $this);
            if ($cacheFilterID = $this->getPropertyComponentId('CachePartitionFilter')) {
              $cachePartitionValue = Tholos::$app->findComponentByID($cacheFilterID)->getProperty('Value');
            } else {
              $cachePartitionValue = '';
            }
            Tholos::$app->writeCache($this->getProperty('Caching'),
              $this->getProperty('CacheID'),
              $back,
              $this->getProperty('CachePartitionedBy', ''),
              $this->getProperty('CacheValidity', ''),
              $cachePartitionValue,
              $sql
            );
          }
          
          // offseting and pageing handled here in case of cache used
          
          if ($this->getProperty('Caching', 'Disabled') !== 'Disabled') {
            $this->setProperty('TotalRowCount', count($back));
            if ((integer)$this->getProperty('QueryOffset', '0') !== 0) {
              $back = array_slice($back, (integer)$this->getProperty('QueryOffset', '0'));
            }
            if ((integer)$this->getProperty('QueryLimit', '0') !== 0) {
              $back = array_slice($back, 0, (integer)$this->getProperty('QueryLimit', '0'));
            }
          } else if ($this->getProperty('CountTotalRows', 'false') === 'true' &&
            $this->getProperty('TotalRowCountField', '') !== '') {
            if (count($back) > 0) {
              $this->setProperty('TotalRowCount', $back[0][$this->getProperty('TotalRowCountField', '')]);
            } else {
              $this->setProperty('TotalRowCount', '0');
            }
          }
          
          if (Eisodos::$parameterHandler->eq('TholosCacheAction', 'info')) {
            $back = $this->getProperty('CacheInfo', array());
          }
          
          $this->setProperty('Opened', 'true');
          $this->setProperty('Result', $back);
          $this->setProperty('ResultType', 'ARRAY');
          $this->setProperty('RowCount', count($back));
          
          Tholos::$app->trace('END', $this);
        }
        
      }
    }
    
    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function autoOpen(): void {
      
      if ($this->getProperty('AutoOpenAllowed', 'true') === 'true' &&
        count(Tholos::$app->findChildIDsByType($this, 'TDBfield')) > 0) {  // csak akkor nyiljon meg a query, ha vannak benne dbfield-ek (kulonben valoszinuleg lov)
        Tholos::$app->trace('BEGIN', $this);
        $this->run(NULL);
        Tholos::$app->trace('END', $this);
      }
      
    }
    
  }
  