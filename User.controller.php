<?
	
	class UserController {
		
		function index(){
			
			global $globalView;
			
			//Display all bets to the user..
			$UserSession = new UserSession;
			$bankroll = $UserSession->User->bankroll;
			
			if( $bankroll==0 )
				$bankroll="";
			
			$globalView->addView('UserDetails', array('bankroll'=>$bankroll));
			
		}
		
		
		function updateDetails($arguments = array()){
			
			$UserSession = new UserSession;
			$UserSession->User->bankroll = $arguments['bankroll'];
			$UserSession->User->save();
				
			if($arguments['password'] != "" ){
				
				$UserSession->User->updatePassword($arguments['password']);
				$UserSession->logout();
				exit;
			}
			
			$this->index();
		} 
	}