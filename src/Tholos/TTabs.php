<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  
  class TTabs extends TComponent {
    
    /**
     * @inheritdoc
     */
    public function render(?TComponent $sender, string $content): string {
      
      /* generating pane headers */
      
      if (!Tholos::$app->checkRole($this)) {
        return '';
      }
      
      $this->renderedContent = '';
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      $defaultTabPane = Eisodos::$parameterHandler->getParam($this->getProperty('Name') . ':tabPane');
      
      Tholos::$app->debug('tabpane: ' . $defaultTabPane);
      
      $partialHead = '';
      foreach (Tholos::$app->findChildIDsByType($this, 'TTabPane') as $TabPaneId) {
        $tabPane = Tholos::$app->findComponentByID($TabPaneId);
        if ($defaultTabPane == $tabPane->getProperty('Name')) {
          $this->setProperty('DefaultTabPane', NULL, 'COMPONENT', $TabPaneId);
        }
        $partialHead .= Tholos::$app->findComponentByID($TabPaneId)->renderPartial($sender, 'head');
      }
      
      Eisodos::$parameterHandler->setParam('partialHead', $partialHead);
      
      $this->renderedContent = parent::render($sender, $content);
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return $this->renderedContent;
      
    }
    
  }
