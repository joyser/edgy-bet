<h5 class="ui orange header"><?=$BetfairRunner->name;?></h5>

<form action="index.php?controller=Bet" method="POST">
<div class="ui input">
  <input type="date" value="<?=$startDate;?>" name="startDate" >
  <input type="date" value="<?=$endDate;?>" name="endDate" >
  <select name="UserId" class="ui selection dropdown">
	  <option value="">All</option>
	  <?
		  foreach( $Users as $User){
			  ?>
			  
			  <option <?=($UserId==$User->id)?"selected=\"true\"":""?> value="<?=$User->id;?>"><?=$User->name;?></option>
			  <?
		  }
		?>
	  
  </select>
  <input type="submit" value="Search" name="submit" >
</div>
</form>