<html>
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <div class="col-sm-8 col-sm-offset-2 main">
               <h1>Inscription à Salsabor</h1>
               <fieldset>
                   <form action="" method="post">
                       <div class="form-group">
                           <label for="prenom" class="control-label">Prénom <span class="mandatory"> *</span></label>
                           <input type="text" class="form-control" name="prenom" placeholder="Prénom">
                           <br>
                           <label for="nom" class="control-label">Nom <span class="mandatory"> *</span></label>
                           <input type="text" class="form-control" name="prenom" placeholder="Nom">
                           <br>
                           <label for="mail" class="control-label">Adresse mail <span class="mandatory"> *</span></label>
                           <input type="text" class="form-control" name="mail" placeholder="example@domaine.com">
                           <br>
                           <label for="address" class="control-label">Adresse<span class="mandatory"> *</span></label>
                           <input type="text" class="form-control" name="address" placeholder="17 rue Jabelot">
                           <br>
                           <label for="address_complement" class="control-label">Complément d'adresse</label>
                           <input type="text" class="form-control" name="address_complement">
                           <br>
                           <label for="postal" class="control-label">Code Postal<span class="mandatory"> *</span></label>
                           <input type="text" class="form-control" name="postal">
                           <br>
                           <label for="town" class="control-label">Ville<span class="mandatory"> *</span></label>
                           <input type="text" class="form-control" name="town">
                           <br>
                           <label for="country" class="control-label">Pays<span class="mandatory"> *</span></label>
                           <input type="text" class="form-control" name="country">
                           <br>
                       </div>
                   </form>
               </fieldset>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>