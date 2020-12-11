<?php

// include 'plugins/simplehtmldom/simple_html_dom.php';

$url = 'https://r3.vfsglobal.com/LithuaniaAppt/Account/RegisteredLogin?q=shSA0YnE4pLF9Xzwon/x/BbG1ynWEGIgVjaroQX6qrvIiR/QAez5H/lcf/Fbhh4KdEMrQPxf2kpKXIuZfrW4GQQwyJe7endrdFHlvW/ZIqU=';

$autorization = new Autorization();

$cookie       = $autorization->getCookie();

if ($cookie) {

    /* Если куки есть, отправка формы */

    $autorization->executeAutorization($url);

}


class Autorization {

    private $headers = [
        'User_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.66 Safari/537.36',
        // 'Accept' => 'image/gif, image/x-xbitmap, image/jpeg, image/pjpeg',
        // 'Accept-Language' => 'ru,zh-cn;q=0.7,zh;q=0.3',
    ];

    private $custom_headers = [
        'sec-fetch-dest:document',
        'sec-fetch-mode:navigate',
        'sec-fetch-site:none',
        'sec-fetch-user:?1',
        'upgrade-insecure-requests:1',
    ];

    private $url_for_cookie = 'https://r3.vfsglobal.com/LithuaniaAppt/Account';

    public function getCookie() {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url_for_cookie);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->headers['user_agent']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->custom_headers);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'] . "/COOKIE.txt");
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo 'Ошибка запроса curl: ' . curl_error($ch);
            return false;
        } else {
            return true;
        }
        curl_close($ch);
    }

    public function getRequestVerificToken($page) {

    	preg_match("/name=\"__RequestVerificationToken\".*?value=\".*?\"/", $page, $token);
    	$token = $token[0];

    	$token = substr($token, 55, -1);

    	return $token;
    }

    public function getCaptchaDeText($page)
    {
    	preg_match("/name=\"CaptchaDeText\".*?value=\".*?\"/", $page, $getCaptchaDeText);
    	$getCaptchaDeText = $getCaptchaDeText[0];

    	$getCaptchaDeText = substr($getCaptchaDeText, 42, -1);

    	return $getCaptchaDeText;
    }

    public function executeAutorization($url) 
    {

    	$curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'] . "/COOKIE.txt");
	    curl_setopt($curl, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'] . "/COOKIE.txt");
	    curl_setopt($curl, CURLOPT_POST, 0);
	    curl_setopt($curl, CURLOPT_USERAGENT, $this->headers['user_agent']);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
	    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    $page_autorize = curl_exec($curl);	 

	    curl_setopt($curl, CURLOPT_URL, $url);

	    preg_match("/\/LithuaniaAppt\/DefaultCaptcha\/Generate.*?\"/", $page_autorize, $output);

	    $url_captcha = 'https://r3.vfsglobal.com' . substr($output[0], 0, -1);

	    // echo $url_captcha . "\n";

    	$head = [
    		'accept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
    		'accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
    		'referer: https://r3.vfsglobal.com/LithuaniaAppt/Account',
    		'sec-fetch-dest: image',
    		'sec-fetch-mode: no-cors',
    		'sec-fetch-site: same-origin',
    		'accept-encoding: gzip, deflate, br'
    	];

	    curl_setopt($curl, CURLOPT_URL, $url_captcha);
	    curl_setopt($curl, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'] . "/COOKIE.txt");
	    curl_setopt($curl, CURLOPT_POST, 0);
	    curl_setopt($curl, CURLOPT_USERAGENT, $this->headers['user_agent']);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $head);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$image_base64 = curl_exec($curl);
		$image_base64 = base64_encode(gzdecode($image_base64));

	    $captcha 	  = new Captcha();
	    $captcha_id   = $captcha->sendCaptcha($image_base64);
		$captcha_resp = $captcha->getResponseCaptcha($captcha_id);
		$captcha_resp = strtoupper($captcha_resp);
		echo $captcha_resp;
	    $token = $this->getRequestVerificToken($page_autorize);
	    $captcha_DeText_form = $this->getCaptchaDeText($page_autorize);

		/* Поля для авторизации */
		
		//admin1@mkkrsk.ru
// Supermega1!

		$post_data = [
			'Mission' => '',
			'Country' => '',
			'Center'  => '',
		    'EmailId'  => 'admin1@mkkrsk.ru',
		    'Password'  => 'Supermega1!',
		    'ConfirmPassword'  => 'Supermega1!',
		    'CaptchaInputText' => $captcha_resp,
		    'CaptchaDeText'    => $captcha_DeText_form,
		    '__RequestVerificationToken' => $token,
		    'IsGoogleCaptchaEnabled'     => False,
		    'reCaptchaURL'     => 'https://www.google.com/recaptcha/api/siteverify?secret={0}&response={1}',
		    'reCaptchaPublicKey'     => '6Ld-Kg8UAAAAAK6U2Ur94LX8-Agew_jk1pQ3meJ1', 
		];

		/////////////// Не понятная кодировка $image_base64 /////////////
		// header('Content-type: image/gif');
		// echo '<img alt="Embedded Image" src="data:image/png;base64,' . $image_base64 . '">';
		// echo $captcha_resp;exit;
		// echo $image_base64;exit;
	    // echo gzdecode($image_base64);
	   	// echo $captcha;
	   	// echo $token;
	   	// echo $captcha_DeText_form;

		/* Отправка постом */
		$post_query = http_build_query($post_data);

		$head = [
			"accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
			"accept-encoding: gzip, deflate, br",
			"accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
			"cache-control: max-age=0",
			"content-length: " . strlen($post_query),
			"content-type: application/x-www-form-urlencoded",
			"origin: https://r3.vfsglobal.com",
			"referer: https://r3.vfsglobal.com/LithuaniaAppt/Account/RegisterUser?Length=7",
			'sec-fetch-dest: document',
    		'sec-fetch-mode: navigate',
    		'sec-fetch-site: same-origin',
			'accept-encoding: gzip, deflate, br',
			"upgrade-insecure-requests: 1",
		];

		$url_post = 'https://r3.vfsglobal.com/LithuaniaAppt/?q=shSA0YnE4pLF9Xzwon%2Fx%2FBbG1ynWEGIgVjaroQX6qrvIiR%2FQAez5H%2Flcf%2FFbhh4KdEMrQPxf2kpKXIuZfrW4GQQwyJe7endrdFHlvW%2FZIqU%3D';

		curl_setopt($curl, CURLOPT_URL, $url_post);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'] . "/COOKIE.txt");
        curl_setopt($curl, CURLOPT_USERAGENT, $this->headers['user_agent']);
        curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_query);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $head);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'] . "/COOKIE.txt");

        /* Обязательно следуем редиректам */

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        $result_post = curl_exec($curl);

		// echo gzdecode($result_post);

	    if (curl_errno($curl)) {
	        echo 'Ошибка запроса curl: ' . curl_error($curl);
	        // return false;
	    } else {
			echo 'Авторизация';
	        // return true;
		}


		$url_home = 'https://r3.vfsglobal.com/LithuaniaAppt/Home/Index';

		$h = [
			'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
			'accept-encoding: gzip, deflate, br',
			'cache-control: max-age=0',
			'accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
			'dnt: 1',
			'sec-fetch-dest: document',
    		'sec-fetch-mode: navigate',
			'sec-fetch-site: same-origin',
			'sec-fetch-user: ?1',
			'upgrade-insecure-requests: 1',
		];

		curl_setopt($curl, CURLOPT_URL, $url_home);
		curl_setopt($curl, CURLOPT_POST, 0);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'] . "/COOKIE.txt");
		curl_setopt($curl, CURLOPT_USERAGENT, $this->headers['user_agent']);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $h);
		curl_setopt($curl, CURLOPT_REFERER, 'https://r3.vfsglobal.com/LithuaniaAppt/?q=shSA0YnE4pLF9Xzwon%2Fx%2FBbG1ynWEGIgVjaroQX6qrvIiR%2FQAez5H%2Flcf%2FFbhh4KdEMrQPxf2kpKXIuZfrW4GQQwyJe7endrdFHlvW%2FZIqU%3D');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$res_get = curl_exec($curl);
		echo gzdecode($res_get);

	    if (curl_errno($curl)) {
	        echo 'Ошибка запроса curl к index"у: ' . curl_error($curl);
	        return false;
	    } else {
			echo 'Index the end';
	        return true;
		}

		curl_close($curl);
    }

    public function sendFormAutorization($url, $post_data)
    {
        /* отправка постом*/
    }
}

class Captcha {

	private $img_encoded;
	private $postdata = [
        'method'    => 'base64',
        'key'       => 'dcd74db32efdb8f07bc83328509f8b49',
        // 'body'      => $img_encoded,
        // 'json'      => 1,
    ];

    // public function __construct($img_encoded)
    // {
    // 	$this->img_encoded = $img_encoded;
    // }

	public function sendCaptcha($img_encoded) {

	    /** 
	     * @img_encoded - image format base_64
	     * Отправка картинки каптчи в rucaptcha */

	    $this->postdata['body'] = $img_encoded;

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, 'https://rucaptcha.com/in.php');
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postdata);
	    $result = curl_exec($ch);

	    if (curl_errno($ch)) {
	        echo "CURL returned error: " . curl_error($ch) . "\n";
	        return false;
	    } 
	    
	    curl_close($ch);
	    
	    // echo $result; // 50161836057
	    
	    if (!empty($result)) {
	        $captcha_id = explode('|', $result)[1];

	    	echo 'Captcha отправлена: ID - ' . $captcha_id . PHP_EOL . "(result = $result)";
	    } else {
	    	echo 'Captcha не отправлена';
	    }

	    return $captcha_id;
	}

    public function getResponseCaptcha($captcha_id) {

    	$url = "https://rucaptcha.com/res.php?key=" . $this->postdata['key'] . "&action=get&id=$captcha_id";

    	/* Получение ответа каптчи от сервиса */

        for($i = 0; $i < 10; $i++) {

		    sleep(3);
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

		    $result  = curl_exec($ch);  
		    curl_close($ch);

		    $res_pos = stripos($result, 'OK');
		    
		    if ($res_pos !== FALSE) {
		        $captcha = explode('|', $result)[1];
		        return $captcha;
		    }
		    echo $result . PHP_EOL;
		}
    }
}