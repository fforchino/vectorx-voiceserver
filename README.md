# vectorx-voiceserver
Source code for the VectorX PHP Voice server. The aim of this code is to provide go sdk wrapper a single point of access 
for converting text into audio files. Ideally it should support pluggable providers, so that multiple providers can be 
selected and accessed using the same APIs.

For now the only provider supported is fakeyou.com.
ResembleAI was intended to be supported too (I started the work on this) but the code is just sketched and needs to be 
completed.

I would add a way for go sdk to pass the API keys to use for the given provider, so that users can use their own paid 
API keys to improve the speed and quality of the rendering. And still caching improves the service for all if hosted on
a shared server.

# How to use

Edit inc/config.php and replace 

`define("RESEMBLE_AI_API_TOKEN", "YOUR_API_TOKEN_HERE"); 
 define("FAKEYOU_USERNAME", "YOUR_EMAIL_HERE");
 define("FAKEYOU_PASSWORD", "YOUR_PASSWORD_HERE");
`

with real data. Then host on PHP web server, and change the API endpoint in go-sdk (https://github.com/fforchino/vector-go-sdk/blob/main/pkg/sdk-wrapper/sdk-wrapper-voice.go),
that currently points to my hosted server:

`theUrl := "https://YOUR_VOICE_SERVER_HOME/index.php/getText?text=" + url.QueryEscape(text) + "&lang=" + vsLanguage + "&voice=" + ttsVoice
`

that's it.

Any help in enhancing the server and its interface towards go-sdk is welcome. 

Changelog

VERSION_03
- Added API hit counter

VERSION_02
- Decrease timeouts when rendering.
- Log start and end of jobs to file.

VERSION_01
- First version on github
  