<?php
    date_default_timezone_set("Asia/Bangkok");
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        echo 'Cannot GET /';
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {      
        $Api = isset($_GET['api']) ? $_GET['api'] : "";
	    $deviceid = isset($_POST['deviceid']) ? $_POST['deviceid'] : "";
        $pin = isset($_POST['pin']) ? $_POST['pin'] : "";
        $accnumber = isset($_POST['accnumber']) ? $_POST['accnumber'] : "";

        if(empty($Api) || empty($deviceid) || empty($pin) || empty($accnumber))
            echo 'กรุณากรอกข้อมูลให้ครบ!';
        else
        {
            $SCB = new SCBEasyAPI(); 
            $SCB->setAccount($deviceid, $pin, $accnumber);
            if($SCB->login()){            
                if ($Api == 'getbalance') {
                    $resp = $SCB->getBalance();
                    $json = json_encode($resp, JSON_UNESCAPED_UNICODE);
                    echo $json;
                } elseif ($Api == 'checkslip') {
                    $barcode = isset($_POST['barcode']) ? $_POST['barcode'] : "";
                    if(empty($barcode))
                        echo 'กรุณากรอกข้อมูลให้ครบ!';
                    else
                    {
                        $resp = $SCB->getcheckslip($barcode);
                        $json = json_encode($resp, JSON_UNESCAPED_UNICODE);
                        echo $json;
                    }
                } elseif ($Api == 'getname') {
                    $bank = isset($_POST['bank']) ? $_POST['bank'] : "";
                    $transferTo = isset($_POST['transferTo']) ? $_POST['transferTo'] : "";
                    $amount = isset($_POST['amount']) ? $_POST['amount'] : "";
                    if(empty($bank) || empty($transferTo) || empty($amount))
                        echo 'กรุณากรอกข้อมูลให้ครบ!';
                    else
                    {
                        $resp = $SCB->getnametransfer($bank, $transferTo, number_format($amount,0,"","")); 
                        $json = json_encode($resp, JSON_UNESCAPED_UNICODE);
                        echo $json;
                    }
                } elseif ($Api == 'transfer') {
                    $bank = isset($_POST['bank']) ? $_POST['bank'] : "";
                    $transferTo = isset($_POST['transferTo']) ? $_POST['transferTo'] : "";
                    $amount = isset($_POST['amount']) ? $_POST['amount'] : "";
                    if(empty($bank) || empty($transferTo) || empty($amount))
                        echo 'กรุณากรอกข้อมูลให้ครบ!';
                    else
                    {
                        $resp = $SCB->transfer($bank, $transferTo, number_format($amount,0,"","")); // โอน Bypass 50k 200k
                        $json = json_encode($resp, JSON_UNESCAPED_UNICODE);
                        echo $json;
                    }
                } //getnametransfer($bank, $transferTo, $amount)
            }  
        } 
    } else {
        echo 'Error!';
    }

    class SCBEasyAPI
    {        
        public $availableBalance = 0;
        private $apiUrl = 'https://fasteasy.scbeasy.com:8443';
        private $apiHash = '';
        private $userAgent = 'Android/13;FastEasy/3.76.0/7940';
        private $deviceId = '';
        private $pin = '';
        private $refreshToken = '';
        private $accountNumber = '';
        private $fileToken = '';
        private $fileTokenimg = '';
        private $scbVersion = '3.76.0/7940';
        private $username = 'user-lu9322806-region-th:ck461V';
        private $proxys = 'as.zgapnf0k.lunaproxy.net:32233';
        
        private $titles = [
        'น.ส.',
        'นางสาว',
        'นาง',
        'นาย',
        'นพ.',
        'พญ.',
        'พล.อ.',
        'พล.ท.',
        'พล.ต.',
        'พ.อ.',
        'พ.ท.',
        'พ.ต.',
        'ร.อ.',
        'ร.ท.',
        'ร.ต.',
        'จ.ส.อ.',
        'จ.ส.ท.',
        'จ.ส.ต.',
        'ส.อ.',
        'ส.ท.',
        'ส.ต.',
        'พลฯ',
        'นนร.',
        'พล.ร.อ.',
        'พล.ร.ท.',
        'พล.ร.ต.',
        'พ.จ.อ.',
        'พ.จ.ท.',
        'พ.จ.ต.',
        'จ.อ.',
        'จ.ท.',
        'จ.ต.',
        'นนร.',
        'พล.อ.อ.',
        'พล.อ.ท.',
        'พล.อ.ต.',
        'น.อ.',
        'น.ท.',
        'น.ต.',
        'ร.อ.',
        'ร.ท.',
        'ร.ต.',
        'พ.อ.อ.',
        'พ.อ.ท.',
        'พ.อ.ต.',
        'จ.อ.',
        'จ.ท.',
        'จ.ต.',
        'พลฯ',
        'นนอ.',
        'นจอ.',
        'พล.ต.อ.',
        'พล.ต.ท.',
        'พล.ต.ต.',
        'พ.ต.อ.',
        'พ.ต.ท.',
        'พ.ต.ต.',
        'ร.ต.อ.',
        'ร.ต.ท.',
        'ร.ต.ต.',
        'ด.ต.',
        'ส.ต.อ.',
        'ส.ต.ท.',
        'ส.ต.ต.',
        'ว่าที่ ร.ต.',
        'ว่าที่ ร.ต.หญิง'
        ];
        
        public function setAccount($deviceId, $pin, $accountNumber)
        {
            $this->deviceId = $deviceId;
            $this->pin = $pin;
            $this->accountNumber = $accountNumber;
            $this->fileToken = "scb-access-token-" . $accountNumber . ".txt";
            $this->fileTokenimg = "img/scb-access-token-" . $accountNumber . ".txt";
            $this->scbVersion = '3.76.0/7940';
        }
        
        private function Curl($method , $url , $header , $data , $cookie = false){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, "Android/11;FastEasy/3.76.0/7940");
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            
            if ($cookie) {
                curl_setopt($ch, CURLOPT_COOKIESESSION, true);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
            }
            
            return curl_exec($ch);
        }
        
        private function CurlForAuth($method , $url , $header , $data = []){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 0); //waiting response timeout in seconds
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); //waiting connection timeout in seconds
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                // Log::error("SCB - AUTH : ".$error_msg);
                echo $error_msg;
                exit; 
            }
            
            curl_close($ch);
            return $response;
        }
        
        public function login() {
            $url = 'https://fasteasy.scbeasy.com:8443/v3/login/preloadandresumecheck' ;
            $headers = array(
            'Accept-Language: th',
			'scb-channel: APP',
			'user-agent: '.$this->userAgent,
			'Content-Type: application/json; charset=UTF-8',
			'Host: fasteasy.scbeasy.com:8443',
			'Connection: close'
            );
            $data = json_encode(['deviceId'  => $this->deviceId , 'isLoadGeneralConsent' => 0, 'jailbreak' => 0 , 'tilesVersion'  => 69 , 'userMode'  => 'INDIVIDUAL']);
            $res = $this->CurlForAuth('POST' , $url , $headers , $data);
            preg_match_all('/(?<=Api-Auth: ).+/', $res , $Auth);
            $Auth = $Auth[0][0] ;
            
            
            if($Auth == ""){
                // echo "SCB - AUTH : ".json_encode($res);
                return false ;
            }
            
            $url = 'https://fasteasy.scbeasy.com/isprint/soap/preAuth' ;
            $headers = array(
            'Api-Auth: '.$Auth,
			'Content-Type: application/json'
            );
            $data = json_encode(["loginModuleId" => "PseudoFE"]);
            $res = $this->Curl('POST' , $url , $headers , $data);
            $data = json_decode($res , true);
            //print_r($data);
            
            $hashType = $data['e2ee']['pseudoOaepHashAlgo'];
            $Sid = $data['e2ee']['pseudoSid'];
            $ServerRandom = $data['e2ee']['pseudoRandom'];
            $pubKey = $data['e2ee']['pseudoPubKey'];
            
            
            
            // ------------------- encrypt pin ------------------------------
            //$url = 'http://heng.kaemsg.com:3000/pin/encrypt';
            $url = 'https://encryptscb.apigetdevice.com/pin/encrypt';
            $headers = array(
            "Content-Type: application/x-www-form-urlencoded"
            );

            $data = http_build_query([
            'Sid'       => $Sid , 
            'ServerRandom' => $ServerRandom , 
            'pubKey'    => $pubKey , 
            'pin'       => $this->pin , 
            'hashType'  => $hashType
            ]);
            $res = $this->Curl('POST' , $url , $headers , $data);
            
            // -------------------- Login ---------------------------------
            $url = 'https://fasteasy.scbeasy.com/v1/fasteasy-login';
            $headers = array(
            'Api-Auth: '.$Auth,
			'Content-Type: application/json' ,
			'user-agent: Android/11;FastEasy/3.76.0/7940',
            );
            $data = json_encode([
            'deviceId'  => $this->deviceId , 
            'pseudoPin' => $res , 
            'pseudoSid' => $Sid
            ]);
            $res = $this->CurlForAuth('POST' , $url , $headers , $data);
            preg_match_all('/(?<=Api-Auth:).+/', $res , $Auth_result);
            // print_r($res);
            $Auth1 = $Auth_result[0][0];
            
            
            if ($Auth1 =="") {
                return false ;
            }
            
            $accessToken = trim($Auth1);
            file_put_contents($this->fileToken, $accessToken);
            file_put_contents($this->fileTokenimg, $this->deviceId . '|' . $this->pin);
            $this->getBalance();  
            return true;
        }
        
        public function loginOld()
        {           
            if (!$this->isLoggedIn()) {               
                $ch = curl_init();               
                curl_setopt($ch, CURLOPT_URL, "{$this->apiUrl}/v1/login/refresh");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);         
                $headers = array();
                $headers[] = 'User-Agent: Android/10;FastEasy/' . $this->scbVersion;
                $headers[] = 'Accept-Language: th';
                $headers[] = 'Content-Type: application/json';
                $headers[] = "Api-Refresh: $this->refreshToken";
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, '{"deviceId": "' . $this->deviceId . '"}');
                
                $result = json_decode(curl_exec($ch));
                $accessToken = $result->data->access_token;
                file_put_contents($this->fileToken, $accessToken);
                
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                curl_close($ch);
                
                if ($result->status->code == 1000) {
                    $this->isLoggedIn();
                    return true;
                    } else {
                    return false;
                }
            }           
            return true;
        }
        
        public function getBalance()
        {
            
            // $accessToken = file_get_contents("scb-access-token.txt");
            $accessToken = file_get_contents($this->fileToken);
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, "{$this->apiUrl}/v2/deposits/casa/details");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            
            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = 'User-Agent: Android/10;FastEasy/' . $this->scbVersion;
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{"accountNo": "' . $this->accountNumber . '"}');
            
            $result = json_decode(curl_exec($ch), true);
            
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            return $result;
            
        }
        
        /* -- start bulktransferprofiles --*/
        
        public function addgroup($groupName)
        {
            $accessToken = file_get_contents($this->fileToken);
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, 'https://fasteasy.scbeasy.com:8443/v1/bulktransferprofiles/group');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"groupName\":\"".$groupName."\"}");
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
            
            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = 'User-Agent: Android/11;FastEasy/3.76.0/7940';
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json';
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $result = json_decode(curl_exec($ch), true);
            
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            
            return $result;
            
        }
        
        public function deletegroup($groupId)
        {
            $accessToken = file_get_contents($this->fileToken);
            $ch = curl_init();
            
            
            curl_setopt($ch, CURLOPT_URL, 'https://fasteasy.scbeasy.com:8443/v1/bulktransferprofiles/group');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"groupId\":\"".$groupId."\"}");
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
            
            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = 'User-Agent: Android/11;FastEasy/3.76.0/7940';
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json';
            
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $result = json_decode(curl_exec($ch), true);    
            
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            return $result;
            
        }
        
        public function getgroup()
        {
            $accessToken = file_get_contents($this->fileToken);
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, "{$this->apiUrl}/v1/bulktransferprofiles/group");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = 'User-Agent: Android/10;FastEasy/' . $this->scbVersion;
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = json_decode(curl_exec($ch), true);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            
            return $result;
            
        }
        
        public function addrecipient($groupId , $ToBankCode, $ToBank, $amount)
        {
            $accessToken = file_get_contents($this->fileToken);
            $bankCode = $this->bankCode($ToBankCode);
            $transferType = $ToBankCode == 'SCB' ? 'SCB' : 'OTHER';
            $ch = curl_init();
            
            
            curl_setopt($ch, CURLOPT_URL, 'https://fasteasy.scbeasy.com:8443/v1/bulktransferprofiles/group/recipient');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"groupId\":\"".$groupId."\",\"recipientList\":[{\"bankCode\":\"".$bankCode."\",\"subFunction\":\"".$transferType."\",\"accountTo\":\"".$ToBank."\",\"nickname\":\"s01\",\"amount\":".$amount."}]}");
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
            
            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = 'User-Agent: Android/11;FastEasy/3.76.0/7940';
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json';
            
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $result = json_decode(curl_exec($ch), true);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            
            return $result;
            
        }
        
        public function getrecipient($groupId)
        {
            $accessToken = file_get_contents($this->fileToken);
            $ch = curl_init();
            
            
            curl_setopt($ch, CURLOPT_URL, 'https://fasteasy.scbeasy.com:8443/v1/bulktransferprofiles/group/recipient?groupId='.$groupId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');        
            
            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = 'User-Agent: Android/11;FastEasy/3.76.0/7940';
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json';
            
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $result = json_decode(curl_exec($ch), true);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            
            return $result;
            
        }
        
        public function verification($groupId,$recipientId,$amount ,$ToBankCode)
        {
            $accessToken = file_get_contents($this->fileToken);
            $ch = curl_init();
            
            
            curl_setopt($ch, CURLOPT_URL, 'https://fasteasy.scbeasy.com:8443/v1/transfer/bulk/verification');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);

            if($ToBankCode == 'SCB')
            {
                curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"ownList\":[],\"otherList\":[],\"accountFrom\":\"".$this->accountNumber."\",\"scbList\":[{\"amount\":\"".$amount."\",\"recipientId\":".$recipientId."}],\"groupId\":\"".$groupId."\"}");
            }
            else
            {
                curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"ownList\":[],\"otherList\":[{\"amount\":\"".$amount."\",\"recipientId\":".$recipientId."}],\"accountFrom\":\"".$this->accountNumber."\",\"scbList\":[],\"groupId\":\"".$groupId."\"}");
            }
            
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
            
            
            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = 'User-Agent: Android/11;FastEasy/3.76.0/7940';
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json';
            
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $result = json_decode(curl_exec($ch), true);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            
            return $result;
            
        }
        
        public function confirmation($transactionToken)
        {
            $accessToken = file_get_contents($this->fileToken);
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://fasteasy.scbeasy.com:8443/v1/transfer/bulk/confirmation');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"transactionToken\":\"".$transactionToken."\"}");
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
            
            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = 'User-Agent: Android/11;FastEasy/3.76.0/7940';
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json';
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $result = json_decode(curl_exec($ch), true);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            
            return $result;
            
        }
        
        public function cleargroup()
        {
            $group = $this->getgroup();
            
            if($group["status"]["code"] == 1000)
            {
                foreach($group["data"]["groupList"] as $grouplist)
                {
                    if($grouplist["groupName"] == "kom")
                    {
                        $deletegrouplist = $this->deletegroup($grouplist["groupId"]);
                    }
                }
            }
        }
        
        public function bulktransferprofiles($ToBankCode, $ToBank, $amount)
        {
            
            $cleargroup = $this->cleargroup();
            
            $addgroup = $this->addgroup("kom");
            if($addgroup["status"]["code"] != 1000)
            {
                return $addgroup; 
            }
            $groupId = $addgroup["data"]["groupId"];
            
            $addrecipient = $this->addrecipient($groupId , $ToBankCode, $ToBank, $amount);
            if($addrecipient["status"]["code"] != 1000)
            {
                return $addrecipient; 
            }
            $getrecipient = $this->getrecipient($groupId);
            if($getrecipient["status"]["code"] != 1000)
            {
                return $getrecipient; 
            }
            if($ToBankCode == 'SCB')
            {
                $recipientId = $getrecipient["data"]["recipientList"]["scbList"][0]["recipientId"];
            }
            else
            {
                $recipientId = $getrecipient["data"]["recipientList"]["otherList"][0]["recipientId"];
            }
            $verification = $this->verification($groupId , $recipientId, $amount, $ToBankCode);
            if($verification["status"]["code"] != 1000)
            {
                $resp = $this->getnametransfer($ToBankCode, $ToBank, $amount); 
                if($resp["status"]["code"] === 5009)
                {
                    return $resp; 
                }
                else
                {
                    return $verification; 
                }               
            }
            $transactionToken = $verification["data"]["transactionToken"];
            $confirmation = $this->confirmation($transactionToken);
            
            $cleargroup = $this->cleargroup();
            return $confirmation;  
        }
        
        /* -- end bulktransferprofiles --*/
        
        function getcheckslip($barcode){
            // $accessToken = file_get_contents("scb-access-token.txt");
            $accessToken = file_get_contents($this->fileToken);
            $ch = curl_init();       
            curl_setopt($ch, CURLOPT_URL, "{$this->apiUrl}/v7/payments/bill/scan");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);  

            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = 'User-Agent: Android/10;FastEasy/' . $this->scbVersion;
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            //curl_setopt($ch, CURLOPT_POSTFIELDS, '{"accountNo": "' . $this->accountNumber . '"}');
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{"barcode": "' . $barcode . '","tilesVersion":"75"}');     
            $result = json_decode(curl_exec($ch), true);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            return $result;
            
        }
        
        public function transactionPage($page = 1)
        {
            // $accessToken = file_get_contents("scb-access-token.txt");
            $accessToken = file_get_contents($this->fileToken);
            $today = date("Y-m-d", time());
            // $yesterday = date("Y-m-d", time() - 60 * 60 * 24);
            $yesterday  = date("Y-m-d", strtotime(date("Y-m-d") . "-2 days"));
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, "{$this->apiUrl}/v2/deposits/casa/transactions");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, true);

            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = 'User-Agent: Android/10;FastEasy/' . $this->scbVersion;
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json;';
            $headers[] = 'scb-channel: APP';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{"accountNo": "' . $this->accountNumber . '","endDate": "' . $today . '","pageNumber": "' . $page . '","pageSize": 50,"productType": "2","startDate": "' . $yesterday . '"}');
            
            $result = json_decode(curl_exec($ch));
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            return $result;
        }
        
        public function transactions()
        {
            $transactions = [];
            $i = 0;
            $limitTime = 4;  // 4x50 = 200 trxs.
            // if ($this->accountNumber = '1222390017') {
            //     $limitTime = 10;
            // }
            $page = 1;
            $minutes = 60;
            
            date_default_timezone_set("Asia/Bangkok");
            
            while ($page != null || $page != '') {
                if ($i == $limitTime) {
                    break;
                }
                try {
                    $result = $this->transactionPage($page);
                    if ($result->status->code != 1000) {
                        return $result;
                    }
                    $page = $result->data->nextPageNumber;
                    foreach ($result->data->txnList as $transaction) {
                        $dateTime = date_create(date('Y-m-d H:i:s', strtotime($transaction->txnDateTime)));
                        $dateTimeNow = date_create(date('Y-m-d H:i:s'));
                        
                        if ($dateTime->diff($dateTimeNow)->format('%h') > 6) {
                            $dateTime = date_create(date("Y-m-d H:i:s", strtotime($transaction->txnDateTime . "-1 days")))->format('Y-m-d H:i:s');
                            } else {
                            $dateTime = date('Y-m-d H:i:s', strtotime($transaction->txnDateTime));
                        }
                        
                        $transactions[] = [
                        'dateTime' => $dateTime,
                        'amount' => $transaction->txnAmount,
                        'txRemark' => $transaction->txnRemark,
                        'txHash' => md5($transaction->txnDateTime . $transaction->txnRemark),
                        'remark' => $this->remarkDescription($transaction->txnRemark),
                        'detail' => $transaction->txnRemark,
                        'channel' => $transaction->txnChannel,
                        'type' => $transaction->txnCode,
                        'nextPageNumber' => $page
                        ];
                    }
                    } catch (\Exception $exception) {
                    // Do nothing.
                }
                $i++;
            }
            
            $dt = array();
            $dt = array_column($transactions, 'dateTime');
            array_multisort($dt, SORT_DESC, $transactions);
            
            return ['availableBalance' => $this->availableBalance, 'transactions' => $transactions];
        }
        
        
        
        public function verifyAccount($bank, $accountNumber)
        {
            // $accessToken = file_get_contents("scb-access-token.txt");
            $accessToken = file_get_contents($this->fileToken);
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, 'https://fasteasy.scbeasy.com:8443/v2/transfer/verification');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);

            if (is_numeric($bank)) {
                $bankCode = $bank;
                } else {
                $bankCode = $this->bankCode($bank);
            }
            $transferType = ($bank == 'SCB') ? '3RD' : (($bank == '014') ? '3RD' : 'ORFT');
            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = 'User-Agent: Android/10;FastEasy/' . $this->scbVersion;
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{"accountFrom": "' . $this->accountNumber . '","accountFromType": "2","accountTo": "' . $accountNumber . '","accountToBankCode": "' . $bankCode . '","amount": "1","annotation": null,"transferType": "' . $transferType . '"}');
            
            $result = json_decode(curl_exec($ch));
            
            if ($result->status->code != 1000 && $result->status->code != 9003) {
                curl_close($ch);
                return json_encode(['code' => 404, 'desc' => 'ไม่พบบัญชี']);
            }
            
            $accountToName = $result->data->accountToName;
            $transferType = $result->data->transferType;
            $accountToName = str_replace('  ', ' ', $accountToName);
            
            $arrName = explode(' ', $accountToName);
            $prefix = '';
            $firstName = '';
            $lastName = '';
            if (sizeof($arrName) < 3) {
                $prefix = $this->getPrefix($accountToName);
                $firstName = $this->getName($accountToName);
                $lastName = $arrName[1];
                } elseif (sizeof($arrName) == 3) {
                $prefix = $arrName[0];
                $firstName = $arrName[1];
                $lastName = $arrName[2];
                } else {
                for ($i = 0; $i < sizeof($arrName) - 2; $i++) {
                    $prefix .= $arrName[$i] . ' ';
                }
                $prefix = substr($prefix, -1);
                $firstName = $arrName[sizeof($arrName) - 2];
                $lastName = $arrName[sizeof($arrName) - 1];
            }
            
            curl_close($ch);
            return json_encode(['code' => 0, 'prefix' => $prefix, 'firstName' => $firstName, 'lastName' => $lastName, 'fullName' => $accountToName, 'verified' => 'SCB']);
        }
        
        public function getnametransfer($bank, $transferTo, $amount)
        {
            
            $accessToken = file_get_contents($this->fileToken);
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, 'https://fasteasy.scbeasy.com:8443/v2/transfer/verification');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);

            $bankCode = $this->bankCode($bank);
            $transferType = $bank == 'SCB' ? '3RD' : 'ORFT';
            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = 'User-Agent: Android/11;FastEasy/3.76.0/7940';
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{"accountFrom": "' . $this->accountNumber . '","accountFromType": "2","accountTo": "' . $transferTo . '","accountToBankCode": "' . $bankCode . '","amount": "' . $amount . '","annotation": null,"transferType": "' . $transferType . '"}');
            
            $result = json_decode(curl_exec($ch), true);
            curl_close($ch);
            return $result;
            
        }
        
        public function transfer($bank, $transferTo, $amount)
        {
            
            $accessToken = file_get_contents($this->fileToken);
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, 'https://fasteasy.scbeasy.com:8443/v2/transfer/verification');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);

            $bankCode = $this->bankCode($bank);
            $transferType = $bank == 'SCB' ? '3RD' : 'ORFT';
            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = 'User-Agent: Android/11;FastEasy/3.76.0/7940';
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{"accountFrom": "' . $this->accountNumber . '","accountFromType": "2","accountTo": "' . $transferTo . '","accountToBankCode": "' . $bankCode . '","amount": "' . $amount . '","annotation": null,"transferType": "' . $transferType . '"}');
            
            $result = json_decode(curl_exec($ch), true);

            if ($result['status']['code'] == 1000) {             
                $accountFromName = $result['data']['accountFromName'];
                $accountTo = $result['data']['accountTo'];
                $accountToBankCode = $result['data']['accountToBankCode'];
                $accountToName = $result['data']['accountToName'];
                $transactionToken = $result['data']['transactionToken'];
                $terminalNo = $result['data']['terminalNo'];
                $sequence = $result['data']['sequence'];
                $transferType = $result['data']['transferType'];
                $pccTraceNo = $result['data']['pccTraceNo'];
                $feeType = $result['data']['feeType'];
                
                $confirmData = [
                'accountFromName' => $accountFromName,
                'accountFromType' => '2',
                'accountTo' => $accountTo,
                'accountToBankCode' => $accountToBankCode,
                'accountToName' => $accountToName,
                'amount' => $amount,
                'botFee' => 0.0,
                'channelFee' => 0.0,
                'fee' => 0.0,
                'feeType' => $feeType,
                'pccTraceNo' => $pccTraceNo,
                'scbFee' => 0.0,
                'sequence' => $sequence,
                'terminalNo' => $terminalNo,
                'transactionToken' => $transactionToken,
                'transferType' => $transferType
                ];
                
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                
                curl_setopt($ch, CURLOPT_URL, 'https://fasteasy.scbeasy.com:8443/v3/transfer/confirmation');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($confirmData));            
                $result = json_decode(curl_exec($ch), true);               
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                
                $jsonData = json_encode($result);
                if (strpos($jsonData, 'Error:error:1408F10B:SSL routines:ssl3_get_record:wrong version number') !== false) {
                    $jsonData = str_replace('Error:error:1408F10B:SSL routines:ssl3_get_record:wrong version number', '', $jsonData);
                }
                $result = json_decode($jsonData, true); 

                curl_close($ch);
                return $result;  

            }else{
                if ($result['status']['header'] == "รายการนี้ซ้ำกับรายการก่อนหน้า") {
                    $accountFromName = $result['data']['accountFromName'];
                    $accountTo = $result['data']['accountTo'];
                    $accountToBankCode = $result['data']['accountToBankCode'];
                    $accountToName = $result['data']['accountToName'];
                    $transactionToken = $result['data']['transactionToken'];
                    $terminalNo = $result['data']['terminalNo'];
                    $sequence = $result['data']['sequence'];
                    $transferType = $result['data']['transferType'];
                    $pccTraceNo = $result['data']['pccTraceNo'];
                    $feeType = $result['data']['feeType'];
                    
                    $confirmData = [
                    'accountFromName' => $accountFromName,
                    'accountFromType' => '2',
                    'accountTo' => $accountTo,
                    'accountToBankCode' => $accountToBankCode,
                    'accountToName' => $accountToName,
                    'amount' => $amount,
                    'botFee' => 0.0,
                    'channelFee' => 0.0,
                    'fee' => 0.0,
                    'feeType' => $feeType,
                    'pccTraceNo' => $pccTraceNo,
                    'scbFee' => 0.0,
                    'sequence' => $sequence,
                    'terminalNo' => $terminalNo,
                    'transactionToken' => $transactionToken,
                    'transferType' => $transferType
                    ];
                    
                    if (curl_errno($ch)) {
                        echo 'Error:' . curl_error($ch);
                    }
                    
                    curl_setopt($ch, CURLOPT_URL, 'https://fasteasy.scbeasy.com:8443/v3/transfer/confirmation');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($confirmData));            
                    $result = json_decode(curl_exec($ch), true);               
                    if (curl_errno($ch)) {
                        echo 'Error:' . curl_error($ch);
                    }

                    $jsonData = json_encode($result);
                    if (strpos($jsonData, 'Error:error:1408F10B:SSL routines:ssl3_get_record:wrong version number') !== false) {
                        $jsonData = str_replace('Error:error:1408F10B:SSL routines:ssl3_get_record:wrong version number', '', $jsonData);
                    }
                    $result = json_decode($jsonData, true); 

                    curl_close($ch);
                    return $result; 
                }
                return $result;             
            }
            
        }
        
        public function transferTrueWallet($transferMobile, $amount)
        {
            
            $accessToken = file_get_contents($this->fileToken);
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, 'https://fasteasy.scbeasy.com:8443/v2/topup/billers/8/additionalinfo');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);

            $headers = array();
            $headers[] = "Api-Auth: $accessToken";
            $headers[] = $this->userAgent;
            $headers[] = 'Accept-Language: th';
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{"annotation": null,"billerId": 8,"depAcctIdFrom": "' . $this->accountNumber . '","note": "TOPUP","pmtAmt": "' . $amount . '","serviceNumber": "' . $transferMobile . '"}');
            
            $result = json_decode(curl_exec($ch), true);
            curl_close($ch);
            
            if($result['status']['code'] == 1000){
                
                $confirmData = [
                'billRef1' => $result['data']['refNo1'],
                'billRef2' => $result['data']['refNo2'],
                'billRef3' => $result['data']['refNo3'],
                'billerId' => '8',
                'depAcctIdFrom' => $this->accountNumber,
                'feeAmt' => 0.0,
                'misc1' => '',
                'misc2' => '',
                'mobileNumber' => $result['data']['refNo1'],
                'note' => '',
                'pmtAmt' => $amount,
                'serviceNumber' => $result['data']['refNo1'],
                'transactionToken' => $result['data']['transactionToken']
                ];
                
                $ch = curl_init();
                
                curl_setopt($ch, CURLOPT_URL, 'https://fasteasy.scbeasy.com:8443/v2/topup');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                $headers = array();
                $headers[] = "Api-Auth: $accessToken";
                $headers[] = $this->userAgent;
                $headers[] = 'Accept-Language: th';
                $headers[] = 'Content-Type: application/json; charset=UTF-8';
                $headers[] = 'Host: fasteasy.scbeasy.com:8443';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($confirmData));
                
                $result = json_decode(curl_exec($ch), true);
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                curl_close($ch);
                return $result;
                
                }else{
                
                return $result;
                
            }
            
        }
        
        private function bankCode($bank)
        {
            
            $bank = ($bank == 'cimb' || $bank == 'CIMB') ? 'CIMBT' : $bank;
            
            $bankCode = [
            'SCB' => '014', 'BBL' => '002', 'KBANK' => '004', 'KTB' => '006', 'BAAC' => '034', 'TMB' => '011',
            'ICBC' => '070', 'BAY' => '025', 'CIMBT' => '022', 'TBANK' => '065', 'GSB' => '030', 'UOB' => '024','KK' => '069','GHB' => '033', 'TTB' => '011', 'LHB' => '073', 'CITIB' => '017', 'TISCOB' => '067'
            ];
            return $bankCode[$bank];
            
        }
        
        private function remarkDescription($text)
        {
            if (preg_match('/\((.*?)\) \/X(.*)/', $text)) {
                preg_match('/\((.*?)\) \/X(.*)/', $text, $temp);
                return [
                'bank' => $temp[1],
                'number' => str_replace(" ", "", $temp[2]),
                'name' => ''
                ];
                } else if (preg_match('/ (.*?) x(.*?) (.*)/', $text)) {
                preg_match('/ (.*?) x(.*?) (.*)/', $text, $temp);
                return [
                'bank' => $temp[1],
                'number' => str_replace(" ", "", $temp[2]),
                'name' => $temp[3]
                ];
                } else {
                return [
                'bank' => '',
                'number' => '',
                'name' => ''
                ];
            }
        }
        
        private function getName($name)
        {
            $results = [];
            
            $temp = str_replace($this->titles, '<> ', $name);
            $temp = mb_ereg_replace('/\s+/', '\s', $temp);
            $temp = array_values(array_filter(explode(' ', $temp)));
            
            if ($temp[0] == '<>') {
                $results['title'] = substr($name, 0, strpos($name, $temp[1]));
                } else {
                $results['title'] = '';
                array_unshift($temp, '');
            }
            
            $results['firstname'] = $temp[1];
            if (count($temp) == 4) {
                $results['middlename'] = $temp[2];
                $results['lastname'] = $temp[3];
                } else {
                unset($temp[0], $temp[1]);
                $results['middlename'] = '';
                $results['lastname'] = implode(' ', $temp);
            }
            
            if (strpos(explode(' ', $name)[0], 'นายนาย') !== false) {
                $name = explode(' ', $name)[0];
                $results['firstname'] = substr($name, 9, strlen($name) - 9);
            }
            
            return $results['firstname'];
        }
        
        private function getFullName($name)
        {
            $results = [];
            
            $temp = str_replace($this->titles, '<> ', $name);
            $temp = mb_ereg_replace('/\s+/', '\s', $temp);
            $temp = array_values(array_filter(explode(' ', $temp)));
            
            if ($temp[0] == '<>') {
                $results['title'] = substr($name, 0, strpos($name, $temp[1]));
                } else {
                $results['title'] = '';
                array_unshift($temp, '');
            }
            
            $results['firstname'] = $temp[1];
            if (count($temp) == 4) {
                $results['middlename'] = $temp[2];
                $results['lastname'] = $temp[3];
                } else {
                unset($temp[0], $temp[1]);
                $results['middlename'] = '';
                $results['lastname'] = implode(' ', $temp);
            }
            
            if (strpos(explode(' ', $name)[0], 'นายนาย') !== false) {
                $name = explode(' ', $name)[0];
                $results['firstname'] = substr($name, 9, strlen($name) - 9);
            }
            
            return $results['firstname'];
        }
        
        private function getPrefix($name)
        {
            $prefix = '';
            for ($i = 0; $i < sizeof($this->titles); $i++) {
                $prefix = substr($name, 0, strlen($this->titles[$i]));
                if ($prefix == $this->titles[$i]) {
                    
                    break;
                }
            }
            return $prefix;
        }
    }
