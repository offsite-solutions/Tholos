<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  
  /**
   * TCacheArrayRenderer Component class
   *
   * @package Tholos
   * @see TComponent
   */
  class TCacheArrayRenderer extends TComponent {
    
    /**
     * @inheritdoc
     */
    public function render(?TComponent $sender, string $content): string {
      
      if (!$sender) {
        return '';
      }
      
      if ($this->getProperty('enabled')=='false') {
        return '';
      }
      
      Tholos::$app->eventHandler($this, "onBeforeRender");
 
      // loading cache from session parameter
      $cacheName = 'Tholos.CacheArray.' . $this->getProperty('CacheId');
      /** @var array $currentCache */
      $currentCache_ = Eisodos::$parameterHandler->getParam($cacheName, '');
      if ($currentCache_ != '') {
        $currentCache = unserialize($currentCache_, false);
      } else {
        $currentCache = Tholos::$app->readCache($this, 'Private', $this->getProperty('CacheId'));
      }
      Tholos::$app->debug('Rendering from array cache - ' . count($currentCache) . ' items', $this);
      
      if (!$currentCache || (is_array($currentCache) && count($currentCache) == 0)) {
        return '';
      }
      
      // Array items resynced in the same render phase
      $differentRenderPhase = (Eisodos::$parameterHandler->getParam($cacheName . '.renderID', '') != Eisodos::$parameterHandler->getParam('Tholos_renderID'));
      
      // purging items which was not touched in the render phase array items rendered
      $cCount=count($currentCache);
      $purgeAt = $this->getProperty('PurgeAt');
      if (
        ($purgeAt == 'EveryRenderPhase'
          || ($purgeAt == 'SameRenderPhase' && !$differentRenderPhase)
          || ($purgeAt == 'DifferentRenderPhase' && $differentRenderPhase))
        && $this->getProperty('PurgeNotRenderedItems', 'false') == 'true'
      ) {
        foreach ($currentCache as $key => $cache) {
          if ($cache['syncMode'] == 'PURGE') {
            unset($currentCache[$key]);
          }
        }
      }
      if ($cCount!=count($currentCache)) {
        Tholos::$app->debug('Purged array cache, left ' . count($currentCache) . ' items', $this);
      }
      
      // sorting array if needed
      if ($this->getProperty('Sorting', '') !== 'NONE') {
        $asc = ($this->getProperty('Sorting', '') === 'ASC') ? 1 : -1;
        usort($currentCache, static function ($a, $b) use ($asc) {
          if ($a['orderValue'] == $b['orderValue']) {
            return 0;
          }
          
          return $asc * (($a['orderValue'] < $b['orderValue']) ? -1 : 1);
        });
      }
      
      $this->renderedContent = '';
      
      // rendering
      $cacheSelectionMode = $this->getProperty('CacheSelectionMode');
      foreach ($currentCache as $cache) {
        if (($cacheSelectionMode == 'All' && $cache['syncMode'] != 'PURGE')
          || ($cacheSelectionMode == 'NewItems' && $cache['syncMode'] == 'NEW')
          || ($cacheSelectionMode == 'NewOrModifiedItems' && ($cache['syncMode'] == 'NEW' || $cache['syncMode'] == 'MODIFIED'))
          || ($cacheSelectionMode == 'ModifiedItems' && $cache['syncMode'] == 'MODIFIED')
          || ($cacheSelectionMode == 'SyncedItems' && $cache['syncMode'] == 'SYNC')
        ) {
          $this->renderedContent .= $cache['result'];
        }
      }
      
      // write out cache
      // Tholos::$app->trace('Writing out cache');
      Eisodos::$parameterHandler->setParam($cacheName, serialize($currentCache));
      
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return $this->renderedContent;
    }
  }

