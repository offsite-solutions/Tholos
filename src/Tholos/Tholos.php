<?php
  
  namespace Tholos;
  
  /**
   * Tholos Bootstrap class
   *
   * This class provides bootstrapping functionality for Tholos. It registers and implements autoload function for
   * loading all Tholos components automatically.
   *
   * @package Tholos
   * @see TholosApplication
   */
  class Tholos {
    
    /**
     * @var TholosApplication Reference to TholosApplication for quick component access. TholosApplication's constructor
     * populates it.
     */
    public static TholosApplication $app;
    
    /**
     * Tholos class prefix used by the autoloader for detecting Tholos-related class load requests
     */
    public const THOLOS_CLASS_PREFIX = "Tholos\\";
    
  }
