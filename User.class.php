<?
	
	class User extends Model{
		
		
		
		public static function passwordHash($password){
			
			
			return password_hash($password, PASSWORD_DEFAULT);
		}
		
		public function addNewUser($username, $password, $name){
			
			//Check that the username does not exist..
			$NewUser = new User;
			
			$UserCheck = $NewUser->find(array('conditions'=>array('username'=>$username)));
			
			if( count($UserCheck) == 0 ){
				
				
				$NewUser->username = $username;
				$NewUser->password = User::passwordHash($password);
				$NewUser->name = $name;
				
				$NewUser->save();
			}
		}
		
		public function updatePassword($password){
			
			if(isset($this->id)){
				
				$this->password = User::passwordHash($password);
				$this->save();
			}
		}
		
	}