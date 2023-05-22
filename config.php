<?php
//header('Content-Type: application/x-mpegURL');
header('Content-Type: text/plain; charset=utf-8');

$actual_link = $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/"));
$static_url = "http://".$actual_link."/static.mp4\r\n\r\n";

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
  array("Euronews", "", "sPgqEHsONK8"),
  array("NBC News", "", "q2hcCya5TE4"),
  array("ABC News", "", "w_Ma8oQLmSM"),
  array("DW News", "", "qMtcWqCL_UQ"),
  array("LoFi Girl", "", "5qap5aO4i9A")
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

echo json_encode($configurations, JSON_PRETTY_PRINT);

?>