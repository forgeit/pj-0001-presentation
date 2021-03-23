<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DemandaAcompanhamento extends MY_Controller {

    public $PENDENTE_VALIDACAO = 2;
    public $VALIDA = 3;
    public $REJEITADA = 7;
    public $SUCESSO = 6;

    public function criarProtocolo($idDemanda) {
        return date('Ymd') . str_pad($idDemanda, 10, "0", STR_PAD_LEFT);
    }

    public function validacao() {
        $data = $this->security->xss_clean($this->input->raw_input_stream);
		$fluxo = json_decode($data, true);

        if (!isset($fluxo['demanda'])) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Demanda é obrigatório.")));
			die();
        }
        
        if (!isset($fluxo['situacao'])) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Situação é obrigatório.")));
			die();
        }

        $demanda = $this->DemandaModel->buscarPorId($fluxo['demanda'], 'id_demanda');

        if(is_null($demanda)) {
			print_r(json_encode($this->gerarRetorno(FALSE, "Demanda inválida.")));
			die();
        }
        
        $demanda['id_situacao'] = $fluxo['situacao'] == 'VALIDA' ? $this->VALIDA : $this->REJEITADA;

        $this->db->trans_begin();

        if (!$this->DemandaModel->atualizar($fluxo['demanda'], $demanda, 'id_demanda')) {
            $this->db->trans_rollback();
			print_r(json_encode($this->gerarRetorno(FALSE, "Erro ao vincular vereador a demanda.")));
			die();
		}

        $novoFluxo = array(
            'id_demanda' => $fluxo['demanda'],
            'id_usuario_operacao' => $this->getCodeUsuario()->id,
            'ts_transacao' => date('Y-m-d H:i:s'),
            'id_situacao' => $fluxo['situacao'] == 'VALIDA' ? $this->VALIDA : $this->REJEITADA
        );

        $this->DemandaFluxoModel->inserir($novoFluxo);

        if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			print_r(json_encode($this->gerarRetorno(FALSE, "Ocorreu um erro definir a situação da demanda.")));
		} else {
			$this->db->trans_commit();
			print_r(json_encode($this->gerarRetorno(TRUE, "Sucesso ao definir a situação da demanda.")));
		}
    }

    public function buscar() {
        $URL_BASE_IMG = 'http://vereador.forgeit.com.br/server/mobile/demanda/arquivo/';
		$URL_BASE_IMG_HIST = 'http://vereador.forgeit.com.br/server/mobile/demanda/historico/arquivo/';
        $URL_BASE_IMG_VEREADOR = 'http://vereador.forgeit.com.br/server/mobile/vereador/foto/';
        
        $dados = $this->DemandaModel->buscarPorIdAcompanhamento($this->uri->segment(3));
        
        if (is_null($dados)) {
            print_r(json_encode($this->gerarRetorno(FALSE, "Demanda não encontrada.")));
            die();
        }

        $dados['protocolo'] = $this->criarProtocolo($dados['id_demanda']);
        // unset($dados['id_demanda']);
        $dados['titulo'] = ucwords($dados['titulo']);

        if ($dados['existeVereador']) {
            $dados['vereador'] = array(
                'nome' => $dados['vereador'],
                'foto' => $URL_BASE_IMG_VEREADOR . $dados['id_vereador_responsavel']
            );
        }
        
        unset($dados['id_vereador_responsavel']);
        $dados['existeVereador'] = $dados['existeVereador'] ? TRUE : FALSE;

        $arquivos = $this->DemandaArquivoModel->buscarArquivosPorIdDemanda($this->uri->segment(3));

        foreach ($arquivos as $key => $value) {
            $arquivos[$key]['arquivo'] = $URL_BASE_IMG . $value['id_demanda_arquivo'];
            unset($arquivos[$key]['id_demanda_arquivo']);
        }
        
        $fluxo = $this->DemandaFluxoModel->buscarFluxoPorIdDemanda($this->uri->segment(3));
        
        $exibirFinalDeFluxo = false;

		foreach ($fluxo as $key => $value) {
            $fluxo[$key]['exibirQuadroDescricao'] = false; 

            if ($value['descricao'] != '' || $value['total'] > 0) {
                $fluxo[$key]['exibirQuadroDescricao'] = true; 
            }

            if ($value['final_de_fluxo']) {
                $exibirFinalDeFluxo = array(
                    'exibir' => true,
                    'mensagem' => $value['situacao'],
                    'bgColor' => $value['id_situacao'] == $this->SUCESSO ? 'bg-green' : 'bg-red'
                );
            }

			$fluxo[$key]['descricao'] = $value['descricao'] == '' ? 'Não Informado' : $value['descricao'];
			$fluxo[$key]['responsavelAtual'] = $value['pessoa'] == '' ? 'Não Informado' : $value['pessoa'];
            $fluxo[$key]['total'] = $value['total'] == 0 ? 'Não Possui' : $value['total'];
            
			unset($fluxo[$key]['total']);
			unset($fluxo[$key]['pessoa']);
			unset($fluxo[$key]['id_demanda_fluxo']);
		}

        $dados['exibirFinalDeFluxo'] = $exibirFinalDeFluxo;
        $dados['prazoFinal'] = $dados['prazoFinal'] == '00/00/0000' ? 'Não Informado' : $dados['prazoFinal'];
        $dados['existePrazoFinal'] = $dados['prazoFinal'] == '' ? FALSE : TRUE;
		$dados['prazoFinal'] = $dados['prazoFinal'] == '' ? 'Não Informado' : $dados['prazoFinal'];
		$dados['descricao'] = $dados['descricao'] == '' ? 'Não Informado' : $dados['descricao'];
		$dados['arquivos'] = $arquivos;
        $dados['fluxo'] = $fluxo;
        
        $dados['exibirControleValidacao'] = $dados['situacaoAtualCod'] == $this->PENDENTE_VALIDACAO;
        $dados['existeArquivos'] = count($dados['arquivos']) > 0;
        unset($dados['situacaoAtualCod']);

		$array = array('data' => array('DemandaDto' => $dados));

		print_r(json_encode($array));
	}

}