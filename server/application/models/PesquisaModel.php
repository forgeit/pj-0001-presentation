<?php

class PesquisaModel extends MY_Model {
	function __construct() {
		parent::__construct();
		$this->table = 'pesquisa';
    }
    
    function buscarTotalSemResposta($usuario) {
		$sql = "SELECT count(*) AS total FROM pesquisa p2 
                JOIN tipo_pesquisa tp ON tp.id_tipo_pesquisa = p2.id_tipo_pesquisa 
                JOIN usuario u ON u.id_usuario = p2.id_usuario_responsavel
                WHERE id_pesquisa NOT IN 
                (
                    SELECT po.id_pesquisa FROM pesquisa_voto pv 
                    JOIN pesquisa_opcao po ON pv.id_pesquisa_opcao = po.id_pesquisa_opcao 
                    WHERE pv.id_usuario = ?
                )
                AND p2.data_validade >= CURRENT_DATE
                ORDER BY p2.data_validade ASC";

        $query = $this->db->query($sql, array($usuario));

        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return null;
        }
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
    

    function buscarMinhasPesquisas($usuario) {
		$sql = "SELECT 
                p.id_pesquisa as id,
                p.titulo,
                p.imagem,
                p.resumo,
                p.data_validade as validade,
                p.data_criacao  as criacao,
                u.nome as responsavel,
                u.imagem  as foto_perfil,
                po.titulo as opcao,
                pv.data_registro as data_voto
                FROM pesquisa_voto pv 
                JOIN pesquisa_opcao po ON pv.id_pesquisa_opcao = po.id_pesquisa_opcao 
                JOIN pesquisa p ON p.id_pesquisa = po.id_pesquisa 
                JOIN usuario u ON u.id_usuario = p.id_usuario_responsavel 
                WHERE pv.id_usuario = ?
                ORDER BY pv.data_registro";

        $query = $this->db->query($sql, array($usuario));

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return null;
        }
	}
}