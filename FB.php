<?php

include('Curl.php');

class FB{

	public $curl;

	function __construct($curl, $TxtAccount, $TxtPassword)
	{
		$this->TxtAccount  = $TxtAccount;
		$this->TxtPassword = $TxtPassword;

		$this->curl = $curl;
		$this->curl->cookie(md5($this->TxtAccount.$this->TxtPassword).'.txt');
	}

	public function login()
	{
		$link = 'https://m.facebook.com';

		$this->curl->config($link)->follow()->get();

		$result = $this->curl->getResult();

		if(
			strpos($result, "Logout") !== FALSE ||
			strpos($result, "See More Stories") !== FALSE ||
			strpos($result, "Xem các Tin bài Khác") !== FALSE ||
			strpos($result, "Tìm bạn bè") !== FALSE ||
			strpos($result, "Tìm kiếm bạn bè") !== FALSE)
		{
			return true; // da login roi
		}

		// chua login,  login ngay
		preg_match("/action=\"(.*?)\"/", $result, $matches);

		$link = $matches[1];

		preg_match_all("/<input(.*?)name=\"(.*?)\"(.*?)value=\"(.*?)\"/", $result, $matches);

		$param = array();

		foreach($matches[2] as $k => $value)
		{
			$param[$value] = $matches[4][$k];
		}

		$param['email'] = $this->TxtAccount;
		$param['pass']  = $this->TxtPassword;
		$param['login']	= "Đăng nhập";

		$this->curl->config($link, $param)->follow()->get();

		$result = $this->curl->getResult();

		if(strpos($result, "TĂ¹y chá»n khĂ¡c") !== FALSE || strpos($result, "Nhá»› trĂ¬nh duyá»‡t") !== FALSE)
		{
			throw new Exception("RESULT:00@");
		}

		throw new Exception("RESULT:999@");
	}

	public function peopleWhoLike($fanpage_id)
	{
		$listsEmail = [];
		$link = 'https://www.facebook.com/plugins/fan.php?connections=100&id='.$fanpage_id;

		$this->curl->config($link)->follow()->get();
		$result = $this->curl->getResult();

		preg_match_all("/title=\"(.*?)\" href\=\"https\:\/\/www\.facebook\.com\/(.*?)\"/", $result, $matches);

		$listUsers = $matches[2];

		foreach($listUsers as $k => $name)
		{
			if(strpos($name, '?') !== FALSE)
			{

			}else{
				$email = $name.'@facebook.com';

				if(!isset($listsEmail[$email]))
				{
					echo $email.'<br>';
				}

				$listsEmail[$email] = $email;
			}
		}

		// var_dump($listsEmail);

		// die($result);
	}

	public function getNodeTime($linkFanpage, $start)
	{
		$link = 'https://m.facebook.com/profile.php?page='.$start.'&id='.$linkFanpage.'&v=timeline';
		$this->curl->config($link)->follow()->get();

		$result = $this->curl->getResult();

		@preg_match_all("/<div class=\"aps\">(.*?)\?(.*?)\">(.*?)<\/div>/", $result, $matches);

		if(!isset($matches[2]) || count($matches[2]) == 0)
		{
			throw new Exception("RESULT:10@Node is not found");
		}

		$temp = array();

		foreach($matches[2] as $index => $node)
		{
			$node = preg_replace("/\&amp\;page\=(\d+)/", "", $node);

			$name = str_replace('</a>', '', $matches[3][$index]);
			// $temp[] = urlencode($node);
			$temp[] = implode('***', array('code' => $node, 'name' => $name));
			// parse_str($node, $array);

			// $temp[] = implode('$$$', $array);
		}

		$str = "RESULT:00@".implode($temp, '###');

		throw new Exception($str);
	}

	public function getLikesId($linkFanpage, $start, $node)
	{
		if($node)
		{
			$node = str_replace('&amp;', '&', $node);
		}

		$link = 'https://m.facebook.com/profile.php?page='.$start.'&id='.$linkFanpage.'&v=timeline&'.$node;

		$this->curl->config($link)->follow()->get();

		$result = $this->curl->getResult();

		@preg_match_all("/sharer.php\?sid=(\d+)\&amp\;/", $result, $matches);

		if(!isset($matches[1]))
		{
			throw new Exception("RESULT:10@Sharer id is not found");
		}

		if(count($matches[1]) == 0)
		{
			throw new Exception("RESULT:11@The scan has been finished.");
		}

		$str = "RESULT:00@".implode($matches[1], '###');

		throw new Exception($str);
	}

	public function getLikes($likeID, $start)
	{
		$start = ($start - 1) * 30;
		$link  = 'https://m.facebook.com/browse/likes/?id='.$likeID.'&start='.$start;

		$this->curl->config($link)->follow()->get();

		$result = $this->curl->getResult();

		@preg_match_all("/friends\/add_friend\.php\?id\=(\d+)\&amp\;/", $result, $matches);

		if(!isset($matches[1]))
		{
			throw new Exception("RESULT:10@Friend id is not found");
		}

		if(count($matches[1]) == 0)
		{
			throw new Exception("RESULT:11@The scan has been finished.");
		}

		// echo '<h1>From '.$start.' to '.($start + 30).'</h1>';
		// echo implode($matches[1], '<br>');

		$str = "RESULT:00@".implode($matches[1], '###');

		throw new Exception($str);
	}

	public function getUIDsInGroup($groupID, $page)
	{
		$start = $page * 30 - 30;
		$link  = 'https://m.facebook.com/browse/group/members/?id='.$groupID.'&start='.$start;

		$this->curl->config($link)->follow()->get();

		$result = $this->curl->getResult();

		@preg_match_all("/member_(\d+)/", $result, $matches);

		if(!isset($matches[1]))
		{
			throw new Exception("RESULT:10@Friend id is not found");
		}

		if(count($matches[1]) == 0)
		{
			throw new Exception("RESULT:11@The scan has been finished.");
		}

		// $str = "RESULT:00@".implode($matches[1], '###');
		echo '<h1>From '.$start.' to '.($start + 30).'</h1>';
		echo implode($matches[1], '<br>');

		// throw new Exception($str);
	}

	public function invite($invited, $personal)
	{
		$link = 'https://m.facebook.com/invite.php';

		$this->curl->config($link)->follow()->get();

		$result = $this->curl->getResult();

		preg_match_all("/<input(.*?)name=\"(.*?)\"(.*?)value=\"(.*?)\"/", $result, $matches);

		$param = array();

		foreach($matches[2] as $k => $value)
		{
			$param[$value] = $matches[4][$k];
		}

		$param['invited'] 	= $invited;
		$param['personal'] 	= $personal;
		$param['invite'] 	= "Mời";

		$this->curl->config($link, $param)->follow()->get();

		$result = $this->curl->getResult();

		var_dump($result);
	}

	public function send($TxtNumber, $TxtMessage)
	{
		// $link = 'https://m.facebook.com/tokenizer/single/?mode=compose&curry={%22folder%22%3A%22inbox%22%2C%22query%22%3Anull}&refid=11';

		// $this->curl->config($link)->follow()->get();

		// $result = $this->curl->getResult();

		// preg_match_all("/<input(.*?)name=\"(.*?)\"(.*?)value=\"(.*?)\"/", $result, $matches);

		// $param = array();

		// foreach($matches[2] as $k => $value)
		// {
		// 	$param[$value] = $matches[4][$k];
		// }

		// // $param['query'] = '+'.$TxtNumber;/
		// $param['query'] = $TxtNumber;

		// unset($param['search']);

		// $link = "https://m.facebook.com/tokenizer/single/?mode=compose&mustBeInitialized";

		// $this->curl->config($link, $param)->follow()->get();

		// $result = $this->curl->getResult();

		// die($result);

		// preg_match("/<a href=\"\/messages\/compose\/\?ids\%5B(\d+)\%5D=(\d+)\"><span>(.*?)<\/span><\/a>/", $result, $matches);

		// if(!isset($matches[2]))
		// {
		// 	throw new Exception("RESULT:01@".$TxtNumber);
		// }

		// $ids  = $matches[2];
		// $name = strip_tags($matches[3]);
		$ids = $TxtNumber;
		$name = uniqid();

		### NEW
		$link = "https://m.facebook.com/messages/compose/?ids%5B".$ids."%5D=".$ids."";

		$this->curl->config($link)->follow()->get();

		$result = $this->curl->getResult();

		preg_match_all("/<input(.*?)name=\"(.*?)\"(.*?)value=\"(.*?)\"/", $result, $matches);

		$param = array();

		foreach($matches[2] as $k => $value)
		{
			$param[$value] = $matches[4][$k];
		}

		$link2 = "https://m.facebook.com/messages/send/?icm=1";

		$param['ids['.$ids.']'] 		= $ids;
		$param['text_ids['.$ids.']'] 	= $name;
		$param['body'] 					= $TxtMessage;
		$param['Send'] 					= 'Gửi';

		unset($param['tid']);
		unset($param['search']);

		$this->curl->referer($link);
		$this->curl->config($link2, $param)->follow()->get();

		$result = $this->curl->getResult();

		if(strpos($result, 'name="ids['.$ids.']" value="'.$ids.'"') !== FALSE
			||
		   strpos($result, $TxtMessage) !== FALSE)
		{
			throw new Exception("RESULT:00@");
		}

		throw new Exception("RESULT:999@".base64_encode($result));
	}
}