<?
	
	class globalView{
		
		
		public $views = array();
		public $displayVariables = array();
		
		function addView($view, $displayVariables = array()){
			
			$this->views[] = $view;
			$this->displayVariables[] = $displayVariables;
		}
		
		function printPage(){
			
			$UserSession = new UserSession;
			
			if($UserSession->mobile){
				
				$mobileString = "Mobile.";
			}else{
				
				$mobileString = "";
			}
			
			foreach( $this->views as $index=>$viewName){
				
				
				$this->printView($mobileString.$viewName, $this->displayVariables[$index] );
			}
		}
		
		function printView($view, $displayVariables){
			
			foreach( $displayVariables as $name => $variable){
				
				$$name = $variable;
			}
			
			if( file_exists("html/$view.php"))
				include "html/$view.php";
		}
		
	}