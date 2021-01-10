<?php
  
  namespace Tholos;
  
  use Throwable;

  /**
   * TQueryFilterGroup Component class
   *
   * TQueryFilterGroup groups TQueryFilters or TQueryFilterGroups together
   * Descendant of TComponent.
   *
   * @package Tholos
   * @see TComponent
   */
  class TQueryFilterGroup extends TComponent {
    
    /**
     * @param TComponent $sender
     * @throws Throwable
     */
    public function initValue(TComponent $sender): void {
      Tholos::$app->trace('BEGIN', $this);
      Tholos::$app->trace('(' . $this->_componentType . ') (ID ' . $this->_id . ')', $this);
      
      $this->setProperty('SQL', '');
      
      try {
        
        $filter_groups = Tholos::$app->findChildIDsByType($this, 'TQueryFilterGroup');
        $filters = Tholos::$app->findChildIDsByType($this, 'TQueryFilter');
        
        foreach (array_merge($filter_groups, $filters) as $componentID) { // vagy TQueryFilterGroup vagy TQuery
          $component = Tholos::$app->findComponentByID($componentID);
          /* @var TQueryFilterGroup|TQueryFilter $component */
          $component->init();
          $component->initValue($sender);
          $filter_SQL = $component->getProperty('SQL');
          if ($this->getProperty('SQL') && $filter_SQL !== '') {
            $this->setProperty('SQL', $this->getProperty('SQL') . "\n" . $this->getProperty('InternalOperator') . ' ' . $filter_SQL . ' ');
          } else if ($filter_SQL !== '') {
            $this->setProperty('SQL', ' ' . $filter_SQL . ' ');
          }
        }
        
        if ($this->getProperty('SQL')) {
          $this->setProperty('SQL', "\n(" . $this->getProperty('SQL') . ') ');
        } else if ($this->getProperty('EmptySQL', '') !== '') {
          $this->setProperty('SQL', "\n(" . $this->getProperty('EmptySQL', '') . ') ');
        }
      } catch (Throwable $e) {
        Tholos::$app->trace('END', $this);
        throw $e;
      }
      
      Tholos::$app->trace('END', $this);
    }
    
    /**
     * @inheritdoc
     */
    public function render(TComponent $sender, string $content): string {
      return '';
    }
  }
