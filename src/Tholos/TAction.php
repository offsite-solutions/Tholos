<?php /** @noinspection DuplicatedCode SpellCheckingInspection PhpUnusedFunctionInspection NotOptimalIfConditionsInspection */
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  
  /**
   * TAction Component class
   *
   * TAction defines the action to be taken by the current route.
   * Descendant of TComponent.
   *
   * @package Tholos
   * @see TComponent
   */
  class TAction extends TComponent {
    
    /**
     * @inheritDoc
     */
    public function init(): void {
      parent::init();
      if ($this->_id === Tholos::$app->action_id) {
        Tholos::$app->checkRole($this, false, true);
      }
    }
    
    /**
     * TAction renders its content into a template according to its Page property
     *
     * @inheritdoc
     */
    public function render(?TComponent $sender, string $content): string {
      
      Tholos::$logger->trace('BEGIN', $this);
      
      $this->renderedContent = '';
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      if (!Tholos::$app->checkRole($this, false, true)) {
        return '';
      }
      
      $this->generateProps();
      $this->generateEvents();
      
      $newContent = Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.main',
        ['content' => $content,
          'sender' => ($sender === NULL ? '' : $sender->getProperty('Name', '')),
          'component_id' => $this->_id,
          'page_headitems' => implode("\n", Tholos::$app->getHeadItems()),
          'page_footitems' => implode("\n", Tholos::$app->getFootItems())],
        false);
      
      if ($this->getPropertyComponentId('Page')) {
        $return = (Tholos::$app->findComponentByID($this->getPropertyComponentId('Page'))->render($this, $newContent));
      } else {
        $return = $newContent;
      }
      
      $this->renderedContent = $return;
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      Tholos::$logger->trace('END', $this);
      
      return $this->renderedContent;
    }
  }
