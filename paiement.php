<div class="form-group">
                               <label for="echeances">Nombre d'échéances mensuelles</label>
                               <input type="text" name="echeances" class="form-control" placeholder="Nombre maximum suggéré : <?php echo $p["echeances_paiement"];?>">
                           </div>
                           <div class="form-group">
                            <label for="numero_echeance">Détail des échances</label>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date de l'échéance</th>
                                        <th>Montant de l'échéance</th>
                                        <th>Méthode de règlement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                           </div>