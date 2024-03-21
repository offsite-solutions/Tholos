<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  
  /**
   * TPage Component class
   *
   * TPage component defines the layout of the HTML page.
   * Descendant of TComponent.
   *
   * @package Tholos
   * @see TComponent
   */
  class TPage extends TComponent {
    
    /**
     * @inheritdoc
     */
    public function render(?TComponent $sender, string $content): string {
      
      if (!Tholos::$app->checkRole($this)) {
        return '';
      }
      
      $this->renderedContent = '';
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      Tholos::$app->render($sender, $this->_id, true); // TPartial-ok renderelese
      
      $this->generateProps();
      $this->generateEvents();
      
      $this->renderedContent = Eisodos::$templateEngine->getTemplate($this->getproperty('Template'), array('content' => $content), false);
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return $this->renderedContent;
      
    }
  }
