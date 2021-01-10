<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;

  /**
   * TPartial Component class
   *
   * @package Tholos
   * @see TComponent
   */
  class TPartial extends TComponent {
    
    /**
     * @inheritdoc
     */
    public function render(TComponent $sender, string $content): string {
      
      if (!Tholos::$app->checkRole($this)) {
        return '';
      }
      
      $this->renderedContent = '';
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      $this->generateProps();
      $this->generateEvents();
      
      if ($this->getProperty('Cacheable', 'false') === 'true') {
        $cachedResult = Eisodos::$parameterHandler->getParam('Tholos.Cache.' . $this->getProperty('ParameterName'));
        if ($cachedResult !== '') {
          Eisodos::$parameterHandler->setParam($this->getProperty('ParameterName'), $cachedResult);
        } else {
          $cachedResult = parent::render($sender, $content);
          Eisodos::$parameterHandler->setParam('Tholos.Cache.' . $this->getProperty('ParameterName'), $cachedResult, true);
          Eisodos::$parameterHandler->setParam($this->getProperty('ParameterName'), $cachedResult);
        }
      } else {
        Eisodos::$parameterHandler->setParam($this->getProperty('ParameterName'), parent::render($sender, $content));
      }
      
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return '';
    }
  }

