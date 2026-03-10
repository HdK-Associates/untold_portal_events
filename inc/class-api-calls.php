<?php 
class HdkSpAPI{
    private string $client_code;
    private string $subdomain;
    private string $key;
    private string $api_user;
    private string $consent_id;

    public function __construct(array $settings){
        $this->client_code = $settings['client_code'];
        $this->subdomain = $settings['subdomain'];
        $this->key = $_ENV['SPEKTRIX_API_KEY'];
        $this->api_user = $_ENV['SPEKTRIX_API_USER'];
        $this->consent_id = '201AGBHDRLQHNHPHKKMPKLGPMDRDTDMVL';
    }
    public function SpektrixGetAPIEvents() {
        if(!$this->client_code && !$this->subdomain){
            return false;
        }
        else{
            $Endpoint 	= $this->subdomain .'/'.$this->client_code.'/api/v3/events?instanceStart_from='.date("Y-m-d");	
            $RawData	= wp_remote_get($Endpoint);	
            $Data		= $this->load_request($RawData);
            return $Data;
        }
    }
    public function SpektrixGetAPIEvent(string $id) {
        $Endpoint 	= $this->subdomain .'/'.$this->client_code.'/api/v3/events/'.$id;		
        $RawData	= wp_remote_get($Endpoint);	
        $Data		= $this->load_request($RawData);
        return $Data;
    }

    public function SpektrixGetAPIEventData(string $ID) {	
        $Endpoint 	= $this->subdomain .'/'.$this->client_code.'/api/v3/events/'.$ID.'/instances?start_from='.date("Y-m-d");		
        $RawData	= wp_remote_get($Endpoint,['timeout'=>120]);	
        $Data		= $this->load_request($RawData);
        return $Data;
    }	
    public function SpektrixGetAPIEventDataMonth(string $ID, int $month) {	
        $Endpoint 	= $this->subdomain .'/'.$this->client_code.'/api/v3/events/'.$ID.'/instances?start_from='.date("Y-m-d").'&start_to='.date("Y-m-d",strtotime('+'.$month.'months'));		
        $RawData	= wp_remote_get($Endpoint,['timeout'=>120]);	
        $Data		= $this->load_request($RawData);
        return $Data;
    }	
    public function SpektrixGetAPIEventDataPriceList(string $ID) {	
        $Endpoint 	= $this->subdomain .'/'.$this->client_code.'/api/v3/instances/'.$ID.'/price-list';		
        $RawData	= wp_remote_get($Endpoint);	
        $Data		= $this->load_request($RawData);
        return $Data;
    }

    public function SpektrixGetAPIEventDataStatus(string $ID) {	
        $Endpoint 	= $this->subdomain .'/'.$this->client_code.'/api/v3/instances/'.$ID.'/status?includeChildPlans=true';		
        $RawData	= wp_remote_get($Endpoint);	
        $Data		= $this->load_request($RawData);
        return $Data;
    }
    
    public function SpektrixGetAPIMerch(){
        $Endpoint 	= $this->subdomain .'/'.$this->client_code.'/api/v3/stock-items';		
        $RawData	= wp_remote_get($Endpoint);	
        $Data		= $this->load_request($RawData);
        return $Data;
    }
    public function SpektrixGetAPIMembers(){
        $Endpoint 	= $this->subdomain .'/'.$this->client_code.'/api/v3/memberships';		
        $RawData	= wp_remote_get($Endpoint);	
        $Data		= $this->load_request($RawData);
        return $Data;
    }
    public function SpektrixGetAPIFunds(){
        $Endpoint 	= $this->subdomain .'/'.$this->client_code.'/api/v3/funds';		
        $RawData	= wp_remote_get($Endpoint);	
        $Data		= $this->load_request($RawData);
        return $Data;
    }
    
    public function SpektrixGetAPITags(){
        $Endpoint 	= $this->subdomain .'/'.$this->client_code.'/api/v3/tag-groups';		
        $RawData	= wp_remote_get($Endpoint);	
        $Data		= $this->load_request($RawData);
        return $Data;
    }

    public function SpektrixPostAPINewsletter($body){
        $Endpoint   = $this->subdomain .'/'.$this->client_code.'/api/v3/customer';
        $args       = array(
            'headers'   => ['content-type' => 'application/json'],
            'body'      => json_encode($body),
        );
        $response = wp_remote_post( $Endpoint, $args);
        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        } else {
            return $response;
        }
    }

    public function SpektrixGetAPIPlans($planID){
        $Endpoint 	= $this->subdomain .'/'.$this->client_code.'/api/v3/plans/'.$planID;		
        $RawData	= wp_remote_get($Endpoint);	
        $Data		= $this->load_request($RawData);
        return $Data;
    }

    public function CreateAuthHeader($method,$endpoint,$body,$date){
        $plaintext  = $method."\n".$endpoint."\n".$date;
        if($method!='GET'){
            $bodyStringToSign = base64_encode(md5(utf8_encode($body),true));
            $plaintext.="\n".$bodyStringToSign;
        }
        $signature = base64_encode(hash_hmac('sha1',utf8_encode($plaintext),base64_decode($this->key),true));
        $auth = 'SpektrixAPI3 '.$this->api_user.':'.$signature;
        return $auth;
    }

    public function SpektrixAPIPostUserWithTags($firstName,$lastName,$email,$tags=null,$server_side=false){
        $bodyParams = [
            'email'=>$email,
            'firstName'=>$firstName,
            'lastName'=>$lastName,
        ];
        if($tags){
            if(!is_array($tags)){
                $tags = [$tags];
            }
            $bodyParams['Tags']=$tags;
            $bodyParams['AgreedStatements']=[$this->consent_id];
        }
          
        if(!isset($bodyParams['Tags'])){
            $bodyParams['Tags']=[];
        }
        //Origin tag 
        $bodyParams['Tags'][]= '3401ARKRNLBJKDJTKHBHRJBNHVDNSKPTR';
        if(!$server_side){
            $endpoint 	= 'https://system.spektrix.com/'.$this->client_code.'/api/v3/customer';      
            $rawdata	= wp_remote_post($endpoint,[
                'method' => 'POST',
                'headers'     => [
                    'Content-Type' => 'application/json',
                ],
                'body'=>json_encode($bodyParams)
            ]);	
        }
        else{
            $endpoint 	= 'https://system.spektrix.com/'.$this->client_code.'/api/v3/customers'; 
             $date       = gmdate('D, d M Y H:i:s \G\M\T');  
             $body = json_encode($bodyParams);   
             $rawdata	= wp_remote_post($endpoint,[
                'method' => 'POST',
                'headers'     => [
                    'Authorization' => $this->CreateAuthHeader('POST',$endpoint,$body,$date),
                    'host' => 'system.spektrix.com',
                    'date' => $date,
                    'Content-Type' => 'application/json',
                ],
                'body'=>$body
            ]);	
        }
        $data = json_decode(wp_remote_retrieve_body( $rawdata),true);
        if(isset($data['id'])){
            return 'Thank you. You\'ve been successfully signed up';
        }
        elseif(isset($data['message']) && $data['message']=='Duplicate Entry'){
            $endpoint 	= 'https://system.spektrix.com/'.$this->client_code.'/api/v3/customers?email='.$email;
            $method     = 'GET';
            $date       = gmdate('D, d M Y H:i:s \G\M\T');
            $rawdata = wp_remote_get( $endpoint,[
            'method' => $method,
            'headers' => [
                'Authorization' => $this->CreateAuthHeader($method,$endpoint,null,$date),
                'host' => 'system.spektrix.com',
                'date' => $date,
            ]
            ]);
            $data = json_decode(wp_remote_retrieve_body( $rawdata),true);
            $return=false;
            if($data['id']){
                $id = $data['id'];
                $endpoint 	= 'https://system.spektrix.com/'.$this->client_code.'/api/v3/customers/'.$id.'/tags'; 
                foreach($tags as $tag){
                    $method     = 'POST';
                    $date       = gmdate('D, d M Y H:i:s \G\M\T');
                    $body = json_encode(["id"=>$tag]);
                    $rawdata	= wp_remote_post($endpoint,[
                        'method' => 'POST',
                        'headers'     => [
                            'Authorization' => $this->CreateAuthHeader($method,$endpoint,$body,$date),
                            'host' => 'system.spektrix.com',
                            'date' => $date, 
                            'Content-Type' => 'application/json',
                        ],
                        'body'=>$body
                    ]);	
                    $data = json_decode(wp_remote_retrieve_body( $rawdata),true);
                    if(isset($data['id'])){
                        $return = true;
                    }
                }
                /* $endpoint   = 'https://system.spektrix.com/'.$this->client_code.'/api/v3/customers/'.$id.'/agreed-statements';
                $method     = 'POST';
                $date       = gmdate('D, d M Y H:i:s \G\M\T');
                $body       = json_encode([["id"=>$this->consent_id]]);
                $rawdata	= wp_remote_post($endpoint,[
                    'method' => 'POST',
                    'headers'     => [
                        'Authorization' => $this->CreateAuthHeader($method,$endpoint,$body,$date),
                        'host' => 'system.spektrix.com',
                        'date' => $date, 
                        'Content-Type' => 'application/json',
                    ],
                    'body'=>$body
                ]);	
                $data = json_decode(wp_remote_retrieve_body( $rawdata),true);
                if(isset($data[0]['id'])){
                    $return = true;
                } */
            }
            if($return){
                return 'Thank you. You\'ve been successfully signed up';
            }
            else{
                return 'Something went wrong. Please try again later';
            }
        }
        else{
            return 'Something went wrong. Please try again later';
        }
    }
   
    public function SpektrixAPIDeleteTagsFromUser($email,$tags){
        $endpoint 	= 'https://system.spektrix.com/'.$this->client_code.'/api/v3/customers?email='.$email;
        $method     = 'GET';
        $date       = gmdate('D, d M Y H:i:s \G\M\T');
        $rawdata = wp_remote_get( $endpoint,[
        'method' => $method,
        'headers' => [
            'Authorization' => $this->CreateAuthHeader($method,$endpoint,null,$date),
            'host' => 'system.spektrix.com',
            'date' => $date,
        ]
        ]);
        $data = json_decode(wp_remote_retrieve_body( $rawdata),true);
        if($data['id']){
            $id = $data['id'];
            foreach($tags as $tag){
                $endpoint 	= 'https://system.spektrix.com/'.$this->client_code.'/api/v3/customers/tags/'.$tag.'?id='.$id; 
                $method     = 'DELETE';
                $date       = gmdate('D, d M Y H:i:s \G\M\T');
                wp_remote_request($endpoint,[
                    'method' => 'DELETE',
                    'headers'     => [
                        'Authorization' => $this->CreateAuthHeader($method,$endpoint,'',$date),
                        'host' => 'system.spektrix.com',
                        'date' => $date, 
                        'Content-Type' => 'application/json',
                    ],
                ]);	
            }
        }
    }

    public function SpektrixAPIAddTagsByUserID($id,$body){
        $endpoint   = 'https://system.spektrix.com/'.$this->client_code.'/api/v3/customers/'.$id.'/tags';
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $args       = array(
            'method' => 'POST',
            'headers' => array(
                'Authorization' => $this->CreateAuthHeader('POST',$endpoint,$body,$date),
                'host' => 'system.spektrix.com',
                'date' => $date,
                'content-type' => 'application/json'
            ),
            'body'=>$body
        );
        $response = wp_remote_post( $endpoint, $args);
        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        } else {
            return $response;
        }
    }
    public function load_request($response) {
        try {
          if ( is_wp_error( $response ) ) {  
            throw new Exception( $response->get_error_message());
            WP_CLI::line( $response->get_error_message());
          }else{
            $json = json_decode( $response['body'] );
          }
        } catch ( Exception $ex ) {
          $json = null;
        }
        return $json;
      }

    public function get_customer(){
       // $Endpoint   = $this->subdomain .'/'.$this->client_code.'/api/v3/customer';
        $Endpoint   = 'https://system.spektrix.com/'.$this->client_code.'/api/v3/customer?$expand=subscriptions';
        $response = wp_remote_get($Endpoint,['headers'=>['credentials'=>'include']]);
        $Data = $this->load_request($response);
        return $Data;
    }
    
    public function set_customer_gift_aid(){
        $Endpoint   = $this->subdomain .'/'.$this->client_code.'/api/v3/customer';
        $body = json_encode(["GiftAidConfirmed"=>true]);
        $response = wp_remote_request($Endpoint,['method'=>'PATCH','body'=>$body]);
        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        } else {
            return $response;
        }
    }

}