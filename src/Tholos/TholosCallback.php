<?php
  
  /**
   * Callback functions used in Tholos templates
   */
   
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use RuntimeException;
  
  class TholosCallback {
    public static function _eq($params = array(), $parameterPrefix = ''): string {
      if (Eisodos::$parameterHandler->eq($params['param'], $params['value'])) {
        return Eisodos::$templateEngine->getTemplate($params['true'], array(), false);
      }
      
      return Eisodos::$templateEngine->getTemplate($params['false'], array(), false);
    }
    
    public static function _eqs($params = array(), $parameterPrefix = ''): string {
      return Eisodos::$parameterHandler->eq($params['param'], $params['value'], Eisodos::$utils->safe_array_value($params, 'defaultvalue')) ? $params['true'] : $params['false'];
    }
    
    public static function _neq($params = array(), $parameterPrefix = ''): string {
      if (Eisodos::$parameterHandler->neq($params['param'], $params['value'])) {
        return Eisodos::$templateEngine->getTemplate($params['true'], array(), false);
      }
      
      return Eisodos::$templateEngine->getTemplate($params['false'], array(), false);
    }
    
    public static function _neqs($params = array(), $parameterPrefix = ''): string {
      return Eisodos::$parameterHandler->neq($params['param'], $params['value']) ? $params['true'] : $params['false'];
    }
    
    public static function _case($params = array(), $parameterPrefix = ''): string {
      return Eisodos::$templateEngine->getTemplate(
        Eisodos::$utils->safe_array_value(
          $params,
          Eisodos::$parameterHandler->getParam($params['param']),
          Eisodos::$utils->safe_array_value($params, 'else')),
        array(),
        false);
    }
    
    public static function _cases($params = array(), $parameterPrefix = ''): string {
      return Eisodos::$utils->safe_array_value($params, Eisodos::$parameterHandler->getParam($params['param']), Eisodos::$utils->safe_array_value($params, 'else'));
    }
    
    public static function _safehtml($params = array(), $parameterPrefix = ''): string {
      if (function_exists('tholos_safeHTML')) {
        $s = tholos_safeHTML(Eisodos::$parameterHandler->getParam($params['param']));
        if (strpos($s, "\n")) {
          $s = '<pre>' . $s . '</pre>';
        }
        
        return $s;
      }
      
      return '';
    }
    
    public static function _trim($params = array(), $parameterPrefix = ''): string {
      return trim(Eisodos::$templateEngine->replaceParamInString($params['value']));
    }
    
    public static function _param2($params = array(), $parameterPrefix = ''): string {
      if (!$params['param']) {
        return '';
      }
      
      return Eisodos::$templateEngine->replaceParamInString(Eisodos::$parameterHandler->getParam(Eisodos::$parameterHandler->getParam($params['param'])));
    }
    
    public static function _listToOptions($params = array(), $parameterPrefix = ''): string {
      $result = '';
      foreach (explode($params['separator'], Eisodos::$parameterHandler->getParam($params['options'])) as $item) {
        $result .= '<option value="' . $item . '" ' . (Eisodos::$parameterHandler->eq($params['selected'], $item) ? 'selected' : '') . '>' . $item . '</option>';
      }
      
      return $result;
    }
    
    /**
     * @param string $parameterPrefix
     * @param array $params
     * @return string
     * @throws Throwable
     */
    public static function _generateListValues($params = array(), $parameterPrefix = ''): string {
      
      if (!Eisodos::$utils->safe_array_value($params, 'component_id')) {
        return '';
      }
      $component = Tholos::$app->findComponentByID(Eisodos::$templateEngine->replaceParamInString($params['component_id']));
      if (!$component) {
        return '';
      }
      if ($component->getProperty('AjaxMode') === 'true') {
        return '';
      }
      $lsId = $component->getPropertyComponentId('ListSource');
      if (!$lsId) {
        return '';
      }
      $ls = Tholos::$app->findComponentByID($lsId);
      if (!$ls) {
        return '';
      }
      
      $backup_params = array();
      foreach (explode('&', $component->getProperty('ListFilter')) as $str) { // ha van listfilter, akkor azt berakni a globalis tombbe az
        $p = explode('=', $str, 2);                                          // eredeti erteket pedig elmenteni
        if (count($p) === 0 || $p[0] === '') {
          continue;
        }
        $key = $p[0];
        $backup_params[$key] = Eisodos::$parameterHandler->getParam($key);
        Eisodos::$parameterHandler->setParam($key, (count($p) > 1 ? urldecode($p[1]) : ''));
      }
      
      // Processing data parameters if any defined under TLOV
      foreach (Tholos::$app->findChildIDsByType($component, 'TDataParameter') as $dpId) {
        $dp = Tholos::$app->findComponentByID($dpId);
        if (!$dp) {
          throw new RuntimeException('Invalid reference');
        }
        $key = $dp->getProperty('ParameterName');
        $backup_params[$key] = Eisodos::$parameterHandler->getParam($key);
        Eisodos::$parameterHandler->setParam($key, $dp->getProperty('Value'));
      }
      
      /* @var TDataProvider $ls */
      $ls->close();
      $ls->run(NULL);//$component);
      // restore saved parameters
      foreach ($backup_params as $key => $value) {
        Eisodos::$parameterHandler->setParam($key, $value);
      }
      
      $fieldId = strtolower($component->getProperty('fieldid'));
      $fieldText = strtolower($component->getProperty('fieldtext'));
      $compType = $component->getComponentType();
      
      if (!in_array($compType, array('TLOV', 'TRadio'))) {
        return '';
      }
      
      $value = array();
      $curVal = $component->getProperty('value');
      if ($curVal) {
        if ($component->getProperty('multiselect')) {
          $value = explode(',', $component->getProperty('value'));
        } else {
          $value[0] = $component->getProperty('value');
        }
      }
      $result = '';
      
      Tholos::$logger->debug(print_r($ls->getProperty('result'),true));
      
      if (is_array($ls->getProperty('result')) && count($ls->getProperty('result')) > 0) {
        foreach ($ls->getProperty('result') as $_val) {
          $result .= Eisodos::$templateEngine->getTemplate('tholos/' . $compType . '.item',
            array('prop_itemactive' => (in_array($_val[$fieldId], $value, false) ? 'true' : 'false'),
              'prop_itemid' => htmlspecialchars($_val[$fieldId]),
              'prop_itemtext' => htmlspecialchars($_val[$fieldText])),
            false);
        }
      }
      
      $ls->close();
      
      return $result;
    }
  }