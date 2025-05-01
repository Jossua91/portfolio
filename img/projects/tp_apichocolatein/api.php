<?php
include_once("AccessBDD.php");

/**
 * Classe de construction des requêtes SQL
 * hérite de AccessBDD qui contient les requêtes de base
 * Pour ajouter une requête :
 * - créer la fonction qui crée une requête (prendre modèle sur les fonctions 
 *   existantes qui ne commencent pas par 'traitement')
 * - ajouter un 'case' dans un des switch des fonctions redéfinies 
 * - appeler la nouvelle fonction dans ce 'case'
 */
class MyAccessBDD extends AccessBDD {
	    
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct(){
        try{
            parent::__construct();
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */	
    protected function traitementSelect(string $table, ?array $champs) : ?array{
        switch($table){     
            case "" :
                // return $this->uneFonction(parametres);
            case "produit_specifique" :
                return $this->selectMotCle($champs);
            case "variantes" :
                return $this->selectVariantes($champs);
            case "intolerance" :
                return $this->selectIntolerances($champs);
            case "nb_specifique":
                return $this->selectNbSpecifique($champs);
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }	
    }

    /**
     * demande d'ajout (insert)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples ajoutés ou null si erreur
     * @override
     */	
    protected function traitementInsert(string $table, ?array $champs) : ?int{
        switch($table){
            case "produit" :
                return $this->insertProduit($champs);
            case "" :
                // return $this->uneFonction(parametres);
            default:                    
                // cas général
                return $this->insertOneTupleOneTable($table, $champs);	
        }
    }
    
    /**
     * demande de modification (update)
     * @param string $table
     * @param string|null $id
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples modifiés ou null si erreur
     * @override
     */	
    protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int{
        switch($table){
            case "transfert_images" :
                return $this->updateCheminImges($champs);
            case "" :
                // return $this->uneFonction(parametres);
            default:                    
                // cas général
                return $this->updateOneTupleOneTable($table, $id, $champs);
        }	
    }  
    
    /**
     * demande de suppression (delete)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples supprimés ou null si erreur
     * @override
     */	
    protected function traitementDelete(string $table, ?array $champs) : ?int{
        switch($table){
            case "produit" :
                return $this->deleteNettoieGamme($champs);
            case "" :
                // return $this->uneFonction(parametres);
            default:                    
                // cas général
                return $this->deleteTuplesOneTable($table, $champs);	
        }
    }	    
        
    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null 
     */
    private function selectTuplesOneTable(string $table, ?array $champs) : ?array{
        if(empty($champs)){
            // tous les tuples d'une table
            $requete = "select * from $table;";
            return $this->conn->queryBDD($requete);  
        }else{
            // tuples spécifiques d'une table
            $requete = "select * from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);	 
            return $this->conn->queryBDD($requete, $champs);
        }
    }	

    /**
     * demande d'ajout (insert) d'un tuple dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "insert into $table (";
        foreach ($champs as $key => $value){
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ") values (";
        foreach ($champs as $key => $value){
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ");";
        return $this->conn->updateBDD($requete, $champs);
    }

    /**
     * demande de modification (update) d'un tuple dans une table
     * @param string $table
     * @param string\null $id
     * @param array|null $champs 
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */	
    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);				
        $champs["id"] = $id;
        $requete .= " where id=:id;";		
        return $this->conn->updateBDD($requete, $champs);	        
    }
    
    /**
     * demande de suppression (delete) d'un ou plusieurs tuples dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples supprimés ou null si erreur
     */
    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "delete from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-5);   
        return $this->conn->updateBDD($requete, $champs);	        
    }
 
    /**
     * récupère le nom, la description et les détails des produits
     * dont 'description' ou 'détails' contient le mot clé présent dans $champs
     * @param array|null $champs contient juste 'cle' avec une valeur de cle
     * @return ?array
     */
    private function selectMotCle(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('cle', $champs)){
            return null;  
        }
        // construction de la requête
        $requete = "select p.nom, p.description, dp.details ";
        $requete .= "from produit p left join details_produits dp on (p.id = dp.idproduit) ";
        $requete .= "where p.description like :cle or dp.details like :cle ";
        $requete .= "order by p.nom";
        // ajoute le % au paramètre
        $champsRequete['cle'] = '%' . $champs['cle'] . '%';        
        return $this->conn->queryBDD($requete, $champsRequete);		
    }
    
    private function selectVariantes() : ?array{
        // construction de la requête
        $requete = "select p.id, p.nom, GREATEST(COUNT(dp.idproduit), 1) AS variantes ";
        $requete .= "from produit p left join details_produits dp on (p.id = dp.idproduit) ";
        $requete .= "group by p.id, p.nom ";
        $requete .= "order by p.nom";
        return $this->conn->queryBDD($requete);		
    }
    
    private function selectIntolerances(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('ingredient', $champs)){
            return null;
        }
        $requete = "SELECT p.id, p.nom, p.description, dp.details ";
        $requete .= "FROM produit p ";
        $requete .= "LEFT JOIN details_produits dp ON p.id = dp.idproduit ";
        $requete .= "WHERE (p.description NOT LIKE :ingredient) ";
        $requete .= "AND (p.nom NOT LIKE :ingredient) ";
        $requete .= "AND (dp.details IS NULL OR dp.details NOT LIKE :ingredient) ";
        $requete .= "ORDER BY p.nom";
        // ajoute le % au paramètre
        $champsRequete['ingredient'] = '%' . $champs['ingredient'] . '%';        
        return $this->conn->queryBDD($requete, $champsRequete);		
    }
    
    private function insertProduit(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists("id", $champs)){
            return null;
        }        
        // construction de la requête
        $req = "insert into gamme (id) ";		 
        $req .= "select (:id) from dual ";		 
        $req .= "where not exists (select * from gamme where id = :id);";
        $champsNecessaires["id"] = $champs["id"];
        $nbInsertGamme = $this->conn->updateBDD($req, $champsNecessaires); 
        if ($nbInsertGamme === null){
            return null;
        }else{
            return $this->insertOneTupleOneTable("produit", $champs) + $nbInsertGamme;
        }
    }
    
    /**
     * dans la table produit, change le chemin des images
     * @param array|null $champs
     * @return int|null nombre de lignes modifiées ou null si erreur
     */
    private function updateCheminImges(?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists("ancien", $champs) || !array_key_exists("nouveau", $champs)){
            return null;
        }
        $req = "update produit ";
        $req .= "set urlimg = replace(urlimg, :ancien, :nouveau);";
        $champsNecessaires["ancien"]=$champs["ancien"];
        $champsNecessaires["nouveau"]=$champs["nouveau"];
        return $this->conn->updateBDD($req, $champsNecessaires);
    }
    
    /**
     * nettoie la table gamme
     * en supprimant les lignes dont le libelle et le picto sont vides
     * et dont l'id n'est pas utilisé par un prouit
     * @return int|null nombre de lignes supprimées
     */
    private function deleteNettoieGamme(?array $champs) : ?int {
        // Vérification des paramètres
            if (empty($champs) || !array_key_exists("id", $champs)) {
                return null;
            }

            // Construction de la requête DELETE
            $req = "DELETE FROM gamme WHERE id = :id AND id NOT IN (SELECT idgamme FROM produit)";

            // Exécution de la requête
            return $this->conn->updateBDD($req, ["id" => $champs["id"]]);
    }

    private function selectNbSpecifique(?array $champs) : ?array {
        if (empty($champs) || !isset($champs['cle'])) {
            return null;
        }

        $requete = "SELECT g.id AS idgamme, COUNT(p.id) AS nbProduits 
            FROM gamme g 
            LEFT JOIN produit p ON g.id = p.idgamme 
            LEFT JOIN details_produits dp ON p.id = dp.idproduit 
            WHERE p.description LIKE :cle OR dp.details LIKE :cle 
            GROUP BY g.id";

        $champsRequete['cle'] = '%' . $champs['cle'] . '%';

        $resultats = $this->conn->queryBDD($requete, $champsRequete);

        $formattedResults = [];
        foreach ($resultats as $ligne) {
            $formattedResults[] = [
                "idgamme" => $ligne['idgamme'],
                $champs['cle'] => $ligne['nbProduits']
            ];
        }

        return $formattedResults;
    }
}