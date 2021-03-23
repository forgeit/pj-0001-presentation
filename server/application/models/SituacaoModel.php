<?php

class SituacaoModel extends MY_Model {
	function __construct() {
		parent::__construct();
		$this->table = 'situacao';
	}

	function buscarCombo() {
		$sql = "SELECT id_situacao, descricao FROM situacao WHERE exibir_combo = 1 ORDER BY 2";

		$query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return null;
        }
	}
}