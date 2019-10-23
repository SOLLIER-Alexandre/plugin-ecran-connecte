<?php
/**
 * Created by PhpStorm.
 * UserView: Rohrb
 * Date: 25/04/2019
 * Time: 10:44
 */

class StudentView extends UserView {

    public function displayFormInscription() {
        return $this->displayBaseForm('Etu', true);
    }

    /**
     * Renvoie un formulaire d'inscription par fichier Excel pour les étudiants
     * @return string   Renvoie le formulaire
     */
    public function displayInsertImportFileStudent() {
        return '<h1> Création comptes étudiants</h1>'.$this->displayInsertImportFile("Etu");
    }

    /**
     * En-tête du tableau des étudiants
     * @return string   Renvoie l'en-tête
     */
    public function displayTabHeadStudent(){
        $title = "Étudiants";
        $tab = ["Numéro étudiant", "Année", "Groupe", "Demi groupe"];
        return $this->displayStartTab('etu', $tab, $title);
    }

    /**
     * Affiche une ligne contenant les données de l'étudiant
     * Pour les trois codes ADE, on affiche normaelement le titre, mais si ce n'est pas le cas, met le code en rouge
     * @param $id           ID de l'étudiant
     * @param $login        Login de l'étudiant
     * @param $year         Code ADE de son année
     * @param $group        Code ADE de son groupe
     * @param $halfgroup    Code ADE de son demi-groupe
     * @param $row          Numéro de la ligne
     * @return string       Renvoie la ligne
     */
    public function displayAllStudent($id, $login, $year, $group, $halfgroup, $row){
        $page = get_page_by_title( 'Modification utilisateur');
        $linkModifyUser = get_permalink($page->ID);
        $string = '
        <tr>
          <th scope="row" class="text-center">'.$row.'</th>
          <td class="text-center"><input type="checkbox" name="checkboxstatusetu[]" value="'.$id.'"/></td>
          <td class="text-center">'.$login.'</td>
          <td class="text-center'; if(is_numeric($year)) $string .= ' errorNotRegister'; $string .= '">'.$year.'</td>
          <td class="text-center'; if(is_numeric($group)) $string .= ' errorNotRegister'; $string .= '">'.$group.'</td>
          <td class="text-center'; if(is_numeric($halfgroup)) $string .= ' errorNotRegister'; $string .= '">'.$halfgroup.'</td>
          <td class="text-center"> <a href="'.$linkModifyUser.$id.'" name="modif" type="submit" value="Modifier">Modifier</a></td>
        </tr>';
          return $string;
    }

    /**
     * Indique la signification du code en rouge
     * @return string   Renvoie la signification
     */
    public function displayRedSignification(){
        return '<div class="red">Zone rouge = Code ADE non enregistré</div>';
    }

    /**
     * Affiche un formulaire pour modifier l'étudiant
     * Pour modifier les codes ADE, on les modifies via les codes déjà présent dans la base de données
     * @param $result       Données de l'étudiant
     * @param $years        Toutes les années enregistrées
     * @param $groups       Tous les groupes enregistrés
     * @param $halfgroups   Tous les demi-groupes enregistrés
     */
    public function displayModifyStudent($result, $years, $groups, $halfgroups){
        $page = get_page_by_title( 'Gestion des utilisateurs');
        $linkManageUser = get_permalink($page->ID);
        $code = unserialize($result->code);
        $model = new CodeAdeManager();
        $titleYear = $model->getTitle($code[0]);
        $titleGroup = $model->getTitle($code[1]);
        $titleHalfgroup = $model->getTitle($code[2]);
        echo '
        <div class="cadre">
         <h3>'.$result->user_login.'</h3>
         <form method="post">
            <label>Année</label>
            <select class="form-control" name="modifYear">
                <option value="'.$code[0].'">'.$titleYear.'</option>
                <option value="0">Aucun</option>
                <optgroup label="Année">
            ';
        $selected = $_POST['modifYear'];
        foreach ($years as $year) {
                echo '<option value="'.$year['code'].'"'; if($year['code'] == $selected) echo "selected"; echo'>'.$year['title'].'</option >';
        }
        echo'
            </optgroup>
            </select>
            <label>Groupe</label>
            <select class="form-control" name="modifGroup">
                <option value="'.$code[1].'">'.$titleGroup.'</option>
                <option value="0">Aucun</option>
                <optgroup label="Groupe">';
        $selected = $_POST['modifGroup'];
        foreach ($groups as $group){
            echo'<option value="'.$group['code'].'"'; if($group['code'] == $selected) echo "selected"; echo'>'.$group['title'].'</option>';
        }
        echo'
            </optgroup>
            </select>
            <label>Demi-groupe</label>
            <select class="form-control" name="modifHalfgroup">
                <option value="'.$code[2].'">'.$titleHalfgroup.'</option>
                <option value="0"> Aucun</option>
                <optgroup label="Demi-Groupe">';
        $selected = $_POST['modifHalfgroup'];
        foreach ($halfgroups as $halfgroup){
            echo'<option value="'.$halfgroup['code'].'"'; if($halfgroup['code'] == $selected) echo "selected"; echo'>'.$halfgroup['title'].'</option>';
        }
        echo'
            </optgroup>
            </select>
            <input name="modifvalider" type="submit" value="Valider">
            <a href="'.$linkManageUser.'">Annuler</a>
         </form>
         </div>';
    }
}