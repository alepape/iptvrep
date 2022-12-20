<?php
//header('Content-Type: application/x-mpegURL');
header('Content-Type: text/plain; charset=utf-8');

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 'On');

$actual_link = $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/"));
$static_url = "http://".$actual_link."/static.mp4\r\n\r\n";

class Channel {

    public $tvg_id;
    public $name;
    public $tvg_logo;
    public $url;
    public $tvg_language;
    public $tvg_country;
    public $group_title;

    public function __construct($group) {
      $this->tvg_id = '';
      $this->name = '';
      $this->tvg_logo = '';
      $this->url = '';
      $this->tvg_language = '';
      $this->tvg_country = '';
      // if ($group != '') {
      //   $this->group_title = $group; // used as default, not override!
      // } else {
      //   $this->group_title = '';
      // }
      $this->group_title = $group;
    }

    public function toString() {
      $line1 = '#EXTINF:-1 tvg-id="'.$this->tvg_id.'" tvg-language="'.$this->tvg_language.'" tvg-logo="'.$this->tvg_logo.'" tvg-country="'.$this->tvg_country.'" group-title="'.$this->group_title.'",'.$this->name."\n";
      $line2 = $this->url . "\n";
      return $line1 . $line2;
    }

    public function parseLines($line1, $line2) {

      $this->url = $line2;
      $parsed = extinfString2Array($line1);
      $this->name = $parsed['name'];
      $this->tvg_id = $parsed['tvg-id'];
      $this->tvg_logo = $parsed['tvg-logo'];
      $this->tvg_language = $parsed['tvg-language'];
      $this->tvg_country = $parsed['tvg-country'];
      if ($parsed['group-title'] != '') {
        $this->group_title = $parsed['group-title'];
      } 
    }
}

function extinfString2Array($string) {

    preg_match_all('/(?P<tag>#EXTINF:[0|\-])|(?:(?P<prop_key>[-a-z]+)=\"(?P<prop_val>[^"]+)")|(?<something>,[^\r\n]+)|(?<url>http[^\s]+)/', $string, $match );

    $count = count( $match[0] );

    $result = [];
    $index = -1;

    for( $i =0; $i < $count; $i++ ) {
        $item = $match[0][$i];

        if( !empty($match['tag'][$i])){
            //is a tag increment the result index
            ++$index;
        }elseif( !empty($match['prop_key'][$i])){
            //is a prop - split item
            $result[$index][$match['prop_key'][$i]] = $match['prop_val'][$i];
        }elseif( !empty($match['something'][$i])){
            //is a prop - split item
            $result[$index]['name'] = trim(substr($item, 1));
        }elseif( !empty($match['url'][$i])){
            $result[$index]['url'] = $item ;
        }
    }

    return $result[0];
}

function endsWith($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }
    return (substr($haystack, -$length) === $needle);
}

function parse_config($configs) {
    $output = "";

    foreach ($configs as $config) {
      $output .= generate_output($config);
    }

    return "#EXTM3U\r\n" . $output;
}

function generate_output($config) {
    if ($config['file'] != null) {
      return local_src($config);
    }
    if ($config['url'] != null) {
      return remote_src($config);
    }
    if ($config['youtube'] != null) {
      return youtube($config);
    }
}

function youtube($config) {
  global $static_url;
  $cleaned_output = "\r\n".'#EXTINF:-1 group-title="+ '.$config['title'].'",+ '.$config['title']."\r\n";
  $cleaned_output .= $static_url;

  for ($x = 0; $x < count($config['youtube']); $x++) {
    $ytvideo = $config['youtube'][$x];
    $yturl = getYTurl($ytvideo[2]);
    $channel = new Channel($config['title']);
    $channel->tvg_id = "yt.".$ytvideo[0];
    $channel->name = $ytvideo[0];
    $channel->tvg_logo = $ytvideo[1];
    $channel->url = $yturl;
    $channel->tvg_language = "English";
    $channel->tvg_country = "Australia";
    $cleaned_output .= $channel->toString();
  }
  return $cleaned_output;
}

function getYTurl($videoid) {
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://youtubei.googleapis.com/youtubei/v1/player?key=AIzaSyAO_FJ2SlqU8Q4STEHLGCilw_Y9_11qcW8',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
   "context": {
     "client": {
      "hl": "en",
      "clientName": "WEB",
      "clientVersion": "2.20210721.00.00",
      "mainAppWebInfo": {
          "graftUrl": "/watch?v='.$videoid.'"
      }
     }
    },
    "videoId": "'.$videoid.'"
  }',
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);
  //$array = [];
  //parse_str($response, $array);
  //$json = $array['player_response'];
  $jsonObj = json_decode($response, true);
  return $jsonObj['streamingData']['hlsManifestUrl'];
  
}

function local_src($config) {
    global $static_url;
    $result = file_get_contents($config['file']);
    // TODO: cleanup, remove empty lines, check EPG?

    if ($config['remove'] != null) {
      $output = remove($result, $config['remove']);
    } else if ($config['keep'] != null) {
      $output = keep($result, $config['keep']);
    } else {
      $output = $result;
    }

    $cleaned_output = "\r\n".'#EXTINF:-1 group-title="+ '.$config['title'].'",+ '.$config['title']."\r\n";
    $cleaned_output .= $static_url;

    $std_array = explode("\n", $output);
    for ($x = 0; $x <= count($std_array); $x++) {
      $line = $std_array[$x];
      if (strpos($line, '#EXTINF:') === 0) {
        $channel = new Channel($config['title']);
        $channel->parseLines($line, $std_array[$x+1]);
        $cleaned_output .= $channel->toString();
      }
    }

    return $cleaned_output;
}

function remote_src($config) {
  global $static_url;
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
	  CURLOPT_URL => $config['url'],
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	));
	
	$result = curl_exec($curl);

    $output = "";

    if ($config['remove'] != null) {
      $output = remove($result, $config['remove']);
    } else if ($config['keep'] != null) {
      $output = keep($result, $config['keep']);
    } else {
      $output = $result;
    }

    // TODO: should be done in keep and remove fcts...
    $cleaned_output = "\r\n".'#EXTINF:-1 group-title="+ '.$config['title'].'",+ '.$config['title']."\r\n";
    $cleaned_output .= $static_url;

    $std_array = explode("\n", $output);
    for ($x = 0; $x <= count($std_array); $x++) {
      $line = $std_array[$x];
      if (strpos($line, '#EXTINF:') === 0) {
        if (isset($config['title'])) {
          $channel = new Channel($config['title']);
        } else {
          $channel = new Channel('');
        }
        $channel->parseLines($line, $std_array[$x+1]);
        $cleaned_output .= $channel->toString();
      }
    }

    return $cleaned_output;
}

function remove($input, $remove) {

    $cleaned = "";

    $std_array = explode("\n", $input);
    for ($x = 0; $x <= count($std_array); $x++) {
      $line = $std_array[$x];
      if (strpos($line, '#EXTINF:') === 0) {
          // new channel
          $clean = TRUE;
          foreach ($remove as $removable) {
            # code...
            if (strpos($line, $removable)) {
              $clean = FALSE;
            }
          }
          if ($clean) {
          $cleaned .= $line . "\n";
          } else {
            $x +=1;
          }
      } else {
        $cleaned .= $line . "\n";
      }
    }

    return $cleaned;
}

function keep($input, $wanted) {

    $kept = "";
    $result_array = array_fill_keys ( $wanted , array());

    $input = str_replace("\r", '', $input);

    $std_array = explode("\n", $input);

    for ($x = 0; $x <= count($std_array); $x++) {
      $line = $std_array[$x];
      if (strpos($line, '#EXTINF:') === 0) {
          // new channel
          $keep = FALSE;
          $found = "";

          foreach ($wanted as $want) {
            if (endswith(trim($line), $want)) {
              $keep = TRUE;
              $found = $want;
              break;
            }
          }
          if ($keep) {
            $line1 = $line . "\n";
            $line2 = $std_array[$x + 1] . "\n";  

            $entry = array($line1, $line2);
            array_push($result_array[$found], $entry);

          } else {
            $x +=1;
          }
      } else {
      }
    }

    // TODO: check duplicates
    // & remove failures

    foreach ($result_array as $key => $value) {
      if (count($value) == 1) {
        $kept .= $value[0][0];
        $kept .= $value[0][1];
      } else if (count($value) > 1) {
        // return the first that works
        foreach ($value as $attempt) {
          if (test_url($attempt[1])) {
            $kept .= $attempt[0];
            $kept .= $attempt[1];
            continue 2;            
          }
        }
      }
    }

    return $kept;
}

function test_url($URL) {
  $curlHandle = curl_init();
  curl_setopt($curlHandle, CURLOPT_URL, $URL);
  curl_setopt($curlHandle, CURLOPT_HEADER, true);
  curl_setopt($curlHandle, CURLOPT_NOBODY  , true);  // we don't need body
  curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
  curl_exec($curlHandle);
  $response = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
  curl_close($curlHandle); // Don't forget to close the connection

  if ($response != 200) {
    return true;
  } else {
    return false;
  }
}

// configuration
$configurations = array();

$yt = array(
  "file" => null,
  "url" => null,
  "remove" => null,
  "keep" => null,
  "youtube" => array(),
  "epg" => false, // TODO: merge EPGs as well
  "group" => "Misc",
  "order" => 6,
  "title" => "Youtube"
);
// title, logo, videoID
$yt["youtube"] = array(
  array("Euronews", "", "ntmPIzlkcJk"),
  array("NBC News", "", "q2hcCya5TE4"),
  array("ABC News", "", "w_Ma8oQLmSM"),
  array("DW News", "", "ammKkVgtIHw"),
  array("LoFi Girl", "", "jfKfPfyJRdk")
);

$main = array(
  "file" => null,
  "url" => "http://i.mjh.nz/au/Melbourne/raw-tv.m3u8",
  "remove" => array(),
  "keep" => null,
  "epg" => true, // TODO: merge EPGs as well
  "group" => "Basic",
  "order" => 0,
  "title" => "Melbourne"
);
$main["remove"] = array(
  "tv.skyrt", 
  "tv.sky2rw", 
  "tv.sky1rw", 
  "tv.7openshop", 
  "tv.7ausbiz",
  "tv.101305020528",
  "tv.7pac12",
  "tv.7olympics",
  "tv.7fueltv",
  "tv.320203000305",
  "tv.c31",
  "tv.acctv",
  "tv.3aw.melbourne"
);
$custom = array(
  "file" => "custom.m3u8",
  "url" => null,
  "remove" => null,
  "keep" => null,
  "epg" => false,
  "order" => 1,
  "title" => "Custom"
);
$iptvorg_en = array(
  "file" => null,
  "url" => "https://iptv-org.github.io/iptv/languages/eng.m3u",
  "remove" => null,
  "keep" => array(),
  "epg" => false,
  "group" => "Misc",
  "order" => 2,
  "title" => "IPTV.org - EN"
);
$iptvorg_en["keep"] = array(
  "ABC News (720p)", // ok
  "BBC News", // ok
  "BBC World News (576p)", // ok 
  "CBS News", // ok
  "CBS News Los Angeles (720p)", // ok
  "CNN",
  "ESPN 2",
  "France 24 English",
  "HBO (East) 1080p",
  "IDG",
  "PBS America",
  "Sky News Extra 2 (540p)",
  "TMZ"
);
$iptvorg_fr = array(
  "file" => null,
  "url" => "https://iptv-org.github.io/iptv/countries/fr.m3u",
  "remove" => null,
  "keep" => array(),
  "epg" => false,
  "group" => "France",
  "order" => 3,
  "title" => "IPTV.org - FR"
);
$iptvorg_fr["keep"] = array("BFM Paris",
  "TV5Monde Info"
);
$fluxus = array(
  "file" => "flux.m3u8",
  "url" => null,
  "remove" => null,
  "keep" => array(),
  "epg" => false,
  "group" => "Misc",
  "order" => 4,
  "title" => "Fluxus.tv"
);
$fluxus["keep"] = array(
  "NHK World (JP)",
  "Movie Kingdom",
  "Pop",
  "NTV News24",
  "Rick and Morty",
  "Bloomberg Television",
  "CinemaWorld (Opt-2)"
);

$big = array(
  "file" => null,
  "url" => "https://gist.githubusercontent.com/onigetoc/8ed7263e644b7d121d0275c805f1ee4a/raw/8c141f092e795ce68b2f9190a988df1be5d2a0c6/IPTV-big-list.m3u",
  "remove" => null,
  "keep" => array(),
  "epg" => false,
  "group" => "Misc",
  "order" => 5,
  "title" => "BIG"
);

$big["keep"] = array(
  "BFM Business",
  "BX1",
  "TF1 Series films",
  "CBS"
);

//array_push($configurations, $main, $custom, $big, $fluxus, $iptvorg_en, $iptvorg_fr, $yt); 
array_push($configurations, $main, $big, $fluxus, $iptvorg_en, $iptvorg_fr, $yt); 

$output = parse_config($configurations);

header('Content-Length: ', mb_strlen($output, '8bit'));

echo $output;

?>