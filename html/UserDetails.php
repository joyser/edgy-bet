<h1 class="ui header">Settings</h1>



<form action="index.php?controller=User&action=updateDetails" method="POST">
<div class="ui action input">
  <input type="text" value="<?=$bankroll;?>" placeholder="€ bankroll" name="bankroll" >
  <input type="password" name="password" placeholder="New password">
    
</div>
  <input type="submit" value="Update" class="ui green button"/>

</form>

