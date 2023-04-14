<?php

class ResembleAiTTSModel extends TTSModelBase
{
    public $TTSModelName = "ResembleAI";

    public function __construct($language) {
        parent::__construct($language);
    }
/*
curl --request POST "https://f.cluster.resemble.ai/synthesize"
  -H "x-access-token: <TOKEN>"
  -H "Content-Type: application/json"
  -H "Accept-Encoding: gzip, deflate, br"
  --data '{
    "voice_uuid": <Voice to synthesize in>,
    "project_uuid": <Project to save to>,
    "title": <Title of the clip>,
    "data": <Text to synthesize>,
    "precision": "ULAW|PCM_16|PCM_24|PCM_32 (default)"
    "output_format": "mp3|wav (default)"
  }'
*/
	public function getTextRemote($text, $fName)
	{
		$ret = false;
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://f.cluster.resemble.ai/synthesize");
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["x-access-token: ".RESEMBLE_AI_API_TOKEN,"Content-Type: application/json", "Accept-Encoding: gzip, deflate, br"]);
		$payload = '{ 
		           "voice_uuid": "Vector",
                   "project_uuid": "Vector",
                   "title": "'.textToTitle($text).'",
                   "data": "'.$text.'",
                   "precision": "PCM_16"
                   "output_format": "mp3"
				   }';
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_HEADER, 0);

		$out = curl_exec($ch);		
		curl_close($ch);
		if ($out!=false) {
			$fp = fopen($fName, 'w');
			if ($fp!=false) {
				fwrite($fp, $out);
				fclose($fp);
				$ret = true;
			}
		}

		return $ret;
	}

	public function getAvailbleVoicesRemote($cachedFileName, $userPreferredVoice) {
        // Todo...
	}

    public function getAvailbleVoicesFromCache($cachedFileName, $userPreferredVoice) {
        // Todo...
    }

    public function getVoices($language, $forceRefresh = false) {
        return "[]";
    }
}
?>