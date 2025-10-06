<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  
  /**
   * TCacheArrayItem Component class
   *
   * @package Tholos
   * @see TComponent
   */
  class TCacheArrayItem extends TComponent {
    
    /** @inheritDoc */
    public function init(): void {
      parent::init();
      $this->selfRenderer = true;
    }
    
    /**
     * @inheritdoc
     */
    public function render(?TComponent $sender, string $content): string {
      
      if (!$sender) {
        return '';
      }
      
      $this->renderedContent = '';
      Tholos::$app->eventHandler($this, 'onBeforeRender');
      
      // loading cache from session parameter
      $cacheName = 'Tholos.CacheArray.' . $this->getProperty('CacheId');
      if (Eisodos::$parameterHandler->eq('cache_' . $this->getProperty('CacheId'), 'invalidate')) {
        Tholos::$logger->debug('Cache forced to invalidate');
        $currentCache = [];
        Eisodos::$parameterHandler->setParam('cache_' . $this->getProperty('CacheId'), '');
      } else {
        $currentCache_ = Eisodos::$parameterHandler->getParam($cacheName, '');
        if ($currentCache_ != '') {
          /** @var array $currentCache */
          $currentCache = unserialize($currentCache_, false);
        } else {
          $currentCache = Tholos::$app->readCache($this, 'Private', $this->getProperty('CacheId'));
        }
      }
      
      // if it's a new render phase, set sync mode to purge
      if (Eisodos::$parameterHandler->eq($cacheName . '.renderID', '')) {
        foreach ($currentCache as &$value) {
          $value['syncMode'] = 'PURGE';
        }
        unset($value);
        Eisodos::$parameterHandler->setParam($cacheName . '.renderID', Eisodos::$parameterHandler->getParam('Tholos_renderID'));
      }
      
      // checking sync field is exists and getting its value
      if ($syncFieldId = $this->getPropertyComponentId('SyncField')) {
        $syncValue = Tholos::$app->findComponentByID($syncFieldId)->getProperty('Value');
      } else {
        $syncValue = '';
      }
      
      // getting orderfield value
      if ($orderFieldId = $this->getPropertyComponentId('OrderField')) {
        $orderValue = Tholos::$app->findComponentByID($orderFieldId)->getProperty('Value');
      } else {
        $orderValue = '';
      }
      
      // checking is index item already exists in cache
      $cacheIndex = Tholos::$app->findComponentByID($this->getPropertyComponentId('IndexField'))->getProperty('Value');
      if (array_key_exists($cacheIndex, $currentCache)) {
        if ($syncValue != $currentCache[$cacheIndex]['syncValue']) {
          $syncMode = 'MODIFIED';
        } else {
          $syncMode = 'SYNC';
        }
      } else {
        $syncMode = 'NEW';
      }
      if ($syncMode != 'SYNC') {
        $subComponents = Tholos::$app->findChildIDsByType($this, 'TComponent');
        $result = '';
        foreach ($subComponents as $id) {
          $result .= Tholos::$app->render($this, $id);
        }
        $currentCache[$cacheIndex] = [
          'result' => $result,
          'syncMode' => $syncMode,
          'syncValue' => $syncValue,
          'orderValue' => $orderValue];
        Tholos::$logger->debug('Rendering item - ' . $cacheIndex, $this);
      } else {
        $currentCache[$cacheIndex] = [
          'result' => $currentCache[$cacheIndex]['result'],
          'syncMode' => $syncMode,
          'syncValue' => $syncValue,
          'orderValue' => $orderValue];
      }
      
      // write out cache
      Tholos::$logger->trace('Writing out to memory cache (' . $cacheIndex . ' - ' . $syncValue . ' - ' . $syncMode . ') ' . count($currentCache) . ' items', $this);
      Eisodos::$parameterHandler->setParam($cacheName, serialize($currentCache));
      
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return '';
    }
  }

