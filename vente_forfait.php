<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$queryProduits = $db->query("SELECT * FROM produits");
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vente | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
               <h1 class="page-title"><span class="glyphicon glyphicon-road"></span> Vente d'un produit</h1>
               <form action="" method="post">
                   <div class="form-group">
                       <label for="produit">Choisissez le forfait</label>
                        <div class="input-group">
                           <select name="produit" class="form-control" id="produit-select" onfocus="feedDetails()" onchange="feedDetails()";>
                           <?php while($produits = $queryProduits->fetch(PDO::FETCH_ASSOC)){ ?>
                               <option value="<?php echo $produits["produit_id"];?>"><?php echo $produits["produit_nom"];?></option>
                           <?php } ?>
                           </select>
                           <span role="button" class="input-group-btn" >
                               <a href="#produit-details" class="btn btn-default" data-toggle="collapse" aria-expanded="false"><span class="glyphicon glyphicon-search"></span> Détails...</a>
                           </span>
                       </div>
                       <div id="produit-details" class="collapse">
                           <div id="produit-content" class="well"></div>
                       </div>
                   </div>
                   <div class="form-group">
                       <label for="personne">Acheteur du forfait</label>
                       <input type="text" name="personne" class="form-control">
                   </div>
                   <div class="form-group">
                       <label for="echeances">Nombre d'échéances</label>
                       <input type="text" name="echeances" class="form-control">
                   </div>
                   <div class="form-group">
                       <label for="date_activation">Date souhaitée d'activation</label>
                       <div class="input-group">
                           <input type="date" name="date_activation" id="today_possible" class="form-control">
                           <span role="buttton" class="input-group-btn"><a class="btn btn-default" role="button" onclick="insertToday()">Aujourd'hui</a></span>
                       </div>
                   </div>
                   <div class="form-group">
                       <label for="prix_achat">Prix du forfait souhaité</label>
                       <input type="text" name="prix_achat" id="prix_calcul" class="form-control">
                   </div>
               </form>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
   <script>
    function feedDetails(){
        var id = $("#produit-select").find(":selected").val();
        $.post("functions/feed_product_details.php",{id}).done(function(data){
            $("#produit-content").empty();
            var json = JSON.parse(data);
            var jours = json.validite_initiale;
            var arep = json.autorisation_report;
            var line = "Volume horaire : "+json.volume_horaire+" heures";
            line += "<br>Valable pendant "+json.validite_initiale+" jours ("+(jours/7)+" semaines) à partir de l'activation";
            line += "<br>Le paiement peut être réglé en maximum "+json.echeances_paiement+" fois.";
            line += "<br>L'extension de durée ";
            line += (arep==0)?"n'est pas":"est";
            line += " autorisée";
            $("#produit-content").append(line);
        });
    }
       
       function calculatePrice(){
           $("#prix_calcul").html("35");
       }
    </script>
</body>
</html>