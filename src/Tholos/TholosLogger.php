<?php

  namespace Tholos;
  
  use DateTime;
  use Eisodos\Eisodos;
  use Eisodos\Logger;
  
  class TholosLogger extends Logger {
  
    public function __construct() {
        parent::__construct();
        Tholos::$logger = $this;
    }
  
    public function log(string $text_, string $debugLevel_ = 'debug', ?object $sender_ = NULL): void {
      
      $d = $this->getDebugLevels();
      
      if (in_array('trace', $d, false)
        || in_array($debugLevel_, $d, false)) {
        
        $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        
        if (array_key_exists(2, $dbt)) {
          $functionName = Eisodos::$utils->safe_array_value($dbt[2], 'function');
          $className = Eisodos::$utils->safe_array_value($dbt[2], 'class');
        } else {
          $functionName = '';
          if ($sender_ === NULL) {
            $className = '';
          } else {
            $className = get_class($sender_);
          }
        }
        
        $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
        
        $logLine = str_pad('[' . $now->format('Y-m-d H:i:s.u') . '] [' . mb_strtoupper($debugLevel_) . '] [' .
            (($sender_ && method_exists($sender_, 'getProperty')) ? $sender_->getProperty('Name') . ' - ' : '') . $className . ']' .
            ($functionName === '' ? '' : ' [' . $functionName . ']')
            , 100) . '|' . str_repeat(' ', ($text_ === 'BEGIN' ? (2 * $this->traceStep++) : ($text_ === 'END' ? (2 * --$this->traceStep) : 2 * $this->traceStep))) . $text_;
        
        $this->writeOutLogLine($logLine);
        
      }
    }
  
  }
