<?php

class PontosUsuarioModel extends MY_Model {
	function __construct() {
		parent::__construct();
		$this->table = 'pontos_usuario';
    }
    
    function buscarPorUsuario($id) {
		$sql = "SELECT valor as xp, data_cadastro FROM pontos_usuario WHERE id_usuario = ? ORDER BY data_cadastro DESC LIMIT 5";

        $query = $this->db->query($sql, array($id));

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return null;
        }
	}
}