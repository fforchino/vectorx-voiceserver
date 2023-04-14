<?php
class TTSController extends BaseController
{
    /**
	* "/tts/getVoices" Endpoint - Returns a json with the available voices
	* @return json
	*/

    public function getVoicesAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
        $responseData = "";
        $forceRefresh = false;

        if (strtoupper($requestMethod) == 'GET') {
            try {
				$lang = "en-US";
                if (isset($arrQueryStringParams['lang']) && $arrQueryStringParams['lang']) {
                    $lang = $arrQueryStringParams['lang'];
                }
                if (isset($arrQueryStringParams['nocache']) && $arrQueryStringParams['nocache']) {
                    if ($arrQueryStringParams['nocache']=="1") {
                        $forceRefresh = true;
                    }
                }
                if (isset($lang)) {
                    $TTSModel = new FakeYouTTSModel("en-US", "");
                    $responseData = $TTSModel->getVoices($lang, $forceRefresh);
                    if ($responseData=="FAIL") {
                        $strErrorDesc = 'TTS Failure.';
                        $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
                    }
				} else {
                    $strErrorDesc = 'Missing parameters.';
                    $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
				}
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }
        // send output
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)),
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }

    /**
	* "/tts/getText" Endpoint - Get a text in the given language
	* @return audio
	*/
    
	public function getTextAction()
    {
	    $isJson = true;
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
        $responseData = "";
        $forceRefresh = false;

        if (strtoupper($requestMethod) == 'GET') {
            try {
				$text = $lang = $voice = "";
                if (isset($arrQueryStringParams['text']) && $arrQueryStringParams['text']) {
                    $text = $arrQueryStringParams['text'];
                }
                if (isset($arrQueryStringParams['lang']) && $arrQueryStringParams['lang']) {
                    $lang = $arrQueryStringParams['lang'];
                }
                if (isset($arrQueryStringParams['voice']) && $arrQueryStringParams['voice']) {
                    $voice = $arrQueryStringParams['voice'];
                }
                if (isset($arrQueryStringParams['nocache']) && $arrQueryStringParams['nocache']) {
                    if ($arrQueryStringParams['nocache']=="1") {
                        $forceRefresh = true;
                    }
                }
                $TTSModel = new FakeYouTTSModel($lang, $voice);
				if ($text!="" && $lang!="") {
					$responseData = $TTSModel->getText($text, $forceRefresh);
					if ($responseData=="FAIL") {
                        $strErrorDesc = 'TTS Failure.';
                        $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
					}
				} else {
                    $strErrorDesc = 'Missing parameters.';
                    $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
				}
				$isJson = false;
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }
        // send output
        if (!$strErrorDesc) {
			if ($isJson) {
				$this->sendOutput(
					$responseData,
					array('Content-Type: application/json', 'HTTP/1.1 200 OK')
				);
			}
			else {
				// TODO!!!!
			}
        } else {
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }
}
?>