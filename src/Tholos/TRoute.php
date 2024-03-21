<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use Throwable;
  
  /**
   * TAction Component class
   *
   * TAction defines the action to be taken by the current route.
   * Descendant of TComponent.
   *
   * @package Tholos
   * @see TComponent
   */
  class TRoute extends TComponent {
    
    /**
     * @throws Throwable
     */
    public function init(): void {
      parent::init();
      if ($this->_id === Tholos::$app->route_id) {
        Tholos::$app->checkRole($this, false, true);
      }
      
      if ($this->getPropertyComponentId("InitSessionProvider") !== false) {
        /* @var TDataProvider $component */
        $component = Tholos::$app->findComponentByID($this->getPropertyComponentId('InitSessionProvider'));
        $component->init();
        $component->run($this);
        if ($component->getProperty('Success') === 'false') {
          exit;
        }
      }
      
      if ($this->getProperty('PersistentSession', 'true') == 'false') {
        Eisodos::$parameterHandler->setParam('DestroySessionOnFinish', 'T');
        Tholos::$app->debug('This is not a persistent session');
      }
      
    }
    
  }
