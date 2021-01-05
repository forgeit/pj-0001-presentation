<?php

class UsuarioModel extends MY_Model {
	function __construct() {
		parent::__construct();
		$this->table = 'usuario';
	}

	function buscarComboVereadores() {
		$sql = "SELECT id_usuario as id, nome as descricao, imagem FROM usuario WHERE flag_vereador ORDER BY 2";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return null;
        }
	}

	function verificarLogin($login, $senha) {

		$sql = "SELECT 
				id_usuario, nome, cargo, imagem
				FROM usuario
				WHERE login = ? AND senha = ?";

        $query = $this->db->query($sql, array($login, $senha));

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return null;
        }
	}
}