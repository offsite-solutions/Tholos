<?php
  
  namespace Tholos;
  
  /**
   * TIterator Component class
   *
   * TIterator component iterates through various datasources.
   * Descendant of TComponent.
   *
   * @package Tholos
   * @see TComponent
   */
  class TIterator extends TComponent {
    
    /** @inheritDoc */
    public function init(): void {
      parent::init();
      $this->selfRenderer = true;
    }
    
    /**
     * @inheritdoc
     */
    public function render(?TComponent $sender, string $content): string {
      
      Tholos::$app->trace('Render start', $this);
      
      if (!Tholos::$app->checkRole($this)) {
        return '';
      }
      
      $lastCache = Tholos::$app->EnableComponentPropertyCache;
      Tholos::$app->EnableComponentPropertyCache = false;
      
      $this->renderedContent = '';
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      $result = '';
      
      if ($this->getPropertyComponentId('JSONSource') !== false) {
        assert(true);
      } elseif ($this->getPropertyComponentId('ListSource') !== false) {
        
        /* @var TQuery $listSource */
        $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'));
        $listSource->close();
        $listSource->setProperty('CountTotalRows', 'true');
        $listSource->run($this);
        $this->setProperty('RowCount', $listSource->getProperty('RowCount', '0'));
        $this->setProperty('TotalRowCount', $listSource->getProperty('TotalRowCount', '0'));
        
        $subComponents = Tholos::$app->findChildIDsByType($this, 'TComponent');
        
        if (1 * $listSource->getProperty('RowCount', '0') > 0) {
          foreach ($listSource->getProperty('Result') as $row) {
            $listSource->propagateResult($row);
            foreach ($subComponents as $id) {
              $result .= Tholos::$app->render($this, $id);
            }
          }
        }
      }
      
      $this->renderedContent = $result;
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      Tholos::$app->trace('Render ended', $this);
      
      Tholos::$app->EnableComponentPropertyCache = $lastCache;
      
      return $this->renderedContent;
    }
  }
