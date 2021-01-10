<?php
  
  namespace Tholos;
  
  /**
   * TGridColumn Component class
   *
   * TGridColumn is a column in a TGrid component
   * Descendant of TGridControls.
   *
   * @package Tholos
   * @see TGrid
   */
  class TGridRowActions extends TComponent {
    
    public function init(): void {
      parent::init();
      $this->setProperty("cellHeadType", Tholos::$app->findParentByType($this, "TGrid")->getProperty("GridHTMLType") === "table" ? "th" : "div");
      $this->setProperty("cellType", Tholos::$app->findParentByType($this, "TGrid")->getProperty("GridHTMLType") === "table" ? "td" : "div");
    }
    
  }
