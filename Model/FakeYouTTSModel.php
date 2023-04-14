<?php

class FakeYouTTSModel extends TTSModelBase
{
    public $TTSModelName = "FakeYou";
    protected $availableVoices = NULL;
    protected $currentVoice = NULL;
    private $sessionCookie = "";

    /*
    Sample model
    {
          "model_token": "TM:bysebgf36tkg",
          "tts_model_type": "tacotron2",
          "creator_user_token": "U:7S161Q96MG530",
          "creator_username": "zombie",
          "creator_display_name": "zombie",
          "creator_gravatar_hash": "c9f26e22a4d10bb1b75e4d8a84c85660",
          "title": "\"Arthur C. Clarke\" (901ep)",
          "ietf_language_tag": "en-US",
          "ietf_primary_language_subtag": "en",
          "is_front_page_featured": false,
          "is_twitch_featured": false,
          "maybe_suggested_unique_bot_command": null,
          "creator_set_visibility": "public",
          "user_ratings": {
            "positive_count": 25,
            "negative_count": 43,
            "total_count": 68
          },
          "category_tokens": [
            "CAT:gty64wem67f",
            "CAT:jhskand3g24",
            "CAT:46m8yaq2ceg"
          ],
          "created_at": "2022-04-15T08:34:03Z",
          "updated_at": "2023-03-21T04:53:35Z"
        },
    */

    public function __construct($language, $voice) {
        parent::__construct($language, $voice);
        $this->login();
    }

    public function login() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.fakeyou.com/login");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        $payload = '{
                   "username_or_email": "'.FAKEYOU_USERNAME.'",
                   "password": "'.FAKEYOU_PASSWORD.'"
                   }';
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        //echo $payload;
        $response = curl_exec($ch);
        //echo $response;
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
        $cookies = array();
        foreach($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        $this->sessionCookie = $cookies["session"];
        //echo $this->sessionCookie;
        /*
        Then you'll get a cookie that has the session cookie
        It won't be a JSON response
        It'll be in the body as a cookie
        You take that session cookie and use it on your future TTS Requests like:
        headers:{
        cookie: "session=12318718xxxx............"
        }
       */
    }

	public function getTextRemote($text, $fName)
	{
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.fakeyou.com/tts/inference");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: session=".$this->sessionCookie, "Content-Type: application/json"));

		$payload = '{
		           "uuid_idempotency_token": "'.guidv4(openssl_random_pseudo_bytes(16)).'",
                   "tts_model_token": "'.$this->currentVoice->model_token.'",
                   "inference_text": "'.$text.'"
				   }';
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        //echo $payload;
		$out = curl_exec($ch);
		//echo $out;
		//{"success":true,"inference_job_token":"JTINF:4pgwksz1069wkc345rkbzgscjp"}
		$response = json_decode($out);
        if ($response->success==true) {
            $retFile = "";
            do {
                sleep(5);
                $retFile = $this->waitForCompletion($response->inference_job_token);
            } while ($retFile=="");
            if ($retFile!="FAIL") {
                if (file_put_contents($fName, file_get_contents($retFile))) {
                    //echo "Saving as ".$fName;
                    return $fName;
                }
                else {
                    return PROJECT_CACHE_PATH."/FAIL.wav";
                }
            }
        }
        return PROJECT_CACHE_PATH."/FAIL.wav";
	}

   	public function getVoices($language, $forceRefresh = false) {
   	    $data = "";
   	    $l = $language;
   	    $pieces = explode("-", $language);
   	    if (count($pieces)>0) {
   	        $l = $pieces[0];
   	    }
        for ($i=0;$i<count($this->availableVoices->models);$i++) {
            if (strtolower($this->availableVoices->models[$i]->ietf_primary_language_subtag) == strtolower($l)) {
                $score = 0;
                if ($this->availableVoices->models[$i]->user_ratings->total_count>0) {
                    $score = ((int)$this->availableVoices->models[$i]->user_ratings->positive_count)*100/((int)$this->availableVoices->models[$i]->user_ratings->total_count);
                }
                $title = str_replace('"', "", $this->availableVoices->models[$i]->title);
                $title = str_replace('\\', "-", $title);
                $title .= " (".round($score)."%)";
                $data .= '{ "id" : "'.$this->availableVoices->models[$i]->model_token.'", "name" : "'.$title.'"},';
            }
        }
        if (strlen($data)>0) {
            $data = substr($data, 0, strlen($data)-1);
        }
        $data = "[".$data."]";

        return $data;
   	}

	public function getAvailbleVoicesRemote($cachedFileName, $userPreferredVoice) {
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.fakeyou.com/tts/list");
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);

		$out = curl_exec($ch);

		file_put_contents($cachedFileName, $out);
        $this->pickAModel($out, $userPreferredVoice);

		return $out;
	}

    public function getAvailbleVoicesFromCache($cachedFileName, $userPreferredVoice) {
        $data = "";
        if (!file_exists($cachedFileName)) {
            $data = getAvailbleVoicesRemote($cachedFileName);
        }
        else {
            $data = file_get_contents($cachedFileName);
            $this->pickAModel($data, $userPreferredVoice);
        }
        return $data;
    }

    private function pickAModel($jsonData, $userPreferredVoice) {
        $this->availableVoices = json_decode($jsonData);
        if ($userPreferredVoice!="") {
            // Look for user preferred voice
            for ($i=0;$i<count($this->availableVoices->models);$i++) {
                if (strtolower($this->availableVoices->models[$i]->model_token) == strtolower($userPreferredVoice)) {
                    $this->currentVoice = $this->availableVoices->models[$i];
                    $this->voiceId = $this->currentVoice->model_token;
                    return;
                }
            }
        }
        // If not found, just pick the first one available
        for ($i=0;$i<count($this->availableVoices->models);$i++) {
            if (strtolower($this->availableVoices->models[$i]->ietf_language_tag) == strtolower($this->language)) {
                $this->currentVoice = $this->availableVoices->models[$i];
                $this->voiceId = $this->currentVoice->model_token;
                break;
            }
        }
    }

    private function waitForCompletion($jobId) {
        $ret = "";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.fakeyou.com/tts/job/".$jobId);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: session=".$this->sessionCookie, "Content-Type: application/json"));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $out = curl_exec($ch);
        //echo $out;
   		$response = json_decode($out);
   		if ($response->success!=true) {
   		    $ret = "FAIL";
        }
        else {
            if ($response->state->status=="complete_success") {
                $ret = "https://storage.googleapis.com/vocodes-public".$response->state->maybe_public_bucket_wav_audio_path;
                //echo "success: ".$ret;
            }
        }
        return $ret;
    }

    protected function getOutputExtension() {
        return ".wav";
    }

    protected function getOutputMimeType() {
        return "audio/x-wav";
    }
}
?>