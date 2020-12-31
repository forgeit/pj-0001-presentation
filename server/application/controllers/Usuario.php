<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Usuario extends MY_Controller
{

	public function definirXPUsuarioMobile() {
		$data = $this->security->xss_clean($this->input->raw_input_stream);
		$usuario = json_decode($data, true);

		if (!$this->validarEntrada($usuario, 'usuario')) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Usuário é obrigatório.")));
			die();
		}

		if (!$this->validarEntrada($usuario, 'valor')) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Valor é obrigatório.")));
			die();
		}

		if (!$this->validarEntrada($usuario, 'tipo')) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Tipo é obrigatório.")));
			die();
		}

		$pontos = $this->PontosUsuarioModel->buscarPorUsuario($usuario['usuario']);

		if (is_null($pontos) || !is_array($pontos) || count($pontos) == 0) {
			$model = array(
				'id_usuario' => $usuario['usuario'],
				'valor' => 0,
				'data_cadastro' => date('Y-m-d')
			);
		} else {
			$model = array(
				'id_usuario' => $usuario['usuario'],
				'valor' => $pontos[0]['xp'],
				'data_cadastro' => date('Y-m-d')
			);
		}

		switch ($usuario['tipo']) {
			case 'add':
				$model['valor'] += $usuario['valor'];
				break;
			default:
				print_r(json_encode($this->gerarRetorno(FALSE, "Operação não implementada.")));
				die();
				break;
		}

		$this->db->trans_begin();

		$idXP = $this->PontosUsuarioModel->inserirRetornaId($model);

		if ($idXP) {
			$this->db->trans_complete();
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao definir o XP.")));
			} else {
				$this->db->trans_commit();
				$retorno = $this->gerarRetorno(TRUE, "Sucesso ao definir o XP.");
				unset($model['data_cadastro']);
				unset($model['id_usuario']);
				$retorno['data'] = $model;
				print_r(json_encode($retorno));
			}
		} else {
			$this->db->trans_rollback();
			print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao definir o XP.")));
		}
	}

	public function situacaoCadastroUsuarioMobile() {
		$id = $this->uri->segment(4);

		$usuario = $this->UsuarioModel->buscarPorId($id, 'id_usuario');

		if (is_null($usuario)) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Usuário não encontrado.")));
			die();
		}

		$pessoa = $this->PessoaModel->buscarPorId($usuario['id_pessoa'], 'id_pessoa');

		if (is_null($pessoa)) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Usuário não encontrado.")));
			die();
		}

		$mensagens = [];

		if (!$this->validarEntrada($pessoa, 'celular')) {
			$mensagens[] = "Celular não informado.";
		}

		if (!$this->validarEntrada($pessoa, 'id_cidade')) {
			$mensagens[] = "Cidade não informada.";
		}

		if (!$this->validarEntrada($pessoa, 'id_bairro')) {
			$mensagens[] = "Bairro não informado.";
		}

		if (!$this->validarEntrada($pessoa, 'id_logradouro')) {
			$mensagens[] = "Logradouro não informado.";
		}

		if (!$this->validarEntrada($pessoa, 'numero')) {
			$mensagens[] = "Número não informado.";
		}

		$retorno = array();
		$retorno['status'] = count($mensagens) > 0;
		$retorno['mensagens'] = $mensagens;

		print_r(json_encode($retorno));

	}

	public function criarUsuarioMobile()
	{
		$data = $this->security->xss_clean($this->input->raw_input_stream);
		$usuario = json_decode($data, true);

		if (!$this->validarEntrada($usuario, 'nome')) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Nome é obrigatório.")));
			die();
		}

		if (!$this->validarEntrada($usuario, 'login')) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Email é obrigatório.")));
			die();
		} else {

		}

		if (!$this->validarEntrada($usuario, 'cpf')) {
			print_r(json_encode($this->gerarRetorno(FALSE, "CPF é obrigatório.")));
			die();
		} else {
			if (!$this->validaCPF($usuario['cpf'])) {
				print_r(json_encode($this->gerarRetorno(FALSE, "CPF é inválido.")));
				die();
			} else {
			}
		}

		if (!$this->validarEntrada($usuario, 'senha')) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Senha é obrigatório.")));
			die();
		} else {
			$usuario['senha'] = md5($usuario['senha']);
		}

		$usuarioCadastro = $this->UsuarioModel->buscarPorId($usuario['login'], 'login');

		if ($usuarioCadastro) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Usuário já cadastrado.")));
			die();
		}

		$pessoaCadastro = $this->PessoaModel->buscarPorId($usuario['cpf'], 'cpf_cnpj');

		if ($pessoaCadastro) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Usuário já cadastrado.")));
			die();
		}

		$usuario['cargo'] = "Usuário";

		$pessoa = array();
		$pessoa['nome'] = $usuario['nome'];
		$pessoa['email'] = $usuario['login'];
		$pessoa['cpf_cnpj'] = $usuario['cpf'];
		$pessoa['id_tipo_pessoa'] = 4;
		$pessoa['fg_tipo_pessoa'] = TRUE;
		unset($usuario['cpf']);

		$this->db->trans_begin();

		$idPessoa = $this->PessoaModel->inserirRetornaId($pessoa);

		if ($idPessoa) {
			$usuario['id_pessoa'] = $idPessoa;

			$idUsuario = $this->UsuarioModel->inserirRetornaId($usuario);

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao criar o usuário.")));
			} else {
				$this->db->trans_commit();
				print_r(json_encode($this->gerarRetorno(TRUE, "Usuário criado com sucesso.")));
			}
		} else {
			$this->db->trans_rollback();
			print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao criar o usuário.")));
		}
	}

	public function buscarVereadoresMobile()
	{
		$lista = $this->UsuarioModel->buscarComboVereadores();

		$array = array('data' => array('vereadores' => $lista));
		print_r(json_encode($array));
	}

	public function buscarDadosUsuarioMobile()
	{
		$id = $this->uri->segment(4);

		$usuario = $this->UsuarioModel->buscarPorId($id, 'id_usuario');


		if (is_null($usuario)) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Usuário não encontrado.")));
			die();
		}

		$pessoa = $this->PessoaModel->buscarPorId($usuario['id_pessoa'], 'id_pessoa');

		if (is_null($pessoa)) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Usuário não encontrado.")));
			die();
		}

		$retorno = array();

		$retorno['nome'] = $usuario['nome'];
		$retorno['login'] = $usuario['login'];

		$imagem = $usuario['imagem'];

		if (!file_exists($imagem)) {
			$imagem = __DIR__ . "/../../../src/app/layout/img/perfil.jpg";
		}

		$type = pathinfo($imagem, PATHINFO_EXTENSION);
		$data = file_get_contents($imagem);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

		$retorno['imagem'] = $base64;

		$retorno['dadosPessoais'] = array();
		$retorno['dadosPessoais']['email'] = $pessoa['email'];
		$retorno['dadosPessoais']['cpf_cnpj'] = $pessoa['cpf_cnpj'];
		$retorno['dadosPessoais']['telefone'] = $pessoa['telefone'];
		$retorno['dadosPessoais']['celular'] = $pessoa['celular'];
		$retorno['dadosPessoais']['fg_tipo_pessoa'] = $pessoa['fg_tipo_pessoa'] == 1 ? "F" : "J";

		$retorno['endereco'] = array();

		if (!is_null($pessoa['id_logradouro'])) {
			$logradouro = $this->LogradouroModel->buscarPorId($pessoa['id_logradouro'], 'id_logradouro');
			if (!is_null($logradouro)) {
				$retorno['endereco']['id_logradouro'] = (int) $logradouro['id_logradouro'];
				$retorno['endereco']['logradouro'] = $logradouro['nome'];
			}
		}

		if (!is_null($pessoa['id_bairro'])) {
			$bairro = $this->BairroModel->buscarPorId($pessoa['id_bairro'], 'id_bairro');
			if (!is_null($bairro)) {
				$retorno['endereco']['id_bairro'] = (int) $bairro['id_bairro'];
				$retorno['endereco']['bairro'] = $bairro['nome'];
			}
		}

		if (!is_null($pessoa['id_cidade'])) {
			$cidade = $this->CidadeModel->buscarPorId($pessoa['id_cidade'], 'id_cidade');
			if (!is_null($cidade)) {
				$retorno['endereco']['id_cidade'] = (int) $cidade['id_cidade'];
				$retorno['endereco']['cidade'] = $cidade['nome'];
				$retorno['endereco']['uf'] = $cidade['uf'];
			}
		}

		$pontos = $this->PontosUsuarioModel->buscarPorUsuario($usuario['id_usuario']);

		if (is_null($pontos)) {
			$pontos = array(
				array('xp' => 0, 'data_cadastro' => date('Y-m-d'))
			);
		}

		$retorno['pontos'] = $pontos;

		$array = array('data' => array('Usuario' => $retorno));
		print_r(json_encode($array));
	}

	public function alterarSenha()
	{
		$data = $this->security->xss_clean($this->input->raw_input_stream);
		$usuario = json_decode($data);

		$usuarioAtual = $this->UsuarioModel->buscarPorId($this->uri->segment(3), 'id_usuario');

		if ($usuarioAtual['senha'] !== md5($usuario->senha)) {
			print_r(json_encode($this->gerarRetorno(FALSE, "A senha atual está errada.")));
			die();
		}

		if ($usuario->novaSenha !== $usuario->confirmacao) {
			print_r(json_encode($this->gerarRetorno(FALSE, "A nova senha deve ser igual a confirmação.")));
			die();
		}

		$usuarioAtual['senha'] = md5($usuario->novaSenha);

		if ($this->UsuarioModel->atualizar($this->uri->segment(3), $usuarioAtual, 'id_usuario')) {
			print_r(json_encode($this->gerarRetorno(TRUE, "Sucesso ao alterar a senha.")));
			die();
		} else {
			print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao alterar a senha.")));
			die();
		}
	}

	private function gerarRetorno($response, $mensagem)
	{
		$message = array();
		$message[] = $response == TRUE ?
			array('tipo' => 'success', 'mensagem' => $mensagem) :
			array('tipo' => 'error', 'mensagem' => $mensagem);

		$array = array(
			'message' => $message,
			'status' => $response == TRUE ? 'true' : 'false'
		);

		return $array;
	}

	public function validaCPF($cpf)
	{
		if (strlen($cpf) != 11) {
			return false;
		}

		if (preg_match('/(\d)\1{10}/', $cpf)) {
			return false;
		}

		for ($t = 9; $t < 11; $t++) {
			for ($d = 0, $c = 0; $c < $t; $c++) {
				$d += $cpf[$c] * (($t + 1) - $c);
			}
			$d = ((10 * $d) % 11) % 10;
			if ($cpf[$c] != $d) {
				return false;
			}
		}
		return true;
	}
}
