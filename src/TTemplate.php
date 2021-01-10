<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;

  /**
   * TTemplate Component class
   *
   * TTemplate component provides standard template call and processing. Template name is provided in the Template property.
   * Descendant of TComponent.
   *
   * @package Tholos
   * @see TComponent
   */
  class TTemplate extends TComponent {
    
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
      
      $this->renderedContent = Eisodos::$templateEngine->getTemplate($this->getproperty('Template'), array('content' => $content), false);
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return $this->renderedContent;
    }
  }
