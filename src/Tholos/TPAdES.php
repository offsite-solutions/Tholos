<?php
  
  namespace Tholos;
  
  use Exception;
  
  /**
   * TPAdES Component class
   *
   * TPAdES component provides an interface to a custom PAdES function via PHP side events.
   * Descendant of TComponent.
   *
   * @package Tholos
   * @see TComponent
   */
  class TPAdES extends TComponent {
    
    public ?object $lastError;
    
    public string $InputPDF = '';
    public string $OutputPDF = '';
    
    /**
     * @inheritdoc
     */
    public function render(?TComponent $sender, string $content): string {
      return '';
    }
    
    /**
     * Sign function invokes onSign event
     *
     * @param ?TComponent $sender Sender Object
     * @param bool $returnIfDisabled Return if component is disabled
     * @return bool Returns false if any error occurs
     * @throws Exception
     */
    public function signPDF(?TComponent $sender, bool $returnIfDisabled = true): bool {
      
      Tholos::$app->trace('BEGIN', $this);
      
      if ($this->getProperty('Enabled', 'true') !== 'true') {
        Tholos::$app->debug('signPDF is disabled, output is unsigned!', $this);
        $this->OutputPDF = $this->InputPDF;
        
        return $returnIfDisabled;
      }
      
      $this->lastError = NULL;
      try {
        Tholos::$app->eventHandler($this, 'onSignPDF');
        Tholos::$app->eventHandler($this, 'onSuccess');
        Tholos::$app->trace('END', $this);
        
        return true;
      } catch (Exception $e) {
        Tholos::$app->error($e->getMessage(), $this);
        if (!Tholos::$app->eventHandler($this, 'onError')) {
          throw $e;
        }
        Tholos::$app->trace('END', $this);
        
        return false;
      }
    }
    
    /**
     * Validate function invokes onValidate event
     *
     * @param ?TComponent $sender Sender Object
     * @param bool $returnIfDisabled Return if component is disabled
     * @return bool Returns false if any error occurs
     */
    public function validatePDF(?TComponent $sender, bool $returnIfDisabled = true): bool {
      
      if ($this->getProperty('Enabled', 'true') !== 'true') {
        return $returnIfDisabled;
      }
      
      $this->lastError = NULL;
      try {
        Tholos::$app->eventHandler($this, 'onValidatePDF');
        Tholos::$app->eventHandler($this, 'onSuccess');
        
        return true;
      } catch (Exception $e) {
        $this->lastError = $e;
        Tholos::$app->eventHandler($this, 'onError');
        
        return false;
      }
    }
    
  }
  