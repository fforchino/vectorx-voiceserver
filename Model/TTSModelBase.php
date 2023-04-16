<?php

abstract class TTSModelBase
{
    public $TTSModelName = "";
    protected $language = "en-US";
    protected $voiceId = "";

    public function __construct($language, $userPreferredVoice) {
        $this->language = $language;
        $voiceId = $userPreferredVoice;
        $this->getAvailableVoices($userPreferredVoice);
    }

    public function getAvailableVoices($userPreferredVoice, $forceRefresh = false) {
        $voiceListFileName = $this->getCacheDir();
		$voiceListFileName.="/voices.json";
		if ($forceRefresh || !file_exists($voiceListFileName)) {
            $this->getAvailbleVoicesRemote($voiceListFileName, $userPreferredVoice);
		}
		else {
		    $this->getAvailbleVoicesFromCache($voiceListFileName, $userPreferredVoice);
		}
    }

    public function getText($text, $forceRefresh = false)
    {
        $textFileName = $this->getCacheDir();
        $textFileName.="/".$this->voiceId;
        if (!file_exists($textFileName)) {
            mkdir($textFileName, 0777, true);
        }
		$textFileName.="/".$this->textToFileName($text);

		if ($forceRefresh || !file_exists($textFileName)) {
            $this->fileLog("Request received (".strlen($text)." characters), start rendering process.");
			$this->getTextRemote($text, $textFileName);
		} else {
		    $this->fileLog("Request received (".strlen($text)." characters), serving with cache data.");
        }
		$mime_type = $this->getOutputMimeType();

		header('Content-type: '.$mime_type);
		header('Content-length: ' . filesize($textFileName));
		header('Content-Disposition: filename="' . basename($textFileName) . '"');
		header('X-Pad: avoid browser bug');
		header('Cache-Control: no-cache');
		readfile($textFileName);
        $this->fileLog("Request served, ".filesize($textFileName)." bytes sent.");
    }

   	public abstract function getVoices($language, $forceRefresh = false);

    protected function getCacheDir() {
        $textFileName = PROJECT_CACHE_PATH.$this->language;
        //echo "DIR:".$textFileName;
        if (!file_exists($textFileName)) {
            mkdir($textFileName, 0777, true);
        }
        $textFileName.="/".$this->TTSModelName;
        if (!file_exists($textFileName)) {
            mkdir($textFileName, 0777, true);
        }
        return $textFileName;
    }

	protected function textToFileName($text) {
		return base64_encode($text).$this->getOutputExtension();
	}

    protected function getOutputExtension() {
        return ".mp3";
    }

	protected function getOutputMimeType() {
	    return "audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3";
	}

	abstract public function getTextRemote($text, $fName);
	abstract public function getAvailbleVoicesRemote($cachedFileName, $userPreferredVoice);
	abstract public function getAvailbleVoicesFromCache($cachedFileName, $userPreferredVoice);

	protected function fileLog($text) {
	    $text = date(""Y-m-d H:i:s")." [".$_SERVER['REMOTE_ADDR']."] ".$text;
	    file_put_contents(LOG_FILENAME, $text, FILE_APPEND | LOCK_EX);
	}
}
?>