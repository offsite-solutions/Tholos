<?php
  
  namespace Tholos;
  
  use Eisodos\Eisodos;
  use Exception;
  use Mpdf\HTMLParserMode;
  
  /**
   * TPage Component class
   *
   * TPage component defines the layout of the HTML page.
   * Descendant of TComponent.
   *
   * @package Tholos
   * @see TComponent
   */
  class TPDFPage extends TPage {
    
    private string $headerContent = '';
    private string $footerContent = '';
    
    public function init(): void {
      
      parent::init();
      /* forces application to start render at this component */
      Tholos::$app->renderer = $this;
      Tholos::$logger->debug('Renderer component has been set', $this);
    }
    
    /**
     * @inheritdoc
     */
    public function render(?TComponent $sender, string $content): string {
      
      try {
        
        if (!Tholos::$app->checkRole($this)) {
          return '';
        }
        
        $this->renderedContent = '';
        Tholos::$app->eventHandler($this, 'onBeforeRender');
        
        Eisodos::$parameterHandler->setParam('TholosViewMode', 'print');
        
        Tholos::$app->initResponsePDF(json_decode($this->getProperty('PDFConfig', '{}'), true, 512, JSON_THROW_ON_ERROR));
        
        if ($this->getProperty('HeaderContainerName') !== false) {
          $this->headerContent = Tholos::$app->cleanupRenderedHTML(Tholos::$app->render($this, Tholos::$app->findComponentIDByNameClassFromIndex($this->getProperty('HeaderContainerName'), 'TContainer', Tholos::$app->action_id), true));
          Tholos::$logger->trace($this->headerContent, $this);
        }
        if ($this->getProperty('FooterContainerName') !== false) {
          $this->footerContent = Tholos::$app->cleanupRenderedHTML(Tholos::$app->render($this, Tholos::$app->findComponentIDByNameClassFromIndex($this->getProperty('FooterContainerName'), 'TContainer', Tholos::$app->action_id), true));
          Tholos::$logger->trace($this->footerContent, $this);
        }
        
        $bodyContent = Tholos::$app->cleanupRenderedHTML(Tholos::$app->render($this, Tholos::$app->findComponentIDByNameClassFromIndex($this->getProperty('BodyContainerName'), 'TContainer', Tholos::$app->action_id), true));
        
        // replace tokens, tokens must be in form %%PARAMETER_NAME
        
        $this->headerContent = Eisodos::$templateEngine->replaceParamInString(Eisodos::$utils->replace_all($this->headerContent, '%%', '$'));
        $this->footerContent = Eisodos::$templateEngine->replaceParamInString(Eisodos::$utils->replace_all($this->footerContent, '%%', '$'));
        $bodyContent = Eisodos::$templateEngine->replaceParamInString(Eisodos::$utils->replace_all($bodyContent, '%%', '$'));
        
        if ($this->getProperty('Watermark') !== false) {
          Tholos::$app->responsePDF->SetWatermarkText($this->getProperty('Watermark'));
          Tholos::$app->responsePDF->showWatermarkText = true;
        }
        
        Tholos::$logger->trace($bodyContent, $this);
        
        $this->generateProps();
        $this->generateEvents();
        
        if ($this->getProperty('CSSFile') !== false) {
          Tholos::$logger->trace('PDF with CSS: ' . $this->getProperty('CSSFile'), $this);
          $css = file_get_contents($this->getProperty('CSSFile'));
          Tholos::$app->responsePDF->WriteHTML($css, HTMLParserMode::HEADER_CSS);
        }
        
        Tholos::$app->responsePDF->SetHTMLHeader($this->headerContent);
        Tholos::$app->responsePDF->SetHTMLFooter($this->footerContent);
        Tholos::$app->responsePDF->WriteHTML(Tholos::$app->cleanupRenderedHTML(Eisodos::$templateEngine->getTemplate($this->getproperty('Template'), array('content' => $bodyContent), false)));
        
        if ($this->getPropertyComponentId('PAdES') !== false) {
          
          /* @var $PAdES TPAdES */
          $PAdES = Tholos::$app->findComponentByID($this->getPropertyComponentId('PAdES'));
          $PAdES->InputPDF = Tholos::$app->responsePDF->Output('', 'S');
          $PAdES->signPDF($this, false);
          
          header('Content-Type: application/pdf');
          if (empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            header('Content-Length: ' . strlen($PAdES->OutputPDF));
          }
          header('Content-disposition: inline; filename="' . date('YmdHis') . '.pdf"');
          header('Cache-Control: public, must-revalidate, max-age=0');
          header('Pragma: public');
          echo $PAdES->OutputPDF;
          Eisodos::$parameterHandler->setParam('Tholos.WriteLanguageFile', 'T');
          Tholos::$app->responseType = 'BINARY'; // force application not to modify output
          
        } else {
          if ($this->getProperty('Download') == 'true') {
            Tholos::$app->responsePDF->Output();
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream');
            header('Content-Type: application/download');
            if (!$this->getProperty('FileName')) {
              $this->setProperty('FileName', date('YmdHis'));
            }
            header('Content-Disposition: attachment;filename="' . $this->getProperty("FileName") . '.pdf"');
            Tholos::$app->responseType = 'BINARY';
          } else {
            Tholos::$app->responseType = 'PDF';
          }
          Eisodos::$parameterHandler->setParam('Tholos.WriteLanguageFile', 'T');
          Tholos::$app->responseType = 'PDF';
        }
        
      } catch (Exception $e) {
        Tholos::$logger->writeErrorLog($e);
        header('X-Tholos-Error-Code: -1');
        header('X-Tholos-Error-Message: ' . $e->getMessage());
        header('X-Tholos-Error-Message-B64: ' . base64_encode($e->getMessage()));
        http_response_code(400);
        Tholos::$app->responseType = 'BINARY';
      }
      
      Tholos::$app->eventHandler($this, 'onAfterRender');
      
      return $this->renderedContent;
      
    }
  }
  