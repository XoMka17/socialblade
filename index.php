<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: " . date("r"));

// include parser-library
include('simplehtmldom_1_5/simple_html_dom.php');
// https://socialblade.com/youtube/user/***

$socialblade = '';
if( isset($_GET['socialblade']) ) {
    $socialblade = $_GET['socialblade'];
}

// https://www.patreon.com/***
$patreon = '';
if( isset($_GET['patreon']) ) {
    $patreon = $_GET['patreon'];
}

if(file_exists(dirname(__FILE__) . '/cookie.txt'))
    unlink(dirname(__FILE__) . '/cookie.txt');

$url = 'https://socialblade.com/youtube/user/' . $socialblade;
$curl = curl_init();
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.12) Gecko/20050919 Firefox/1.0.7');
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl,CURLOPT_UNRESTRICTED_AUTH,1);
curl_setopt($curl, CURLOPT_REFERER, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__) . '/cookie.txt');
curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__) . '/cookie.txt');
curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,30);

$str = curl_exec($curl);

$html = new simple_html_dom();
$html->load($str);

$views = preg_replace('~[^-0-9]+~','',$html->find('span#afd-header-views-30d')[0]->innertext);
$subscriber_rank = preg_replace('~[^-0-9]+~','',$html->find('p#afd-header-subscriber-rank')[0]->innertext);
$subscribers_month = preg_replace('~[^-0-9]+~','',$html->find('span#afd-header-subs-30d')[0]->innertext);

$subscribers_day = 0;
$div = $html->find("a[href=/youtube/user/$socialblade/realtime]",-1);
if($div)
    $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);

if($subscribers_day == 0) {
    $str = ucfirst($socialblade);
    $div = $html->find("a[href=/youtube/user/$str/realtime]",-1);
    if($div)
        $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
}
else if($subscribers_day == 0) {
    $str = lcfirst($socialblade);
    $div = $html->find("a[href=/youtube/user/$str/realtime]",-1);
    if($div)
        $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
}
else if($subscribers_day == 0) {
    $str = mb_convert_case($socialblade, MB_CASE_UPPER, "UTF-8");
    $div = $html->find("a[href=/youtube/user/$str/realtime]",-1);
    if($div)
        $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
}
else if($subscribers_day == 0) {
    $str = mb_convert_case($socialblade, MB_CASE_LOWER, "UTF-8");
    $div = $html->find("a[href=/youtube/user/$str/realtime]",-1);
    if($div)
        $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
}

$div = $html->find('div[id=averagedailyviews]',0);
if($div)
    $subscribers_middle_day = preg_replace('~[^-0-9]+~','',$div->parent()->children(1)->find('span',0)->innertext);

// subscribers
$url .= '/realtime';
curl_setopt($curl, CURLOPT_URL, $url);
$str = curl_exec($curl);
$html = new simple_html_dom();
$html->load($str);

$subscribers = 0;
$rawUser = $html->find('p#rawUser')[0]->innertext;
$subscribers_stat = $html->find('p#rawCount')[0]->innertext;

for($i = 0; $i < 10; $i++) {
    $url = 'https://bastet.socialblade.com/youtube/lookup?query='. $rawUser;
    curl_setopt($curl, CURLOPT_URL, $url);
    $str = curl_exec($curl);
    $html = new simple_html_dom();
    $html->load($str);

//    $aHeaderInfo = curl_getinfo($curl);
//    $curlHeaderSize = $aHeaderInfo['header_size'];
//
//    $sBody = trim(mb_substr($str, $curlHeaderSize));
//    $subscribers = $sBody;

    $arr = explode("\n", $str);

    if(count($arr) >= 15) {
        $subscribers = $arr[14];
        break;
    }
}
// !subscribers

$info = array();
$info[0] = array();
$info[1] = array();
$info[2] = array();

$info[0]['subscribers'] = (int)$subscribers;
$info[0]['subscribers_day'] = (int)$subscribers_day;
$info[0]['subscribers_middle_day'] = (int)$subscribers_middle_day;
$info[0]['subscribers_month'] = (int)$subscribers_month;
$info[0]['subscriber_rank'] = (int)$subscriber_rank;
$info[0]['subscribers_stat'] = (int)$subscribers_stat;

if( $subscribers == 0 || $views == 0 || $subscriber_rank == 0 || $subscribers_day == 0 || $subscribers_month == 0 || $subscribers_middle_day == 0 ) {
    $url = 'https://socialblade.com/youtube/channel/' . $socialblade;
    curl_setopt($curl, CURLOPT_URL, $url);
    $str = curl_exec($curl);
    $html = new simple_html_dom();
    $html->load($str);

    $views = preg_replace('~[^-0-9]+~','',$html->find('span#afd-header-views-30d')[0]->innertext);
    $subscriber_rank = preg_replace('~[^-0-9]+~','',$html->find('p#afd-header-subscriber-rank')[0]->innertext);
    $subscribers_month = preg_replace('~[^-0-9]+~','',$html->find('span#afd-header-subs-30d')[0]->innertext);

    $subscribers_day = 0;
    $div = $html->find("a[href=/youtube/user/$socialblade/realtime]",-1);
    if($div)
        $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);

    if($subscribers_day == 0) {
        $str = ucfirst($socialblade);
        $div = $html->find("a[href=/youtube/user/$str/realtime]",-1);
        if($div)
            $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
    }
    else if($subscribers_day == 0) {
        $str = lcfirst($socialblade);
        $div = $html->find("a[href=/youtube/user/$str/realtime]",-1);
        if($div)
            $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
    }
    else if($subscribers_day == 0) {
        $str = mb_convert_case($socialblade, MB_CASE_UPPER, "UTF-8");
        $div = $html->find("a[href=/youtube/user/$str/realtime]",-1);
        if($div)
            $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
    }
    else if($subscribers_day == 0) {
        $str = mb_convert_case($socialblade, MB_CASE_LOWER, "UTF-8");
        $div = $html->find("a[href=/youtube/user/$str/realtime]",-1);
        if($div)
            $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
    }

    $div = $html->find('div[id=averagedailyviews]', 0);
    if($div)
        $subscribers_middle_day = preg_replace('~[^-0-9]+~','',$div->parent()->children(1)->find('span',0)->innertext);


// subscribers
    $url .= '/realtime';
    curl_setopt($curl, CURLOPT_URL, $url);
    $str = curl_exec($curl);
    $html = new simple_html_dom();
    $html->load($str);

    $subscribers = 0;
    $rawUser = $html->find('p#rawUser')[0]->innertext;
    $subscribers_stat = $html->find('p#rawCount')[0]->innertext;

    for($i = 0; $i < 10; $i++){
        $url = 'https://bastet.socialblade.com/youtube/lookup?query='. $rawUser;
        curl_setopt($curl, CURLOPT_URL, $url);
        $str = curl_exec($curl);
        $html = new simple_html_dom();
        $html->load($str);

        $arr = explode("\n", $str);

        if(count($arr) >= 15) {
            $subscribers = $arr[14];
            break;
        }
    }
// !subscribers

    $info[1]['subscribers'] = (int)$subscribers;
    $info[1]['subscribers_day'] = (int)$subscribers_day;
    $info[1]['subscribers_middle_day'] = (int)$subscribers_middle_day;
    $info[1]['subscribers_month'] = (int)$subscribers_month;
    $info[1]['subscriber_rank'] = (int)$subscriber_rank;
    $info[1]['subscribers_stat'] = (int)$subscribers_stat;
}
if( $subscribers == 0 || $views == 0 || $subscriber_rank == 0 || $subscribers_day == 0 || $subscribers_month == 0 || $subscribers_middle_day == 0) {
    $url = 'https://socialblade.com/youtube/search/' . $socialblade;
    curl_setopt($curl, CURLOPT_URL, $url);
    $str = curl_exec($curl);
    $html = new simple_html_dom();
    $html->load($str);

    $url = 'https://socialblade.com' . $html->find('a.ui-red')[0]->href;
    curl_setopt($curl, CURLOPT_URL, $url);
    $str = curl_exec($curl);
    $html = new simple_html_dom();
    $html->load($str);

    $views = preg_replace('~[^-0-9]+~','',$html->find('span#afd-header-views-30d')[0]->innertext);
    $subscriber_rank = preg_replace('~[^-0-9]+~','',$html->find('p#afd-header-subscriber-rank')[0]->innertext);
    $subscribers_month = preg_replace('~[^-0-9]+~','',$html->find('span#afd-header-subs-30d')[0]->innertext);

    $subscribers_day = 0;
    $div = $html->find("a[href=/youtube/channel/$socialblade/realtime]",-1);
    if($div)
        $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);

    if($subscribers_day == 0) {
        $str = ucfirst($socialblade);
        $div = $html->find("a[href=/youtube/channel/$str/realtime]",-1);
        if($div)
            $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
    }
    else if($subscribers_day == 0) {
        $str = lcfirst($socialblade);
        $div = $html->find("a[href=/youtube/channel/$str/realtime]",-1);
        if($div)
            $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
    }
    else if($subscribers_day == 0) {
        $str = mb_convert_case($socialblade, MB_CASE_UPPER, "UTF-8");
        $div = $html->find("a[href=/youtube/channel/$str/realtime]",-1);
        if($div)
            $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
    }
    else if($subscribers_day == 0) {
        $str = mb_convert_case($socialblade, MB_CASE_LOWER, "UTF-8");
        $div = $html->find("a[href=/youtube/channel/$str/realtime]",-1);
        if($div)
            $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
    }

    $div = $html->find('div[id=averagedailyviews]', 0);
    if($div)
        $subscribers_middle_day = preg_replace('~[^-0-9]+~','',$div->parent()->children(1)->find('span',0)->innertext);

    // subscribers
    $url .= '/realtime';
    curl_setopt($curl, CURLOPT_URL, $url);
    $str = curl_exec($curl);
    $html = new simple_html_dom();
    $html->load($str);

    $subscribers = 0;
    $rawUser = $html->find('p#rawUser')[0]->innertext;
    $subscribers_stat = $html->find('p#rawCount')[0]->innertext;

    for($i = 0; $i < 10; $i++){
        $url = 'https://bastet.socialblade.com/youtube/lookup?query='. $rawUser;
        curl_setopt($curl, CURLOPT_URL, $url);
        $str = curl_exec($curl);
        $html = new simple_html_dom();
        $html->load($str);

        $arr = explode("\n", $str);

        if(count($arr) >= 15) {
            $subscribers = $arr[14];
            break;
        }
    }
// !subscribers

    $info[2]['subscribers'] = (int)$subscribers;
    $info[2]['subscribers_day'] = (int)$subscribers_day;
    $info[2]['subscribers_middle_day'] = (int)$subscribers_middle_day;
    $info[2]['subscribers_month'] = (int)$subscribers_month;
    $info[2]['subscriber_rank'] = (int)$subscriber_rank;
    $info[2]['subscribers_stat'] = (int)$subscribers_stat;
}
if( $subscribers == 0 || $views == 0 || $subscriber_rank == 0 || $subscribers_day == 0 || $subscribers_month == 0 || $subscribers_middle_day == 0 ) {
    $url = 'https://socialblade.com/youtube/channel/' . $socialblade;
    curl_setopt($curl, CURLOPT_URL, $url);
    $str = curl_exec($curl);
    $html = new simple_html_dom();
    $html->load($str);

    $views = preg_replace('~[^-0-9]+~','',$html->find('span#afd-header-views-30d')[0]->innertext);
    $subscriber_rank = preg_replace('~[^-0-9]+~','',$html->find('p#afd-header-subscriber-rank')[0]->innertext);
    $subscribers_month = preg_replace('~[^-0-9]+~','',$html->find('span#afd-header-subs-30d')[0]->innertext);

    $subscribers_day = 0;
    $div = $html->find("a[href=/youtube/channel/$socialblade/realtime]",-1);
    if($div)
        $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);

    if($subscribers_day == 0) {
        $str = ucfirst($socialblade);
        $div = $html->find("a[href=/youtube/channel/$str/realtime]",-1);
        if($div)
            $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
    }
    else if($subscribers_day == 0) {
        $str = lcfirst($socialblade);
        $div = $html->find("a[href=/youtube/channel/$str/realtime]",-1);
        if($div)
            $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
    }
    else if($subscribers_day == 0) {
        $str = mb_convert_case($socialblade, MB_CASE_UPPER, "UTF-8");
        $div = $html->find("a[href=/youtube/channel/$str/realtime]",-1);
        if($div)
            $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
    }
    else if($subscribers_day == 0) {
        $str = mb_convert_case($socialblade, MB_CASE_LOWER, "UTF-8");
        $div = $html->find("a[href=/youtube/channel/$str/realtime]",-1);
        if($div)
            $subscribers_day = preg_replace('~[^-0-9]+~','',$div->parent()->parent()->children(0)->find('span',0)->innertext);
    }

    $div = $html->find('div[id=averagedailyviews]', 0);
    if($div)
        $subscribers_middle_day = preg_replace('~[^-0-9]+~','',$div->parent()->children(1)->find('span',0)->innertext);


// subscribers
    $url .= '/realtime';
    curl_setopt($curl, CURLOPT_URL, $url);
    $str = curl_exec($curl);
    $html = new simple_html_dom();
    $html->load($str);

    $subscribers = 0;
    $rawUser = $html->find('p#rawUser')[0]->innertext;
    $subscribers_stat = $html->find('p#rawCount')[0]->innertext;

    for($i = 0; $i < 10; $i++){
        $url = 'https://bastet.socialblade.com/youtube/lookup?query='. $rawUser;
        curl_setopt($curl, CURLOPT_URL, $url);
        $str = curl_exec($curl);
        $html = new simple_html_dom();
        $html->load($str);

        $arr = explode("\n", $str);

        if(count($arr) >= 15) {
            $subscribers = $arr[14];
            break;
        }
    }
// !subscribers

    $info[3]['subscribers'] = (int)$subscribers;
    $info[3]['subscribers_day'] = (int)$subscribers_day;
    $info[3]['subscribers_middle_day'] = (int)$subscribers_middle_day;
    $info[3]['subscribers_month'] = (int)$subscribers_month;
    $info[3]['subscriber_rank'] = (int)$subscriber_rank;
    $info[3]['subscribers_stat'] = (int)$subscribers_stat;
}


$url = 'https://www.patreon.com/' . $patreon;
curl_setopt($curl, CURLOPT_URL, $url);
$str = curl_exec($curl);
$html = new simple_html_dom();
$html->load($str);
$donat = preg_replace('~[^-0-9]+~','',$html->find('h6')[1]->innertext);

echo '<!doctype html>
<html>
<head>
    <title>Some value</title>

    <meta charset="utf-8" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
<div>';

// diff - разница общего числа подписчиков с вкладки LIVE и необновлённого
// ### Output's format ###
// общее число подписчиков(с вкладки LIVE);
// подписчики за день(за "сегодня");
// средние подписчики за день(daily Averages);
// подписчики за месяц (Last 30 Days);
// место в рейтинге(subscriber rank);
// месячные донаты(это с Патреон)

if($info[0]['subscribers'] && $subscribers == 0)
    $subscribers = $info[0]['subscribers'];
else if($info[1]['subscribers'] && $subscribers == 0)
    $subscribers = $info[1]['subscribers'];
else if($info[2]['subscribers'] && $subscribers == 0)
    $subscribers = $info[2]['subscribers'];

if($info[0]['subscribers_day'] && $subscribers_day == 0)
    $subscribers_day = $info[0]['subscribers_day'];
else if($info[1]['subscribers_day'] && $subscribers_day == 0)
    $subscribers_day = $info[1]['subscribers_day'];
else if($info[2]['subscribers_day'] && $subscribers_day == 0)
    $subscribers_day = $info[2]['subscribers_day'];

if($info[0]['subscribers_middle_day'] && $subscribers_middle_day == 0)
    $subscribers_middle_day = $info[0]['subscribers_middle_day'];
else if($info[1]['subscribers_middle_day'] && $subscribers_middle_day == 0)
    $subscribers_middle_day = $info[1]['subscribers_middle_day'];
else if($info[2]['subscribers_middle_day'] && $subscribers_middle_day == 0)
    $subscribers_middle_day = $info[2]['subscribers_middle_day'];

if($info[0]['subscribers_month'] && $subscribers_month == 0)
    $subscribers_month = $info[0]['subscribers_month'];
else if($info[1]['subscribers_month'] && $subscribers_month == 0)
    $subscribers_month = $info[1]['subscribers_month'];
else if($info[2]['subscribers_month'] && $subscribers_month == 0)
    $subscribers_month = $info[2]['subscribers_month'];

if($info[0]['subscriber_rank'] && $subscriber_rank == 0)
    $subscriber_rank = $info[0]['subscriber_rank'];
else if($info[1]['subscriber_rank'] && $subscriber_rank == 0)
    $subscriber_rank = $info[1]['subscriber_rank'];
else if($info[2]['subscriber_rank'] && $subscriber_rank == 0)
    $subscriber_rank = $info[2]['subscriber_rank'];

if($info[0]['subscribers_stat'] && $subscribers_stat == 0)
    $subscribers_stat = $info[0]['subscribers_stat'];
else if($info[1]['subscribers_stat'] && $subscribers_stat == 0)
    $subscribers_stat = $info[1]['subscribers_stat'];
else if($info[2]['subscribers_stat'] && $subscribers_stat == 0)
    $subscribers_stat = $info[2]['subscribers_stat'];

$diff = 0;
if((int)$subscribers > 0)
    $diff = (int)$subscribers - (int)$subscribers_stat;

if((int)$subscribers < 0 ) echo '-';
else echo '+';
echo str_pad(abs($subscribers), 8, '0', STR_PAD_LEFT) . ';';

if((int)$subscribers_day + $diff < 0 ) echo '-';
else echo '+';
echo str_pad(abs($subscribers_day + $diff), 8, '0', STR_PAD_LEFT) . ';';

if((int)$subscribers_middle_day < 0 ) echo '-';
else echo '+';
echo str_pad(abs($subscribers_middle_day), 8, '0', STR_PAD_LEFT) . ';';

if((int)$subscribers_month  + $diff < 0 ) echo '-';
else echo '+';
echo str_pad(abs($subscribers_month + $diff), 8, '0', STR_PAD_LEFT) . ';';

if((int)$subscriber_rank < 0 ) echo '-';
else echo '+';
echo str_pad(abs($subscriber_rank), 8, '0', STR_PAD_LEFT) . ';';

if((int)$donat < 0 ) echo '-';
else echo '+';
echo str_pad(abs($donat), 8, '0', STR_PAD_LEFT);

echo '
</div>
</body>
</html>';
curl_close($curl);
?>