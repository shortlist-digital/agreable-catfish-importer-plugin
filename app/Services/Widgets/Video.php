<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use \stdClass;

class Video {
  public static function getFromWidgetDom($widgetDom) {
    $widgetData = new stdClass();
    $widgetData->type = 'embed';
    $videoIframe = $widgetDom->find('iframe');
    $facebookVideo = $widgetDom->find('div[data-href]');

    if (!isset($videoIframe[0])) {
      $widgetData->embed = $facebookVideo[0]->{'data-href'};
    } else {
      $src = self::filterFrameSrc($videoIframe[0]->src);
      $widgetData->embed = $src;
    }

    return $widgetData;
  }

  public static function filterFrameSrc($url) {
		if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
			$url = str_replace("//","", $url);
			$url = "http://" . $url;
    }
  	if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $id)) {
			$values = $id[1];
		} else if (preg_match('/youtube\.com\/embed\/([^\&\?\/]+)/', $url, $id)) {
			$values = $id[1];
		} else if (preg_match('/youtube\.com\/v\/([^\&\?\/]+)/', $url, $id)) {
			$values = $id[1];
		} else if (preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $id)) {
			$values = $id[1];
		}
		else if (preg_match('/youtube\.com\/verify_age\?next_url=\/watch%3Fv%3D([^\&\?\/]+)/', $url, $id)) {
				$values = $id[1];
		} else {
			return $url;
		}
		return "https://www.youtube.com/watch?v=".$values;
  }
}
