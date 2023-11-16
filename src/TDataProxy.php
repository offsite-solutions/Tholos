<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use Exception;
  use RuntimeException;

  class TDataProxy extends TComponent {
    
    private function parseHeaders($headers): array {
      $head = array();
      foreach ($headers as $k => $v) {
        $t = explode(':', $v, 2);
        if (isset($t[1])) {
          $head[trim($t[0])] = trim($t[1]);
        } else {
          $head[] = $v;
          if (preg_match("#HTTP/[\d.]+\s+([\d]+)#", $v, $out)) {
            $head['response_code'] = (int)$out[1];
          }
        }
      }
      
      return $head;
    }
    
    /**
     * Mirrors a TDataProvider call to a remote Tholos Application
     *
     * @param TComponent|NULL $sender
     * @param array $requestPropertyNames_
     * @param array $responsePropertyNames_
     * @return array|string
     * @throws Exception
     */
    public function open(?TComponent $sender, $requestPropertyNames_ = array(), $responsePropertyNames_ = array()) {
      
      try {
        
        // user defined parameters
        $proxy_client_parameters = array();
        foreach (Tholos::$app->findChildIDsByType($this, 'TDataProxyParameter') as $id) {
          $component = Tholos::$app->findComponentByID($id);
          if (!$component) {
            throw new RuntimeException("Invalid reference");
          }
          $proxy_client_parameters[$component->getProperty('ParameterName', '')] = $component->getProperty('Value', '');
        }
        
        if (!$sender) {
          throw new RuntimeException('Sender must be specified');
        }
        
        // sending caller object's selected properties to the other side, query1>OrderBy=1 asc
        foreach ($requestPropertyNames_ as $n) {
          if ($sender->getPropertyType($n) === 'ARRAY') {
            $proxy_client_parameters[$sender->getProperty('Name') . '>' . $n] = json_encode($sender->getProperty($n, ''), JSON_THROW_ON_ERROR);
          } else {
            $proxy_client_parameters[$sender->getProperty('Name') . '>' . $n] = $sender->getProperty($n, '');
          }
        }
        
        // sending caller object's selected properties to the other side to get its values back, query1:OrderBy=1 asc
        foreach ($responsePropertyNames_ as $n) {
          $proxy_client_parameters[$sender->getProperty('Name') . '<' . $n] = 'response';
        }
        
        // merging all parameters with input parameters
        $data = array_merge(
          $proxy_client_parameters,
          $_POST,
          $_GET,
          array('responseType' => 'PROXY', // force response type to PROXY for a common structure
            'TholosProxy:TargetComponentID' => $sender->_id,
            'TholosProxy:ProxyComponentID' => $this->_id,
            'TholosProxy:SESSION_ID' => Eisodos::$parameterHandler->getParam('_sessionid'),
            'TholosProxy:Tholos_sessionID' => Eisodos::$parameterHandler->getParam('Tholos_sessionID'),
            'TholosProxy:Tholos_renderID' => Tholos::$app->renderID,
            'TholosProxy:LoginID' => Eisodos::$parameterHandler->getParam('LoginID'),
            'TholosCacheAction' => 'refresh' // do not use cache on the proxy side
          )
        );
        
        $restrictedParameters = array('tholos_route', 'tholos_action', 'tholos_partial');
        
        foreach ($restrictedParameters as $key) {
          if (array_key_exists($key, $data)) {
            $data[$key] = '';
          }
        }
        
        Tholos::$app->trace(print_r($data, true));
        
        // creating http header info
        $options = array(
          'http' => array(
            'header' => array(
              'Content-type: application/x-www-form-urlencoded',
              'HTTP_X_REQUESTED_WITH: ' . ($this->getProperty('AJAXMode', 'false') === 'true' ? 'xmlhttprequest' : '')
            ),
            'method' => 'POST',
            'content' => http_build_query($data, '', '&', PHP_QUERY_RFC3986)
          )
        );
        
        $context = stream_context_create($options);
        $url = $this->getProperty('URL') . Tholos::$app->getComponentRouteActionFromIndex($sender->_id);
        
        Tholos::$app->trace('Proxy URL: ' . $url, $this);
        Tholos::$app->trace('Proxy parameters: ' . print_r($data, true), $this);
        
        $result = file_get_contents($url, false, $context);
        if ($result === false) {
          throw new RuntimeException('False result');
        }
        
        Tholos::$app->trace($result);
        $header = $this->parseHeaders($http_response_header);
        Tholos::$app->trace(print_r($header, true), $this);
        
        if (array_key_exists('Location', $header)) {
          Eisodos::$parameterHandler->setParam('REDIRECT', $header['Location']);
        }
        if (array_key_exists('X-Tholos-Redirect', $header)) {
          Eisodos::$parameterHandler->setParam('REDIRECT', $header['X-Tholos-Redirect']);
        }
        if (array_key_exists('X-Tholos-Logout', $header)) {
          Tholos::$app->roleManager->logout();
        }
        
        switch ($header['response_code']) {
          case 200:
            return $result;
          default:
            return [];
        }
        
      } catch (Exception $e) {
        Tholos::$app->error($e->getMessage(), $this);
        Eisodos::$logger->writeErrorLog($e);
        throw new RuntimeException('DataProxy call is invalid!');
      }
      
      
    }
    
  }

