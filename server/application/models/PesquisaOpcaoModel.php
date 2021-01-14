<?php

class PesquisaOpcaoModel extends MY_Model {
	function __construct() {
		parent::__construct();
		$this->table = 'pesquisa_opcao';
	}

	function buscarPorPesquisa($pesquisa) {
		$sql = "SELECT * FROM pesquisa_opcao po WHERE id_pesquisa = ?";

        $query = $this->db->query($sql, array($pesquisa));

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return null;
        }
	}
}