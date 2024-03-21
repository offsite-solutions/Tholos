<?php /** @noinspection DuplicatedCode SpellCheckingInspection PhpUnusedFunctionInspection NotOptimalIfConditionsInspection */
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  
  class TFormControl extends TComponent {
    
    /**
     * @inheritdoc
     */
    public function init(): void {
      Tholos::$app->trace('BEGIN', $this);
      Tholos::$app->trace('(' . $this->_componentType . ') (ID ' . $this->_id . ')', $this);
      
      $this->initialized = true;
      if (($this->getProperty('valueparameter', '') !== '') && ($this->getProperty('dbfield', '') === '') && $this->getProperty('value') === '') {
        $this->setProperty('value', Eisodos::$parameterHandler->getParam($this->getProperty('valueparameter', '')));
      }
      
      Tholos::$app->trace('END', $this);
    }
  }
