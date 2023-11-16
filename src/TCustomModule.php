<?php /** @noinspection DuplicatedCode SpellCheckingInspection PhpUnusedFunctionInspection NotOptimalIfConditionsInspection */
  
  namespace Tholos;
  
  class TCustomModule {
    
    protected static $instance;
    
    public static function getInstance() {
      if (NULL === static::$instance) {
        static::$instance = new static();
      }
      
      return static::$instance;
    }
    
  }
