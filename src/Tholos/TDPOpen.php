<?php
  
  namespace Tholos;
  class TDPOpen extends TComponent {
    
    /**
     * @inheritdoc
     */
    public function render(?TComponent $sender, string $content): string {
      
      if ($this->getProperty('DataSource') !== false) {
        /* @var TQuery $listSource */
        $listSource = Tholos::$app->findComponentByID($this->getPropertyComponentId('DataSource'));
        if ($this->getProperty('CloseDataSourceBeforeOpen','false')=='true') {
          $listSource->close();
        }
        $listSource->run(null);
      }
      
      return '';
    }
    
    
  }
  
  ?>