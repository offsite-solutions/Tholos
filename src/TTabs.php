<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;

  class TTabs extends TComponent {
    
    /**
     * @inheritdoc
     */
    public function render(TComponent $sender, string $content): string {
      
      /* generating pane headers */
      
      if (!Tholos::$app->checkRole($this)) {
        return '';
      }
      
      $this->renderedContent = '';
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      $partialHead = '';
      foreach (Tholos::$app->findChildIDsByType($this, 'TTabPane') as $TabPaneId) {
        $partialHead .= Tholos::$app->findComponentByID($TabPaneId)->renderPartial($sender, 'head');
      }
      
      Eisodos::$parameterHandler->setParam('partialHead', $partialHead);
      
      $this->renderedContent = parent::render($sender, $content);
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return $this->renderedContent;
      
    }
    
  }
