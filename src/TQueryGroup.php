<?php
  
  namespace Tholos;
  
  use Throwable;

  class TQueryGroup extends TDataProvider {
    
    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function init(): void {
      Tholos::$app->trace('BEGIN', $this);
      $this->initialized = true;
      // set runtime properties
      
      if (Tholos::$app->action_id === $this->_id) { // a dataprovider direktben meg lett hivva
        
        // initializing all child component -- a run miatt ezek kesobb inicializalodnak
        foreach (Tholos::$app->findChildIDsByType($this, 'TLinkedComponent') as $componentid) {
          $queryGroupSource = Tholos::$app->findComponentByID($componentid);
          $queryGroupSource->init();
          $linkedComponent = Tholos::$app->findComponentByID($queryGroupSource->getPropertyComponentId('Component'));
          if ($linkedComponent and is_a($linkedComponent, Tholos::THOLOS_CLASS_PREFIX . 'TQuery')) {
            $linkedComponent->setProperty('AutoOpenAllowed', 'false');
            Tholos::$app->trace('Autoopening disabled', $linkedComponent);
          } else {
            Tholos::$app->error('Linked component is not a TQuery component!', $this);
          }
        }
      }
      
      Tholos::$app->trace('END', $this);
    }
    
    /**
     * @inheritDoc
     * @throws Throwable
     */
    protected function open(?TComponent $sender, $nativeSQL = ''): void {
      
      Tholos::$app->trace('BEGIN', $this);
      
      $result = array();
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TLinkedComponent') as $componentid) {
        $queryGroupSource = Tholos::$app->findComponentByID($componentid);
        $queryGroupSource->init();
        $linkedComponent = Tholos::$app->findComponentByID($queryGroupSource->getPropertyComponentId('Component'));
        if ($linkedComponent and is_a($linkedComponent, Tholos::THOLOS_CLASS_PREFIX . 'TQuery')) {
          /* @var TQuery $linkedComponent */
          $linkedComponent->run($this);
          $result[$linkedComponent->getProperty('Name')] = $linkedComponent->getProperty('Result');
        }
      }
      
      $this->setProperty('Opened', 'true');
      $this->setProperty('Result', $result);
      $this->setProperty('ResultType', 'ARRAY');
      
      Tholos::$app->trace('END', $this);
      
    }
    
  }
