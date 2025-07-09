<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  
  /**
   * TCacheArraySave Component class
   *
   * @package Tholos
   * @see TComponent
   */
  class TCacheArraySave extends TComponent {
    
    /**
     * @inheritdoc
     */
    public function render(?Tcomponent $sender, string $content): string {
      
      if (!$sender) {
        return '';
      }
      
      if ($this->getProperty('enabled')=='false') {
        return '';
      }
      
      $this->renderedContent = '';
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      // loading cache from session parameter
      $cacheName = 'Tholos.CacheArray.' . $this->getProperty('CacheId');
      $currentCache_ = Eisodos::$parameterHandler->getParam($cacheName, '');
      if ($currentCache_ != '') {
        $currentCache = unserialize($currentCache_, false);
      } else {
        $currentCache = [];
      }
      
      Tholos::$app->writeCache('Private', $this->getProperty('CacheId'), $currentCache, '', $this->getProperty('CacheValidity'), '', '');
      
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return '';
    }
  }

