<?
	
	class UserSession{
		
		private $isLoggedIn=0;
		public $ipAddress;
		public $mobile;
		private $sessionToken;
		
		function __construct(){
			
			//Record the IP address of the session..
			$this->ipAddress = $_SERVER['REMOTE_ADDR'];
			
			//Check if the user is logged in..
			if( $_SESSION['isLoggedIn'] ){
				
				$userId = $_SESSION['userId'];
				$sessionToken = $_SESSION['sessionToken'];
				$this->sessionToken = $sessionToken;
								
				//Retrieve the last login attempt
				$Login = new Login;
				$Login = $Login->find(array('conditions'=>array('UserId'=>$userId),'orderBy'=>'time DESC', 'limit'=>'1' ) )[0];
				
				if( !empty($sessionToken) && $sessionToken==$Login->sessionToken){
					
					$this->isLoggedIn=1;
					
					//Update the last active record..
					$Login->lastActive = date('Y-m-d H:i:s');
					$Login->save();
					
					//Load the user into object
					$User = new User;
					$User = $User->find(array('conditions'=>array('id'=>$userId)  ) )[0];
					
					$this->User = $User;
					
					if( $this->User->level != 1 && $this->User->level != 2){
						
						$this->mobile=1;
					}else{
						
						$this->mobile = $_SESSION['mobile'];
					}
					
				}else{
					
					//logout current session
					$this->logout();
				}
				
			}
			
		}
		
		function login($username, $password){
			
			//Check logins from this IP address in the last 10 minutes..
			$lookBack = new DateTime('-10 minutes');
			$Login = new Login;
			$Logins = $Login->find(array('conditions'=>array('ipAddress'=>$this->ipAddress, 'time>'=>$lookBack->format('Y-m-d H:i:s'))));
			
			if( count($Logins) < 10 ){
				
				//Attempt to find the user in database..				
				$User = new User;
				
				$User = $User->find(array('conditions'=>array('username'=>$username)  ) );
				
				//Resue the login object for logging this login
				$Login->ipAddress = $this->ipAddress;
				$Login->wasSuccess = 0;
				$Login->time = date('Y-m-d H:i:s');		
				
				if( count($User) == 1 ){
					
					$Login->UserId = $User[0]->id;
															
					if( password_verify($password,$User[0]->password) ){
						
						//Generate session token..
						$sessionToken = User::passwordHash($User[0]->username);
						
						//Record session variables
						$_SESSION['isLoggedIn'] = 1;
						$_SESSION['sessionToken'] = $sessionToken;
						$_SESSION['userId'] = $User[0]->id;
						$this->User = $User[0];
						$this->isLoggedIn = 1;
						
						//Mark login as success
						$Login->wasSuccess=1;
						$Login->sessionToken=$sessionToken;
						
						//Check if mobile
						if(isMobileDevice()){
							
							$this->mobile = 1;
							$_SESSION['mobile']=$this->mobile;
						}
						
					}
				}
				
				$Login->save();
			}
		}
		
		function logout(){
			
			//Retrieve the login attempt
			$Login = new Login;
			$Login = $Login->find(array('conditions'=>array('sessionToken'=>$this->sessionToken) ) );
						
			if(count($Login)==1){
				
				$Login = $Login[0];
				
				//Note the logout time
				$Login->logoutTime = date('Y-m-d H:i:s');
				$Login->save();
			}
			
			
			$this->isLoggedIn = 0;
			unset($this->sessionToken);
			unset($this->userId);
				
			session_unset();
			
		}
		
		
		function isLoggedIn(){
			
			return $this->isLoggedIn;
		}
		
		function toMobile(){
			
			$this->mobile = 1;
			$_SESSION['mobile'] = $this->mobile;
		}
		
		function toDesktop(){
			
			$this->mobile = 0;
			$_SESSION['mobile'] = $this->mobile;
		}
		
		function logPageLoad(){
			
			$PageLoad = new PageLoad;
			
			$PageLoad->UserId = $this->User->id;
			$PageLoad->requestURL = $_SERVER[REQUEST_URI];
			$PageLoad->save();
			
		}
		
	}