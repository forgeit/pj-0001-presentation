<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Demanda extends MY_Controller {

	private $DEMANDA_PENDENTE_DE_VALIDACAO = 7;

	public function vincularVereador() {
		$idDemanda =  $this->uri->segment(3);
		$idVereador =  $this->uri->segment(5);

		$usuario = $this->UsuarioModel->buscarPorId($idVereador, 'id_usuario');
		$demanda = $this->DemandaModel->buscarPorId($idDemanda, 'id_demanda');

		if(is_null($usuario) || is_null($demanda)) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Demanda ou vereador inválido.")));
			die();
		}

		if (!$usuario['flag_vereador']) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Usuário não é um vereador.")));
			die();
		}
		
		$demanda['id_vereador_responsavel'] = $usuario['id_usuario'];

		if ($demanda['id_situacao'] >= 3) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Não é mais possível alterar o vereador.")));
			die();
		}

		if ($this->DemandaModel->atualizar($idDemanda, $demanda, 'id_demanda')) {
			print_r(json_encode($this->gerarRetorno(TRUE, "Vinculado com sucesso.")));
			die();
		} else {
			print_r(json_encode($this->gerarRetorno(FALSE, "Erro ao vincular vereador a demanda.")));
			die();
		}
	}

	public function getArquivo()
    {
		$id =  $this->uri->segment(4);
		
		$model = $this->DemandaArquivoModel->buscarArquivo($id);

		$img = $model[0]['arquivo'];

        $info = getimagesize($img);
        header('Content-type: ' . $info['mime']);
        readfile($img);
    }

	public function getImagem()
    {
		$id =  $this->uri->segment(3);
		
		$model = $this->DemandaArquivoModel->buscarArquivo($id);

		$img = $model[0]['arquivo'];

        $info = getimagesize($img);
        header('Content-type: ' . $info['mime']);
        readfile($img);
    }

	public function criarDemandaMobile() {
		$data = $this->security->xss_clean($this->input->raw_input_stream);
		$demanda = json_decode($data, true);
		$demanda['id_situacao'] = $this->DEMANDA_PENDENTE_DE_VALIDACAO;
		$demanda['dt_criacao'] = date('Y-m-d');
		$demanda['dt_contato'] = date('Y-m-d');

		$solicitante = isset($demanda['solicitante']) ? $demanda['solicitante'] : null; 

		if (is_null($solicitante)) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Informe o solicitante.")));
			die();
		}

		if (!isset($demanda['descricao'])) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Descreva a demanda.")));
			die();
		}

		if (!isset($demanda['id_tipo_demanda'])) {
			$demanda['tipo_demanda'] = 1;
		}

		if (!isset($demanda['id_vereador_responsavel'])) {
			$demanda['id_vereador_responsavel'] = null;
		} else {
			$vereador = $this->UsuarioModel->buscarPorId($demanda['id_vereador_responsavel'], 'id_usuario');

			if (is_null($vereador)) {
				print_r(json_encode($this->gerarRetorno(FALSE, "Vereador não encontrado na base de dados.")));
				die();	
			}
		}

		unset($demanda['solicitante']);

		$arquivos = $demanda['arquivos'];

		unset($demanda['arquivos']);

		$pessoa = $this->PessoaModel->buscarPorEmail($solicitante['email'])[0];

		if (is_null($pessoa)) {
			print_r(json_encode($this->gerarRetorno(FALSE, "O solicitante informado não está cadastrado na base de dados.")));
			die();
		}

		$demanda['id_solicitante'] = $pessoa['id_pessoa'];

		$this->db->trans_begin();

		$idDemanda = $this->DemandaModel->inserirRetornaId($demanda);

		$demandaFluxoModel = array(
			'id_demanda' => $idDemanda,
			'id_situacao' => $this->DEMANDA_PENDENTE_DE_VALIDACAO,
			'ts_transacao' => date('Y-m-d H:i:s')
		);

		$this->DemandaFluxoModel->inserir($demandaFluxoModel);

		if (count($arquivos) > 0) {
			foreach ($arquivos as $key => $value) {
				$imagem = $this->gerarImagem($value['base64'], $value['nome']);

				$demandaArquivoModel = array();
				$demandaArquivoModel['id_demanda'] = $idDemanda;
				$demandaArquivoModel['arquivo'] = $imagem;
				$demandaArquivoModel['nome'] = $value['nome'];
				$this->DemandaArquivoModel->inserir($demandaArquivoModel);
			}
		}

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao registrar a nova demanda.")));
		} else {
			$this->db->trans_commit();
			print_r(json_encode($this->gerarRetorno(TRUE, "A demanda foi registrada com sucesso.")));
		}
	}

	public function buscarDemandasPorUsuarioMobile() {
		$lista = $this->DemandaModel->buscarPorUsuario($this->uri->segment(4));
		print_r(json_encode(array('data' => array ('datatables' => $lista ? $lista : array()))));
	}

	public function gerarImagem($base64, $nome) {
		if (!file_exists("/home1/forge821/dados/sistema/demandas/fotos/")) {
			return null;
		}

		$folderPath =  "/home1/forge821/dados/sistema/demandas/fotos/" . date('Ymd') . "/";

		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0755, true);
		}

        $image_base64 = base64_decode($base64);

		$file = $folderPath . uniqid() . '-' . $nome;

        file_put_contents($file, $image_base64);

        if (file_exists($file)) {
        	return $file;
        } else {
        	return null;
        }

	}

	public function buscarTodos() {
		$lista = $this->DemandaModel->buscarTodosNativo();
		print_r(json_encode(array('data' => array ('datatables' => $lista ? $lista : array()))));
	}

	public function buscarPorData() {
		$data = $this->uri->segment(4) . '-' . $this->uri->segment(3) . '-' . $this->uri->segment(2);
		$lista = $this->DemandaModel->buscarPorDataNativo($data);
		print_r(json_encode(array('data' => array ('datatables' => $lista ? $lista : array()))));
	}

	public function buscarMobile() {
		$URL_BASE_IMG = 'http://vereador.forgeit.com.br/server/mobile/demanda/arquivo/';
		$URL_BASE_IMG_HIST = 'http://vereador.forgeit.com.br/server/mobile/demanda/historico/arquivo/';
		$URL_BASE_IMG_VEREADOR = 'http://vereador.forgeit.com.br/server/mobile/vereador/foto/';
		
		$dados = $this->DemandaModel->buscarPorIdCompleto($this->uri->segment(4));
		$arquivos = $this->DemandaArquivoModel->buscarArquivosPorIdDemanda($this->uri->segment(4));

		$flagPossuiArquivos = false;
		
		if (is_array($arquivos) && count($arquivos) > 0) {
			$flagPossuiArquivos = true;

			foreach ($arquivos as $key => $value) {
				$arquivos[$key]['imagem'] = $URL_BASE_IMG . $value['id_demanda_arquivo'];
				unset($arquivos[$key]['arquivo']);
				unset($arquivos[$key]['id_demanda_arquivo']);
			}
		}

		$dados['possuiArquivo'] = $flagPossuiArquivos;

		$fluxo = $this->DemandaFluxoModel->buscarFluxoPorIdDemanda($this->uri->segment(4));

		foreach ($fluxo as $key => $value) {
			$fluxo[$key]['descricao'] = $value['descricao'] == '' ? 'Não Informado' : $value['descricao'];
			$fluxo[$key]['pessoa'] = $value['pessoa'] == '' ? 'Não Informado' : $value['pessoa'];
			$fluxo[$key]['numero_arquivos'] = $value['total'] == 0 ? 'Não Possui' : $value['total'];
			$fluxo[$key]['data_fluxo'] = $value['tsTransacao'];
			unset($fluxo[$key]['total']);
			unset($fluxo[$key]['tsTransacao']);

			$arquivosFluxo = $this->DemandaArquivoFluxoModel->buscarArquivosPorIdFluxo($value['id_demanda_fluxo']);
			$flagPossuiArquivosHistorico = false;
			
			if (is_array($arquivosFluxo) && count($arquivosFluxo) > 0) {
				$flagPossuiArquivosHistorico = true;
				foreach ($arquivosFluxo as $keyImg => $img) {
					$arquivosFluxo[$keyImg]['imagem'] = $URL_BASE_IMG_HIST . $img['id_demanda_arquivo_fluxo'];
					unset($arquivosFluxo[$keyImg]['arquivo']);
					unset($arquivosFluxo[$keyImg]['id_demanda_arquivo_fluxo']);
					unset($arquivosFluxo[$keyImg]['id_demanda_fluxo']);
				}
			}

			$fluxo[$key]['arquivos'] = $arquivosFluxo;
			$fluxo[$key]['possuiArquivo'] = $flagPossuiArquivosHistorico;
		}

		$vereador = $this->UsuarioModel->buscarPorId($dados['id_vereador_responsavel'], 'id_usuario');
		$situacao = $this->SituacaoModel->buscarPorId($dados['id_situacao'], 'id_situacao');

		$naoRetornarVereador = is_null($vereador) || !$vereador;

		if (!$naoRetornarVereador) {
			$dados['vereador'] = array(
				'nome' => $vereador['nome'],
				'foto' => $URL_BASE_IMG_VEREADOR . $vereador['id_usuario']
			);
		}
		
		$dados['situacao'] = $situacao['descricao'];
		unset($dados['id_situacao']);
		
		$dados['abertura'] = $dados['dtContato'] == '00/00/0000' ? 'Não Informado' : $dados['dtContato'];
		$dados['prazoFinal'] = $dados['prazoFinal'] == '00/00/0000' ? 'Não Informado' : $dados['prazoFinal'];
		$dados['prazoFinal'] = $dados['prazoFinal'] == '' ? 'Não Informado' : $dados['prazoFinal'];
		$dados['descricao'] = $dados['descricao'] == '' ? 'Não Informado' : $dados['descricao'];
		unset($dados['id_vereador_responsavel']);
		unset($dados['dtContato']);
		$dados['arquivos'] = $arquivos;
		$dados['fluxo'] = $fluxo;

		$array = array('data' => $dados);

		print_r(json_encode($array));
	}

	public function buscar() {

		$dados = $this->DemandaModel->buscarPorIdCompleto($this->uri->segment(3));
		$arquivos = $this->DemandaArquivoModel->buscarArquivosPorIdDemanda($this->uri->segment(3));

		if (is_array($arquivos) && count($arquivos) > 0) {
			foreach ($arquivos as $key => $value) {
				$imagem = $value['arquivo'];

				if (!file_exists($imagem)) {
					continue;
				}

				$type = pathinfo($imagem, PATHINFO_EXTENSION);
				$data = file_get_contents($imagem);
				$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

				$arquivos[$key]['base64'] = $base64;
			}
		}

		$fluxo = $this->DemandaFluxoModel->buscarFluxoPorIdDemanda($this->uri->segment(3));

		foreach ($fluxo as $key => $value) {
			$fluxo[$key]['descricao'] = $value['descricao'] == '' ? 'Não Informado' : $value['descricao'];
			$fluxo[$key]['pessoa'] = $value['pessoa'] == '' ? 'Não Informado' : $value['pessoa'];
			$fluxo[$key]['total'] = $value['total'] == 0 ? 'Não Possui' : $value['total'];
		}

		$dados['dtContato'] = $dados['dtContato'] == '00/00/0000' ? 'Não Informado' : $dados['dtContato'];
		$dados['prazoFinal'] = $dados['prazoFinal'] == '00/00/0000' ? 'Não Informado' : $dados['prazoFinal'];
		$dados['prazoFinal'] = $dados['prazoFinal'] == '' ? 'Não Informado' : $dados['prazoFinal'];
		$dados['descricao'] = $dados['descricao'] == '' ? 'Não Informado' : $dados['descricao'];
		$dados['id_vereador_responsavel'] = $dados['id_vereador_responsavel'];
		$dados['arquivos'] = $arquivos;
		$dados['fluxo'] = $fluxo;

		$array = array('data' => array('DemandaDto' => $dados));

		print_r(json_encode($array));
	}

	public function salvar() {
		$data = $this->security->xss_clean($this->input->raw_input_stream);
		$demanda = json_decode($data);
		$demandaModel = array();
		$demandaModel['dt_criacao'] = date('Y-m-d');
		$demandaModel['id_situacao'] = 1;// Demanda iniciada

		if ($demanda->titulo) {
			$demandaModel['titulo'] = strtoupper($demanda->titulo);
		} else {
			print_r(json_encode($this->gerarRetorno(FALSE, "O campo título é obrigatório.")));
			die();
		}

		if ($demanda->solicitante) {
			$demandaModel['id_solicitante'] = strtoupper($demanda->solicitante);
		} else {
			print_r(json_encode($this->gerarRetorno(FALSE, "O campo solicitante é obrigatório.")));
			die();
		}

		if (isset($demanda->descricao)) {
			if ($demanda->descricao) {
				$demandaModel['descricao'] = strtoupper($demanda->descricao);
			}
		}

		if ($demanda->tipoDemanda) {
			$demandaModel['id_tipo_demanda'] = strtoupper($demanda->tipoDemanda);
		} else {
			print_r(json_encode($this->gerarRetorno(FALSE, "O campo tipo de demanda é obrigatório.")));
			die();
		}		

		if ($demanda->dtContato) {
			$data = explode("/", $demanda->dtContato);
			$demandaModel['dt_contato'] = $data[2] . '-' . $data[1] . '-' . $data[0];
		} else {
			print_r(json_encode($this->gerarRetorno(FALSE, "O campo data de contato é obrigatório.")));
			die();
		}		

		if (isset($demanda->prazoFinal)) {
			if ($demanda->prazoFinal) {

				$data = explode("/", $demanda->prazoFinal);
				$demandaModel['prazo_final'] = $data[2] . '-' . $data[1] . '-' . $data[0];

			}
		}
		
		$novosArquivos = array();

		if (isset($demanda->arquivos)) {
			if (count($demanda->arquivos) > 0) {
				$arquivosTemporarios = $demanda->arquivos;
				$temporario = "../arquivos/tmp/";
				$diretorio = "../arquivos/demanda/";

				foreach ($arquivosTemporarios as $key => $value) {
					if (!file_exists($temporario . $value)) {
						print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao efetuar o upload.")));
						die();
					} else {
						$novoDiretorio = $diretorio . date('Ymd');
						if (!file_exists($novoDiretorio)) {
							if (!mkdir($novoDiretorio)) {
								print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao criar o diretório.")));
								die();
							}
						}

						if (!is_dir($novoDiretorio)) {
							print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao criar o diretório.")));
							die();
						}

						$novo = array (
							'arquivo' => $novoDiretorio . "/" . date('YmdHis-') . rand(1001, 9999) . "-" . $value,
							'nome' => $value);

						if (!copy($temporario . $value, $novo['arquivo'])) {
							print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao efetuar o upload.")));
							die();	
						}	

						$novosArquivos[] = $novo;
					}
				}
			}
		}

		$this->db->trans_begin();
		$idDemanda = $this->DemandaModel->inserirRetornaId($demandaModel);

		$demandaFluxoModel = array(
			'id_demanda' => $idDemanda,
			'id_situacao' => 1,
			'ts_transacao' => date('Y-m-d H:i:s')
		);

		$this->DemandaFluxoModel->inserir($demandaFluxoModel);

		if (count($novosArquivos) > 0) {
			foreach ($novosArquivos as $key => $value) {
				$demandaArquivoModel = array();
				$demandaArquivoModel['id_demanda'] = $idDemanda;
				$demandaArquivoModel['arquivo'] = $value['arquivo'];
				$demandaArquivoModel['nome'] = $value['nome'];
				$this->DemandaArquivoModel->inserir($demandaArquivoModel);
			}
		}

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao registrar a nova demanda.")));
		} else {
			$this->db->trans_commit();
			print_r(json_encode($this->gerarRetorno(TRUE, "A demanda foi registrada com sucesso.")));
		}
	}

	public function remover() {
		$id = $this->uri->segment(3);

		$this->db->trans_begin();

		try {
			if (!$this->DemandaArquivoFluxoModel->removerPorIdDemanda($id)) {
				$this->db->trans_rollback();
				print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao remover.")));
				die();
			}

			if (!$this->DemandaFluxoModel->removerPorIdDemanda($id)) {
				$this->db->trans_rollback();
				print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao remover.")));
				die();
			}

			if (!$this->DemandaArquivoModel->removerPorIdDemanda($id)) {
				$this->db->trans_rollback();
				print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao remover.")));
				die();
			}

			if (!$this->DemandaModel->removerPorIdDemanda($id)) {
				$this->db->trans_rollback();
				print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao remover.")));
				die();
			}

			if ($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao remover.")));
			} else {
				$this->db->trans_commit();
				print_r(json_encode($this->gerarRetorno(TRUE, "Sucesso ao remover.")));
			}
		} catch(Exception $ex) {
			$this->db->trans_rollback();
			print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao remover.")));
		}

	}

	// private function gerarRetorno($response, $mensagem) {
	// 	$message = array();
	// 	$message[] = $response == TRUE ? 
	// 		array('tipo' => 'success', 'mensagem' => $mensagem) : 
	// 		array('tipo' => 'error', 'mensagem' => $mensagem);

	// 	$array = array(
	// 		'message' => $message,
	// 		'status' => $response == TRUE ? 'true' : 'false'
	// 	);

	// 	return $array;
	// }
	
}