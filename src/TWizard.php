<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;

  class TWizard extends TComponent {
    
    /**
     * @inheritdoc
     */
    public function render(TComponent $sender, string $content): string {
      
      /* generating wizard steps */
      
      if (!Tholos::$app->checkRole($this)) {
        return '';
      }
      
      $this->renderedContent = '';
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      $partialHead = '';
      foreach (Tholos::$app->findChildIDsByType($this, 'TWizardStep') as $WizardStepId) {
        $partialHead .= Tholos::$app->findComponentByID($WizardStepId)->renderPartial($sender, 'head');
      }
      
      Eisodos::$parameterHandler->setParam('partialHead', $partialHead);
      
      $this->renderedContent = parent::render($sender, $content);
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return $this->renderedContent;
      
    }
    
  }
