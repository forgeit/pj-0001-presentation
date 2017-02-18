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
}