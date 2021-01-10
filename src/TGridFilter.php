<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;

  /**
   * TGridFilter Component class
   *
   * TGridColumn is a column in a TGrid component
   * Descendant of TGridControls.
   *
   * @package Tholos
   * @see TGrid
   */
  class TGridFilter extends TComponent {
    
    /**
     * @inheritdoc
     */
    
    public function render(TComponent $sender, string $content): string {
      if ($sender !== NULL) {
        return parent::render($sender, $content);
      }
      
      return '';
    }
    
    public function generateDefault($gridName_): void {
      
      if ($this->getProperty('DefaultRelation') && $this->getProperty('Value')) {
        $hasFilter = false;
        for ($i = 1; $i < 100; $i++) {
          if (Eisodos::$parameterHandler->neq($gridName_ . '_f_' . $i, '')) {
            $filterParam = explode(':', Eisodos::$parameterHandler->getParam($gridName_ . '_f_' . $i), 3);
            if ($filterParam[0] === $this->getProperty('name')) {
              $hasFilter = true;
              break;
            }
          }
        }
        if (!$hasFilter) {
          // find an empty slot
          $slotId = 0;
          for ($i = 1; $i < 100; $i++) {
            if (Eisodos::$parameterHandler->eq($gridName_ . '_f_' . $i, '')) {
              $slotId = $i;
              break;
            }
          }
          Eisodos::$parameterHandler->setParam($gridName_ . '_f_' . $slotId, $this->getProperty('name') . ':' . $this->getProperty('DefaultRelation', '') . ':' . $this->getProperty('Value', ''));
        }
      }
    }
    
  }
