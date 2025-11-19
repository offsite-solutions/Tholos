<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  
  /**
   * TGridColumn Component class
   *
   * TGridColumn is a column in a TGrid component
   * Descendant of TGridControls.
   *
   * @package Tholos
   * @see TGrid
   */
  class TGridColumn extends TComponent {
    
    private object|null $parentGrid = NULL;
    
    /**
     * @inheritdoc
     */
    public function render(?TComponent $sender, string $content): string {
      if (!$this->parentGrid) {
        $this->parentGrid = Tholos::$app->findParentByType($this, 'TGrid');
      }
      
      $this->setProperty('cellHeadType', $this->parentGrid->getProperty('GridHTMLType') === 'table' ? 'th' : 'div');
      $this->setProperty('cellType', $this->parentGrid->getProperty('GridHTMLType') === 'table' ? 'td' : 'div');
      if ($this->getPropertyComponentId('DBField') && $this->getProperty('MarkChanges', 'false') === 'true') {
        $this->setProperty('ValueChanged', Tholos::$app->findComponentByID($this->getPropertyComponentId('DBField'))->getProperty('ValueChanged'));
      }
      if ($sender === NULL) {
        return '';
      }
      
      return parent::render($sender, $content);
    }
    
    /**
     * @inheritdoc
     */
    public function renderPartial(?TComponent $sender, string $partialID, string $content = '', array $parameters = array()): string {
      
      if (!Tholos::$app->checkRole($this)) {
        return '';
      }
      
      if (!$this->parentGrid) {
        $this->parentGrid = Tholos::$app->findParentByType($this, 'TGrid');
      }
      
      $this->setProperty('cellHeadType', $this->parentGrid->getProperty('GridHTMLType') === 'table' ? 'th' : 'div');
      $this->setProperty('cellType', $this->parentGrid->getProperty('GridHTMLType') === 'table' ? 'td' : 'div');
      $this->setProperty('parent_grid_id', $this->parentGrid->getProperty('ID'));
      
      if ($partialID === 'head') {
        
        if (Tholos::$app->findParentByType($this, 'TGrid')->getProperty('SortedByAlways', '') !== '') {
          $this->setProperty('Sortable', 'false');
        }
        
        if ($this->getPropertyComponentId('GridFilter')) {
          $columnButton = Tholos::$app->findComponentByID($this->getPropertyComponentId('GridFilter'))->renderPartial($this, 'columnbutton');
        } else {
          $columnButton = '';
        }
        
        $this->generateProps();
        $this->generateEvents();
        
        return Eisodos::$templateEngine->getTemplate('tholos/' . $this->_componentType . '.partial.head',
          array('sortingmark' => ($this->getProperty('Sortable', 'false') === 'false') ? '' : (
          $sender->getPropertyComponentId('SortedBy') === $this->_id ? (
          $sender->getProperty('SortingDirection') === 'ASC' ? 'sorting_asc' : 'sorting_desc'
          ) : 'sorting'
          ),
            'sortingdirection' => ($sender->getPropertyComponentId('SortedBy') == $this->_id ? ($sender->getProperty('SortingDirection') == 'ASC' ? 'DESC' : 'ASC') : $this->getProperty('SortingDirection')),
            'columnbutton' => $columnButton
          ),
          false);
      }
      
      return parent::renderPartial($sender, $partialID, $content, $parameters);
    }
  }
