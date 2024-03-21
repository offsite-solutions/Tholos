<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  
  /**
   * TMap Component class
   *
   * TMap is a Google Maps-powered map component
   * Descendant of TComponent.
   *
   * @package Tholos
   *
   */
  class TMap extends TComponent {
    
    /**
     * @inheritdoc
     */
    public function render(?TComponent $sender, string $content): string {
      
      if (!Tholos::$app->checkRole($this)) {
        return '';
      }
      
      $this->renderedContent = '';
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      $partialHead = '';
      $partialMenu = '';
      
      foreach (Tholos::$app->findChildIDsByType($this, 'TMapSource') as $MapSourceId) {
        $partialHead .= Tholos::$app->findComponentByID($MapSourceId)->renderPartial($sender, 'head', $this->getFullRenderName());
        $partialMenu .= Tholos::$app->findComponentByID($MapSourceId)->renderPartial($sender, 'menu', $this->getFullRenderName());
      }
      
      Eisodos::$parameterHandler->setParam('partialHead', $partialHead);
      Eisodos::$parameterHandler->setParam('partialMenu', $partialMenu);
      
      $this->renderedContent = parent::render($sender, $content);
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return $this->renderedContent;
    }
  }
