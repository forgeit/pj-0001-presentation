<?php

class PesquisaModel extends MY_Model {
	function __construct() {
		parent::__construct();
		$this->table = 'pesquisa';
	}

	function buscarPesquisaUsuario($usuario) {
		$sql = "SELECT p2.*, tp.*, u.nome, u.imagem as foto_perfil FROM pesquisa p2 
                JOIN tipo_pesquisa tp ON tp.id_tipo_pesquisa = p2.id_tipo_pesquisa 
                JOIN usuario u ON u.id_usuario = p2.id_usuario_responsavel
                WHERE id_pesquisa NOT IN 
                (
                    SELECT po.id_pesquisa FROM pesquisa_voto pv 
                    JOIN pesquisa_opcao po ON pv.id_pesquisa_opcao = po.id_pesquisa_opcao 
                    WHERE pv.id_usuario = ?
                )
                AND p2.data_validade >= CURRENT_DATE
                ORDER BY p2.data_validade ASC 
                LIMIT 1";

        $query = $this->db->query($sql, array($usuario));

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return null;
        }
	}
}