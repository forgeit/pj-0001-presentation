<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PesquisaVoto extends MY_Controller {

	public function salvarMobile() {
		$data = $this->security->xss_clean($this->input->raw_input_stream);
		$entrada = json_decode($data, true);

		if (!$this->validarEntrada($entrada, 'usuario') || !$this->validarEntrada($entrada, 'opcao')) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Usuário e opção são obrigatórios.")));
			die();
		}

		$usuario = $this->UsuarioModel->buscarPorId($entrada['usuario'], 'id_usuario');
		$opcao = $this->PesquisaOpcaoModel->buscarPorId($entrada['opcao'], 'id_pesquisa_opcao');

		if (is_null($usuario) || is_null($opcao)) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Usuário e opção são obrigatórios.")));
			die();
		}

		$total = $this->PesquisaVotoModel->buscarPorUsuarioPesquisa($usuario['id_usuario'], $opcao['id_pesquisa']);

		if (is_null($total)) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Erro ao validar usuário.")));
			die();
		}

		if ($total['total'] > 0) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Usuário já votou na pesquisa.")));
			die();
		}

		$voto = array();

		$voto['id_usuario'] = $usuario['id_usuario'];
		$voto['id_pesquisa_opcao'] = $opcao['id_pesquisa_opcao'];
		$voto['data_registro'] = date('Y-m-d H:i:s');

		$this->db->trans_begin();
		$id = $this->PesquisaVotoModel->inserirRetornaId($voto);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			print_r(json_encode($this->gerarRetorno(FALSE, "Erro ao registrar.")));
		} else {
			$this->db->trans_commit();
			print_r(json_encode($this->gerarRetorno(TRUE, "Voto registrado com sucesso.")));
		}
        
	}
	
}