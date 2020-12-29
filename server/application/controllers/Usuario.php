<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario extends MY_Controller {

	public function buscarDadosUsuarioMobile() {
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

	public function alterarSenha() {
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

	private function gerarRetorno($response, $mensagem) {
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
}