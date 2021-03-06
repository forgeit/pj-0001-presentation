<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DemandaFluxo extends MY_Controller {

	public function getArquivo()
    {
		$id =  $this->uri->segment(5);
		
		$model = $this->DemandaArquivoFluxoModel->buscarArquivo($id);

		$img = $model[0]['arquivo'];

        $partesNome = explode(".", $model[0]['nome']);
		$tipo = trim($partesNome[count($partesNome) -1]);
		$tipo = strtoupper($tipo);

		if ($tipo == "PDF") {
			header('Content-type: application/pdf');
		} else {
			$info = getimagesize($img);
        	header('Content-type: ' . $info['mime']);
		}

        readfile($img);
    }

	public function buscarArquivos() {	
		$lista = $this->DemandaFluxoModel->buscarArquivosPorDemanda($this->uri->segment(3));
		print_r(json_encode(array('data' => array ('ArrayList' => $lista ? $lista : array()))));
	}

	public function salvar() {
		$data = $this->security->xss_clean($this->input->raw_input_stream);
		$demandaFluxo = json_decode($data);

		$demandaFluxoModel = array();
		$demandaFluxoModel['id_demanda'] = $this->uri->segment(3);
		$demandaFluxoModel['id_usuario_operacao'] = $this->getCodeUsuario()->id;
		$demandaFluxoModel['ts_transacao'] = date('Y-m-d H:i:s');

		if (isset($demandaFluxo->situacao)) {
			if ($demandaFluxo->situacao) {
				$demandaFluxoModel['id_situacao'] = $demandaFluxo->situacao;
			} else {
				print_r(json_encode($this->gerarRetorno(FALSE, "É obrigatório informar a situação.")));
				die();
			}
		} else {
			print_r(json_encode($this->gerarRetorno(FALSE, "É obrigatório informar a situação.")));
			die();
		}

		if (isset($demandaFluxo->descricao)) {
			if ($demandaFluxo->descricao) {
				$demandaFluxoModel['descricao'] = $demandaFluxo->descricao;
			}
		}

		if (isset($demandaFluxo->destinatario)) {
			if ($demandaFluxo->destinatario) {
				$demandaFluxoModel['id_destinatario'] = $demandaFluxo->destinatario;
			}
		}

		$novosArquivos = array();

		if (isset($demandaFluxo->arquivos)) {
			if (count($demandaFluxo->arquivos) > 0) {
				$arquivosTemporarios = $demandaFluxo->arquivos;
				$temporario = "../arquivos/tmp/";
				$diretorio = "../arquivos/demanda_fluxo/";

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
							'arquivo' => $novoDiretorio . "/" . date('YmdHis-') . $value,
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

		$idDemandaFluxo = $this->DemandaFluxoModel->inserirRetornaId($demandaFluxoModel);		

		if (count($novosArquivos) > 0) {
			foreach ($novosArquivos as $key => $value) {
				$demandaArquivoFluxoModel = array();
				$demandaArquivoFluxoModel['id_demanda_fluxo'] = $idDemandaFluxo;
				$demandaArquivoFluxoModel['arquivo'] = $value['arquivo'];
				$demandaArquivoFluxoModel['nome'] = $value['nome'];
				$this->DemandaArquivoFluxoModel->inserir($demandaArquivoFluxoModel);
			}
		}

		$demandaModel['id_situacao'] = $demandaFluxoModel['id_situacao'];

		$this->DemandaModel->atualizar($this->uri->segment(3), $demandaModel, 'id_demanda');

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro ao atualizar o fluxo da demanda.")));
		} else {
			$this->db->trans_commit();
			print_r(json_encode($this->gerarRetorno(TRUE, "Sucesso ao atualizar o fluxo da demanda.")));
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