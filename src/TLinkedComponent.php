<?php
  
  namespace Tholos;
  
  /**
   * TLinkedComponent Component class
   *
   * TLinkedComponent loads and renders external components
   * Descendant of TComponent.
   *
   * @package Tholos
   * @see TComponent
   */
  class TLinkedComponent extends TComponent {
    
    /**
     * @inheritdoc
     */
    public function render(TComponent $sender, string $content): string {
      
      if (!Tholos::$app->checkRole($this)) {
        return '';
      }
      
      Tholos::$app->trace('BEGIN', $this);
      Tholos::$app->trace('(TComponent) (ID ' . $this->_id . ') ' . $this->_componentType . ', SENDER: ' . ($sender === NULL ? 'null' : $sender->getProperty('Name', '')), $this);
      
      $this->renderedContent = '';
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      if ($this->getPropertyComponentId('Component')) {
        $this->renderedContent = Tholos::$app->render($sender, $this->getPropertyComponentId('Component'));
      } else {
        $this->renderedContent = '';
      }
      
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      Tholos::$app->trace('(TComponent) (ID ' . $this->_id . ') ' . $this->_componentType . ', SENDER: ' . ($sender === NULL ? 'null' : $sender->getProperty('Name', '')) . ', LENGTH: ' . strlen($this->renderedContent), $this);
      Tholos::$app->trace('END', $this);
      
      return $this->renderedContent;
    }
  }
