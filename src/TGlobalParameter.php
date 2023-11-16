<?php /** @noinspection DuplicatedCode SpellCheckingInspection PhpUnusedFunctionInspection NotOptimalIfConditionsInspection */
  
  namespace Tholos;
  
  use Eisodos\Eisodos;

  /**
   * TGlobalParameter Component class
   *
   * TGlobalParameter loads and renders external components
   * Descendant of TComponent.
   *
   * @package Tholos
   * @see TComponent
   */
  class TGlobalParameter extends TComponent {
    
    /* public function init() {
      
      parent::init();
      Eisodos::$parameterHandler->setParam($this->getProperty('ParameterName'),$this->getProperty('Value'));
      // must add this parameter the $_POST array for correct TDataProxy functionality
      $_POST[$this->getProperty('ParameterName')]=$this->getProperty('Value');
    } */
    
    /**
     * @inheritdoc
     */
    public function render(?TComponent $sender, string $content): string {
      
      Eisodos::$parameterHandler->setParam($this->getProperty('ParameterName'), $this->getProperty('Value'));
      // must add this parameter the $_POST array for correct TDataProxy functionality
      $_POST[$this->getProperty('ParameterName')] = $this->getProperty('Value');
      
      return '';
    }
  }
