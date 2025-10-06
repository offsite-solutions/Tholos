<?php
  
  namespace Tholos;
  
  use Eisodos\Abstracts\Singleton;
  
  /**
   * Tholos Bootstrap class
   *
   * This class provides bootstrapping functionality for Tholos. It registers and implements autoload function for
   * loading all Tholos components automatically.
   *
   * @package Tholos
   * @see TholosApplication
   */
  class Tholos extends Singleton {
    
    /** @var TholosLogger Overrides Eisodos logger object if necessary */
    public static TholosLogger $logger;
    
    /**
     * @var TholosApplication Reference to TholosApplication for quick component access. TholosApplication's constructor
     * populates it.
     */
    public static TholosApplication $app;
    
    /**
     * Tholos class prefix used by the autoloader for detecting Tholos-related class load requests
     */
    public const string THOLOS_CLASS_PREFIX = "Tholos\\";
    
    public function init(array $options_): Tholos {
      self::$logger = TholosLogger::getInstance();
      self::$app = TholosApplication::getInstance();
      
      self::$logger->init([]);
      self::$app->init([]);
      
      return $this;
    }
  }
