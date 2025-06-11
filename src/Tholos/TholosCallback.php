<?php
  
  /**
   * Callback functions used in Tholos templates
   */
  
  use Eisodos\Eisodos;
  use Tholos\TDataProvider;
  use Tholos\Tholos;
  
  function _eq($parameterPrefix = '', $params = array()): string {
    if (Eisodos::$parameterHandler->eq($params['param'], $params['value'])) {
      return Eisodos::$templateEngine->getTemplate($params['true'], array(), false);
    }
    
    return Eisodos::$templateEngine->getTemplate($params['false'], array(), false);
  }
  
  function _eqs($parameterPrefix = '', $params = array()): string {
    return Eisodos::$parameterHandler->eq($params['param'], $params['value'], Eisodos::$utils->safe_array_value($params, 'defaultvalue')) ? $params['true'] : $params['false'];
  }
  
  function _neq($parameterPrefix = '', $params = array()): string {
    if (Eisodos::$parameterHandler->neq($params['param'], $params['value'])) {
      return Eisodos::$templateEngine->getTemplate($params['true'], array(), false);
    }
    
    return Eisodos::$templateEngine->getTemplate($params['false'], array(), false);
  }
  
  function _neqs($parameterPrefix = '', $params = array()): string {
    return Eisodos::$parameterHandler->neq($params['param'], $params['value']) ? $params['true'] : $params['false'];
  }
  
  function _case($parameterPrefix = '', $params = array()): string {
    return Eisodos::$templateEngine->getTemplate(
      Eisodos::$utils->safe_array_value(
        $params,
        Eisodos::$parameterHandler->getParam($params['param']),
        Eisodos::$utils->safe_array_value($params, 'else')),
      array(),
      false);
  }
  
  function _cases($parameterPrefix = '', $params = array()): string {
    return Eisodos::$utils->safe_array_value($params, Eisodos::$parameterHandler->getParam($params['param']), Eisodos::$utils->safe_array_value($params, 'else'));
  }
  
  function _safehtml($parameterPrefix = '', $params = array()): string {
    if (function_exists('tholos_safeHTML')) {
      $s = tholos_safeHTML(Eisodos::$parameterHandler->getParam($params['param']));
      if (strpos($s, "\n")) {
        $s = '<pre>' . $s . '</pre>';
      }
      
      return $s;
    }
    
    return '';
  }
  
  function _trim($parameterPrefix = '', $params = array()): string {
    return trim(Eisodos::$templateEngine->replaceParamInString($params['value']));
  }
  
  function _param2($parameterPrefix = '', $params = array()): string {
    if (!$params['param']) {
      return '';
    }
    
    return Eisodos::$templateEngine->replaceParamInString(Eisodos::$parameterHandler->getParam(Eisodos::$parameterHandler->getParam($params['param'])));
  }
  
  function _listToOptions($parameterPrefix = '', $params = array()): string {
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
  function _generateListValues($parameterPrefix = '', $params = array()): string {
    
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
    
    $fieldId = $component->getProperty('fieldid');
    $fieldText = $component->getProperty('fieldtext');
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
