<?php

class DemandaArquivoFluxoModel extends MY_Model {
	function __construct() {
		parent::__construct();
		$this->table = 'demanda_arquivo_fluxo';
	}
	

	function removerPorIdDemanda($id) {
		$sql = "delete from demanda_arquivo_fluxo where id_demanda_fluxo in (select id_demanda_fluxo from demanda_fluxo where id_demanda = ?)";
        return $query = $this->db->query($sql, array($id));
	}
	
	function buscarArquivo($id) {
		$sql = "SELECT 
				*
				FROM demanda_arquivo_fluxo
				WHERE 
				id_demanda_arquivo_fluxo = ?";

        $query = $this->db->query($sql, array($id));

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return null;
        }
	}	

	function buscarArquivosPorIdFluxo($id) {
		$sql = "select
				* from demanda_arquivo_fluxo where id_demanda_fluxo = ?";

        $query = $this->db->query($sql, array($id));

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return null;
        }
	}
}