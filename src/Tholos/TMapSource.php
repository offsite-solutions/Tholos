<?php

namespace Tholos;

  /**
   * TMap Component class
   *
   * TMap is a Google Maps-powered map component
   * Descendant of TComponent.
   *
   * @package Tholos
   *
   */

class TMapSource extends TComponent {

  /**
   * @inheritdoc
   */
  public function init(): void {
      Tholos::$logger->trace('BEGIN', $this);
      Tholos::$logger->trace('(' . $this->_componentType . ') (ID ' . $this->_id . ')', $this);
      parent::init();
      Tholos::$logger->debug('Turn off AutoOpen on list source of TMapSorce', $this);
      Tholos::$app->findComponentByID($this->getPropertyComponentId('ListSource'))->setProperty('AutoOpenAllowed', 'false');
      Tholos::$logger->trace('END', $this);
  }
}
