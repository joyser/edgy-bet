<?
	
	class Login extends Model{
		
		protected	$belongsTo = array(
					'User'=>array(
						'required'=>true,
					)
		);
		
	}