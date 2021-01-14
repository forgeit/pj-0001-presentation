<?php

class PesquisaVotoModel extends MY_Model {
	function __construct() {
		parent::__construct();
		$this->table = 'pesquisa_voto';
	}

	function buscarPorUsuarioPesquisa($usuario, $pesquisa) {
		$sql = "select count(*) as total from pesquisa_voto pv 
				join pesquisa_opcao po ON po.id_pesquisa_opcao = pv.id_pesquisa_opcao 
				join pesquisa p on p.id_pesquisa = po.id_pesquisa 
				where 
				p.id_pesquisa = ?
				and pv.id_usuario = ?";

        $query = $this->db->query($sql, array($pesquisa, $usuario));

        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return null;
        }
	}
}